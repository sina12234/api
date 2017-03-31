<?php
class user_organizationAccount{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pageGetOneByOrgId($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_organizationAccountDao::getOneByOrgId($oid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageUpdate($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:0;
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'withdrawId is error');	
		}
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		$withdraw = $params["withdraw"];
		$balance = $params["balance"];
		$datas = array();
		$datas[] = "withdraw = withdraw-$withdraw";
		$datas[] = "balance = balance-$balance";
		$res = user_db_organizationAccountDao::update($oid,$datas);
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	
	
}
