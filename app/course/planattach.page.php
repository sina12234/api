<?php
class course_planattach{
	public function pageList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The planid is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$planId= (int)$inPath[3];
		$course_api = new course_api;
		$cond = array();
		$cond["plan_id"] = $planId;
		$listPlanAttach = $course_api->listPlanAttach($cond,1,100);
		if($listPlanAttach === false){
			$ret->result->code = -2;
			$ret->result->msg = "the section is not found!";
			return $ret;
		}else{
			return $listPlanAttach;
		}
	}
	public function pageGet($inPath){
		$ret = new stdclass;
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result =  new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "planAttid is not found!";
			return $ret;
		}
		$planAttId = (int)$inPath[3];
		$course_db = new course_db;
		$data = $course_db->getPlanAttach($planAttId);
		if(!empty($data)){
			$ret->data=$data;
		}else{
			$ret->result =  new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "planAttid is not found!";
			return $ret;
		}
		return $ret;
	}
	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$planAttId = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData(),true);
		$course_api = new course_api;
		$retPlanAtt = $course_api->updatePlanAttach($planAttId,$params);
		if($retPlanAtt === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	/*这里的planId 其实是classId*/
	public function pageadd($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The planid is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$planId = $inPath[3];
		$params =array();
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params["attach"])){
			$ret->result->code = -3;
			$ret->result->msg = "the attach is empty!";
			return $ret;	
		}
		$course_api = new course_api;
		$retAttach = $course_api->addplanattach($planId,$params);
		if($retAttach === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	/*
	 */
	public function pagedel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The planAttId is not found!";
	/*	if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		if (empty($inPath[4]) || !is_numeric($inPath[4])) {
			return $ret;
		}
	*/
	//	$planAttId = (int)$inPath[3];
	//	$planId = (int)$inPath[4];
		$params = SJson::decode(utility_net::getPostData());
		$course_api = new course_api;
		$planAttIdtmp = $params->planAttIds;
	//	$planAttIdtmp = array("1","2","3");
		$planAttIds = implode(",",$planAttIdtmp);
		$course_api = new course_api;
		$retPlanAtt = $course_api->delPlanAttach($planAttIds);
/* 判断权限的删除
		$retPlanAttplanid = $course_api->getPlanAttach($planAttId);
		if(empty($retPlanAttplanid)){
			return $ret;
		}
		if($retPlanAttplanid["plan_id"] == $planId){
			$retPlanAtt = $course_api->delPlanAttach($planAttId);
		}else{ 
			$retPlanAtt = false;
		}
*/
		if($retPlanAtt === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail del";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}

	public function pageGetPlanAttachByPidArr($inPath){
		$pid_arr = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;	
		$ret->code = -1;
		$ret->msg  = '';
		$ret->data = '';
		if(empty($pid_arr) && !is_array($pid_arr)){
			$ret->msg = 'params is error';	
			return $ret;
		}	
		$course_db = new course_db;
		$attach_ret = $course_db->getPlanAttachByPidArr($pid_arr);

		if(!empty($attach_ret) && !empty($attach_ret->items)){
			$ret->code = 0;
			$ret->msg  = 'success';
			$ret->data = $attach_ret->items;
			return $ret;
		}else{
			$ret->code = -2;
			$ret->msg  = ' get data failed';
			return $ret;
		}
	}
	public function pageUpdateDownloadCount($inPath){
		$ret = new stdclass;
		$ret->code = -1;
		$ret->msg  = '';
		$ret->data = '';
		if(empty($inPath[3])){
			$ret->msg = 'planAttachId is error';
			return $ret;
		}
		$planAttachId = $inPath[3];
		$info = course_db_planAttachDao::updateDownloadCount($planAttachId);
		if(!empty($info)){
			$ret->code=0;
			$ret->msg = 'ok';
			$ret->data = $info;
			return $ret;
		}
		$ret->msg  = 'updateDownloadCount error';
		return $ret;

	}




}
