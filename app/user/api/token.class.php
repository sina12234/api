<?php
class user_api_token{
	/**
	 * @param $token
	 * @return $info
	 * $info['token'],
	 * $info['user_id'],
	 * $info['user_status'],
	 * $info['live_status'],
	 * $info['user_ip'],
	 * "user_ip"=>utility_ip::toIp($token_info['user_ip']),
	 */
	public static function getToken($token){
		$user_db = new user_db;
		$info = $user_db->getToken($token);
		$ret = array();
		if(!empty($info)){
			$ret = array(
					"token"=>$info['token'],
					"user_id"=>$info['fk_user'],
					"user_status"=>$info['user_status'],
					"live_status"=>$info['live_status'],
					"user_ip"=>utility_ip::toIp($info['user_ip'])
				    );
		}
		return $ret;
	}
}
