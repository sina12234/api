<?php
class verify_db{
	public static function InitDB($dbname="db_user") {
		$db = new SDb();
		$db->useConfig($dbname);
		return $db;
	}
	public function addVerifyCodeLog($mobile="",$email="",$sender_ip=""){
		$table=array("t_user_verify_code_log");
		$db = self::InitDB();
		$Log=array();
		$Log['user_ip']=utility_ip::toLong($sender_ip);
		$Log['mobile']=$mobile;
		$Log['email']=$email;
		return $db->insert($table,$Log);
	}
	public function getVerifyCodeLogCt($mobile="",$email="",$send_ip=""){
		$table=array("t_user_verify_code_log");
		$db = self::InitDB();
		$send_ip=utility_ip::toLong($send_ip);
		$t = date("Y-m-d H:i:s",time()-15*60);
		return $db->selectOne($table,array("user_ip"=>$send_ip,"create_time>'$t'"),"count(*) as ct");
	}
	public function addVerifyCode($Verify){
		$table=array("t_user_verify_code");
		$db = self::InitDB();
		return $db->insert($table,$Verify);
	}
	public function getVerifyCodeByMobile($Mobile){
		$table=array("t_user_verify_code");
		$db = self::InitDB();
		$db->setLimit(1);
		$currentTime = date('Y-m-d H:i:s');
		$minTime = date('Y-m-d H:i:s',strtotime( '-15 Minute', strtotime($currentTime)));
		return $db->select($table,"mobile=".$Mobile." AND last_updated>='".$minTime."' AND last_updated<='".$currentTime."'","*",$groupby="",$order="pk_verify DESC");
	}
	public function getVerifyCodeByEmail($Email){
		$table=array("t_user_verify_code");
		$db = self::InitDB();
		return $db->selectOne($table,array("Email"=>$Email),"*",$groupby="",$order="VerifyID DESC");
	}
	public function addSMSLog($Log){
		$table=array("t_sms_send_log");
		$db = self::InitDB();
		return $db->insert($table,$Log);
	}
	public function getSMSByMobile($mobile, $limit, $page){
		$table=array("t_sms_send_log");
		$db = self::InitDB();
		$item = new stdclass;
		$item->log_id = "pk_logid";
		$item->mobile = "mobile";
		$item->tpl_id = "tpl_id";
		$item->tpl_value = "tpl_value";
		$item->sid = "sid";
		$item->code = "code";
		$item->send_result = "send_result";
		$item->create_time = "createt_time";
		$condition = "mobile='$mobile'";
		$db->setLimit($limit);
		$db->setPage($page);
		$db->setCount(true);
		$v = $db->select($table, $condition, $item, "", "pk_logid desc");
		return $v->items;
	}
}
