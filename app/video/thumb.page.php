<?php

class video_thumb
{
    public function pageGetThumbs($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($inPath[3])){
			$ret->result->msg = "缺少参数";
			return $ret;
        }
        $video_id = $inPath[3];
        $db = new video_db;
        $data = $db->getThumbs($video_id);
        $ret->result->code = 0;
        $ret->data = $data;
        return $ret;
    }
    public function pageAddThumbs($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->video_id) || empty($params->width) || empty($params->height) || empty($params->rows) || empty($params->cols) || empty($params->intervals) || empty($params->thumbs) || empty($params->last_num)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
        $db = new video_db;
        $thumbs = array(
            "fk_video" => $params->video_id, "width" => $params->width, "height" => $params->height,
            "rows" => $params->rows, "cols" => $params->cols, "intervals" =>$params->intervals,
            "thumbs" => $params->thumbs, "last_num" => $params->last_num
        );
        $data = $db->addThumbs($thumbs);
        $ret->result->code = 0;
        $ret->data = $data;
        return $ret;
    }
    public function pageUpdateThumbs($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->video_id) || empty($params->thumbs) || empty($params->last_num)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
        $db = new video_db;
        $thumbs = array(
            "thumbs" => $params->thumbs, "last_num" => $params->last_num
        );
        if(!empty($params->width)){
            $thumbs["width"] = $params->width;
        }
        if(!empty($params->height)){
            $thumbs["height"] = $params->height;
        }
        if(!empty($params->rows)){
            $thumbs["rows"] = $params->rows;
        }
        if(!empty($params->cols)){
            $thumbs["cols"] = $params->cols;
        }
        if(!empty($params->intervals)){
            $intervals["intervals"] = $params->intervals;
        }
        $data = $db->updateThumbs($params->video_id, $thumbs);
        $ret->result->code = 0;
        $ret->data = $data;
        return $ret;
    }
}

