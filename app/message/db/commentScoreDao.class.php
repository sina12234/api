<?php

class message_db_commentScoreDao
{
    const  dbName = 'db_message';
    const TABLE = 't_comment_score';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }


    public static function CheckIsAddScore($courseId, $uid, $planId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$uid} and fk_course={$courseId} and fk_plan={$planId} and status=1 ";

        return $db->selectOne(self::TABLE, $condition);
    }


    public static function delComment($userId, $planId, $courseId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$userId} AND fk_plan={$planId} AND fk_course={$courseId}";
		$item = array('status'=>0);
        $res = $db->update(self::TABLE, $condition ,$item);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    //获取教师评分详情
    public static  function lists($params){
        $db = self::InitDB(self::dbName);
        if ($params['page'] && $params['length']) {
            $db->setPage($params['page']);
            $db->setLimit($params['length']);
            $db->setCount(true);
        }
		$condition = "teacher_id={$params['teacherId']}  and status=1 order by score {$params['sort']}";
        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
		return $res;
    }

    //课程打分列表
    public static function listByCourseId($cid, $teacherId=0, $page=1, $length=-1)
    {
        $db = self::InitDB(self::dbName, 'query');

        $page = isset($page) && (int)$page ? (int)$page : 1;
        $length = isset($length) && (int)$length ? (int)$length : 50;

        $condition = "fk_course={$cid} and status=1";
        $teacherId && $condition .= " AND teacher_id={$teacherId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $item = array(
            "pk_comment",
            "score",
            "fk_user",
            "teacher_id",
            "fk_course",
            "fk_plan",
            "comment",
            "last_updated"
        );
        $res = $db->select(self::TABLE, $condition,$item);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	 public static function checkIsCommentByPlanId($planIdStr, $uid)
    {
        $db = self::InitDB(self::dbName);
        $condition = " status=1 and fk_user={$uid} and fk_plan IN ({$planIdStr})";

        return $db->select(self::TABLE, $condition);
    }

    /*
     * 评分列表
     */
    public static function commentList($cid, $teacherId=0, $page=1, $length=-1,$time,$score)
    {
        $db = self::InitDB(self::dbName, 'query');

        $page = isset($page) && (int)$page ? (int)$page : 1;
        $length = isset($length) && (int)$length ? (int)$length : 20;
        $condition = "fk_course={$cid} and status=1 ";
        if($time){
            $condition .= "and UNIX_TIMESTAMP(FROM_UNIXTIME(UNIX_TIMESTAMP(create_time),'%Y-%m-%d %H:%i:%S'))>= {$time}";
        }
        if($score){
            $condition .= " and score={$score}";
        }

        $teacherId && $condition .= " AND teacher_id={$teacherId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $item = array(
            "pk_comment",
            "score",
            "fk_user",
            "teacher_id",
            "fk_course",
            "fk_plan",
            "comment",
            "create_time"
        );
        $res = $db->select(self::TABLE, $condition,$item);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
