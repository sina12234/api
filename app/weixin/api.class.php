<?php
class weixin_api{
	public static function getConfig(){
		$db = new weixin_db;
		$db_ret=$db->getConfig();
		$config=array();
		if(empty($db_ret['jsapi_ticket']) || empty($db_ret['access_token']) || $db_ret['token_expires_in']-600 < time()){
			//token会在10分钟内过期了，主动刷新
			require_once(ROOT_LIBS."/weixin/mp/wechat.class.php");
			$options = array(
				'token'=>$db_ret['token'], //填写你设定的key
				'encodingaeskey'=>$db_ret['encodingaeskey'], //填写加密用的EncodingAESKey
				'appid'=>$db_ret['appid'], //填写高级调用功能的app id, 请在微信开发模式后台查询
				'appsecret'=>$db_ret['appsecret'], //填写高级调用功能的密钥
				'partnerid'=>$db_ret['partnerid'], //财付通商户身份标识，支付权限专用，没有可不填
				'partnerkey'=>$db_ret['partnerkey'], //财付通商户权限密钥Key，支付权限专用
				'paysignkey'=>$db_ret['paysignkey'] //商户签名密钥Key，支付权限专用
			);
			$weObj = new Wechat($options);
			$access_token = $weObj->checkAuth();

			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$access_token";
			$res = SJson::decode(SHttp::get($url));
			$jsapi_ticket="";
			if(!empty($res->ticket)){
				$jsapi_ticket = $res->ticket;
			}


			if(!empty($access_token)){
				//更新数据库里的token
				$token_expires_in=time()+300;
				$db->updateAccessToken($access_token,$jsapi_ticket,$token_expires_in);
				$db_ret['refresh']=1;
				$db_ret['access_token']=$access_token;
				$db_ret['jsapi_ticket']=$jsapi_ticket;
				$db_ret['token_expires_in']=$token_expires_in;
			}
		}
		if(!empty($db_ret['ip_list'])){
			$db_ret['ip_list']=explode(",",$db_ret['ip_list']);
		}
		return $db_ret;
	}
	public static function sendCustomTextMessage($openid, $text, &$result){
		require_once(ROOT_LIBS."/weixin/mp/wechat.class.php");
		$config = weixin_api::getConfig();
		$weObj = new Wechat($config);
		//$weObj->valid();
		$data=array(
			"touser"		=>$openid,
			"msgtype"		=>"text",
			"text"			=>array("content"=>$text),
		);
		$ret_send = $weObj->sendCustomMessage($data, $result);
		return $ret_send;
	}
}
