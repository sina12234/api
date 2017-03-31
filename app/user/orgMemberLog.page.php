<?php
class user_orgMemberLog{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	public function pageAdd($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['fk_member']) && !is_numeric($params['fk_member'])){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberLogDao::add($params);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	

	
	
}
