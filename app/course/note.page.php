<?php
/**
 * 新增加班级,章节改变课程状态
 */
class course_note{

    //添加笔记
    public  function pagenoteAdd()
    {
        $params = SJson::decode(utility_net::getPostData(),true);
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -201;
        $ret->result->msg  = "";
        $ret->result->data  = "";
        if (empty($params['fk_plan'])) {
            $ret->result->code = -202;
            $ret->result->msg  = "fk_plan is empty";
            return $ret;exit;
        }
        if (empty($params['play_time_tmp'])) {
            $ret->result->code = -203;
            $ret->result->msg  = "play_time_tmp is empty";
            return $ret;exit;
        }
        if (empty($params['class_id'])) {
            $ret->result->code = -204;
            $ret->result->msg  = "class_id is empty";
            return $ret;exit;
        }

        if (empty($params['course_id'])) {
            $ret->result->code = -205;
            $ret->result->msg  = "course_id is empty";
            return $ret;exit;
        }
        $noteAdd = course_db_noteDao::noteAdd($params);
        $ret->result->code = 200;
        $ret->result->msg  = "success";
        $ret->result->data = $noteAdd;
        return $ret;


    }
    //查询笔记总数
    public function pagenoteCount(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $noteAdd = course_db_noteDao::noteCount($params);
        return json_encode($noteAdd);
    }

    //查询报名信息
    public function pagegetCourseUser(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $getCourseUser = course_db_courseUserDao::getCourseUser($params);
        return json_encode($getCourseUser);
    }

    //删除笔记
    public function pageDelNote(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -201;
        $ret->result->msg  = "";
        $ret->result->data  = "";
        $params = SJson::decode(utility_net::getPostData(),true);
        if (empty($params['id'])) {
            $ret->result->code = -202;
            $ret->result->msg  = "note_id is empty";
            return $ret;exit;
        }
        if (empty($params['fk_user'])) {
            $ret->result->code = -203;
            $ret->result->msg  = "fk_user is empty";
            return $ret;exit;
        }
        $DelNote = course_db_noteDao::DelNote($params);
        if($DelNote){
            $ret->result->code = 200;
            $ret->result->msg  = "success";
            $ret->result->data = $DelNote;
            return $ret;
        }else{
            $ret->result->code = -204;
            $ret->result->msg  = "error";
            $ret->result->data = $DelNote;
            return $ret;
        }

    }



    //编辑笔记
    public function pageUpdateNote(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -201;
        $ret->result->msg  = "";
        $ret->result->data  = "";
        $params = SJson::decode(utility_net::getPostData(),true);

        if (empty($params['id'])) {
            $ret->result->code = -202;
            $ret->result->msg  = "note_id is empty";
            return $ret;exit;
        }
        if (empty($params['fk_user'])) {
            $ret->result->code = -203;
            $ret->result->msg  = "fk_user is empty";
            return $ret;exit;
        }
        if (empty($params['content'])) {
            $ret->result->code = -204;
            $ret->result->msg  = "content is empty";
            return $ret;exit;
        }

        $UpdateNote = course_db_noteDao::UpdateNote($params);
        if($UpdateNote){
            $ret->result->code = 200;
            $ret->result->msg  = "success";
            $ret->result->data = $UpdateNote;
            return $ret;
        }else{
            $ret->result->code = -204;
            $ret->result->msg  = "error";
            $ret->result->data = $UpdateNote;
            return $ret;
        }
    }

    //笔记列表
    public function pagenoteList(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -201;
        $ret->result->msg  = "";
        $ret->result->data  = "";
        $params = SJson::decode(utility_net::getPostData(),true);
        $noteList = course_db_noteDao::noteList($params);
        print_r(json_encode($noteList));die;

//        if (empty($params['id'])) {
//            $ret->result->code = -202;
//            $ret->result->msg  = "note_id is empty";
//            return $ret;exit;
//        }
    }
}
