<?php

/**
 * @author hetao fanbin
 */
class message_db{
	public static function InitDB($dbname="db_message",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}

	function addSetting($data){
		$db = self::InitDB("db_message");
		$key = md5("message_db.t_message_plan_setting.".$data->fk_plan);
		$v = redis_api::get($key);
		if($v){
			$v["text_pattern"] = $data->text_pattern;
			redis_api::set($key, $v, 7200);
		}
		$table = array("t_message_plan_setting");
		$condition = "fk_plan=$data->fk_plan";
		$item = array("text_pattern"=>$data->text_pattern);
		$ret = $db->update($table, $condition, $item);
		if($ret){
			return $ret;
		}
		return $db->insert($table, $data);
	}
	public function getSetting($fk_plan){
		$db = self::InitDB("db_message","query");
		$key = md5("message_db.t_message_plan_setting.$fk_plan");
		$v = redis_api::get($key);
		if($v){
			return $v;
		}
		$item = new stdclass;
		$item->set_id = "pk_set";
		$item->plan_id = "fk_plan";
		$item->text_pattern = "text_pattern";
		$table = array("t_message_plan_setting");
		$condition = "fk_plan=$fk_plan";
		$v = $db->selectOne($table, $condition, $item);
		if(!$v){
			$v = array("plan_id"=>$fk_plan, "text_pattern"=>"normal");
		}
		redis_api::set($key, $v, 7200);
		return $v;
	}
	public function addText($params){
		$db = self::InitDB("db_message");
		$data = new stdclass;
		$data->fk_user_from = $params->user_from_id;
		if(!empty($params->user_to_id)){
			$data->fk_user_to = $params->user_to_id;
		}else{
			$data->fk_user_to = 0;
		}
		$data->fk_plan = $params->plan_id;
		$data->type = $params->type;
		$data->content = $params->content;
		if(!empty($params->live_second)){
			$data->live_second = $params->live_second;
			$data->live_second_original = $params->live_second;
		}else{
			$data->live_second = 0;
		}
		$table = array("t_message_plan_text");
		$ret = $db->insert($table, $data);

		$key = "t_message_plan_text.".$params->plan_id;
		redis_api::del($key);

		return $ret;
	}
	public function deleteText($plan_id,$text_id){
		$db = self::InitDB("db_message");
		$condition = "pk_msg_text=$text_id";
		$item = array("status"=>-1);
		$table = array("t_message_plan_text");
		$ret = $db->update($table, $condition, $item);
		$key = "t_message_plan_text.".$plan_id;
		redis_api::del($key);
		return $ret;
	}
	public function updateTextLiveSecond($plan_id, $start, $addTime){
		$db = self::InitDB("db_message");
		$condition = "fk_plan=$plan_id and last_updated>='$start' and live_second_original>0";
		$item = "live_second=live_second_original+$addTime";
		$table = array("t_message_plan_text");
		$key = "t_message_plan_text.".$plan_id;
		redis_api::del($key);
		$ret = $db->update($table, $condition, $item);
		return $ret;
	}
	public function addGood($params){
		$db = self::InitDB("db_message");
		$data = new stdclass;
		$data->fk_user = $params->user_to_id;
		$data->fk_plan = $params->plan_id;
		$data->type = $params->type;
		$table = array("t_message_plan_good");
		$update=array("num=num+1");
		$ret = $db->insert($table, $data, $isreplace=false,$isdelayed=false,$update);

		$key = "t_message_plan_good.".$params->plan_id;
		redis_api::del($key);

		return $ret;
	}
	/**
	 * 改进，分页获取，每页获取1000
	 * @return array
	 */
	public function getText($plan_id){
		$db = self::InitDB("db_message","query");

		$key = "t_message_plan_text.$plan_id";
		$v = redis_api::get($key);
		if($v){
			return $v;
		}
		$data=array();

		$item = new stdclass;
		$item->msg_id = "pk_msg_text";
		$item->user_from_id = "fk_user_from";
		$item->user_to_id = "fk_user_to";
		$item->plan_id = "fk_plan";
		$item->type = "type";
		$item->content = "content";
		$item->live_second = "live_second";
		$item->last_updated = "last_updated";
		$table = array("t_message_plan_text");
		$condition = "fk_plan=$plan_id and status=0";
		$page=1;
		do{
			$db->setLimit(1000);
			$db->setPage($page++);
			$db->setCount(false);
			$v = $db->select($table, $condition, $item, "", "pk_msg_text");
			if(!empty($v->items)){
				$data=array_merge($data,$v->items);
			}else{
				break;
			}
		}while(true);
		redis_api::set($key, $data, 3600*5);
		return $data;
	}
	/**
	 * 最取200个用户的数据
	 */
	public function getGood($plan_id){
		$db = self::InitDB("db_message","query");

		$key = "t_message_plan_good.$plan_id";
		$v = redis_api::get($key);
		if($v){
			return $v;
		}
		$item = new stdclass;
		$item->msg_id = "pk_msg_good";
		$item->user_id = "fk_user";
		$item->plan_id = "fk_plan";
		$item->type = "type";
		$item->num = "num";
		$item->last_updated = "last_updated";
		$table = array("t_message_plan_good");
		$condition = "fk_plan=$plan_id and status=0";
		$db->setLimit(200);//只取200个
		$db->setPage(1);
		$v = $db->select($table, $condition, $item, "", "pk_msg_good");
		redis_api::set($key, $v->items, 3600*5);
		return $v->items;
	}
	function addSingleForbid($data){
		$db = self::InitDB("db_message");
		$key = md5("message_db.t_message_plan_text_forbid.".$data->fk_plan.".".$data->fk_user);
		$key2 = md5("message_db.t_message_plan_text_forbid.".$data->fk_plan);
		$v = array("status"=>$data->status);
		redis_api::set($key, $v, 7200);
		$table = array("t_message_plan_text_forbid");
		$ret = $db->insert($table, $data, True);
		redis_api::del($key2);
		return $ret;
	}
	public function getSingleForbid($fk_plan, $fk_user){
		$db = self::InitDB("db_message","query");
		$key = md5("message_db.t_message_plan_text_forbid.$fk_plan.$fk_user");
		$v = redis_api::get($key);
		if($v){
			return $v;
		}
		$item = new stdclass;
		$item->status = "status";
		$table = array("t_message_plan_text_forbid");
		$condition = "fk_plan=$fk_plan and fk_user=$fk_user";
		$v = $db->selectOne($table, $condition, $item);
		if(!$v){
			$v = array("status"=>"none");
		}
		redis_api::set($key, $v, 7200);
		return $v;
	}
	public function getSingleForbidByPlan($plan_id){
		$db = self::InitDB("db_message","query");
		$key = md5("message_db.t_message_plan_text_forbid.$plan_id");
		$v = redis_api::get($key);
		if($v){
			return $v;
		}
		$item = new stdclass;
		$item->user_id = "fk_user";
		$item->status = "status";
		$table = array("t_message_plan_text_forbid");
		$condition = "fk_plan=$plan_id";
		$v = $db->select($table, $condition, $item);
		redis_api::set($key, $v, 7200);
		return $v;
	}

	public function getTeacherScoreByTidArr($tid_str){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_teacher_total");
		$condition = "fk_user_teacher IN ($tid_str)";
		$item = new stdclass;
		$item->teacher_id = "fk_user_teacher";
		$item->owner_id   = "fk_user_owner";
		$item->total_user = "total_user";
		$item->avg_score  = "score";
		$item->student_score  = "student_score";
		$item->desc_score  = "desc_score";
		$item->explain_score  = "explain_score";
		return $db->select($table,$condition, $item);
	}

	public function getTeacherScoreByTid($tid){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_teacher_total");
		$condition = "fk_user_teacher = $tid";
		$item = new stdclass;
		$item->teacher_id = "fk_user_teacher";
		$item->owner_id   = "fk_user_owner";
		$item->total_user = "total_user";
		$item->avg_score  = "avg_score";
		return $db->select($table,$condition, $item);
	}

	//获取老师评分
	public function getTeacherScore($tid){
			$db = self::InitDB("db_message","query");
			$table = array("t_score_teacher_total");
			$condition = "fk_user_teacher = $tid";
			return $db->select($table,$condition);
	}
	public function listCourseScoreByCourseIds( $courseIdStr ){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_course_total");
		$condition = "fk_course IN ($courseIdStr)";
		return $db->select($table,$condition);
	}

	public function getPlanGoodByPidArr($pid_arr){
		$db = self::InitDB("db_message","query");
		$table = array("t_message_plan_good");
		$pid_str = implode(',',$pid_arr);
		$condition = "fk_plan IN ($pid_str) and status = 0";
		$item = array('fk_user','fk_plan','sum(num) as num');
		$groupby = 'fk_user';
		return $db->select($table,$condition,$item,$groupby);
	}

	public function getPlanScoreByPidArr($pid_arr){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_course_detail");
		$pid_str = implode(',',$pid_arr);
		$condition = "fk_plan IN ($pid_str)";
		$item = array('count(fk_user) as user_total','sum(avg_score) as score_count');
		return $db->select($table,$condition,$item);
	}

}
