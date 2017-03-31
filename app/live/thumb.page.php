<?php
/**
  * 取直播截图
  */
class live_thumb{
	public function pageThumbByPlan($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($params->plan_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$live_db = new live_db;
        $data = $live_db->getPublishByRecordPlanID($params->plan_id);
        if(!empty($data)){
            $stream_name = $data["stream_name"];
            if(empty($params->type)){
                $type = "small";
            }else{
                $type = $params->type;
            }
            $key = "live-thumb_" . $type . "_" . $stream_name;
            $r = redis_api::useConfig("live_thumb");
            $v = redis_api::getBinary($key);
            if(!empty($v)){
                $ret->result->code=0;
                $ret->data = base64_encode($v);
                return $ret;
            }
        }
		return $ret;
	}
}
