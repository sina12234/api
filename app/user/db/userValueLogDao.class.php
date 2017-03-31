<?php

class user_db_userValueLogDao
{
    const dbName = 'db_user';
    const TABLE = 't_user_account_log';

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
        $condition = "pk_log={$pKey}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_log={$pKey}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_log={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getLogByidArr($idArr){
        $db = self::InitDB('db_user', 'query');
        $table = self::TABLE;
        $idStr = implode(',', $idArr);
        $condition = "pk_log IN ($idStr)";
        return $db->select($table, $condition);
    }
}
