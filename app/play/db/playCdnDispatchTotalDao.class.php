<?php
class play_db_playCdnDispatchTotalDao
{
    const dbName = 'db_live';
    const TABLE = 't_live_play_cdn_dispatch_total';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //课程列表
    public static function getTotalList($condition,$page='',$length='')
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

    //用户总数添加CDN
    public static function AddTotalCdn($data,$replace){
        $db = self::InitDB(self::dbName);
        return  $db->insert(self::TABLE, $data,false,false,$replace);
    }

    //删除总数
    public static function DelTotal($condition){
        $db = self::InitDB(self::dbName);
        return  $db->delete(self::TABLE, $condition);
    }
}