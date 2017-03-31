<?php

/**
 * @author zhangtaifeng
 */
class task_db{

    public static function InitDB($dbname="db_course",$dbtype="main") {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);
        return $db;
    }


    //获取教师列表
    public static function taskList($param){


        $table=array("t_task");
        $db  = self::InitDB("db_course", "query");
        if($param['page'] && $param['pageNum']){
            $db->setPage($param['page']);
            $db->setLimit($param['pageNum']);
            $db->setCount(true);
        }
        $item = 't_task.*';
        $item.=',t_course_class.pk_class,t_course_class.name,t_course_class.user_total';
        $item.=',t_course.pk_course,t_course.title';
        $left=new stdclass;
        $left->t_course_class = "t_course_class.pk_class = t_task.fk_class";
        $left->t_course = "t_course.pk_course = t_task.fk_course";
        $status = $param['status'];
        $class = $param['classIds'];
        $status = $param['status'];
        //未发布
        if($param['status'] == 0){
            $condition =  "t_task.status = $status and fk_class in ($class) and t_task.status <> -1";
            //待批改
        }elseif($param['status'] == 1){
            $condition =  "t_task.mark_count < t_task.student_count and fk_class in ($class) and t_task.status = 1";
            //已批改
        }elseif($param['status'] == 2){
            $condition =  "t_task.mark_count = t_task.student_count and fk_class in ($class) and t_task.student_count <> 0 and t_task.status = 1";
        }elseif ($param['status'] == 3){
            //所有
            $condition ="t_task.status!=-1 and fk_class in ($class)";
        }
        $orderby = array('t_task.create_time'=>'desc');
        //select($table,$condition="",$item="",$groupby="",$orderby="",$leftjoin="")
        $data = $db->select($table,$condition,$item,"",$orderby,$left);
        return $data;
    }

    //获取学生作业列表
    public static  function getStudentTaskList($param){
        $table=array("t_course_user");
        $db  = self::InitDB("db_course", "query");
//        if($param['page'] && $param['pageNum']){
//            $db->setPage($param['page']);
//            $db->setLimit($param['pageNum']);
//            $db->setCount(true);
//            $page = $param['page'];
//            $pageNum = $param['pageNum'];
//        }

        $page = $param['page'];
        $pageNum = $param['pageNum'];
        $studentId = $param['studentId'];
        //echo $studentId;die;
        $sql = "SELECT t_task.*,a.fk_user_student,a.pk_task_student,a.status as student_status FROM t_task
LEFT JOIN (SELECT * FROM t_task_student WHERE fk_user_student=$studentId) AS a ON pk_task=a.fk_task
WHERE t_task.fk_class IN (SELECT fk_class FROM t_course_user WHERE fk_user=$studentId)";
        if($param['status'] == 0){
            $sql .=" and a.pk_task_student is null and t_task.status <> 0 and t_task.status <> -1";
            if($page=='null' && $pageNum=='null'){
                $sql .=" ORDER BY t_task.create_time DESC";
            }else{
                $sql .=" ORDER BY t_task.create_time DESC limit $page,$pageNum";
            }
        }elseif($param['status'] == 1){
            $sql .=" and a.pk_task_student is not null and a.status = 1";
            if($page=='null' && $pageNum=='null'){
                $sql .=" ORDER BY t_task.create_time DESC";
            }else{
                $sql .=" ORDER BY t_task.create_time DESC limit $page,$pageNum";
            }
        }elseif($param['status'] == 2){
            //已批改
            $sql .=" and a.pk_task_student is not null and a.status = 2";
            if($page=='null' && $pageNum=='null'){
                $sql .=" ORDER BY t_task.create_time DESC";
            }else{
                $sql .=" ORDER BY t_task.create_time DESC limit $page,$pageNum";
            }
        }else{
            $sql .=" and t_task.status <> 0 and t_task.status <> -1";
            if($page=='null' && $pageNum=='null'){
                $sql .=" ORDER BY t_task.create_time DESC";
            }else{
                $sql .=" ORDER BY t_task.create_time DESC limit $page,$pageNum";
            }
        }
        $data = $db->execute($sql);
        return $data;
    }

    //
    public static function getTaskListIsPrompt($params){
        $table=array("t_task");
        $db  = self::InitDB("db_course", "query");
        $item = 'SUM(t_task.student_count) as student_count_sum,SUM(t_task.mark_count) as mark_count_sum';
        $condition = "fk_user_teacher = $params ";
        $data = $db->select($table,$condition,$item,"");;
        return $data;

    }






}
