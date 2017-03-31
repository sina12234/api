<?php
class exam_log{
	public function __construct($inPath){
		return;
	}

	public function pageLogIssue($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		if($params->user_id != $plan["user_id"] && $params->user_id != $plan["user_plan_id"]){
			$ret->result->msg = "不是老师";
			return $ret;
		}
		$exam_db = new exam_db;
		foreach($params->data as $v){
			$exam_db->logPlanUserQuestion($params->plan_id, $v->user_id, $params->question_id, $v->fk_answers, $v->options, $plan["course_id"], $v->correct);
			$exam_db->logUserQuestionCountForPlan($v->user_id, $params->question_id, $v->correct);
		}
		$ret->result->code = 0;
		return $ret;
	}
	/* 备课出题 列表 */
    public function pageLogUserQues($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
	/*	$params = new stdclass;
		$params->user_id = 153;
		$params->plan_id = 397;
	*/
		if(empty($params->plan_id) ||empty($params->user_id)){
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		$exam_api = new exam_api;
		$data = array();
		$data["plan_id"] = $params->plan_id;
		$data["user_id"] = $params->user_id;
		$ret = $exam_api->logUserQuestionForPlanList($data,$page=0,$length=0,$item=0,$orderby=array());
		$ret->result = new stdclass;
		$ret->result->code = "0";
		return $ret;
	}
    /* 快速出题 列表 */
    public function pageLogUserQuesQuick($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->plan_id) ||empty($params->user_id)){
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		$exam_api = new exam_api;
		$data = new stdClass();
		$data->pid = $params->plan_id;
		$data->uid = $params->user_id;
        $stat_db = new stat_db();
		$ret = $stat_db::getPlanPhraseLogByPid($data,$page=0,$length=0);
		$ret->result = new stdclass;
		$ret->result->code = "0";
		return $ret;
	}
}
?>
