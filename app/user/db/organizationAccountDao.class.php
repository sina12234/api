<?php

class user_db_organizationAccountDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_account';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
    public static function getOneByOrgId($orgId){
        $db = self::InitDB(self::dbName,'query');
        $condition = "fk_org = $orgId";
        return $db->selectOne(self::TABLE, $condition);
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

	public static function update($oid, $data){
        $db = self::InitDB(self::dbName);
        $condition = "fk_org = $oid";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }





}

