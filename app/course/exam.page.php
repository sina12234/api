<?php
class course_exam{
	public function pageAdd($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The planid is not found!";
		$params = SJson::decode(utility_net::getPostData());
		/*{{{   
		//测试数据
		$params = new stdclass;
		$params->plan_id = "121";
		$params->question_id = "3";
		$params->type = "1";
		$params->q_desc = "哈哈";
		$params->q_desc_img = "哈哈_url";
		$params->a = "121";
		$params->b = "12w";
		$params->c = "123";
		$params->d = "124";
		$params->e = "125";
		$params->answer = "125";
		$params->status = "1";
		}}}
 		*/
		if(empty($params->q_desc) && empty($params->q_desc_img)){
			$ret->result->code = -3;
			$ret->result->msg = "the question desc is empty!";
			return $ret;		
		}
		if(empty($params->plan_id)){
			return $ret;		
		}else{
			$course_api = new course_api;
			$course_api->addcourseplanexam($params);
		}
		//define("DEBUG",true);
		if($course_api === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagedel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The examids not found!";
		$params = SJson::decode(utility_net::getPostData());
		if (empty($params->ids)) {
			return $ret;
		}
		$examidstmp = $params->ids;
		$examids = implode(",",$examidstmp);
		$course_api = new course_api;
		$retExam = $course_api->delCoursePlanExam($examids);
		if($retExam === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail del";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The planid is not found!";
		$params = SJson::decode(utility_net::getPostData());
//{{{  测试数据
/*
		$params = new stdclass;
		$params->plan_id = 121;
*/
	//	define("DEBUG",true);
//}}}  测试数据
		if (empty($params->plan_id)) {
			return $ret;
		}
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 200;}else{$length = $inPath[4];}
		$data["plan_id"] = $params->plan_id;
		$course_api = new course_api;
		$item  = '';
		$orderby = array();
		if(isset($params->orderby->order_no)){
			$orderby["order_no"] = $params->orderby->order_no;
		}
		$examlist = $course_api->coursePlanExamList($data,$page,$length,$item,$orderby);
		if($examlist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the class is not found!";
			return $ret;
		}
		return $examlist;
	}
	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The examid is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$examid = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		$course_api = new course_api;
		$retexam = $course_api->updateCoursePlanExam($examid,$params);
		if($retexam === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageGetPlanExamsByPlan($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		if(empty($params->plan_id) || empty($params->user_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		if($plan["user_id"]!=$params->user_id && $plan["user_plan_id"]!=$params->user_id){
			$ret->result->msg = "用户不是这个课程的老师";
			return $ret;
		}
		$data = $course_db->getPlanExamsByPlan($params->plan_id);
		if(empty($data->items)){
			$ret->result->msg = "没有内容";
			return $ret;
		}
		$ret->data = $data->items;
		$ret->result->code = 0;
		return $ret;
	}
	public function pageUpdatePlanExamStatus($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		if(empty($params->plan_exam_id) || empty($params->plan_id) || empty($params->user_id) || empty($params->status)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		if($plan["user_id"]!=$params->user_id && $plan["user_plan_id"]!=$params->user_id){
			$ret->result->msg = "用户不是这个课程的老师";
			return $ret;
		}
		$course_db->updatePlanExamStatus($params->plan_exam_id, $params->plan_id, $params->status);
		$ret->result->code = 0;
		return $ret;
	}

	public function pageGetPlanQuestionCountByPidArr($inPath){

		$pid_arr = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->code = -1;
		$ret->msg = "";
		$ret->data = 0;
		if(!empty($pid_arr) && !is_array($pid_arr)){
			$ret->msg = 'params is error';
			return $ret;
		}
		$course_db = new course_db;
		$question_ret = $course_db->getPlanQuestionCountByPidArr($pid_arr);
		if(!empty($question_ret) && !empty($question_ret->items[0])){
			$ret->code = 0;
			$ret->msg = 'success';
			$ret->data = $question_ret->items[0]['count'];
		}else{
			$ret->code = -2;
			$ret->msg = 'get data failed';
		}
		return $ret;

	}
	public function pageGetPlanQuestionByPid($inPath){

		$pid = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->code = -1;
		$ret->msg = "";
		$ret->data = '';
		if(empty($pid) && !is_numeric($pid)){
			$ret->msg = 'params is error';
			return $ret;
		}
		$course_db = new course_db;
		$question_ret = $course_db->getExamByPid($pid);
		if(!empty($question_ret) && !empty($question_ret->items)){
			$ret->code = 0;
			$ret->msg = 'success';
			$ret->data = $question_ret->items;
		}else{
			$ret->code = -2;
			$ret->msg = 'get data failed';
		}
		return $ret;
	}

	
	public function pageAddPhrase()
	{
		$time = date('Y-m-d H:i:s');
		//$data = SJson::decode(utility_net::getPostData(),true);
		$data = [
			'type'   => 2,
			'answer' => '{"A":"对","B":"错"}',
			'create_time' => $time,
			'last_updated' => $time
		];
		
		$course_db = new course_db;
		$ret = $course_db->addPhrase($data);
	}
	
	public function pageGetPhrase(){
		$ret = new stdclass;
		$ret->code = -1;
		$ret->msg = "";
		$ret->data = '';
		$params = SJson::decode(utility_net::getPostData(),true);

		$course_db = new course_db;
		$res = $course_db->getPhrase($params);
	
		if(!empty($res)){
			$ret->code = 0;
			$ret->msg = 'success';
			$ret->data = $res;
		}else{
			$ret->code = -2;
			$ret->msg = 'get data failed';
		}
		return $ret;
	}
	
	public function pageAddPlanPhrase(){
		$ret = new stdclass;
		$ret->code = -1;
		$ret->msg = "";
		$ret->data = '';
		$params = SJson::decode(utility_net::getPostData(),true);
		$time = date('Y-m-d H:i:s');
		$data = [
			'fk_plan'      => $params['planId'],
			'fk_phrase'    => $params['phraseId'],
			'answer_right' => $params['answerRight'],
			'create_time'  => $time,
			'last_updated' => $time
		];
		
		$course_db = new course_db;
		$res = $course_db->addPlanPhrase($data);
		return $res;
	}
}
