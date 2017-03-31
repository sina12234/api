<?php
class task_db_courseDao{
    const dbName = 'db_course';
    const TABLE = 't_course';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    //查询
    public static function getClassName($condition,$items)
    {
        $db = self::InitDB(self::dbName, 'query');
        $orderby = "create_time desc";
        return $db->select(self::TABLE, $condition,$items,'',$orderby);
    }

    //插入
    public static function attachUpload($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }


}