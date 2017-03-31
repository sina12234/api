<?php
class task_db_userDao{
    const dbName = 'db_user';
    const TABLE = 't_user';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    //查询
    public static function testSelect()
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "status = 1";
        return $db->select(self::TABLE, $condition);
    }

    //插入
    public static function imgUpload($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }

    //教师Id获取教师名字
    public static function getTeacherName($teacherId){
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_user = $teacherId";
        return $db->select(self::TABLE, $condition);

    }





}