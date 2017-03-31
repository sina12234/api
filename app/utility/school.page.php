<?php
class utility_school{

	public function pageschoolList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//return $ret;
		$params = SJson::decode(utility_net::getPostData());
		//$uid = !empty($inPath[3]) ? $inPath[3] : '';
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])){$length =20;}else{$length = $inPath[4];}

		$utility_api = new utility_api();

		$listorg = $utility_api->getschoolList($page,$length);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$listorg1 = SJson::decode($listorg);
		return $listorg1;
	}

	public function pageGetSchoolInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$schoolid = $inPath[3];
		if(empty($schoolid)){
			return $ret;
		}
		$profile_info = array();
		$profile_info = utility_db::getSchoolById($schoolid);
		if (empty($profile_info)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);
		}
		return array(
			'data' => $profile_info,
		);
	}

	public function setResult($data='',$code=0,$msg='success'){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code =$code;
		$ret->result->data =$data;
		$ret->result->msg= $msg;
		return $ret;
	}

	public function pageUpdateSchoolInfo($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$sid=$inPath[3];
		if(empty($params)){
			return $this->setResult('',-1,'param is empty');
		}
		if(empty($sid)){
			return $this->setResult('',-1,'sid is empty');
		}
		$utility_db = new utility_db();
		$updateProfile = array();
		$orgInfo = utility_db::getSchoolById($sid);
		if(!empty($orgInfo)) {
			$data = array();
			$data['fk_school'] = $sid;
			$data['school_name'] = $params->school_name;
			$data['addr'] = $params->addr;
			$data['phone'] = $params->phone;
			$updateProfile = $utility_db->updateSchoolInfo($sid, $params);
			//$verify['last_updated']=date("Y-m-d H:i:s");
		}
		if($updateProfile===false){
			return $this->setResult('',-1,'update is empty');
		}
		return $this->setResult($updateProfile);
	}

	public function pagesearchShow($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$utility_api = new utility_api();
		$school_list = $utility_api->getsearchShow($params);
		if (empty($school_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $school_list;
	}

	public function pagegetNormalSchoolNameByInfo($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$data = !empty($params['school_name']) ? $params['school_name'] : '';
		$orgInfo = utility_db::getNormalSchoolNameByInfo($data);
		if (empty($orgInfo)) {
			return array("code" => -100,"msg" => 'is not found data ');
		}
		return $orgInfo;
	}
}
