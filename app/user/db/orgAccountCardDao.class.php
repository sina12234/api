<?php

class user_db_orgAccountCardDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_account_card';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }	
    public static function getOrgCardByOrgId($orgId){
        $db = self::InitDB(self::dbName,'query');
        $condition = "fk_org = $orgId AND status > -1";
		$orderBy = array('create_time'=>'desc');
        return $db->selectOne(self::TABLE,$condition,'','',$orderBy);
    }	
	public static function add($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        return $res;
    }
    public static function MgrList($cond = array(),$page = null,$length = null){
		$db = self::InitDB(self::dbName,"query");
		$condition = array();
		if(isset($cond['status'])){
			if($cond["status"]=="on"){
				$condition[] = "t_organization_account_card.status = 2";

			}elseif($cond["status"]=="off"){
				$condition[] = "t_organization_account_card.status >= -1";
				$condition[] = "t_organization_account_card.status <= 0";

			}elseif($cond["status"]=="all"){
				$condition[] = 't_organization_account_card.status > -2';
			}else{
				$condition['t_organization_account_card.status'] = $cond['status'];
			}
		}else{
			$condition = 't_organization_account_card.status > -2';
		}
		if(!empty($cond['time'])){
            $time  = date('Y-m-d', strtotime($cond['time']));
            $time1 = date('Y-m-d', strtotime($cond['time']) + 86400);
            $condition[] = "t_organization_account_card.create_time >=\"$time\"";
            $condition[] = "t_organization_account_card.create_time <=\"$time1\"";
		}
		if(!empty($cond['org_id'])){
			$condition['t_organization_account_card.fk_org'] = $cond['org_id'];
		}

		$item    = array(
			"card_id"=>"pk_card",
			"org_id"=>"t_organization_account_card.fk_org",
			"card_no"=>"card_no",
			"bank",
			"t_organization_account_card.type",
			"user",
			"card_mobile"=>"t_organization_account_card.mobile",
			"branch"=>"t_organization_account_card.branch",
			"t_organization_account_card.status",
			"t_organization_account_card.sort",
			"t_organization_account_card.region_level0",
			"t_organization_account_card.region_level1",
			"t_organization_account_card.region_level2",
			"t_organization_account_card.create_time",
			"t_organization_account_card.last_updated",
			"t_organization_profile.subname",
		);

		if($page && $length){
			$db->setPage($page);
			$db->setLimit($length);
		}
		$left=new stdclass;
		$left->t_organization_profile="t_organization_profile.fk_org=t_organization_account_card.fk_org";
		$res = $db->select(self::TABLE, $condition,$item,"","",$left);
		return $res;
    }

	public static function update($cardid, $data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_card = $cardid";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }

}
