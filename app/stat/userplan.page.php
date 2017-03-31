<?php
class stat_userplan{
	
	
	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	
	public function pageGetUserPlanStatCountByPid($inPath){
		$uid = !empty($inPath[3])?$inPath[3]:'';
		$pidArr = SJson::decode(utility_net::getPostData(),true);
		if(empty($pidArr) || empty($uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = stat_db::getUserPlanStatCountByPid($uid,$pidArr);
		if(!empty($res->items)){
			return $this->setResult($res->items[0]['count']);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageGetUserPlanStatCountByUid($inPath){
		$uid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($uid) || !is_numeric($uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = stat_db::getUserPlanStatCountByUid($uid);
		if(!empty($res->items)){
			return $this->setResult($res->items[0]['count']);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetUserPlanStatByPidArr($inPath){
		$uid = !empty($inPath[3])?$inPath[3]:'';
		$pidArr = SJson::decode(utility_net::getPostData(),true);
		if(empty($pidArr) || empty($uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = stat_db::getUserPlanStatByPidArr($uid,$pidArr);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetUserPlanStatByPid($inPath){
		$pid = !empty($inPath[3])?$inPath[3]:'';
		$page = isset($inPath[4])?$inPath[4]:1;
		$length = isset($inPath[5])?$inPath[5]:50;
		if(empty($pid)){
			return $this->setResult('',-1,'pid is error');
		}
		$res = stat_db::getUserPlanStatByPid($pid,$page, $length);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageAddPlanPhraseLog(){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['data'])){
			return $this->setResult('',-1,'params is error');	
		}
		
		$time = date("Y-m-d H:i:s");
		$values = '';
		foreach($params['data'] as $val){
			$planId = !empty($val['planId']) ? $val['planId'] : 0;
			$userId = !empty($val['userId']) ? $val['userId'] : 0;
			$answer = !empty($val['answer']) ? $val['answer'] : '';
			$questId= !empty($val['questId']) ? $val['questId'] : 0;
			$status = !empty($val['answerStatus']) ? $val['answerStatus'] : 0;
			
			$values.=" (NULL,{$planId},{$questId},{$userId},\"{$answer}\",{$status},\"{$time}\",\"{$time}\"),";
		}
		$res = stat_db::addPlanPhraseLog($values);
		return $res;
	}

	public function pageGetPlanPhraseLogByPid(){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->pid)){
			return $this->setResult('',-1,'params is error');
		}
		$res = stat_db::getPlanPhraseLogByPid($params);
		return $res;
	}
}
