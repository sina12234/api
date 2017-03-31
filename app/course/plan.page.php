<?php
class course_plan{
	/**
	 * @docs https://wiki.gn100.com/doku.php?id=docs:api:course
	 * 验证Plan 播放权限
	 */
	public function pageVerify($inPath){
		$ret = new stdclass;
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result=new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "plan_id is not set!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
		if(!isset($params->user_id)){
			$ret->result=new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "userid not set!";
			return $ret;
		}
		$plan_id= $inPath[3];
		$ok = course_api::verifyPlan($params->user_id, $plan_id, $reg, $trial);
		$ret->data = array(
			"ok"=>$ok,
			"reg"=>$reg,
		);
		if(isset($trial['time'])){
			$ret->data["time"]=$trial['time'];
		}
		if(isset($trial['video_public_type'])){
			$ret->data["trial_type"]=$trial['video_public_type'];
			$ret->data["video_public_type"]=$trial['video_public_type'];
		}
		if(isset($trial['live_public_type'])){
			$ret->data["trial_type"]=$trial['live_public_type'];
			$ret->data["live_public_type"]=$trial['live_public_type'];
		}
		return $ret;
	}
	/**
	 * @docs https://wiki.gn100.com/doku.php?id=docs:api:course
	 * 验证多个 Plan 播放权限
	 */
	public function pageVerifyMulti($inPath){
		$ret = new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		if(!isset($params->user_id)){
			$ret->result=new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "userid not set!";
			return $ret;
		}
		if(!isset($params->plan_ids)){
			$ret->result=new stdclass;
			$ret->result->code = -2;
			$ret->result->msg= "plan_ids not set!";
			return $ret;
		}
		$ret->data=new stdclass;
		foreach($params->plan_ids as $plan_id){
			$ok = course_api::verifyPlan($params->user_id, $plan_id, $reg, $trial);
			$result = array(
				"ok"=>$ok,
				"reg"=>$reg,
			);
			if(isset($trial['time'])){
				$result["time"]=$trial['time'];
			}
			if(isset($trial['video_public_type'])){
				$result["trial_type"]=$trial['video_public_type'];
				$result["video_public_type"]=$trial['video_public_type'];
			}
			if(isset($trial['live_public_type'])){
				$result["trial_type"]=$trial['live_public_type'];
				$result["live_public_type"]=$trial['live_public_type'];
			}
			$ret->data->$plan_id = $result;
		}
		return $ret;
	}
	public function pageGet($inPath){
		$ret = new stdclass;
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result=new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "plan_id is not set!";
			return $ret;
		}
		$plan_id= $inPath[3];
		$db = new course_db;
		if(!empty($params->maindb)){
			$ret->data = $db->getPlanFromMainDb($plan_id);
		}else{
			$ret->data = $db->getPlan($plan_id);
		}
		if(!empty($ret->data)){
			$ret->data['status']=course_status::name($ret->data['status']);
		}
		return $ret;
	}

	public function pageGetuni($inPath){
		$ret = new stdclass;
		$ret->result=new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "not found!";
		$params = SJson::decode(utility_net::getPostData());
		$course_id= empty($params->course_id)? "":$params->course_id;
		$section_id = empty($params->section_id)? "":$params->section_id;
		$class_id = empty($params->class_id)? "":$params->class_id;
		if($section_id && $class_id && $course_id){
			$db = new course_db;
			$ret->data = $db->getPlanuni($course_id,$section_id,$class_id);
			if(!empty($ret->data)){
				$ret->data['status']=course_status::name($ret->data['status']);
			}
			$ret->result->code = 0;
			$ret->result->msg = "SUCCESS！";
		}else{
			return $ret;
		}
		return $ret;
	}

    public function pageGetPlanByCids($inPath){
        $ret=new stdClass;
		$ret->result=new stdclass;
		$ret->result->code = -1;
		$ret->result->msg='';
		$params = SJson::decode(utility_net::getPostData());
        if(empty($params)){
            $ret->result->msg='cids is empty';
            return $ret;
        }
	    $db = new course_db;
		$ret->data=$db->getPlansByCourseIds($params);
        if(empty($ret->data)){
            $ret->result->msg='data is empty';
            return $ret;
        }
        return $ret;
    }

	public function pagelist($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		if (empty($inPath[4]) || !is_numeric($inPath[4])) {
			return $ret;
		}

		$params = SJson::decode(utility_net::getPostData());
		$cid = empty($params->cid)? "":$params->cid;
		$orgUserId = empty($params->orgUserId)? "":$params->orgUserId;
		$plan_id= empty($params->plan_id)? "":$params->plan_id;
		$class_id = empty($params->class_id)? "":$params->class_id;
		$user_plan_id = empty($params->user_plan_id)? "":$params->user_plan_id;
		$sid = empty($params->sid)? "":$params->sid;
		$order_by = empty($params->order_by)? "desc":$params->order_by;
		if(empty($params->week)){
			$week = false;
		}else{
			$week = $params->week;
		}
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 15;}else{$length = $inPath[4];}
		$allcourse=false;
		if(!empty($params->allcourse)){
			$allcourse= true;
		}
		$data = array();
		//传入的时间戳
		if(isset($params->start_time) && is_numeric($params->start_time)){
			$data["start_time"] = $params->start_time;
		}
		if(isset($params->startTime)){
			$timeArr = explode(',',$params->startTime);
			if(!empty($timeArr)){
				$data['partStart'] = $timeArr[0];
				$data['partEnd']   = $timeArr[1];
			}
		}
		if(isset($params->endstart_time) && is_numeric($params->endstart_time)){
			$data["endstart_time"] = $params->endstart_time;
		}
		if(isset($params->status) && is_numeric($params->status)){
			$data["status"] = $params->status;
		}
		if(isset($params->type) && is_numeric($params->type)){
			$data["type"] = $params->type;
		}

		$course_api = new course_api;
		$listplan= $course_api->getlistplan($cid,$orgUserId,$class_id,$user_plan_id,$sid,$plan_id,$week,$allcourse,$order_by,$data,$page,$length);
		if($listplan === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listplan;
	}

	public function pageEndGroupByClassIds($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";

		$params = SJson::decode(utility_net::getPostData());
		$userId = 0;
		$classIdsArr = array();
		if(!empty($params->classIdsArr)){
			foreach($params->classIdsArr as $k=>$v){
				$classIdsArr[] = $v;
			}
		}

		if(!empty($params->userId)){
			$userId = $params->userId;
		}
		$type = !empty($params->type) ? $params->type : 0;
		$ut = !empty($params->ut) ? $params->ut : 0;
		$course_api = new course_api;
		$listplan= $course_api->planEndGroupByclassIds($classIdsArr,$userId,$type,$ut);
		if($listplan === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listplan;
	}

	public function pageEndGroupByClassIdsV2($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";

		$params = SJson::decode(utility_net::getPostData());
		$userId = 0;
		$classIdsArr = array();
		if(!empty($params->classIdsArr)){
			foreach($params->classIdsArr as $k=>$v){
				$classIdsArr[] = $v;
			}
		}

		if(!empty($params->userId)){
			$userId = $params->userId;
		}
		$course_api = new course_api;
		$listplan= $course_api->planEndGroupByclassIdsV2($classIdsArr,$userId);
		if($listplan === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listplan;
	}

	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id)){
			$ret->result->code = -3;
			$ret->result->msg = "the plan_id is empty!";
			return $ret;
		}else{
			$time = date("Y-m-d H:i:s");
			$plan["fk_user"] = $params->user_id;
			$plan["fk_user_plan"] = $params->user_plan_id;
		//	$plan["status"] = $params->status;
			$plan["start_time"] = empty($params->start_time)? $time:$params->start_time;
			if(!empty($params->end_time)){
				$plan["end_time"] = $params->end_time;
			}
			$plan["live_public_type"] = empty($params->live_public_type)? 0 :$params->live_public_type;
			$plan["video_public_type"] = empty($params->video_public_type)? 0:$params->video_public_type;
			$plan["video_trial_time"] = empty($params->video_trial_time)? 0:$params->video_trial_time;
			$plan["last_updated"] = $time;
			$cid = $params->course_id;
			$sid = $params->section_id;
			$class_id = $params->class_id;

		}
		$course_api = new course_api;
		$course_db = new course_db;
		$old_class = $course_api->getclass($class_id);
		$course_info = $course_db->getCourse($cid);
		$old_plan = $course_db->getPlanFromMainDb($params->plan_id);
		if($old_plan['user_plan_id'] != $plan['fk_user_plan']){
			if($old_class['user_class_id'] != $plan['fk_user_plan']){
				if($course_info['status'] == 3){
					stat_api::addTeacherStatCourseCompleteCountByClass($plan['fk_user_plan'],$cid);
					stat_api::addTeacherStatOrgCourseCompleteCountByClass($plan['fk_user_plan'],$plan['fk_user'],$cid);
				}else{
					stat_api::addTeacherStatCourseRemainCount($plan['fk_user_plan'],$cid);
					stat_api::addTeacherStatOrgCourseRemainCount($plan['fk_user_plan'],$plan['fk_user'],$cid);
				}
				if($course_info['admin_status'] == -2){
					stat_api::addTeacherStatCourseOffCountByClass($plan['fk_user_plan'],$cid);
					stat_api::addTeacherStatOrgCourseOffCountByClass($plan['fk_user_plan'],$plan['fk_user'],$cid);

				}elseif($course_info['admin_status'] == 1){
					stat_api::addTeacherStatCourseOnCountByClass($plan['fk_user_plan'],$cid);
					stat_api::addTeacherStatOrgCourseOnCountByClass($plan['fk_user_plan'],$plan['fk_user'],$cid);

				}
			}
		}

		$r =$course_api->updateplan($params->plan_id,$plan);

		if($r === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			if($old_plan['user_plan_id'] != $plan['fk_user_plan']){
				if($course_info['status'] == 3){
					stat_api::reduceTeacherStatCourseCompleteCountByClass($old_plan['user_plan_id'],$cid);
					stat_api::reduceTeacherStatOrgCourseCompleteCountByClass($old_plan['user_plan_id'],$old_plan['user_id'],$cid);
				}else{
					stat_api::reduceTeacherStatCourseRemainCount($old_plan['user_plan_id'],$cid);
					stat_api::reduceTeacherStatOrgCourseRemainCount($old_plan['user_plan_id'],$old_plan['user_id'],$cid);
				}
				if($course_info['admin_status'] == -2){
					stat_api::reduceTeacherStatCourseOffCountByClass($old_plan['user_plan_id'],$cid);
					stat_api::reduceTeacherStatOrgCourseOffCountByClass($old_plan['user_plan_id'],$old_plan['user_id'],$cid);
				}elseif($course_info['admin_status'] == 1){
					stat_api::reduceTeacherStatCourseOnCountByClass($old_plan['user_plan_id'],$cid);
					stat_api::reduceTeacherStatOrgCourseOnCountByClass($old_plan['user_plan_id'],$old_plan['user_id'],$cid);
				}
			}
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageinsert($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The courseid is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$course_id = $inPath[3];

		$params = SJson::decode(utility_net::getPostData());
		if(empty($params)){
			$ret->result->code = -3;
			$ret->result->msg = "the data is empty!";
			return $ret;
		}else{
			$plan["user_id"] = $params->user_id;
			$plan["user_plan_id"] = $params->user_plan_id;
			$plan["course_id"] = $course_id;
			$plan["section_id"] = $params->section_id;
			$plan["class_id"] = $params->class_id;
			$plan["live_public_type"] = empty($params->live_public_type)? 0 :$params->live_public_type;
			$plan["video_public_type"] = empty($params->video_public_type)? 0:$params->video_public_type;
			$plan["video_trial_time"] = empty($params->video_trial_time)? 0:$params->video_trial_time;
			$plan["status"] = empty($params->status)? "1":$params->status;
            if(!empty($params->start_time)){
                $plan['start_time'] = $params->start_time;
            }
			if(!empty($params->end_time)){
				$plan["end_time"] = $params->end_time;
			}
		}
		$course_api = new course_api;
		$course_db = new course_db;
		$old_class = $course_api->getclass($plan['class_id']);
		$course_info = $course_db->getCourse($plan['course_id']);
		if($old_class['user_class_id'] != $plan['user_plan_id']){
			if($course_info['status'] == 3){
				stat_api::addTeacherStatCourseCompleteCountByClass($plan['user_plan_id'],$plan['course_id']);
				stat_api::addTeacherStatOrgCourseCompleteCountByClass($plan['user_plan_id'],$plan['user_id'],$plan['course_id']);
			}else{
				stat_api::addTeacherStatCourseRemainCount($plan['user_plan_id'],$plan['course_id']);
				stat_api::addTeacherStatOrgCourseRemainCount($plan['user_plan_id'],$plan['user_id'],$plan['course_id']);
			}
			if($course_info['admin_status'] == -2){
				stat_api::addTeacherStatCourseOffCountByClass($plan['user_plan_id'],$plan['course_id']);
				stat_api::addTeacherStatOrgCourseOffCountByClass($plan['user_plan_id'],$plan['user_id'],$plan['course_id']);

			}elseif($course_info['admin_status'] == 1){
				stat_api::addTeacherStatCourseOnCountByClass($plan['user_plan_id'],$plan['course_id']);
				stat_api::addTeacherStatOrgCourseOnCountByClass($plan['user_plan_id'],$plan['user_id'],$plan['course_id']);
			}
		}
		$plan_id   = $course_api->addplan($plan);
		if($plan_id === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$group_config = SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$group_suject = $group_config->subject;
			$group_grade  = $group_config->grade;
			$grade_info   = tag_db::getMappingCourseByCidAndGid($course_id,$group_grade);
			$subject_info = tag_db::getMappingCourseByCidAndGid($course_id,$group_suject);
			if(!empty($grade_info->items)){
				$grade_tag = array();
				foreach($grade_info->items as $grade){
					$grade_tag[] = $grade['fk_tag'];
				}
				tag_api::addmappingplan($plan_id,$group_grade,$grade_tag);
			}
			if(!empty($subject_info->items)){
				$subject_tag = array();
				foreach($subject_info->items as $subject){
					$subject_tag[] = $subject['fk_tag'];
				}
				tag_api::addmappingplan($plan_id,$group_suject,$subject_tag);
			}
			$ret->result->code = 0;
			//TODO更新课程状态
			$course_api->update($course_id,array("status"=>1));
			//如果课程不是公开课，更新人数
			$ret->result->msg  = "success";
			$ret->result->data = $plan_id;
		}
		return $ret;
	}
	public function pageSetStatus($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		if(empty($inPath[3]) || empty($params->status)){
			$ret->result->code = -1;
			$ret->result->msg = "params error!";
			return $ret;
		}
		$course_db  = new course_db();
		$plan_info  = $course_db->getPlanFromMainDb($inPath[3]);
		$old_course = $course_db->getCourse($plan_info['course_id']);

		$update_r = course_api::setPlanStatus($inPath[3],$params->status);
		if($update_r){
			$new_course = $course_db->getCourse($plan_info['course_id']);
			if($new_course['status'] == course_status::finished && $old_course['status'] != course_status::finished){
				$new_class  = $course_db->classList($plan_info['course_id']);
				$new_plan   = $course_db->getPlanTeacherByCourseId($plan_info['course_id']);
				$tid_arr = array();
				foreach($new_class->items as $so){
					$tid_arr[$so['fk_user_class']] = $so['fk_user_class'];
				}
				foreach($new_plan->items as $po){
					$tid_arr[$po['fk_user_plan']] = $po['fk_user_plan'];
				}
				stat_api::addTeacherStatCourseCompleteCount($tid_arr);
				stat_api::addTeacherStatOrgCourseCompleteCount($tid_arr,$new_course['fk_user']);
			}
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}else{
			$ret->result->code = -2;
			$ret->result->msg ="update fail";
		}
		return $ret;
	}
	/**
	 * 获取plan的状态，这个接口是从 主库 里获取，请尽量避免使用这个方法
	 * 推荐使用下面的方法 getPlanStatusV2()
	public function pageGetStatus($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3])){
			$ret->result->code = -1;
			$ret->result->msg = "params error!";
			return $ret;
		}
		$plan_id = $inPath[3];
		$db = new course_db;
		$plan_info = $db->getPlanFromMainDb($plan_id);
		if(empty($plan_info)){
			$ret->result->code = -1;
			$ret->result->msg = "not found plan info";
			return $ret;
		}
		$section_info = $db->getSection($plan_info['section_id']);
		$course_info = $db->getCourse($plan_info['course_id']);
		$ret->data=new stdclass;
		$ret->data->plan_status= course_status::name($plan_info['status']);
		$ret->data->section_status= course_status::name($section_info['status']);
		$ret->data->course_status= course_status::name($course_info['status']);
		return $ret;
	}
	 **/
	/**
	 * 获取单个或者多个plan的状态,从数据库里获取,尽量从中间层里获取
	 **/
	public function pageGetStatusV2($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id)){
			$ret->result->code = -1;
			$ret->result->msg = "params error!";
			return $ret;
		}
		$db = new course_db;
		$plan_info = $db->getPlanStatus($params->plan_id);
		if(empty($plan_info->items)){
			$ret->result->code = -1;
			$ret->result->msg = "not found plan info";
			return $ret;
		}
		if(is_numeric($params->plan_id)){
			$ret->data=new stdclass;
			$ret->data->plan_status= course_status::name($plan_info->items[0]['plan_status']);
			$ret->data->section_status= course_status::name($plan_info->items[0]['section_status']);
			$ret->data->course_status= course_status::name($plan_info->items[0]['course_status']);
		}else{
			$ret->data=array();
			foreach($plan_info->items as $item){
				$plan_id = $item['plan_id'];
				$status=new stdclass;
				$status->plan_status= course_status::name($item['plan_status']);
				$status->section_status= course_status::name($item['section_status']);
				$status->course_status= course_status::name($item['course_status']);
				$ret->data[$plan_id]=$status;
			}
		}
		return $ret;
	}
	public function pageGetUnstartPlan($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->hours)){
			return $ret;
		}
		$course_db = new course_db;
		$data = $course_db->getUnstartPlan($params->hours);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}
	public function pageGetRemindInfo($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id) || empty($params->minutes)){
			$ret->result->msg="缺少参数";
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg="没有这个上课计划";
			return $ret;
		}
		$teacher = user_db::getUser($plan["user_plan_id"]);
		if(!$teacher){
			$ret->result->msg="没有这个老师";
			return $ret;
		}
		$course = $course_db->getCourse($plan["course_id"]);
		if(!$course){
			$ret->result->msg="没有这个课程";
			return $ret;
		}
		$subdomain = user_db::getSubDomainByUserId($course["fk_user"]);
		if(empty($subdomain)){
			$subdomain = "www.yunke.com";
		}else{
            $subdomain = $subdomain["subdomain"];
		}
		$section = $course_db->getSection($plan["section_id"]);
		if(!$section){
			$ret->result->msg="没有这个section";
			return $ret;
		}
		$starttime = split(" ", $plan["start_time"])[1];
		$starttime = substr($starttime, 0, 5);
        //$weixin = "【高能100即将开课】\n您已报名“".$course["title"]."”，".$section["name"]."，主讲老师：".$teacher["name"]."。今日".$starttime."开课，距离上课时间还有".$params->minutes."分钟，请准时通过电脑或手机进入课堂：https://$subdomain/course.info.show/".$plan["course_id"];
        $weixin = "【上课提醒】你报名的".$course["title"].$section["name"]."将在今日".$starttime."开课，记得准时来上课哦。https://$subdomain/course.info.show/".$plan["course_id"];
		$heading = $course["title"] . " 上课通知(" . $params->minutes . "分钟)：";
		$text = "您已报名“<a href='https://{$subdomain}/course.plan.play/$params->plan_id' target='_blank'>".$course["title"]."</a>”，今日".$starttime."开课，距离上课时间还有".$params->minutes."分钟，请准时到达。";
		$users = $course_db->getClassUser($plan["class_id"]);
		if(empty($users->items)){
			$ret->result->msg="这个班没有学生";
			return $ret;
		}
		$ret->result->code = 0;
		$ret->data = $users->items;
		$ret->weixin = $weixin;
		$ret->heading = $heading;
		$ret->text = $text;
		return $ret;
	}
	public function pageGetPlanListByOwner($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=0;
		$ret->result->msg="";
		if(empty($inPath[3])){
		    $ret->result->code=-1;
		    $ret->result->msg="params error";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
		$res = course_api::getPlanListByOwner($inPath[3],$params);
		if(empty($res)){
		    $ret->result->code=-2;
		    $ret->result->msg="data is empty!";
			return $ret;
		}
		$ret->data = $res;
		return $ret;
	}
    public function pageGetCourseByPlan($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($params->plan_id)){
            $ret->result->msg = "缺少参数";
            return $ret;
        }
        $db = new course_db;
        $data = $db->getPlan($params->plan_id);
        if(!empty($data)){
            $data = $db->getCourse($data["course_id"]);
            if(!empty($data)){
                $ret->result->code = 0;
                $ret->data = $data;
            }
        }
        return $ret;
    }

	public function pageGetClassPlan($inPath){
        $ret=new stdClass;
		$ret->code = 0;
		$ret->msg  = 'success';
		$ret->data = '';
		$class_id_arr = SJson::decode(utility_net::getPostData(),true);

        if(empty($class_id_arr)){
			$ret->code = -1;
            $ret->msg='classid is empty';
            return $ret;
        }
	    $db = new course_db;
		$class_plan_ret=$db->getClassPlan($class_id_arr);
        if(!empty($class_plan_ret->items)){
			$ret->data = $class_plan_ret->items;
			return $ret;
        }else{
			$ret->code = -1;
            $ret->msg='get data is failed';
			return $ret;
		}

    }

	public function pageGetNextPlanId($inPath)
	{
		if (!isset($inPath[3], $inPath[4]) || !(int)($inPath[3]) || !(int)($inPath[4]))
			return api_func::setMsg(1000);

		$res = course_db_coursePlanDao::getNextPlanId($inPath[3], $inPath[4]);

		if (!empty($res['pk_plan'])) return api_func::setData(['planId' => $res['pk_plan']]);

		return api_func::setMsg(3002);
	}

	public function pageHasVideo($inPath)
	{
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params['planIdArr'])){
			return api_func::setMsg(1000);
		}

		$planRes = course_db_coursePlanDao::planList(array('planId'=>$params['planIdArr']));
		if(empty($planRes->items)) return api_func::setMsg(3002);

		$videoIdArr = array_column($planRes->items,'fk_video','pk_plan');
		$videoIds = implode(',',$videoIdArr);
		$videoRes = video_db::listVideosByVideoIds($videoIds);
		if(empty($videoRes->items)) return api_func::setMsg(3002);

		foreach($videoRes->items as $val){
			$videoInfo[$val['pk_video']] = !empty($val['segs_totaltime']) ? $val['segs_totaltime'] : $val['totaltime'];
		}

		foreach($videoIdArr as $key=>$val){
			$data[] = [
				'planId'    => $key,
				'hasVideo'  => !empty($videoInfo[$val]) ? 1 : 0,
				'totalTime' => !empty($videoInfo[$val]) ? $videoInfo[$val] : 0
			];
		}

		return api_func::setData($data);
	}

	//题目类型
	public function pageQuestionType($inPath)
	{
		if(empty($inPath[3])) return api_func::setMsg(1000);
		$type = (int)$inPath[3];
		$res = course_db::getPhraseByType($type);

		return api_func::setData($res);
	}

  	/* 获取排课列表 */
	public function pageGetPlanList($inPath){
		$ret = new stdclass();
		$ret->result = new stdclass();
		$ret->result->code = 0;
		$ret->result->msg  = "success";

		$params = SJson::decode(utility_net::getPostData());

        if (isset($params->order)){ $orderBy = $params->order; }
        if (isset($params->condition)){ $conditions = $params->condition; }

		$ret->data = course_db_coursePlanDao::getPlanList($conditions,$orderBy);

		return $ret;
	}

	public function pageGetPlanByCourseId(){
		$params = SJson::decode(utility_net::getPostData(), true);

		$courseId = !empty($params['courseId']) ? (int)$params['courseId'] : 0;
		if(empty($courseId)) return api_func::setMsg(1000);

		$res = course_db_coursePlanDao::getPlanByCourseId($courseId);

		if(empty($res->items)) return api_func::setMsg(3002);
		return api_func::setData($res->items);
	}
	//获取有关planID 的信息
	public function pageGetplanidInfo(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$planId = !empty($params['planId']) ? (int)$params['planId'] : 0;
		if(empty($planId)) return api_func::setMsg(1000);
		$res = course_db_coursePlanDao::getplanidInfo($planId);
		if($res) return $res;
		return false;
	}
	//获取planid对应的章节信息
	public function pageGetSectionInfo(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$courseId = !empty($params['fk_course']) ? (int)$params['fk_course'] : 0;
		if(empty($courseId)) return api_func::setMsg(1000);
		$res = course_db_courseSectionDao::getSectionInfo($courseId);
		if($res) return json_encode($res);
		return false;
	}
}
