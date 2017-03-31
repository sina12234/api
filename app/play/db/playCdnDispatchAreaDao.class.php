<?php
class play_db_playCdnDispatchAreaDao
{
    const dbName = 'db_live';
    const TABLE = 't_live_play_cdn_dispatch_area';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //地区列表
    public static function getAreaList($condition,$page='',$length='')
    {
        $db = self::InitDB(self::dbName, 'query');
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $condition = array('status'=>$condition['status']);
        return $db->select(self::TABLE, $condition);
    }

    //气度添加CDN
    public static function AddAreaCdn($data,$replace){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data,false,false,$replace);
    }

    //删除地区
    public static  function DelArea($condition){
        $db = self::InitDB(self::dbName);
        return $db->delete(self::TABLE, $condition);
    }
}