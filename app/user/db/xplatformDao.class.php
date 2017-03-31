<?php

class user_db_xplatformDao
{
    const dbName = 'db_user';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getSchoolBySchoolId($school_id){
        $db = self::InitDB();

		$cond = array("school_id"=>$school_id);
		$item=array("school_id","school_name","school_areacode","subdomain","user_id"=>"fk_user");
        $res = $db->selectOne("t_user_parterner_school_xplatform", $cond, $item);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

}
