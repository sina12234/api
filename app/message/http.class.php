<?php

/**
 * @author
 * @docs https://wiki.gn100.com/doku.php?id=docs:message:plan
 */
class message_http{
	/**
	 * 增加消息信号
	 */
	public static function addSignalForScore($userId, $planId, $info){
		$params = new stdclass;
		$params->user_from_id = 0;
		$params->user_from_token = "";
		$params->user_to_id = $userId;
		$params->plan_id = $planId;
		$params->type = message_type::score_info;
		$params->content = SJson::encode($info);
		return self::addPlanMsg($params);
	}
	/**
	 * 当学生发言断的时候，广播结束发言的信号
	 */
	public static function addStopChatPublishSignal($userId, $token, $planId){
		$params = new stdclass;
		$params->user_from_id = $userId;
		$params->user_from_token = $token;
		$params->user_to_id = 0;
		$params->plan_id = $planId;
		$params->type = message_type::ask_cancel;
		$params->content = "cancel";
		return self::addPlanMsg($params);
	}
	public static function clearPlanMessage($params, $type){
		$conf = SConfig::getConfig(ROOT_CONFIG."/services.conf","message");
		$data = new stdclass;
		$data->Password = md5($conf->apikey);
		$data->PlanId = (int)($params->plan_id);
		$data->MessageType = $type;
		$url = $conf->gateway . "/message.plan.clean";
		$ret = SHttp::post($url,array("data"=>SJson::encode($data)));
		return $ret;
	}
	/**
	 * 通过内网调用golang 聊天server,只能是信号！
	 */
	public static function addPlanMsg($params){
		$conf = SConfig::getConfig(ROOT_CONFIG."/services.conf","message");
		$url = $conf->gateway . "/message.plan.add";
		$params->plan_id = (int) ($params->plan_id);
		$params->type= (int) ($params->type);
		$params->message_type = "signal";
		if(isset($params->user_from_id)) 	$params->user_from_id= (int) ($params->user_from_id);
		if(isset($params->user_to_id))		$params->user_to_id= (int) ($params->user_to_id);
		if(isset($params->live_second))		$params->live_second= floatval($params->live_second);
		$params->private_key = md5($conf->apikey);
		$ret = SHttp::post($url,array("data"=>SJson::encode($params)));
		return $ret;
	}
}
