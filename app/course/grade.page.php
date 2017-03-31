<?php
class course_grade{
	public function pageList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$course_api = new course_api;
		$gradelist = $course_api->getgradelist();
		if($gradelist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the cate is not found!";
			return $ret;
		}
   //     print_r($gradelist);
		return $gradelist;
	}
}
