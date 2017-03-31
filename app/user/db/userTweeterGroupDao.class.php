<?php

class user_db_userTweeterGroupDao
{
    const dbName = 'db_user';
    const TABLE = 't_user_tweeter_group';
    const ExpiredTime = 7200;

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($insertData, $updateData=[])
    {
        $db = self::InitDB(self::dbName);

        if (!empty($updateData)) {
            $res = $db->insert(self::TABLE, $insertData, false, false, $updateData);
        } else {
            $res = $db->insert(self::TABLE, $insertData);
        }

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function update($uid, $groupId, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_tweeter_group={$groupId} and fk_user={$uid}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_tweeter_group={$pKey}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function del($uid, $groupId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_tweeter_group={$groupId} and fk_user={$uid}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        return $res;
    }

    public static function groupList($uid, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName, 'query');
        if (!empty($v)) return $v;
        $condition = "fk_user={$uid}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);

        return $res;
    }

    public static function updateGroupNum($pKey, $num = 1)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user_tweeter_group={$pKey}";

        $data = [
            "group_user_count=group_user_count+{$num}"
        ];
        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
