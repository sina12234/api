<?php
/**
 * IP信息查询
 * @author hetal
 * @copyright 2014 GN100.COM
 * @version 1.0
 */
class utility_ip{
	public static function toLong($ip){
		if(is_numeric($ip)){
			return $ip;
		}else{
			return sprintf("%u", ip2long($ip)); 
		}
	}
	public static function realIp(){
		if(isset($_SERVER['HTTP_X_REAL_IP'])){
			return $_SERVER['HTTP_X_REAL_IP'];
		}
		if(isset($_SERVER['HTTP_REMOTEIP'])){
			return $_SERVER['HTTP_REMOTEIP'];
		}
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		if(isset($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		}
		return null;
	}
	public static function toIp($ip){
		if(is_numeric($ip)){
			$long = 4294967295 - ($ip - 1); 
			return long2ip(-$long); 
		}else{
			return $ip;
		}
	}
	/**
	 * 根据IP获取信息
	 * @param int|string $ip
	 * @return mixed
		{
			area_desc: "",
				area_name: "",
				ip: "213412341234",
				ok: false,
				op_desc: "",
				op_name: ""
		}
	 */
	public static function info($ip){
		$conf = SConfig::getConfig(ROOT_CONFIG."/services.conf","ip");
		if(empty($conf)){return false;}
		$ip = utility_ip::toIp($ip);
		return SJson::decode(SHttp::get($conf->gateway."/?ip=$ip"));
	}
}
