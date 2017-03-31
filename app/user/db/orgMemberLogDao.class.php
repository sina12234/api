<?php

class user_db_orgMemberLogDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_member_log';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	public static function add($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        if ($res === false) {
            SLog::fatal(
                'db error[%s],params[%s]',
                var_export($db->error(), 1),
                var_export($data, 1)
            );
        }
        return $res;
    }
	

}
