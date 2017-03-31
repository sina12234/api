<?php
class weixin_db{

	public static function InitDB($dbname="db_weixin") {
		$db = new SDb();
		$db->useConfig($dbname, 'main');
		return $db;
	}
	public static function getConfig(){
		$table=array("t_weixin_config");
		$db = self::InitDB();
		return $db->selectOne($table,array("pk_config"=>"1"), "*");
	}
	public static function updateAccessToken($access_token,$jsapi_ticket,$expires_in){
		$table=array("t_weixin_config");
		$db = self::InitDB();
		return $db->update($table,array("pk_config"=>"1"), array(
				"access_token"=>$access_token,
				"jsapi_ticket"=>$jsapi_ticket,
				"token_expires_in"=>$expires_in)
		);
	}
	public static function addRecieve($receive){
		$table=array("t_weixin_message_receive");
		$db = self::InitDB();
		return $db->insert($table,$receive);
	}
	public static function listRecieve($conditon=array(),$page=1,$length=20){
		$table=array("t_weixin_message_receive");
		$db = self::InitDB();
		$db->setPage($page);
		$db->setLimit($length);
		return $db->select($table,$conditon,"*",'',"pk_receive desc");
	}
	public static function getRecieve($rid){
		$table=array("t_weixin_message_receive");
		$db = self::InitDB();
		return $db->selectOne($table,array("pk_receive"=>$rid));
	}
	public static function addReply($reply){
		$table=array("t_weixin_message_reply");
		$db = self::InitDB();
		return $db->insert($table,$reply);
	}
	public static function addMedia($media){
		$table=array("t_weixin_media");
		$db = self::InitDB();
		return $db->insert($table,$media);
	}
	public static function getMediaByMid($media_id){
		$table=array("t_weixin_media");
		$db = self::InitDB();
		return $db->selectOne($table,array("media_id"=>$media_id));
	}
}

