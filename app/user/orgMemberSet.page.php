<?php
class user_orgMemberSet{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	
	public function pageListByOrgId($inPath){	
		$oid = isset($inPath[3])?$inPath[3]:0;
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$params = SJson::decode(utility_net::getPostData());
		$status = isset($params->status)?$params->status:'';
		$res = user_api::getMemberSetListByOrgId($oid,$status);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageUpdate($inPath){
		$setId = !empty($inPath[3])?$inPath[3]:0;
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($setId) && !is_numeric($setId)){
			return $this->setResult('',-1,'setId is error');	
		}
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		$res = user_db_orgMemberSetDao::update($setId,$params);
		if($res !== false){
			if(isset($params['status'])){
				if($params['status'] == -1){
					user_db_orgMemberPriorityDao::del($setId);
					$mdata['member_status'] = $params['status'];
				}else{
					$mdata['status'] = $params['status'];
					user_db_orgMemberPriorityDao::update($setId,$mdata,'');
				}
				user_db_orgMemberDao::updateBySetId($setId,$mdata);
			}
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageAdd($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['title']) || empty($params['fk_org'])){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberSetDao::add($params);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
    public function pageGetOrgMemberSetInfo($inPath)
    {
        $setId = isset($inPath[3]) && (int)$inPath[3] ? (int)$inPath[3] : 0;
        if (!$setId) return api_func::setMsg(1000);
		
		$params = SJson::decode(utility_net::getPostData(),true);
		$orgId  = !empty($params['orgId']) ? (int)$params['orgId'] : 0;
        $res = user_db_orgMemberSetDao::getMemberSet($setId, $orgId);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }
	
	public function pageGetOrgMemberSets($inPath)
    {
        $params = SJson::decode(utility_net::getPostData(),true);

        if(empty($params['setId'])){
                return api_func::setMsg(1000);
        }
        $setId = $params['setId'];
	if(!isset($params['status'])){	
            $res = user_db_orgMemberSetDao::getMemberSets($setId);
        } else {       
            $status = (int) $params['status'];
            $condition = "pk_member_set IN ({$setId}) AND status={$status}";
            $res = user_db_orgMemberSetDao::getMemberSetList($condition);
        }
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }
	
	public function pageGetMgrMemberSetList($inPath){	
		$oid = isset($inPath[3])?$inPath[3]:0;
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberSetDao::getListByOrgId($oid);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

}
