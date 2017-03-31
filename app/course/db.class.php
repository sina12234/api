<?php

/**
 * @author hetao fanbin
 */
class course_db
{
    public static function InitDB($dbname = "db_course", $dbtype = "main")
    {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);

        return $db;
    }

    public function addcourse($course)
    {
        $db = self::InitDB();

        return $db->insert("t_course", $course);
    }

    public static function getCourse($course_id)
    {
        $db  = self::InitDB("db_course", "query");
        $key = md5("course_db.getCourse.".$course_id);
        $v   = redis_api::get($key);
        if ($v) {
            return $v;
        }

        $condition              = array("status <> -1");
        $condition["pk_course"] = $course_id;
        $v                      = $db->selectOne("t_course", $condition);
        redis_api::set($key, $v, 60);

        return $v;
    }

    public function getMaxCourseIdByUid($uid, $courseType='')
    {
        $db  = self::InitDB("db_course", "query");
		$condition = " fk_user={$uid}";
		if(!empty($courseType)){
			$condition .= " and type={$courseType}";
		}
        $row = $db->selectOne("t_course", $condition, "max(pk_course) as course_id");
        if ($row === false) {
            return $row;
        }
        if (empty($row['course_id'])) {
            return 0;
        }

        return $row['course_id'];
    }

    public function getMaxClassIdByCid($course_id)
    {
        $db  = self::InitDB("db_course", "query");
        $row = $db->selectOne("t_course_class", array("fk_course" => $course_id), "max(pk_class) as class_id");
        if ($row === false) {
            return $row;
        }
        if (empty($row['class_id'])) {
            return 0;
        }

        return $row['class_id'];
    }

    /**
     * 修改课程
     * 语法 update($table,$condition="",$item="");
     */
    public function updateCourse($course_id, $Course)
    {
        //define('DEBUG',TRUE);
        $db    = self::InitDB("db_course");
        $key   = md5("course_db.getCourse.".$course_id);
        $v     = redis_api::del($key);
        $key   = "course_api::getcourselist.{$course_id}.v2";
        $v     = redis_api::del($key);
        $key_1 = md5("course_db.t_course_user.ct.".$course_id);
        //$key_2 =md5( "course_db.t_course_user.ct.".$class_id);
        redis_api::del($key_1);

        //redis_api::del($key_2);
        return $db->update("t_course", array("pk_course" => $course_id), $Course);
    }

    public function updateCourseStatus($course_id, $status, $progressStatus=0)
    {
        $db        = self::InitDB("db_course");
        $key       = md5("course_db.getCourse.".$course_id);
        $v         = redis_api::del($key);
        $key       = "course_api::getcourselist.{$course_id}.v2";
        $v         = redis_api::del($key);
        $condition = array("pk_course" => $course_id);
		if($progressStatus){
			$data['progress_status'] = $progressStatus;
		}
		$data['status'] = $status;
		
        return $db->update("t_course", $condition, $data);
    }

    public function updateCourseAdminStatus($course_id, $status)
    {
        $db        = self::InitDB("db_course");
        $key       = md5("course_db.getCourse.".$course_id);
        $v         = redis_api::del($key);
        $key       = "course_api::getcourselist.{$course_id}.v2";
        $v         = redis_api::del($key);
        $condition = array("pk_course" => $course_id);

        return $db->update("t_course", $condition, array("admin_status" => $status));
    }

	public function getclassMaxuserTotal($courseId){
		$db        = self::InitDB("db_course");
		$item = new stdclass;
		$item->sum_max_user = "sum(max_user)";
		//	$item->service_score = "sum(service_score)";
		$table = array("t_course_class");
		$condition = array("status <> -1");
		$condition["fk_course"] = $courseId;
		return $db->select($table, $condition, $item);
	}

    public static function getstudentCourse($id){
		$db        = self::InitDB("db_course");
		$table = array("t_course_user");
		$condition = array("status =1");
		$condition["fk_user"] = $id;
		return $db->select($table, $condition);
	}
    public static function getstudentCount($id){
		$db        = self::InitDB("db_course");
		$table = array("t_course");
		$condition ="pk_course IN ($id)";
		return $db->select($table, $condition);
	}
	public static function getMgrRecommendCourse($id){
		$db        = self::InitDB("db_course");
		$table = array("t_course");
		$condition ="pk_course IN ($id) and (admin_status !=-2 and admin_status !=0)";
		return $db->select($table, $condition);
	}
	public static function SearchCourseAjax($data){
		$db        = self::InitDB("db_course", "query");
		$table = array("t_course");
		$condition ="title like '%".$data."%'";
		return $db->select($table, $condition);
	}
	public static function mgrSearchCourse($data){
		$db        = self::InitDB("db_course", "query");
		$table = array("t_course");
		$condition ="title like '%".$data."%' and (admin_status !=-2 and admin_status !=0) ";
		return $db->select($table, $condition);
	}
    /* 参数
	 * page      页数
	 * length    取出条数
	 * fee       付费免费 fee 付费 free 免费
	 * oid       机构id
	 * grade_id  年级
	 * status    状态 1 未开始 2 直播中 3 已完结 －1 删除 0 默认状态
	 * week      周 如果为1则取出未来30天的课程
	 * shelf     1 上架 -1 未审核通过 -2 下架 0 默认 //筛选规则  1为上架 其他为未上架
	 * key_all   boolean sphinx索引生成参数
	 * begin_id  开始查找的course_id
	 */
    public function courseList($page = 1, $length = 4, $fee = null, $oid = null, $grade_id = null, $status = null, $week = false, $shelf = false, $key_all = false, $begin_id = null, $data = array()){
        //define('DEBUG',true);
        $condition = array();
        if ($fee == "fee") {
            $condition = array("fee_type <> 0");
        } elseif ($fee == "free") {
            $condition = array("fee_type = 0");
        }
        if ($week) {
            $time  = date('Y-m-d', time());
            $time1 = date('Y-m-d', time() + 86400 * 30);
            //$condition[] = "start_time >CURDATE()";
            $condition[] = "start_time >\"$time\"";
            $condition[] = "start_time <\"$time1\"";
        }
        if ($oid) {
           $condition[]='fk_user in ('.$oid.')';
        }
        if ($shelf != false) {
            if ($shelf == "1") {
                $condition['admin_status'] = $shelf;
            } else {
                $condition[] = "admin_status in(0,-2)";
            }
            if ($status) {
                $condition['status'] = $status;
            } else {
                $condition[] = 'status>0';
            }
        }
        if ($grade_id) {
            $condition["fk_grade"] = $grade_id;
        }
        $item    = array(
            "t_sort.sort", "pk_course", "first_cate","second_cate","third_cate",
			"fk_cate", "type", "fk_grade", "fk_user","title",
            "tags", "descript", "scope","thumb_big", "thumb_med", "thumb_small",
            "t_course.start_time", "t_course.end_time", "public_type", "fee_type",
            "user_total", "max_user", "min_user", "t_course.status", "top", "admin_status",
			"check_status","is_promote",
            "admin_status", "price", "price_market",
            "t_course.last_updated", "t_course.create_time"
        );
        $orderby = array();
        if ($week) {
            $orderby["t_course.start_time"] = "asc";
        }
        if ($data) {
            if (!empty($data["create_time"])) {
                $orderby["t_course.create_time"] = $data["create_time"];
            }
            if (!empty($data["user_total"])) {
                $orderby["t_course.user_total"] = $data["user_total"];
            }
			if (!empty($data["type"])) {
				if($data["type"]==1){
					$condition[] = "t_course.type<>2 AND t_course.type<>3";
				}else{
					$condition["t_course.type"] = $data["type"];
				}
			}
        } else {
            $orderby["top"] = "desc";
        }
        $left         = new stdclass;
        $left->t_sort = "t_sort.fk_course=t_course.pk_course";
		//判断是否sphinx生成索引，走单独的sphinx从库
        if ($key_all == true) {
            $condition = array("pk_course >= $begin_id");
            $orderby   = array("pk_course" => "asc");
			$db = self::InitDB("db_course", "query_sphinx");
			$left->t_course_promote = "t_course_promote.fk_course=t_course.pk_course";
			$item    = array(
				"t_sort.sort", "pk_course", "first_cate","second_cate","third_cate",
				"fk_cate", "type", "fk_grade", "fk_user","title",
				"tags", "descript", "scope","thumb_big", "thumb_med", "thumb_small",
				"t_course.start_time", "t_course.end_time", "public_type", "fee_type",
				"user_total", "max_user", "min_user", "t_course.status", "top", "admin_status",
				"check_status","is_promote","price_promote","t_course_promote.status as promote_status",
				"admin_status", "price", "price_market",
				"t_course.last_updated", "t_course.create_time"
        );
        }else{
			$db = self::InitDB("db_course", "query");
		}
		// 新增的把title不为空的筛选出来
		$condition[] = 'title<> ""';
		if(isset($data["search"])){
			$condition[] = "title LIKE '%{$data["search"]}%'";
		}
        $db->setPage($page);
        $db->setLimit($length);

        return $db->select("t_course", $condition, $item, "", $orderby, $left);
    }


    public function courseLikeList($user_id = null,$course_ids = array(),$data = array(),$orderby=null){
        //define('DEBUG',true);
		$condition = array("status <> -1");
		$condition[] = "deleted <> -1";
		$condition[] = "check_status <> -1";
		if(!empty($user_id)){
			$condition["fk_user"] = $user_id;
		}
		if (is_array($course_ids)) {
			if(!empty($course_ids)){
				$condition[] = 'pk_course in ('.implode(',', $course_ids).')';
			}
		}else{
			if(!empty($course_ids)){
				$condition["pk_course"] = $course_ids;
			}
		}

		if(isset($data["search"])){
			$condition[] = "title LIKE '%{$data["search"]}%'";
		}

		$item    = array(
			"course_id"=>"pk_course",
			"cate_id"=>"fk_cate",
			"type"=>"type",
			"grade"=>"fk_grade",
			"user_id"=>"fk_user",
			"title",
			"tags",
			"descript",
			"thumb_big",
			"thumb_med",
			"thumb_small",
			"t_course.start_time",
			"t_course.end_time",
			"public_type",
			"fee_type",
			"max_user",
			"min_user",
			"user_total",
			"top",
			"status",
			"admin_status",

			"deleted",
			"check_status",

			//"system_status",
			"admin_status",
			"t_course.last_updated",
			"t_course.create_time",

		);
        $orderby = array();
		$orderby["fk_user"] = "desc";
        $db = self::InitDB("db_course", "query");
        return $db->select("t_course", $condition, $item, "", $orderby);
    }

    public function MgrcourseList($cond = array(),$orderBy = array(),$groupBy = array(), $page = 1,$length =10){
		$db = self::InitDB("db_course", "query");

        $condition = array();
		$item    = array(
			"course_id"=>"pk_course",
			"cate_id"=>"fk_cate",
			"type"=>"type",
			"grade"=>"fk_grade",
			"user_id"=>"fk_user",
			"title",
			"tags",
			"descript",
			"thumb_big",
			"thumb_med",
			"thumb_small",
			"t_course.start_time",
			"t_course.end_time",
			"public_type",
			"fee_type",
			"max_user",
			"min_user",
			"user_total",
			"top",
			"status",
			"admin_status",
			"deleted",
			"check_status",
			"admin_status",
			"t_course.last_updated",
			"t_course.create_time",
			"t_sort.sort",
		);
        $orderby = array();
		if(!empty($orderBy["starttime"])){
			$orderby["start_time"] = $orderBy["starttime"];
		}
		if(!empty($orderBy["usertotal"])){
			$orderby["user_total"] = $orderBy["usertotal"];
		}
		if(!empty($orderBy["status"])){
			$orderby["status"] = $orderBy["status"];
		}
		if(!empty($orderBy["feetype"])){
			$orderby["fee_type"] = $orderBy["feetype"];
		}
		if(empty($orderBy)){
			$orderby["pk_course"] = "desc";
		}
		if((!empty($cond["starttime1"])) && (!empty($cond["starttime2"]))){
			$time1 = $cond["starttime1"] ;
			$time2 = $cond["starttime2"] ;
			$condition[] = "start_time >\"$time1\"";
			$condition[] = "start_time <\"$time2\"";
		}
		if(isset($cond["status"])){
			$condition["status"] = $cond["status"];
		}
		if(isset($cond["admin_status"])){
			$condition["admin_status"] = $cond["admin_status"];
		}
		if(!empty($cond["user_id"])){
			$condition["fk_user"] = $cond["user_id"];
		}
        $left = new stdclass;
        $left->t_sort = "t_sort.fk_course=t_course.pk_course";
        $db->setPage($page);
        $db->setLimit($length);
		$condition[] = 'title<> ""';
		//$condition["pk_course"] = 369;
        return $db->select("t_course", $condition, $item, "", $orderby, $left);
    }
    public function cateList()
    {
        $db = self::InitDB("db_course", "query");

        return $db->select("t_course_category", "", array("pk_cate", "name", "last_updated"), "", "", "");
    }

    public function gradeList()
    {
        $db = self::InitDB("db_course", "query");

        return $db->select("t_course_grade", "", array("pk_grade", "name", "last_updated"), "", "", "");
    }

    public function addSection($section)
    {
        $db = self::InitDB("db_course");

        return $db->insert("t_course_section", $section);
    }

    /*	public function delSection($sid,$cid){
		$condition = array("pk_section"=>$sid,"fk_course"=>$cid);
		return $this->_db->delete("t_course_section",$condition);
	}
*/
    public function delSection($sid, $cid)
    {
        $db        = self::InitDB("db_course");
        $condition = array("pk_section" => $sid, "fk_course" => $cid);

        return $db->update("t_course_section", $condition, array("status" => "-1"));
    }

    public function updateSection($section_id, $section)
    {
        $db = self::InitDB("db_course");

        return $db->update("t_course_section", array("pk_section" => $section_id), $section);
    }

    public function updateSectionStatus($section_id, $status)
    {
        $db        = self::InitDB("db_course");
        $condition = array("pk_section" => $section_id);

        return $db->update("t_course_section", $condition, array("status" => $status));
    }

    /*
	 *获取单个章节
	 */
    public function getSection($sid)
    {
        $item               = new stdclass;
        $item->section_id   = "pk_section";
        $item->course_id    = "fk_course";
        $item->name         = "name";
        $item->last_updated = "last_updated";
        $item->descript     = "descript";
        $item->status       = "status";
        $db                 = self::InitDB("db_course", "query");

        return $db->selectOne("t_course_section", array("pk_section" => $sid), $item);
    }

    /*
	 *获取多个章节
	 */
    public function sectionList($course_id,$sectionIds='')
    {
        $db                     = self::InitDB("db_course", "query");
        $condition = " status != -1";
        if(!empty($course_id)){
            $condition .= " AND fk_course = {$course_id}";
        }
        if(!empty($sectionIds)){
            $condition .= " AND pk_section IN ({$sectionIds})";
        }
        return $db->select("t_course_section", $condition, array("pk_section", "fk_course", "name", "last_updated", "descript", "create_time", "order_no", "status"), "", array("pk_section" => "asc"), "");
    }

    public function addClass($class)
    {
        $db = self::InitDB("db_course");

        return $db->insert("t_course_class", $class);
    }

    public function getClass($class_id)
    {
        $db = self::InitDB("db_course", "query");
        //获取课程里的班级
        $key = md5("course_db.t_course_class.id.".$class_id);
        $v   = redis_api::get($key);
        if ($v !== false) return $v;

        $v = $db->selectOne("t_course_class", array("pk_class" => $class_id));
        redis_api::set($key, $v, 120);

        return $v;
    }

    public function getClassindex($course_id)
    {
        $db = self::InitDB("db_course", "query");
        //获取课程里的班级
        $key = md5("course_db.t_course_class_course.id.".$course_id);
        $v   = redis_api::get($key);
        if ($v !== false) return $v;

        $items = [
            'pk_class','name','fk_user_class','region_level0','region_level1','region_level2',
            'address','max_user','user_total'
        ]; 
        $v = $db->selectOne("t_course_class", array("fk_course" => $course_id), $items, '',  array("pk_class"=>"asc"));
        redis_api::set($key, $v, 120);

        return $v;
    }

    //获取课程排序信息
    public function getSort($course_id)
    {
        $db  = self::InitDB("db_course", "query");
        $key = md5("course_db.t_sort.".$course_id);
        $v   = redis_api::get($key);
        if ($v !== false) {
            return $v;
        }
        $v = $db->selectOne("t_sort", array("fk_course" => $course_id));
        if (!$v) $v = 0;
        redis_api::set($key, $v, 120);

        return $v;
    }

    public function updateClass($class_id, $class)
    {
        $db = self::InitDB("db_course");
        //获取课程里的班级
        $class_old = self::getClass($class_id);
        if (empty($class_old['fk_course'])) return false;
        $course_id = $class_old['fk_course'];
        $key       = md5("course_db.t_course_class.".$course_id);
        redis_api::del($key);
        $key = md5("course_db.t_course_class.id.".$class_id);
        redis_api::del($key);
        //$key_1 =md5( "course_db.t_course_user.ct.".$course_id);
        $key_2 = md5("course_db.t_course_user.ct.".$class_id);
        //redis_api::del($key_1);
        redis_api::del($key_2);

        return $db->update("t_course_class", array("pk_class" => $class_id), $class);
    }

    public function updateClassStatus($class_id, $status, $progressStatus=0)
    {
        $db = self::InitDB("db_course");
        //获取课程里的班级
        $class_old = self::getClass($class_id);
        if (empty($class_old['fk_course'])) return false;
        $course_id = $class_old['fk_course'];
        $key       = md5("course_db.t_course_class.".$course_id);
        redis_api::del($key);
        $key = md5("course_db.t_course_class.id.".$class_id);
        redis_api::del($key);

        $condition = array("pk_class" => $class_id);
		if($progressStatus){
			$data['progress_status'] = $progressStatus;
		}
		$data['status'] = $status;
		
        return $db->update("t_course_class", $condition, $data);
    }

    public function delClass($class_id)
    {
        $db = self::InitDB("db_course");
        //获取课程里的班级
        //获取课程里的班级
        $class_old = self::getClass($class_id);
        if (empty($class_old['fk_course'])) return false;
        $course_id = $class_old['fk_course'];
        $key       = md5("course_db.t_course_class.".$course_id);
        redis_api::del($key);
        $key = md5("course_db.t_course_class.id.".$class_id);
        redis_api::del($key);

        $condition = array("pk_class" => $class_id);

        return $db->update("t_course_class", $condition, array("status" => "-1"));
    }

    public function classList($course_id)
    {
        $db = self::InitDB("db_course", "query");
        //获取课程里的班级
        $key     = md5("course_db.t_course_class.".$course_id);
        $keyHash = md5("content");
        $v       = redis_api::hGet($key, $keyHash);
        //临时修改()
        //if ($v !== false) return $v;

        $condition              = array();
        $condition              = array("status <> -1");
        $condition["fk_course"] = $course_id;
        $item                   = array("pk_class", "fk_course", "fk_user_class", "name", "descript", "type", "status", "user_total", "max_user", "min_user", "last_updated", "create_time","address","region_level0","region_level1","region_level2");
        $v                      = $db->select("t_course_class", $condition, $item, "", array("pk_class" => "asc"), "");
        redis_api::hset($key, $keyHash, $v);

        return $v;
    }
	/*
	 *	@import courseids array()  课程的id集合
	 *		  	user_class_id  班主任信息
	 *			user_id 机构id  如果0就是搜出所有的
	 *			orderby
	 *
	 */
    public function classListByCourseIds($user_id=null,$user_class_id=null,$cond=array(),$orderby=array(),$page=null,$length=null){
        $db = self::InitDB("db_course", "query");
        //获取课程里的班级
        $condition = array();
		$condition = array("t_course_class.status <> -1");
		if(!empty($cond["course_ids"])){
			$courseids = implode(',', $cond["course_ids"]);
			$condition[] = "t_course_class.fk_course IN ($courseids)";
		}
		if(!empty($user_id)){
			$condition["t_course_class.fk_user"] = $user_id;
		}
		if(!empty($user_class_id)){
			$condition["fk_user_class"] = $user_class_id;
		}
		
		if(!empty($cond["course_type"])&&$cond["course_type"]=='3'){
			$condition[] = "t_course.type='".$cond["course_type"]."'";
		}elseif(!empty($cond["course_type"])&&$cond["course_type"]=='2'){
			$condition[] = "t_course.type='".$cond["course_type"]."'";
		}elseif(!empty($cond["course_type"])&&$cond["course_type"]=='1'){
			$condition[] = "t_course.type='".$cond["course_type"]."'";
		}
		$nowTime = date("Y-m-d H:i:s",time());
		if(isset($cond["ut"])&&$cond["ut"]=='1'){
            $condition[] = "t_course_class.`progress_status` =1";              
		}elseif(isset($cond["ut"])&&$cond["ut"]=='2'){
            $condition[] = "t_course_class.`progress_status`=2";
		}elseif(isset($cond["ut"])&&$cond["ut"]=='3'){
           $condition[] = "t_course_class.`progress_status` =3";  
		}
		$item = array(
			"user_id"=>"t_course_class.fk_user",
			"class_id"=>"pk_class",
			"course_id"=>"t_course_class.fk_course",
			"user_class_id"=>"fk_user_class",
			"t_course_class.name",
			"t_course_class.descript",
			"t_course.type",
			"t_course_class.status",
			"t_course_class.user_total",
			"t_course_class.max_user",
			"t_course_class.min_user",
			"t_course_class.last_updated",
			"t_course_class.create_time",
			"t_course_class.progress_percent",
			"t_course_class.progress_plan",
			"t_course_class.progress_status",
			"course_title"=>"t_course.title",
			"course_type"=>"t_course.type",
			"course_thumb"=>"t_course.thumb_med",
			"fee_type"=>"t_course.fee_type",
			"t_course.price","t_course.price_market",
			"start_time"=>"t_course.start_time",
		);
        $left  = new stdclass;
        $left->t_course  = "t_course.pk_course=t_course_class.fk_course";
		$orCon = array();
		if(!empty($orderby['st'])&&$orderby['st']=="1"){
			$orCon['create_time'] = "desc";
		}elseif(!empty($orderby['st'])&&$orderby['st']=="2"){
			$orCon['user_total'] = "desc";
		}else{
			$orCon['create_time'] = "desc";
		}
		if ($page) {
			$db->setPage($page);
		}
		if ($length) {
			$db->setLimit($length);
		}
		
		$v = $db->select("t_course_class", $condition, $item,"", $orCon, $left);
		return $v;
    }
    public function classListByCond($user_id,$user_class_id,$course_id){
        $db = self::InitDB("db_course", "query");
        //获取课程里的班级

        $condition = array();
        $condition = array("t_course_class.status <> -1");
		if(!empty($course_id)){
			$condition["fk_course"] = $course_id;
		}
		if(!empty($user_id)){
			$condition["t_course_class.fk_user"] = $user_id;
		}
		if(!empty($user_class_id)){
			$condition["fk_user_class"] = $user_class_id;
		}
		$item = array(
			"user_id"=>"t_course_class.fk_user",
			"class_id"=>"pk_class",
			"course_id"=>"fk_course",
			"user_class_id"=>"fk_user_class",
			"t_course_class.name",
			"t_course_class.descript",
			"t_course_class.type",
			"t_course_class.status",
			"t_course_class.user_total",
			"t_course_class.max_user",
			"t_course_class.min_user",
			"t_course_class.last_updated",
			"t_course_class.create_time",
			"course_title"=>"t_course.title"
		);
        $left  = new stdclass;
        $left->t_course  = "t_course.pk_course=t_course_class.fk_course";
		$v = $db->select("t_course_class", $condition, $item, "", array("pk_class" => "asc"), $left);
		return $v;
    }

    public function listClasses($classIdsStr)
    {
        $condition = array();
        $condition = array("pk_class in ( $classIdsStr)");
        $item      = array("pk_class", "fk_course", "fk_user", "fk_user_class", "name",
						  "descript", "type", "status", "user_total", "max_user", "min_user",
						  "region_level0","region_level1","region_level2",'address',
						  "last_updated", "create_time");
        $db        = self::InitDB("db_course", "query");

        return $db->select("t_course_class", $condition, $item, "", "", "");
    }

    public function listSections($sectionIdsStr)
    {
        $condition = array();
        $condition = array("pk_section in ( $sectionIdsStr)");
        $item      = array("pk_section", "fk_course", "name", "descript", "order_no", "status", "last_updated", "create_time");
        $db        = self::InitDB("db_course", "query");

        return $db->select("t_course_section", $condition, $item, "", "", "");
    }

    //for sphinx indexing course
    public function listClassesByCourseIds($idsStr)
    {
        $condition = array("fk_course in ( $idsStr )", "status <> -1");
        $item      = array("pk_class", "fk_course");
        $db        = self::InitDB("db_course", "query_sphinx");

        return $db->select("t_course_class", $condition, $item, "", array("pk_class" => "asc"), "");
    }

    //for sphinx indexing course
    public function listSectionsByCourseIds($idsStr){
        $condition = array("fk_course in ( $idsStr )", "status <> -1");
        $item      = array("pk_section", "fk_course", "descript");
        $db        = self::InitDB("db_course", "query_sphinx");
        return $db->select("t_course_section", $condition, $item, "", array("pk_section" => "asc"), "");
    }

    //for sphinx indexing course
    public function listPlanDateByCourseIds($idsStr){
        $condition = array("fk_course in ( $idsStr )","status <> -1","deleted = 0");
        $item      = array("fk_course", "start_time");
        $db        = self::InitDB("db_course", "query_sphinx");
        return $db->select("t_course_plan", $condition, $item, "", "", "");
    }

	//for sphinx indexing course
    public function listPlanByCourseIds($idsStr){
        $condition = array("fk_course in ( $idsStr )","status <> -1","deleted = 0");
        $db        = self::InitDB("db_course", "query_sphinx");
		$order     = array('start_time'=>'asc');
        return $db->select("t_course_plan", $condition,'','',$order);
    }

    //for sphinx indexing plan
    public function listPlans($page, $length, $minute=NULL)
    {
        $left                   = new stdclass;
        $left->t_course_section = "t_course_section.pk_section=t_course_plan.fk_section";
        $left->t_course_class   = "t_course_class.pk_class=t_course_plan.fk_class";
        $left->t_course         = "t_course.pk_course=t_course_plan.fk_course";
        $db                     = self::InitDB("db_course", "query_sphinx");
        $db->setPage($page);
        $db->setLimit($length);
		if(NULL !== $minute && (int)($minute)){
			$condition[] = "(t_course_plan.last_updated > DATE_SUB(NOW(),INTERVAL $minute MINUTE) OR t_course.last_updated > DATE_SUB(NOW(),INTERVAL $minute MINUTE))";
		}else{
			$condition =array();
		}
        $orderby   = array("pk_plan" => "asc");
        $item      = array(
            "pk_plan", "fk_user_plan", "t_course_plan.fk_course","t_course_plan.fk_video as video_id",
            "fk_section", "fk_class", "t_course_plan.start_time", "t_course_plan.end_time", "live_public_type",
            "video_public_type", "video_trial_time", "t_course_plan.status", "t_course_plan.create_time",
            "t_course_plan.last_updated", "t_course_section.name as section_name","t_course.last_updated","t_course_section.order_no section_order_no",
            "t_course_class.name as class_name", "t_course_class.fk_user_class", "t_course_class.max_user",
            "t_course_class.user_total","t_course_class.region_level0","t_course_class.region_level1","t_course_class.region_level2","t_course_class.address",
			"fk_cate", "fk_grade", "t_course.first_cate","t_course.second_cate","t_course.third_cate","t_course.thumb_big course_thumb_big","t_course.thumb_med course_thumb_med",
            "t_course.title as course_name", "t_course.status as course_status","t_course.fee_type","t_course.fk_user as fk_user_owner","t_course.thumb_small course_thumb_small",
            "t_course.admin_status as admin_status","t_course.type as course_type","t_course_section.descript as section_desc"
        );

        return $db->select("t_course_plan", $condition, $item, "", $orderby, $left);
    }

    public function addPlan($Plan)
    {
        $db = self::InitDB("db_course");

        return $db->insert("t_course_plan", $Plan);
    }

    public function updatePlan($plan_id, $Plan)
    {
        $condition = array("pk_plan" => $plan_id);
        $db        = self::InitDB("db_course");

        return $db->update("t_course_plan", $condition, $Plan);
    }

	 public function updatePlanTeacher($course_id, $class_id, $teacher_id, $data){
        $condition = array("fk_class" => $class_id, "fk_course" => $course_id, 'fk_user_plan'=>$teacher_id);
        $db        = self::InitDB("db_course");

        return $db->update("t_course_plan", $condition, $data);
    }

	public function MgrdeletePlan($cid = null,$deleted = null){
		if(empty($cid)){
			return false;
		}
		$condition = array("fk_course" => $cid, );
		$db = self::InitDB("db_course");

		return $db->update("t_course_plan", $condition, array("deleted" => $deleted));
	}
    public function delPlan($cid = null, $class_id = null, $sid = null)
    {
		$condition=array();
        if ($sid) {
            $condition = array("fk_section" => $sid);
        }
        if ($cid && $class_id) {
            $condition = array("fk_course" => $cid, "fk_class" => $class_id);
        }
        if ($cid && $sid) {
            $condition = array("fk_course" => $cid, "fk_section" => $sid);
        }
		if(empty($condition))return false;
        $db = self::InitDB("db_course");

        return $db->update("t_course_plan", $condition, array("status" => "-1"));
    }

    public function updatePlanStatus($plan_id, $status)
    {
        $db  = self::InitDB("db_course");
        $key = md5("db_course.plan.".$plan_id);
        redis_api::del($key);
        $condition = array("pk_plan" => $plan_id);

        return $db->update("t_course_plan", $condition, array("status" => $status));
    }

    public function planList($course_id = 0, $orgUserId = 0, $class_id = 0, $user_plan_id = 0, $section_id = 0, $plan_id = 0, $week = false, $allCourse = true, $order_by = "desc", $data = array(), $page = null, $length = null)
    {
        $db        = self::InitDB("db_course", "query");
        $condition = array();
        $condition = array("t_course_plan.status <> -1");
        if ($course_id) {
            $condition["pk_course"] = $course_id;
        }
        if ($orgUserId) {
            $condition["t_course_plan.fk_user"] = $orgUserId;
        }
        if ($class_id) {
            $condition['pk_class'] = $class_id;
        }
        if ($user_plan_id) {
            $condition['fk_user_plan'] = $user_plan_id;
        }
        if ($section_id) {
            $condition['pk_section'] = $section_id;
        }
        if ($plan_id) {
            $condition['pk_plan'] = $plan_id;
        }
        if ($week) {
            $time1       = date('Y-m-d', strtotime('today'));
            $time2       = date('Y-m-d', strtotime('today') + 86400 * 30);
            $condition[] = "t_course_plan.start_time >\"$time1\"";
            $condition[] = "t_course_plan.start_time <\"$time2\"";
        }
        // 获那天0点到24点
        if (isset($data["start_time"])) {
            $startTime = $data["start_time"];
            //	$endstartTime = $data["start_time"]+86400;
            $startTimes = date('Y-m-d H:i:s', $startTime);
            //	$endstartTimes = date('Y-m-d',$endstartTime);
            $condition[] = "t_course_plan.start_time >\"$startTimes\"";
            //	$condition[] = "t_course_plan.start_time <\"$endstartTimes\"";
        }

		//获取一段时间
		if(isset($data['partStart']) && isset($data['partEnd'])){
			$condition[] = "t_course_plan.start_time >= '".$data['partStart']."' and  t_course_plan.start_time <= '".$data['partEnd']."' ";
		}

		//这个用于timetable的
		if (isset($data["endstart_time"])) {
			if (isset($data["status"])) {
				if ($data["status"] == 3) {
					$endstartTime  = $data["endstart_time"];
					$endstartTimes = date('Y-m-d H:i:s', $endstartTime);
					$condition[]   = "(t_course_plan.start_time <\"$endstartTimes\" or t_course_plan.status=3)";
					//$condition[]   = "t_course_plan.start_time <\"$endstartTimes\"";
					//  $condition[] = "t_course_plan.status = 3";
				}else{
					$endstartTime  = $data["endstart_time"];
					$endstartTimes = date('Y-m-d H:i:s', $endstartTime);
					$condition[]   = "t_course_plan.start_time <\"$endstartTimes\"";
				}
			}else{
				$endstartTime  = $data["endstart_time"];
				$endstartTimes = date('Y-m-d H:i:s', $endstartTime);
				$condition[]   = "t_course_plan.start_time <\"$endstartTimes\"";
			}
        } else {
			//这个是用于老师个人中心页面的筛选当天的plan
            if (isset($data["start_time"])) {
                $endstartTime  = $data["start_time"] + 86400;
                $endstartTimes = date('Y-m-d H:i:s', $endstartTime);
                $condition[]   = "t_course_plan.start_time <\"$endstartTimes\"";
            }
        }
        if (isset($data["status"])) {
            if ($data["status"] == 3) {
              //  $condition[] = "t_course_plan.status = 3";
            }
            if ($data["status"] == 1) {
                $condition[] = "t_course_plan.status <> 3";
            }
			//当status==-3的时候就是筛选出全部的plan然后按照asc排序
            if ($data["status"] == -3) {
            }
        }
		if(isset($data['type'])){
            $condition[] = "t_course.type={$data['type']}";
        }

        $item = array(
            "plan_id"                                          => "pk_plan",
            "t_course_plan.fk_user as user_id", "user_plan_id" => "fk_user_plan", "course_id" => "pk_course", "t_course.pk_course",
            "section_id"                                       => "pk_section", "class_id" => "pk_class",
            "fk_user_class",//班主任
            "fk_user_plan",//上课老师

            "user_total_class"                                 => "t_course_class.user_total",//上
            "max_user_class"                                   => "t_course_class.max_user",//
            "section_descipt"                                  => "t_course_section.descript",//

            "user_total_course"                                => "t_course.user_total",//上
            "max_user_course"                                  => "t_course.max_user",//

            "fk_user_course"                                   => "t_course.fk_user",//
            "t_course_plan.start_time", "t_course_plan.end_time",
            // "user_total", "max_user","min_user","status","system_status",
            "t_course_plan.live_public_type",
            "t_course_plan.video_public_type",
            "t_course_plan.video_trial_time",
            "status"                                           => "t_course_plan.status",
            "plan_status"                                      => "t_course_plan.status",
            "course_status"                                    => "t_course.status",
            "section_status"                                   => "t_course_section.status",
            "type_id"                                          => "t_course.type",
            "video_id"                                         => "t_course_plan.fk_video",
            "t_course_plan.last_updated",
            "t_course_plan.create_time",
            "t_course.status course_status",
            "t_course.title",
            "t_course.thumb_small",
            "t_course.thumb_med",
            "t_course.thumb_big",
            "t_course.price",
            "t_course.price_market",
            "t_course.admin_status",
            "t_course.fee_type",
            "course_type"=>"t_course.type",
            "t_course_section.name section_name",
			"t_course_section.order_no",
            "t_course_class.name class_name",
        );
        if ($week) {
            $orderby = array("t_course_plan.start_time" => "asc");
        } else {
			if(!empty($data['type']) &&($data['type'] == 1 || $data['type'] == 3)){
                $orderby = array("t_course_plan.start_time" => 'asc');
            }else{
                $orderby = array("pk_plan" => $order_by,);
            }
            if (isset($data["status"])) {
                if ($data["status"] == 3) {
                    $orderby = array("t_course_plan.start_time" => "desc");
                }
                if ($data["status"] == 1) {
                    $orderby = array("t_course_plan.start_time" => "asc");
                }
                if ($data["status"] == -3) {
                    $orderby = array("t_course_plan.start_time" => "asc");
                }
            }
        }
        if ($page) {
            $db->setPage($page);
        }
        if ($length) {
            $db->setLimit($length);
        }
        $table = array("t_course_plan", "t_course", "t_course_section", "t_course_class");
        if (!$allCourse) {
            $condition[] = "t_course.admin_status = 1";
        }
        $condition[] = "t_course_plan.fk_course =t_course.pk_course";
        $condition[] = "t_course_plan.fk_section=t_course_section.pk_section";
        $condition[] = "t_course_plan.fk_class=t_course_class.pk_class";

        return $db->select($table, $condition, $item, "", $orderby);
    }


    public function planEndGroupByclassIds($classIdsArr=array(),$userId=null,$type,$ut){
        $db        = self::InitDB("db_course", "query");
        $condition = array();
		$nowTime = date("Y-m-d H:i:s",time());
		if(!empty($userId)){
			$condition["fk_user"] = $userId;
		}
		$item = array('fk_user',"fk_class","start_time","status","fk_course","pk_plan","fk_video");
		$table = "t_course_plan";
		$classIds = implode(',',$classIdsArr);
		$condition[] = "fk_class IN ($classIds)";
		return $db->select($table,$condition,$item,"");
    }
	
	public function planEndGroupByclassIdsV2($classIdsArr=array(),$userId=null)
	{

        $db        = self::InitDB("db_course", "query");
        $condition = array();
		if(!empty($userId)){
				$condition["fk_user"] = $userId;
		}
		$table = "t_course_plan";
        $item = array('count(pk_plan) as planend_count','fk_user',"fk_class");
		$classIds = implode(',',$classIdsArr);
		$condition[] = "fk_class IN ($classIds)";
        $condition["status"] = 3;
        $group = array('fk_class');
        return $db->select($table,$condition,$item,$group);
    }

    public function planGroupSectionByCourseIds($courseIdsArr=array()){
        $db        = self::InitDB("db_course", "query");
        $condition = array();
        $condition = array("t_course_section.status <> -1");
		if(!empty($userId)){
			$condition["fk_user"] = $userId;
		}
		//$condition[]   = "t_course.type <> 2";
		$table = "t_course_section";
		$db = self::InitDB("db_course", "query");
		$item = array('count(pk_section) as section_count','fk_course');
		$courseIds = implode(',',$courseIdsArr);
		$condition[] = "fk_course IN ($courseIds)";
		$group = array('fk_course');
		return $db->select($table,$condition,$item,$group);
    }

    public function getPlan($plan_id)
    {
        $db  = self::InitDB("db_course", "query");
        $key = md5("db_course.plan.".$plan_id);
        $v   = redis_api::get($key);
        if ($v) {
            return $v;
        }
        $item                    = new stdclass;
        $item->plan_id           = "pk_plan";
        $item->user_id           = "fk_user";
        $item->user_plan_id      = "fk_user_plan";
        $item->course_id         = "fk_course";
        $item->video_id 		 = "fk_video";
        $item->section_id        = "fk_section";
        $item->class_id          = "fk_class";
        $item->start_time        = "start_time";
        $item->end_time          = "end_time";
        $item->live_public_type  = "live_public_type";
        $item->video_public_type = "video_public_type";
        $item->video_trial_time  = "video_trial_time";
        $item->status            = "status";
        $v                       = $db->selectOne("t_course_plan", array("pk_plan" => $plan_id), $item);
        redis_api::set($key, $v, 60);

        return $v;
    }

    /**
     * 是否从主库获取
     */
    public function getPlanFromMainDb($plan_id)
    {
        $db                      = self::InitDB("db_course");
        $key                     = md5("db_course.plan.".$plan_id);
        $item                    = new stdclass;
        $item->plan_id           = "pk_plan";
        $item->user_id           = "fk_user";
        $item->user_plan_id      = "fk_user_plan";
        $item->course_id         = "fk_course";
        $item->section_id        = "fk_section";
        $item->class_id          = "fk_class";
        $item->video_id 		 = "fk_video";
        $item->start_time        = "start_time";
        $item->end_time          = "end_time";
        $item->live_public_type  = "live_public_type";
        $item->video_public_type = "video_public_type";
        $item->video_trial_time  = "video_trial_time";
        $item->status            = "status";
        $v                       = $db->selectOne("t_course_plan", array("pk_plan" => $plan_id), $item);
        redis_api::set($key, $v, 60);

        return $v;
    }

    /*
	 *	通过联合索引获取Plan
	 *
	 */
    public function getPlanuni($course_id, $section_id, $class_id)
    {
        //define('DEBUG',true);
        $item               = new stdclass;
        $item->plan_id      = "pk_plan";
        $item->user_id      = "fk_user";
        $item->user_plan_id = "fk_user_plan";
        $item->course_id    = "fk_course";
        $item->section_id   = "fk_section";
        $item->class_id     = "fk_class";
        $item->start_time   = "start_time";
        $item->end_time     = "end_time";
        $item->status       = "status";
        $db                 = self::InitDB("db_course", "query");

        return $db->selectOne("t_course_plan", array("fk_course" => $course_id, "fk_section" => $section_id, "fk_class" => $class_id), $item);
    }


    /*
	 *学生报名
	 */
    public function addRegistration($reg)
    {
        $db = self::InitDB("db_course");
        if (is_array($reg)) {
            $course_id = $reg["fk_course"];
            $class_id  = $reg["fk_class"];
        } else {
            $course_id = $reg->fk_course;
            $class_id  = $reg->fk_class;
        }
        $key_1 = md5("course_db.t_course_user.ct.".$course_id);
        $key_2 = md5("course_db.t_course_user.ct.".$class_id);
        redis_api::del($key_1);
        redis_api::del($key_2);

        $res = $db->insert("t_course_user", $reg);
        if ($res === false) {
            SLog::fatal('db error[%s],params[%s]', var_export($db->error(), 1), var_export($reg, 1));
        }

        return $res;
    }
 
    /**
     * 调班 ,需要修正报名数(原始的课程和班级，修改后的课程和班级)
     */
    public function updateRegClass($course_user_id, $upregdata)
    {
        /*
	$upregdata = array(
		"course_id"=>$params->course_id,
		"class_id"=>$params->class_id,
 */
        $db        = self::InitDB("db_course");
        $condition = array("pk_course_user" => $course_user_id);
        $updata    = array(
            "fk_class"  => $upregdata["class_id"],
            "fk_course" => $upregdata["course_id"],
        );
        $key_1     = md5("course_db.t_course_user.ct.".$upregdata['course_id']);
        $key_2     = md5("course_db.t_course_user.ct.".$upregdata['class_id']);
        $key_3     = md5("course_db.t_course_user.ct.".$upregdata['old_course_id']);
        $key_4     = md5("course_db.t_course_user.ct.".$upregdata['old_class_id']);
        if ($upregdata['course_id'] != $upregdata['old_course_id']) {
            redis_api::del($key_3);
        }
        redis_api::del($key_1);
        redis_api::del($key_2);
        redis_api::del($key_4);

        return $db->update("t_course_user", $condition, $updata);
    }

    public function updateUserClass($condition, $data)
    {
        $db = self::InitDB("db_course");

        return $db->update("t_course_user", $condition, $data);
    }
    /**
     * 获取所有报名用户
     */
    public function listregistrationBycond($course_ids, $class_id = null, $uids = null, $user_owner = null,$page=null,$length=null){
		$condition = array("status > 0");
		if (is_array($course_ids)) {
			if(!empty($course_ids)){
				$condition[] = 'fk_course in ('.implode(',', $course_ids).')';
			}
		}else{
			if(!empty($course_ids)){
				$condition["fk_course"] = $course_ids;
			}
		}
/*
		if ($class_id) {
			$condition["fk_class"] = $class_id;
		}
*/
		if (is_array($class_id)) {
			if(!empty($class_id)){
				$condition[] = 'fk_class in ('.implode(',', $class_id).')';
			}
		}else{
			if(!empty($class_id)){
				$condition["fk_class"] = $class_id;
			}
		}
		if (is_array($uids)) {
			if(!empty($uids)){
				$condition[] = 'fk_user in ('.implode(',', $uids).')';
			}
		}
		if ($user_owner) {
			$condition["fk_user_owner"] = $user_owner;
        }
        if (empty($condition)) return false;
        $item    = array(
            "course_user_id" => "pk_course_user",
            "uid"            => "fk_user",
            "cid"            => "fk_course",
            "class_id"       => "fk_class",
            "user_owner"     => "fk_user_owner",
            "status"         => "status",
            "last_updated"   => "last_updated",
            "create_time"    => "create_time"
        );
        $orderby = array("pk_course_user" => "desc");
        $db      = self::InitDB("db_course", "query");
        if ($page) {
            $db->setpage($page);
        }
        if ($length) {
            $db->setlimit($length);
        }

        return $db->select("t_course_user", $condition, $item, "", $orderby, "");
    }

    /**
     * 获取所有报名用户
     */
    public function listregistration($course_id, $class_id = null, $uid = null, $user_owner = null,$page = null, $length = null)
    {
        $condition = array("status =1 ");
        if ($course_id) {
            $condition["fk_course"] = $course_id;
        }
        if ($class_id) {
            $condition["fk_class"] = $class_id;
        }
        if ($uid) {
            $condition["fk_user"] = $uid;
        }
        if ($user_owner) {
            $condition["fk_user_owner"] = $user_owner;
        }
        if (empty($condition)) return false;
        $item    = array(
            "course_user_id" => "pk_course_user",
            "uid"            => "fk_user",
            "cid"            => "fk_course",
            "class_id"       => "fk_class",
            "user_owner"     => "fk_user_owner",
            "status"         => "status",
            "last_updated"   => "last_updated",
            "create_time"    => "create_time",
			"expire_time"    => "expire_time",
        );
        $orderby = array("pk_course_user" => "desc");
        $db      = self::InitDB("db_course", "query");
        if ($page) {
            $db->setpage($page);
        }
        if ($length) {
            $db->setlimit($length);
        }

        return $db->select("t_course_user", $condition, $item, "", $orderby, "");
    }

    public function delCourseUser($params)
    {

        $db        = self::InitDB("db_course");
        $table     = 't_course_user';
        $uidStr    = implode(',', $params->uidArr);
        $condition = "fk_course = $params->courseId AND fk_class = $params->classId AND fk_user IN ($uidStr)";

        return $db->delete($table, $condition);

    }

    public function getClassUser($classId)
    {
        $key = md5("course_db.t_course_user.".$classId);
        $v   = redis_api::get($key);
        if ($v !== false) {
            return $v;
        }

        $item          = new stdclass;
        $item->user_id = "fk_user";
        $table         = array("t_course_user");
        $condition     = array("fk_class" => $classId, "status = 1");
        $db            = self::InitDB("db_course", "query");
        $v             = $db->select($table, $condition, $item);
        redis_api::set($key, $v, 120);

        return $v;
    }

    public function getCourseUserByFkuser($course_id, $uid)
    {

        $db        = self::InitDB("db_course", "query");
        $table     = 't_course_user';
        $condition = "fk_course=$course_id AND fk_user=$uid AND status = 1";

        return $db->selectOne($table, $condition);

    }
    
    public function getCourseUserByClassAndUids($condition)
    {
        $db        = self::InitDB("db_course", "query");
        $table     = 't_course_user';
        $condition="fk_class=".$condition['fk_class']." AND fk_user IN(".  implode(",", $condition['fk_user']).")";
        return $db->select($table, $condition);
    }

    public function getOnClassUser($classId)
    {
        $key = md5("course_db.t_course_plan_user.".$classId);
        $v   = redis_api::get($key);
        if ($v) {
            return $v;
        }
        $item          = new stdclass;
        $item->user_id = "fk_user";
        $table         = array("t_course_plan_user");
        $condition     = "fk_class=$classId";
        $db            = self::InitDB("db_course", "query");
        $v             = $db->select($table, $condition, $item);
        redis_api::set($key, $v, 60);

        return $v;
    }

    /*
	 * 根据主键获取报名学生的信息
	 */
    public function getRegistrationbyPk($course_user_id = null)
    {
        $condition = array("status = 1");
        if ($course_user_id) {
            $condition["pk_course_user"] = $course_user_id;
        } else {
            return false;
        }
        $item = array(
            "course_user_id" => "pk_course_user",
            "uid"            => "fk_user",
            "cid"            => "fk_course",
            "class_id"       => "fk_class",
            "status"         => "status",
            "last_updated"   => "last_updated",
            "create_time"    => "create_time"
        );
        $db   = self::InitDB("db_course", "query");

        return $db->selectOne("t_course_user", $condition, $item, "", "", "");
    }

    /**
     * 获取课程或者班级的总人数
     */
    public function getRegistrationCountByCourse($course_id)
    {
        $db    = self::InitDB("db_course", "query");
        $key_1 = md5("course_db.t_course_user.ct.".$course_id);
        $v     = redis_api::get($key_1);
        if ($v !== false) {
            return $v;
        }

        $condition              = array("status =1");
        $condition["fk_course"] = $course_id;
        $item                   = array("count(*) as ct");
        $ret                    = $db->selectOne("t_course_user", $condition, $item);
        $v                      = 0;
        if (!empty($ret['ct'])) $v = $ret['ct'];
        redis_api::set($key_1, $v);

        return $v;
    }

    public function getRegistrationCountByClass($class_id)
    {
        $db    = self::InitDB("db_course", "query");
        $key_2 = md5("course_db.t_course_user.ct.".$class_id);
        $v     = redis_api::get($key_2);
        if ($v !== false) {
            return $v;
        }

        $condition             = array("status =1");
        $condition["fk_class"] = $class_id;
        $item                  = array("count(*) as ct");
        $ret                   = $db->selectOne("t_course_user", $condition, $item);
        $v                     = 0;
        if (!empty($ret['ct'])) $v = $ret['ct'];
        redis_api::set($key_2, $v);

        return $v;
    }

    public function addPlanUser($course_id, $class_id, $plan_id, $user_id, $token)
    {
        $key = md5("course_db.t_course_plan_user.".$class_id);
        redis_api::del($key);
        $user_flag = substr($token, 0, 5);
        $item      = array("fk_course" => $course_id, "fk_class" => $class_id, "fk_plan" => $plan_id, "fk_user" => $user_id, "user_token" => $token, "user_flag" => $user_flag);
        $db        = self::InitDB("db_course");

        return $db->insert("t_course_plan_user", $item, true);
    }

    /*
		无status字段
	*/
    public function delPlanUser($plan_id, $user_id)
    {
        $db = self::InitDB("db_course");

        return $db->delete("t_course_plan_user", array("fk_plan" => $plan_id, "fk_user" => $user_id));
    }

    public function delPlanUserByToken($token)
    {
        $db = self::InitDB("db_course");

        return $db->delete("t_course_plan_user", array("user_token" => $token));
    }

    public function listPlanUser($plan_id)
    {
        $item = array("course_id" => "fk_course", "class_id" => "fk_class", "plan_id" => "fk_plan", "user_id" => "fk_user");
        $db   = self::InitDB("db_course", "query");

        return $db->select("t_course_plan_user", array("fk_plan" => $plan_id), $item);
    }

    public function getPlanUserByClassId($class_id, $user_id)
    {
        $key = md5("course_db.getPlanUserByClassId.".$class_id.$user_id);
        $v   = redis_api::get($key);
        if ($v !== false) {
            return $v;
        }
        $db = self::InitDB("db_course", "query");
        $v  = $db->selectOne("t_course_plan_user", array("fk_class" => $class_id, "fk_user" => $user_id));
        if (!$v) $v = 0;
        redis_api::set($key, $v, 60);

        return $v;
    }

    public function getPlanUserByPlanId($plan_id, $user_id, $token)
    {
        $db = self::InitDB("db_course", "query");

        return $db->selectOne("t_course_plan_user", array("fk_plan" => $plan_id, "fk_user" => $user_id, "user_flag" => $token));
    }

    public function getUnstartPlan($hours)
    {
        $item         = new stdclass;
        $item->id     = "pk_plan";
        $item->class  = "fk_class";
        $item->course = "fk_course";
        $item->t      = "unix_timestamp(start_time)";
        $table        = array("t_course_plan");
        $condition    = "status=1 and start_time>now() and start_time<date_add(now(), interval $hours hour)";
        $db           = self::InitDB("db_course", "query");

        return $db->select($table, $condition, $item, "", "start_time");
    }

    public function getCourseList($user, $limit = 0, $page = 1)
    {
        $table            = array("t_course");
        $item             = new stdclass;
        $item->course_id  = "pk_course";
        $item->user_total = "user_total";
        $condition        = "fk_user=$user";
        $orderby          = "pk_course";
        $db               = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getCourseUser($course_id, $limit = 0, $page = 1)
    {
        $table         = array("t_course_user");
        $item          = new stdclass;
        $item->user_id = "fk_user";
        $condition     = array("fk_course" => $course_id, "status =1");
        $orderby       = "pk_course_user";
        $db            = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getOrgClass($user, $limit = 0, $page = 1)
    {
        $table          = array("t_course_class");
        $item           = new stdclass;
        $item->class_id = "pk_class";
        $condition      = "fk_user=$user";
        $orderby        = "pk_class";
        $db             = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getTeacherClass($user, $limit = 0, $page = 1)
    {
        $table           = array("t_course_plan");
        $item            = new stdclass;
        $item->plan_id   = "pk_plan";
        $item->course_id = "fk_course";
        $item->class_id  = "fk_class";
        $condition       = "fk_user_plan=$user";
        $orderby         = "pk_plan";
        $db              = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }


    public function getUserClass($user, $limit = 0, $page = 1)
    {
        $table                = array("t_course_user");
        $item                 = new stdclass;
        $item->course_user_id = "pk_course_user";
        $item->course_id      = "fk_course";
        $condition            = array("fk_user" => $user, "status =1");
        $orderby              = "pk_course_user";
        $db                   = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function addDiscount($data)
    {
        $table = array("t_discount");
        $db    = self::InitDB("db_course");

        return $db->insert($table, $data);
    }

    public function getDiscountsByOrg($user_id, $limit, $page)
    {
        $table                = array("t_discount");
        $item                 = new stdclass;
        $item->discount_id    = "pk_discount";
        $item->name           = "name";
        $item->introduction   = "introduction";
        $item->status         = "status";
        $item->owner          = "owner";
        $item->org_id         = "fk_org";
        $item->course_id      = "fk_course";
        $item->discount_type  = "discount_type";
        $item->discount_value = "discount_value";
        $item->min_fee        = "min_fee";
        $item->starttime      = "starttime";
        $item->endtime        = "endtime";
        $item->createtime     = "createtime";
        $condition            = "fk_org=$user_id";
        $orderby              = "pk_discount desc";
        $db                   = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function forbidDiscount($user_id, $discount_id)
    {
        $table     = array("t_discount");
        $condition = "pk_discount=$discount_id and owner=$user_id";
        $item      = array("status" => -1);
        $db        = self::InitDB("db_course");

        return $db->update($table, $condition, $item);
    }

    public function recoverDiscount($user_id, $discount_id)
    {
        $table     = array("t_discount");
        $condition = "pk_discount=$discount_id and owner=$user_id";
        $item      = array("status" => 0);
        $db        = self::InitDB("db_course");

        return $db->update($table, $condition, $item);
    }

    public function forbidDiscountCode($user_id, $discount_code_id)
    {
        $table     = array("t_discount_code");
        $condition = "pk_discount_code=$discount_code_id and owner=$user_id";
        $item      = array("status" => -1);
        $db        = self::InitDB("db_course");

        return $db->update($table, $condition, $item);
    }

    public function recoverDiscountCode($user_id, $discount_code_id)
    {
        $table     = array("t_discount_code");
        $condition = "pk_discount_code=$discount_code_id and owner=$user_id";
        $item      = array("status" => 0);
        $db        = self::InitDB("db_course");

        return $db->update($table, $condition, $item);
    }

    public function getDiscountById($discount_id)
    {
        $table                = array("t_discount");
        $item                 = new stdclass;
        $item->discount_id    = "pk_discount";
        $item->name           = "name";
        $item->introduction   = "introduction";
        $item->owner          = "owner";
        $item->org_id         = "fk_org";
        $item->course_id      = "fk_course";
        $item->discount_type  = "discount_type";
        $item->discount_value = "discount_value";
        $item->min_fee        = "min_fee";
        $item->starttime      = "starttime";
        $item->endtime        = "endtime";
        $item->createtime     = "createtime";
        $item->status         = "status";
		$item->user_limit     = "user_limit";
        $condition            = "pk_discount=$discount_id";
        $db                   = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $item);
    }

    public function getDiscountCodesByDiscountId($discount_id, $limit, $page)
    {
        $table                  = array("t_discount_code");
        $item                   = new stdclass;
        $item->discount_code_id = "pk_discount_code";
        $item->introduction     = "introduction";
        $item->status           = "status";
        $item->owner            = "owner";
        $item->discount_id      = "fk_discount";
        $item->discount_code    = "discount_code";
        $item->total_num        = "total_num";
        $item->used_num         = "used_num";
        $item->user_limit       = "user_limit";
        $item->starttime        = "starttime";
        $item->endtime          = "endtime";
        $item->createtime       = "createtime";
        $condition              = "fk_discount=$discount_id";
        $orderby                = "pk_discount_code desc";
        $db                     = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function addDiscountCode($data)
    {
        $table = array("t_discount_code");
        $db    = self::InitDB("db_course");

        return $db->insert($table, $data);
    }

    public function getDiscountCodeById($discount_code_id)
    {
        $table                  = array("t_discount_code");
        $item                   = new stdclass;
        $item->discount_code_id = "pk_discount_code";
        $item->introduction     = "introduction";
        $item->status           = "status";
        $item->owner            = "owner";
        $item->discount_id      = "fk_discount";
        $item->discount_code    = "discount_code";
        $item->total_num        = "total_num";
        $item->used_num         = "used_num";
        $item->user_limit       = "user_limit";
        $item->starttime        = "starttime";
        $item->endtime          = "endtime";
        $item->createtime       = "createtime";
        $condition              = "pk_discount_code=$discount_code_id";
        $db                     = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $item);
    }

    public function getDiscountCodeByCode($discount_code)
    {
        $table                  = array("t_discount_code");
        $item                   = new stdclass;
        $item->discount_code_id = "pk_discount_code";
        $item->introduction     = "introduction";
        $item->status           = "status";
        $item->owner            = "owner";
        $item->discount_id      = "fk_discount";
        $item->discount_code    = "discount_code";
        $item->total_num        = "total_num";
        $item->used_num         = "used_num";
        $item->user_limit       = "user_limit";
        $item->starttime        = "starttime";
        $item->endtime          = "endtime";
        $item->createtime       = "createtime";
        $condition              = "discount_code = '$discount_code'";
        $db                     = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $item);
    }

    public function  getDiscountCodeUsedsByCodeId($discount_code_id, $limit, $page)
    {
        $table                  = array("t_discount_code_used");
        $item                   = new stdclass;
        $item->order_id         = "fk_order";
        $item->discount_code_id = "fk_discount_code";
        $item->user_id          = "fk_user";
        $item->status           = "status";
        $item->createtime       = "createtime";
        $condition              = "fk_discount_code=$discount_code_id and status!=2";
        $orderby                = array("fk_order" => "desc");
        $db                     = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getDiscountCodeUsedsAllByCodeId($discount_code_id, $limit, $page)
    {
        $table                  = array("t_discount_code_used");
        $item                   = new stdclass;
        $item->order_id         = "fk_order";
        $item->discount_code_id = "fk_discount_code";
        $item->user_id          = "fk_user";
        $item->status           = "status";
        $item->createtime       = "createtime";
        $condition              = "fk_discount_code=$discount_code_id";
        $orderby                = array("fk_order" => "desc");
        $db                     = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getDiscountCodeUsedByOrderId($order_id)
    {
        $table                  = array("t_discount_code_used");
        $item                   = new stdclass;
        $item->order_id         = "fk_order";
        $item->discount_code_id = "fk_discount_code";
        $item->user_id          = "fk_user";
        $item->status           = "status";
        $item->createtime       = "createtime";
        $condition              = "fk_order=$order_id";
        $db                     = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $item);
    }

    public function getDiscountCodeUsedByOrderIdOk($order_id)
    {
        $table                  = array("t_discount_code_used");
        $item                   = new stdclass;
        $item->order_id         = "fk_order";
        $item->discount_code_id = "fk_discount_code";
        $item->user_id          = "fk_user";
        $item->status           = "status";
        $item->createtime       = "createtime";
        $condition              = "fk_order=$order_id and status!=2";
        $db                     = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $item);
    }

    public function getDiscountCodeUsedsByCodeIdUserId($discount_code_id, $user_id, $limit, $page)
    {
        $table            = array("t_discount_code_used");
        $item             = new stdclass;
        $item->order_id   = "fk_order";
        $item->status     = "status";
        $item->createtime = "createtime";
        $condition        = "fk_discount_code=$discount_code_id and fk_user=$user_id";
        $orderby          = array("fk_order" => "desc");
        $db               = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

	/*
	 * 通过用户id查找用户优惠券
	 * @params $user_id 用户id $limit $page
	 */
	public function getDiscountCodeByUserId($user_id, $limit, $page)
    {
        $table               = array("t_discount_code_used");
        $item                = new stdclass;
		$item->discount_code = "fk_discount_code";
        $item->order_id      = "fk_order";
        $item->status        = "status";
        $item->createtime    = "createtime";
        $condition           = "fk_user=$user_id";
        $orderby             = array("fk_order" => "desc");
        $db                  = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);
        return $db->select($table, $condition, $item, "", $orderby);
    }

	public function getListDiscountByIds($disCountCodeIds, $limit, $page, $code, $owner)
    {
        $table             = array("t_discount_code");
        $item              = new stdclass;
		$item->owner       = "owner";
		$item->fk_discount = "fk_discount";
		$item->discount_code= "discount_code";
		$item->total_num   = "total_num";
		$item->used_num    = "used_num";
		$item->user_limit  = "user_limit";
		$item->createtime  = "createtime";
		$item->starttime   = "starttime";
		$item->endtime     = "endtime";
		$item->status      = "status";
		$condition         = "pk_discount_code IN ($disCountCodeIds)";

		if(!empty($code))
		{
			$condition.= " and discount_code='{$code}'";
		}
		if(!empty($owner))
		{
			$condition.= " and owner={$owner}";
		}

        $orderby           = array('endtime'=>'desc');
        $db                = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);
        return $db->select($table, $condition, $item, "", $orderby);
    }

	public function getDiscountByIds($discountIds, $limit, $page)
    {
        $table               = array("t_discount");
        $item                = new stdclass;
		$item->name          = "name";
		$item->pk_discount   = "pk_discount";
		$item->fk_course     = "fk_course";
		$item->discount_type = "discount_type";
		$item->discount_value= "discount_value";
		$item->min_fee       = "min_fee";
		$item->user_limit    = "user_limit";
		$item->status        = "status";
        $condition           = "pk_discount IN ($discountIds)";
        $db                  = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);
        return $db->select($table, $condition, $item);
    }


    public function getDiscountCodeUsedsByCodeIdUserIdOk($discount_code_id, $user_id, $limit, $page)
    {
        $table          = array("t_discount_code_used");
        $item           = new stdclass;
        $item->order_id = "fk_order";
        $condition      = "fk_discount_code=$discount_code_id and fk_user=$user_id and status!=2";
        $orderby        = array("fk_order" => "desc");
        $db             = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getDiscountCodeUsedsByUserId($user_id, $statuses, $limit, $page)
    {
        $table                  = array("t_discount_code_used");
        $item                   = new stdclass;
        $item->order_id         = "fk_order";
        $item->discount_code_id = "fk_discount_code";
        $item->status           = "status";
        $item->createtime       = "createtime";
        if (0 == count($statuses)) {
            $extra_condition = "";
        } else if (1 == count($statuses)) {
            $extra_condition = " and status=".$statuses[0];
        } else {
            $extra_condition = " and status in (".implode(", ", $statuses).")";
        }
        $condition = "fk_user=$user_id ".$extra_condition;
        $orderby   = array("fk_order" => "desc");
        $db        = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function updateUsedNumForDiscountCodeById($discount_code_id, $num)
    {
        $table     = array("t_discount_code");
        $condition = "pk_discount_code=$discount_code_id";
        $item      = array("used_num=used_num+$num");
        $db        = self::InitDB("db_course");

        $res =  $db->update($table, $condition, $item);
        if ($res === false) {
            SLog::fatal('updateUsedNumForDiscountCodeById failed,params[%s]', var_export([$discount_code_id, $num], 1));
        }

        return $res;
    }

    public function setUsedNumForDiscountCodeById($discount_code_id, $num)
    {
        $table     = array("t_discount_code");
        $condition = "pk_discount_code=$discount_code_id";
        $item      = array("used_num" => $num);
        $db        = self::InitDB("db_course");

        $res = $db->update($table, $condition, $item);
        if ($res === false) {
            SLog::fatal('setUsedNumForDiscountCodeById failed,params[%s]', var_export([$discount_code_id, $num], 1));
        }

        return $res;
    }

    public function addDiscountCodeUsed($data)
    {
        $table = array("t_discount_code_used");
        $db    = self::InitDB("db_course");

        return $db->insert($table, $data);
    }

    public function setStatusForDiscountCodeUsedByOrderId($order_id, $status)
    {
        $table     = array("t_discount_code_used");
        $condition = "fk_order=$order_id";
        $item      = array("status" => $status);
        $db        = self::InitDB("db_course");

        return $db->update($table, $condition, $item);
    }

    public function updateDiscountCodeForUsed($order_id, $discount_code_id)
    {
        $table     = array("t_discount_code_used");
        $condition = "fk_order=$order_id and status=0";
        $item      = array("fk_discount_code" => $discount_code_id);
        $db        = self::InitDB("db_course");

        return $db->update($table, $condition, $item);
    }


    public function getCoursesByOrg($user_id)
    {
        $table           = array("t_course");
        $item            = new stdclass;
        $item->course_id = "pk_course";
        $item->title     = "title";
        $item->descript  = "descript";
        $condition       = "fk_user=$user_id";
        $db              = self::InitDB("db_course", "query");
        $ret             = $db->select($table, $condition, $item);

        return $ret;
    }

    public function getFeeCoursesByOrg($user_id)
    {
        $table           = array("t_course");
        $item            = new stdclass;
        $item->course_id = "pk_course";
        $item->title     = "title";
		$item->price     = "price";
        $item->descript  = "descript";
        $condition       = "fk_user=$user_id and fee_type!=0";
        $db              = self::InitDB("db_course", "query");
        $ret             = $db->select($table, $condition, $item);

        return $ret;
    }

    public function deleteUsedByOrderId($order_id)
    {
        $table     = array("t_discount_code_used");
        $condition = "fk_order=$order_id";
        $db        = self::InitDB("db_course");

        return $db->delete($table, $condition);
    }

    public function countStudent($cids)
    {
        $table     = array("t_course_user");
        $condition = array(
            'fk_course in ('.$cids.')',
        );
        $items     = array(
            'fk_course',
            'count(*) as num',
        );
        $group     = array('fk_course');
        $db        = self::InitDB("db_course", "query");
        $ret       = $db->select($table, $condition, $items, $group, "", "");

        return $ret;
    }

    public function getStudentsByCid($cid)
    {
        $table     = array("t_course_user");
        $condition = array(
            'fk_course' => $cid,
        );
        $items     = array(
            'fk_user',
        );
        $db        = self::InitDB("db_course", "query");
        $ret       = $db->select($table, $condition, $items, "", "", "");

        return $ret;
    }

    public function addCourseTop($course_id)
    {
        //define('DEBUG',true);
        $db  = self::initdb();
        $key = md5("course_db.getCourse.".$course_id);
        $v   = redis_api::del($key);
        $key = "course_api::getcourselist.{$course_id}.v2";
        $v   = redis_api::del($key);
        $sql = 'update t_course set top=(select max(top) from (select * from t_course where pk_course='.$course_id.') as a)+1 where pk_course='.$course_id;

        return $db->execute($sql);

    }

    public function delCourseTop($course_id)
    {
        $table = array("t_course");
        $db    = self::initdb();
        $key   = md5("course_db.getCourse.".$course_id);
        $v     = redis_api::del($key);
        $key   = "course_api::getcourselist.{$course_id}.v2";
        $v     = redis_api::del($key);

        return $db->update($table, array('pk_course' => $course_id), array('top' => 0));

    }

    public function getPlansByClassId($class_id)
    {
        $db  = self::InitDB("db_course", "query");
        $item          = new stdclass;
        $item->plan_id = "pk_plan";
        $item->status  = "status";
		$item->start_time = "start_time";
		$item->end_time   = "end_time";
        $condition     = "fk_class=$class_id AND status <> -1";
		$orderBy       = array('pk_plan'=>'asc');
        $data          = $db->select("t_course_plan", $condition, $item,'',$orderBy);
        if (!empty($data->items)) {
            return $data->items;
        }

        return false;
    }

    public function getPlansByCourseIDs($courseIds)
    {
        $db  = self::InitDB("db_course", "query");
        $item      = array(
            't_course_plan.fk_course', 't_course_plan.pk_plan', 't_course_plan.fk_user_plan', 't_course_plan.status', 't_course_plan.start_time',
            't_course_class.name as class_name', 't_course_class.max_user', 't_course_class.min_user', 't_course_class.user_total',
            't_course_section.name as section_name',
        );
        $condition = "t_course_plan.fk_course in (".implode(',', $courseIds).")";
        $orderby   = array('' => '');
        $left      = array(
            't_course_class'   => 't_course_class.pk_class=t_course_plan.fk_class',
            't_course_section' => 't_course_section.pk_section=t_course_plan.fk_section',
        );
        $data      = $db->select("t_course_plan", $condition, $item, "", "", $left);
        if (!empty($data->items)) {
            return $data->items;
        }

        return false;
    }

    public function countPlanByOwner($org_owner, $params)
    {
        //define('DEBUG',true);
        $table     = array("t_course_plan");
        $condition = 'fk_user='.$org_owner;
        if (!empty($params->status)) {
            $condition .= ' AND status='.$params->status;
        }else{
            $condition .= ' AND status in (1,2,3)';
        }
        if (!empty($params->start_time) && !empty($params->end_time)) {
            $condition .= ' AND start_time>\''.$params->start_time.'\' AND start_time<\''.$params->end_time.'\'';
        }
        $items = array(
            'count(pk_plan) as count',
        );
        $db  = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $items);
    }

    public function countStudentByOwner($org_owner, $params)
    {
        $table     = array("t_course_user");
        $condition = 'fk_user_owner='.$org_owner;
        if (!empty($params->status)) {
            $condition .= ' AND status='.$params->status;
        }
        if (!empty($params->start_time) && !empty($params->end_time)) {
            $condition .= ' AND create_time>\''.$params->start_time.'\' AND create_time<\''.$params->end_time.'\'';
        }
        $items = array(
            'count(distinct fk_user) as count',
        );
        $db  = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $items);
    }

    public function getPlanListByOwner($org_owner, $params)
    {
        //define('DEBUG',true);
        $table     = array("t_course_plan");
        $condition = 't_course_plan.fk_user='.$org_owner;
        if (!empty($params->status)) {
            $condition .= ' AND t_course_plan.status='.$params->status;
        }else{
            $condition .= ' AND t_course_plan.status<>-1';
        }
        if (!empty($params->start_time) && !empty($params->end_time)) {
            $condition .= ' AND t_course_plan.start_time>\''.$params->start_time.'\' AND t_course_plan.start_time<\''.$params->end_time.'\'';
        }
        $items = array("t_course_plan.*", "t_course_section.name as section_name",
				"t_course_class.name as class_name","t_course.title","t_course.thumb_big","t_course.thumb_med");
        $left                   = new stdclass;
        $left->t_course_section = "t_course_section.pk_section=t_course_plan.fk_section";
        $left->t_course_class   = "t_course_class.pk_class=t_course_plan.fk_class";
		$left->t_course         = "t_course.pk_course=t_course_plan.fk_course";
        $db  = self::InitDB("db_course", "query");

        return $db->select($table, $condition, $items, '', '', $left);
        $orderby = array("t_course_plan.pk_plan" => "asc");
    }

    public static function listCourseUser($condition, $page = 1, $length = 100, $item = '', $orderBy = '', $groupBy = '')
	{
        $table = array("t_course_user");
        $db    = self::InitDB('db_course', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, $groupBy, $orderBy);
    }

    public function getCourseUserTotal($orgOwner)
    {
        $db  = self::InitDB('db_course', 'query');
        $sql = "select count(*) as totalNum from (select `fk_user` from `t_course_user` where `fk_user_owner`={$orgOwner} AND status > 0 group by `fk_user`) as b";

        return $db->execute($sql);
    }

    public function getCourseByCids($courseIds,$orgUserId=0)
    {
        //define('DEBUG',true);
        $table              = array("t_course");
        $condition          = array(
            'pk_course in ('.implode(',', $courseIds).')',
        );
		if($orgUserId>0) $condition['fk_user'] = $orgUserId;
        $items              = new stdclass;
        $items->course_id   = "pk_course";
        $items->course_name = "title";
        $items->owner_id   = "fk_user";
        $items->course_type = "type";
        $items->fk_cate = "fk_cate";
        $items->fk_grade = "fk_grade";
        $items->thumb_big = "thumb_big";
        $items->thumb_med = "thumb_med";
        $items->thumb_small= "thumb_small";
		$items->user_total= "user_total";
        $items->stasus= "status";
        $items->status= "status";
        $items->admin_status= "admin_status";
	$items->start_time  = "start_time";
        $items->end_time    = "end_time";
        $db                 = self::InitDB('db_course', 'query');

        return $db->select($table, $condition, $items, '', '', '');
    }

    public function countStudentByClassIds($cids)
    {
        //define('DEBUG',true);
        $table     = array("t_course_user");
        $condition = array(
            'fk_class in ('.implode(',', $cids).')',
        );
        $items     = array(
            'count(*) as num',
        );
        $db        = self::InitDB("db_course", "query");
        $ret       = $db->selectOne($table, $condition, $items, "", "", "");

        return $ret;
    }

    public function addCoursePlanExam($data)
    {
        $db = self::InitDB();

        return $db->insert("t_course_plan_exam", $data);
    }

    public function coursePlanExamList($data = array(), $page, $length, $item = '', $orderby = array(), $groupby = '')
    {
        $db                   = self::InitDB("db_course", "query");
        $table                = "t_course_plan_exam";
        $items                = new stdclass;
        $items->plan_exam_id  = "pk_plan_exam";
        $items->plan_id       = "fk_plan";
        $items->question_id   = "fk_question";
        $items->type          = "type";
        $items->q_desc        = "q_desc";
        $items->q_desc_img    = "q_desc_img";
        $items->a             = "a";
        $items->b             = "b";
        $items->c             = "c";
        $items->d             = "d";
        $items->e             = "e";
        $items->answer_a_id   = "fk_answer_a";
        $items->answer_b_id   = "fk_answer_b";
        $items->answer_c_id   = "fk_answer_c";
        $items->answer_d_id   = "fk_answer_d";
        $items->answer_e_id   = "fk_answer_e";
        $items->answer        = "answer";
        $items->order_no      = "order_no";
        $items->status        = "status";
        $condition            = array();
        $condition            = array("status <> -1");
        $condition["fk_plan"] = $data["plan_id"];
        $orderBy              = array();
        if (empty($orderby)) {
            $orderBy = array("pk_plan_exam" => "asc");
        } else {
            if (isset($orderby["order_no"])) {
                $orderBy["order_no"] = $orderby["order_no"];
            }
        }
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $items, $groupby, $orderBy, "");
    }

    public function delCoursePlanExam($examids)
    {
        $db        = self::InitDB("db_course");
        $table     = "t_course_plan_exam";
        $condition = array("pk_plan_exam in ( $examids)");
        $data      = array("status" => "-1");

        return $db->update($table, $condition, $data);
    }

    public function updateCoursePlanExam($examid, $data)
    {
        $db        = self::InitDB("db_course");
        $table     = "t_course_plan_exam";
        $condition = array("pk_plan_exam" => $examid);

        return $db->update($table, $condition, $data);
    }

    public function addDiscountV2($data)
    {
        $table = array("t_discount");
        $db    = self::InitDB("db_course");

        return $db->insert($table, $data);
    }

    public function getDiscountsByOrgV2($user_id, $limit, $page)
    {
        $table             = array("t_discount");
        $item              = new stdclass;
        $item->discount_id = "pk_discount";
        $item->name        = "name";
        //$item->introduction = "introduction";
        $item->status         = "status";
        $item->owner          = "owner";
        $item->org_id         = "fk_org";
        $item->course_id      = "fk_course";
        $item->discount_type  = "discount_type";
        $item->discount_value = "discount_value";
        $item->min_fee        = "min_fee";
        $item->total_num      = "total_num";
        $item->user_limit     = "user_limit";
        $item->duration       = "duration";
        //$item->starttime = "starttime";
        //$item->endtime = "endtime";
        $item->createtime = "createtime";
        $condition        = "fk_org=$user_id";
        $orderby          = "pk_discount desc";
        $db               = self::InitDB("db_course", "query");
        $db->setLimit($limit);
        $db->setPage($page);
        $db->setCount(true);

        return $db->select($table, $condition, $item, "", $orderby);
    }

    public function getDiscountByIdV2($discount_id)
    {
        $table                = array("t_discount");
        $item                 = new stdclass;
        $item->discount_id    = "pk_discount";
        $item->name           = "name";
        $item->introduction   = "introduction";
        $item->owner          = "owner";
        $item->org_id         = "fk_org";
        $item->course_id      = "fk_course";
        $item->discount_type  = "discount_type";
        $item->discount_value = "discount_value";
        $item->min_fee        = "min_fee";
        $item->total_num      = "total_num";
        $item->user_limit     = "user_limit";
        $item->duration       = "duration";
        $item->createtime     = "createtime";
        $item->status         = "status";
        $condition            = "pk_discount=$discount_id";
        $db                   = self::InitDB("db_course", "query");

        return $db->selectOne($table, $condition, $item);
    }

    public function getClassUserList($condition, $page, $length, $item, $orderBy, $groupBy)
    {
        $db    = self::InitDB('db_course');
        $table = 't_course_user';
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, $groupBy, $orderBy);
    }

    public function addPlanAttach($data = array())
    {
        $db = self::InitDB("db_course");

        return $db->insert("t_course_plan_attach", $data);
    }

    /*	public function delPlanAttach($planAttId){
        $db = self::InitDB("db_course");
		$condition = array("pk_plan_attach"=>$planAttId);
		return $db->update("t_course_plan_attach",$condition,array("status"=>"-1"));
	}
*/
    public function delPlanAttach($planAttIds)
    {
        $db        = self::InitDB("db_course");
        $table     = "t_course_plan_attach";
        $condition = array("pk_plan_attach in ( $planAttIds)");
        $data      = array("status" => "-1");

        return $db->update($table, $condition, $data);
    }

    /*
	 *获取单个章节
	 */
    public function getPlanAttach($planAttId)
    {
        $db                          = self::InitDB("db_course", "query");
        $item                        = array(
            "planattid" => "pk_plan_attach",
            "plan_id"   => "fk_plan",
            "title"     => "title",
            "attach"    => "atttach",
            "order_no"  => "order_no",
            "type"      => "type",
            "thumb"     => "thumb",
            "status"    => "status",
        );
        $condition                   = array("status <> -1");
        $condition["pk_plan_attach"] = $planAttId;

        return $db->selectOne("t_course_plan_attach", $condition, $item);
    }

    public function listPlanAttach($cond, $page = 1, $length = 100, $orderBy = 'ASC', $groupBy = '')
    {
        $db                   = self::InitDB("db_course", "query");
        $table                = array("t_course_plan_attach");
        $item                 = array(
            "planattid" => "pk_plan_attach",
            "plan_id"   => "fk_plan",
            "class_id"   => "fk_class",
            "title"     => "title",
            "attach"    => "atttach",
            "order_no"  => "order_no",
            "type"      => "type",
            "thumb"     => "thumb",
            "fk_user"   => "fk_user",
            "status"    => "status",
            "create_time" => "create_time",
        );
        $condition            = array("status <> -1");
        //$condition["fk_plan"] = $cond["plan_id"];
        $condition["fk_class"] = $cond["plan_id"];
        $orderby              = array();
        $orderby              = array("pk_plan_attach" => "asc");
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, $groupBy, $orderby);
    }

    public function updatePlanAttach($planAttId, $data)
    {
        $table                       = array("t_course_plan_attach");
        $db                          = self::InitDB("db_course");
        $condition["pk_plan_attach"] = $planAttId;

        return $db->update($table, $condition, $data);
    }

	public function getPlanAttachByPidArr($pid_arr){

        $db      = self::InitDB("db_course");
        $table   = array("t_course_plan_attach");
		$pid_str = implode(',',$pid_arr);
		$condition = "fk_plan in ($pid_str) and status <> -1";
        $orderby = array("create_time" => "desc");
        return $db->select($table, $condition, '', '', $orderby);
	}

    public function getTopCourseByOwner($uid)
    {
        //define('DEBUG',TRUE);
        $table                     = array("t_course");
        $db                        = self::InitDB("db_course");
        $condition                 = array("top>0");
        $condition["fk_user"]      = $uid;
        $condition["admin_status"] = 1;
        $items                     = array('title', 'pk_course', 'thumb_small', 'top');
        $orderby                   = array("top" => "asc");

        return $db->select($table, $condition, $items, '', $orderby);
    }
     public function getMgrCourseByInfo($pk_course){
        $table                     = array("t_course");
        $db                        = self::InitDB("db_course");
        $condition["pk_course"]      = $pk_course;
        $items                     = array('title', 'pk_course', 'thumb_small');
        return $db->select($table, $condition, $items, '', '');
    }
    public function delHistoryTopCourse($uid, $top)
    {
        //define('DEBUG',TRUE);
        $db = self::InitDB("db_course");

        return $db->update("t_course", array("fk_user" => $uid, 'top' => $top), array('top' => 0));
    }

    public function updatePlanExamStatus($plan_exam_id, $plan_id, $status)
    {
        $db        = self::InitDB("db_course");
        $table     = array("t_course_plan_exam");
        $condition = "pk_plan_exam=$plan_exam_id and fk_plan=$plan_id";
        $data      = array("status" => $status);

        return $db->update($table, $condition, $data);
    }

    public function getPlanExamsByPlan($plan_id, $limit = 0, $page = 1)
    {
        $table              = array("t_course_plan_exam");
        $item               = new stdclass;
        $item->plan_exam_id = "pk_plan_exam";
        $item->plan_id      = "fk_plan";
        $item->question_id  = "fk_question";
        $item->type         = "type";
        $item->q_desc       = "q_desc";
        $item->q_desc_img   = "q_desc_img";
        $item->a            = "a";
        $item->answer_a_id  = "fk_answer_a";
        $item->b            = "b";
        $item->answer_b_id  = "fk_answer_b";
        $item->c            = "c";
        $item->answer_c_id  = "fk_answer_c";
        $item->d            = "d";
        $item->answer_d_id  = "fk_answer_d";
        $item->e            = "e";
        $item->answer_e_id  = "fk_answer_e";
        $item->answer       = "answer";
        $item->order_no     = "order_no";
        $item->status       = "status";
        $item->createtime   = "create_time";
        $item->lastupdated  = "last_updated";
        $condition          = "fk_plan=$plan_id and status<>-1";
        $orderby            = "pk_plan_exam";
        $db                 = self::InitDB("db_course", "query");
        if ($limit > 0) {
            $db->setLimit($limit);
            $db->setPage($page);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, "", $orderby);
    }


	public static function getTeacherClassByUidArr($fk_user_class_str){
        $db = self::InitDB("db_course",'query');
		$left = array('t_course'=> 't_course_class.fk_course = t_course.pk_course');
		$table = "t_course_class";
		$condition = "t_course_class.fk_user_class IN ($fk_user_class_str) AND t_course_class.status <> -1 AND t_course.admin_status = 1";
		$items = array(
			'pk_class'=>'t_course_class.pk_class',
			'fk_user'=> 't_course_class.fk_user',
			'fk_course'=> 't_course_class.fk_course',
			'user_total'=> 't_course_class.user_total',
			'fk_user_class'=> 't_course_class.fk_user_class',
			'admin_status' => 't_course.admin_status',
		);
		return $db->select($table,$condition,$items,'', '',$left);
	}

	public static function getOrgCourseCount($uid_arr){
		$table = "t_course";
        $db = self::InitDB("db_course", "query");
		$item=array('count(pk_course) as course_count','fk_user');
		$uid_str = implode(',',$uid_arr);
		$condition = "fk_user IN ($uid_str) AND admin_status <>-1";
		$group = array('fk_user');
		return $db->select($table,$condition,$item,$group);
	}

    public static function getSeekOrgCourseCount($uid_arr){
        $table = "t_course";
        $db = self::InitDB("db_course", "query");
        $item=array('count(pk_course) as course_count','fk_user');
        $uid_str = implode(',',$uid_arr);
        $condition = "fk_user IN ($uid_str) AND admin_status=1 AND status<>-1";
        $group = array('fk_user');
        return $db->select($table,$condition,$item,$group);
    }

	public static function getClassByTidAndCourseId($teacher_id,$course_id,$owner_id=0){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_class';
		if(!empty($owner_id)){
			$condition = "fk_user_class = $teacher_id and fk_course = $course_id AND status <> -1 AND fk_user = $owner_id";
		}else{
			$condition = "fk_user_class = $teacher_id and fk_course = $course_id AND status <> -1";
		}
		return $db->selectOne($table,$condition);
	}

	public static function getPlanByTidAndCourseId($teacher_id,$course_id,$owner_id=0){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan';
		if(!empty($owner_id)){
			$condition = "fk_user_plan = $teacher_id and fk_course = $course_id and fk_user = $owner_id and status<>-1";
		}else{
			$condition = "fk_user_plan = $teacher_id and fk_course = $course_id and status <> -1";
		}
		return $db->selectOne($table,$condition);
	}

	public static function getPlanTeacherByCourseId($course_id){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan';
		$condition = "fk_course = $course_id and status <> -1";
		$group = array('fk_user_plan');
		$item  = "fk_user_plan";
		return $db->select($table,$condition,$item,$group);
	}

	public function getPlanTeacherByClassId($class_id){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan';
		$condition = "fk_class = $class_id and status <> -1";
		$group = array('fk_user_plan');
		return $db->select($table,$condition,'',$group);
	}

	public function getPlanTeacherByCidAndSid($course_id,$section_id){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan';
		$condition = "fk_course = $course_id and fk_section = $section_id and status <> -1";
		$group = array('fk_user_plan');
		return $db->select($table,$condition,'',$group);
	}
	/**
	 * 获取plan的状态
	 */
	public function getPlanStatus($plan_id){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan';
		if(is_numeric($plan_id)){
			$condition=array("pk_plan"=>$plan_id);
		}elseif(is_array($plan_id)){
			$condition="pk_plan in (".implode(",",$plan_id).")";
		}else{
			return false;
		}
		$left = new stdclass;
		$left->t_course = "t_course.pk_course=fk_course";
		$left->t_course_section = "t_course_section.pk_section=fk_section";
		$item=new stdclass;
		$item->plan_id="pk_plan";
		$item->plan_status="t_course_plan.status";
		$item->course_status="t_course.status";
		$item->section_status="t_course_section.status";
		return $db->select($table,$condition,$item,"","",$left);
	}


	public function checkUserRegisterCourse($course_id,$class_id,$uid,$owner_id){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_user';
		$condition = array();
		if(!empty($owner_id)){
			$condition['fk_user_owner'] = $owner_id;
		}
		$condition['fk_course'] = $course_id;
		$condition['fk_class'] = $class_id;
		$condition['fk_user'] = $uid;
		return $db->selectOne($table,$condition);
	}


	public function getPlanQuestionCountByPidArr($pid_arr){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan_exam';
		$pid_str = implode(',', $pid_arr);
		$condition = "fk_plan in ($pid_str)";
		$item = array('count(fk_plan) as count');
		return $db->select($table,$condition,$item);
	}

	public function getExamByPid($pid){
        $db = self::InitDB("db_course", "query");
		$table = 't_course_plan_exam';
		$condition = "fk_plan = $pid and status =1";
		return $db->select($table,$condition,'','','create_time DESC');
	}

	public static function getBlockCourseOrderByInfo($condi){

        $db = self::InitDB("db_course", "query");
		$table = 't_course';
		$condition='';
		if(!empty($condi['pk_course'])){
			$condition .= "  pk_course not in (".$condi['pk_course'].") and admin_status='1'";
		}
		if(!empty($condi['num'])){
			$db->setLimit($condi['num']);
		}
		return $db->select($table,$condition,'','','','');
	}

    public static function getDefaultCourseRecomend($condi){
        $db = self::InitDB("db_course", "query");
		$table = 't_course';
		$condition='';
		if(!empty($condi['fk_grade'])){
			$condition = "fk_grade='".$condi['fk_grade']."'";
		}
        /*if(!empty($condi['subject'])){
			$condition .= "subject='".$condi['subject']."'";
		}*/
		if(!empty($condi['pk_course'])){
			$condition .= " and pk_course  in (".$condi['pk_course'].") and admin_status='1'";
		}
		if($condi['order_str']=="user_total:desc"){
			$orderby= array("user_total"=>"desc");
		}elseif($condi['order_str']=="remain_user:desc"){
			$orderby= array("(max_user-user_total)"=>"desc");
		}else{
			$orderby='';
		}
		if(!empty($condi['num'])){
			$db->setLimit($condi['num']);
		}
		return $db->select($table,$condition,'','',$orderby);
	}
	public function getClassPlan($class_id_arr){
		$db = self::InitDB("db_course", "query");
		$table = 't_course_plan';
		$class_id_str = implode(',',$class_id_arr);
		$condition = "fk_class in ($class_id_str) and status <> -1";
		$order = array('start_time'=>'asc');
		return $db->select($table,$condition,'','',$order);

	}

	public function getAttrList($page,$limit,$name='',$status='',$cateid = ''){
		$db = self::InitDB("db_course", "query");
        $table = array("t_course_attr");
		$condition = '';
		if(!empty($name)){
			$condition .= "name like '%$name%' and ";
		}
		if(!empty($cateid)){
			$condition .= "fk_cate = $cateid and ";
		}
		if(is_numeric($status)){
			$condition .= "status = $status";
		}else{
			$condition .= 'status <> -1';
		}
		if(!empty($page) && !empty($limit)){
			$db->setLimit($limit);
			$db->setPage($page);
			$db->setCount(true);
		}
		$order = array('pk_attr'=>'asc');
		return $db->select($table,$condition,'','',$order);
	}

	public static function addAttr($data){
		$db = self::InitDB("db_course");
        $table = array("t_course_attr");
		return $db->insert($table,$data);
	}

	public static function updateAttr($attr_id,$data){
        $db = self::InitDB('db_course');
        $table = array('t_course_attr');
		$condition = array('pk_attr' => $attr_id);
		return $db->update($table, $condition, $data);
 	}

	public static function delAttrByAid($attr_id){
        $db = self::InitDB('db_course');
        $table = array('t_course_attr');
		$condition = array('pk_attr' => $attr_id);
		return $db->delete($table, $condition);
 	}

	/*
	 *修改多个分类属性
	 *@params $attrIds string 属性id
	 *@params $data array
	 *@autor lijingjuan
	 */
	public function updateAttrByAttrIds($attrIds,$data){
		$db = self::InitDB('db_course');
        $table = array('t_course_attr');
		$condition = "pk_attr in ($attrIds)";
		return $db->update($table, $condition, $data);
	}

	/*
	 *修改属性分类id值
	 *@params $cateId int 分类id
	 *@params $data array
	 *@autor lijingjuan
	 */
	public function updateAttrByCateId($cateId,$data){
		$db = self::InitDB('db_course');
        $table = array('t_course_attr');
		$condition = "fk_cate = $cateId";
		return $db->update($table, $condition, $data);
	}

	/*
	 *获取分类属性
	 *@params $cate_id int 分类id
	 *@autor lijingjuan
	 */
	public function getAttrByCateId($cate_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr');
		$condition = "status <> -1 and fk_cate = $cate_id";
 		return $db->select($table,$condition);
	}

	/*
	 *获取分类属性和属性值
	 *@params $cate_id int 分类id
	 *@autor lijingjuan
	 */
	public function getAttrAndValueByCateId($cate_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr');
		$left = new stdclass;
		$left->t_course_attr_value = "t_course_attr.pk_attr = t_course_attr_value.fk_attr";
		$item = array(
				't_course_attr.pk_attr as attr_id','t_course_attr.name','t_course_attr.name_display','t_course_attr.fk_cate as cate_id',
				't_course_attr_value.pk_attr_value as attr_value_id','t_course_attr_value.name as value_name'
		);
		$condition = "t_course_attr.status <> -1 and t_course_attr.fk_cate = $cate_id and t_course_attr_value.status <> -1";
 		return $db->select($table,$condition,$item,'','',$left);
	}

	/*
	 *获取分类属性
	 *@params $cateIdArr array 分类id
	 *@autor lijingjuan
	 */
	public function getAttrByCateIdArr($cateIdArr){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr');
		$cateIdStr = implode(',',$cateIdArr);
		$condition = "status <> -1 and fk_cate in($cateIdStr)";
 		return $db->select($table,$condition);
	}

	public static function getAttrByAid($attr_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr');
		$condition = array('pk_attr' => $attr_id);
 		return $db->selectOne($table,$condition);
	}

	public static function addAttrValue($data){
		$db = self::InitDB("db_course");
        $table = array("t_course_attr_value");
		return $db->insert($table,$data);
	}

	public function getAttrValueListByAttrId($page,$limit,$attr_id,$name='',$status=''){
		$db = self::InitDB("db_course", "query");
        $table = array("t_course_attr_value");
		$condition = '';
		if(!empty($name)){
			$condition .= "name like '%$name%' and ";
		}
		if(is_numeric($status)){
			$condition .= "status = $status and ";
		}else{
			$condition .= 'status <> -1 and ';
		}
		$condition .= "fk_attr = $attr_id";
		if(!empty($page) && !empty($limit)){
			$db->setLimit($limit);
			$db->setPage($page);
			$db->setCount(true);
		}
		$order = array('pk_attr_value'=>'asc');
		return $db->select($table,$condition,'','',$order);
	}

	public static function updateAttrValue($attr_value_id,$data){
        $db = self::InitDB('db_course');
        $table = array('t_course_attr_value');
		$condition = array('pk_attr_value' => $attr_value_id);
		return $db->update($table, $condition, $data);
 	}

	public static function delAttrValueByAvid($attr_value_id){
        $db = self::InitDB('db_course');
        $table = array('t_course_attr_value');
		$condition = array('pk_attr_value' => $attr_value_id);
		return $db->delete($table, $condition);
 	}

	public static function delAttrValueByAttrId($attr_id){
        $db = self::InitDB('db_course');
        $table = array('t_course_attr_value');
		$condition = array('fk_attr' => $attr_id);
		return $db->delete($table, $condition);
 	}

	public static function updateAttrValueByAttrId($attr_id,$data){
        $db = self::InitDB('db_course');
        $table = array('t_course_attr_value');
		$condition = array('fk_attr' => $attr_id);
		return $db->update($table, $condition, $data);
 	}

	public static function getAttrValueById($attr_value_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr_value');
		$condition = array('pk_attr_value' => $attr_value_id);
 		return $db->selectOne($table,$condition);
	}

	public function getAttrValueByAttrValueIds($attr_value_ids){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr_value');
		$condition = "pk_attr_value in ($attr_value_ids)";
 		return $db->select($table,$condition);
	}

	/*
	 *根据attrids列取属性
	 *@autor binbin
	 *@date 2012-12-25 圣诞快乐
	 */
	public static function listAttrValueByAttrIds($attrIdsArr){
		$db = self::InitDB("db_course", "query");
        $condition = array();
        $condition = array("t_course_attr_value.status <> -1");
		//$condition[]   = "t_course.type <> 2";
		$table = "t_course_attr_value";
        $items=array(
           'attr_value_id'=>'pk_attr_value',
           'attr_id'=>'fk_attr',
		   'name',
		   'descript',
        );
		$attrIds = implode(',',$attrIdsArr);
		$condition[] = "fk_attr IN ($attrIds) ";
		return $db->select($table,$condition,$items);
    }

	public static function getAttrValueByAttrId($attr_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_attr_value');
		$condition = "status <> -1 and fk_attr = $attr_id";
 		return $db->select($table,$condition);
	}


	public static function getCourseAttrValueByAttrId($attr_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_mapping_course_attr_value');
		$left = new stdclass;
		$left->t_course_attr_value = 't_course_attr_value.pk_attr_value = t_mapping_course_attr_value.fk_attr_value';
		$item = array('t_mapping_course_attr_value.fk_course as course_id','t_mapping_course_attr_value.fk_attr_value as attr_value_id',
					't_course_attr_value.name as value_name','t_course_attr_value.fk_attr as attr_id');
		$condition = "t_mapping_course_attr_value.status <> -1 and t_course_attr_value.fk_attr = $attr_id and t_course_attr_value.status <> -1";
 		return $db->select($table,$condition,$item,'','',$left);
	}

	public static function getCourseAttrValueByAttrValueId($attr_value_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_mapping_course_attr_value');
		$condition = "fk_attr_value =$attr_value_id and status <> -1";
 		return $db->select($table,$condition);
	}

	public static function getCourseAttrValueByCourseId($course_id){
        $db = self::InitDB('db_course','query');
        $table = array('t_mapping_course_attr_value');
		$left = new stdclass;
		$left->t_course_attr_value = 't_course_attr_value.pk_attr_value = t_mapping_course_attr_value.fk_attr_value';
		$item = array('t_mapping_course_attr_value.fk_course as course_id','t_mapping_course_attr_value.fk_attr_value as attr_value_id',
					't_course_attr_value.name as value_name','t_course_attr_value.fk_attr as attr_id');
		$condition = "t_mapping_course_attr_value.status <> -1 and t_mapping_course_attr_value.fk_course = $course_id";
 		return $db->select($table,$condition,$item,'','',$left);
	}

	//中间层获取课程属性值
	public static function getCourseAttrValueByCourseIds($courseids){
		$courseids = is_array($courseids)?implode(',',$courseids):$courseids;
        $db = self::InitDB('db_course','query');
        $table = array('t_mapping_course_attr_value');
		$left = new stdclass;
		$left->t_course_attr_value = 't_course_attr_value.pk_attr_value = t_mapping_course_attr_value.fk_attr_value';
		$left->t_course_attr = 't_course_attr_value.fk_attr = t_course_attr.pk_attr';
		$item = array('t_mapping_course_attr_value.fk_course as course_id','t_mapping_course_attr_value.fk_attr_value as attr_value_id',
					't_course_attr_value.name as value_name','t_course_attr_value.fk_attr as attr_id','t_course_attr.name_display as attr_name_display','t_course_attr.name as attr_name');
		$condition = "t_mapping_course_attr_value.status <> -1 and t_mapping_course_attr_value.fk_course in ( $courseids )";
 		return $db->select($table,$condition,$item,'','',$left);
	}

	/*
	 *获取课程分类树状列表
	 *@autor lijingjuan
	 *@date 2012-12-14
	 */
	public function getCateList(){
		$db = self::InitDB('db_course','query');
		$table = array('node'=>"t_course_category");
		$left = new stdclass;
		$left->t_course_category = "node.pk_cate = t_course_category.pk_cate ";
		$item = array('node.name','node.pk_cate','node.level','node.lft','node.rgt','node.name_display');
		$condition = "node.status <> -1 AND node.lft BETWEEN t_course_category.lft AND t_course_category.rgt";
		$order = array('node.lft'=>'asc');
		return $db->select($table,$condition,$item,'',$order,$left);
	}

	/*
	 *获取课程分类父节点信息
	 *@params $cateId int 分类id
	 *@params $levl int 分类级别
	 *@autor lijingjuan
	 *@date 2012-12-14
	 */
	public function getParentCateById($cateId,$level){
		$db = self::InitDB('db_course','query');
		$sql = "SELECT parent.name,parent.pk_cate
				FROM t_course_category AS node,
				t_course_category AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.pk_cate = $cateId
				AND parent.level = $level
				AND parent.status <> -1
				ORDER BY parent.lft";
		return $db->execute($sql);
	}
	/*
	 *获取课程分类父节点信息
	 *@params $cateId int 分类id
	 *@params $levl int 分类级别
	 *@autor lijingjuan
	 *@date 2012-12-14
	 */
	public function getParentCateByIds($cateIds,$level){
		$cateIds= is_array($cateIds)?implode(',',$cateIds):$cateIds;
		$db = self::InitDB('db_course','query');
		$sql = "SELECT distinct parent.pk_cate as pCateId,parent.name as pName,node.pk_cate as nCateId
				FROM t_course_category AS node,
				t_course_category AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.pk_cate in($cateIds)
				AND parent.level = $level
				AND parent.status <> -1
				ORDER BY parent.lft";
		return $db->execute($sql);
	}

	/*
	 *通过分类id获取分类信息
	 *@params $cateId int 分类id
	 *@autor lijingjuan
	 */
	public function getCateById($cateId){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_category');
		$condition = array('pk_cate' => $cateId);
 		return $db->selectOne($table,$condition);
	}

	public function getCateByCateIdArr($cateIdArr){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_category');
		$cateIdStr = implode(',',$cateIdArr);
		$condition = "pk_cate in ($cateIdStr)";
 		return $db->select($table,$condition);
	}

	//中间层调用
	public function getCateByCateIdStr($cateIdStr){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_category');
		$condition = "pk_cate in ($cateIdStr)";
 		return $db->select($table,$condition);
	}

	/*
	 *获取相同层级的最大左值分类信息
	 *@params $level int 分类层级
	 *@params $lft int 左值
	 *@params $rgt int 右值
	 *@autor lijingjuan
	 */
	public function getBigLftCateByLevel($level,$lft='',$rgt=''){
        $db = self::InitDB('db_course','query');
        $table = array('t_course_category');
		$condition = '';
		if(!empty($lft) && !empty($rgt)){
			$condition .= "lft > $lft and rgt <$rgt and ";
		}
		$condition .= "status <> -1 and level = $level";
		$order = array('lft'=>'desc');
 		return $db->selectOne($table,$condition,'','',$order);
	}

	/*
	 *添加分类
	 *@prams $data array
	 *@autor lijingjuan
	 */
	public function addCate($data){
		$db = self::InitDB("db_course");
        $table = array("t_course_category");
		return $db->insert($table,$data);
	}

	/*
	 *修改分类
	 *@prams $cateId int 分类id
	 *@prams $data array
	 *@autor lijingjuan
	 */
	public function updateCate($cateId,$data){
		$db = self::InitDB("db_course");
        $table = array("t_course_category");
		$condition = "pk_cate = $cateId";
		return $db->update($table,$condition,$data);
	}

	/*
	 *删除分类
	 *@params $lft int 左值
	 *@params $rgt int 右值
	 *@autor lijingjuan
	 */
	public function delCateByLftAndRgt($lft,$rgt){
		$db = self::InitDB("db_course");
        $table = array("t_course_category");
		$condition = "lft BETWEEN $lft AND $rgt";
		return $db->delete($table,$condition);
	}

	/*
	 *修改左值
	 *@params $compare int 右值或者左值（如果是添加相邻节点传递右值，如果添加子节点传递左值）
	 *@params $data array
	 *@autor lijingjuan
	 */
	public function setLftCate($compare,$data){
		$db = self::InitDB('db_course');
        $table = array('t_course_category');
		$condition = "lft > $compare";
		return $db->update($table, $condition, $data);
	}

	/*
	 *修改右值
	 *@params $compare int 右值或左值（如果是添加相邻节点传递右值，如果添加子节点传递左值）
	 *@params $data array
	 *@autor lijingjuan
	 */
	public function setRgtCate($compare,$data){
		$db = self::InitDB('db_course');
        $table = array('t_course_category');
		$condition = "rgt > $compare";
		return $db->update($table, $condition, $data);
	}

	/*
	 *获取子节点
	 *@params $rgt int 右值
	 *@params $lft int 左值
	 *@params $level int 级别
	 *@autor lijingjuan
	 */
	public function getNodeCateByLftAndRgt($lft,$rgt,$level){
		$db = self::InitDB('db_course','query');
        $table = array('t_course_category');
		$condition = "lft BETWEEN $lft AND $rgt AND level = $level+1";
		$order = array('lft' => 'asc');
 		return $db->select($table,$condition,'','',$order);
	}

	/*
	 *获取分类根据级别
	 *@params $level int 级别
	 *@autor lijingjuan
	 */
	public function getCateByLevel($level){
		$db = self::InitDB('db_course','query');
        $table = array('t_course_category');
		$condition = "level = $level";
		$order = array('lft' => 'asc');
 		return $db->select($table,$condition,'','',$order);
	}

	/*
	 *通过一级分类获取课程
	 *@params $first_cate int 分类id
	 *@autor lijingjuan
	 */
	public function getCourseByFirstCate($first_cate){
		$db = self::InitDB('db_course','query');
        $table = array('t_course');
		$condition = "first_cate = $first_cate";
 		return $db->select($table,$condition);
	}
	/*
	 *通过二级分类获取课程
	 *@params $second_cate int 分类id
	 *@autor lijingjuan
	 */
	public function getCourseBySecondCate($second_cate){
		$db = self::InitDB('db_course','query');
        $table = array('t_course');
		$condition = "second_cate = $second_cate";
 		return $db->select($table,$condition);
	}

	/*
	 *通过三级分类获取课程
	 *@params $third_cate int 分类id
	 *@autor lijingjuan
	 */
	public function getCourseByThirdCate($third_cate){
		$db = self::InitDB('db_course','query');
        $table = array('t_course');
		$condition = "third_cate = $third_cate";
 		return $db->select($table,$condition);
	}


	public function addMappingCourseAttrValue($data){
		$db = self::InitDB("db_course");
        $table = array("t_mapping_course_attr_value");
		return $db->insert($table,$data);
	}

	public function updateMappingCourseAttrValue($attrValueIds,$courseId,$data){
		$db = self::InitDB("db_course");
        $table = array("t_mapping_course_attr_value");
		$condition = "fk_course = $courseId and fk_attr_value in ($attrValueIds)";
		return $db->update($table,$condition,$data);
	}

	public function delMappingCourseAttrValueByCidAndAvids($attrValueIds,$courseId){
        $db = self::InitDB("db_course");
        $table = array("t_mapping_course_attr_value");
        $condition = "fk_course = $courseId and fk_attr_value in ($attrValueIds)";
        return $db->delete($table, $condition);
    }

	public function updateMappingCourseAttrValueByCid($courseId,$data){
		$db = self::InitDB("db_course");
        $table = array("t_mapping_course_attr_value");
		$condition = "fk_course = $courseId";
		return $db->update($table,$condition,$data);
	}
    public function countCourseByOwner($owner, $params){
        $table = array("t_course");
        $condition = 'fk_user='.$owner;
        if (!empty($params->status)) {
            $condition .= ' AND admin_status='.$params->status;
        }
        if (!empty($params->start_time) && !empty($params->end_time)) {
            $condition .= ' AND create_time>\''.$params->start_time.'\' AND create_time<\''.$params->end_time.'\'';
        }
        $items = array(
            'count(pk_course) as count',
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items);
    }
    public function countTeacherClassByUid($uid, $params){
        $table = array("t_course_class");
        $condition = 'fk_user_class='.$uid;
        if (!empty($params->owner)) {
            $condition .= ' AND fk_user='.$params->owner;
        }
        if (!empty($params->status)) {
            $condition .= ' AND status='.$params->status;
        }
        $items = array(
            'count(fk_user_class) as count',
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items);
    }
    public function countTeacherPlanByUid($uid, $params){
        $table = array("t_course_plan");
        $condition = 't_course_plan.fk_user_plan='.$uid;
        if (!empty($params->owner)) {
            $condition .= ' AND t_course_plan.fk_user='.$params->owner;
        }
        if (!empty($params->status)) {
            $condition .= ' AND t_course_plan.status='.$params->status.' AND t_course.type='.$params->status;
        }
		$left =new stdclass;
		$left->t_course="t_course.pk_course=t_course_plan.fk_course";
        $items = array(
            'count(fk_user_plan) as count',
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items,"","",$left);
    }
    public function countStudentCourseByUid($uid, $params){
        $table = "t_course_user";
        $condition = 't_course_user.fk_user='.$uid;
        if (!empty($params->owner)) {
            $condition .= ' AND t_course_user.fk_user_owner='.$params->owner;
        }
        if (!empty($params->status)) {
            $condition .= ' AND t_course.status='.$params->status;
        }
        $items = array(
            'count(pk_course_user) as count',
        );
        $left='t_course on t_course_user.fk_course=t_course.pk_course';
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items,'','',$left);
    }
    public function countStudentPlanByUid($uid, $params){
        $table = "t_course_user";
        $condition = 't_course_user.fk_user='.$uid;
        if (!empty($params->owner)) {
            $condition .= ' AND t_course_user.fk_user_owner='.$params->owner;
        }
        if (!empty($params->status)) {
            $condition .= ' AND t_course.status='.$params->status;
        }
        if (!empty($params->start_time)) {
            $condition .= ' AND t_course_plan.start_time="'.$params->start_time.'"';
        }
        if (!empty($params->end_time)) {
            $condition .= ' AND t_course_plan.end_time="'.$params->end_time.'"';
        }
        $items = array(
            'count(t_course_user.pk_course_user) as count',
        );
        $left=array(
            't_course'=>'t_course_user.fk_course=t_course.pk_course',
            't_course_plan'=>'t_course_plan.fk_course=t_course_user.fk_course',
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items,'','',$left);
    }

	public function getMgrAppRecommendList($page,$length,$name){
		$db = self::InitDB('db_course','query');
		$table = "t_course_category";
		$left = array('t_course_app_recommend'=>'t_course_app_recommend.fk_cate = t_course_category.pk_cate');
		$condition = array("t_course_category.level = 3"," t_course_category.status = 1");
		if(!empty($name)){
			array_push($condition,"t_course_category.name like '%$name%'");
		}
		$items = array("t_course_category.pk_cate as cate_id","t_course_category.name","t_course_category.name_display","t_course_app_recommend.fk_course as course_ids");
		if(!empty($page) && !empty($length)){
			$db->setPage($page);
			$db->setLimit($length);
		}
		return $db->select($table,$condition,$items,'','',$left);
	}

	public function getAppRecommendByCateId($cateId){
		$db = self::InitDB('db_course','query');
		$table = "t_course_app_recommend";
		$condition = "fk_cate = $cateId";
		return $db->selectOne($table,$condition);
	}

	public function updateRecommend($cateId,$data){
		$db = self::InitDB('db_course');
		$table = "t_course_app_recommend";
		$condition = "fk_cate = $cateId";
		return $db->update($table,$condition,$data);
	}

	public function addRecommend($data){
		$db = self::InitDB('db_course');
		$table = "t_course_app_recommend";
		return $db->insert($table,$data);
	}

	public function checkCourseByFirstCateArr($firstCateArr,$ownerId=0,$courseIds=''){
		$db = self::InitDB('db_course','query');
		$table = "t_course";
		$firstCateStr = implode(',',$firstCateArr);
                $condition = "first_cate in ($firstCateStr) and admin_status = 1";
		if($ownerId > 0){
			$condition .= " and fk_user={$ownerId}";
		}
                if(!empty($courseIds)){
			$condition .= " and pk_course in ({$courseIds})";
		}

		$groupBy = "first_cate";
		return $db->select($table,$condition,'',$groupBy);
	}

	public function checkCourseBySecondCateArr($secondCateArr,$ownerId=0,$courseIds=''){
		$db = self::InitDB('db_course','query');
		$table = "t_course";
		$secondCateStr = implode(',',$secondCateArr);
                $condition = "second_cate in ($secondCateStr) and admin_status = 1";
		if($ownerId > 0){
			$condition .= " and fk_user={$ownerId}";
		}
                if(!empty($courseIds)){
			$condition .= " and pk_course in ({$courseIds})";
		}
		$groupBy = "second_cate";
		return $db->select($table,$condition,'',$groupBy);
	}

	public function checkCourseByThirdCateArr($thirdCateArr,$ownerId=0,$courseIds=''){
		$db = self::InitDB('db_course','query');
		$table = "t_course";
		$thirdCateStr = implode(',',$thirdCateArr);
                $condition = "third_cate in ($thirdCateStr) and admin_status = 1";
		if($ownerId > 0){
			$condition .= " and fk_user={$ownerId}";
		}
                if(!empty($courseIds)){
			$condition .= " and pk_course in ({$courseIds})";
		}
		$groupBy = "third_cate";
		return $db->select($table,$condition,'',$groupBy);
	}

	public function checkMappingCourseByAttrValueIdArr($attrValueIdArr,$ownerId=0){
		$db = self::InitDB('db_course','query');
		$table = "t_mapping_course_attr_value";
		$left = array("t_course"=>"t_course.pk_course = t_mapping_course_attr_value.fk_course");
		$attrValueIdStr = implode(',',$attrValueIdArr);
		if($ownerId > 0){
			$condition = "t_mapping_course_attr_value.fk_attr_value in ($attrValueIdStr) and t_mapping_course_attr_value.status <> -1 and t_course.admin_status = 1 and t_course.fk_user={$ownerId}";
		}else{
			$condition = "t_mapping_course_attr_value.fk_attr_value in ($attrValueIdStr) and t_mapping_course_attr_value.status <> -1 and t_course.admin_status = 1";
		}
		$groupBy = "t_mapping_course_attr_value.fk_attr_value";
		$item = array('t_mapping_course_attr_value.fk_attr_value','t_mapping_course_attr_value.fk_course');
		return $db->select($table,$condition,$item,$groupBy,'',$left);
	}

	public function getRecommendList($cateId){
		$db = self::InitDB('db_course','query');
		$table = "t_course_app_recommend";
		$condition = "fk_cate IN ($cateId)";
		$items = ['fk_cate','fk_course'];
		return $db->select($table,$condition,$items);
	}
	public static function getCourseFirstCateInfo($condition){
		$table=array("t_course_category");
        $db = self::InitDB("db_course","query");
		return $db->select($table,$condition);
	}
	public static function getCourseCateSomeName($condition){
		$table=array("t_course_category");
        $db = self::InitDB("db_course","query");
		$condition = "pk_cate IN ($condition) AND level=1";
		return $db->select($table,$condition);
	}
	//获取所有属性值
	public function getAllAttrValue(){
		$db = self::InitDB('db_course','query');
		$table = "t_course_attr";
		$left = array("t_course_attr_value"=>"t_course_attr_value.fk_attr = t_course_attr.pk_attr");
		$condition = "t_course_attr.status=1 and t_course_attr_value.status=1";
		$item = array("t_course_attr.fk_cate,t_course_attr_value.pk_attr_value,t_course_attr_value.name");
		return $db->select($table,$condition,$item, "", "", $left);
	}

	//获取用使用的优惠券
	public function getDiscountCodeByOrder($orderIds){
		$db = self::InitDB('db_course','query');
		$table = "t_discount_code_used";
		$left = array("t_discount_code"=>"t_discount_code.pk_discount_code = t_discount_code_used.fk_discount_code");
		$condition = "t_discount_code_used.fk_order IN ({$orderIds})";
		$item = array("t_discount_code_used.fk_user,t_discount_code_used.fk_order,t_discount_code.discount_code");
		return $db->select($table,$condition,$item, "", "", $left);
	}

	//添加问题模板
	public function addPhrase($data)
	{
		$db = self::InitDB('db_course');
		$table = "t_phrase";
		return $db->insert($table,$data);
	}

	public function getPhrase($params){
		$db = self::InitDB('db_course','query');
		$table = "t_phrase";
		$condition = '';
		if(!empty($params['phraseId'])){
			$condition .= "t_phrase.pk_phrase={$params['phraseId']} and ";
		}
		if(!empty($params['type'])){
			$condition .= "t_phrase.type={$params['type']} and ";
		}
		$condition .= "status>-1";
		return $db->selectOne($table,$condition);
	}

	public function addPlanPhrase($data){
		$db = self::InitDB('db_course');
		$table = "t_course_plan_phrase";
		return $db->insert($table,$data);
	}

	public static function classByclassIds($classIds)
	{
        $db        = self::InitDB("db_course", "query");
        $condition = array();
		$table = "t_course_class";
		$condition[] = "pk_class IN ($classIds)";
		$condition["status"] = 3;
		return $db->select($table,$condition);
    }
	
	
	public static function classAndCourseList($data,$page,$length){
        $db = self::InitDB("db_course", "query");
        $condition = "t_course_class.status <> -1";
		
		if(!empty($data['courseId'])) {
            if (is_array($data['courseId'])) {
                $courseIdStr = implode(',', $data['courseId']);
				$condition .= " and fk_course IN ({$courseIdStr})";
            } else {
				$condition .= " and fk_course = {$data['courseId']}";
            }
        }
		if(!empty($data['userClassId'])){
			$condition .= " and fk_user_class = {$data['userClassId']}";
		}
		if(!empty($data['userId'])){
			$condition .= " and t_course_class.fk_user = {$data['userId']}";
		}
		if(!empty($data['courseType'])){
			$condition .= " and t_course.type = {$data['courseType']}";
        }
        if(!empty($data['classId'])){
            if(is_array($data['classId'])){
                $classIds = implode(',', $data['classId']);
                $condition .= " and t_course_class.pk_class IN ({$classIds})";
            }else{
                $condition .= " and t_course_class.pk_class = {$data['classId']}";
            }
        }
        if(!empty($data['progress_status'])){
            $condition .= " and t_course_class.progress_status = {$data['progress_status']}";
        }

		$item = array(
			"user_id"=>"t_course_class.fk_user",
			"class_id"=>"pk_class",
			"course_id"=>"fk_course",
			"user_class_id"=>"fk_user_class",
			"t_course_class.name",
			"t_course_class.status",
            "t_course_class.progress_percent",
            "t_course_class.progress_plan",
			"t_course_class.user_total",
			"t_course_class.create_time",
			"t_course.thumb_med",
			"course_title"=>"t_course.title",
			"course_type"=>"t_course.type"
		);
		$order = array("t_course_class.create_time" => "desc");

		if(!empty($data['sort'])){
			if($data['sort'] == 'total'){
				$order = array("t_course_class.user_total" => "desc");
			}elseif($data['sort'] == 'courseTime'){
				$order = array("t_course.start_time" => "desc");
			}
		}



		$left  = new stdclass;
        $left->t_course  = "t_course.pk_course=t_course_class.fk_course";
		$db->setPage($page);
        $db->setLimit($length);
        $db->setCount(true);
		$v = $db->select("t_course_class", $condition, $item, "", $order, $left);
		return $v;

    }

	public static function getPhraseByType($type)
	{
		$db = self::InitDB('db_course','query');
        $table = array('t_phrase');
        $condition = " type=$type ";
		$item = array("pk_phrase","type","answer");
		return $db->select($table, $condition, $item);
	}
	//获取机构的分类id
	public static function getOrgCate($uid,$type=0,$extWhere=array()){
		$db = self::InitDB('db_course','query');
        $table = array('t_course');
		$allCate=array();
		$cateIds=array();
        $condition = " fk_user=$uid and admin_status=1 and first_cate>0 and third_cate>0 and second_cate>0";
		if(!empty($extWhere)){
			foreach($extWhere as $k=>$v){
				$condition .=" and $k='$v' ";
			}
		}
		if($type==0){
			$item = array("distinct(third_cate) as third_cate","first_cate","second_cate");
		}elseif($type==1){
			$item = array("distinct(first_cate) as first_cate");
		}elseif($type==2){
			$item = array("distinct(second_cate) as second_cate");
		}elseif($type==3){
			$item = array("distinct(third_cate) as third_cate");
		}
		$orderby =array(
			"first_cate" => "asc",
			"second_cate" => "asc",
			"third_cate" => "asc",
		);
		$result = $db->select($table, $condition, $item, "", $orderby);
		if($result->items){
			foreach($result->items as $item){
				$first_cate = empty($item['first_cate'])?0:$item['first_cate'];
				$second_cate = empty($item['second_cate'])?0:$item['second_cate'];
				$third_cate = empty($item['third_cate'])?0:$item['third_cate'];
				if($first_cate && $second_cate && $third_cate){
					if(empty($allCate[$first_cate])){
						$allCate[$first_cate]=array();
					}
					if(empty($allCate[$first_cate][$second_cate])){
						$allCate[$first_cate][$second_cate]=array();
					}
					if(empty($allCate[$first_cate][$second_cate][$third_cate])){
						$allCate[$first_cate][$second_cate][$third_cate]=$third_cate;
					}
				}
				if(!empty($first_cate)) $cateIds[]=$first_cate;
				if(!empty($second_cate)) $cateIds[]=$second_cate;
				if(!empty($third_cate)) $cateIds[]=$third_cate;
			}
		}
		$data["lists"] = $allCate;
		$data["ids"] = $cateIds;;
		return $data;
	}
        
        //group
        public  function addGroup($data){
                $db    = self::InitDB("db_course");
		/*$table = array("t_course_class_group");
		$ret = $db->insert($table, $data);
		if($ret){
			return $ret;
		}*/
                $db->execute("BEGIN");
                $groupSql="INSERT INTO t_course_class_group(fk_course,fk_class,group_teacher_id,group_name,create_time)"
                        . "VALUES({$data['fk_course']},{$data['fk_class']},{$data['group_teacher_id']},'{$data['group_name']}','{$data['create_time']}')";
                $classSql = "UPDATE t_course_class SET group_count=group_count+1 WHERE pk_class=".$data['fk_class']; 
                
                $groupRes=$db->execute($groupSql);//var_dump($groupRes);
                $classRes=$db->execute($classSql);
                if($groupRes&&$classRes){
                    $db->execute("COMMIT");
                    return $groupRes;//组id
                }
                $db->execute("ROLLBACK");
                return false;
	}
        
        public function upGroup($groupid,$data){
                $db    = self::InitDB("db_course");
		$table = array("t_course_class_group");
                return $db->update($table, array("pk_group" => $groupid), $data);
        }
        
        public function delGroup($groupid,$classid){
                $db    = self::InitDB("db_course");
                //开始事务
                $db->execute("BEGIN");

                $relationSql = "DELETE  FROM t_course_class_group_user WHERE fk_class=".$classid." AND fk_class_group=".$groupid;
                //$groupSql = "UPDATE t_course_class_group SET status=-1 WHERE pk_group=".$groupid;
                $groupSql = "DELETE FROM t_course_class_group WHERE pk_group=".$groupid;
                $courseSql = "UPDATE t_course_class SET group_count=group_count-1 WHERE pk_class=".$classid;
                
                $relationRes = $db->execute($relationSql);//print_r($relationRes);
                $groupRes = $db->execute($groupSql);//print_r($groupRes);
                $courseRes=$db->execute($courseSql);//print_r($courseRes);die;
                if ($relationRes>=0 && $groupRes && $courseRes) {//=0代表未绑定关系时情况
                    //提交成功
                    $db->execute("COMMIT");
                    return true;
                }
                /*SLog::fatal('db error[%s]', var_export(
                                [
                    'qqrelationSql' => $relationSql,
                    'qqcustomSql' => $customSql
                                ], 1
                ));*/
                //事务回滚
                $db->execute("ROLLBACK");
                return false;
        }
        
        public function groupList($classid){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group");
                $condition = array('fk_class' => $classid,'status'=>1);
                return $db->select($table,$condition);
        }
        
        //设置分组权限
        public function setGroupPrivilege($classid,$data){
                $db    = self::InitDB("db_course");
		$table = array("t_course_class");
                return $db->update($table, array("pk_class" => $classid), $data);
        }
        
        public function batchHandleGroupUser($data){//代码冗余严重，需要优化
                $db    = self::InitDB("db_course");
                //开始事务
                //$db->execute("BEGIN");
                
                $fk_user=explode(",",$data['fk_user']);
                $count=count($fk_user);
                $groupid=$data['fk_class_group'];
                $classid=$data['fk_class'];
                if($data['fk_class_group']>0){//添加分组
                    //开始事务
                    $db->execute("BEGIN");
                    $sql_delete="DELETE FROM t_course_class_group_user WHERE fk_class=".$classid." AND fk_user IN(".$data['fk_user'].")";
                    
                    $sql="INSERT INTO t_course_class_group_user(`fk_class_group`,`fk_user`,`fk_class`,`fk_course`,`create_time`) VALUES";
                    for($i=0;$i<count($fk_user);$i++){
                        if(!empty($fk_user[$i])){
                            $sql.="({$data['fk_class_group']},{$fk_user[$i]},{$data['fk_class']},{$data['fk_course']},'{$data['create_time']}'),";
                        }
                    }
                    $sql=  rtrim($sql,",");//echo $sql;
                    
                    //$sql_group= "UPDATE t_course_class_group SET user_count=user_count+{$count} WHERE pk_group=".$groupid;
                    
                    $deleteRes=$db->execute($sql_delete);//print_r($deleteRes);
                    $relationRes = $db->execute($sql);//print_r($relationRes);
                    //$groupRes = $db->execute($sql_group);//print_r($groupRes);
                    if ($deleteRes>=0 && $relationRes) {//=0代表未绑定关系时情况
                    //提交成功
                    $db->execute("COMMIT");
                    return true;
                    }
                    //事务回滚
                    $db->execute("ROLLBACK");
                    return false;
                }else{
                    $sql="DELETE FROM t_course_class_group_user WHERE fk_class=".$classid." AND fk_user IN(".$data['fk_user'].")";
                    //$sql_group= "UPDATE t_course_class_group SET user_count=user_count-{$count} WHERE pk_group=".$groupid;
                   
                    $relationRes = $db->execute($sql);//file_put_contents("/home/yaojie/jay", $groupid."|".$sql."|".$relationRes);
                    if($relationRes){
                        return true;
                    }
                    return false;
                    //$groupRes = $db->execute($sql_group);//print_r($groupRes);
                    //if ($relationRes && $groupRes) {//=0代表未绑定关系时情况
                    //提交成功
                    //$db->execute("COMMIT");
                    //return true;
                    //}
                    //事务回滚
                    //$db->execute("ROLLBACK");
                    //return false;
                }
                
        }
        
        public function userList($condition,$page,$pagesize,$cache){
                $db    = self::InitDB("db_course","query");
                $key =md5( "course.group.userlist.".$condition['fk_class'].$condition['fk_class_group'].$page.$pagesize);
		if($cache){
                $v = redis_api::get($key);//print_r($v);
		if($v){return $v;}
                }
		$table = array("t_course_class_group_user");
                if(!empty($page)&&!empty($pagesize)){
                    $db->setPage($page);
                    $db->setLimit($pagesize);
                    $db->setCount(true);
                }//print_r($condition);
                $v=$db->select($table,$condition,'','','pk_id desc');//print_r($v);
                redis_api::set($key,$v,300);
                return $v;
        }
        
        public function getUser($params){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group_user");
                return $db->selectOne($table,$params);
        }
        
         public function getTeacher($params){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group");
                return $db->selectOne($table,$params);
        }
        
        //获取未分组list
        public function notInGroup($classid,$page,$pagesize,$cache){
                $db    = self::InitDB("db_course","query");
                $key =md5( "course.group.notInGroup.".$classid.$page.$pagesize);
		if($cache){
                $v = redis_api::get($key);
		if($v){return $v;}
                }
                $offset=($page-1)*$pagesize;
                $sql="SELECT a.fk_user FROM t_course_user AS a LEFT JOIN t_course_class_group_user AS b  "
                        . "ON (a.fk_class=b.fk_class AND a.fk_user=b.fk_user) "
                        . "WHERE a.fk_class={$classid} AND fk_class_group IS null "
                        . "ORDER BY pk_course_user DESC "
                        ."Limit {$offset},{$pagesize}";//echo $sql;die;
                $v=$db->execute($sql);
                redis_api::set($key,$v,300);
                return $v;
        }
        
        public function notInGroupTotal($classid,$cache){
                $db    = self::InitDB("db_course","query");
                $key =md5( "course.group.notInGroupTotal.".$classid);
		if($cache){
                $v = redis_api::get($key);
		if($v){return $v;}
                }
                $sql="SELECT count(a.fk_user) AS totalnum FROM t_course_user AS a LEFT JOIN t_course_class_group_user AS b  "
                        . "ON (a.fk_class=b.fk_class AND a.fk_user=b.fk_user) "
                        . "WHERE a.fk_class={$classid} AND fk_class_group IS null ";
                       //echo $sql;die;
                $v=$db->execute($sql);
                redis_api::set($key,$v,300);
                return $v;
        }
        
        public function checkIsGroupuserByClassAndUid($params){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group_user");
                return $db->selectOne($table,$params);
        }
        
        public function getGroupInfoByGroupid($groupid){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group");
                return $db->selectOne($table,array('pk_group'=>$groupid));
        }
        
        public  function batchCheckUserInGroup($classid,$userids){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group_user");
                $userids=  implode(",", $userids);
                $condition="fk_class={$classid} AND fk_user IN({$userids})";
                return $db->select($table,$condition);
        }
        
        public function getclassTeachers($params){
                $db    = self::InitDB("db_course","query");
		$table = array("t_course_class_group");
                return $db->select($table,$params);
        }

}
