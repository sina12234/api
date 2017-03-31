<?php
class task_db_taskStudentDao{
    const dbName = 'db_course';
    const TABLE = 't_task_student';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //学生提交作业
    public static function replyTaskAdd($data,$update=array()){
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $data,false,false,$update);
    }
    //学生查看作业 未批改
    public static function getStudentTaskDetail($condition){
        $db = self::InitDB(self::dbName, 'query');
        //$condition = "pk_task_student = $pk_task_student";
        return $db->select(self::TABLE, $condition);
    }

    //获取学生题目列表
    public static function getStudentAllTask($param){
        $db = self::InitDB(self::dbName, 'query');
        if(!empty($param['page']) && !empty($param['pageSize'])){
            $db->setPage($param['page']);
            $db->setLimit($param['pageSize']);
            $db->setCount(true);
        }
        $fk_task = $param['fk_task'];
        if(!empty($param['status']) && !empty($param['fk_user_student'])){
            $status = $param['status'];
            $fk_user_student = $param['fk_user_student'];
            $condition = "fk_task = $fk_task AND status=$status and fk_user_student = $fk_user_student";
        }else{
            $condition = "fk_task = $fk_task AND status!= -1";
        }

        $orderby = array('create_time'=>'asc');
        return $db->select(self::TABLE, $condition,'','',$orderby);
    }

    public static function selStudentInfo($param){
        $db = self::InitDB(self::dbName, 'query');
        $fk_task = $param['fk_task'];
        $condition = "fk_task = $fk_task AND status=1";
        return $db->select(self::TABLE, $condition,'','');
    }

    //批改作业修改 status
    public static function updateTaskStudentStatus($pk_task_student,$data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_task_student = $pk_task_student";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }

    //删除学提交作业
    public static function getdelCommitTask($params){
        $db = self::InitDB(self::dbName);
        $pk_task = $params['fk_task'];
        $condition = "fk_task = $pk_task";
        $params = "status = -1";
        $res = $db->update(self::TABLE, $condition, $params);
        return $res;
    }


    public static function taskStudent($condition){
        $db = self::InitDB(self::dbName, 'query');
        return $db->select(self::TABLE, $condition);
    }
}