<?php
class exam_api{

	public function genquestionid($uid){
		$db = new exam_db;
		$maxid = $db->getmaxquestionidbyuid($uid);
		if($maxid===false)return false;
		$question = $db->getquestion($maxid);
		if($maxid===0 || !empty($question['descript'])|| !empty($question["desc_img"])){
			//新增一个
			$question=array();
			$question['fk_user_org']=$uid;
			$question['type']="0";
			$time = date("y-m-d h:i:s");
			$question['create_time']= $time;
			$question['last_updated']= $time;
			$retquestion = $db->addquestion($question);
			if($retquestion)return $retquestion;
		}
		return $maxid;
	}
	/*
	 *更新问题信息
	 */
	public function updateQuestion($qid,$datafrom){
		$keys = array(
			"fk_user_org"=>"uid",
			"type"=>"type",
			"fk_subject"=>"subject_id",
			"fk_grade"=>"grade_id",
			"descript"=>"desc",
			"desc_img"=>"desc_img",
			"result"=>"result",
			"mode"=>"mode",
		);
		$exam_db = new exam_db;
		$qid = (int)$qid;
		$data = array();
		foreach($keys as $datak=>$datav){
			if(isset($datafrom[$datav])){
				$data[$datak] = $datafrom[$datav];
			}
		}
		$time = date("Y-m-d H:i:s");
		$data["last_updated"]= $time;
		$ret = $exam_db->updateQuestion($qid,$data);	
		return $ret;
	}
	public function AddAnswer($data){
		$db = new exam_db;
		$question=array();
		$datain['question_id'] = $data["question_id"];
		$datain['correct'] = $data["correct"];
		if(isset($data["desc"])){
			$datain["desc"] = $data["desc"];
		}
		if(isset($data["desc_img"])){
			$datain["desc_img"] = $data["desc_img"];
		}
		$ret = $db->addAnswer($datain);
		//新增一个
		return $ret;
	}
	/*
	 * 获取一个用户的统计信息
	 *
	 */
	public function logUserQuestionForPlanList($data,$page,$length,$item,$orderby){
		$exam_db = new exam_db();
		$retlist = $exam_db->logUserQuestionForPlanList($data,$page,$length,$item,$orderby);
		$retdata = $retlist->items;
		if($retlist === false) return false;	
		if(empty($retdata)){$retdata = 0;}
		$ret = new stdClass;
		$ret->data = $retdata;
	//扩展需要
	/*	$ret->page = $retlist->page;
		$ret->size = $retlist->pageSize;
		$ret->total = $retlist->totalPage;
	*/
		return $ret;
	}

}
