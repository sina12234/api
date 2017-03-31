<?php
class task_db_taskStudentReplyDao{
    const dbName = 'db_course';
    const TABLE = 't_task_student_reply';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //教师修改作业
    public static function teacherReplyTask($data){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data);
    }
    //已经批改作业列表
    public static function getStudentAllTaskAlealy($param){
        $db = self::InitDB(self::dbName, 'query');
        $condition = $param;
        // $orderby = array('create_time'=>'desc');
        return $db->select(self::TABLE, $condition);
    }
    //删除批改作业
    public static function getdelReplyTask($params){
        $db = self::InitDB(self::dbName);
        $pk_task = $params['fk_task'];
        $condition = "fk_task = $pk_task";
        $params = "status = -1";
        $res = $db->update(self::TABLE, $condition, $params);
        return $res;
    }

}