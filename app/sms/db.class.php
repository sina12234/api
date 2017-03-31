<?php
class sms_db{
	public static function InitDB($dbname="db_sms",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
    public function addSmsTask($table, $data){
        $db = self::InitDB("db_sms", "main");
        return $db->insert($table, $data);
    }
    public function getSmsTask($table){
        $db = self::InitDB("db_sms", "query");
		$condition = "process='fresh'";
        $v = $db->selectOne($table, $condition);
        return $v;
    }
    public function confirmSmsTask($table, $task){
        $db = self::InitDB("db_sms", "main");
        $condition = "pk_task=".$task["pk_task"]." and process='fresh'";
        $item = array("process"=>"work");
        return $db->update($table, $condition, $item);
    }
    public function finishSmsTask($table, $task_id){
        $db = self::InitDB("db_sms", "main");
        $condition = "pk_task=$task_id";
        $item = array("process"=>"finish");
        return $db->update($table, $condition, $item);
    }
    public function addSmsLog($data){
        $db = self::InitDB("db_sms", "main");
        $v = $db->insert("t_sms_log", $data);
        return $v;
    }
    public function getSmsLog(){
        $db = self::InitDB("db_sms", "query");
		$condition = "process='fresh' and send_type='task'";
        $v = $db->selectOne("t_sms_log", $condition);
        return $v;
    }
    public function confirmSmsLog($task){
        $db = self::InitDB("db_sms", "main");
        $condition = "pk_log=".$task["pk_log"]." and process='fresh'";
        $item = array("process"=>"work");
        return $db->update("t_sms_log", $condition, $item);
    }
    public function modifySmsLog($condition, $value){
        $db = self::InitDB("db_sms", "main");
        $v = $db->update("t_sms_log", $condition, $value);
        return $v;
    }
    public function addYunpianReport($data){
        $db = self::InitDB("db_sms", "main");
        $v = $db->insert("t_yunpian_report", $data);
        return $v;
    }
    public function getYunpianReport(){
        $db = self::InitDB("db_sms", "query");
		$condition = "process='fresh'";
        $v = $db->selectOne("t_yunpian_report", $condition);
        return $v;
    }
    public function confirmYunpianReport($task){
        $db = self::InitDB("db_sms", "main");
        $condition = "pk_result=".$task["pk_result"]." and process='fresh'";
        $item = array("process"=>"work");
        return $db->update("t_yunpian_report", $condition, $item);
    }
    public function finishYunpianReport($task){
        $db = self::InitDB("db_sms", "main");
        $condition = "pk_result=".$task["pk_result"];
        $item = array("process"=>"finish");
        return $db->update("t_yunpian_report", $condition, $item);
    }
}
?>
