<?php
class log_mgrLog{

	public function pageAddMgrLog($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$userId   = isset($params['fk_user']) && (int)($params['fk_user']) ? $params['fk_user'] : 0;
		$nodeUrl  = !empty($params['node_url']) ? $params['node_url'] : '';
		if(empty($userId) || empty($nodeUrl)){
			 return api_func::setMsg(1000);
		}
		$mgrLogDb = new log_mgrLogDb;
		$addRet = $mgrLogDb->addMgrLog($params);
		if(!empty($addRet)){
			return api_func::setData($addRet);
		}else{
			return api_func::setMsg(3002);
		}
	}
	
	public function pageUpdateMgrLog($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$logId   = isset($params['log_id']) && (int)($params['log_id']) ? $params['log_id'] : 0;
		$data  = !empty($params['data']) ? $params['data'] : '';
		if(empty($logId) || empty($data)){
			 return api_func::setMsg(1000);
		}
		$mgrLogDb = new log_mgrLogDb;
		$updateData = array('data'=>$data);
		$updateRet = $mgrLogDb->updateMgrLog($logId,$updateData);
		if($updateRet !== false){
			return api_func::setData($updateRet);
		}else{
			return api_func::setMsg(3002);
		}
	}
	
	public function pageGetMgrLog($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$userId   = isset($params['fk_user']) && (int)($params['fk_user']) ? $params['fk_user'] : 0;
		$nodeUrl  = !empty($params['node_url']) ? $params['node_url'] : '';
		if(empty($userId) || empty($nodeUrl)){
			 return api_func::setMsg(1000);
		}
		$mgrLogDb = new log_mgrLogDb;
		$ret = $mgrLogDb->getMgrLog($params['fk_user'],$params['node_url']);
		if(!empty($ret)){
			return api_func::setData($ret);
		}else{
			return api_func::setMsg(3002);
		}
	}
	
	public function pageGetMgrLogList($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$uid   = isset($params['fk_user']) && (int)($params['fk_user']) ? $params['fk_user'] : 0;
		$nodeUrl  = isset($params['node_url']) ? $params['node_url'] : '';
		$nodeDesc  = isset($params['node_desc']) ? $params['node_desc'] : '';
		$nodeTitle  = isset($params['node_title']) ? $params['node_title'] : '';
		$startTime  = isset($params['start_time']) ? $params['start_time'] : '';
		$endTime  = isset($params['end_time']) ? $params['end_time'] : '';
		$page  = isset($params['page']) ? $params['page'] : 1;
		$length  = isset($params['length']) ? $params['length'] : 20;
		$mgrLogDb = new log_mgrLogDb;
		$ret = $mgrLogDb->getMgrLogList($page,$length,$uid,$nodeDesc,$nodeTitle,$nodeUrl,$startTime,$endTime);
		if(!empty($ret)){
			return api_func::setData($ret);
		}else{
			return api_func::setMsg(3002);
		}
	}
  
}

