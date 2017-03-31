<?php
class exam_question{
	public function pageGenQuestionId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$uid = $inPath[3];
		$user_api = new user_api;
		//TODO判断否有机构权限
		$retOrg = $user_api->getOrgByUid($uid);
		if(!empty($retOrg->data)){
			$exam_api = new exam_api;
			$qid = $exam_api->genquestionId($uid);
			if(!empty($qid)){
				//	unset($ret->result);
				$ret->data=array("uid"=>(int)$uid,"qid"=>(int)$qid);
				$ret->result->code = 0;
				$ret->result->msg= "success";
			}else{
				$ret->result->code = -2;
			}
		}else{
			$ret->result->code = -2;
			$ret->result->msg= "failed";
		}
		return $ret;
	}
	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$qid = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData(),true);
/*		$params = array(
			"type"=>"radio",
			"subject_id"=>"1",
			"grade_id"=>"1001",
			"desc"=>"haha",
		);
*/	
		if((empty($params["desc"])) && (empty($params["desc_img"]))){
			$ret->result->code = -3;
			$ret->result->msg = "the class name is empty!";
			return $ret;			
			exit();
		}else{
			$datafrom = $params;
			//	$class = $params;
			$array_type = array(
				"radio"=>"1",  	//单选题
				"multiple"=>"2",//多选题
				"app"=>"3",		//应用题	
			);
			if(isset($params["type"])){
				$datafrom["type"] = $array_type[$params["type"]];
			}

		}
		$exam_api = new exam_api;
		$exam_api->updateQuestion($qid,$datafrom);
		//define("DEBUG",true);
		if($exam_api === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pageGetUserRightAnswerCountByPidArr($inPath){
        $pid_arr = SJson::decode(utility_net::getPostData(),true);
		$uid = $inPath[3];
        if( empty($pid_arr) && empty($uid)){
        	return $this->setResult('',-1, 'params error!');
		}
		$exam_db = new exam_db();
		$ret = $exam_db->getUserRightAnswerCountByPidArr($uid,$pid_arr);
		if(!empty($ret) && !empty($ret->items[0])){
        	return $this->setResult($ret->items[0]['count']);	
		}else{
        	return $this->setResult('', -2, 'get data failed');
		}
	}

	public function pageGetLogUserQuestionByPid($inPath){
        $pid = SJson::decode(utility_net::getPostData());
		$uid = !empty($inPath[3])?$inPath[3]:0;
        if( empty($pid) && empty($uid)){
        	return $this->setResult('',-1, 'params error!');
		}
		$exam_db = new exam_db;
		$ret = $exam_db->getLogUserQuestionByPid($uid,$pid);
		if(empty($ret) && empty($ret->items)){
        	return $this->setResult('', -2, 'get data failed');
		}else{
        	return $this->setResult($ret->items);
		}
	}

    public function pageAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

		if (empty($params['userId'])) return api_func::setMsg(1000);

        $data = [
            'fk_user_org' => isset($params['userOwner']) ? $params['userOwner'] : 0,
            'fk_user'     => $params['userId'],
            'type'        => isset($params['selectOption']) ? $params['selectOption'] : 0,
            'source'      => isset($params['source']) ? $params['source'] : 0,
            'fk_subject'  => isset($params['subjectId']) ? $params['subjectId'] : 0,
            'fk_grade'    => isset($params['gradeId']) ? $params['gradeId'] : 0,
            'descript'    => isset($params['descript']) ? $params['descript'] : '',
            'desc_img'    => isset($params['imgDesc']) ? $params['imgDesc'] : '',
            'analysis'    => isset($params['analysis']) ? $params['analysis'] : '',
            'result'      => isset($params['result']) ? $params['result'] : '',
            'mode'        => isset($params['difficulty']) ? $params['difficulty'] : 0,
            'status'      => isset($params['status']) ? $params['status'] : 0,
            'create_time' => date('Y-m-d H:i:s')
        ];

        $res = exam_questionDao::add($data);
		if ($res) return api_func::setData(['questionId'=>$res]);

        return api_func::setMsg(1);
    }




}
