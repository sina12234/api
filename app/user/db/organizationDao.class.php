<?php

class user_db_organizationDao
{
    const dbName = 'db_user';

    const TABLE = 't_organization';

    const ExpiredTime = 7200;

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function listsByOrgIdArr($idArr, $page = 1, $length = -1)
    {
        if (count($idArr) < 1) return false;

        $db = self::InitDB(self::dbName, 'query');
        $condition = 'status=1 AND pk_org IN ('.implode(',', $idArr).')';
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
	public static function getOrgInfoByUserId($userId){
		$db = self::InitDB(self::dbName, 'query');
		$userId = is_array($userId)?implode(',',$userId):intval($userId);
        $condition = "fk_user_owner IN ($userId)";
        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
	}

    public static function getOrgCount($params){
        $db = self::InitDB(self::dbName, 'query');
        $con = "status IN (1,2)";
        if(!empty($params["minDate"]) && !empty($params["maxDate"])){
            $con .= " AND create_time BETWEEN '".$params['minDate']."' AND '".$params['maxDate']."'";
        }
        $item = "COUNT(*) orgCount";
        $res = $db->selectOne(self::TABLE,$con,$item);
        return $res;
    }
    public static function getOrgListByMgr($params){
        $db = self::InitDB(self::dbName,'query');
        $table="t_organization";
        $condition = "t_organization.status IN (1,2)";
        if(!empty($params["minDate"])&&!empty($params["maxDate"])){
            $condition .= " AND t_organization.create_time BETWEEN '".$params['minDate']."' AND '".$params['maxDate']."'";
        }
        if (!empty($params["page"]) && !empty($params["length"])) {
            $db->setPage($params["page"]);
            $db->setLimit($params["length"]);
            $db->setCount(true);
        }
        $items="t_organization.pk_org,t_organization.fk_user_owner,";
        $items.="t_organization_profile.subname,t_organization_profile.province,t_organization_profile.city,t_organization_profile.county";
        $left=new stdclass;
        $left->t_organization_profile="t_organization_profile.fk_org=t_organization.pk_org";
        $res = $db->select($table,$condition,$items,'','',$left);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
