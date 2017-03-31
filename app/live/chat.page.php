<?php
class live_chat{
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
		if($_REQUEST['call']=="play" && !empty($_REQUEST['app'])){
			//Auth鉴权 ,根据token获取用户ID,判断有没有播放权限(有没有购买)
			//根据planid获取发布端publish的信息
			//再根据app,stream_name等信息，再次鉴定
			//鉴定成功，写在线表，然后播放
		}
	}
	/**
	 * 载联聊天校验，一个用户同一时间只能发布一个视频
	 * */
	public function pagePublish($inPath){
		//{{{验证token权限
		if(empty($_REQUEST['token'])){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		$info = live_api::getInfo($_REQUEST['token']);
		if(empty($info->token)){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		$db = new live_db;
		$auth = $db->getChatAuth($info->token);
		if(empty($auth['fk_user'])){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		if($_REQUEST['app'] !=$auth['app_name'] || $_REQUEST['app']!="chat" || $_REQUEST['name']!=$auth['stream_name']){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		//}}}
	}
	/**
	 * 结束发布
	 * */
	public function pagePublishDone($inPath){
		if(empty($_REQUEST['token'])){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		$info = live_api::getInfo($_REQUEST['token']);
		if(empty($info->token)){
			header("HTTP/1.0 404 Not Found");
			exit;
		}
		message_http::addStopChatPublishSignal($info->user_id,$info->token,$info->plan_id);
		//$db = new live_db;
		//$auth = $db->getChatAuth($_REQUEST['token']);
		//if(!empty($auth)){
		//	message_http::addStopChatPublishSignal($auth['fk_user'],$_REQUEST['token'],$_REQUEST['planid']);
		//}
	}
}
