<?php

class tag_db_tagDao
{
    const dbName = 'db_tag';
    const TABLE = 't_tag';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getTagsByUserId($userId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user={$userId}";
        $res = $db->select(self::TABLE, $condition);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getTagName($tagIdArr, $page = 1, $length = -1)
    {
        if (count($tagIdArr) < 1) return false;
        $db = self::InitDB(self::dbName, 'query');
        $idStr = implode(',', $tagIdArr);

        $condition = "pk_tag IN ({$idStr})";
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        return $res;
    }

    public static function addTag($data)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($id)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_tag={$id}";

        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}

