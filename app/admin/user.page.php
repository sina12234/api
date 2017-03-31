<?php
class admin_user{
	/**
	 * @todo 增加密码加密功能
	 */
	function pageVerify($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result=array("code"=>0,"msg"=>"success");
		if(empty($params->name) || empty($params->password) || empty($params->login_ip)){
			$ret->result['code']=-1;
			$ret->result['msg']='params is empty';
			return $ret;
		}
		$admin_db = new admin_db;
		$user = $admin_db->getAdminUser($params->name);
		/**
		 * TODO
		 */
		$user_password = user_api::encryptPassword($params->password);
		if($user['password']!= $user_password){
			$ret->result['code']=-1;
			$ret->result['msg']='password is error';
			return $ret;
		}
		$ret->data = array('uid'=> $user['pk_mgr_user'],'name'=>$user['name']);
		return $ret;
	}

	public function pageVerifyPsd($inPath){
		
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		if(empty($params->uid) || empty($params->password)){
			$ret->code = -1;
			$ret->msg  = 'params is empty';
		}
		$user = admin_db::getAdminUserByUid($params->uid);	
		$user_password = user_api::encryptPassword($params->password);
		if($user['password'] != $user_password){
			$ret->code = -2;
			$ret->msg  = 'password is error';
		}else{
			$ret->code = 0;
			$ret->msg  = 'success';
		}
		return $ret;
	}

	public function pageUpdatePassword($inPath){
		
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		if(empty($params->uid) || empty($params->password)){
			$ret->code = -1;
			$ret->msg  = 'params is empty';
		}
		$new_password = user_api::encryptPassword($params->password);
		$data = array('password'=>$new_password);
		$update_ret = admin_db::updateUser($params->uid,$data);	
		if($update_ret === false){
			$ret->code = -2;
			$ret->msg  = 'update is error';
		}else{
			$ret->code = 0;
			$ret->msg  = 'success';
		}
		return $ret;
	}


}
