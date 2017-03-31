<?php

class weixin_auth{
	public function __construct($inPath){
	}

	public function pageGet($inPath){
		$config = weixin_api::getConfig();
		$ret = new stdclass;
		$ret->data =  new stdclass;
		if(!empty($config)){
			unset($config['pk_config']);
			unset($config['create_time']);
			$ret->data=$config;
			utility_cache::pageCache(60);
		}
		return $ret;
	}
}

