<?php

class message_db_commentCourseDao
{
    const  dbName = 'db_message';
    const TABLE = 't_comment_course';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function lists($params)
    {
        $db = self::InitDB(self::dbName, 'query');

        $page = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length = isset($params['length']) && $params['length'] ? $params['length'] : -1;

        $item = ['pk_comment', 'fk_user', 'fk_course', 'fk_plan', 'comment', 'last_updated'];

        $condition = [];
        if (isset($params['courseIdArr']) && count($params['courseIdArr'])>0) {
            $courseIdStr = implode(',', $params['courseIdArr']);
            array_push($condition, "fk_course IN ({$courseIdStr})");
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, $item);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function listByCourseId($cid, $teacherId=0, $page=1, $length=-1)
    {
        $db = self::InitDB(self::dbName, 'query');

        $page = isset($page) && (int)$page ? (int)$page : 1;
        $length = isset($length) && (int)$length ? (int)$length : 50;

        $condition = "fk_course={$cid}";
        $teacherId && $condition .= " AND fk_user_teacher={$teacherId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $item = array(
            "pk_comment",
            "fk_user",
            "fk_user_teacher",
            "fk_course",
            "fk_plan",
            "comment",
        );
        $res = $db->select(self::TABLE, $condition,$item);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function checkIsComment($courseId, $uid, $planId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$uid} and fk_course={$courseId} and fk_plan={$planId}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function checkIsCommentByPlanId($planIdStr, $uid)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$uid} and fk_plan IN ({$planIdStr})";

        return $db->select(self::TABLE, $condition);
    }

    public static function delComment($userId, $planId, $courseId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$userId} AND fk_plan={$planId} AND fk_course={$courseId}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
