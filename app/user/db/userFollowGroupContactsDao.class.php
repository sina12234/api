<?php

class user_db_userFollowGroupContactsDao
{
    const dbName = 'db_user';
    const TABLE = 't_user_follow_group_contacts';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $data);

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function update($pKey, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_follow_group_contacts={$pKey}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_follow_group_contacts={$pKey}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_follow_group_contacts={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc user group move support batch
     *
     * @param $userIdArr
     * @param $groupId
     * @param $loginUser
     * @return bool|int
     * @author wen
     */
    public static function move($userIdArr, $groupId, $loginUser)
    {
        if (count($userIdArr) < 1) {
            SLog::debug('params error [%s]', var_export($userIdArr, 1));
            return false;
        }

        $db = self::InitDB(self::dbName);
        $condition = "fk_user_owner={$loginUser} AND fk_user IN (".implode(',', $userIdArr).')';
        $data = [
            'fk_user_follow_group' => $groupId
        ];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
        }

        return $res;
    }

    public static function checkUserIsExist($user, $loginUser)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$user} and fk_user_owner={$loginUser}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function listsByGroupId($groupId, $userId, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user_follow_group={$groupId} and fk_user_owner={$userId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
        }

        return $res;
    }

    public static function getDefaultAndBlackGroupList($userId, $page, $length)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user_follow_group IN (-4, -5) and fk_user_owner={$userId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
        }

        return $res;
    }

    public static function getUserByUserIds($userIds)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user_follow_group NOT IN (-1, -4) and fk_user IN ($userIds)";
        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
        }

        return $res;
    }

    public static function getEachGroupNum($userId)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "fk_user_owner={$userId}";
        $item = ['fk_user_follow_group', 'count(*) as num'];

        $res = $db->select(self::TABLE, $condition, $item, 'fk_user_follow_group');
        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
        }

        return $res;
    }

    public static function delContact($userId, $linkManId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user_owner={$userId} and fk_user={$linkManId}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
        }

        return $res;
    }
}
