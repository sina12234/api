<?php
class live_api{
	/*
	 *@param $info
		$info={
			"plan_id"
			"user_id"
			"stream"
			"timestamp"
			"token"
		};
	 */
	public static function getInfo($token){
		$conf = SConfig::getConfig(ROOT_CONFIG."/key.conf","live");
		if(!empty($conf->key)){
			$aes = new utility_aes();
			$aes->set_key($conf->key);
			$info = SJson::decode($aes->decrypt($token));
			return $info;
		}
		return ;
	}
	public static function getAuthByToken($token){
		$db = new live_db;
		return $db->getAuthByToken($token);
	}
	public static function getPublishByUserID($uid){
		$db = new live_db;
		return $db->getPublishByUserID($uid);
	}
	public static function setPublish($uid){
		$Client=array();
		$Client['server_ip'] 	= utility_ip::toLong(utility_ip::realIp());
		$Client['client_id'] 	= $_REQUEST['clientid'];
		$Client['user_ip'] 		= utility_ip::toLong($_REQUEST['addr']);
		$Client['live_call'] 	= $_REQUEST['call'];
		$Client['app_name']		= $_REQUEST['app'];
		$Client['flash_ver'] 	= $_REQUEST['flashver'];
		$Client['swf_url'] 		= $_REQUEST['swfurl'];
		$Client['tc_url'] 		= $_REQUEST['tcurl'];
		$Client['page_url'] 	= $_REQUEST['pageurl'];
		$Client['fk_plan'] 		= 0;
		$Client['stream_name'] 	= $_REQUEST['name'];
		$Client['create_time'] 	= date("Y-m-d H:i:s");
		$db = new live_db;
		return $db->setPublish($uid,$Client);
	}
	public static function updatePublish(){
		$Client=array();
		$Client['server_ip'] 	= utility_ip::toLong(utility_ip::realIp());
		$Client['client_id'] 	= $_REQUEST['clientid'];
		$Client['live_call'] 	= $_REQUEST['call'];
		$db = new live_db;
		return $db->updatePublish($Client);
	}
	public static function allowChat($uid,$planid,$token){
		$live_db = new live_db;
		$course_db= new course_db;
		$token_info = $course_db->getPlanUserByPlanId($planid,$uid,$token);
		$publish_info = $live_db->getPublishByPlanID($planid);
		if(empty($token_info) || empty($publish_info)){
			return false;
		}
		$chat_auth=array();
		$chat_auth['fk_user']=$uid;
		$chat_auth['fk_plan']=$planid;
		$chat_auth['app_name']="chat";
		$chat_auth['stream_name']=$publish_info['stream_name'];
		$chat_auth['pub_token']=$token_info['user_token'];
		$chat_auth['status']=1;
		$chat_auth['create_time']=date("Y-m-d H:i:s");

		return $live_db->setChatAuth($chat_auth);
	}
	public static function closeChat($uid, $planid){
		//根据UserID获取serveraddr和client等发布信息
		$db = new live_db;
		$publish = $db->getChatAuthByUserId($uid);
		$chat_cdn = $db->listChatPublishCdn();
		$ret = null;
		if(!empty($publish) && !empty($chat_cdn->items)){
			//踢人
			foreach($chat_cdn->items as $item){
				$url = "http://".$item['intranet_ip'].":8100/control/drop/publisher?app=".$publish['app_name']."&name=".$publish['stream_name'];
				$i=2;
				while($i--){
					$ret = SHttp::get($url);
					if(!empty($ret)){break;}
				}
			}
		}
		//删除token
		$token_info = $db->getChatAuthByUserId($uid);
		if(!empty($token_info['pub_token'])){
			$db->delChatAuth($token_info['pub_token']);
		}
		return $ret;
	}
	public static function getOneLiveEncodingTask($work_id, $task_type){
		$db = new live_db;
        while(true){
            $ret = $db->getOneLiveEncodingFreeTask($task_type);
            if(empty($ret)){
                return $ret;
            }
            $ret["work_id"] = $work_id;
            $a = $db->setWorkIdForLiveEncodingFree($ret["task_id"], $work_id);
            if(!empty($a)){
                return $ret;
            }
        }
	}
	public static function checkOneLiveEncodingTask($task){
		$db = new live_db;
		$ret = $db->getOneLiveEncodingTaskByPlan($task["plan_id"]);
		if(!empty($ret) && $ret["task_id"] != $task["task_id"]){
			$db->setWorkIdForLiveEncoding($task["task_id"], $ret["work_id"]);
			return true;
		}else{
			return false;
		}
	}
}
