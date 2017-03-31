<?php
class user_orgMember{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pageCheckMemberByUidAndSetId($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->userId) && !is_numeric($params->userId)){
			return $this->setResult('',-1,'params is error');	
		}
		if(empty($params->setId) && !is_numeric($params->setId)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberDao::checkMemberByUidAndSetId($params->userId,$params->setId);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageAdd($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['fk_user']) || empty($params['fk_member_set'])){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberDao::add($params);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageUpdate($inPath){
		$memberId = !empty($inPath[3])?$inPath[3]:0;
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($memberId) && !is_numeric($memberId)){
			return $this->setResult('',-1,'memberId is error');	
		}
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		$res = user_db_orgMemberDao::update($memberId,$params);
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetMemberListByMemberSetId($inPath){
		$setId = isset($inPath[3])?$inPath[3]:0;
		if(empty($setId) && !is_numeric($setId)){
			return $this->setResult('',-1,'params is error');	
		}
		$params = SJson::decode(utility_net::getPostData());
		$page = isset($params->page)?$params->page:1;
		$length = isset($params->length)?$params->length:0;
		$memberStatus = isset($params->member_status)?$params->member_status:'';
		$res = user_db_orgMemberDao::getMemberListByMemberSetId($page,$length,$setId,$memberStatus);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	//用户购买的会员列表
	public function pageGetUserMember($inPath){
		$params = SJson::decode(utility_net::getPostData(), true);
		
		$page   = !empty($inPath[3]) ? (int)$inPath[3] : 1;
		$length = !empty($inPath[4]) ? (int)$inPath[4] : -1;
		$setId  = !empty($params['setId']) ? (int)$params['setId'] : 0;
		$userId = !empty($params['userId']) ? (int)$params['userId'] : 0;
		$orgId  = !empty($params['orgId']) ? (int)$params['orgId'] : 0;
		$mobile = !empty($params['mobile']) ? $params['mobile'] : '';
		$memberStatus = isset($params['member_status']) ? (int)$params['member_status'] : '';
		$res = user_db_orgMemberDao::getMemberListByMemberSetId($page,$length,$setId,$memberStatus,$orgId,$mobile,$userId);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetValidMemberListByUid($inPath){
		$userId = isset($inPath[3])?$inPath[3]:0;
		if(empty($userId) && !is_numeric($userId)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberDao::getValidMemberListByUid($userId);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
    public function pageGetMemberByUidAndSetIdArr()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $uid    = isset($params['userId']) && (int)$params['userId'] ? (int)$params['userId'] : 0;
        if (!$uid || empty($params['setIdArr'])) return api_func::setMsg(1000);

        $res = user_db_orgMemberDao::getMemberByUidAndSetIdArr($uid, $params['setIdArr']);

        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    public function pageGetMemberBySetIdArr()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['setIdArr'])) return api_func::setMsg(1000);

        $res = user_db_orgMemberDao::getMemberBySetIdArr($params['setIdArr']);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }
	
	public function pageGetMgrMemberList($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$page = isset($params->page)?$params->page:1;
		$length = isset($params->length)?$params->length:0;
		$setId = isset($params->setId)?$params->setId:0;
		$orgId = isset($params->orgId)?$params->orgId:0;
		$mobile = isset($params->mobile)?$params->mobile:'';
		$res = user_db_orgMemberDao::getMemberListByMemberSetId($page,$length,$setId,'',$orgId,$mobile);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageDelByUidArr($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->setId) && !is_numeric($params->setId)){
			return $this->setResult('',-1,'setId is error');	
		}
		if(empty($params->uidArr)){
			return $this->setResult('',-1,'uidArr is empty');
		}
		$data['member_status'] = isset($params->member_status)?$params->member_status:-1;
		$res = user_db_orgMemberDao::delByUidArr($params->setId,$params->uidArr,$data);
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageCheckIsMemberByUidArrAndSetId($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->uidArr) && !is_numeric($params->uidArr)){
			return $this->setResult('',-1,'params is error');	
		}
		if(empty($params->setId) && !is_numeric($params->setId)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberDao::checkIsMemberByUidArrAndSetId($params->uidArr,$params->setId);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
}
