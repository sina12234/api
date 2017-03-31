<?php
class play_db_playCdnDispatchCdnDao
{
    const dbName = 'db_live';
    const TABLE = 't_live_play_cdn';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //cdn列表
    public static function getCdnList($condition,$page='',$length='')
    {
        $db = self::InitDB(self::dbName, 'query');
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        return $db->select(self::TABLE, $condition);
    }
}