<?php
class stat_api{

	private static $statOrg = array(
		"pv" => "pv",
		"vv_live" => "vv_live",
		"vv_record" => "vv_record",
		"vt_live" => "vt_live",
		"vt_record" => "vt_record",
		"zan" =>"zan",
		"comment" =>"comment",
		"answers" => "answers",
		"answers_right" =>"answers_right",
		"handup" => "handup",
		"call" => "call"
	);
	private static $addstatOrg = array(
		"fk_user"=>"uid",
		"fk_user_org"=>"orguser",
		"pv" => "pv",
		"vv_live" => "vv_live",
		"vv_record" => "vv_record",
		"vt_live" => "vt_live",
		"vt_record" => "vt_record",
		"zan" =>"zan",
		"comment" =>"comment",
		"answers" => "answers",
		"answers_right" =>"answers_right",
		"handup" => "handup",
		"call" => "call"
	);
	private static $userStatField = array(
		"fk_user"=> "fk_user",
		"owner_from"=> "owner_from",
		"pv"     => "pv",
		"vv_live" => "vv_live",
		"vv_record" => "vv_record",
		"vt_live" => "vt_live",
		"vt_record" => "vt_record",
		"zan" =>"zan",
		"comment" =>"comment",
		"answers" => "answers",
		"answers_right" =>"answers_right",
		"org_count"   => "org_count",
		"course_count" => "course_count",
		"point_count" => "point_count",
		"handup" => "handup",
		"call" => "call",
	);

	public static function addUserStat($user_data){
		$add_data = array();
		foreach(self::$userStatField as $k=>$f){
			if(isset($user_data[$f])){
				$add_data[$k] = $user_data[$f];
			}
		}
		return stat_db::addUserStat($add_data);
	}

	/*
	 *例如：$field_data = array(comment=>1,course_count=>10);
	 *$field_data：字段名对应数据库原有值基础上增加的数量
	 */
	public static function addUserStatFieldNum($fk_user,$field_data){
		$updata = array();
		if(!empty($fk_user)){
			$stat_ret = stat_db::getUserStatByFkuser($fk_user);
			if(!empty($stat_ret)){
				foreach($field_data as $k=>$v){
					$updata[] = "$k = $k + $v";
				}		
				return stat_db::updateUserStat($fk_user,$updata);
			}else{
				$field_data['fk_user'] = $fk_user;
				self::addUserStat($field_data);	
			}	
		}
	}
	
	/*
	 *例如：$field_data = array(comment=>1,course_count=>10);
	 *$field_data：字段名对应数据库原有值基础上减少的数量
	 */
	public static function reduceUserStatFieldNum($fk_user,$field_data){
		$updata = array();
		if(!empty($fk_user)){
			$stat_ret = stat_db::getUserStatByFkuser($fk_user);
			if(!empty($stat_ret)){
				foreach($field_data as $k=>$v){
					$updata[] = "$k = $k - $v";
				}		
				return stat_db::updateUserStat($fk_user,$updata);
			}
		}
	}
	
	/*
	 *例如：$field_data = array(comment=>1,zan=>10);
	 *$field_data：字段名对应数据库原有值基础上增加的数量
	 *$pk_day日期格式：2015-10-11
	 */
	public static function addDayUserStatFieldNum($pk_day,$fk_user,$field_data){
		$updata = array();
		if(!empty($fk_user) && !empty($pk_day)){
			$stat_ret = stat_db::getDayUserStatByUserAndDay($fk_user,$pk_day);
			if(!empty($stat_ret)){
				foreach($field_data as $k=>$v){
					$updata[] = "$k = $k + $v";
				}		
				return stat_db::updateDayUserStat($fk_user,$pk_day,$updata);
			}else{
				$field_data['fk_user'] = $fk_user;
				$field_data['pk_day'] = $pk_day;
				return stat_db::addDayUserStat($field_data);	
			}	
		}
	}
	
	/*
	 *例如：$field_data = array(comment=>1,zan=>10);
	 *$field_data：字段名对应数据库原有值基础上减少的数量
	 *$pk_day日期格式：2015-10-11
	 */
	public static function reduceDayUserStatFieldNum($pk_day,$fk_user,$field_data){
		$updata = array();
		if(!empty($fk_user) && !empty($pk_day)){
			$stat_ret = stat_db::getDayUserStatByUserAndDay($fk_user,$pk_day);
			if(!empty($stat_ret)){
				foreach($field_data as $k=>$v){
					$updata[] = "$k = $k - $v";
				}		
				return tat_db::updateDayUserStat($fk_user,$pk_day,$updata);
			}
		}
	}

	/*
	 * 增加一条统计信息
	 */
	public function addUserStatOrg($statdata){
		$arrayin = array();
		foreach(self::$addstatOrg as $key=>$value){
			if(!empty($statdata[$value])){
				$arrayin[$key] = $statdata[$value];
			}
		}
		$ret = stat_db::addUserStatOrg($arrayin);	
		return $ret;
	}
	/*
	/* $params->plan_id  
	 * $params->user_to_id
	 * 点赞或者评论的时候信息更新 如果没有这用户就会创建
	 * 为了不影响上课暂无返回值
	 */
	public function addUpZanData($params){
		$arrayin = array();
		$plan_id = $params->plan_id;
		$course_db = new course_db;
		$ret_plan = $course_db->getPlan($plan_id);
		$orgUser = $ret_plan["user_id"];
		$uid = $params->user_to_id;
		$retup = stat_db::setZanNum($orgUser,$uid);
		if($retup == false){
			$arrayin = array("fk_user"=>$uid,"fk_user_org"=>$orgUser,"zan"=>1);
			$retin = stat_db::addUserStatOrg($arrayin);	
			if($retin === false){
				$retupt = stat_db::setZanNum($orgUser,$uid);
			}
		}
	}

	/* $params->course_id  
	 * $params->user_id
	 * 评论的时候信息更新 如果没有这用户就会创建
	 * 为了不影响上课暂无返回值
	 */
	public function addUpCommentData($params){
		$arrayin = array();
		$course_id = $params->course_id;
		$course_db = new course_db;
		$ret_plan = $course_db->getCourse($course_id);
		$orgUser = $ret_plan["fk_user"];
		$uid = $params->user_id;
		$retup = stat_db::setCommentNum($orgUser,$uid);
		if($retup == false){
			$arrayin = array("fk_user"=>$uid,"fk_user_org"=>$orgUser,"comment"=>1);
			$retin = stat_db::addUserStatOrg($arrayin);	
			if($retin === false){
				$retupt = stat_db::setZanNum($orgUser,$uid);
			}
		}
	}

	/*
	 * 获取单个用户的统计信息
	 * 参数 orguser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 */
	public function getUserStatOrg($orgUser,$uid){
		if (empty($orgUser)||empty($uid)){
			return false;		
		}
		$ret_data = stat_db::getUserStatOrg($orgUser,$uid);
		if (empty($ret_data)) {
			return false;
		}
		return $ret_data;
	}
	/*
	 *获取所有章节信息
	 */
/*	public function UserStatOrglist($orgUser,$uid,$seldata,$page,$length){
		$course_id = (int)$course_id;
		//$array_status = array("禁用","未开始","开始中","已经结束",);
		$listItem = stat_db::UserStatOrglist($orgUser,$uid,$seldata,$page,$length)
		if($listItem === false){ return false;	}
		if(empty($listItem->items)){
			return false;
		}
		$list = $listItem->items;
		return $list;
	}

*/


	public function updateUserStatOrg($orgUser,$uid,$updata){
		$course_db = new course_db;
		$course_id = (int)$course_id;
		$ret = $course_db->updateCourse($course_id,$coursein);	

		return $ret;
	}		

	/*
	 * 当用户获得赞的时候调用此接口 zan+1
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 */
	public function setZanNum($orgUser,$uid){
		$orgUser = (int)$orgUser;
		$uid = (int)$uid;
		$ret = stat_db::setZanNum($orgUser,$uid);	
		return $ret;
	}		
	/*
	 * 当用户获得赞的时候调用此接口 comment+1
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 */
	public function setCommentNum($orgUser,$uid){
		$orgUser = (int)$orgUser;
		$uid = (int)$uid;
		$ret = stat_db::setCommentNum($orgUser,$uid);	
		return $ret;
	}		


	public function addCourseStatSectionCount($data){
		$arrayin = array();
		$course_id = $data["course_id"];
		$statdata = array("fk_course"=>$course_id,"section_count"=>1);
		$ret = stat_db::addCourseStatSectionCount($statdata);
		return $ret;
	}
	public function addCourseStatClassCount($data){
		$arrayin = array();
		$course_id = $data["course_id"];
		$statdata = array("fk_course"=>$course_id,"class_count"=>1);
		$ret = stat_db::addCourseStatClassCount($statdata);	
		return $ret;
	}
	/*
	 * 当用户创建section时的时候调用此接口  section_count
	 * 参数 fk_course 课程id     
	 * $data["count"]=="1" +1
	 * $data["count"]=="-1" -1
	 * setArray 是传过来经过API处理后需要更新的数据
	 */
	public function setCourseStatSectionCount($course_id,$data){
		$course_id = (int)$course_id;
		$retset = stat_db::getCourseStat($course_id,$data);	
		if(empty($retset)){
			$statdata = array("fk_course"=>$course_id,"section_count"=>1);
			$ret = stat_db::addCourseStatSectionCount($statdata);	
		}else{
			$ret = stat_db::setCourseStatSectionCount($course_id,$data);	
		}
		return $ret;
	}	

	/*
	 * 当用户获得赞的时候调用此接口 class+1
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 */
	public function setCourseStatClassCount($course_id,$data){
		$course_id = (int)$course_id;
		$retset = stat_db::getCourseStat($course_id,$data);	
		if(empty($retset)){
			$statdata = array("fk_course"=>$course_id,"class_count"=>1);
			$ret = stat_db::addCourseStatClassCount($statdata);	
		}else{
			$ret = stat_db::setCourseStatClassCount($course_id,$data);	
	}
		return $ret;
	}		
	public function getCourseStat($course_id,$data = array()){
		$course_id = (int)$course_id;
		$ret = stat_db::getCourseStat($course_id,$data);	
		return $ret;
	}		
	/* 
	 * t_user_org_stat 
	 * 输入 增加一条统计数据
	 * $statdata = array("fk_user"=>$statdata["course_id"],);
	 *	$statdata["course_count"] = $data["course_count"] = 1  增加一条课程纪录
	 *	$statdata["class_count"] = $data["class_count"] = 1  增加一条班级纪录
	 *
	 */
	public function addUserOrgStat($data){
		$statdata = array();
		$userId = $data["course_id"];
		$statdata = array("fk_user"=>$userId);
		if(isset($data["course_count"])){
			$statdata["course_count"] = $data["course_count"];
		}
		if(isset($data["class_count"])){
			$statdata["class_count"] = $data["class_count"];
		}
		$ret = stat_db::addUserOrgStat($statdata);	
		return $ret;
	}
	/*
	 * 当机构用户设置class时候调用此接口 class+1  或-1
	 * 参数fk_user 机构所有者id  
	 */
	public function setUserOrgStatClassCount($user_id,$data){
		$user_id = (int)$user_id;
		$retset = stat_db::getUserOrgStat($user_id,$data);	
		if(empty($retset)){
			$statdata = array("fk_user"=>$user_id,"class_count"=>1);
			$ret = stat_db::addUserOrgStat($statdata);	
		}else{
			$ret = stat_db::setUserOrgStatClassCount($user_id,$data);	
		}
		return $ret;
	}		
	/*
	 * 当机构用户设置course时候调用此接口 course+1  或-1
	 * 参数fk_user 机构所有者id  
	 */
	public function setUserOrgStatCourseCount($user_id,$data){
		$user_id = (int)$user_id;
		$retset = stat_db::getUserOrgStat($user_id,$data);	
		if(empty($retset)){
			$statdata = array("fk_user"=>$user_id,"course_count"=>1);
			$ret = stat_db::addUserOrgStat($statdata);	
		}else{
			$ret = stat_db::setUserOrgStatCourseCount($user_id,$data);	
		}
		return $ret;
	}	

	public static function addPlanCommentNew($plan_id,$comment_count){
		$retset = stat_db::getPlanStatByPid($plan_id);	
		if(empty($retset)){
			if(!empty($plan_id)){
				$statdata = array(
							"fk_plan"=>$plan_id,
							"comment_new"=>$comment_count,
					);
				$ret = stat_db::addPlanStat($statdata);	
			}
		}else{
			$set_arr = array("`comment_new` = `comment_new`+$comment_count");
			$ret = stat_db::updatePlanStat($plan_id,$set_arr);	
		}
		return $ret;
	}
	
	public static function addCourseCommentNew($course_id,$comment_count){
		$retset = stat_db::getCourseStatByCid($course_id);	
		if(empty($retset)){
			if(!empty($course_id)){
				$statdata = array(
							"fk_course"=>$course_id,
							"comment_new"=>$comment_count,
					);
				$ret = stat_db::addCourseStat($statdata);	
			}
		}else{
			$set_arr = array("`comment_new` = `comment_new`+$comment_count");
			$ret = stat_db::updateCourseStat($course_id,$set_arr);	
		}
		return $ret;
	}

	public static function checkTeacherCourse($teacher_id,$course_id,$owner_id = 0){
		$course_ret = course_db::getClassByTidAndCourseId($teacher_id,$course_id,$owner_id );
		$plan_ret = course_db::getPlanByTidAndCourseId($teacher_id,$course_id,$owner_id);
		if(!empty($plan_ret) || !empty($course_ret)){
			return true;
		}else{
			return false;
		}
	}

	//$teacher_id是班主任
	public static function addTeacherStatStudentCount($teacher_id,$student_count){
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(empty($retset)){
			if(!empty($teacher_id)){
				$statdata = array(
							"fk_user"=>$teacher_id,
							"student_count"=>$student_count,
					);
				$ret = stat_db::addTeacherStat($statdata);	
			}
		}else{
			$set_arr = array("`student_count` = `student_count`+$student_count");
			$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
		}
		return $ret;
	}

	//$teacher_id是班主任
	public static function reduceTeacherStatStudentCount($teacher_id,$student_count){
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(!empty($retset) && !empty($retset['student_count'])){
			$set_arr = array("`student_count` = `student_count`-$student_count");
			$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);
			return $ret;
		}else{
			return false;
		}
	}

	//修改教师评论数
	public static function addTeacherStatComment($teacher_id,$comment){
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(empty($retset)){
			if(!empty($teacher_id)){
				$statdata = array(
							"fk_user"=>$teacher_id,
							"comment"=>$comment,
					);
				$ret = stat_db::addTeacherStat($statdata);	
			}
		}else{
			$set_arr = array("`comment` = `comment`+$comment");
			$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
		}
		return $ret;
	}

	public static function reduceTeacherStatComment($teacher_id,$comment){
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(!empty($retset) && !empty($retset['comment'])){
			$set_arr = array("`comment` = `comment`-$comment");
			$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);
			return $ret;
		}else{
			return false;
		}
	}

	//修改老师评分和评价人数
	public static function setTeacherStatAvgScore($teacher_id){
		$message_db = new message_db;
		$score_ret = $message_db->getTeacherScoreByTid($teacher_id);
		$stat_ret = stat_db::getTeacherStatByTid($teacher_id);	
		$score_count = 0;
		$total_user = 0;
		if(!empty($score_ret) && !empty($score_ret->items)){
			foreach($score_ret->items as $so){
				$total_user += $so['total_user'];
				$score_count += $so['avg_score'];
			}	
			$avg_score = round($score_count/$total_user,1);
			if(empty($stat_ret)){
				if(!empty($teacher_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"avg_score"=>$avg_score,
							"score_user_count"=>$total_user,
						);
					$ret = stat_db::addTeacherStat($statdata);	
				}
			}else{
				$set_arr = array("avg_score = $avg_score","score_user_count = $total_user");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
			}
			return $ret;
		}else{
			return false;
		}
	}
	
	//修改教师总的直播时长
	public static function setTeacherStatTotaltime($teacherId,$beforeTotalTime,$modifyTotalTime){
		$retset = stat_db::getTeacherStatByTid($teacherId);	
		if( empty($retset) ){
			$totaltime = 0-$beforeTotalTime + $modifyTotalTime;
			if($totaltime <0){
				$totaltime = 0;
			}
			if(!empty($teacherId)){
				$statdata = array(
							"fk_user"=>$teacherId,
							"totaltime"=>$totaltime,
						);
				$ret = stat_db::addTeacherStat($statdata);	
			}
		}else{
			$totaltime = $retset['totaltime']-$beforeTotalTime + $modifyTotalTime;
			if($totaltime < 0){
				$totaltime = 0;
			}
			$setArr = array("`totaltime` = $totaltime");
			$ret = stat_db::updateTeacherStat($teacherId,$setArr);	
		}
		return $ret;
	}	

	//$tidarr教师id数组,针对课程完结的时候修改状态的操作	
	public static function addTeacherStatCourseCompleteCount($tidarr){
		$retset = stat_db::getTeacherStatByTidArr($tidarr);
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(!in_array($tid,$stat_tidarr)){
				if(!empty($tid)){
					$statdata = array(
							"fk_user"=>$tid,
							"course_complete_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);	
				}
			}else{
				if(!empty($stat_ret[$tid]['course_remain_count'])){
					$set_arr = array("`course_complete_count` =`course_complete_count`+ 1","course_remain_count= course_remain_count - 1");
					$ret = stat_db::updateTeacherStat($tid,$set_arr);	
				}
			}
		}
		return $ret;
	}	

	//针对将课程完结改为未完结的情况
	public static function reduceTeacherStatCourseCompleteCount($tidarr){
		$retset = stat_db::getTeacherStatByTidArr($tidarr);	
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(in_array($tid,$stat_tidarr) && !empty($stat_ret[$tid]['course_complete_count'])){
				$set_arr = array("`course_complete_count` =`course_complete_count`- 1","course_remain_count= course_remain_count + 1");
				$ret = stat_db::updateTeacherStat($tid,$set_arr);	
			}
		}
		return $ret;
	}	

	//针对修改班级或者删除班级之前没有这节课的操作
	public static function addTeacherStatCourseCompleteCountByClass($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatByTid($teacher_id);	
			if(!empty($retset)){
				$set_arr = array("`course_complete_count` =`course_complete_count` + 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
			}else{
				if(!empty($teacher_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"course_complete_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatCourseCompleteCountByClass($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_complete_count'])){
				$set_arr = array("`course_complete_count` =`course_complete_count` - 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	//修改教师剩余课程数
	public static function addTeacherStatCourseRemainCount($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatByTid($teacher_id);	
			if(!empty($retset)){
				$set_arr = array("`course_remain_count` =`course_remain_count` + 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
			}else{
				if(!empty($teacher_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"course_remain_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);	
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatCourseRemainCount($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_remain_count'])){
				$set_arr = array("`course_remain_count` =`course_remain_count` - 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	//修改教师上架课程数
	public static function addTeacherStatCourseOnCount($tidarr){
		$retset = stat_db::getTeacherStatByTidArr($tidarr);
		$stat_tidarr = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
			}
		}
		foreach($tidarr as $tid){
			if(!in_array($tid,$stat_tidarr)){
				if(!empty($tid)){
					$statdata = array(
							"fk_user"=>$tid,
							"course_on_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);	
				}
			}else{
				$set_arr = array("`course_on_count` =`course_on_count`+ 1");
				$ret = stat_db::updateTeacherStat($tid,$set_arr);	
			}
		}
		return $ret;
	}	

	public static function reduceTeacherStatCourseOnCount($tidarr){
		$retset = stat_db::getTeacherStatByTidArr($tidarr);	
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(in_array($tid,$stat_tidarr) && !empty($stat_ret[$tid]['course_on_count'])){
				$set_arr = array("`course_on_count` =`course_on_count`- 1");
				$ret = stat_db::updateTeacherStat($tid,$set_arr);	
			}
		}
		return $ret;
	}	

	//修改教师未上架课程数
	public static function addTeacherStatCourseOffCount($tidarr){
		$retset = stat_db::getTeacherStatByTidArr($tidarr);
		$stat_tidarr = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
			}
		}
		foreach($tidarr as $tid){
			if(!in_array($tid,$stat_tidarr)){
				if(!empty($tid)){
					$statdata = array(
							"fk_user"=>$tid,
							"course_off_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);	
				}
			}else{
				$set_arr = array("`course_off_count` =`course_off_count`+ 1");
				$ret = stat_db::updateTeacherStat($tid,$set_arr);	
			}
		}
		return $ret;
	}	

	public static function reduceTeacherStatCourseOffCount($tidarr){
		$retset = stat_db::getTeacherStatByTidArr($tidarr);	
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(in_array($tid,$stat_tidarr) && !empty($stat_ret[$tid]['course_off_count'])){
				$set_arr = array("`course_off_count` =`course_off_count`- 1");
				$ret = stat_db::updateTeacherStat($tid,$set_arr);	
			}
		}
		return $ret;
	}	

	//针对修改班级或者删除班级之前没有这节课的操作
	public static function addTeacherStatCourseOnCountByClass($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatByTid($teacher_id);	
			if(!empty($retset)){
				$set_arr = array("`course_on_count` =`course_on_count` + 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
			}else{
				if(!empty($teacher_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"course_on_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatCourseOnCountByClass($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_on_count'])){
				$set_arr = array("`course_on_count` =`course_on_count` - 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	public static function addTeacherStatCourseOffCountByClass($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatByTid($teacher_id);	
			if(!empty($retset)){
				$set_arr = array("`course_off_count` =`course_off_count` + 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
			}else{
				if(!empty($teacher_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"course_off_count"=>1,
						);
					$ret = stat_db::addTeacherStat($statdata);
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatCourseOffCountByClass($teacher_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id);
		$retset = stat_db::getTeacherStatByTid($teacher_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_off_count'])){
				$set_arr = array("`course_off_count` =`course_off_count` - 1");
				$ret = stat_db::updateTeacherStat($teacher_id,$set_arr);	
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	/*教师分机构表字段的所有操作*/
	//$teacher_id是班主任
	public static function addTeacherStatOrgStudentCount($teacher_id,$owner_id,$student_count){
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id, $owner_id);	
		if(empty($retset) ){
			if(!empty($teacher_id) && !empty($owner_id)){
				$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"student_count"=>$student_count,
						);
				$ret = stat_db::addTeacherStatOrg($statdata);	
			}
		}else{
			$set_arr = array("`student_count` =`student_count`+ $student_count");
			$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
		}
		return $ret;
	}	

	//$teacher_id是班主任
	public static function reduceTeacherStatOrgStudentCount($teacher_id,$owner_id,$student_count){
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id, $owner_id);	
		if(!empty($retset) && !empty($retset['student_count'])){
			$set_arr = array("`student_count` =`student_count`- $student_count");
			$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
			return $ret;
		}else{
			return false;
		}
	}	

	public static function setTeacherStatOrgTotaltime($teacher_id,$owner_id,$beforeTotalTime,$modifyTotalTime){
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id, $owner_id);	
		if( empty($retset) ){
			if(!empty($teacher_id) && !empty($owner_id)){
				$totaltime = 0-$beforeTotalTime + $modifyTotalTime;
				if($totaltime < 0){
					$totaltime = 0;
				}
				$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"totaltime"=>$totaltime,
						);
				$ret = stat_db::addTeacherStatOrg($statdata);	
			}
		}else{
			$totaltime = $retset['totaltime']-$beforeTotalTime + $modifyTotalTime;
			if($totaltime < 0){
				$totaltime = 0;
			}
			$set_arr = array("`totaltime` = $totaltime");
			$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
		}
		return $ret;
	}	

	//修改教师分机构评分和评分人数
	public static function setTeacherStatOrgAvgScore($teacher_id,$owner_id,$avg_score,$total_user){
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
		if(empty($retset)){
			if(!empty($teacher_id) && !empty($owner_id)){
				$statdata = array(
						"fk_user"=>$teacher_id,
						"fk_user_owner"=>$owner_id,
						"score_user_count"=>$total_user,
						"avg_score"=>$avg_score,
					);
				$ret = stat_db::addTeacherStatOrg($statdata);	
			}
		}else{
			$set_arr = array("`score_user_count` = $total_user","avg_score=$avg_score ");
			$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
		}
		return $ret;
	}	


	//$tidarr教师id数组,针对课程完结的时候修改状态的操作	
	public static function addTeacherStatOrgCourseCompleteCount($tidarr,$owner_id){
		$retset = stat_db::getTeacherStatOrgByTidArr($tidarr,$owner_id);
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(!in_array($tid,$stat_tidarr)){
				if(!empty($tid) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$tid,
							"fk_user_owner"=>$owner_id,
							"course_complete_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);	
				}
			}else{
				if(!empty($stat_ret[$tid]['course_remain_count'])){
					$set_arr = array("`course_complete_count` =`course_complete_count`+ 1","course_remain_count= course_remain_count - 1");
					$ret = stat_db::updateTeacherStatOrg($tid,$owner_id,$set_arr);	
				}
			}
		}
		return $ret;
	}	
	//针对将课程完结改为未完结的情况
	public static function reduceTeacherStatOrgCourseCompleteCount($tidarr,$owner_id){
		$retset = stat_db::getTeacherStatOrgByTidArr($tidarr,$owner_id);	
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(in_array($tid,$stat_tidarr) && !empty($stat_ret[$tid]['course_complete_count'])){
				$set_arr = array("`course_complete_count` =`course_complete_count`- 1","course_remain_count= course_remain_count + 1");
				$ret = stat_db::updateTeacherStatOrg($tid,$owner_id,$set_arr);	
			}
		}
		return $ret;
	}	

	//针对修改班级或者删除班级之前没有这节课的操作
	public static function addTeacherStatOrgCourseCompleteCountByClass($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
			if(!empty($retset)){
				$set_arr = array("`course_complete_count` =`course_complete_count` + 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
			}else{
				if(!empty($teacher_id) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"course_complete_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatOrgCourseCompleteCountByClass($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);
		
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_complete_count'])){
				$set_arr = array("`course_complete_count` =`course_complete_count` - 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	public static function addTeacherStatOrgCourseRemainCount($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
			if(!empty($retset)){
				$set_arr = array("`course_remain_count` =`course_remain_count` + 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
			}else{
				if(!empty($teacher_id) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"course_remain_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);	
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatOrgCourseRemainCount($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_remain_count'])){
				$set_arr = array("`course_remain_count` =`course_remain_count` - 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	public static function addTeacherStatOrgComment($teacher_id,$owner_id,$comment){
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
		if(empty($retset)){
			if(!empty($teacher_id) && !empty($owner_id)){
				$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"comment"=>$comment,
					);
				$ret = stat_db::addTeacherStatOrg($statdata);	
			}
		}else{
			$set_arr = array("`comment` = `comment`+$comment");
			$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
		}
		return $ret;
	}

	public static function reduceTeacherStatOrgComment($teacher_id,$owner_id,$comment){
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
		if(!empty($retset) && !empty($retset['comment'])){
			$set_arr = array("`comment` = `comment`-$comment");
			$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);
			return $ret;
		}else{
			return false;
		}
	}

	//修改教师分机构上架课程数
	public static function addTeacherStatOrgCourseOnCount($tidarr,$owner_id){
		$retset = stat_db::getTeacherStatOrgByTidArr($tidarr,$owner_id);
		$stat_tidarr = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
			}
		}
		foreach($tidarr as $tid){
			if(!in_array($tid,$stat_tidarr)){
				if(!empty($tid) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$tid,
							"fk_user_owner"=>$owner_id,
							"course_on_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);	
				}
			}else{
				$set_arr = array("`course_on_count` =`course_on_count`+ 1");
				$ret = stat_db::updateTeacherStatOrg($tid,$owner_id,$set_arr);	
			}
		}
		return $ret;
	}	
	public static function reduceTeacherStatOrgCourseOnCount($tidarr,$owner_id){
		$retset = stat_db::getTeacherStatOrgByTidArr($tidarr,$owner_id);	
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(in_array($tid,$stat_tidarr) && !empty($stat_ret[$tid]['course_on_count'])){
				$set_arr = array("`course_on_count` =`course_on_count`- 1");
				$ret = stat_db::updateTeacherStatOrg($tid,$owner_id,$set_arr);	
			}
		}
		return $ret;
	}	
	
	//修改教师分机构未上架课程数
	public static function addTeacherStatOrgCourseOffCount($tidarr,$owner_id){
		$retset = stat_db::getTeacherStatOrgByTidArr($tidarr,$owner_id);
		$stat_tidarr = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
			}
		}
		foreach($tidarr as $tid){
			if(!in_array($tid,$stat_tidarr)){
				if(!empty($tid) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$tid,
							"fk_user_owner"=>$owner_id,
							"course_off_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);	
				}
			}else{
				$set_arr = array("`course_off_count` =`course_off_count`+ 1");
				$ret = stat_db::updateTeacherStatOrg($tid,$owner_id,$set_arr);	
			}
		}
		return $ret;
	}	

	public static function reduceTeacherStatOrgCourseOffCount($tidarr,$owner_id){
		$retset = stat_db::getTeacherStatOrgByTidArr($tidarr,$owner_id);	
		$stat_tidarr = array();
		$stat_ret = array();
		$ret = false;
		if(!empty($retset)){
			foreach($retset->items as $ro){
				$stat_tidarr[] = $ro['fk_user'];
				$stat_ret[$ro['fk_user']] = $ro;
			}
		}
		foreach($tidarr as $tid){
			if(in_array($tid,$stat_tidarr) && !empty($stat_ret[$tid]['course_off_count'])){
				$set_arr = array("`course_off_count` =`course_off_count`- 1");
				$ret = stat_db::updateTeacherStatOrg($tid,$owner_id,$set_arr);	
			}
		}
		return $ret;
	}	

	//针对班级修改班主任或删除班级，排课修改老师
	public static function addTeacherStatOrgCourseOnCountByClass($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
			if(!empty($retset)){
				$set_arr = array("`course_on_count` =`course_on_count` + 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
			}else{
				if(!empty($teacher_id) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"course_on_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatOrgCourseOnCountByClass($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_on_count'])){
				$set_arr = array("`course_on_count` =`course_on_count` - 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	

	public static function addTeacherStatOrgCourseOffCountByClass($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		if(!$check_ret){
			$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
			if(!empty($retset)){
				$set_arr = array("`course_off_count` =`course_off_count` + 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
			}else{
				if(!empty($teacher_id) && !empty($owner_id)){
					$statdata = array(
							"fk_user"=>$teacher_id,
							"fk_user_owner"=>$owner_id,
							"course_off_count"=>1,
						);
					$ret = stat_db::addTeacherStatOrg($statdata);
				}	
			}
			return $ret;
		}else{
			return false;
		}
	}	

	public static function reduceTeacherStatOrgCourseOffCountByClass($teacher_id,$owner_id,$course_id){
		$check_ret = self::checkTeacherCourse($teacher_id,$course_id,$owner_id);
		$retset = stat_db::getTeacherStatOrgByTid($teacher_id,$owner_id);	
		if(!$check_ret && !empty($retset)){
			if(!empty($retset['course_off_count'])) {
				$set_arr = array("`course_off_count` =`course_off_count` - 1");
				$ret = stat_db::updateTeacherStatOrg($teacher_id,$owner_id,$set_arr);	
				return $ret;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	
	
	public static function getDayUserOrgOrderStatByPkday($minDate,$maxDate){
		 if(empty($minDate) && empty($maxDate)){
            return false;
        }
		$orgRet = stat_db::getDayUserOrgOrderStatByPkday($minDate,$maxDate);
		if(!empty($orgRet->items)){
			$orgList  = array();
			$statList = array();
			foreach($orgRet->items as $org){
				if($org['fk_user']){
					$ownerIdArr[] = $org['fk_user'];
				}
				$statList[] = $org; 
			}
			if(!empty($ownerIdArr)){
				$orgInfo = user_db::getOrginfoByUidArr($ownerIdArr);
				if(!empty($orgInfo->items)){
					foreach($orgInfo->items as $vo){
						$orgList[$vo['user_owner']]['name'] = $vo['name'];
						$orgList[$vo['user_owner']]['subname'] = $vo['subname'];
					}
				}
			}
			foreach($statList as &$stat ){
				if(!empty($orgList[$stat['fk_user']])){
					$stat['org_name'] = $orgList[$stat['fk_user']]['name'];
					$stat['org_subname'] = $orgList[$stat['fk_user']]['subname'];
				}else{
					$stat['org_name'] = '';
					$stat['org_subname'] = '';
				}
			}
			return $statList;
		}else{
			return false;
		}
	}
	/* 机构用户日报表api */
	public static function getDayOrgUserStat($searchDate){
            if(empty($searchDate)){
                return false;
            }
            $orgRet = stat_db::getDayOrgUserStat($searchDate);
            if(!empty($orgRet->items)){
                $statList = array();
                foreach($orgRet->items as $org){
                    //查询机构详情
                    $org_pro_ret = user_db::getOrgProfileByOidArr(array($org['fk_org']));
                    $org['org_name'] = $org_pro_ret->items[0]['subname'];
                    $statList[] = $org; 
                }
                return $statList;
            }else{
                return false;
            }
	}
        
	public function dayUserStatOrgVvList($orgUserin,$timesin,$timeein,$seldatain){
		$orgUser = (int)$orgUserin;
		$times = $timesin;
		$timee = $timeein;
		$stat_db  = new stat_db();
		$dayVvlist1  = $stat_db->dayUserStatOrgVvList($orgUser,$times,$timee,$seldatain);
		if ($dayVvlist1 === false) return false;
		$dayVvlist  = $dayVvlist1->items;
		$arrayPort = array();
		foreach($dayVvlist as $k=>$v){
			$arrayPort[$v["day"]][] = $v;
		}

		if (empty($dayVvlist)) {
			;
		}else{
			$port = array();
			foreach($arrayPort as $ak=>$dayVvlist){
				$port[$ak]["zero"] = 0;
				$port[$ak]["one"] = 0;
				$port[$ak]["two"] = 0;
				$port[$ak]["three"] = 0;
				$port[$ak]["four"] = 0;
				$port[$ak]["five"] = 0;
				//$port["six"] = 0;
				$port[$ak]["six-ten"] =0;
				$port[$ak]["ten-twenty"] = 0;
				$port[$ak]["twenty-fifty"] = 0;
				$port[$ak]["fifty-1h"] = 0;
				$port[$ak]["1h-2h"] = 0;
				$port[$ak]["2h-5h"] = 0;
				$port[$ak]["5h-10h"] = 0;
				$port[$ak]["10h-50h"] = 0;
				$port[$ak]["50h-100h"] = 0;
				$port[$ak]["100h"] = 0;

				foreach($dayVvlist as $dk=>$dv){
					if($dv["vv_live"]+$dv["vv_record"]==0){
						$port[$ak]["zero"] +=1;
					}elseif($dv["vv_live"]+$dv["vv_record"]==1){
						$port[$ak]["one"] +=1;
					}elseif($dv["vv_live"]+$dv["vv_record"]==2){
						$port[$ak]["two"] +=1;
					}elseif($dv["vv_live"]+$dv["vv_record"]==3){
						$port[$ak]["three"] +=1;
					}elseif($dv["vv_live"]+$dv["vv_record"]==4){
						$port[$ak]["four"] +=1;
					}elseif($dv["vv_live"]+$dv["vv_record"]==5){
						$port[$ak]["five"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=6) && ($dv["vv_live"]+$dv["vv_record"]<10)){
						$port[$ak]["six-ten"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=10) && ($dv["vv_live"]+$dv["vv_record"]<20)){
						$port[$ak]["ten-twenty"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=20) && ($dv["vv_live"]+$dv["vv_record"]<50)){
						$port[$ak]["twenty-fifty"] += 1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=50) && ($dv["vv_live"]+$dv["vv_record"]<100)){
						$port[$ak]["fifty-1h"] += 1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=100) && ($dv["vv_live"]+$dv["vv_record"]<200)){
						$port[$ak]["1h-2h"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=200) && ($dv["vv_live"]+$dv["vv_record"]<500)){
						$port[$ak]["2h-5h"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=500) && ($dv["vv_live"]+$dv["vv_record"]<1000)){
						$port[$ak]["5h-10h"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=1000) && ($dv["vv_live"]+$dv["vv_record"]<5000)){
						$port[$ak]["10h-50h"] +=1;
					}elseif(($dv["vv_live"]+$dv["vv_record"]>=5000) && ($dv["vv_live"]+$dv["vv_record"]<10000)){
						$port[$ak]["50h-100h"] +=1;
					}elseif($dv["vv_live"]+$dv["vv_record"]>=10000){
						$port[$ak]["100h"] +=1;
					}
				}
			}
		}
		$ret        = new stdClass;
		$ret->data  = $port;
		//$ret->data  = $arrayPort;
        return $ret;
    }
	
	public function dayUserStatOrgTotalVvList($orgUserin,$timesin,$timeein,$seldatain){
		$orgUser = (int)$orgUserin;
		$times = $timesin;
		$timee = $timeein;
		$stat_db  = new stat_db();
		$dayVvlist1  = $stat_db->dayUserStatOrgTotalVvList($orgUser,$times,$timee,$seldatain);
		if ($dayVvlist1 === false) return false;
		$dayVvlist  = $dayVvlist1->items;


		$arraOut = array(
			//'orguser' => '0',
			'vv_1' => '0',
			'vv_2' => '0',
			'vv_3' => '0',
			'vv_4' => '0',
			'vv_5' => '0',
			'vv_6' =>'0',
			'vv_7_15' => '0',
			'vv_16_30' => '0',
			'vv_31' => '0',
		);
		if(!empty($dayVvlist)){
			foreach($dayVvlist as $k=>$dayv){
				foreach($dayv as $dk=>$dv){
					if(isset($arraOut[$dk])){
						$arraOut[$dk] += $dv;	
					}
				}
			}
		}

		$ret = new stdClass;
		//$ret->data  = $dayVvlist;
		$ret->data = $arraOut;
		if(!empty($dayVvlist)){
			$ret->data['Sourcedata'] = $dayVvlist;
		}else{
			$ret->data['Sourcedata'] = 0;
		}
		return $ret;
	}


	
	
	
}
