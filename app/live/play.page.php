<?php
/**
  * 直播权限设置，文档地址 http://wiki.gn100.com/doku.php?id=docs:api:live
  * @author hetao 2014/12/16
  */
class live_play{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	/**
	  * 获取播放地址
	  */
	public function pageGetLiveUrl($inPath){
		$r = new stdclass;
		$r->data=array();
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id) || empty($params->ip)){
			return array("result"=>array("code"=>-1));
		}

		$uid = !empty($params->user_id) ? $params->user_id :0;
		$scheme = !empty($params->scheme) ? $params->scheme :"";

		$db = new live_db;
		$publish = $db->getPublishByPlanID($params->plan_id);
		if(empty($publish['stream_name'])){
			return array("result"=>array("code"=>-2,"msg"=>"plan_id error"));
		}
		$uptime = strtotime($publish['last_updated']);
		$stream_name= $publish['stream_name'];
		$filename="/play/$uptime/$stream_name/index.m3u8";
		$r->data['stream_name_v2'] = $filename;//cdn加速名

		$r->data['stream_name'] = $publish['stream_name'];
		$r->data['app_name'] = $publish['app_name'];
		$r->data['live_call'] = $publish['live_call'];

		//获取所有cdn名
		$cdns = $db->listCdn("LIVE-RTMP");
		self::cdnDispatch($cdns->items,"LIVE-RTMP",$params->ip, $uid, $params->plan_id);
		foreach($cdns->items as $cdn){
			if(!empty($cdn['cdn_provider'])){
				$rtmp_name = "rtmp_{$cdn['cdn_provider']}";
			}else{
				$rtmp_name = "rtmp";
			}
			$r->data['cdn_rtmp'][]=array(
				"name"=>$cdn['cdn_name'],
				"rtmp"=>$rtmp_name,
				"default"=>(int)$cdn['is_default'],
			);
			$r->data[$rtmp_name] = live_cdn::getRTMP($cdn,$cdn['cdn_provider'],$stream_name);
		}
		//获取所有cdn名
		$cdns = $db->listCdn("LIVE-HLS");
		self::cdnDispatch($cdns->items,"LIVE-HLS",$params->ip, $uid, $params->plan_id);
		foreach($cdns->items as $cdn){
			if(!empty($cdn['cdn_provider'])){
				$hls_name = "hls_{$cdn['cdn_provider']}";
			}else{
				$hls_name = "hls_v2";
			}
			$r->data['cdn_hls'][]=array(
				"name"=>$cdn['cdn_name'],
				"hls"=>$hls_name,
				"default"=>(int)$cdn['is_default'],
			);
			if($hls_name=="hls_v2"){
				$r->data[$hls_name] = live_cdn::getHLS($cdn,$cdn['cdn_provider'],$filename,$scheme);
			}elseif($cdn['cdn_provider']=="chinacache"){//chinacache视频
				$r->data[$hls_name] = live_cdn::getHLS($cdn,$cdn['cdn_provider'],"/play/{$stream_name}/index.m3u8",$scheme);
			}elseif($cdn['cdn_provider']=="cc"){//cc视频
				$r->data[$hls_name] = live_cdn::getHLS($cdn,$cdn['cdn_provider'],"/play/{$stream_name}.m3u8",$scheme);
			}else{
				$r->data[$hls_name] = live_cdn::getHLS($cdn,$cdn['cdn_provider'],"/play/{$stream_name}.m3u8",$scheme);
			}
			//old hls
			if(!isset($r->data['hls'])){
				$r->data["hls"] = live_cdn::getHLS_OLD($cdn,$cdn['cdn_provider'],$stream_name,$scheme);
			}
		}

		$cdn_info = $this->getCdn("CHAT-RTMP",$params->ip);
		$r->data['chat']['cdn_id']=$cdn_info['cdn_id'];
		$r->data['chat']['host']=$cdn_info['host'];

		$chat_publish = $db->getChatPublishCdn();
		if(!empty($chat_publish['host_name'])){
			$r->data['host_chat_publish']=$chat_publish['host_name'];
		}


		return $r;
	}
	public function pageGetVodUrl($inPath){
		$r = new stdclass;
		$r->data=array();
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id) || empty($params->ip)){
			return array("result"=>array("code"=>-1));
		}
		$uid = !empty($params->user_id) ? $params->user_id :0;
		$scheme = !empty($params->scheme) ? $params->scheme :"";
		$video_db = new video_db;
		$course_db = new course_db;
		$plan_info = $course_db->getPlan($params->plan_id);
		if(empty($plan_info)){
			return array("result"=>array("code"=>-2,"msg"=>"plan_id error"));
		}
		$video_info = $video_db->getVideo($plan_info['video_id']);
		if(!empty($video_info)){
			$uptime = strtotime($video_info['last_updated']);
			$video_uid = $video_info['user_id'];
			$ip = utility_ip::toLong($video_info['ip']);
			$vid = $video_info['video_id'];
			if(empty($video_info['segs'])){
				$video_info['segs'] = array(array(0,(int)$video_info['totaltime']));
			}else{
				$video_info['segs'] = SJson::decode($video_info['segs']);
				//转成int类型
				foreach($video_info['segs'] as &$sg){
					foreach($sg as $k=>$v){
						$sg[$k]=(int)$v;
					}
				}
			}
			$segs =str_replace("=","",base64_encode(SJson::encode($video_info['segs'])));
			$filename = "/hls/{$uptime}/{$video_uid}_{$vid}_{$ip}/{$segs}/index.m3u8";
			$r->data['stream_name'] = $video_info['filename'];
			$r->data['stream_name_v2'] = $filename;//cdn加速名
			$r->data['updatetime'] = $video_info['last_updated'];
			$r->data['video_id'] = $video_info['video_id'];
			$r->data['segs'] = $video_info['segs'];
			$r->data['has_type'] = $video_info['has_type'];
			$r->data['finish_type'] = $video_info['finish_type'];
		//获取所有cdn名
		$db = new live_db;
		$cdns = $db->listCdn("VOD-HLS");
		self::cdnDispatch($cdns->items,"VOD-HLS",$params->ip, $uid, $params->plan_id);
		foreach($cdns->items as $cdn){
			if(!empty($cdn['cdn_provider'])){
				$hls_name = "hls_{$cdn['cdn_provider']}";
			}else{
				$hls_name = "hls_v2";
			}
			$r->data['cdn_hls'][]=array(
				"name"=>$cdn['cdn_name'],
				"hls"=>$hls_name,
				"default"=>(int)$cdn['is_default'],
			);
			if($hls_name=="hls_v2"){
				$r->data[$hls_name] = live_cdn::getVODHLS($cdn,$cdn['cdn_provider'],$filename,$scheme,$video_info);
			}else{
				$r->data[$hls_name] = live_cdn::getVODHLS($cdn,$cdn['cdn_provider'],$filename,$scheme,$video_info);
			}
			//old hls
			if(!isset($r->data['hls'])){
				$r->data['hls'] = live_cdn::getVODHLS_OLD($cdn,$cdn['cdn_provider'],$video_info['filename'],$scheme,$video_info);
			}
		}

		}

		return $r;
	}
	/**
	 * 根据 $ip,$uid,$plan_id 更改默认cdn,可以指定不同的定向逻辑
	 * @param $ip string
	 * @param $type HLS | VIDEO //点播视频 ，或者 直播视频
	 * @return array("host"=>"","cdn_id"=>0)
	 */
	private function cdnDispatchSetDefault(array &$cdns, $cdn_id){
		$exists=false;
		foreach($cdns as $cdn){
			if($cdn['pk_cdn']==$cdn_id){
				$exists=true;
			}
		}
		if($exists){
			foreach($cdns as &$cdn){
				if($cdn['pk_cdn'] == $cdn_id){
					$cdn['is_default']=1;
					break;
				}else{
					$cdn['is_default']=0;
				}
			}
		}
		return true;
	}
	private function cdnDispatch(array &$cdns, $type, $ip="", $uid="", $plan_id=""){
		$db = new live_db;
		if(!empty($uid)){
			$cdn_info = $db->getCdnDispatchUser($uid,$type);
			if(!empty($cdn_info['fk_cdn'])){
				return self::cdnDispatchSetDefault($cdns, $cdn_info['fk_cdn']);
			}
		}
		if(!empty($plan_id)){
			$cdn_info = $db->getCdnDispatchPlan($plan_id,$type);
			if(!empty($cdn_info['fk_cdn'])){
				return self::cdnDispatchSetDefault($cdns, $cdn_info['fk_cdn']);
			}
			//课程和报名数
			$course_db = new course_db;
			$plan = $course_db->getPlan($plan_id);
			$cdn_info = $db->getCdnDispatchCourse($plan['course_id'],$type);
			if(!empty($cdn_info['fk_cdn'])){
				return self::cdnDispatchSetDefault($cdns, $cdn_info['fk_cdn']);
			}
			$course = $course_db->getCourse($plan['course_id']);
			if(!empty($course['user_total'])){
				$cdn_info = $db->getCdnDispatchTotal($course['user_total'],$type);
				if(!empty($cdn_info['fk_cdn'])){
					return self::cdnDispatchSetDefault($cdns, $cdn_info['fk_cdn']);
				}
			}

		}
		if(!empty($ip)){
			$ipinfo = utility_ip::info($ip);
			//获取dispatch的信息
			if(!empty($ipinfo->area_name)){
				$cdn_info = $db->getCdnDispatch($ipinfo->area_name,$ipinfo->op_name,$type);
				if(!empty($cdn_info['fk_cdn'])){
					return self::cdnDispatchSetDefault($cdns, $cdn_info['fk_cdn']);
				}
			}
		}
	}
	/**
	 * 根据 $ip 获取用户的 cdn 地址
	 * @param $ip string
	 * @param $type HLS | VIDEO //点播视频 ，或者 直播视频
	 * @return array("host"=>"","cdn_id"=>0)
	 */
	private function getCdn($type, $ip=""){
		$host="";
		$cdn_id=0;
		$db = new live_db;
		if(!empty($ip)){
			$ipinfo = utility_ip::info($ip);
			//获取dispatch的信息
			if(!empty($ipinfo->area_name)){
				$cdn = $db->getCdnDispatch($ipinfo->area_name,$ipinfo->op_name,$type);
				if(!empty($cdn)){
					$cdn_id = $cdn['fk_cdn'];
					$cdn = $db->getCDN($type,$cdn_id);
					$host= $cdn['host_name'];
				}
			}
		}
		if(empty($host)){
			$cdn = $db->getDefaultCdn($type);
			if(!empty($cdn)){
				$cdn_id=$cdn['pk_cdn'];
				$host= $cdn['host_name'];
			}
		}
		return array("host"=>$host,"cdn_id"=>$cdn_id);
	}

   //卡顿列表查询
	public static function pagegetKartunList(){
		//数组加true 对象不加
		$params = SJson::decode(utility_net::getPostData(), true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		//卡顿列表
		$list = live_db_getKartunListDao::getKartunList($params);
		if($list){
			$ret->result->code = 200;
			$ret->result->msg = "success";
			$ret->data = $list;
			return $ret;
		}
	}
}
