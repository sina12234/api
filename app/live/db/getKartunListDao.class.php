<?php

class live_db_getKartunListDao
{
    const dbName = 'db_log';
    const TABLE = 't_play_extra_log';

    public static function InitDB($dbName = self::dbName, $dbType = 'main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //MGR卡顿列表查询
    public function getKartunList($param){
        $db = self::InitDB(self::dbName, 'query');
        $page = $param['page'];
        $length = $param['pageSize'];
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $orderby = array('log_time'=>'desc');
        return $db->select(self::TABLE,'','','',$orderby);
    }
}