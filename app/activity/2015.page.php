<?php
class activity_2015{
	public function pageGetScore($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$db = new activity_db;
		$r=false;
		if(!empty($params->idcd)){
			$r = $db->getScoreByIDCard($params->idcd);
		}
		if(empty($t) && !empty($params->name) && !empty($params->numb)){
			$r = $db->getScoreByNameNumber($params->name, $params->numb);
		}
		$ret->data=$r;
		return $ret;
	}
	public function pageGetYCBScore($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$db = new activity_db;
		$r=false;
		if(empty($t) && !empty($params->name)){
			$r = $db->getYCBScoreByName($params->name);
		}
		$ret->data=$r;
		return $ret;
	}
}
