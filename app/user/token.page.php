<?php
class user_token{

	function pageGen($inPath){
		$ret = new stdclass;
		$ret->result=array("code"=>-1,"msg"=>"");
		$params=SJson::decode(utility_net::getPostData());
		if(!isset($params->uid) || empty($params->platform) || empty($params->ip)){
			$ret->result['code']=-1;
			$ret->result['msg']='params error';
			return $ret;
		}
		$user_db = new user_db;
		$ip = utility_ip::toLong($params->ip);
		if(empty($params->token)){
			$token = md5($params->uid.".".$ip.rand(1000000,999999999));
		}else{
			$token=$params->token;
		}
		$db_data=array();
		$db_data['token']	=	$token;
		$db_data['fk_user']	=	$params->uid;
		$db_data['user_ip']	=	$ip;
		$db_data['platform']=	$params->platform;
		if(isset($params->user_status)){
			$db_data['user_status']=	$params->user_status;
		}
		if(isset($params->live_status)){
			$db_data['live_status']=	$params->live_status;
		}
		$token_id = $user_db->addToken($db_data);
		if($token_id!==false){
			$ret->result['code']=0;
			$ret->result['msg']='success';
			$ret->data=array("token"=>$token);
		}else{
			$ret->result=array("code"=>-2);
			$ret->result=array("msg"=>'add redis failed');
		}
		return $ret;
	}
	function pageGet($inPath){
		$ret = new stdclass;
		$ret->result=array("code"=>0,"msg"=>"");
		if(empty($inPath[3])){
			$ret->result['code']=-1;
			return $ret;
		}
		$user_db = new user_db;
		$token_info = $user_db->getToken($inPath[3]);
		if(!empty($token_info)){
			$ret->data=array(
				"token"=>$token_info['token'],
				"uid"=>$token_info['fk_user'],
				"user_status"=>$token_info['user_status'],
				"live_status"=>$token_info['live_status'],
				"user_ip"=>utility_ip::toIp($token_info['user_ip']),
				);
		}else{
			$ret->result=array("code"=>-1);
		}
		return $ret;
	}
	function pageDel($inPath){
		$ret = new stdclass;
		$ret->result=array("code"=>0,"msg"=>"");
		if(empty($inPath[3])){
			$ret->result['code']=-1;
			return $ret;
		}
		$user_db = new user_db;
		$r = $user_db->delToken($inPath[3]);
		if($r){
			$ret->result=array("code"=>1);
		}else{
			$ret->result=array("code"=>-1);
		}
		return $ret;
	}
	/**
	 * 校验token,目前只有播放接口用到了
	 */
	function pageVerify($inPath){
		$ret = new stdclass;
		$ret->result=array("code"=>-1,"msg"=>"");
		if(!isset($inPath[3])){
			return $ret;
		}
		$token = $inPath[3];
        $plan_id = null;
        if(!empty($inPath[4])){
            $plan_id = $inPath[4];
        }
		$user_db = new user_db;
		$token_info = $user_db->getToken($token);
		$user_id = 0;
		if(!empty($token_info)){// && utility_ip::toIp($token_info['user_ip'])==$params->ip){
			//TODO 判断这个用户有没有购买这个课程
            $user_id = $token_info["fk_user"];
		}
		$ret->result=array("code"=>0, "user_id"=>$user_id);
		$perm = course_api::verifyPlan($user_id,$plan_id,$apply,$try_info);
		if($perm){
			$ret->result['ok']=1;
			if($apply){
				$ret->result['reg']=1;
			}
			if(!empty($try_info["time"])){
				$ret->result["try_seconds"] = $try_info["time"];
			}
			$ret->result["code"]=0;
			return $ret;
		}
		$ret->result=array("code"=>-1);
		return $ret;
	}
}
