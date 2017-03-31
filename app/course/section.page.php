<?php
class course_section{
	public function pageList($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) && empty($params['sectionIdArr'])) {
			return $ret;
        }
		$sectionIds = !empty($params['sectionIdArr']) ? implode(',', $params['sectionIdArr']) : '';
		$course_id = (int)$inPath[3];
		$course_api = new course_api;
		$sectionlist = $course_api->getSectionList($course_id, $sectionIds);
		if($sectionlist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the section is not found!";
			return $ret;
		}else{
			return $sectionlist;
		}
	}
	public function pageGet($inPath){
		$ret = new stdclass;
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result =  new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "section id is not found!";
			return $ret;
		}
		$course_db = new course_db;
		$data = $course_db->getSection($inPath['3']);
		if(!empty($data)){
			$data['status']=course_status::name($data['status']);
			$ret->data=$data;
		}
		return $ret;
	}
	public function pageplanGroupSectionByCourseIds($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";

		$courseIdsArr = array();
		$params = SJson::decode(utility_net::getPostData());
		if(!empty($params->courseIdsArr)){
			foreach($params->courseIdsArr as $k=>$v){
				$courseIdsArr[] = $v;
			}
		}
		//$courseIdsArr = array("138","137","157","150");
		$course_api = new course_api;
		//$course_db = new course_db;
		//define("DEBUG",true);
		$listsection= $course_api->planGroupSectionByCourseIds($courseIdsArr);
		if($listsection === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listsection;
	}
	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$section_id = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		$section = array();
	/*	if(empty($params->name)){
			$ret->result->code = -3;
			$ret->result->msg = "the section name is empty!";
			return $ret;			
		}else{
*/
			$time = date("Y-m-d H:i:s");
			if(isset($params->name)){
				$section["name"] = $params->name;
			}
			$section["descript"] = empty($params->descript)? '请输入章节描述':$params->descript;
			$section["last_updated"] = $time;
		//	$section["status"] = $params->status;
			$section["status"] = empty($params->status)? '1':$params->status;
			if(isset($params->order_no)){
				$section["order_no"] = $params->order_no;
			}
/*		}*/
		$course_api = new course_api;
		$ret_section = $course_api->updatesection($section_id,$section);
		if($ret_section === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagecreate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The cid is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$cid = (int)$inPath[3];
		//在这里强制转换了下
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params["name"])){
			$ret->result->code = -3;
			$ret->result->msg = "the name is empty!";
			return $ret;	
		}else{
			$section = $params;
		}
		$course_api = new course_api;
		$ret_course = $course_api->addsection($cid,$section);
		//统计创建的section
		//{{{
		$stat_api = new stat_api;
		$data = array();
		$data["count"] = "1";
		$retget = $stat_api->setCourseStatSectionCount($cid,$data);
		//}}}
		if($ret_course === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
			$ret->result->data = $ret_course;
		}
		return $ret;
	}
	/*
	 *即使没有这个id也会删除 这个是sql的原因 应该再完善下
 	 *或者前端做AjAx判断
	 */
	public function pagedel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The sid is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		if (empty($inPath[4]) || !is_numeric($inPath[4])) {
			return $ret;
		}
		$sid = (int)$inPath[3];
		$cid = (int)$inPath[4];
		//在这里强制转换了下
		$course_api = new course_api;
		$course_db = new course_db;
		$course_info = $course_db->getCourse($cid);
		$plan_info = $course_db->getPlanTeacherByCidAndSid($cid,$sid);

		$ret_section = $course_api->delSection($sid,$cid);

		$stat_api = new stat_api;
		$data = array();
		$data["count"] = "-1";
		$retget = $stat_api->setCourseStatSectionCount($cid,$data);
		if($ret_section === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail del";
		}else{

			foreach($plan_info->items as $po){
				if($course_info['status'] == 3){
					stat_api::reduceTeacherStatCourseCompleteCountByClass($po['fk_user_plan'],$cid);
					stat_api::reduceTeacherStatOrgCourseCompleteCountByClass($po['fk_user_plan'],$course_info['fk_user'],$cid);
				}else{
					stat_api::reduceTeacherStatOrgCourseRemainCount($po['fk_user_plan'],$course_info['fk_user'],$cid);
					stat_api::reduceTeacherStatCourseRemainCount($po['fk_user_plan'],$cid);
				}
				if($course_info['admin_status'] == -2){
					stat_api::reduceTeacherStatCourseOffCountByClass($po['fk_user_plan'],$cid);
					stat_api::reduceTeacherStatOrgCourseOffCountByClass($po['fk_user_plan'],$course_info['fk_user'],$cid);
				}elseif($course_info['admin_status'] == 1){
					stat_api::reduceTeacherStatCourseOnCountByClass($po['fk_user_plan'],$cid);
					stat_api::reduceTeacherStatOrgCourseOnCountByClass($po['fk_user_plan'],$course_info['fk_user'],$cid);
				}
			}

			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
}
