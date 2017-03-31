<?php

class user_db_organizationTagDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_tag';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    public static function add( $insertData ){
        $db = self::InitDB(self::dbName);   
        return $db->insert(self::TABLE, $insertData);;
    }

    public static function update($orgId, $tagIdArr, $data){
        $db = self::InitDB(self::dbName);
		$tagIdStr = implode(',',$tagIdArr);
        $condition = "fk_org = $orgId AND fk_tag IN ($tagIdStr)";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	
	public static function del($orgId){
        $db = self::InitDB(self::dbName);
		$data = array('status'=>0);
        $condition = "fk_org = $orgId";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	
    public static function getOrgTagByOrgId($orgId){
        $db = self::InitDB(self::dbName);
        $condition = "fk_org = $orgId AND status = 1";
        return $db->select(self::TABLE, $condition);
    }
	
	public static function getAllOrgTagByOrgId($orgId){
        $db = self::InitDB(self::dbName);
        $condition = "fk_org = $orgId";
        return $db->select(self::TABLE, $condition);
    }

	public static function getOrgTagSort($orgId,$sort,$limit){
        $db = self::InitDB(self::dbName);
        $condition = "fk_org = $orgId";
		if(!empty($limit)){
			$db->setLimit($limit);
		}
		$orderBy = array("$sort"=>'desc');
        return $db->select('t_organization_tag_sort', $condition,'','',$orderBy);
    }

}
