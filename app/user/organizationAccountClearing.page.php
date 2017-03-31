<?php
class user_organizationAccountClearing{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	
	public function pageGetClearByOrgId($inPath)
	{
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 20;}else{$length = $inPath[4];}

		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params)){
			return $this->setResult('',-1,'params is error');
		}
		
		if(!empty($params['orgId'])){
			$data['orgId'] = $params['orgId'];
		}
		if(!empty($params['time'])){
			$data['time'] = $params['time'];
		}
		
		$res = user_db_organizationAccountClearingDao::rows($data,$page,$length);
		
		if(!empty($res->items)){
			redis_api::useConfig('t_organization_account_clearing');
			
			foreach($res->items as &$val){
				$key = md5('clearing_'.$val['fk_org'].'_'.$val['create_time'].'_'.$val['end_time']);
				$val['isNew']= redis_api::get($key);
				
			}
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetClearByClaerId($inPath)
	{
		if(empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $this->setResult('',-1,'params is error');
		}
		if(empty($inPath[4]) || !is_numeric($inPath[4])) {
			return $this->setResult('',-1,'params is error');
		}

		$res = user_db_organizationAccountClearingDao::row($inPath[3],$inPath[4]);
		if(!empty($res)){
			
			redis_api::useConfig('t_organization_account_clearing');
			$key = md5('clearing_'.$res['fk_org'].'_'.$res['create_time'].'_'.$res['end_time']);
			redis_api::hDelAll($key);
			
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
}
?>
