<?php

class course_db_courseUserDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_user';
    const ExpiredTime = 7200;

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

        return $db->insert(self::TABLE, $data);
    }
	
	public static function updateRegistration($courseUserId,$data){
		$db = self::InitDB("db_course");
		$condition = array("pk_course_user" => $courseUserId);
		return $db->update("t_course_user",$condition,$data);
	}
	
    public static function listsByCourseIdArr($idArr, $orgOwner='', $page = 1, $length = 0)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "status =1";
        $orgOwner && $condition = " AND fk_user_owner={$orgOwner}";

        if (is_array($idArr) && count($idArr)>0) {
            $condition .= ' AND fk_course IN ('.implode(',', $idArr).')';
        } else {
            $condition .= " AND fk_course={$idArr}";
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $res = $db->select(self::TABLE, $condition, '', '', 'pk_course_user desc');

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function listsByClassId($classId, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "fk_class={$classId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select(self::TABLE, $condition);
    }
    
    public static function listsByClassIdSort($classId, $page = 1, $length = 20,$orderby='',$cache)
    {
        $db = self::InitDB(self::dbName, 'query');
        $key =md5( "course.group.listsByClassIdSort.".$classId.$page.$length);
	if($cache){
        $v = redis_api::get($key);//print_r($v);
	if($v){return $v;}
        }
        $condition = "fk_class={$classId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $v=$db->select(self::TABLE, $condition,'', '', $orderby);
        redis_api::set($key,$v,300);
        return $v;
    }

    public static function getClassRegUserTotalNum($classId)
    {
        $db    = self::InitDB(self::dbName, 'query');
        $table = self::TABLE;
        $sql   = "select  count(*) as num from {$table} where `fk_class`={$classId} and `status` =1";

        $res = $db->execute($sql);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));

            return 0;
        }

        return $res[0]['num'];
    }

    public static function getClassRegUser($classId)
    {
        $db    = self::InitDB(self::dbName, 'query');
        $table = self::TABLE;
        $sql   = "select * from {$table} where `fk_class`={$classId} and `status` =1";

        $res = $db->execute($sql);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));

            return 0;
        }

        return $res;
    }
    public static function checkUserIsRegFromMainDb($uid, $courseId)
    {
        $db = self::InitDB(self::dbName);

        $condition = "fk_course={$courseId} and fk_user={$uid} and status=1";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function getUserRegCourse($userId, $courseIdArr = [], $page = 1, $length = -1, $ownerId = 0)
    {
        $db  = self::InitDB(self::dbName, 'query');
        $condition = "fk_user={$userId} AND status =1";
        if(!empty($ownerId)){
            $condition .= " and fk_user_owner = {$ownerId}";
        }
        if (!empty($courseIdArr)) {
            $courseIdStr = implode(',', $courseIdArr);
            $condition .= " AND fk_course IN ({$courseIdStr})";
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $res = $db->select(self::TABLE, $condition, '', '', 'pk_course_user desc');
        if ($res === false) return false;

        return $res;
    }

    public static function updateExpireTime($pKey, $time)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "pk_course_user={$pKey}";
        $data      = ['expire_time' => date('Y-m-d H:i:s', $time)];

        $res = $db->update(self::TABLE, $condition, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

	public static function getUserCourseCount($userId,$ownerId=0){

		$db = self::InitDB(self::dbName, 'query');
		if(!empty($ownerId)){
			 $condition = "fk_user={$userId} AND fk_user_owner={$ownerId} AND status =1";
		}else{
			$condition = "fk_user={$userId} AND status =1";
		}
		$items = array("count(fk_course) as course_count");
		return $db->select(self::TABLE,$condition,$items);
	}
	
	public static function getUserLivingCourse($userId,$type,$startTime,$ownerId=0){
		$db = self::InitDB(self::dbName, 'query');
		/* 废弃
		$left=array('t_course'=>'t_course.pk_course=t_course_user.fk_course',
					't_fee'=>'t_fee.fk_course=t_course_user.fk_course');
		*/		
		$left=array('t_course'=>'t_course.pk_course=t_course_user.fk_course');
		$condition = array(
						't_course_user.fk_user' => $userId,
						't_course.type' => $type,
						't_course_user.status =1',
						"t_course.end_time >= '$startTime'"
					);
		if(!empty($ownerId)){
			 $condition['t_course_user.fk_user_owner'] = $ownerId;
		}		
		$items = array("t_course_user.fk_user","t_course_user.fk_user_owner","t_course_user.fk_class","t_course_user.fk_course","t_course_user.status","t_course_user.expire_time",
					"t_course.title","t_course.thumb_big","t_course.thumb_med","t_course.thumb_small as thumb_sma","t_course.fee_type",'t_course.price');
		return $db->select(self::TABLE,$condition,$items,'','',$left);	
	}
	
	public static function getUserRegisterCourseList($userId,$page,$length,$ownerId=0,$title='',$type=0){
		$db = self::InitDB(self::dbName, 'query');
		/* 废弃
		$left=array('t_course'=>'t_course.pk_course=t_course_user.fk_course',
					't_course_class'=>'t_course_class.pk_class=t_course_user.fk_class',
					't_fee'  => "t_fee.fk_course=t_course_user.fk_course");
		*/			
		$left=array('t_course'=>'t_course.pk_course=t_course_user.fk_course',
					't_course_class'=>'t_course_class.pk_class=t_course_user.fk_class');
		$condition = array(
						't_course_user.fk_user' => $userId,
						't_course_user.status =1',
					);
		if(!empty($ownerId)){
			 $condition['t_course_user.fk_user_owner'] = $ownerId;
		}
		if(!empty($title)){
			array_push($condition,"t_course.title LIKE '%$title%'");
		}
		if(!empty($type)){
			array_push($condition,"t_course.type = {$type}");
		}
		$items = array("t_course_user.fk_user","t_course_user.fk_user_owner","t_course_user.fk_class","t_course_user.fk_course",
					"t_course.third_cate","t_course.title","t_course.thumb_big","t_course.thumb_med","t_course.thumb_small","t_course.fee_type","t_course.type","t_course.status",
					't_course_class.name as class_name','t_course_class.fk_user_class','t_course.price','t_course_class.progress_percent','t_course_class.progress_plan');
		
		$orderBy = array('t_course_user.pk_course_user'=>'desc');
		if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
		return $db->select(self::TABLE,$condition,$items,'',$orderBy,$left);	
	}

	public static function getStudentByOwnerId($ownerId){
        $table = array("t_course_user");
        $db    = self::InitDB('db_course', 'query');
		$condition = array('fk_user_owner' => $ownerId);
		$groupBy = 'fk_user';
        return $db->select($table, $condition, '', $groupBy);
    }

    public static function getOrgStudentCount($ownerId_str){
        $table=array('t_course_user');
        $db = self::InitDB('db_course','query');
        $item=array('count(fk_user) as student_count','fk_user_owner');
        $condition = "fk_user_owner IN ($ownerId_str) AND status = 1";
        $group = array('fk_user_owner');
        return $db->select($table,$condition,$item,$group);
    }
	
	//本机构下报名的课
	public static function getUserOrgCourse($data,$page,$length){
		$db = self::InitDB(self::dbName, 'query');

		$condition = "fk_user_owner = {$data['ownerId']} and fk_course = {$data['courseId']} and source > 0";
		if(!empty($data['classId'])){
			$condition .= " and fk_class = {$data['classId']}";
		}
		$db->setPage($page);
        $db->setLimit($length);
        $db->setCount(true);
	
		return $db->select(self::TABLE,$condition);
	}


    //获取报名课程   -- 学生
    public static function getCourseUser($param){
        $table = array("t_course_user");
        $db    = self::InitDB('db_course', 'query');
        $fk_user = $param['fk_user'];
        $fk_course = $param['fk_course'];
        $fk_class = $param['fk_class'];
        $condition = "fk_user = $fk_user AND fk_course = $fk_course AND fk_class = $fk_class  AND source > 0";
        return $db->select($table, $condition);
    }
	
}

