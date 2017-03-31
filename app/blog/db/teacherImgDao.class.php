<?php

class blog_db_teacherImgDao
{
    const dbName = 'db_blog';
    const TABLE = 't_teacher_img';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function listsByUserId($uid, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user={$uid}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, '', '', 'create_time desc');

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function del($pKey)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "pk_teacher_img={$pKey}";

        $res = $db->delete(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "pk_teacher_img={$pKey}";

        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateImgName($tid, $name)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_teacher_img={$tid}";
        $item = ['image_name'=> $name];

        $res = $db->update(self::TABLE, $condition, $item);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}

