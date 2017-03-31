<?php

class user_db_orgAccountWithdrawDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_account_withdraw';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
    public static function getlist($orgId,$page,$length,$start,$end,$status){
        $db = self::InitDB(self::dbName,'query');
		if($page && $length){
			$db->setPage($page);
			$db->setLimit($length);
		}
		
		$left = new stdclass;
		$left->t_organization_account_card = "t_organization_account_card.pk_card = t_organization_account_withdraw.fk_org_account_card";
        $items = array('t_organization_account_card.bank','t_organization_account_card.card_no',
						't_organization_account_withdraw.withdraw','t_organization_account_withdraw.descript','t_organization_account_withdraw.status',
						't_organization_account_withdraw.create_time','t_organization_account_withdraw.pk_withdraw');
		$condition = "t_organization_account_withdraw.fk_org = $orgId";
		if(is_numeric($status)){
			$condition .= " AND t_organization_account_withdraw.status = $status";
		}elseif(!empty($status)){
			$condition .= " AND t_organization_account_withdraw.status IN ($status)";
		}
		if(!empty($start) && !empty($end)){
			$condition .= " AND t_organization_account_withdraw.create_time BETWEEN '$start' AND '$end'";
		}
		$orderBy = array('t_organization_account_withdraw.create_time'=>'asc');
        return $db->select(self::TABLE,$condition,$items,'',$orderBy,$left);
    }
	
	public static function update($withdrawId, $data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_withdraw = $withdrawId";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	
	public static function add($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        return $res;
    }
    public static function MgrList($cond = array(),$page = null,$length = null){
		$db = self::InitDB(self::dbName,"query");
		$condition = array();
		if(isset($cond['status']) && is_numeric($cond['status'])){
			$condition = array("t_organization_account_withdraw.status = {$cond['status']}");
		}elseif(isset($cond['status']) && !empty($cond['status'])){
			$condition = array("t_organization_account_withdraw.status IN ({$cond['status']})");
		}
		if(!empty($cond['time'])){
            $time  = date('Y-m-d', strtotime($cond['time']));
            $time1 = date('Y-m-d', strtotime($cond['time']) + 86400);
            $condition[] = "t_organization_account_withdraw.create_time >=\"$time\"";
            $condition[] = "t_organization_account_withdraw.create_time <=\"$time1\"";
		}
		if(!empty($cond['org_id'])){
			$condition['t_organization_account_withdraw.fk_org'] = $cond['org_id'];
		}
		$item    = array(
			"withdraw_id"=>"pk_withdraw",
			"org_id"=>"t_organization_account_withdraw.fk_org",
			"org_account_card"=>"fk_org_account_card",
			"user_create"=>"t_organization_account_withdraw.fk_user_create",
			"user_submit"=>"t_organization_account_withdraw.fk_user_submit",
			"withdraw_org",
			"withdraw",
			"t_organization_account_withdraw.descript",
			"t_organization_account_withdraw.status",
			"t_organization_account_withdraw.check_time",
			"t_organization_account_withdraw.create_time",
			"t_organization_account_withdraw.last_updated",
			"t_organization_profile.subname",
			"t_organization_account_card.card_no",
			"t_organization_account_card.bank",
		);
		$left=new stdclass;
		$left->t_organization_profile="t_organization_profile.fk_org=t_organization_account_withdraw.fk_org";
		$left->t_organization_account_card="t_organization_account_card.pk_card=t_organization_account_withdraw.fk_org_account_card";
		if($page && $length){
			$db->setPage($page);
			$db->setLimit($length);
		}	
		$res = $db->select(self::TABLE, $condition,$item,"","",$left);
		return $res;
    }
	

}
