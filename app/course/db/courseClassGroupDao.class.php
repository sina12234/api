<?php

class course_db_courseClassGroupDao
{
    const dbName = 'db_course';

    const TABLE = 't_course_class_group';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function listGroup($redisTime,$cond, $page=1, $length=20, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        
        $condition_str = serialize($cond);  
        $key = md5("course_db.listGroup.".$condition_str);
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
    
    
    public static function rowGroup($cond, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();
        
        $res = $db->selectOne(self::TABLE, $cond, $item, $groupBy, $orderBy);

        return $res;
    }
    
    public static function addGroup($data)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data);
    }
    
    public static function updateGroup($condition,$data)
    {
        $db = self::InitDB(self::dbName);

        return $db->update(self::TABLE, $condition, $data);
    }
    
    public static function delGroup($condition)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->delete(self::TABLE, $condition);
        
        return $res;
    }
    
    public static function queryGroup($sql)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->execute($sql);
        
        return $res;
    }
    

    public static function listGroupAndUser($redisTime,$cond, $page=1, $length=20, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        
        $condition_str = serialize($cond);  
        $key = md5("course_db.listGroupAndUser.".$condition_str);
        if ($redisTime){      
            $res   = redis_api::get($key);
            if ($res) {  return $res; }
        }
        $item = array(
            't_course_class_group.pk_group','t_course_class_group.fk_course',' t_course_class_group.fk_class','t_course_class_group.group_teacher_id',' t_course_class_group.group_name','t_course_class_group.user_count','t_course_class_group.status'
            ,'t_course_user_class_group.fk_user'
         );
        $left  = new stdclass();
        $left->t_course_user_class_group  = "t_course_user_class_group.fk_group=t_course_class_group.pk_group";
        $res = $db->select(self::TABLE, $cond, $item, $groupBy, $orderBy, $left);
        if ($redisTime){
            if(!empty($res)){ redis_api::set($key, $res, $redisTime); }
        } else {
            redis_api::del($key);
        }
        return $res;
    }
    
}
