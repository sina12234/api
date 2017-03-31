<?php

class course_db_resellDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_resell';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getCourseResell($courseId, $resellOrgId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_course={$courseId} and fk_org_resell={$resellOrgId}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function updateOrderCountAndIncome($courseId, $orgResellId, $inCome)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "fk_course={$courseId} and fk_org_resell={$orgResellId}";
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

    public static function updateStatus($resellId,$status){
        $db = self::InitDB(self::dbName);
        $condition = "pk_course_resell={$resellId}";
        $data      = "status={$status}";
        $db->update(self::TABLE, $condition, $data);
    }

    public static function getPromoteStatusNotOnVarNot($orgResellId){
        $db = self::InitDB(self::dbName);
        $condition = "t_course_resell.fk_org_resell={$orgResellId}  AND t_course_promote.status_code<>1";
        $item = array(
            't_course_resell.pk_course_resell as resell_id','t_course_resell.status','t_course_promote.status_code as status_code','t_course_resell.fk_course as course_id','t_course_resell.fk_org_resell as org_resell_id'
        );
        $left  = new stdclass;
        $left->t_course_promote  = "t_course_resell.ver<>t_course_promote.ver AND t_course_resell.fk_course=t_course_promote.fk_course";
        $res = $db->select(self::TABLE,$condition,$item,"","",$left);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
        
#########################################################################################
    public static function getResellCourseList($page,$length,$data=array())
    {
        $db = self::InitDB('db_course','query');
        $table = array('t_course_resell');
        $condition[] = "t_course_resell.status <> -1";
        if (!empty($data->status)) {
            if ($data->status=='n') $condition[] = "t_course_resell.status = 1";
            if ($data->status=='f') $condition[] = "t_course_resell.status <= -1";
        }
        if (!empty($data->uid)) $condition[] = "t_course_resell.fk_org_resell={$data->uid}";
        if (!empty($data->search)) $condition[] = "t_course.title LIKE '%{$data->search}%'";

        $order = $data->sort;
        $item = array(
            't_course_resell.fk_course as course_id','t_course_resell.fk_org_resell','t_course_resell.price_resell','t_course_promote.price_promote','t_course.price_market','t_course_resell.status','t_course_resell.order_count','t_course_resell.enroll_count','t_course_resell.income','t_course_resell.ver',
            't_course.fk_user as fk_org_promote','t_course.title','t_course.type as course_type','t_course.price_market','t_course.price','(t_course.max_user-t_course.user_total) as remain_user','t_course.thumb_small',
            't_course_promote.ver as promote_ver','t_course_promote.status as promote_status'
         );
        $left  = new stdclass();
        $left->t_course  = "t_course.pk_course=t_course_resell.fk_course";
        $left->t_course_promote  = "t_course_resell.fk_course=t_course_promote.fk_course";
        $db->setPage($page);
        $db->setLimit($length);
        return $db->select($table,$condition,$item, "", $order, $left);
    }    
    
    public static function getResellCourse($courseIds,$resellOrgId,$condition)
    {
        $db = self::InitDB('db_course','query');
        $table = array('t_course_resell');
        $item = array("pk_course_resell","fk_org_resell","fk_course","price_resell","order_count","enroll_count","income","create_time","last_updated","ver","status");
        return $db->select($table,$condition,$item);
    }
    
    public static function updateResellCourse($courseId,$resellOrgId=0,$pcourse)
    {
        $db = self::InitDB("db_course");
        $condition[] = "fk_course = $courseId ";
        if(!empty($resellOrgId)) $condition[] = " fk_org_resell = $resellOrgId ";
        return $db->update("t_course_resell", $condition, $pcourse);
    }

    public static function addResellCourse($pcourse)
    {
        $db = self::InitDB("db_course");

        return $db->insert("t_course_resell", $pcourse);
    }

    public static function getResellCourseById($courseId,$resellOrgId)
    {
        $db = self::InitDB('db_course','query');
        $table = array('t_course_resell');
        $condition = "fk_org_resell = $resellOrgId and fk_course=$courseId ";
        $item = array("pk_course_resell","fk_org_resell","fk_course","price_resell","order_count","enroll_count","income","create_time","last_updated","ver","status");
        return $db->selectOne($table,$condition,$item);
    }    

    public static function getSalesCourse($params){
            $db = self::InitDB('db_course','query');
            $table = array('t_course_resell');
            $condition = '';
			$left =new stdclass;
			$left->t_course_promote	= "t_course_promote.fk_course=t_course_resell.fk_course";
			if(!empty($params['fk_org_resell'])){
				$condition.= "t_course_resell.fk_org_resell='".$params['fk_org_resell']."' "; 
			}
			if(!empty($params['con'])){
				$condition.=  "AND ".$params['con']; 
			}
            $items = array("t_course_resell.fk_course","t_course_resell.fk_org_resell","t_course_resell.price_resell","t_course_resell.ver","t_course_promote.ver","t_course_resell.status restatus","t_course_promote.status",);
            return $db->select($table,$condition,$items,"","",$left);
    }
    
    public static function getCourseResellCount($orgId)
    {
        $db    = self::InitDB("db_course", "query");
        $condition = "fk_org_resell=".$orgId;
        $item                  = array("count(*) as ct");
        $ret                   = $db->selectOne("t_course_resell", $condition, $item);
        $v                     = 0;
        if (!empty($ret) && !empty($ret['ct'])){
            $v = $ret['ct'];
        }

        return $v;
    }
}

