<?php
class activity_2014{
	public function pageScore($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		if(empty($params->uname)){
			$ret->result=array("code"=>-1);
			return $ret;
		}
		$db = new activity_db;
		if(is_numeric($params->uname)){
			$score = $db->getScore($params->uname);
		}else{
			$score = $db->getScoreByName($params->uname);
		}
		if(empty($score)){
			$ret->result=array("code"=>-2);
			return $ret;
		}
		$ret->data=$score;
		return $ret;
	}
}
