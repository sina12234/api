<?php
class message_score {
	
	public function setResult($data='', $code=0,$msg='success'){
		
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pageGetTeacherScoreByTidArr($inPath){

        $tid_arr = SJson::decode(utility_net::getPostData(),true);
        if( empty($tid_arr)){
        	return $this->setResult('',-1, 'params error!');
		}
		$tid_str = implode(',',$tid_arr);
		$message_db = new message_db();
		$ret = $message_db->getTeacherScoreByTidArr($tid_str);
		if(!$ret){
        	return $this->setResult('', -2, 'get data failed');
		}else{
        	return $this->setResult($ret);	
		}

	}

	
	public function pageGetPlanScoreByPidArr($inPath){

        $pid_arr = SJson::decode(utility_net::getPostData(),true);
        if( empty($pid_arr) && !is_array($pid_arr)){
        	return $this->setResult('',-1, 'params error!');
		}
		$message_db = new message_db();
		$ret = $message_db->getPlanScoreByPidArr($pid_arr);
		if(!empty($ret) && !empty($ret->items)){
        	return $this->setResult($ret->items[0]);	
		}else{
        	return $this->setResult('', -2, 'get data failed');
		}

	}

















}
