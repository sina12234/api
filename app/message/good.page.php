<?php
class message_good {
	
	public function setResult($data='', $code=0,$msg='success'){
		
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pageGetPlanGoodByPidArr($inPath){
		
        $pid_arr = SJson::decode(utility_net::getPostData(),true);
        if( empty($pid_arr) && !is_array($pid_arr)){
        	return $this->setResult('',-1, 'params error!');
		}
		$message_db = new message_db();
		$ret = $message_db->getPlanGoodByPidArr($pid_arr);
		if(empty($ret) && empty($ret->items)){
        	return $this->setResult('', -2, 'get data failed');
		}else{
        	return $this->setResult($ret->items);	
		}

	}

	

















}
