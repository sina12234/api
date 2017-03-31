<?php
class live_cdn{

	static public function getRTMP(array $cdn,$provider="",$stream_name){
		$sc = parse_url($cdn['host_name'], PHP_URL_SCHEME);
		$url_prefix=$cdn['host_name'];
		if(empty($sc)){
			$url_prefix="rtmp://{$cdn['host_name']}";
		}
		$data=array(
			"url"=>"{$url_prefix}/play",
			"cdn_id"=>$cdn['pk_cdn'],
			"streamList"=>array(
				array("name"=>"原画","stream"=>$stream_name,"bitrate"=>640000),
			),
		);
		return $data;
	}
	static public function getHLS(array $cdn,$provider="",$stream_name,$scheme){
		$sc = parse_url($cdn['host_name'], PHP_URL_SCHEME);
		$url_prefix=$cdn['host_name'];
		if(empty($sc)){
			$url_prefix="{$scheme}://{$cdn['host_name']}";
		}
		$data=array(
			"url"=>"{$url_prefix}",
			"cdn_id"=>$cdn['pk_cdn'],
			"stream"=>$stream_name,
			"detail"=>array(
				array("name"=>"直播","stream"=>$stream_name)
			)
		);
		$host = parse_url($cdn['host_name'], PHP_URL_HOST);
		$key = self::gn_cipher($host,$stream_name);
		foreach($key as $k=>$v){
			$data[$k] = $v;
		}

		foreach($data['detail'] as &$detail){
			$key = self::gn_cipher($host,$detail['stream']);
			foreach($key as $k=>$v){
				$detail[$k] = $v;
			}
		}

		return $data;
	}
	/**
	 * 老的接口
	 */
	static public function getHLS_OLD(array $cdn,$provider="",$stream_name,$scheme){
		$sc = parse_url($cdn['host_name'], PHP_URL_SCHEME);
		$url_prefix=$cdn['host_name'];
		if(empty($sc)){
			$url_prefix="{$scheme}://{$cdn['host_name']}";
		}
		$data=array(
			"url"=>"{$url_prefix}/play",
			"cdn_id"=>$cdn['pk_cdn'],
			"stream"=>$stream_name."/index.m3u8",
			"detail"=>array(
				array("name"=>"直播","stream"=>$stream_name."/index.m3u8")
			)
		);
		return $data;
	}



	static public function getVodHLS(array $cdn,$provider="",$stream_name,$scheme,$video){
        if(empty($video['finish_type']))return;
		$sc = parse_url($cdn['host_name'], PHP_URL_SCHEME);
		$url_prefix=$cdn['host_name'];
		if(empty($sc)){
			$url_prefix="{$scheme}://{$cdn['host_name']}";
		}
		$data=array(
			"url"=>"{$url_prefix}",
			"cdn_id"=>$cdn['pk_cdn'],
			//"segs"=>$video['segs'],
			"stream"=>$stream_name,
			"video_id"=>$video['video_id'],
			"updatetime"=>(int)strtotime($video['last_updated']),
			"detail"=>array(
				//array("name"=>"直播","stream"=>$stream_name)
			)
		);
		$host = parse_url($cdn['host_name'], PHP_URL_HOST);
		$key = self::gn_cipher($host,$stream_name);
		foreach($key as $k=>$v){
			$data[$k] = $v;
		}
		$types = explode(",",$video['finish_type']);
        foreach($types as $type){
            if(!empty($type)){
                $name = "标清";
                if($type=="org"){ $name = "原画";}
                elseif($type=="hd"){$name = "高清"; }

                array_push($data['detail'], array("name"=>$name,"stream"=>pathinfo($stream_name,PATHINFO_DIRNAME)."/{$type}.m3u8"));
            }
        }

		foreach($data['detail'] as &$detail){
			$key = self::gn_cipher($host,$detail['stream']);
			foreach($key as $k=>$v){
				$detail[$k] = $v;
			}
		}

		return $data;
	}
	static public function getVodHLS_OLD(array $cdn,$provider="",$stream_name,$scheme,$video){
        if(empty($video['finish_type']))return;
		$sc = parse_url($cdn['host_name'], PHP_URL_SCHEME);
		$url_prefix=$cdn['host_name'];
		if(empty($sc)){
			$url_prefix="{$scheme}://{$cdn['host_name']}";
		}
		$data=array(
			"url"=>"{$url_prefix}",
			"cdn_id"=>$cdn['pk_cdn'],
			//"segs"=>$video['segs'],
			"stream"=>$stream_name,
			"video_id"=>$video['video_id'],
			"detail"=>array(
			)
		);
		$types = explode(",",$video['finish_type']);
        foreach($types as $type){
            if(!empty($type)){
                $name = "标清";
                if($type=="org"){ $name = "原画";}
                elseif($type=="hd"){$name = "高清"; }

                array_push($data['detail'], array("name"=>$name,"stream"=>pathinfo($stream_name,PATHINFO_DIRNAME)."/{$type}.m3u8"));
            }
        }

		return $data;
	}
	public static function gn_cipher($host,$uri){
		if(stripos($host,"play-cdn-hls-cc.gn100.com")!==false){//CC
			$timestamp = time()+5*3600;
			$g_gn_secret="afc40a31873679427dc2bfdadd3d1955";
			$buf = "$uri-$timestamp-0-0-$g_gn_secret";
			$secret = md5($buf);
			return array("key_name"=>"cc_key","key_value"=>"$timestamp-0-0-$secret");
		}elseif(stripos($host,"play-cdn-hls-chinacache.gn100.com")!==false){//chinacache
			$timestamp = time()+5*3600;
			$g_gn_secret="7b0801f82460a2951595061e90cfa330";
			$buf = "$uri-$timestamp-0-0-$g_gn_secret";
			$secret = md5($buf);
			return array("key_name"=>"auth_key","key_value"=>"$timestamp-0-0-$secret");
		}elseif(stripos($host,"cdn-qiniu-hls-ssl.gn100.com")!==false){//chinacache
			$timestamp = time()+5*3600;
			$g_gn_secret="a6f151eed02f7f9e52922f9a0d7f6a6c";
			$buf = "$uri-$timestamp-0-0-$g_gn_secret";
			$secret = md5($buf);
			return array("key_name"=>"qiniu_key","key_value"=>"$timestamp-0-0-$secret");
		}else{
			$timestamp = time()+5*3600;
			$g_gn_secret="96c6ff224fc53d1ded22462c3cb72cc0";
			$buf = "$uri-$timestamp-0-0-$g_gn_secret";
			$secret = md5($buf);
			return array("key_name"=>"key","key_value"=>"$timestamp-0-0-$secret");
		}
	}
}
