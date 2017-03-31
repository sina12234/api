<?php
class verify_send{
	public function __construct($inPath){
	}
	public function pageMobile($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code=-1;
		if(empty($params->mobile) || empty($params->tpl_id) || empty($params->msg) || empty($params->sender_ip)){
			$ret->result->msg='参数错误';
			return $ret;
		}
		parse_str($params->msg,$msg_info);
		if(empty($msg_info)){
			$ret->result->msg='参数内容msg格式错误';
			return $ret;
		}

		if(!empty($msg_info['#code#'])){
			//判断IP限制
			$ct = verify_api::getVerifyCodeLogCt($params->mobile,"",$params->sender_ip);
			if($ct>10){
				$ret->result->msg="您的发送了太多的验证码，请15分钟后再试！";
				return $ret;
			}
		}
		$r = verify_api::sendSMS($params->mobile,$params->msg,$params->tpl_id);
		if(!isset($r->code) || $r->code){
			$ret->result->msg='operation failure';
			return $ret;
		}

		$ret->result->sms_code=$r->code;
		$ret->result->sms_msg=$r->msg;
		if(!empty($msg_info['#code#'])){
			//验证码服务，写验证码表
			$Verify = array();
			$Verify['code']=$msg_info['#code#'];
			$Verify['fk_user']=0;
			$Verify['mobile']=$params->mobile;
			$verify_db = new verify_db;
			$verifyId = $verify_db->addVerifyCode($Verify);
			$ret->result->verify_log=$verifyId;
			//记防刷日志
			verify_api::addVerifyCodeLog($params->mobile,$email="",$params->sender_ip);
		}
		$ret->result->code=$r->code;

		return $ret;
	}
	/**
	 * 发送语音验证码
	 */
	public function pageVoice($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code=-1;
		if(empty($params->mobile) || empty($params->code) || empty($params->sender_ip)){
			$ret->result->msg='参数错误';
			return $ret;
		}
		//判断IP限制
		$ct = verify_api::getVerifyCodeLogCt($params->mobile,"",$params->sender_ip);
		if($ct>10){
			$ret->result->msg="您的发送了太多的验证码，请15分钟后再试！";
			return $ret;
		}
		$r = verify_api::sendVoice($params->mobile,$params->code);
		$ret->result->sms_code=$r->code;
		//验证码服务，写验证码表
		$Verify = array();
		$Verify['code']=$params->code;
		$Verify['fk_user']=0;
		$Verify['mobile']=$params->mobile;
		$verify_db = new verify_db;
		$verifyId = $verify_db->addVerifyCode($Verify);
		$ret->result->verify_log=$verifyId;
		//记防刷日志
		verify_api::addVerifyCodeLog($params->mobile,$email="",$params->sender_ip);
		$ret->result->code=$r->code;
		if($r->code!=0){
			$ret->result->msg=$r->msg;
		}
		return $ret;

	}
}
