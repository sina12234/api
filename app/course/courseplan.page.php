<?php
class course_courseplan{
    public function pageGetCoursePlanByPid($inPath){
        $ret = new stdclass;
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->result=new stdclass;
            $ret->result->code = -1;
            $ret->result->msg= "plan_id is not set!";
            return $ret;
        }
        $planId = $inPath[3];
        $ret=new stdClass;
        $ret->code = 0;
        $ret->msg  = 'success';
        $ret->data = '';
        if(empty($inPath[3])){
            $ret->result->code = -2;
            $ret->result->msg= "plan_id not set!";
            return $ret;
        }
        $planId = (int)$inPath[3];
        $coursePlan = course_db_coursePlanDao::getPlanById($planId);

        if(!empty($coursePlan)){
            $user = user_db_userDao::row($coursePlan["fk_user_plan"]);
            $class = course_db_courseClassDao::getClassInfo($coursePlan["fk_class"]);
            $course = course_db::getCourse($coursePlan["fk_course"]);
            $userCount = course_db_courseUserDao::getClassRegUserTotalNum($coursePlan["fk_class"]);
            $coursePlan["teacher_name"] =  empty($user["real_name"])?$user["name"]:$user["real_name"];
            $coursePlan["class_name"] =  $class["name"];
            $coursePlan["course_name"] =  $course["title"];
            $coursePlan["user_count"] =  $userCount;
            $ret->data = $coursePlan;
        }
        return $ret;
    }
    //取讲课老师id
    public function pageGetTeacherByCourseId(){
        $ret=new stdClass;
        $ret->code = 0;
        $ret->msg  = 'success';
        $ret->data = "";
        $params = SJson::decode(utility_net::getPostData());
        $courseId = is_array($params->courseId)?$params->courseId:intval($params->courseId);
        $result = course_db_coursePlanDao::getTeacherByCourseId($courseId);
        if($result->items){
            $ret->data = $result->items;
        }
        return $ret;
    }

    //获取plan信息
    public function pagegetCoursePlan(){
        $ret=new stdClass;
        $ret->code = 0;
        $ret->msg  = 'success';
        $ret->data = "";
        $params = SJson::decode(utility_net::getPostData());
        $courseId = is_array($params->courseId)?$params->courseId:intval($params->courseId);
        $result = course_db_coursePlanDao::getCoursePlan($courseId);
        if($result->items){
            $ret->data = $result->items;
        }
        return $ret;
    }

}
?>