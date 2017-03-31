<?php
class user_orgAccountCard{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pagegetOrgCardByOrgId($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgAccountCardDao::getOrgCardByOrgId($oid);
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
		if(empty($params['card_no']) || empty($params['bank']) || empty($params['mobile'] || empty($params['user']))){
			return $this->setResult('',-1,'params is empty');	
		}
		
		$res = user_db_orgAccountCardDao::add($params);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'add data is failed');
		}
	}
	
	public function pageMgrList($inPath){
		$params = SJson::decode(utility_net::getPostData());

		$status = isset($params->status)?$params->status:null;
		$time = isset($params->time)?$params->time:0;
		$org_id = isset($params->org_id)?$params->org_id:0;

		$condition = array();
		$condition["status"] = $status;
		$condition["time"] = $time;
		$condition["org_id"] = $org_id;

		$page = isset($params->page)?$params->page:1;
		$length = isset($params->length)?$params->length:0;

		$res = user_db_orgAccountCardDao::Mgrlist($condition,$page,$length);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageUpdate($inPath){
		$cardId = !empty($inPath[3])?$inPath[3]:0;
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($cardId) && !is_numeric($cardId)){
			return $this->setResult('',-1,'withdrawId is error');	
		}
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		$res = user_db_orgAccountCardDao::update($cardId,$params);
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
}
