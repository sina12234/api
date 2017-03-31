<?php
class task_db_taskThumbDao{
    const dbName = 'db_course';
    const TABLE = 't_task_thumb';

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

    //pk_task 查询图片
    public static function getTaskDetailThumb($condition){
        $db = self::InitDB(self::dbName, 'query');
        //$condition = "status = 1";
        return $db->select(self::TABLE, $condition);
    }
    //修改图片
    public static function updatePublishTaskImg($param){
        $db = self::InitDB(self::dbName);
        $thumbid = $param['con']['pk_thumb'];
        $condition = "pk_thumb = $thumbid";
        $res = $db->update(self::TABLE, $condition, $param['data']);
        return $res;
    }

    //删除图片
    public static function delImage($params){
        $db = self::InitDB(self::dbName);
        $up = 'status = -1';
        $res = $db->update(self::TABLE, $params, $up);
        return $res;
    }



}