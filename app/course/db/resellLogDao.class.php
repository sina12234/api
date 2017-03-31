<?php

class course_db_resellLogDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_resell_log';

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

        return  $db->insert(self::TABLE, $data);
    }
    
    ###########################################################################################
    /*获取分销、推广成交记录*/
    public static function getCourseResellLog($page,$length,$params=array()){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_resell_log');
        $condition = '';
        if($params['type']==1){
            $condition .= 't_course_resell_log.fk_org_promote='.$params["org_promote_id"].'';
        }elseif($params['type']==2){
            $condition .= 't_course_resell_log.fk_org_resell='.$params["org_resell_id"].'';
        }
        if(!empty($params['course_resell_id'])){
            $condition .= ' AND t_course_resell_log.fk_course_resell='.$params["course_resell_id"].'';
        }
		if(!empty($params['fk_order_content'])){
			$condition .= " AND t_course_resell_log.fk_order_content IN ({$params['fk_order_content']})";
		}
        $order = 't_course_resell_log.last_updated desc';
        $item = array(
            't_course_resell_log.fk_course_resell as course_resell_id','t_course_resell_log.fk_org_resell as org_resell_id','t_course_resell_log.fk_org_promote as org_promote_id','t_course_resell_log.fk_user as user_id','t_course_resell_log.fk_order_content as order_content_id','t_course_resell_log.fk_order as order_id','t_course_resell_log.create_time','t_course_resell_log.last_updated','t_course_resell_log.price_resell','t_course_resell_log.price_promote','t_course.title'
        );
        $left  = new stdclass();
        $left->t_course  = "t_course.pk_course=t_course_resell_log.fk_course_resell";
        $db->setPage($page);
        $db->setLimit($length);
        $db->setCount(true);
        return $db->select($table,$condition,$item, "", $order, $left);

    }
}

