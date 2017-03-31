<?php

class tweeter_db_picDao
{
    const dbName = 'db_tweeter';
    const TABLE = 't_tweeter_pic';

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

    public static function update($pKey, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter_pic={$pKey}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter_pic={$pKey}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter_pic={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getPicList($tweeterId, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName);
        if (is_array($tweeterId)) {
            $str = implode(',', $tweeterId);
            $condition = "fk_tweeter IN ({$str})";
        } else {
            $condition = "fk_tweeter={$tweeterId}";
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        return $res;
    }
}
