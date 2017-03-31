<?php
class task_db_taskMappingTagDao{
    const dbName = 'db_tag';
    const TABLE = 't_mapping_tag_task';

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
    public static function addMappingTag($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data,$isreplace=true);
    }

    //通过TASKID 查询 tagid
    public static function getTaskDetailTag($param){
        $db = self::InitDB(self::dbName, 'query');
        $condition = $param;
        return $db->select(self::TABLE, $condition);
    }


}