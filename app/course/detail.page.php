<?php
class course_detail{
	public function __construct($inPath){
		return;
	}
	public function  pageSearchCourseAjax($inPath){
	    $params = SJson::decode(utility_net::getPostData());
		if(!empty($params->keyword)){
			$ret = course_db::SearchCourseAjax($params->keyword);
		}
		return $ret;
	}
	public function  pagemgrSearchCourse($inPath){
	    $params = SJson::decode(utility_net::getPostData());
		if(!empty($params->keyword)){
			$ret = course_db::mgrSearchCourse($params->keyword);
		}
		return $ret;
	}
	
}
