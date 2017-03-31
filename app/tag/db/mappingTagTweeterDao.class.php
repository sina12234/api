<?php

class tag_db_mappingTagTweeterDao
{
    const dbName = 'db_tag';
    const TABLE = 't_mapping_tag_Tweeter';
    const ExpiredTime = 7200;

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getAllTagArticleCountListByTeacherId($teacherId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user={$teacherId} and status=1";
        $item = ['fk_tag','status','count(*) as total'];
        $res = $db->select(self::TABLE, $condition, $item, 'fk_tag', 'last_updated desc');

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getTweeter($tagId, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_tag={$tagId} and status=1";
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

    public static function getTagId($tweeterIdArr)
    {
        if (count($tweeterIdArr) < 1) return false;
        $db = self::InitDB(self::dbName, 'query');

        sort($tweeterIdArr);
        $idStr = implode(',', $tweeterIdArr);
        $condition = "fk_tweeter IN ({$idStr}) and status=1";

        $key = md5(self::TABLE.'getTagId'.$idStr);
        $v   = redis_api::get($key);
        if (!empty($v)) return $v;
        $item = ['fk_tag'];

        $res = $db->select(self::TABLE, $condition, $item);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        redis_api::set($key, $res, self::ExpiredTime);
        return $res;
    }
}

