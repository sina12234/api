<?php
class user_feedback {
	
	public function setResult($data='',$code=0,$msg='success'){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->data = $data;
		$ret->result->code = $code;
		$ret->result->msg  = $msg;
		return $ret;

	}

	public function pageAddUserFeedback($inPath){
		$user_db = new user_db();
		$params=SJson::decode(utility_net::getPostData());
		if( empty($params) ){
			return $this->setResult('',-1,'params is error');	
		}
		$add_ret = $user_db->addUserFeedback($params);
		if(empty($add_ret)){
			return $this->setResult('',-2, 'add failed data');
		}
		return $this->setResult($add_ret);
	}
	
	public function pageSetFeedback($inPath)
	{
		$user_db = new user_db();
		$params=SJson::decode(utility_net::getPostData());
		if( empty($params) ){
			return $this->setResult('',-1,'params is error');	
		}
		$set_ret = $user_db->SetFeedback($params);
		return $set_ret;
	}

	public function pageGetUserFeedbackByUid($inPath){

		if(empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $this->setResult('',-1, 'invaild parameter');
		}
		$uid = $inPath[3];
		$user_db = new user_db();
		$data = $user_db->getUserFeedbackByUid($uid);
		if(!$data->items){
			return $this->setResult('',-2, 'the data is not found!');
		}
		return $this->setResult($data);

	}
	
	public function pageGetUserFeedBackList($inPath)
	{
		$page   = !empty((int)($inPath[3]))?(int)($inPath[3]):1;
		$length = !empty((int)($inPath[4]))?(int)($inPath[4]):20;
		$params = SJson::decode(utility_net::getPostData());
		
		$data = array();
		if(!empty($params->type)){
			$data['type'] = $params->type;
		}
		if(!empty($params->starttime)){
			$data['starttime'] = $params->starttime;
		}
		if(!empty($params->endtime)){
			$data['endtime'] = $params->endtime;
		}
		
		$user_db = new user_db();
		$data = $user_db->getUserFeedbackList($page,$length,$data);

		return $data;
	}
	
	public function pageGetUserByids($inPath)
	{
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uidArr))
		{
			return $this->setResult('',-1, 'the params is not found!');
		}
		foreach($params->uidArr as $v)
		{
			$uidArr[] = $v;
		}
		$ret=user_db::getUserProfileByUidArr($uidArr);
		return $ret;
        if($ret===false)
		{
            return false;
		}
		$retdata = array();
		if(!empty($ret->items))
		{
			$retdata["data"] = $ret->items;
		}
		return $retdata;
	}
	
	public function pageGetFeedbackByFdkId($inPath)
	{
		if(empty($inPath[3]) || !is_numeric($inPath[3])) 
		{
			return $this->setResult('',-1, 'invaild parameter');
		}
		$fdkId = $inPath[3];
		$user_db = new user_db();
		$data = $user_db->getUserFeedbackByFdkId($fdkId);;
		if(empty($data))
		{
			return $this->setResult('',-2, 'the data is not found!');
		}
		return $data;
	}
	
	public function pageGetUserProfile($inPath)
	{
		if(empty($inPath[3]) || !is_numeric($inPath[3])) 
		{
			return $this->setResult('',-1, 'invaild parameter');
		}
		$uid = $inPath[3];
		$user_db = new user_db();
		$data = $user_db->getUserProfile($uid);;
		if(empty($data))
		{
			return $this->setResult('',-2, 'the data is not found!');
		}
		return $data;
	}
}

