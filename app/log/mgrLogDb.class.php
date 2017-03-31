<?php

/**
 * @author lijingjuan
 */
class log_mgrLogDb{
	const dbName = 'db_mgr';
    const TABLE  = 't_mgr_log';
	public static function InitDB($dbName=self::dbName,$dbType="main") {
		redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
	}
	public function addMgrLog($data){
		$db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE, $data);
		return $res;
	}
	
	public function updateMgrLog($logId, $data){
		$db = self::InitDB(self::dbName);
        $res = $db->update(self::TABLE,array("pk_log" =>$logId), $data);
		return $res;
	}
	
	public function getMgrLog($fk_user,$node_url){
		$db = self::InitDB(self::dbName);
		$condition = array('fk_user'=>$fk_user,'node_url'=>"$node_url"," data ='' ");
		$orderBy = array('create_time'=>'desc');
        $res = $db->selectOne(self::TABLE, $condition,'','',$orderBy);
		return $res;
	}
	
	public function getMgrLogList($page,$length,$uid,$nodeDesc,$nodeTitle,$nodeUrl,$startTime,$endTime){
		$db = self::InitDB(self::dbName);
		if($page && $length){
			$db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
		}
		$left=array('t_mgr_user'=>'t_mgr_user.pk_mgr_user=t_mgr_log.fk_user');
		$condition = array();
		if(!empty($uid)){
			$condition['t_mgr_log.fk_user'] = $uid;
		}
		if(!empty($nodeDesc)){
			$condition['t_mgr_log.node_desc'] = "$nodeDesc";
		}
		if(!empty($nodeTitle)){
			$condition['t_mgr_log.node_title'] = "$nodeTitle";
		}
		if(!empty($nodeUrl)){
			$condition['t_mgr_log.node_url'] = "$nodeUrl";
		}
		if(!empty($startTime) && !empty($endTime)){
			array_push($condition,"t_mgr_log.create_time between '$startTime' and '$endTime'");
		}
		$items = array('t_mgr_log.pk_log','t_mgr_log.fk_user','t_mgr_log.node_desc','t_mgr_log.node_title',
					't_mgr_log.node_url','t_mgr_log.data','t_mgr_log.create_time','t_mgr_user.name');
		$orderBy = array('t_mgr_log.create_time'=>'desc');
        $res = $db->select(self::TABLE, $condition,$items,'',$orderBy,$left);
		return $res;
	}
}
?>
