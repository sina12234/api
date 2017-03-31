<?php

class user_db_studentDao
{
    const dbName = 'db_course';

    const TABLE = 't_task';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    public static function lists($cond, $page=1, $length=-1, $item='*', $orderBy='', $groupBy='')
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

    public static function listsByUserIdArr($idArr, $page = 1, $length = -1)
    {
        if (count($idArr) < 1) return false;

        $db = self::InitDB(self::dbName, 'query');
        $condition = 'fk_user IN ('.implode(',', $idArr).')';
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

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "fk_user={$pKey}";

        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }


    public static function test(){
        $db = self::InitDB(self::dbName, 'query');
        $id = 726;
        $key = md5($id);
        $v = redis_api::get($key);
        if($v) {return $v;}
        $condition = "pk_task=$id";
        $res = $db->select(self::TABLE, $condition);
        redis_api::set($key,$res,3600*5);
        return $res;
    }
}
