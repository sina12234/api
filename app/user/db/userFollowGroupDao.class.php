<?php

class user_db_userFollowGroupDao
{
    const dbName = 'db_user';
    const TABLE = 't_user_follow_group';

    /**
     * db init
     *
     * @param string $dbName
     * @param string $dbType
     * @return SDb
     *
     * @author wen 2015-12-16
     */
    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    /**
     * @desc add group
     *
     * @param $data
     * @return bool|int
     *
     * @author wen 2015-12-16
     */
    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $data);

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc update group
     *
     * @param $uid
     * @param $groupId
     * @param $data
     * @return bool|int
     *
     * @author wen 2015-12-16
     */
    public static function update($uid, $groupId, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_follow_group={$groupId} and fk_user={$uid}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc fetch row
     *
     * @param $pKey
     * @return bool
     *
     * @author wen 2015-12-16
     */
    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_follow_group={$pKey}";

        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc delete group info by uid group id
     *
     * @param $uid
     * @param $groupId
     * @return bool|int
     *
     * @author wen 2015-12-16
     */
    public static function del($uid, $groupId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_follow_group={$groupId} and fk_user={$uid}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc get group list by uid
     *
     * @param $uid
     * @param int $page
     * @param int $length
     * @return DbData
     *
     * @author wen 2015-12-16
     */
    public static function groupList($uid, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user={$uid}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
