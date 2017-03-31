<?php

class user_db_orgMemberSetDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_member_set';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
    public static function getListByOrgId($orgId,$status=''){
        $db = self::InitDB(self::dbName,'query');
		$condition = '';
		if(is_numeric($status)){
			$condition .= "status = $status AND ";
		}else{
			$condition .= 'status <> -1 AND ';
		}
		$condition .= "fk_org = $orgId";
		$orderBy = array('create_time'=>'asc');
        return $db->select(self::TABLE,$condition,'','',$orderBy);
    }

	public static function update($setId, $data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_member_set = $setId";
		if(!empty($data['price_30']) || !empty($data['price_90']) || !empty($data['price_180']) || !empty($data['price_360'])){
			if(empty($data['price_30'])){
				$data['price_30'] = 0;
			}
			if(empty($data['price_90'])){
				$data['price_90'] = 0;
			}
			if(empty($data['price_180'])){
				$data['price_180'] = 0;
			}
			if(empty($data['price_360'])){
				$data['price_360'] = 0;
			}
		}
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	public static function add($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        return $res;
    }

    public static function getMemberSet($setId, $orgId='')
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_member_set={$setId} AND status <> -1";
		if(!empty($orgId)){
			$condition .= " and fk_org={$orgId}";
		}

        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	
	public static function getMemberSets($setIds)
	{
		$db = self::InitDB(self::dbName, 'query');
		$condition = "pk_member_set IN ({$setIds}) AND status <> -1";

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
	}

    public static function getListByOrgIds($orgIds,$status=''){
        $db = self::InitDB(self::dbName,'query');
        $condition = '';
        if(is_numeric($status)){
            $condition .= "status = $status AND ";
        }else{
            $condition .= 'status <> -1 AND ';
        }
        $condition .= "fk_org IN ({$orgIds})";
        $orderBy = array('create_time'=>'asc');
        return $db->select(self::TABLE,$condition,'','',$orderBy);
    }   
    
	public static function getMemberSetList($condition)
	{
            $db = self::InitDB(self::dbName, 'query');
            
            $res = $db->select(self::TABLE, $condition);

            return $res;
	}
}
