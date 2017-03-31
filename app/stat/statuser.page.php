<?php
class stat_statuser{
	/*
	 * 增加一条统计信息
     * create by ztf
	 */
	public function pageAddUserStat($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params)){
			$ret->result->code = -3;
			$ret->result->msg = "the data is empty!";
			return $ret;	
		}
		$stat_api = new stat_api;
		$retCourseApi = $stat_api->addUserStat($params);
		if($retCourseApi === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	
	public function pageSetUserOrgStatClassCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$user_id = $inPath[3];
		$data = array();
		$data["count"] = "1";
		$stat_api = new stat_api;
		$retget = $stat_api->setUserOrgStatClassCount($user_id,$data);
		if($retget === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail setNum";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageSetUserOrgStatCourseCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$user_id = $inPath[3];
		$data = array();
		$data["count"] = "1";
		$stat_api = new stat_api;
		$retget = $stat_api->setUserOrgStatCourseCount($user_id,$data);
		if($retget === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail setNum";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	
	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	
	public function pageGetUserStatByUid($inPath){
		$uid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($uid) && !is_numeric($uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = stat_db::getUserStatByFkuser($uid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageGetOrgStudentStat($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || empty($inPath[4])) {
            $ret->result->code = -2;
            $ret->result->msg= "The id is not found!";
			return $ret;
		}
		$db = new stat_db;
		$res = $db->getOrgStudentStat($inPath[3],$inPath[4]);
        if($res===false){
            $ret->result->code = -3;
            $ret->result->msg= "The data is not found!";
			return $ret;
        }
        $ret->data=$res;
        return $ret;
	}
}
