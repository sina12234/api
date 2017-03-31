<?php
class task_db_taskMappingTagStudentDao{
    const dbName = 'db_tag';
    const TABLE = 't_mapping_tag_task_student';

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
    public static function addMappingStudentTag($data){

        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }

    //通过t_task_student id 查询已批改 标签
    public static function replyTaskTag($condition){
        $db = self::InitDB(self::dbName, 'query');
        return $db->select(self::TABLE, $condition);
    }


}