<?php

class message_db_scoreCourseDetailDao
{
    const dbName = 'db_message';
    const TABLE = 't_score_course_detail';

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

        $page    = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length  = isset($params['length']) && $params['length'] ? $params['length'] : -1;
        $orderBy = 't_score_course_detail.student_score '.$params["sort"].',t_score_course_detail.last_updated desc';
        $item    = ['t_score_course_detail.pk_detail', 't_score_course_detail.fk_user', 't_score_course_detail.fk_user_owner', 't_score_course_detail.fk_course', 't_score_course_detail.fk_plan', 't_score_course_detail.student_score','t_comment_course.comment'];

        $condition = [];
        if (isset($params['teacherId']) && $params['teacherId']) {
            $condition['t_score_course_detail.fk_user_teacher'] = $params['teacherId'];
        }

        if (isset($params['userOwner']) && $params['userOwner']) {
            $condition['t_score_course_detail.fk_user_owner'] = $params['userOwner'];
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $left=new stdclass;
        $left->t_comment_course="t_comment_course.fk_user_teacher = t_score_course_detail.fk_user_teacher and t_comment_course.fk_user= t_score_course_detail.fk_user";
        $res = $db->select(self::TABLE, $condition, $item, 't_score_course_detail.fk_user_teacher,t_score_course_detail.fk_user', $orderBy,$left);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function listByCourseId($cid, $teacherId=0, $page=1, $length=-1)
    {
        $db = self::InitDB(self::dbName, 'query');

        $page = isset($page) && (int)$page ? (int)$page : 1;
        $length = isset($length) && (int)$length ? (int)$length : 20;

        $condition = "t_score_course_detail.fk_course={$cid}";
        $teacherId && $condition .= " AND t_score_course_detail.fk_user_teacher={$teacherId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $item = array(
            "t_score_course_detail.pk_detail",
            "t_score_course_detail.fk_user",
            "t_score_course_detail.fk_user_owner",
            "t_score_course_detail.fk_user_teacher",
            "t_score_course_detail.fk_course",
            "t_score_course_detail.fk_plan",
            "t_score_course_detail.avg_score",
            "t_score_course_detail.student_score",
            "t_score_course_detail.desc_score",
            "t_score_course_detail.explain_score",
            "t_score_course_detail.service_score",
            "t_score_course_detail.last_updated",
            "t_comment_course.comment",

        );
		$left=new stdclass;
		$left->t_comment_course="t_comment_course.fk_plan = t_score_course_detail.fk_plan and t_comment_course.fk_user= t_score_course_detail.fk_user";
        $res = $db->select(self::TABLE, $condition,$item,"","t_score_course_detail.last_updated DESC",$left);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function delCommentScoreDetail($userId, $planId, $courseId)
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

