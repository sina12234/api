<?php
class task_db_taskDao{
    const dbName = 'db_course';
    const TABLE = 't_task';

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
        //$condition = "status = 1";
        $condition = array("status" => "1");
        return $db->select(self::TABLE, $condition);
    }

    //插入
    public static function testInsert($data){

        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }
    //教师发布作业 t_task
    public static function teacherPublishTask($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }

    //sel 分页
    public static function selss($page = 1, $length = -1){
        $db = self::InitDB(self::dbName, 'query');
        $condition = "status = 1";
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        return $db->select(self::TABLE, $condition);
    }

    //学生提交作业后修改布作业表 t_task 提交次数
    public static function UpdateCommitNum($withdrawId,$data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_task = $withdrawId";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
    //教师批改作业 修改批改次数
    public static function UpdateReplyNum($withdrawId,$data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_task = $withdrawId";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }

    //获取当前提交作业次数
    public static function getCommitNum($id){
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_task = $id";
        return $db->selectOne(self::TABLE, $condition);
    }

    //待批改 查看作业
    public static function getTaskDetail($param){
        $db = self::InitDB(self::dbName, 'query');
        $condition = $param;
        return $db->select(self::TABLE, $condition);
    }

    //未发布修改作业
    public static function updatePublishTask($param){
        $db = self::InitDB(self::dbName);
        $pk_task = $param['pk_task'];
        $condition = "pk_task = $pk_task";
        $res = $db->update(self::TABLE, $condition, $param);
        return $res;
    }

    //删除作业 作业表
    public static  function  getdelTask($params){
        $db = self::InitDB(self::dbName);
        $pk_task = $params['pk_task'];
        $condition = "pk_task = $pk_task";
        $params = "status = -1";
        $res = $db->update(self::TABLE, $condition, $params);
        return $res;
    }




}