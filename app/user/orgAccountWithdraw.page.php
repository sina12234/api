<?php
class user_orgAccountWithdraw{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pageList($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->oid) && !is_numeric($params->oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$page = isset($params->page)?$params->page:1;
		$length = isset($params->length)?$params->length:0;
		$start = isset($params->start_time)?$params->start_time:'';
		$end = isset($params->end_time)?$params->end_time:'';
		$status = isset($params->status)?$params->status:'';
		$res = user_db_orgAccountWithdrawDao::getlist($params->oid,$page,$length,$start,$end,$status);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageAdd($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['fk_org']) && !is_numeric($params['fk_org'])){
			return $this->setResult('',-1,'fk_org is error');	
		}
		if(empty($params['fk_org_account_card']) && !is_numeric($params['fk_org_account_card'])){
			return $this->setResult('',-1,'cardId is error');	
		}
		if(empty($params['withdraw']) ){
			return $this->setResult('',-1,'withdraw is empty');	
		}
		$res = user_db_orgAccountWithdrawDao::add($params);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'add data is failed');
		}
	}
	
	public function pageUpdate($inPath){
		$withdrawId = !empty($inPath[3])?$inPath[3]:0;
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($withdrawId) && !is_numeric($withdrawId)){
			return $this->setResult('',-1,'withdrawId is error');	
		}
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		$res = user_db_orgAccountWithdrawDao::update($withdrawId,$params);
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageMgrList($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$status = isset($params->status)?$params->status:'';
		$time = isset($params->time)?$params->time:0;
		$org_id = isset($params->org_id)?$params->org_id:0;
		$condition = array();
		$condition["status"] = $status;
		$condition["time"] = $time;
		$condition["org_id"] = $org_id;
		$page = isset($params->page)?$params->page:1;
		$length = isset($params->length)?$params->length:0;
		$res = user_db_orgAccountWithdrawDao::Mgrlist($condition,$page,$length);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	
	
}
