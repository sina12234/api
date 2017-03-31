<?php
class live_video{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	private function error404(){
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	/**
	 * 显示API的说明
	 * */
	public function pageEntry($inPath){
	}
	/**
	 * 播放权限
	 * */
	public function pagePlay($inPath){
		//参数鉴定，主要是token和planid
		if(!empty($_REQUEST['app'])){
			if(empty($_REQUEST['token'])){
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			$info = live_api::getInfo($_REQUEST['token']);
			if(empty($info)){
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			if($info->timestamp <= time()-3600){//最长1小时
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			$live_db = new live_db;
			$publish_info = $live_db->getPublishByPlanID($info->plan_id);
			if(empty($publish_info) || $publish_info['stream_name'] != $info->stream){
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			$db = new course_db;
			$plan_info = $db->getPlan($info->plan_id);
			if(empty($plan_info)){
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			$perm = course_api::verifyPlan($info->user_id,$info->plan_id,$apply,$try_info);
			if($perm === false){
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			$db->addPlanUser(
				$plan_info['course_id'],
				$plan_info['class_id'],
				$plan_info['plan_id'],
				$info->user_id,
				$info->token
			);
		} 
	}
	/**
	 * HLS播放
	 * 更新t_live_play_hls播放情况
	 * */
	public function pageHlsConnect($inPath){
		return $this->pagePlayHls($inPath);
	}
	public function pagePlayHls($inPath){
		//Auth鉴权
		//header("HTTP/1.0 404 Not Found");
		if(empty($_SERVER['HTTP_X_ORIGINAL_URI'])){
			//404
		}
		$url = $_SERVER['HTTP_X_ORIGINAL_URI'];
		$parsed_url = parse_url($url);
		if(basename($parsed_url['path'])=='index.m3u8'){
			parse_str($parsed_url['query'],$params);
			if(empty($params['token'])){
				//404
			}
			$_REQUEST['token']=$params['token'];
			return $this->pageConnect($inPath);
		};
	}
	/**
	 * 发布视频校验，一个用户同一时间只能发布一个视频
	 * */
	public function pagePublish($inPath){
		//{{{验证token权限
		if(empty($_REQUEST['token'])){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		$auth = live_api::getAuthByToken($_REQUEST['token']);
		if(empty($auth['fk_user'])){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		if($_REQUEST['app'] !=$auth['app_name'] || $_REQUEST['name']!=$auth['stream_name']){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		//}}}
		//{{{判断是否正在发布
		$db_r = live_api::getPublishByUserID($auth['fk_user']);
		if(!empty($db_r['live_call']) && ($db_r['live_call']=="publish" || $db_r['live_call']=="update_publish")){
			if(strtotime($db_r['last_updated']) > time()-40){
				header("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		//}}}
		$r = live_api::setPublish($auth['fk_user']);
		if($r===false){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
	}
	/**
	 * 结束播放
	 * */
	public function pagePlayDone($inPath){
		if(!empty($_REQUEST['app'])){
			if(empty($_REQUEST['token'])){
				exit;
			}
			$info = live_api::getInfo($_REQUEST['token']);
			if(empty($info)){
				exit;
			}
			$db = new course_db;
			$r = $db->delPlanUserByToken($info->token);
		}
	}
	/**
	 * 结束发布
	 * */
	public function pagePublishDone($inPath){
		live_api::updatePublish();
		//设置状态
		if(empty($_REQUEST['token'])){
			return ;
		}
		$auth = live_api::getAuthByToken($_REQUEST['token']);
		if(empty($auth['fk_user'])){
			return ;
		}
		//{{{
		$publish = live_api::getPublishByUserID($auth['fk_user']);
		if(!empty($publish['plan_id'])){
			$update_r = course_api::setPlanStatus($publish['plan_id'],course_status::finished);
			//上课结束
			message_api::startCloseClass($publish['plan_id'], $publish["uid"], $publish["user_token"], false);
		}
		//}}}
	}
	/**
	 * 结束录制
	 * */
	public function pageRecordDone($inPath){
		//header("HTTP/1.0 404 Not Found");
	}
	/**
	 * 心跳数据
	 * */
	public function pagePublishUpdate($inPath){
		if($_REQUEST['call']=="update_publish"){
			live_api::updatePublish();
		}
	}
	public function pagePlayUpdate($inPath){
		if($_REQUEST['call']=="update_play" && !empty($_REQUEST['app'])){
		}
	}
}
