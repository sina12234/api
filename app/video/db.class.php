<?php

/**
 * @author hetao
 */
class video_db
{
    public static function InitDB($dbname = "db_video", $dbtype = "main")
    {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);

        return $db;
    }
    public function addVideo($Video)
    {
        $db = self::InitDB();

        return $db->insert("t_video", $Video);
    }
    public function update($video_id, $Video)
    {
        $db = self::InitDB();

        $key = md5("video_db.getVideo.".$video_id);
        redis_api::del($key);
        return $db->update("t_video", array("pk_video" => $video_id), $Video);
    }
    public function addType($video_id, $hasType, $finishType){
        $db = self::InitDB();


        $data = array();
        if(!empty($finishType)){
            if("org" == $finishType){
                array_push($data, "has_type=(has_type | 1) & ~4");
                array_push($data, "finish_type=(finish_type | 1) & ~4");
            }else if("low" == $finishType){
                array_push($data, "has_type=has_type | 2");
                array_push($data, "finish_type=finish_type | 2");
            }else{
                array_push($data, "has_type=(has_type | 4) & ~1");
                array_push($data, "finish_type=(finish_type | 4) & ~1");
            }
        }else{
            if("org" == $hasType){
                array_push($data, "has_type=(has_type | 1) & ~4");
                array_push($data, "finish_type=(finish_type & ~1) & ~4");
            }else if("low" == $hasType){
                array_push($data, "has_type=has_type | 2");
                array_push($data, "finish_type=finish_type & ~2");
            }else{
                array_push($data, "has_type=(has_type | 4) & ~1");
                array_push($data, "finish_type=(finish_type & ~4) & ~1");
            }
        }
        $key = md5("video_db.getVideo.".$video_id);
        redis_api::del($key);
        return $db->update("t_video", array("pk_video" => $video_id), $data);
    }
    public function getVideo($video_id)
    {
        $db = self::InitDB("db_video","query");

        $key = md5("video_db.getVideo.".$video_id);
        $v   = redis_api::get($key);
        if ($v) {
            return $v;
        }

		$condi=array("pk_video"=>$video_id);
        $item = array(
            "video_id"   => "pk_video", "user_id" => "fk_user", "type"=>"type",
            "title", "desc", "valid", "progress", "status", "filename", "ip", "filename_org",
			"totaltime", "segs", "create_time", "last_updated",
			"thumb0","thumb1","thumb2","thumb3","thumb4","thumb5","thumb6",
			"thumb7","thumb8","segs","segs_totaltime","has_type","finish_type","segs"
        );

        $v = $db->selectOne("t_video", $condi, $item);
		if(!empty($v)){
			redis_api::set($key, $v, 600);
		}
		return $v;
    }
    public function addThumbs($thumbs)
    {
        $db = self::InitDB();

        return $db->insert("t_video_thumbs", $thumbs);
    }
    public function updateThumbs($video_id, $thumbs)
    {
        $db = self::InitDB();

        $key = md5("video_db.getThumbs.".$video_id);
        redis_api::del($key);
        return $db->update("t_video_thumbs", array("fk_video" => $video_id), $thumbs);
    }
    public function getThumbs($video_id)
    {
        $db = self::InitDB("db_video","query");

        $key = md5("video_db.getThumbs.".$video_id);
        $v   = redis_api::get($key);
        if ($v) {
            return $v;
        }

		$condi=array("fk_video"=>$video_id);
        $item = array(
            "video_id" => "fk_video", "width" => "width", "height" => "height",
            "rows" => "rows", "cols" => "cols", "intervals" => "intervals", "thumbs" => "thumbs",
            "last_num" => "last_num", "last_updated" => "last_updated"
        );

        $v = $db->selectOne("t_video_thumbs", $condi, $item);
		if(!empty($v)){
			redis_api::set($key, $v, 600);
		}
		return $v;
    }
	//for sphinx indexing plan
	public static function listVideosByVideoIds( $idsStr ){
		$db = self::InitDB("db_video","query_sphinx");
		if($idsStr == '')
			return array();
		$condition = array("pk_video in ( $idsStr )");
		$item = array('pk_video','t_video.segs_totaltime','t_video.totaltime');
		$table = array("t_video");
		return $db->select($table, $condition, $item);
	}

    public static function getListByVideoArr($videoIdArr)
    {
        $db = self::InitDB("db_video", "query");
        if (count($videoIdArr) < 1) return [];

        $str       = implode(',', $videoIdArr);
        $condition = array("pk_video in ( $str )");
        $item      = array('pk_video', 't_video.segs_totaltime', 't_video.totaltime','t_video.segs');
        $table     = array("t_video");

        return $db->select($table, $condition, $item);
    }

    /* 获取截图列表 */
    public static function listThumb($redisTime,$cond, $page=1, $length=20, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB();

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        
        $condition_str = serialize($cond);  
        $key = md5("video_db.listThumb.".$condition_str);
        if ($redisTime){      
            $res   = redis_api::get($key);
            if ($res) {  return $res; }
        }
        
        $res = $db->select('t_video_thumbs', $cond, $item, $groupBy, $orderBy);
        if ($redisTime){
            if(!empty($res)){ redis_api::set($key, $res, $redisTime); }
        } else {
            redis_api::del($key);
        }
        return $res;
    }
}
