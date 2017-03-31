<?php
/**
  * 直播权限设置，文档地址 http://wiki.gn100.com/doku.php?id=docs:api:live
  * @author hetao 2014/12/16
  */
class live_publish{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	/**
	 * 获取发布信息
	 */
	public function pageGet($inPath){
		$r= new stdclass;
		$r->data=array();
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uid) && empty($params->plan_id)){
			return $r;
		}
		$db = new live_db;
		if(!empty($params->uid)){
			$r->data = $db->getPublishByUserID($params->uid);
		}elseif(!empty($params->plan_id)){
			$r->data = $db->getPublishByPlanID($params->plan_id);
		}
		return $r;
	}
	/**
	 * 设置直播流的plan_id,正式开始上课，设置旧的录制视频为无效视频
	 */
	public function pageSetPlan($inPath){
		$r= new stdclass;
		$r->result=array("code"=>-1,"msg"=>"params error");
		if(empty($inPath[3]) || empty($inPath[4]) || !isset($inPath[5])){
			return $r;
		}
		$params=SJson::decode(utility_net::getPostData());
		$uid = $inPath[3];
		$token = $inPath[4];
		$plan_id = $inPath[5];
		$db = new live_db;
		$publish = $db->getPublishByUserID($uid);
		if(empty($publish)){
			$r->result=array("code"=>-2,"msg"=>"publish is empty");
			return $r;
		}
		if(!empty($params->cleanFile)){
			$ret = $db->delRecordFile($uid,$plan_id);
		}
		$record_plan_id = 0;
		if(!empty($plan_id)){
			$record_plan_id=$plan_id;
		}
		$error = $db->updatePublishPlan($publish,$token,$plan_id,$record_plan_id);
		for($startPort=8100;$startPort<=8100;$startPort++){
			$i=2;
			$fixed = false;
			while($i--){
				$url = "http://".utility_ip::toIP($publish['server_ip']).":$startPort/control/record/start?app=".$publish['app_name']."&name=".$publish['stream_name']."&rec=record";
				$ret = SHttp::get($url);
				if(!empty($ret)){$fixed = true; break;}
			}
			if($fixed)break;
		}
		if($error!==false){
			$r->result['code']=0;
			$r->result['msg']="";
		}else{
			$r->result['msg']='更新失败，有可能token已经被别人占用';
		}
		return $r;
	}
	/**
	  * 获取认证信息
	  */
	public function pageGetAuth($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			return $r;
		}
		$db = new live_db;
		$auth = $db->getAuthByUid($inPath[3]);
		if(!empty($auth)){
			$pub_cdn = $db->getVideoPublishCdn();
			$data=array();
			$data['uid'] = $auth['fk_user'];
			$data['token'] = $auth['pub_token'];
			if(!empty($pub_cdn)){
				$data['server'] = "rtmp://".$pub_cdn['host_name']."/".$auth['app_name'];
			}
			$data['stream_name'] = $auth['stream_name']."?token=".$auth['pub_token'];
			$r->data=$data;
		}
		return $r;
	}
	/**
	  * 获取认证信息
	  */
	public function pageGetAuthByToken($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			return $r;
		}
		$db = new live_db;
		$auth = $db->getAuthByToken($inPath[3]);
		if(!empty($auth)){
			$data=array();
			$data['uid'] = $auth['fk_user'];
			$data['token'] = $auth['pub_token'];
			$data['server'] = "rtmp://121.42.56.177/".$auth['app_name'];
			//$data['server'] = "rtmp://pub.gn100.com/".$auth['app_name'];
			$data['stream_name'] = $auth['stream_name']."?token=".$auth['pub_token'];
			$r->data=$data;
		}
		return $r;
	}
	public function pageSetAuth($inPath){
		$r= new stdclass;
		$r->result=array("code"=>-1,"msg"=>"");
		if(empty($inPath[3])){
			return $r;
		}
		$update=array();
		$params=SJson::decode(utility_net::getPostData());
		if(!empty($params->stream_name)){ 	$update['stream_name'] = $params->stream_name; }
		if(!empty($params->token)){ 	$update['pub_token'] = $params->token; }
		if(!empty($params->app_name)){ 	$update['app_name'] = $params->app_name; }
		if(isset($params->status)){ 	$update['status'] = $params->status; }
		$db = new live_db;
		if(!empty($update)){
			$error = $db->setPublishAuth($inPath[3],$update);
			if($error!==false){
				$r->result['code']=0;
			}else{
				$r->result['msg']='更新失败，有可能token已经被别人占用';
			}
		}else{
			$r->result['msg']='没有更新';
		}
		return $r;
	}
	/**
	  * 1.根据用户信息关闭流(发布流)
	  * 2.关闭后，发布端可以重连，但是plan_id被重置
	  * @param array $inPath  $inPath[3]是UserID
	  */
	public function pageClose($inPath){
		$r= new stdclass;
		$r->result=array("code"=>-1,"msg"=>"");
		if(empty($inPath[3])){
			return $r;
		}
		//根据UserID获取serveraddr和client等发布信息
		$db = new live_db;
		$uid = $inPath[3];
		$publish = $db->getPublishByUserID($uid);
		if(empty($publish)){
			$r->result=array("code"=>-2,"msg"=>"no plan info");
			return $r;
		}
		$chat_cdn = $db->listVideoPublishCdn();
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
/*
	for($startPort=8100;$startPort<=8100;$startPort++){
		$i=2;
		$fixed = false;
		while($i--){
			$url = "http://".utility_ip::toIP($publish['server_ip']).":$startPort/control/drop/publisher?app=".$publish['app_name']."&name=".$publish['stream_name'];
			$ret = SHttp::get($url);
			if(!empty($ret)){$fixed = true; break;}
		}
	}
 */
		if(empty($ret)){
			$r->result=array("code"=>0,"msg"=>"success");
		}else{
			$r->result=array("code"=>0,"msg"=>"already closeed");
		}
		return $r;
	}
	public function pageCloseChat($inPath){
		$r= new stdclass;
		$r->result=array("code"=>-1,"msg"=>"uid or planid is empty");
		if(empty($inPath[3]) || empty($inPath[4])){
			return $r;
		}
		//根据UserID获取serveraddr和client等发布信息
		$db = new live_db;
		$UserID = $inPath[3];
		$publish = $db->getChatAuthByUserId($UserID);
		$chat_cdn = $db->listChatPublishCdn();
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
		$token_info = $db->getChatAuthByUserId($UserID);
		if(!empty($token_info['pub_token'])){
			$db->delChatAuth($token_info['pub_token']);
		}
		if(empty($ret)){
			$r->result=array("code"=>0,"msg"=>"success");
		}else{
			$r->result=array("code"=>0,"msg"=>"already closeed");
		}
		return $r;
	}
	public function pageAllowChat($inPath){
		$r= new stdclass;
		$r->result=array("code"=>-1,"msg"=>"uid or planid is empty");
		if(empty($inPath[3]) || empty($inPath[4])){
			return $r;
		}
		$ret = live_api::allowChat($inPath[3],$inPath[4]);
		if($ret){
			$r->result=array("code"=>0,"msg"=>"success");
		}else{
			$r->result=array("code"=>0,"msg"=>"already allowed");
		}
		return $r;
	}
	public function pageGetLiveEncodingTask($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->work_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
        if(empty($params->task_type)){
            $task_type = "";
        }else{
            $task_type = $params->task_type;
        }

        $db = new live_db;
        $db->clearLiveEncodingTask($params->work_id);

		while(true){
			$task = live_api::getOneLiveEncodingTask($params->work_id, $task_type);
			if(empty($task)){
				$ret->result->msg = "没有空闲任务";
				return $ret;
			}else{
				$a = live_api::checkOneLiveEncodingTask($task);
				if($a){
					continue;
				}else{
					$ret->result->code = 0;
					$ret->data = $task;
					return $ret;
				}
			}
		}
	}
	public function pageUpdateProcessForLiveEncoding($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->task_id) || empty($params->process)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
        if(isset($params->status)){
            $status = $params->status;
        }else{
            $status = false;
        }

		$db = new live_db;
		$db->setProcessForLiveEncoding($params->task_id, $params->process, $status);
		$ret->result->code = 0;
		return $ret;
	}
	public function pageDeleteLiveEncodingTask($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->task_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}

		$db = new live_db;
		$db->backupLiveEncodingTask($params->task_id);
		$db->deleteLiveEncodingTask($params->task_id);
		$ret->result->code = 0;
		return $ret;
	}
	public function pageAddLiveEncodingTask($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($params->user_id) || empty($params->plan_id) || empty($params->task_type)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
        if(empty($params->chat_files)){
            $chat_files = false;
        }else{
            $chat_files = $params->chat_files;
        }
		$db = new live_db;
        if("LIVE" == $params->task_type){
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "LIVE-O", $params->source_ip, $params->video_file, $chat_files);
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "LIVE-L", $params->source_ip, $params->video_file, $chat_files);
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "LIVE-H", $params->source_ip, $params->video_file, $chat_files);
        }else if("UPLOAD" == $params->task_type){
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "UPLOAD-L", false, false, false);
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "UPLOAD-H", false, false, false);
        }else if("REENCODE" == $params->task_type){
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "REENCODE-L", false, false, false);
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, "REENCODE-H", false, false, false);
        }else{
            $db->addLiveEncodingTask($params->user_id, $params->plan_id, $params->task_type, false, false, false);
        }
		$ret->result->code = 0;
		return $ret;
	}
	public function pageAddLiveChatFile($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(!isset($params->user_id) || empty($params->plan_id)  || empty($params->ip)|| empty($params->chat_file)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$db = new live_db;
		$db->addLiveChatFile($params->user_id, $params->plan_id, $params->ip, $params->chat_file);
		$ret->result->code = 0;
		return $ret;
	}
	public function pageGetLiveChatFileByPlan($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->plan_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$db = new live_db;
        $data = $db->getLiveChatFileByPlan($params->plan_id);
        if(!empty($data->items)){
            $ret->data = $data->items;
        }
        $ret->result->code = 0;
        return $ret;
	}
}
