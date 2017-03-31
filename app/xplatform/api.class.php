<?php
/**
WEB-5248
X平台后端接口
print_r($ret);
print_r($ret);
$api = new xplatform_api;
//$ret=$api->register2ByPhone("13666666666","123456",$nickName="测试");
$r=$api->changePwd2("13666666666", "111111",$ret);
print_r($r);
$api = new xplatform_api;
$r=$api->login2("13488810009","2222222",$ret);
//$ret = $api->getUserInfo("13488810009");
var_dump($ret);
var_dump($r);
*/
class xplatform_api{
	static $appId = 98007;
	static $clientKey = "D71EEF036B231B51BC5BDAE9";
	static $serverKey = "C790F7800AB2AFACBCF3E60A";
	var $gateway="https://login.yunxiaoyuan.com/";
	//var $gateway="https://dev.login.yunxiaoyuan.com/";


	//后台接口
	public function getUserInfo($mobilePhone,&$ret=null){
		$url=$this->gateway."Login/getUserInfo";
		$req = new xplatform_api_request_backend;
		$req->body=array(
			"accountName"=>$mobilePhone,
		);
		$ret = $this->exec($url,$req);
		if(!empty($ret->body->accountId)){
			return $ret;
		}
		return false;
	}
	public function login2($mobilePhone,$pwd,&$ret=null){
		$url=$this->gateway."Login/login2";
		$req = new xplatform_api_request_backend;
		$req->body=array(
			"accountName"=>$mobilePhone,
			"newPwd"=>strtoupper(md5($pwd)),
		);
		$ret = $this->exec($url,$req);
		if($ret->ret->code == 0){
			return true;
		}
		return false;
	}
	public function changePwd2($accountName,$pwd,&$ret=null){
		$url=$this->gateway."Login/changePwd2";
		$req = new xplatform_api_request_backend;
		$req->body=array(
			"accountName"=>$accountName,
			"newPwd"=>strtoupper(md5($pwd)),
		);
		$ret = $this->exec($url,$req);
		if($ret->ret->code == 0){
			return true;
		}
		return false;
	}
	public function changeUserInfo($accountId,&$ret=null){
		$url=$this->gateway."Login/changeUserInfo";
		$req = new xplatform_api_request_backend;
		$req->head['accountId']=$accountId;
		$req->body=array(
			"accountId"=>$accountId,
			"accountName"=>$accountName,
			"mobilePhone"=>$mobilePhone,
			"nickName"=>$nickName,
		);
		$ret = $this->exec($url,$req);
		return $ret;
	}
	public function verifyOnceToken($accountId,$onceToken,&$ret=null){
		$url=$this->gateway."Login/verifyOnceToken";
		$req = new xplatform_api_request_backend;
		$req->head['accountId']=$accountId;
		$req->body=array(
			"accountId"=>$accountId,
			"onceToken"=>$onceToken,
		);
		$ret = $this->exec($url,$req);
		if(empty($ret->body->accessToken)){
			return false;
		}
		return $ret;
	}
	public function register2ByPhone($mobilePhone,$newPwd,$nickName="",&$ret=null){
		if(empty($nickName))$nickName=$mobilePhone;
		$url=$this->gateway."Login/register2ByPhone";
		$req = new xplatform_api_request_backend;
		$req->body=array(
			"userList"=>array(
				array(
					"mobilePhone"=>$mobilePhone,
					"newPwd"=>strtoupper(md5($newPwd)),
					"nickName"=>$nickName
				)
			)
		);
		$ret = $this->exec($url,$req);
		return $ret;
	}




	private function exec($url, $req){
		$str = $this->post($url,$req->toString());
		$resp = new xplatform_api_request_backend;
		return $resp->parse($str);
	}
	public function post( $url, $params=array(),$returnHeader=false, $timeout=3){
		if(is_array($params)){
			$content = empty($params)?"":http_build_query($params);
		}else{
			$content=$params;
		}
		$opts = array(
			'http'=>array(
				'method' => 'POST',
				'timeout'=>$timeout,
				'header' =>
				"Accept-Language: zh-cn\r\n" .
				"User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)\r\n" .
				"Referer: $url\r\n" .
				"Connection: Close\r\n" .
				(!empty($cookies)?"Cookie: ".self::cookie_build($cookies)."\r\n":"").
				"Content-Type: application/x-www-form-urlencoded\r\n" . 
				"Content-Length: ".strlen($content)."\r\n",
					'content' => $content
				)

			);
		$context = stream_context_create($opts);
		$result =  file_get_contents($url,false,$context);
		if($returnHeader){
			return array("result"=>$result,"header"=>$http_response_header);
		}else{
			return $result;
		}

	}
}
class xplatform_api_request_backend{
	var $head=array(
		"appId"=>98007,//xplatform_api::$appId,
		"v"=>"1",
		"accountId"=> 0,//云校园账号Id
		"deviceType"=> 0,//系统类型1-Android 2-IOS
		"brand"=> "",//设备品牌
		"model"=> "",//设备新型号
		"systemVersion"=> "",//设备系统版本
		"uuid"=> ""//设备唯一码
	);
	var $body=array();
	public function toString(){
		$req = array_merge(array("head"=>$this->head),array("body"=>$this->body));
		$req['body']= $this->_encrypt($req['body']);
		return json_encode($req);
	}
	public function parse($str){
		$ret = json_decode($str);
		$ret->body = $this->_decrypt($ret->body);
		return $ret;
	}
	//解密
	private function _decrypt($data) {
		if(empty($data))return ;
		if(is_array($data) || is_object($data)){$data=json_encode($data);}
		$method = "des-ede3";
		$ret = openssl_decrypt($data, $method, xplatform_api::$serverKey, false);
		return json_decode($ret);
	}
	private function _encrypt($data){
		if(empty($data))return ;
		if(is_array($data) || is_object($data)){$data=json_encode($data);}
		$method = "des-ede3";
		$ret = openssl_encrypt($data, $method, xplatform_api::$serverKey, false);
		return $ret;
	}
}
