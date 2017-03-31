<?php
class verify_api{
	public static function sendSMS($Mobile,$msg,$tpl_id){
		$conf = SConfig::getConfig(ROOT_CONFIG."/services.conf","sms");
		if(empty($conf)){return false;}
		$data = array(
				'apikey' 	=> $conf->apikey,
				'mobile' 	=> $Mobile,
				'tpl_id' 	=> $tpl_id,
				'tpl_value'	=> $msg,
			     );
		//发送短信
		$r = SHttp::post($conf->gateway,$data);
		$result = SJson::decode($r);
		$Log=array();
		$Log['mobile']=$Mobile;
		$Log['tpl_id']=$tpl_id;
		$Log['tpl_value']=$msg;
		$Log['send_result']=$r;
		if(isset($result->code)){
			$Log['code']=$result->code;
			if(!empty($result->result->sid)){
				$Log['sid']=$result->result->sid;
			}
		}else{
			$Log['code']=-99;
			$Log['sid']="";
		}
		$db = new verify_db;
		$ret=$db->addSMSLog($Log);
		return  $result;
	}
	/**
	 * 发送语音验证码
	 */
	public static function sendVoice($Mobile,$code){
		$conf = SConfig::getConfig(ROOT_CONFIG."/services.conf","sms");
		if(empty($conf)){return false;}
		$data = array(
				'apikey' 	=> $conf->apikey,
				'mobile' 	=> $Mobile,
				'code'		=> $code,
				'display_num'=> $conf->display_num,
			     );
		//发送短信
		$r = SHttp::post($conf->gateway_voice,$data);
		$result = SJson::decode($r);
		$Log=array();
		$Log['mobile']=$Mobile;
		$Log['tpl_id']=0;
		$Log['tpl_value']=$code;
		$Log['send_result']=$r;
		if(isset($result->code)){
			$Log['code']=$result->code;
			if(!empty($result->result->sid)){
				$Log['sid']=$result->result->sid;
			}
		}else{
			$Log['code']=-99;
			$Log['sid']="";
		}
		$db = new verify_db;
		$ret=$db->addSMSLog($Log);
		return  $result;
	}
	public static function verifyMobile($mobileno,$verifycode){
		//取出有效（最近的3条）
		$db = new verify_db;
		$r=$db->getVerifyCodeByMobile($mobileno);
		if(!empty($r->items)){
			foreach ($r->items as $item){
				if($item['code']==$verifycode)return true;
			}
		}
		return false;
		//和数据库的对比
	}
	/**
	  * TODO
	  **/
	public static function verifyEmail($mobileno,$verifycode){
	}
	public static function getVerifyCodeLogCt($mobile="",$email="",$sender_ip=""){
		$db = new verify_db;
		$r = $db->getVerifyCodeLogCt($mobile,$email,$sender_ip);
		if(!isset($r['ct']))return 999;
		return $r['ct'];
	}
	public static function addVerifyCodeLog($mobile="",$email="",$sender_ip=""){
		$db = new verify_db;
		return $db->addVerifyCodeLog($mobile,$email,$sender_ip);
	}
}
