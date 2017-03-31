<?php
class sms_api{
    public static function sendSms($task){
		$conf = SConfig::getConfig(ROOT_CONFIG."/services.conf","sms");
		if(empty($conf)){return false;}
        $data = array(
            "apikey" => $conf->apikey,
            "mobile" => $task["phone"],
            "uid" => strval($task["pk_log"]),
            "callback_url" => $conf->callback
        );
        if(empty($task["tpl_id"])){
            $data["text"] = $task["tpl_value"];
            $url = $conf->gateway_send;
        }else{
            $data["tpl_id"] = $task["tpl_id"];
            $data["tpl_value"] = $task["tpl_value"];
            $url = $conf->gateway;
        }
		//发送短信
        $r = SHttp::post($url, $data);
        //return "12345666";
        return $r;
    }
    public static function getSmsLog(){
        $db = new sms_db();
        $task = $db->getSmsLog();
        if(empty($task)){
            return false;
        }
        //为了防止主从库的问题，所以要检查确认结果
        $a = $db->confirmSmsLog($task);
        if(empty($a)){
            return false;
        }else{
            return $task;
        }
    }
    public static function writeSmsLogReturn($task, $r){
        $item = array("result"=>$r, "sid"=>0, "fee"=>0, "is_send_success"=>2, "process"=>"finish");
        try{
            $result = SJson::decode($r);
            if(!empty($result->result) && !empty($result->result->sid)){
                $item["sid"] = $result->result->sid;
                if(!empty($result->result->fee)){
                    $item["fee"] = $result->result->fee;
                }
                $item["is_send_success"] = 1;
            }
        } catch(Exception $e){
        }
        $db = new sms_db();
        $condition = "pk_log=".$task["pk_log"];
        $db->modifySmsLog($condition, $item);
    }
    public static function getYunpianReport(){
        $db = new sms_db();
        $task = $db->getYunpianReport();
        if(empty($task)){
            return false;
        }
        //为了防止主从库的问题，所以要检查确认结果
        $a = $db->confirmYunpianReport($task);
        if(empty($a)){
            return false;
        }else{
            return $task;
        }
    }
    public static function dealYunpianReport($task){
        try{
            $data = SJson::decode($task["content"]);
            $db = new sms_db;
            for($i=0;$i<count($data);$i++){
                if(!empty($data[$i]->sid)){
                    if(empty($data[$i]->report_status)){
                        $success = 2;
                    }else{
                        if("SUCCESS" == $data[$i]->report_status){
                            $success = 1;
                        }else{
                            $success = 2;
                        }
                    }
                    $params = new stdclass;
                    $params->success = $success;
                    $condition = "sid=" . $data[$i]->sid;
                    $db->modifySmsLog($condition, $params);
                }
            }
        } catch(Exception $e){
        }
    }
}
?>
