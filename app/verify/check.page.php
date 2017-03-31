<?php
class verify_check{
	public function __construct($inPath){
	}
	public function pageMobile($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code=-1;
		if(empty($params->mobile) || empty($params->code)){
			$ret->result->msg='参数错误';
			return $ret;
		}
		//判断IP限制
		$check_ret = verify_api::verifyMobile($params->mobile,$params->code);
		if($check_ret){
			$ret->result->code=0;
			return $ret;
		}
		return $ret;

	}
}
