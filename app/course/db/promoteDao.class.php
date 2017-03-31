<?php

class course_db_promoteDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_promote';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getCoursePromote($courseId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_course={$courseId} and status=1";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function updateOrderCountAndIncome($courseId, $inCome)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_course={$courseId}";
        $data      = [
            "order_count=order_count+1",
            //"enroll_count=enroll_count+1",
            "income=income+{$inCome}"
        ];

        $res = $db->update(self::TABLE, $condition, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
    
    ##########################################################################
    
    public static function getPromoteCourseList($page,$length,$data=array())
    {
        $db = self::InitDB('db_course','query');
        $table = array('t_course_promote');
        $condition[] = "t_course_promote.status <> -1";
        if (!empty($data->uid)) $condition[] = "t_course.fk_user={$data->uid}";
        if (!empty($data->search)) $condition[] = "t_course.title LIKE '%{$data->search}%'";
        $order = (!empty($data->sort)) ? $data->sort : 't_course_promote.last_updated desc';
        $item = array(
            't_course_promote.fk_course as course_id','t_course_promote.price_promote','t_course_promote.org_count','t_course_promote.enroll_count','t_course_promote.order_count','t_course_promote.income','t_course_promote.ver','t_course_promote.create_time','t_course_promote.last_updated','t_course_promote.status_code','t_course_promote.status',
            't_course.title','t_course.type as course_type','t_course.price_market','t_course.price','(t_course.max_user-t_course.user_total) as remain_user','t_course.thumb_small'
         );
        $left  = new stdclass();
        $left->t_course  = "t_course.pk_course=t_course_promote.fk_course";
        $db->setPage($page);
        $db->setLimit($length);
        return $db->select($table,$condition,$item, "", $order, $left);
    }
    
    public static function addPromoteCourse($pcourse)
    {
        $db = self::InitDB("db_course");

        return $db->insert("t_course_promote", $pcourse);
    }

    public static function getPromoteCourseById($courseId)
    {
        $db = self::InitDB('db_course','query');
        $table = array('t_course_promote');
        $condition = "fk_course=$courseId";
        $order = 't_course_promote.last_updated desc';
        $item = array(
            "t_course_promote.fk_course","t_course_promote.price_promote","t_course_promote.org_count","t_course_promote.order_count","t_course_promote.income","t_course_promote.ver","t_course_promote.create_time","t_course_promote.last_updated","t_course_promote.status","t_course_promote.status_code","t_course.title","t_course.fk_user","t_course.max_user","t_course.user_total","(t_course.max_user-t_course.user_total) as remain_user"
         );
        $left  = new stdclass();
        $left->t_course  = "t_course.pk_course=t_course_promote.fk_course";
        return $db->selectOne($table,$condition,$item, "", $order, $left);
    }

    public static function updatePromoteCourse($courseId,$pcourse)
    {
        $db = self::InitDB("db_course");
        $condition = "fk_course = $courseId ";
        return $db->update("t_course_promote", $condition, $pcourse);
    }    

    public static function getCoursePromoteCount($params)
    {
        $db    = self::InitDB("db_course", "query");
        $condition = "";
        if(!empty($params["fk_user"])){
            $condition .="fk_user=".$params["fk_user"]." ";
        }
        if(!empty($params["is_promote"])){
            $condition .=" AND is_promote=".$params["is_promote"]." ";
        }
        $item                  = array("count(*) as ct");
        $ret                   = $db->selectOne("t_course", $condition, $item);
        $v                     = 0;
        if (!empty($ret) && !empty($ret['ct'])){
            $v = $ret['ct'];
        }

        return $v;
    }
}

