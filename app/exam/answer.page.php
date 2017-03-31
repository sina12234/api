<?php
class exam_answer{
	public function pageAddAnswer(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";

		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params["question_id"])){
			$ret->result->code = -3;
			$ret->result->msg = "the class name is empty!";
			return $ret;	
			exit();
		}
	 
		$data = $params;
		$exam_api = new exam_api;
		$retAns = $exam_api->addAnswer($data);
		if($retAns!==false){
			//	unset($ret->result);
			$ret->data=array("aid"=>(int)$retAns);
			$ret->result->code = 0;
			$ret->result->msg= "success";
			return $ret;
		}else{
			$ret->result->code = -2;
		}
		$ret->result->code = -2;
		$ret->result->msg= "failed";
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
		if(empty($params["desc"])){
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

    public function pageAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

		if (empty($params['questionId'])) return api_func::setMsg(1000);

        $data = [
            'fk_question' => $params['questionId'],
            'desc'        => isset($params['desc']) ? $params['desc'] : '',
            'desc_img'    => isset($params['imgDesc']) ? $params['imgDesc'] : '',
            'correct'     => isset($params['correct']) ? $params['correct'] : 0,
        ];

		if (exam_db_questionAnswerDao::add($data)) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }
}
