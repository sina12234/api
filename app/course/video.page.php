<?php
class course_video{
	public function pageGenID($inPath){
		$ret = new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		$ret->result =  new stdclass;
		if (empty($inPath[3]) || empty($params->plan_id)){
			$ret->result->code = -1;
			$ret->result->msg= "params is error";
			return $ret;
		}
		$course_db = new course_db;
		$video_db = new video_db;
		$plan_info = $course_db->getPlanFromMainDb($params->plan_id);
		if(empty($plan_info)){
			$ret->result->code = -2;
			$ret->result->msg= "plan not found";
			return $ret;
		}

		$video_id = $plan_info['video_id'];
		if(empty($video_id)){
			$video_info = array();
			$video_info['fk_user']=$plan_info['user_id'];
            $user_id = $plan_info['user_id'];
			$ip = "";
            if(!empty($params->ip)){
                $video_info['ip']=$params->ip;
                $ip = $params->ip;
            }
			if(!empty($params->type))	$video_info['type']=$params->type;//0 默认，1 课程视频  2 用户视频
			$video_info['valid']=0	;	//1有效视频 0 无效视频
			$video_info['progress']=0	;	//1转码完成 0 没有开始 -1 转码失败
			$video_info['status']=0	;	//1 正常 -1 屏蔽
			$video_info['create_time']=date("Y-m-d H:i:s")	;	//1 正常 -1 屏蔽
			$video_id = $video_db->addVideo($video_info);
			if(!empty($video_id)){
				$r = $course_db->updatePlan($params->plan_id, array("fk_video"=>$video_id));
                $ret->data = array("video_id"=>$video_id, "ip"=>$ip, "user_id"=>$user_id);
			}else{
				$ret->result->code = -3;
				$ret->result->msg= "gen video id error";
                return $ret;
			}
		}else{
            $video = $video_db->getVideo($video_id);
            if(empty($video)){
                $ret->result->code = -4;
                $ret->result->msg= "get video info error";
                return $ret;
            }
            $ip = $video['ip'];
            $user_id = $video["user_id"];
            $ret->data=array("video_id"=>$video_id, "ip"=>$ip, "user_id"=>$user_id);
        }
		return $ret;
	}
	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if (empty($inPath[3])){
			$ret->result->code = -1;
			$ret->result->msg= "video id is error";
			return $ret;
		}
		$vid = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		$r = video_api::update($vid,$params);
		if($r!==false){
			$ret->result->code=0;
			//修改老师时间信息
			$video_db = new video_db;
			$video_info = $video_db->getVideo($vid);
			if(!empty($params->totaltime)){
				if($video_info['totaltime'] == 0){
					stat_api::setTeacherStatOrgTotaltime($video_info['user_id'],$video_info['user_id'],0,$params->totaltime);
					stat_api::setTeacherStatTotaltime($video_info['user_id'],0,$params->totaltime);
				}elseif($video_info['segs_totaltime'] == 0){
					stat_api::setTeacherStatOrgTotaltime($video_info['user_id'],$video_info['user_id'],$video_info['totaltime'],$params->totaltime);
					stat_api::setTeacherStatTotaltime($video_info['user_id'],$video_info['totaltime'],$params->totaltime);
				}else{
					stat_api::setTeacherStatOrgTotaltime($video_info['user_id'],$video_info['user_id'],$video_info['segs_totaltime'],$params->totaltime);
					stat_api::setTeacherStatTotaltime($video_info['user_id'],$video_info['segs_totaltime'],$params->totaltime);
				}
			}
			if(!empty($params->segs_totaltime)){
				if($video_info['segs_totaltime'] == 0){
					stat_api::setTeacherStatOrgTotaltime($video_info['user_id'],$video_info['user_id'],$video_info['totaltime'],$params->segs_totaltime);
					stat_api::setTeacherStatTotaltime($video_info['user_id'],$video_info['totaltime'],$params->segs_totaltime);
				}else{
					stat_api::setTeacherStatOrgTotaltime($video_info['user_id'],$video_info['user_id'],$video_info['segs_totaltime'],$params->segs_totaltime);
					stat_api::setTeacherStatTotaltime($video_info['user_id'],$video_info['segs_totaltime'],$params->segs_totaltime);
				}
			}
		}else{
			$ret->result->code=-2;
		}
		return $ret;
	}

	/*
	 * 修改视频封面
	 */
	public function pageUpdateImg($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		if (empty($params->vid)){
			$ret->result->code = -1;
			$ret->result->msg= "video id is error";
			return $ret;
		}
		$vid = (int)($params->vid);
		$updates = array();
		if(!empty($params->img))	$updates['thumb0'] = $params->img;
		$video_db = new video_db;
		$updates['last_updated'] = date("Y-m-d H:i:s");
		$r = $video_db->update($vid,$updates);

		return $r;
	}

	public function pageGet($inPath){
		$ret = new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		if (empty($params->video_id) && empty($params->plan_id)){
			$ret->result =  new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "params is error";
			return $ret;
		}
		$item=array();
		$video_id=0;
		if(!empty($params->plan_id)){
			$course_db = new course_db;
			$plan_info = $course_db->getPlan($params->plan_id);
			if(!empty($plan_info['video_id'])){
				$video_id = $plan_info['video_id'];
			}
		}
		if(!empty($params->video_id)){
			$video_id = $params->video_id;
		}
		if(!empty($video_id)){
			$video_db = new video_db;
			$item = $video_db->getVideo($video_id);
		}
		if(!empty($item)){            
            if(!empty($params->plan_id)){
                $condition = ['pid' => $params->plan_id , 'rtime'=>0]; 
                $pointItem = video_api::getTeacherPointList($condition);
                if($pointItem['code'] == 0){
                     $item['point'] = $pointItem['result'];
                }
            }
			if($item['status']==1){
				$item['status']="enabled";
			}elseif($item['status']==0){
				$item['status']="disabled";
			}

			if($item['progress']==1){
				$item['progress']="ok";
			}elseif($item['progress']==-1){
				$item['progress']="error";
			}

			if($item['valid']==1){
				$item['valid']="valid";
			}elseif($item['valid']==-1){
				$item['valid']="invalid";
			}
			$ret->data=$item;
			return $ret;
		}
		$ret->data=array();
		return $ret;
	}
}
