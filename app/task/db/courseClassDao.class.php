<?php
class task_db_courseClassDao{
    const dbName = 'db_course';
    const TABLE = 't_course_class';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //查询
    public static function getClass($condition,$items)
    {
        $db = self::InitDB(self::dbName, 'query');
        return $db->select(self::TABLE, $condition,$items);
    }

    //插入
    public static function attachUpload($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }

    //查询班级信息
    public static function getClassInfos($params){
        $db = self::InitDB(self::dbName);
        return $db->select(self::TABLE ,$params);
    }


}