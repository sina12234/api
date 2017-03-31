<?php
class task_db_taskTagDao{
    const dbName = 'db_tag';
    const TABLE = 't_tag';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    //查询
    public static function tagSelect($condition)
    {
        $db = self::InitDB(self::dbName, 'query');
        return $db->select(self::TABLE, $condition);
    }

    //插入
    public static function insertTag($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }

    //删除标签
    public static function DelTag($pk_tag){
        $db = self::InitDB(self::dbName);
        $con = "pk_tag = $pk_tag";
        return $db->delete(self::TABLE, $con);
    }


}