<?php

class user_organizationUser{
	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
    public function pageGetAdminList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $t = api_func::listsParamCheck($params);

        return user_db_organizationUserDao::lists($t['condition'], $t['page'], $t['length'], $t['orderBy'], $t['item'], $t['groupBy']);
    }

	public function pageSearchOrgTeacherByRealName($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->oid) || empty($params->real_name)){
			return $this->setResult('',-1,'params is empty');
		}
		$ret = user_db::searchOrgTeacherByRealName($params->oid,$params->real_name);
		if(!empty($ret->items)){
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pagelistUserProfileByOids($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->oids)){
			return $this->setResult('',-1,'params is empty');
		}
		$oids = array();
		foreach($params->oids as $k=>$v){
			$oids[] = $v;
		}
		$ret = user_api::listUserProfileByOids($oids);
		return $ret;
		if(!empty($ret->items)){
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

}
