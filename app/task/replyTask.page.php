<?php
/**
 * 学生提交作业
 * @author zhouyu
 * date: 2016/7/5
 */
class task_replyTask{

    //提交作业
    public function pageStudentReplyTask($inPath){
        //数组加true 对象不加
        $params = SJson::decode(utility_net::getPostData(), true);

        $ret = new stdclass;
        $ret->result =  new stdclass;
        if(empty($params['fk_task']) || empty($params['fk_user_student']) ){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $update = array(
            'desc'=>$params['desc'],
            'create_time'=>$params['create_time'],
            'status'=>$params['status'],
        );
        //提交作业
        $inster = task_db_taskStudentDao::replyTaskAdd($params,$update);
        if($inster){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->data = $inster;
            return $ret;
        }
    }


    //获取当前提交次数
    public function pageGetCommitNum($params){

        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->data =  new stdclass;
        if(empty($params['id']) ){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $commitNum = task_db_taskDao::getCommitNum($params['id']);
        $ret->result->code = 200;
        $ret->result->msg = "success";
        $ret->data=$commitNum;
        return $ret;


    }
    //学生提交作业后修改布作业表 t_task 提交次数
    public function pageUpdateCommitNum(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $withdrawId = $params['pk_task'];
        $data = array('student_count'=>$params['student_count']);
        $data = task_db_taskDao::UpdateCommitNum($withdrawId,$data);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->data =  new stdclass;
        $ret->result->code = 200;
        $ret->result->msg = "success";
        $ret->data=$data;
        return $ret;

    }

    //学生作业列表
    public function pagegetStudentTaskList(){

        $params = SJson::decode(utility_net::getPostData(), true);
        $data = task_db::getStudentTaskList($params);
        $ret = new stdclass;
        $ret->data = $data;
        return $ret;
    }
    //教师Id获取教师Name
    public function pagegetTeacherName(){
        $teacherId = SJson::decode(utility_net::getPostData());
        $data = task_db_userDao::getTeacherName($teacherId);
        $ret = new stdclass;
        $ret->data = $data->items;
        return $ret;
    }

    //作业详情 未批改  查看作业
    public function pagegetStudentTaskDetail(){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $pk_task_student = SJson::decode(utility_net::getPostData(),true);
        if(empty($pk_task_student['pk_task_student'])){
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $data = task_db_taskStudentDao::getStudentTaskDetail($pk_task_student);
        $ret->result->code = 200;
        $ret->result->msg = "success";
        $ret->data=$data;
        return $ret;

    }
}