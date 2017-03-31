<?php

class user_db_teacherDao
{
    const dbName = 'db_user';

    const TABLE = 't_user_teacher_profile';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function listsByUserIdArr($idArr, $page = 1, $length = 20)
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

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function updateBanner($tid, $bannerImg)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$tid}";
        $item = ['banner_img'=> $bannerImg];

        $res = $db->update(self::TABLE, $condition, $item);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
