<?php

class user_db_orgAccountOrderContentDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_account_order_content';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
    public static function getOrgIncomeAllByTime($orgId,$start,$end){
        $db = self::InitDB(self::dbName,'query');
		$items = "sum(income_all) as income_all";
        $condition = "fk_org = $orgId AND create_time BETWEEN '$start' AND '$end'";
        return $db->selectOne(self::TABLE, $condition,$items);
    }
	
	public static function getOrgOrderCountByOrgId($orgId,$startTime,$endTime){
		$db = self::InitDB(self::dbName,'query');
		$condition = "fk_org = $orgId";
		if (!empty($startTime) && !empty($endTime)) {
            $condition .= " AND create_time BETWEEN '$startTime' AND '$endTime'";
        }
		$groupBy = 'order_id';
        $res = $db->select(self::TABLE, $condition,'',$groupBy);
        return $res;
	}
	
	public static function add($insertData, $updateData=[])
    {
        $db = self::InitDB(self::dbName);

        if (!empty($updateData)) {
            $res = $db->insert(self::TABLE, $insertData, false, false, $updateData);
        } else {
            $res = $db->insert(self::TABLE, $insertData);
        }

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	
	public static function rows($params,$page,$length)
	{
		$db = self::InitDB(self::dbName);
		
                $condition = "status>0";
                if(!empty($params['orgId'])){
                    $condition .= " and fk_org = {$params['orgId']}";
                }
		if(!empty($params['startTime']) && !empty($params['endTime'])){
			$condition .= " and withdraw_time BETWEEN '{$params['startTime']}' AND '{$params['endTime']}'";
		}
		
		if($params['status'] == 3){
			$condition .= " and status=3";
		}
		
		if($params['status'] < 3){
			$condition .= " and (status=1 or status=2)";
		}

        if(!empty($params['objType'])){
            $condition .= " and type={$params['objType']}";
        }
		if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        
        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
	}

}
