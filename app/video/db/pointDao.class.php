<?php

class video_db_pointDao
{
    const dbName = 'db_video';

    const TABLE = 't_video_point_teacher';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function listPoint($redisTime,$cond, $page=1, $length=20, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        
        $condition_str = serialize($cond);  
        $key = md5("video_db.listPoint.".$condition_str);
        if ($redisTime){      
            $res   = redis_api::get($key);
            if ($res) {  return $res; }
        }
        
        $res = $db->select(self::TABLE, $cond, $item, $groupBy, $orderBy);
        if ($redisTime){
            if(!empty($res)){ redis_api::set($key, $res, $redisTime); }
        } else {
            redis_api::del($key);
        }
        return $res;
    }
    
    
    public static function rowPoint($cond, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();
        
        $res = $db->selectOne(self::TABLE, $cond, $item, $groupBy, $orderBy);

        return $res;
    }
    
    public static function addPoint($data,$update)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data,false,false,$update);
    }
    
    public static function updatePoint($condition,$data)
    {
        $db = self::InitDB(self::dbName);

        return $db->update(self::TABLE, $condition, $data);
    }
    
    public static function delPoint($condition)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->delete(self::TABLE, $condition);
        
        return $res;
    }
}
