<?php

class utility_db_schoolDao
{
    const dbName = 'db_utility';

    const TABLE = 't_region_school';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function lists($cond, $page=1, $length=20, $item='*', $orderBy='', $groupBy='')
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

    public static function listsBySchoolIdArr($idArr, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = '';

        if (count($idArr)>0) {
            $condition = 'pk_school IN ('.implode(',', $idArr).')';
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
