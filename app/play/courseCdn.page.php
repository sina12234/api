<?php
/**
 *
 * @author zhouyu
 * date:
 */
class play_courseCdn{

    //cdn列表
    public function pagegetCdnList(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $cdn = play_db_playCdnDispatchCdnDao::getCdnList($params);
        $ret->data = $cdn->items;
        return $ret;
    }
    //课程列表
    public function pagegetCourseList($inPath)
    {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $page = $params['page'];
        $length = $params['pageSize'];
        $courseList = play_db_playCdnDispatchCourseDao::getCourseList($params,$page,$length);
        $ret->data = $courseList;
        return $ret;
    }

    //用户列表
    public function pagegetUserList(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $page = $params['page'];
        $length = $params['pageSize'];
        $courseList = play_db_playCdnDispatchUserDao::getUserList($params,$page,$length);
        $ret->data = $courseList;
        return $ret;
    }

    //排课列表
    public function pagegetPlanList(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $page = $params['page'];
        $length = $params['pageSize'];
        $planList = play_db_playCdnDispatchPlanDao::getPlanList($params,$page,$length);
        $ret->data = $planList;
        return $ret;
    }

    //cdn总数列表
    public function pagegetTotalList(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $page = $params['page'];
        $length = $params['pageSize'];
        $totalList = play_db_playCdnDispatchTotalDao::getTotalList($params,$page,$length);
        $ret->data = $totalList;
        return $ret;
    }

    //地区列表
    public function pagegetAreaList(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $page = $params['page'];
        $length = $params['pageSize'];
        $AreaList = play_db_playCdnDispatchAreaDao::getAreaList($params,$page,$length);
        $ret->data = $AreaList;
        return $ret;
    }


    //课程添加CDN
    public function pageAddCourseCdn(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);

        $replace = array('fk_cdn'=>$params['fk_cdn'],'cdn_type'=>$params['cdn_type']);
        $addCourse = play_db_playCdnDispatchCourseDao::AddCourseCdn($params,$replace);
         if($addCourse === false){
            $ret->result->code = -2;
            $ret->result->msg = "error";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        return $ret;
    }
    //添加用户CDN
    public function pageAddUserCdn(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $replace = array('fk_cdn'=>$params['fk_cdn'],'cdn_type'=>$params['cdn_type']);
        $addUser = play_db_playCdnDispatchUserDao::AddUserCdn($params,$replace);
        if($addUser === false){
            $ret->result->code = -2;
            $ret->result->msg = "error";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        return $ret;
    }

    //添加排课 cdn
    public function pageAddPlanCdn(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $replace = array('fk_cdn'=>$params['fk_cdn'],'cdn_type'=>$params['cdn_type']);
        $addPlan = play_db_playCdnDispatchPlanDao::AddPlanCdn($params,$replace);
        if($addPlan === false){
            $ret->result->code = -2;
            $ret->result->msg = "error";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        return $ret;
    }

    //添加总数cdn
    public function pageAddTotalCdn(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $replace = array('fk_cdn'=>$params['fk_cdn'],'user_total'=>$params['user_total']);
        $totalPlan = play_db_playCdnDispatchTotalDao::AddTotalCdn($params,$replace);
        if($totalPlan=== false){
            $ret->result->code = -2;
            $ret->result->msg = "error";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        $ret->data = $totalPlan;
        return $ret;
    }

    //添加地区cdn
    public function pageAddAreaCdn(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $replace = array('fk_cdn'=>$params['fk_cdn'],'area_name'=>$params['area_name'],'op_name'=>$params['op_name'],'cdn_type'=>$params['cdn_type']);
        $areaPlan = play_db_playCdnDispatchAreaDao::AddAreaCdn($params,$replace);
        if($areaPlan=== false){
            $ret->result->code = -2;
            $ret->result->msg = "error";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        $ret->data = $areaPlan;
        return $ret;
    }

    //删除课程
    public function pageDelCourse(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $delCourse = play_db_playCdnDispatchCourseDao::DelCourse($params);
        return $delCourse;
    }

    //删除用户
    public  function pageDelUser(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $delUser = play_db_playCdnDispatchUserDao::DelUser($params);
        return $delUser;
    }

    //删除总数
    public function pageDelTotal(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $delTotal = play_db_playCdnDispatchTotalDao::DelTotal($params);
        return $delTotal;
    }

    //删除排课
    public function pageDelPlan(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $delPlan = play_db_playCdnDispatchPlanDao::DelPlan($params);
        return $delPlan;
    }

    //删除地区
    public function pageDelArea(){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $params = SJson::decode(utility_net::getPostData(), true);
        $delArea = play_db_playCdnDispatchAreaDao::DelArea($params);
        return $delArea;
    }




}
