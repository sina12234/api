<?php
/**
 * 教师批改作业
 * @author zhouyu
 * date: 2016/7/5
 */
class task_correctTask{

    //教师批改作业
    public function pageTeacherCorrectTask($inPath){

        //数组加true 对象不加
        $params = SJson::decode(utility_net::getPostData(), true);

        $ret = new stdclass;
        $ret->result =  new stdclass;
        //        if(empty($params['fk_task']) || empty($params['fk_user_student']) ||empty($params['desc'] ) ){
        //            $ret->result->code = -2;
        //            $ret->result->msg = "参数错误";
        //            return $ret;
        //        }
        //批改作业
        $inster = task_db_taskStudentReplyDao::teacherReplyTask($params);
        if($inster){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->data = $inster;
            return $ret;
        }
    }

    //修改 批改次数
    public function pageupdateReplyCount(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $withdrawId = $params['pk_task'];
        $data = array('mark_count'=>$params['mark_count'],'status'=>$params['status']);
        $data = task_db_taskDao::UpdateReplyNum($withdrawId,$data);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->data =  new stdclass;
        if($data){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->data=$data;
            return $ret;
        }
    }

    // 批改作业展示 taskid 查询 学生所有作业 getStudentAllTaskAlealy
    public function pagegetStudentAllTask(){
        //数组加true 对象不加
        $param = SJson::decode(utility_net::getPostData(),true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        //批改作业
        $sel = task_db_taskStudentDao::getStudentAllTask($param);
        $ret->result->code = 200;
        $ret->result->msg = "success";
        $ret->data = $sel;
        return $ret;

    }

    public function pageupdateTaskStudentStatus(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $pk_task_student = $params['pk_task_student'];
        $data = array('status'=>$params['status']);
        $data = task_db_taskStudentDao::updateTaskStudentStatus($pk_task_student,$data);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->data =  new stdclass;
        $ret->result->code = 200;
        $ret->data=$data;
        return $ret;

    }


    //删除批改作业
    public function pagegetdelReplyTask(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskStudentReplyDao::getdelReplyTask($params);
        $ret->result->data = $dataList;
        return $ret;
    }


}