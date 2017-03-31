<?php

class user_db_userDao
{
    const dbName = 'db_user';

    const TABLE = 't_user';

    const ExpiredTime = 7200;

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function lists($cond, $page=1, $length=-1, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB(self::dbName, 'query');

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

    public static function listsByUserIdArr($idArr, $page = 1, $length = -1)
    {
        if (count($idArr) < 1) return false;

        $db = self::InitDB(self::dbName, 'query');
        $condition = 'pk_user IN ('.implode(',', $idArr).')';
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select(self::TABLE, $condition);
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName, 'query');

        $k = md5(self::TABLE.$pKey);
        $v = redis_api::get($k);
        if (!empty($v)) return $v;

        $condition = "pk_user={$pKey}";
        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        redis_api::set($k, $res, self::ExpiredTime);
        return $res;
    }

    public static function updateRealName($mobile, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = "mobile={$mobile}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateUserSource($uid, $source)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_user={$uid}";
        $data = ['source' => $source];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getUserInfoByMobile($mobile)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "mobile={$mobile}";

        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	public static function getUserInfoByUserId($userId)
    {
		$userId = is_array($userId)?implode(',',$userId):intval($userId);
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_user in ($userId)";
        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
    }

    public static function searchUserByKeyword($keyword)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "name LIKE '%{$keyword}%' or real_name LIKE '%{$keyword}%'";

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
