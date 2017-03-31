<?php
class stat_statcourse{
	/*
	 * 增加一条统计信息
	 */
	public function pageAddSectionCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$course_id = $inPath[3];
		$data = array("course_id"=>$course_id);
		$stat_api = new stat_api;
		$retCourseApi = $stat_api->addCourseStatSectionCount($data);
		if($retCourseApi === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageAddClassCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$course_id = $inPath[3];
		$data = array("course_id"=>$course_id);
		$stat_api = new stat_api;
		$retCourseApi = $stat_api->addCourseStatClassCount($data);
		if($retCourseApi === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageCourseStatClassCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$course_id = $inPath[3];
		$data = array();
		$stat_api = new stat_api;
		$retget = $stat_api->setCourseStatClassCount($course_id,$data);
		if($retget === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail setNum";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageCourseStatSectionCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$course_id = $inPath[3];
		$data = array();
		$data["count"]="1";
		$stat_api = new stat_api;
		$retget = $stat_api->setCourseStatSectionCount($course_id,$data);
		if($retget === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail setNum";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagegetCourseStat($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$course_id = $inPath[3];
		$data = array();
		$stat_api = new stat_api;
		$retget = $stat_api->getCourseStat($course_id,$data);
		if(empty($retget)){
			echo "hh";
		}
		if($retget==false){
			echo "123";
		}else{
			return $retget;
		}
	}
	//获取用户课程的学习时间
	public function pageCourseTotal(){
		$ret = new stdclass;
		$ret->code = -1;
		$ret->msg= "参数错误";
		$ret->data= array();
		
		$params = SJson::decode(utility_net::getPostData());
		$uid = empty($params->uid)?0:intval($params->uid);
		$cidArr = empty($params->cidArr)?array():$params->cidArr;
        if(empty($uid) || empty($cidArr)){
			return $ret;
		}
		$cidArrs = array();
		foreach($cidArr as $cid){
			$cidArrs[] = $cid;
		}
        $result = stat_db::getUserCourseTotalTime($uid,$cidArrs);
		$ret->code = 0;
		$ret->msg = "获取成功";
		if($result->items){
			$ret->data = $result->items;
		}
        return $ret;
	}
}
