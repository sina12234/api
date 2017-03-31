<?php
/**
  * 用户API 
  * @link http://wiki.gn100.com/doku.php?id=docs:api:user
  **/
class user_parterner{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	/**
	  * 
	  */
	function pageSet($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($params->parterner->nickname) || empty($params->parterner->source) || empty($params->parterner->parterner_uid)){
			$ret->result->code = -1; 
			return $ret;
		}
		$user_db = new user_db;
		$parter = $user_db->getUserParterner($params->parterner->source,$params->parterner->parterner_uid);

		$parter_info = array();
		if(!empty($params->uid)){ $parter_info['fk_user']	=	$params->uid; }
		if(!empty($params->parterner->parterner_uinfo)){ 
			$parter_info['parterner_uinfo']	=	$params->parterner->parterner_uinfo; 
		}
		if(!empty($params->parterner->auth_code)){ 
			$parter_info['auth_code']	=	$params->parterner->auth_code; 
		}
		if(!empty($params->parterner->thumb_url)){ 
			$parter_info['thumb_url']	=	$params->parterner->thumb_url; 
		}
		$parter_info['nickname']		=	$params->parterner->nickname;
		$parter_info['source']			=	$params->parterner->source;
		$parter_info['parterner_uid']	=	$params->parterner->parterner_uid;
		$parterner_id = 0;
		$uid = 0;
		if(empty($parter)){
			//新增
			$uid = $params->uid;
			$parter_info['create_time']		=	date("Y-m-d H:i:s");
			$parterner_id = $user_db->addUserParterner($parter_info);
			if(empty($parterner_id)){
				$ret->result->code = -2; 
				return $ret;
			}
		}else{
			$parterner_id = $parter['pk_parterner'];
			$uid = $parter['fk_user'];
			$db_ret = $user_db->updateUserParterner($params->parterner->source,$params->parterner->parterner_uid,$parter_info);
			if($db_ret===false){
				$ret->result->code = -2; 
				return $ret;
			}
		}
		$ret->result->code = 0; 
		$ret->data=array("parterner_id"=>$parterner_id,"uid"=>$uid);
		return $ret;
	}
	function pageBind($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($params->parterner_id) || empty($params->uid)){
			$ret->result->code = -1; 
			return $ret;
		}
		$user_db = new user_db;
		$parter = $user_db->getUserParternerById($params->parterner_id);
		if(empty($parter['pk_parterner'])){
			$ret->result->code = -3; 
			return $ret;
		}
		$userinfo = $user_db->getUser($params->uid);
		if(empty($userinfo['pk_user'])){
			$ret->result->code = -2; 
			return $ret;
		}
		$db_ret = $user_db->bindParterner($params->parterner_id,$params->uid);
		if($db_ret===false){
			$ret->result->code = -4; 
			return $ret;
		}
		$ret->result->code = 0; 
		return $ret;

	}
	function pageGet($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$user_db = new user_db;
		if(!empty($params->parterner->source) && !empty($params->parterner->parterner_uid)){
			$data=$user_db->getUserParterner($params->parterner->source,$params->parterner->parterner_uid);
		}elseif(!empty($params->parterner->source) && !empty($params->parterner->user_id)){
			$data=$user_db->getUserParternerByUId($params->parterner->source,$params->parterner->user_id);
		}elseif(!empty($params->parterner_id)){
			$data=$user_db->getUserParternerById($params->parterner_id);
		}
		if(!empty($data)){
			$ret->data=array(
					"parterner_id"=>$data['pk_parterner'],
					"uid"=>$data['fk_user'],
					"nickname"=>$data['nickname'],
					"source"=>$data['source'],
					"parterner_uid"=>$data['parterner_uid'],
					"parterner_uinfo"=>$data['parterner_uinfo'],
					"thumb_url"=>$data['thumb_url'],
					"auth_code"=>$data['auth_code'],
					"create_time"=>$data['create_time'],
					"last_updated"=>$data['last_updated'],
					);
		}else{
			$ret->result=array("code"=>-1);
		}
		return $ret;
	}
	/**
	 * 提代第3个学校和我们云课域名的绑定关系并跳转
	 * WEB-7189
	 */
	function pageGetSchool($inPath){
		$ret = new stdclass;
		$ret->result = array("code"=>-1);
		if(empty($inPath[3])){
			return $ret;
		}
		$params=SJson::decode(utility_net::getPostData());
		$school_id = $inPath[3];
		if(empty($params->source) || $params->source=="xplatform"){//X平台
			$school_info = user_db_xplatformDao::getSchoolBySchoolId($school_id);
		}elseif($params->source=="fjedu"){//福建教育平台 
			$school_info = user_db_fjeduDao::getSchoolBySchoolId($school_id);
		}
		if(!empty($school_info)){
			$ret->data=$school_info;
		}
		return $ret;
	}

}
