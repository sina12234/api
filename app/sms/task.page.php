<?php
class sms_task{
	public function __construct($inPath){
		return;
    }
    public function pageAddSmsTask($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
        if(empty($inPath[3])){
            return $ret;
        }
        $params->createtime = date("Y-m-d H:i:s", time());
        $table = "t_sms_".$inPath[3]."_task";
        $db = new sms_db();
        $result = $db->addSmsTask($table, $params);
        if(empty($result)){
            return $ret;
        }else{
		    $ret->result->code = 0;
            return $ret;
        }
    }
    /**
     * 取一个发短信任务（后台插入的。查表得到process为fresh的记录，并且设置成work）
     * 输入为任务类型（表名为t_sms_[type]_task）
     */
    public function pageGetSmsTask($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";

        if(empty($params->type)){
			$ret->result->msg = "缺少参数";
			return $ret;
        }
		$ret->result->code = 0;
        $table = "t_sms_".$params->type."_task";
        $db = new sms_db();
        $task = $db->getSmsTask($table);
        if(empty($task)){
			$ret->result->msg = "没有任务";
            return $ret;
        }
        //为了防止主从库的问题，所以要检查确认结果
        $a = $db->confirmSmsTask($table, $task);
        if(empty($a)){
			$ret->result->msg = "没有任务";
            return $ret;
        }else{
            $ret->data = $task;
            return $ret;
        }
    }
    /**
     * 设置后台任务完成。输入参数为表类型和任务id
     */
    public function pageFinishSmsTask($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";

        if(empty($params->type) || !isset($params->task_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
        }
		$ret->result->code = 0;
        $table = "t_sms_".$params->type."_task";
        $db = new sms_db();
        $db->finishSmsTask($table, $params->task_id);
        return $ret;
    }
    /*
     * 插入一条发短信的任务
     */
    public function pageAddSmsLog($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";

        $params->createtime = date("Y-m-d H:i:s", time());

        $db = new sms_db();
        $data = $db->addSmsLog($params);
        if(!empty($data)){
		    $ret->result->code = 0;
            $ret->data = $data;
        }
        return $ret;
    }
    /*
     * 处理一条短信任务（成功返回的data设置为任务id）
     */
    public function pageWorkSmsLog($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = 0;
		$ret->result->msg = "";

        $task = sms_api::getSmsLog();
        if(empty($task)){
            return $ret;
        }
        $r = sms_api::sendSms($task);
        sms_api::writeSmsLogReturn($task, $r);
        $ret->data = $task["pk_log"];
        return $ret;
    }
    /*
     * 云片回调
     */
    public function pageYunpianReturn($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
        $ret->result->msg = "";

        if(empty($params->sms_status)){
            $ret->result->msg = "no params";
            return $ret;
        }

		$ret->result->code = 0;
        $data = new stdclass;
        $data->content = $params->sms_status;
        $data->report_type = "reply";
        $data->createtime = date("Y-m-d H:i:s", time());

        $db = new sms_db;
        $db->addYunpianReport($data);
        return $ret;
    }
    /*
     * 得到云片返回的结果任务
     */
    public function pageWorkYunpianReport($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = 0;
        $ret->result->msg = "";

        $task = sms_api::getYunpianReport();
        if(empty($task)){
            return $ret;
        }
        $r = sms_api::dealYunpianReport($task);
        $db = new sms_db;
        $ret->data = $db->finishYunpianReport($task);
        return $ret;
    }
}
