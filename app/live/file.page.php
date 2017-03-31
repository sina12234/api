<?php
/**
  * 直播权限设置，文档地址 http://wiki.gn100.com/doku.php?id=docs:api:live
  * @author hetao 2014/12/16
  */
class live_file{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	/**
	  * 保存
	  */
	public function pageSaveRecord($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		if(empty($inPath[4])){
			$r->result=array("code"=>-2);
			return $r;
		}
		$uid = $inPath[3];
		$plan_id = $inPath[4];
		$db = new live_db;
		$Video=array();
		$Video['fk_user']=$uid;
		$Video['fk_plan']=$plan_id;
		$params=SJson::decode(utility_net::getPostData());
		if(!empty($params->filename)) 	$Video['filename']=$params->filename;
		if(!empty($params->source_ip)) 	$Video['source_ip']=$params->source_ip;
		if(!empty($params->filetime)) 	$Video['filetime']=$params->filetime;
		if(!empty($params->filesize)) 	$Video['filesize']=$params->filesize;
		if(!empty($params->bitrate))	$Video['bitrate']=$params->bitrate;
		if(!empty($params->duration)) 	$Video['duration']=$params->duration;
		if(!empty($params->video_width)) $Video['video_width']=$params->video_width;
		if(!empty($params->video_height)) $Video['video_height']=$params->video_height;
		if(!empty($params->video_framerate)) $Video['video_framerate']=$params->video_framerate;
		$db_r = $db->addRecordFile($Video);
		if($db_r!==false){
			$r->result=array("code"=>0,"msg"=>$db_r);
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	public function pageListRecord($inPath){
		$r= new stdclass;
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		if(empty($inPath[4])){
			$r->result=array("code"=>-2);
			return $r;
		}
		$plan_id = $inPath[4];
		$db = new live_db;
		$db_r = $db->listRecordFile($plan_id);
		if(!empty($db_r->items)){
			$r->data=$db_r->items;
		}else{
			$r->data=array();
		}
		return $r;
	}
	/**
	 * 获取一个
	 */
	public function pageGetUpload($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		$file_id = $inPath[3];
		$db = new live_db;
		$db_r = $db->getUploadFile($file_id);
		if($db_r!==false){
			$r->data=$db_r;
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	/**
	  * 保存
	  */
	public function pageSaveUpload($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		if(empty($inPath[4])){
			$r->result=array("code"=>-2);
			return $r;
		}
		$uid = $inPath[3];
		$plan_id = $inPath[4];
		$db = new live_db;
		$Video=array();
		$Video['fk_user']=$uid;
		$Video['fk_plan']=$plan_id;
		$params=SJson::decode(utility_net::getPostData());
		if(!empty($params->filename)) 	$Video['filename']=$params->filename;
		if(!empty($params->source_ip)) 	$Video['source_ip']=$params->source_ip;
		if(!empty($params->filename_org)) 	$Video['filename_org']=$params->filename_org;
		if(!empty($params->filesize)) 	$Video['filesize']=$params->filesize;
		if(!empty($params->bitrate))	$Video['bitrate']=$params->bitrate;
		if(!empty($params->duration)) 	$Video['duration']=$params->duration;
		if(!empty($params->video_width)) $Video['video_width']=$params->video_width;
		if(!empty($params->video_height)) $Video['video_height']=$params->video_height;
		if(!empty($params->video_framerate)) $Video['video_framerate']=$params->video_framerate;
		if(!empty($params->encoding_status )) $Video['encoding_status ']=$params->encoding_status ;
		$db_r = $db->addUploadFile($Video);
		if($db_r!==false){
			$r->result=array("code"=>0,"msg"=>$db_r);
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	public function pageSetUpload($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		$file_id = $inPath[3];
		$db = new live_db;
		$params=SJson::decode(utility_net::getPostData());
		$Video=array();
		if(isset($params->encoding_status )) $Video['encoding_status']=$params->encoding_status ;
		if(isset($params->status)) $Video['status']=$params->status;
		if(isset($params->filename)) $Video['filename']=$params->filename;
		if(isset($params->source_ip)) $Video['source_ip']=$params->source_ip;
		if(empty($Video)){
			$r->result=array("code"=>-2);
			return $r;
		}
		$db_r = $db->setUploadFile($file_id,$Video);
		if($db_r!==false){
			$r->result=array("code"=>0,"msg"=>$db_r);
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	public function pageListUpload($inPath){
		$r= new stdclass;
		if(empty($inPath[4])){
			$r->result=array("code"=>-2);
			return $r;
		}
		$plan_id = $inPath[4];
		$db = new live_db;
		$db_r = $db->listUploadFile($plan_id);
		if(!empty($db_r->items)){
			$r->data=$db_r->items;
		}else{
			$r->data=array();
		}
		return $r;
	}
/*
	public function pageListUploadByEncodingStatus($inPath){
		$r= new stdclass;
		$db = new live_db;
		$params=SJson::decode(utility_net::getPostData());
		$condi=array();
		if(!isset($params->encoding_status)){
			$r->result=array("code"=>-1);
			return $r;
		}
		$db_r = $db->listUploadFileByEncodingStatus($params->encoding_status);
		if(!empty($db_r->items)){
			$r->data=$db_r->items;
		}else{
			$r->data=array();
		}
		return $r;
	}
*/
	/**
	  * 保存
	  */
	public function pageAddUploadTask($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		if(empty($inPath[4])){
			$r->result=array("code"=>-2);
			return $r;
		}
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->type)){
			$r->result=array("code"=>-4);
			return $r;
		}
		if($params->type != "RECORD" && $params->type !="UPLOAD"){
			$r->result=array("code"=>-3);
			return $r;
		}
		$uid = $inPath[3];
		$plan_id = $inPath[4];
		$db = new live_db;
		$Video=array();
		$Video['fk_user']=$uid;
		$Video['fk_plan']=$plan_id;
		$Video['task_type']="UPLOAD-L";
		$db_r = $db->addUploadTask($Video);
		if($db_r!==false){
			$r->result=array("code"=>0,"msg"=>$db_r);
            $Video['task_type']="UPLOAD-H";
            $db->addUploadTask($Video);
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	public function pageGetUploadTask($inPath){
		$r= new stdclass;
		$r->data=array();
		$uid = 0;
		if(!empty($inPath[3])){
			$uid = $inPath[3];
		}
		if(empty($inPath[4])){
			$r->result=array("code"=>-2);
			return $r;
		}
		$plan_id = $inPath[4];
		$db = new live_db;
		$db_r = $db->getUploadTaskByUidPid($uid,$plan_id);

		if($db_r!==false){
			$r->data=$db_r;
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	public function pageGetUploadTasks($inPath){
		$r= new stdclass;
		$r->data=array();
	
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['planIds'])){
			$r->result=array("code"=>-2);
			return $r;
		}
	
		$db = new live_db;
		$db_r = $db->getUploadTaskByPids($params['planIds']);

		if(!empty($db_r->items)){
			$r->data=$db_r->items;
		}else{
			$r->result=array("code"=>-3);
		}
		
		return $r;
	}

	public function pageSetUploadTask($inPath){
		$r= new stdclass;
		$r->data=array();
		if(empty($inPath[3])){
			$r->result=array("code"=>-1);
			return $r;
		}
		$task_id = $inPath[3];
		$db = new live_db;
		$params=SJson::decode(utility_net::getPostData());
		$Video=array();
		if(isset($params->status)) $Video['status']=$params->status;
		if(!empty($params->type)){
			if($params->type == "RECORD" || $params->type =="UPLOAD"){
				$Video['task_type']=$params->type;
			}
		}
		if(empty($Video)){
			$r->result=array("code"=>-2);
			return $r;
		}
		$task = $db->getUploadTask($task_id);
		if(empty($task)){
			$r->result=array("code"=>-5);
			return $r;
		}
		$db_r = $db->setUploadTask($task_id,$Video);
		if($db_r!==false){
			$r->result=array("code"=>0,"msg"=>$db_r);
		}else{
			$r->result=array("code"=>-3);
		}
		return $r;
	}
	public function pageListUploadTask($inPath){
		$r= new stdclass;
		$limit = 1;
		$db = new live_db;
		$db_r = $db->listUploadTask($limit);
		if(!empty($db_r->items)){
			$r->data=$db_r->items;
		}else{
			$r->data=array();
		}
		return $r;
	}

    public function pageGetUploadList()
    {
        $params=SJson::decode(utility_net::getPostData(), true);

        $userId = !empty($params['userId']) && (int)($params['userId']) ? (int)($params['userId']) : 0;
        $planStr = !empty($params['planIdStr']) && trim($params['planIdStr']) ? trim($params['planIdStr']) : '';
        if (!$userId || !$planStr) return api_func::setMsg(1000);

        $res = live_db_uploadFileDao::getUploadList($userId, $planStr);
        if (empty($res->items)) return api_func::setMsg(3002);

        return api_func::setData($res->items);
    }
}
