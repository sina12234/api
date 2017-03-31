<?php
class task_db_taskTagBelongGroupDao{
    const dbName = 'db_tag';
    const TABLE = 't_tag_belong_group';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    //查询
    public static function tagSelect()
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "status = 0";
        return $db->select(self::TABLE, $condition);
    }

    //插入
    public static function addTagBelong($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }


}