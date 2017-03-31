<?php

class user_db_organizationUserDao
{
    const dbName = 'db_user';

    const TABLE = 't_organization_user';

    const ExpiredTime = 7200;

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function lists($cond, $page=1, $length=20, $orderBy='', $item='*', $groupBy='')
    {
        $db = self::InitDB();

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $cond, $item, $groupBy, $orderBy);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName, 'query');

        $k = md5(self::TABLE.$pKey);
        $v = redis_api::get($k);
        if (!empty($v)) return $v;

        $condition = "pk_org={$pKey} AND status=1";
        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        redis_api::set($k, $res, self::ExpiredTime);
        return $res;
    }

    public static function listsByOrgIdArr($idArr, $page = 1, $length = -1)
    {
        if (count($idArr) < 1) return false;

        $db = self::InitDB(self::dbName, 'query');
        $condition = 'status=1 AND fk_org IN ('.implode(',', $idArr).')';
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
	public static function getAdminByOrgId($orgId){
		$db = self::InitDB(self::dbName, 'query');
		$item = "distinct(fk_user) as fk_user";
        $condition = "status=1 AND fk_org IN ($orgId) and user_role =5";
        $res = $db->select(self::TABLE, $condition,$item);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return empty($res->items)?array():$res->items;
	}
}
