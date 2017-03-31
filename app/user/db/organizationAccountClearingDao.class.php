<?php

class user_db_organizationAccountClearingDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_account_clearing';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
	public static function rows($data, $page = 1, $length = 20)
    {
        if (empty($data)) return false;

        $db = self::InitDB(self::dbName, 'query');
        $condition = "fk_org = {$data['orgId']} and status = 1";
		
		if(!empty($data['time'])){
			$condition .= " and create_time <= {$data['time']} and end_time >= {$data['time']}";
		}

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));;
        }
        return $res;
    }
	
	public static function row($pKey,$orgId)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_clearing={$pKey} and fk_org={$orgId}";

        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	
	
	
	
}

