<?php
/**
 * @author  fanbin
 */
class exam_db{
	public static function InitDB($dbname="db_exam",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
	/*
	 * 增加问题
	 $data = array(
		 desc = 
		 desc_img = 
		 correct =  0
	 );
	 */
	public function addQuestion($data){
		$db = self::InitDB();
		$table = "t_question";
		return $db->insert($table,$data);
	}
	public function getQuestion($qid){
        $db = self::InitDB("db_exam","query");
		$table = "t_question";
		$condition = array();
		$condition["pk_question"] = $qid;
		$ret = $db->selectOne($table,$condition);
		return $ret;
	}
	public function getMaxQuestionIdByUid($uid){
        $db = self::InitDB("db_exam","query");
		$table = "t_question";
		$condition = array();
		$condition["fk_user_org"] = $uid;
		$row = $db->selectOne($table,$condition,"max(pk_question) as question_id");
		if($row===false){
			return $row;
		}
		if(empty($row['question_id'])){
			return 0;
		}
		return $row['question_id'];
	}
	/*
	 *更新题目信息
	 */
	public function updateQuestion($qid,$data=array()){
        $db = self::InitDB("db_exam");
		$table = "t_question";
		$condition = array();
		$condition["pk_question"] = $qid;
		return $db->update($table,$condition,$data);	
	}
	/*
	 * 按照机构用户列取题目
	 * uid 机构所有者 
	 */
/*	public function listQuestions($uid,$data=array(),$page,$length){
        $db = self::InitDB("db_exam","query");
		$table = "t_question";
		$item=new stdclass;
		$item->question_id="pk_question";
		$item->uid = "fk_user_org";
		$item->type = "type";
		$item->fk_subject = "subject_id";
		$item->fk_grade = "grade_id";
		$item->t_question.desc = "t_question.desc";
		$item->t_question.desc_img = "t_question.desc_img";
		$item->result = "result";
		$item->mode = "mode";
		$item->task_use_num = "task_use_num";
		$item->task_correct_num = "task_correct_num";
		$item->plan_use_num = "plan_use_num";
		$item->plan_correct_num = "plan_correct_num";
		$condition = array();
		$orderby = array(
			"pk_question"=>"DESC", 
		);
		$db->setPage($page);
		$db->setLimit($length);
		$ret = $db->select($table,$condition,$item,"",$orderby);
		return $ret;
	}
*/
	/*
	 * 按照机构用户和题目id列取答案
	 * uid 机构所有者 
	 * qid    问题id
	 */
	public function listAnswers($qid,$data=array()){
        $db = self::InitDB("db_exam","query");
		$table = "t_question_answer";
		$item=new stdclass;
		$item->answer_id="pk_answer";
		$item->question_id = "fk_question";
		$item->desc = "desc";
		$item->desc_img = "desc_img";
		$item->correct= "correct";
		$condition = array();
		$orderby = array(
			"pk_answer"=>"DESC", 
		);
	//	$db->setPage($page);
	//	$db->setLimit($length);
		$ret = $db->select($table,$condition,$item,"",$orderby);
		return $ret;
	}
	
	/*
	 * 增加问题的答案
	 $data = array(
		 fk_question = 
		 desc = 
		 desc_img = 
		 correct =  0
	 );
	 */
	public function addAnswer($data=array()){
		$db = self::InitDB();
		$table = "t_question_answer";
		$datain = array(
			"fk_question" => $data["question_id"],
			"correct" => $data["correct"],
		);
		if(isset($data["desc"])){
			$datain["desc"] = $data["desc"];
		}
		if(isset($data["desc_img"])){
			$datain["desc_img"] = $data["desc_img"];
		}
		return $db->insert($table,$datain);
	}
	/*
	 * 按照机构用户和题目id列取答案
	 * uid 机构所有者 
	 * qid    问题id
	 */
	public function listAnswersByQuestion($uid,$qid,$data=array()){
        $db = self::InitDB("db_exam","query");
		$table = "t_question_answer";
		$item=new stdclass;
		$item->answer_id="pk_answer";
		$item->question_id = "fk_question";
		$item->desc = "desc";
		$item->desc_img = "desc_img";
		$item->correct= "correct";
		$condition = array();
		$orderby = array();
		$orderby["pk_answer"] = "desc";
		$ret = $db->select($table,$condition,$item,"",$orderby);
		return $ret;
	}
	public function logPlanUserQuestion($plan_id, $user_id, $question_id, $fk_answers, $options, $course_id, $correct){
		$db = self::InitDB("db_exam");
		$data = new stdclass;
		$data->fk_plan = $plan_id;
		$data->fk_user = $user_id;
		$data->fk_question = $question_id;
		$data->fk_answers = $fk_answers;
		$data->options = $options;
		$data->fk_course = $course_id;
		$data->correct = $correct;
		$table = array("t_log_plan_user_question");
		$ret = $db->insert($table, $data);
		return $ret;
	}
	public function logUserQuestionCountForPlan($user_id, $question_id, $correct){
		$db = self::InitDB("db_exam");
		$data = new stdclass;
		$data->fk_user = $user_id;
		$data->fk_question = $question_id;
		$data->plan_use_num = 1;
		$data->plan_correct_num = $correct;
		$table = array("t_log_user_question_count");
		$ret = $db->insert($table, $data);
		if($ret){
			$condition = "fk_user=$user_id and fk_question=$question_id";
			if($correct){
				$item = array("plan_use_num=plan_use_num+1", "plan_correct_num=plan_correct_num+1");
			}else{
				$item = array("plan_use_num=plan_use_num+1");
			}
			$ret = $db->update($table, $condition, $item);
		}
		return $ret;
	}
	/*
	 * 获取一个用户的统计信息
	 *
	 */
	public function logUserQuestionForPlanList($data = array(), $page=null, $length=null, $item='', $orderby=array(), $groupby=''){
		$db = self::InitDB("db_exam");
		$table = array("t_log_plan_user_question");
		$items = new stdclass;
		$items->plan_id = "fk_plan";
		$items->user_id = "fk_user";
		$items->question_id = "fk_question";
		$items->answers = "fk_answers";
		$items->options = "options";
		$items->course_id = "fk_course";
		$items->correct = "correct";
		$condition = array();
		$condition["fk_plan"] = $data["plan_id"];
		$condition["fk_user"] = $data["user_id"];
		$orderby = array();
		return $db->select($table,$condition,$items,$groupby,$orderby,"");	
	}
	public function getUserRightAnswerCountByPidArr($uid,$pid_arr){
		$db = self::InitDB("db_exam");
		$table = array("t_log_plan_user_question");
		$pid_str = implode(',',$pid_arr);
		$condition = "fk_user = $uid and fk_plan in ($pid_str) and correct = 1";
		$item = array('count(correct) as count');
		$groupby = 'fk_question';
		return $db->select($table,$condition,$item,$groupby);	
	}

	public function getLogUserQuestionByPid($uid=0,$pid){
		$db = self::InitDB("db_exam");
		$table = array("t_log_plan_user_question");

		$condition = "fk_plan = $pid ";
		if(!empty($uid)){
			$condition .= " AND fk_user = $uid";
		}
		$groupby = 'fk_question';
		return $db->select($table,$condition,'',$groupby);	
	}
	
	public function getAnswersByAidStr($aidStr){
        $db = self::InitDB("db_exam","query");
		$table = "t_question_answer";
		$condition = "pk_answer in ($aidStr)";
		$orderby = array(
			"pk_answer"=>"DESC", 
		);
		$ret = $db->select($table,$condition,$item,"",$orderby);
		return $ret;
	}
	
	public function listQuestions($page = 1, $length = 10){
		$db = self::InitDB("db_exam","query");
		$table = "t_question";
		$left = new stdclass;
		$left->t_question_stat = "t_question.pk_question = t_question_stat.fk_question";
		$condition = "t_question.status <> -1";
        $orderby   = array("t_question.pk_question" => "asc");
		$item = array('t_question.pk_question','t_question.fk_user_org','t_question.fk_user','t_question.type','t_question.source','t_question.descript',
					't_question.desc_img','t_question.mode','t_question.result','t_question.analysis','t_question.status','t_question.create_time','t_question.last_updated',
					't_question_stat.task_use_num','t_question_stat.task_correct_num','t_question_stat.plan_use_num','t_question_stat.plan_correct_num');
		$db->setPage($page);
        $db->setLimit($length);
		return $db->select($table, $condition, $item, "", $orderby, $left);
	}
	
	public function listFavQuestionByQids($qids){
		$db = self::InitDB("db_exam","query");
		$table = "t_fav_user_question";
		$condition = "status <> -1 and fk_question in ($qids)";
		return $db->select($table,$condition);
	}
	
	public function listQuestionAnswerByQids($qids){
		$db = self::InitDB("db_exam","query");
		$table = "t_question_answer";
		$condition = "fk_question in ($qids)";
		return $db->select($table,$condition);
	}
	
	
	

}
