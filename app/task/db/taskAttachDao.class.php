<?php
class task_db_taskAttachDao{
    const dbName = 'db_course';
    const TABLE = 't_task_attach';

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
    public static function attachUpload($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }

    //t_attach 查询附件
    public static function getTaskDetailAttach($condition){
        $db = self::InitDB(self::dbName, 'query');
        return $db->select(self::TABLE, $condition);
    }
    //未发布 修改作业  修改附件
    public static function updatePublishTaskAttach($param){
        $db = self::InitDB(self::dbName);
        $thumbid = $param['con']['pk_attach'];
        $condition = "pk_attach = $thumbid";
        $res = $db->update(self::TABLE, $condition, $param['data']);
        return $res;
    }

    public static function delAttach($params){
        $db = self::InitDB(self::dbName);
        $up = 'status = -1';
        $res = $db->update(self::TABLE, $params, $up);
        return $res;
    }


}