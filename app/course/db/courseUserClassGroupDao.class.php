<?php

class course_db_courseUserClassGroupDao
{
    const dbName = 'db_course';

    const TABLE = 't_course_user_class_group';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function listUserClassGroup($redisTime,$cond, $page=1, $length=20, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        
        $condition_str = serialize($cond);  
        $key = md5("course_db.listUserClassGroup.".$condition_str);
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
    
    
    public static function rowUserClassGroup($cond, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();
        
        $res = $db->selectOne(self::TABLE, $cond, $item, $groupBy, $orderBy);

        return $res;
    }
    
    public static function addUserClassGroup($data)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data);
    }
    
    public static function updateUserClassGroup($condition,$data)
    {
        $db = self::InitDB(self::dbName);

        return $db->update(self::TABLE, $condition, $data);
    }
    
    public static function delUserClassGroup($condition)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->delete(self::TABLE, $condition);
        
        return $res;
    }
}
