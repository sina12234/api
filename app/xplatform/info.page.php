<?php
class xplatform_info{
	/**
	 * 校验onceToken
	 * @return $object $result
	 * $result->data->uid 
	 */
	public function pageVerifyOnceToken($inPath){
		$ret=array("result"=>array("code"=>-1,"msg"=>""),"data"=>array("uid"=>0));
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->accountId) || empty($params->onceToken)){
			return $ret;
		}
		$api = new xplatform_api;
		if($api->verifyOnceToken($params->accountId, $params->onceToken,$r)===false){
			$ret['result']['code']=-2;
			return $ret;
		}
		$mobile = $r->body->account;
		$user_id = user_db::getUserIDByMobile($mobile);
		if(empty($user_id) && !empty($r->body->account) && !empty($r->body->nickName)){
			//新用户，注册后自动登录
			$user_info = new stdclass;
			$user_info->name = $r->body->nickName;
			$user_info->mobile = $r->body->account;
			$user_info->source=2;//2 X平台导入  https://wiki.gn100.com/doku.php?id=docs:db:user
			$user_info->thumb_big = "5,05844dc4357b";
			$user_info->thumb_med= "4,058582450327";
			$user_info->thumb_small= "7,05863b61ed03";
			$user_api = new user_api;
			$user_id = $user_api->addUser($user_info);
		}else{
			//老用户，直接登录
		}
		$ret['result']['code']=0;
		$ret['data']['uid']=$user_id;
		return $ret;
	}
}
