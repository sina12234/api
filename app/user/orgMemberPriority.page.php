<?php
class user_orgMemberPriority{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	public function pageAdd($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['object_ids']) || empty($params['fk_member_set'])){
			return $this->setResult('',-1,'params is error');	
		}
		$objectIdArr = explode(',',$params['object_ids']);
		$data = array();
		$data['fk_member_set'] = $params['fk_member_set'];
		$data['type'] = isset($params['type'])?$params['type']:1;
		$data['strategy'] = isset($params['strategy'])?$params['strategy']:1;
		$data['status'] = isset($params['status'])?$params['status']:0;
		foreach($objectIdArr as $bo){
			$data['object_id'] = $bo;
			$data['create_time'] = date('Y-m-d H:i:s',time());
			$res[] = user_db_orgMemberPriorityDao::add($data);
		}
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageUpdate($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['object_ids']) || empty($params['fk_member_set'])){
			return $this->setResult('',-1,'params is error');	
		}
		$data['type'] = isset($params['type'])?$params['type']:1;
		$data['status'] = isset($params['status'])?$params['status']:0;
		$res = user_api::updateMemberPriority($params['fk_member_set'],$params['object_ids'],$data);
		if($res !==  false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageUpdateByObjectId($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['object_id']) || empty($params['data'])){
			return $this->setResult('',-1,'params is error');	
		}
		$type = isset($params['type'])?$params['type']:1;
		$setData = $params['data'];
		$res = user_api::updateMemberPriorityByObjectId($params['object_id'],$type,$setData);
		if($res !==  false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageDelByObjectId($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['object_id']) || empty($params['type'])){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_orgMemberPriorityDao::delByObjectId($params['object_id'],$params['type']);
		if($res !==  false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageGetMemberPriority($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->setId) && !is_numeric($params->setId)){
			return $this->setResult('',-1,'params is error');	
		}
		$page   = !empty($inPath[3]) ? (int)$inPath[3] : 1;
		$length = !empty($inPath[4]) ? (int)$inPath[4] : -1;
		$type = isset($params->type)?$params->type:1;
		$res = user_api::getMemberPriority($params->setId,$type,$page,$length);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	/*
	 *@desc   通过object_id查询会员课程
	 *@param  $object_id(int|array)
	 */
	public function pageGetMemberPriorityByObjectId($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->object_id)){
			return $this->setResult('',-1,'params is error');	
		}
		$type = isset($params->type)?$params->type:1;
		$res = user_db_orgMemberPriorityDao::getMemberPriorityByObjectId($params->object_id,$type);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	

	
	
}
