<?php
class live_db{
	public static function InitDB($dbname="db_live",$dbtype="main"){
		redis_api::useConfig($dbname);
		$db = new SDb;
		$db->useConfig($dbname,$dbtype);
		return $db;
	}
	/**
	  * 根据token获取auth记录权限
	  **/
	public function getAuthByToken($token){
		$db = self::InitDB("db_live","query");
		$condition = array("pub_token"=>$token);
		return $ConnectID=$db->selectOne("t_live_publish_auth",$condition);
	}
	/**
	  * 根据uid获取auth记录权限
	  **/
	public function getAuthByUid($uid){
		$condition = array("fk_user"=>$uid);
		$db = self::InitDB("db_live","query");
		return $ConnectID=$db->selectOne("t_live_publish_auth",$condition);
	}
	public function delChatAuth($token){
		$db = self::InitDB("db_live");
		$condi = array('pub_token' => $token);
		return $db->delete("t_live_chat_auth",$condi);
	}
	public function setChatAuth($Auth){
		$db = self::InitDB("db_live");
		return $db->insert("t_live_chat_auth",$Auth,true);
	}
	public function getChatAuth($token){
		$condi = array('pub_token' => $token);
		$db = self::InitDB("db_live","query");
		return $db->selectOne("t_live_chat_auth",$condi);
	}
	public function getChatAuthByUserId($uid){
		$condi = array('fk_user' => $uid);
		$db = self::InitDB("db_live","query");
		return $db->selectOne("t_live_chat_auth",$condi);
	}
	/**
	  *
	  */
	public function setPublishAuth($uid,$Auth){
		$Auth['fk_user']=$uid;
		$db = self::InitDB("db_live");
		return $db->insert("t_live_publish_auth",$Auth,true);
	}
	public function getPublishByUserID($uid){
		$db = self::InitDB("db_live","query");
		$item = new stdclass;
		$item->uid = "fk_user";
		$item->user_token= "user_token";
		$item->user_ip= "user_ip";
		$item->plan_id= "fk_plan";
		$item->stream_name= "stream_name";
		$item->server_ip= "server_ip";
		$item->client_id= "client_id";
		$item->record_plan_id= "record_fk_plan";
		$item->user_ip= "user_ip";
		$item->app_name= "app_name";
		$item->live_call= "live_call";
		$item->last_updated = 'last_updated';
		$condition = array("fk_user"=>$uid);
		return $ConnectID=$db->selectOne("t_live_publish",$condition,$item);
	}
	public function getPublishByPlanID($plan_id){
		$db = self::InitDB("db_live","query");
		$item = new stdclass;
		$item->uid = "fk_user";
		$item->user_ip= "user_ip";
		$item->plan_id= "fk_plan";
		$item->record_plan_id= "record_fk_plan";
		$item->stream_name= "stream_name";
		$item->server_ip= "server_ip";
		$item->client_id= "client_id";
		$item->user_ip= "user_ip";
		$item->app_name= "app_name";
		$item->live_call= "live_call";
		$item->last_updated= "last_updated";
		$condition = array("fk_plan"=>$plan_id);
		return $ConnectID=$db->selectOne("t_live_publish",$condition,$item);
	}
	public function getPublishByRecordPlanID($record_plan_id){
		$db = self::InitDB("db_live","query");
		$item = new stdclass;
		$item->uid = "fk_user";
		$item->user_ip= "user_ip";
		$item->plan_id= "fk_plan";
		$item->record_plan_id= "record_fk_plan";
		$item->stream_name= "stream_name";
		$item->server_ip= "server_ip";
		$item->client_id= "client_id";
		$item->user_ip= "user_ip";
		$item->app_name= "app_name";
		$item->live_call= "live_call";
		$condition = array("record_fk_plan"=>$record_plan_id);
		return $ConnectID=$db->selectOne("t_live_publish",$condition,$item);
	}
	public function setPublish($uid,$Publish){
		$db = self::InitDB("db_live");
		$Publish['fk_user']=$uid;
		$r = $db->insert("t_live_publish",$Publish);
		if(!$r){
			$r = self::updatePublishByUid($uid,$Publish);
		}
		return $r;
	}
	public function updatePublishPlan($Publish,$token,$plan_id,$record_plan_id){
		$update=array();
		$update['user_token']=$token;
		$update['fk_plan']=$plan_id;
		if($record_plan_id){
			$update['record_fk_plan']=$record_plan_id;
		}
		$db = self::InitDB("db_live");
		return $db->update("t_live_publish",array(
			"server_ip"=>$Publish['server_ip'],
			"client_id"=>$Publish['client_id'],
			),$update
			);
	}
	public function updatePublish($Publish){
		$db = self::InitDB("db_live");
		return $db->update("t_live_publish",array(
			"server_ip"=>$Publish['server_ip'],
			"client_id"=>$Publish['client_id'],
			),array(
			"live_call"=>$Publish['live_call'],
			"last_updated"=>date("Y-m-d H:i:s")
			)
			);
	}
	public function updatePublishByUid($uid,$Publish){
		$db = self::InitDB("db_live");
		return $db->update("t_live_publish",array(
			"fk_user"=>$uid,
			),$Publish);
	}
	/**
	 * $cdn_type = "VIDEO" | "CHAT" | "HLS"
	 * VIDEO 直播视频
	 * CHAT 聊天
	 * HLS 点播视频
	 */
	public function getCdnDispatch($area_name,$op_name,$cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$condition = array("area_name"=>$area_name,"op_name"=>$op_name,"status"=>1,"cdn_type"=>$cdn_type);
		$key =md5( "live_db.t_live_play_cdn_dispatch_area.$area_name.$op_name.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$v = $db->selectOne("t_live_play_cdn_dispatch_area",$condition);
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function getCdnDispatchUser($uid,$cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$condition = array("fk_user"=>$uid,"status"=>1,"cdn_type"=>$cdn_type);
		$key =md5( "live_db.t_live_play_cdn_dispatch_user.$uid.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$v = $db->selectOne("t_live_play_cdn_dispatch_user",$condition);
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function getCdnDispatchPlan($plan_id,$cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$condition = array("fk_plan"=>$plan_id,"status"=>1,"cdn_type"=>$cdn_type);
		$key =md5( "live_db.t_live_play_cdn_dispatch_plan.$plan_id.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$v = $db->selectOne("t_live_play_cdn_dispatch_plan",$condition);
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function getCdnDispatchCourse($course_id,$cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$condition = array("fk_course"=>$course_id,"status"=>1,"cdn_type"=>$cdn_type);
		$key =md5( "live_db.t_live_play_cdn_dispatch_course.$course_id.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$v = $db->selectOne("t_live_play_cdn_dispatch_course",$condition);
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function getCdnDispatchTotal($total,$cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$condition = array("user_total<=$total","status"=>1,"cdn_type"=>$cdn_type);
		$key =md5( "live_db.t_live_play_cdn_dispatch_total.$total.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$v = $db->selectOne("t_live_play_cdn_dispatch_total",$condition,"","", $order=array("user_total"=>"desc"));
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function getChatCdnByPlanId($plan_id=0){
		$db = self::InitDB("db_live","query");
		return $db->selectOne("t_live_play_cdn",$condition=array("status"=>1,"cdn_type"=>"CHAT"));
	}
	public function getCdn($type,$cdn_id){
		$cdns = self::listCdn($type);
		if($cdns){
			foreach($cdns->items as $cdn){
				if($cdn['pk_cdn']==$cdn_id){
					return $cdn;
				}
			}
		}
		return false;
	}
	public function getDefaultCdn($type){
		$cdns = self::listCdn($type);
		if($cdns){
			foreach($cdns->items as $cdn){
				if(!empty($cdn['is_default'])){
					return $cdn;
				}
			}
		}
		return false;
	}
	/**
	 * $cdn_type = "VIDEO" | "CHAT" | "HLS"
	 * VIDEO 直播视频
	 * CHAT 聊天
	 * HLS 点播视频
	 */
	public function listCdn($cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$key =md5( "live_db.t_live_play_cdn.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$condition = array("status"=>1,"cdn_type"=>$cdn_type);
		$v = $db->select("t_live_play_cdn",$condition);
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function listCdnDispatch($cdn_type="VIDEO"){
		$db = self::InitDB("db_live","query");
		$condition = array("status"=>1,"cdn_type"=>$cdn_type);
		$key =md5( "live_db.t_live_play_cdn_dispatch_area.$cdn_type");
		$v = redis_api::get($key);
		if($v!==false){return $v;}
		$v = $db->select("t_live_play_cdn_dispatch_area",$condition);
		if(!$v)$v=0;
		redis_api::set($key,$v,60);
		return $v;
	}
	public function getVideoPublishCdn(){
		$condition = array("status"=>1,"publish_type"=>"VIDEO");
		$db = self::InitDB("db_live","query");
		return $db->selectOne("t_live_publish_cdn",$condition);
	}
	public function listVideoPublishCdn(){
		$db = self::InitDB("db_live","query");
		$condition = array("status"=>1,"publish_type"=>"VIDEO");
		return $db->select("t_live_publish_cdn",$condition);
	}
	public function getChatPublishCdn(){
		$db = self::InitDB("db_live","query");
		$condition = array("status"=>1,"publish_type"=>"CHAT");
		return $db->selectOne("t_live_publish_cdn",$condition);
	}
	public function listChatPublishCdn(){
		$db = self::InitDB("db_live","query");
		$condition = array("status"=>1,"publish_type"=>"CHAT");
		return $db->select("t_live_publish_cdn",$condition);
	}
	//{{{ 记录视频文件，需要从主库里读写
	public function addRecordFile($File){
		$db = self::InitDB("db_live");
		return $db->insert("t_live_record_file",$File);
	}
	public function listRecordFile($plan_id){
		$db = self::InitDB("db_live");
		$item=array("user_id"=>"fk_user","file_id"=>"pk_file","plan_id"=>"fk_plan","filename","filesize","duration","bitrate","video_width","video_height","video_framerate","last_updated", "source_ip");
		return $db->select("t_live_record_file",array("fk_plan"=>$plan_id,"status"=>0),$item,"",
			$order=array("filetime"=>"asc","pk_file"=>"asc")
		);
	}
	public function delRecordFile($uid,$plan_id){
		$db = self::InitDB("db_live");
		$condi=array("fk_user"=>$uid,"fk_plan"=>$plan_id);
		return $db->update("t_live_record_file",$condi,array("status"=>-1));
	}
	public function addUploadFile($File){
		$db = self::InitDB("db_live");
		return $db->insert("t_live_upload_file",$File);
	}
	public function setUploadFile($file_id,$File){
		$db = self::InitDB("db_live");
		return $db->update("t_live_upload_file",array("pk_file"=>$file_id),$File);
	}
	public function getUploadFile($file_id){
		$db = self::InitDB("db_live");
		$item=array("user_id"=>"fk_user","file_id"=>"pk_file","plan_id"=>"fk_plan","filename","filename_org","filesize","duration","bitrate","video_width","video_height","video_framerate","last_updated","encoding_status");
		return $db->selectOne("t_live_upload_file",array("pk_file"=>$file_id),$item);
	}
	public function listUploadFile($plan_id){
		$db = self::InitDB("db_live");
		$condi['fk_plan']=$plan_id;
		$condi['status']=0;
		$item=array("user_id"=>"fk_user","file_id"=>"pk_file","plan_id"=>"fk_plan","filename","filename_org","filesize","duration","bitrate","video_width","video_height","video_framerate","last_updated","status","encoding_status", "source_ip");
		return $db->select("t_live_upload_file",$condi,$item);
	}
	//}}}
	public function addUploadTask($task){
		$db = self::InitDB("db_live");
		return $db->insert("t_live_encoding_task",$task);
	}
	public function setUploadTask($task_id,$task){
		$db = self::InitDB("db_live");
		return $db->update("t_live_encoding_task",array("pk_task"=>$task_id),$task);
	}
	public function getUploadTask($task_id){
		$db = self::InitDB("db_live","query");
		$item=array("task_id"=>"pk_task","user_id"=>"fk_user","plan_id"=>"fk_plan","task_type","status");
		return $db->selectOne("t_live_encoding_task",array("pk_task"=>$task_id),$item);
	}

    /*
     * 只查询标清
     */
	public function getUploadTaskByUidPid($user_id,$plan_id){
		$item=array("task_id"=>"pk_task","user_id"=>"fk_user","plan_id"=>"fk_plan","task_type","status");
		$db = self::InitDB("db_live","query");
		if(!empty($user_id)){
			$cond['fk_user']   = $user_id;
            $cond['task_type'] = 'UPLOAD-L';
		}
		$cond['fk_plan']=$plan_id;
		return $db->selectOne("t_live_encoding_task",$cond,$item);
	}
	public function getUploadTaskByPids($planIds){
		$item=array("task_id"=>"pk_task","user_id"=>"fk_user","plan_id"=>"fk_plan","task_type","status");
		$db = self::InitDB("db_live","query");

		$cond = " task_type = 'UPLOAD-L' and fk_plan IN ({$planIds})";
		return $db->select("t_live_encoding_task",$cond,$item);
	}
	public function listUploadTask($limit = 1){
		$db = self::InitDB("db_live","query");
		$condi['status']=0;
		$db->setLimit($limit);
		$item=array("task_id"=>"pk_task","user_id"=>"fk_user","plan_id"=>"fk_plan","task_type","status");
		return $db->select("t_live_encoding_task",$condi,$item);
	}
    public function clearLiveEncodingTask($work_id){
        $db = self::InitDB("db_live");
        $sql = "update t_live_encoding_task set work_id=null where work_id='$work_id'";
		$ret = $db->execute($sql);
		return $ret;
    }
	public function robOneLiveEncodingTask($work_id, $task_type){
        $db = self::InitDB("db_live");
        $extra = "";
        if(!empty($task_type)){
            $extra = "and task_type='$task_type'";
        }
		$sql = "update t_live_encoding_task set work_id='$work_id' where work_id is null $extra order by task_type desc, pk_task limit 1";
		$ret = $db->execute($sql);
		return $ret;
	}
	public function getOneLiveEncodingTaskByWorkId($work_id){
		$db = self::InitDB("db_live");
		$item = new stdclass;
		$item->task_id = "pk_task";
		$item->user_id = "fk_user";
		$item->plan_id = "fk_plan";
		$item->task_type = "task_type";
		$item->status = "status";
		$item->last_updated = "last_updated";
		$item->source_ip = "source_ip";
		$item->video_file = "video_file";
		$item->chat_files = "chat_files";
		$item->process = "process";
		$item->work_id = "work_id";
		$item->createtime = "createtime";
		$table = array("t_live_encoding_task");
		$condition = "work_id='$work_id'";
		$orderby = "task_type desc, pk_task";
		$v = $db->selectOne($table, $condition, $item, "", $orderby);
		return $v;
	}
	public function getOneLiveEncodingFreeTask($task_type){
		$db = self::InitDB("db_live");
		$item = new stdclass;
		$item->task_id = "pk_task";
		$item->user_id = "fk_user";
		$item->plan_id = "fk_plan";
		$item->task_type = "task_type";
		$item->status = "status";
		$item->last_updated = "last_updated";
		$item->source_ip = "source_ip";
		$item->video_file = "video_file";
		$item->chat_files = "chat_files";
		$item->process = "process";
		$item->work_id = "work_id";
		$item->createtime = "createtime";
		$table = array("t_live_encoding_task");
		$condition = "work_id is null";
        if(!empty($task_type)){
            $condition .= " and task_type='$task_type'";
        }
		$orderby = "task_type desc, pk_task";
		$v = $db->selectOne($table, $condition, $item, "", $orderby);
		return $v;
	}
	public function getOneLiveEncodingTaskByPlan($plan_id){
		$db = self::InitDB("db_live", "query");
		$item = new stdclass;
		$item->task_id = "pk_task";
		$item->user_id = "fk_user";
		$item->plan_id = "fk_plan";
		$item->task_type = "task_type";
		$item->status = "status";
		$item->last_updated = "last_updated";
		$item->source_ip = "source_ip";
		$item->video_file = "video_file";
		$item->chat_files = "chat_files";
		$item->process = "process";
		$item->work_id = "work_id";
		$item->createtime = "createtime";
		$table = array("t_live_encoding_task");
		$condition = "fk_plan=$plan_id";
		$orderby = "task_type desc, pk_task";
		$v = $db->selectOne($table, $condition, $item, "", $orderby);
		return $v;
	}
	public function setWorkIdForLiveEncodingFree($task_id, $work_id){
		$db = self::InitDB("db_live");
		$table = array("t_live_encoding_task");
		$condition = "pk_task=$task_id and work_id is null";
		$item = array("work_id"=>$work_id);
		$ret = $db->update($table, $condition, $item);
		return $ret;
	}
	public function setWorkIdForLiveEncoding($task_id, $work_id){
		$db = self::InitDB("db_live");
		$table = array("t_live_encoding_task");
		$condition = "pk_task=$task_id";
		$item = array("work_id"=>$work_id);
		$ret = $db->update($table, $condition, $item);
		return $ret;
	}
	public function setProcessForLiveEncoding($task_id, $process, $status){
		$db = self::InitDB("db_live");
		$table = array("t_live_encoding_task");
		$condition = "pk_task=$task_id";
        $item = array("process"=>$process);
        if(false !== $status){
            $item["status"] = $status;
        }
		$ret = $db->update($table, $condition, $item);
		return $ret;
	}
	public function backupLiveEncodingTask($task_id){
		$db = self::InitDB("db_live");
		$sql = "insert ignore into t_live_encoding_task_backup select * from t_live_encoding_task where pk_task=$task_id";
		$ret = $db->execute($sql);
		return $ret;
	}
	public function deleteLiveEncodingTask($task_id){
		$db = self::InitDB("db_live");
		$table = array("t_live_encoding_task");
		$condition = "pk_task=$task_id";
		$ret = $db->delete($table, $condition);
		return $ret;
	}
	public function addLiveEncodingTask($user_id, $plan_id, $task_type, $source_ip, $video_file, $chat_files){
		$db = self::InitDB("db_live");
		$table = array("t_live_encoding_task");
		$data = new stdclass;
		$data->fk_user = $user_id;
		$data->fk_plan = $plan_id;
		$data->task_type = $task_type;
		if(!empty($source_ip)){
			$data->source_ip = $source_ip;
		}
		if(!empty($video_file)){
			$data->video_file = $video_file;
		}
		if(!empty($chat_files)){
			$data->chat_files = $chat_files;
		}
        $data->createtime = date("Y-m-d H:i:s");
		$ret = $db->insert($table, $data);
		return $ret;
	}
    public function addLiveChatFile($user_id, $plan_id, $ip, $chat_file){
		$db = self::InitDB("db_live");
		$table = array("t_live_chat_file");
		$data = new stdclass;
		$data->fk_user = $user_id;
		$data->fk_plan = $plan_id;
		$data->ip = $ip;
		$data->chat_file = $chat_file;
		$ret = $db->insert($table, $data);
		return $ret;
    }
    public function getLiveChatFileByPlan($plan_id){
		$db = self::InitDB("db_live", "query");
		$table = array("t_live_chat_file");
		$item = new stdclass;
		$item->chat_id = "pk_chat";
		$item->user_id = "fk_user";
		$item->plan_id = "fk_plan";
		$item->ip = "ip";
		$item->chat_file = "chat_file";
		$item->status = "status";
		$item->createtime = "createtime";
		$condition = "fk_plan=$plan_id and status>=0";
		$orderby = "pk_chat";
		$ret = $db->select($table, $condition, $item, "", $orderby);
		return $ret;
    }
}
