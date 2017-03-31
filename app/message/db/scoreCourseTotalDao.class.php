<?php

class message_db_scoreCourseTotalDao
{
    const dbName = 'db_message';
    const TABLE = 't_score_course_total';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function listByCourseIdArr($courseIdArr, $page = 1, $length = -1)
    {
        if (count($courseIdArr) < 1) return false;

        $courseStr = implode(',', $courseIdArr);
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_course IN ({$courseStr})";
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}

