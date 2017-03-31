<?php
class stat_dayuserstatorg{
	
	public $ret;

    public function setResult($data='', $code=1, $msg='success'){
         $this->ret['result'] = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data
         );

   		 return $this->ret;
	}
	public function pageDayUserStatOrgVvList($inPath){	
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";

		$stat_api = new stat_api;
		$params = SJson::decode(utility_net::getPostData());

		$seldata = array();
		//cond
		if(!empty($params->times)){
			$times =  $params->times;
		}else{
            return $this->setResult('',-2, 'params error!');
		}
		if(!empty($params->timee)){
			$timee =  $params->timee;
		}else{
            return $this->setResult('',-2, 'params error!');
		}
		//$timee="2016-03-16";
		//$times="2016-03-14";
		if(isset($params->orgUser)){
			$orgUser =  $params->orgUser;
		}
		$statList = $stat_api->dayUserStatOrgVvList($orgUser,$times,$timee,$seldata);
		if($statList === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
			//error_log("stat".var_export($statList,true)."\n",3, "/tmp/fanfan.log_");
		//return $statList;
		return $this->setResult($statList->data);
	}


	public function pageDayUserStatOrgTotalVvList($inPath){	
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";

		$stat_api = new stat_api;
		$params = SJson::decode(utility_net::getPostData());

		$seldata = array();
		//cond
		if(!empty($params->times)){
			$times =  $params->times;
		}else{
            return $this->setResult('',-2, 'params error!');
		}
		if(!empty($params->timee)){
			$timee =  $params->timee;
		}else{
            return $this->setResult('',-2, 'params error!');
		}
		if(isset($params->orgUser)){
			$orgUser =  $params->orgUser;
		}
		$statList = $stat_api->dayUserStatOrgTotalVvList($orgUser,$times,$timee,$seldata);
		if($statList === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $this->setResult($statList->data);
	}







}









