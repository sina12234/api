<?php
/**
 * @author fanbin
 */
class stat_db extends common_db{

    const DB_STAT = 'db_stat';

    public function __construct()
    {
        parent::__construct(self::DB_STAT);
    }

	public static function InitDB($dbname="db_stat",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
	/* 
	 * 增加一条统计信息
	 */

	public static function addUserStatOrg($statdata){
		$db = self::InitDB();
		$table = "t_user_stat_org";
		return $db->insert($table,$statdata);
	}
	/*
	 * 获取单个用户的统计信息
	 * 参数 orguser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 */
	public static function getUserStatOrg($orgUser,$uid){
		$db = self::InitDB("db_stat","query");

		$table = "t_user_stat_org";

		$item=new stdclass;
		$item->uid = "fk_user";
		$item->orguser = "fk_user_org";
		$item->pv = "pv";
		$item->vv_live= "vv_live";
		$item->vv_record= "vv_record";
		$item->vt_live= "vt_live";
		$item->vt_record= "vt_record";
		$item->zan ="zan";
		$item->comment ="comment";
		$item->answers ="answers";
		$item->answers_right ="answers_right";
		$item->handup ="handup";
		$item->call ="call";

		$condition = array();
		$condition['fk_user'] = $uid;
		$condition['fk_user_org'] = $orgUser;

		//$key =md5( "stat_db.getUserStatOrg.".$oid);
		//$v = redis_api::get($key);
		//if($v){return $v;}

		$v = $db->selectOne($table,$condition,$item);
		//redis_api::set($key,$v,60);
		return $v;
	}
	/*
	 * 当用户获得赞的时候调用此接口 zan+1
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 * setArray 是传过来经过API处理后需要更新的数据
	 */
    public static function setZanNum($orgUser,$uid){
        $db = self::InitDB();
		$table = "t_user_stat_org";
		$condition = array();
		$condition['fk_user_org'] = $orgUser;
		$condition['fk_user'] = $uid;
		$setArray = array("`zan` = `zan` + 1");
        return $db->update($table,$condition,$setArray);
    }
	/*
	 * 当用户发表评的时候调用此接口  comment+1
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 * setArray 是传过来经过API处理后需要更新的数据
	 */
    public static function setCommentNum($orgUser,$uid){
        $db = self::InitDB();
		$table = "t_user_stat_org";
		$condition = array();
		$condition['fk_user_org'] = $orgUser;
		$condition['fk_user'] = $uid;
		$setArray = array("`comment` = `comment` + 1");
        return $db->update($table,$condition,$setArray);
    }
	/*
	 * 当更新数据时调用此接口
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 * upArray 是传过来经过API处理后需要更新的数据
	 */
	public static function updateUserStatOrg($orgUser,$uid,$upArray){
        $db = self::InitDB();
		$table = "t_user_stat_org";
		$condition = array();
		$condition['fk_user_org'] = $orgUser;
		$condition['fk_user'] = $uid;
		return $db->update($table,$condition,$upArray);
	}
	/*
	 * 获取多个 用户或机构的统计信息
	 * 参数 orgUser 机构id   uid 所查的用户的id
	 * orgUser  uid  是两个主键
	 * seldata 是筛选条件
	 */
	public static function UserStatOrgList($orgUser,$uid,$seldata = array(),$page = null,$length = null){
		$db = self::InitDB("db_stat","query");

		$table = "t_user_stat_org";

		$item=new stdclass;
		$item->uid = "fk_user";
		$item->orguser = "fk_user_org";
		$item->pv = "pv";
		$item->vv_live= "vv_live";
		$item->vv_record= "vv_record";
		$item->vt_live= "vt_live";
		$item->vt_record= "vt_record";
		$item->zan ="zan";
		$item->comment ="comment";
		$item->answers ="answers";
		$item->answers_right ="answers_right";
		$item->handup ="handup";
		$item->call ="call";

		$condition = array();
		if(!empty($uid)){
			$condition['fk_user'] = $uid;
		}
		if(!empty($orguser)){
			$condition['fk_user_org'] = $orgUser;
		}

		$order = array();
		$order["fk_user"] = "desc";

		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}

		//$key =md5( "stat_db.UserStatOrglist.".$oid);
		//$v = redis_api::get($key);
		//if($v){return $v;}
		return $db->select($table,$condition,$item,"",$orderby,"");
	}

	/* 
	 * t_course_stat 
	 * 输入 增加一条统计数据
	 * $statdata = array("fk_course"=>$statdata["course_id"],"section_count"=>"1");
	 *
	 */
	public static function addCourseStatSectionCount($statdata){
		$db = self::InitDB();
		$table = "t_course_stat";
		return $db->insert($table,$statdata);
	}
	/* 
	 * t_course_stat 
	 * 输入 增加一条统计数据
	 * $statdata = array("fk_course"=>$statdata["course_id"],"class_count"=>"1");
	 *
	 */
	public static function addCourseStatClassCount($statdata){
		$db = self::InitDB();
		$table = "t_course_stat";
		return $db->insert($table,$statdata);
	}
	/*
	 * 当用户创建section时的时候调用此接口  section_count+1
	 * 参数 fk_course 课程id   
	 * setArray 是传过来经过API处理后需要更新的数据
	 */
    public static function setCourseStatSectionCount($course_id,$data){
        $db = self::InitDB();
		$table = "t_course_stat";
		$condition = array();
		$condition['fk_course'] = $course_id;
		if($data["count"]=="1"){
			$setArray = array("`section_count` = `section_count` + 1");
		}elseif($data["count"]=="-1"){
			$setArray = array("`section_count` = `section_count` - 1");
		}
		return $db->update($table,$condition,$setArray);
    }
	/*
	 * 当用户创建section时的时候调用此接口  class_count+1
	 * 参数 fk_course 课程id   
	 * setArray 是传过来经过API处理后需要更新的数据
	 */
    public static function setCourseStatClassCount($course_id,$data=array()){
        $db = self::InitDB();
		$table = "t_course_stat";
		$condition = array();
		$condition['fk_course'] = $course_id;
		if($data["count"]=="1"){
			$setArray = array("`class_count` = `class_count` + 1");
		}elseif($data["count"]=="-1"){
			$setArray = array("`class_count` = `class_count` - 1");
		}
		return $db->update($table,$condition,$setArray);
    }
	/*
	 * 获取单个Course的统计信息
	 * 参数 course_id 课程id   
	 */
	public static function getCourseStat($course_id,$data = array()){
		$db = self::InitDB("db_stat","query");
		$table = "t_course_stat";
		$item=new stdclass;
		$item->course_id = "fk_course";
		$item->vv_live= "vv_live";
		$item->vv_record= "vv_record";
		$item->vt_live= "vt_live";
		$item->vt_record= "vt_record";
		$item->section_count ="section_count";
		$item->class_count ="class_count";
		$item->comment ="comment";
		$condition = array();
		$condition['fk_course'] = $course_id;

		//$key =md5( "stat_db.getUserStatOrg.".$oid);
		//$v = redis_api::get($key);
		//if($v){return $v;}
		$v = $db->selectOne($table,$condition,$item);
		//redis_api::set($key,$v,60);
		return $v;
	}
	/*
	 * 通过一组course id来查询course相关数据
	 * 参数 idsStr 课程id字符串 用于生成course index
	 */
	public function listCourseStatByIds( $idsStr ){
		$condition = array("fk_course in ( $idsStr )");
		$item= array("fk_course","vv_live","vv_record","vt_live","vt_record","section_count","class_count","comment","comment_new");
		$db = self::InitDB("db_stat","query");
		return $db->select("t_course_stat",$condition,$item,"",array("fk_course"=>"asc"),"");
	}
	/*
	 * 通过一组plan id来查询plan相关数据
	 * 参数 idsStr 课程id字符串 用于生成plan index
	 */
	public function listPlanStatByIds( $idsStr ){
		$condition = array("fk_plan in ( $idsStr )");
		$item= array("fk_plan","vv_live","vv_record","vt_live","vt_record","comment","comment_new");
		$db = self::InitDB("db_stat","query");
		return $db->select("t_plan_stat",$condition,$item,"",array("fk_plan"=>"asc"),"");
	}
	/*
	 * 通过plan id来查询plan相关数据
	 */
	public static function listPlanStatById( $idStr ){
		if(strstr($idStr,',')){
			$condition = array("fk_plan in ( $idStr )");
		}else{
			$condition = array("fk_plan = $idStr ");
		}
		$item= array("fk_plan","vv_live","vv_record","vt_live","vt_record","comment","comment_new","discuss","zan","handup","call","max_online","on_time","late","correct","answer_rate","status","last_updated","classroom_test_count","ask_count");
		$db = self::InitDB("db_stat","query");
		return $db->select("t_plan_stat",$condition,$item);
	}

	/*
	 * 通过plan id来查询备课出题答题统计数据
	 */
	public static function getClassExamStat( $id ){
		$condition = array("fk_plan = $id ");
		$db = self::InitDB("db_stat","query");
		return $db->select("t_plan_exam_stat",$condition);
	}
	/*
	 * 通过plan id来查询快速出题答题统计数据
	 */
	public static function getClassPhraseStat( $id ){
		$condition = array("fk_plan = $id ");
		$db = self::InitDB("db_stat","query");
		return $db->select("t_plan_phrase_stat",$condition);
	}
	/* 
	 * t_user_org_stat 
	 * 输入 增加一条统计数据
	 * $statdata = array("fk_user"=>$statdata["course_id"],"section_count"=>"1");
	 *
	 */
	public static function addUserOrgStat($statdata){
		$db = self::InitDB();
		$table = "t_user_org_stat";
		return $db->insert($table,$statdata);
	}
	/*
	 * 当机构创建class时的时候调用此接口  class_count+1
	 * 参数 fk_user 机构用户Id   
	 * setArray 是传过来经过API处理后需要更新的数据
	 */
    public static function setUserOrgStatClassCount($user_id,$data=array()){
        $db = self::InitDB();
		$table = "t_user_org_stat";
		$condition = array();
		$condition['fk_user'] = $user_id;
		if($data["count"]=="1"){
			$setArray = array("`class_count` = `class_count` + 1");
		}elseif($data["count"]=="-1"){
			$setArray = array("`class_count` = `class_count` - 1");
		}
		return $db->update($table,$condition,$setArray);
    }
    public static function setUserOrgStatCourseCount($user_id,$data=array()){
        $db = self::InitDB();
		$table = "t_user_org_stat";
		$condition = array();
		$condition['fk_user'] = $user_id;
		if($data["count"]=="1"){
			$setArray = array("`course_count` = `course_count` + 1");
		}elseif($data["count"]=="-1"){
			$setArray = array("`course_count` = `course_count` - 1");
		}
		return $db->update($table,$condition,$setArray);
    }
	/*
	 * 获取单个机构用户的统计信息
	 * 参数 user_id 机构用户id   
	 */
	public static function getUserOrgStat($user_id,$data = array()){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_org_stat";
		$item=new stdclass;
		$item->user_id = "fk_user";
		$item->pv = "pv";
		$item->vv_live= "vv_live";
		$item->vv_record= "vv_record";
		$item->vt_live= "vt_live";
		$item->vt_record= "vt_record";
		$item->zan = "zan";
		$item->comment = "comment";
		$item->course_count ="course_count";
		$item->class_count ="class_count";
		$condition = array();
		$condition['fk_user'] = $user_id;

		//$key =md5( "stat_db.getUserStatOrg.".$oid);
		//$v = redis_api::get($key);
		//if($v){return $v;}
		$v = $db->selectOne($table,$condition,$item);
		//redis_api::set($key,$v,60);
		return $v;
	}
	//for sphinx indexing organizations
	public static function listOrgstatByIds( $idsStr ){
		$db = self::InitDB("db_stat","query_sphinx");
		$left=new stdclass;
		$condition = array("fk_user in ( $idsStr )");
		$item = array("fk_user","vv_record","vv_live","vt_record","vt_live","zan","comment","discuss",
			"course_count","class_count","student_new");
		return $db->select("t_user_org_stat", $condition, $item, '', '' );
	}

	public static function getUserOrgStatFkuser(){

		$db = self::InitDB("db_stat","query");
        $table = "t_day_user_org_stat";
        $groupby = 'fk_user';
        $items   = 'fk_user';
        $order   = array('fk_user'=>'asc');
        $fkuser  = $db->select($table,'',$items,$groupby,$order);
        return $fkuser; 
	}

	public static function getUserOrgStatByPkday($params){
		$db = self::InitDB("db_stat","query");
       	$table = "t_day_user_org_stat";

        $condition = "pk_day BETWEEN '$params->min_date' AND '$params->max_date' AND fk_user = $params->fk_user";
        $order     = array('pk_day'=>'desc');
        return $db->select($table,$condition,'','',$order);
    }
	
	public static function getDayUserOrgOrderStatByPkday($min_date,$max_date){
		$db = self::InitDB("db_stat","query");
       	$table = "t_day_user_org_stat";
		$condition = '';
		if(!empty($min_date) && empty($max_date)){
			$condition .= "pk_day = '$min_date' AND ";
		}elseif(empty($min_date) && !empty($max_date)){
			$condition .= "pk_day = '$max_date' AND ";
		}elseif(!empty($min_date) && !empty($max_date)){
			$condition .= "pk_day BETWEEN '$min_date' AND '$max_date' AND ";
		}
		$condition .= ' order_new <> 0';
        $order = array('pk_day'=>'desc','order_new'=>'desc');
        return $db->select($table,$condition,'','',$order);
    }
        /* 机构用户日报表api */
        public static function getDayOrgUserStat($search_date){
            $db = self::InitDB("db_stat","query");
            $table = "t_day_org_user_teacher";
            $condition = '';
            if(!empty($search_date)){
                $condition .= "pk_day = '$search_date' ";
            }
            $order = array('pk_day'=>'desc');
            return $db->select($table,$condition,'','',$order);
        }
	public static function getDayOrgStatByOwnerid($ownerId,$min_date,$max_date){
		$db = self::InitDB("db_stat","query");
       	$table = "t_day_user_org_stat";
		$condition = '';
		if(!empty($min_date) && empty($max_date)){
			$condition .= "pk_day = '$min_date' AND ";
		}elseif(empty($min_date) && !empty($max_date)){
			$condition .= "pk_day = '$max_date' AND ";
		}elseif(!empty($min_date) && !empty($max_date)){
			$condition .= "pk_day BETWEEN '$min_date' AND '$max_date' AND ";
		}
		$condition .= " fk_user = {$ownerId}";
		$items = array('pk_day','fk_user','income_new','order_new','student_new');
        $order = array('pk_day'=>'asc');
        return $db->select($table,$condition,$items,'',$order);
    }
	
	public static function getOrgOrderCountByDay($ownerId,$min_date,$max_date){
		$db = self::InitDB("db_stat","query");
       	$table = "t_day_user_org_stat";
		$condition = '';
		if(!empty($min_date) && empty($max_date)){
			$condition .= "pk_day = '$min_date' AND ";
		}elseif(empty($min_date) && !empty($max_date)){
			$condition .= "pk_day = '$max_date' AND ";
		}elseif(!empty($min_date) && !empty($max_date)){
			$condition .= "pk_day BETWEEN '$min_date' AND '$max_date' AND ";
		}
		$condition .= " fk_user = {$ownerId}";
		$items = array('fk_user','sum(income_new) as income_all','sum(order_new) as order_count');
        return $db->select($table,$condition,$items);
    }
	
	public static function getTeacherStatByTid($teacher_id){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_teacher_stat";
		$condition = "fk_user = $teacher_id";
		return $db->selectOne($table,$condition);
	}

	public static function getTeacherStatByTidArr($tidarr){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_teacher_stat";
		$tidstr = implode(',',$tidarr);
		$condition = "fk_user in ($tidstr)";
		return $db->select($table,$condition);
	}
	
	public static function addTeacherStat($data){

		$db = self::InitDB("db_stat");
		$table = "t_user_teacher_stat";
		return $db->insert($table,$data);
	}

	public static function updateTeacherStat($teacher_id,$data){

		$db = self::InitDB("db_stat");
		$table = "t_user_teacher_stat";
		$condition = "fk_user = $teacher_id";
		return $db->update($table,$condition,$data);

	}

	public static function getTeacherStatOrgByTid($teacher_id,$owner_id){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_teacher_stat_org";
		$condition = "fk_user = $teacher_id and fk_user_owner = $owner_id";
		return $db->selectOne($table,$condition);
	}

	public static function getTeacherStatOrgByTidArr($tidarr,$owner_id){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_teacher_stat_org";
		$tidstr = implode(',',$tidarr);
		$condition = "fk_user in ($tidstr) and fk_user_owner = $owner_id";
		return $db->select($table,$condition);
	}
	

	public static function addTeacherStatOrg($data){
		$db = self::InitDB("db_stat");
		$table = "t_user_teacher_stat_org";
		return $db->insert($table,$data);
	}

	public static function updateTeacherStatOrg($teacher_id,$owner_id,$data){
		$db = self::InitDB("db_stat");
		$table = "t_user_teacher_stat_org";
		$condition = "fk_user = $teacher_id and fk_user_owner = $owner_id";
		return $db->update($table,$condition,$data);

	}
	//获取推广注册每日统计的信息
	public static function getPromoteStat($params){
		//define('DEBUG',true);
		$db = self::InitDB("db_stat","query");
		$table = 't_day_promote_stat';
		$condition = '1';
		//$condition = 'pk_day>=$params->start_time and pk_day<=$params->end_time and fk_promote=$params->fk_promote and fk_user_owner=$params->fk_user_owner';
		if(isset($params->start_time) && $params->start_time){
			$condition .= " and pk_day>='$params->start_time'";
		}
		if(isset($params->end_time) && $params->end_time){
			$condition .= " and pk_day<='$params->end_time'";
		}
		if(isset($params->fk_promote) && $params->fk_promote){
			$condition .= " and fk_promote=$params->fk_promote";
		}
		if(isset($params->fk_user_owner) && $params->fk_user_owner){
			$condition .= " and fk_user_owner=$params->fk_user_owner";
		}
		if(isset($params->pk_day) && $params->pk_day){
			$condition .= " and pk_day='$params->pk_day'";
		}
		if(isset($params->fk_promote_in) && $params->fk_promote_in){
			$condition .= " and $params->fk_promote_in";
		}
		if(isset($params->orderby) && $params->orderby){
			$orderby = $params->orderby;
		}else{
			$orderby = array('turn_count'=>'desc');
		}

		return $db->select($table,$condition,'','',$orderby);
	}
	
	public function getTeacherStatByTids($tids){
		$db = self::InitDB("db_stat","query");
		$table = 't_user_teacher_stat';
		$condition = "fk_user in ($tids)";
		return $db->select($table,$condition);
	}

	public function getTeacherStatOrgByTids($tids){
		$db = self::InitDB("db_stat","query");
		$table = 't_user_teacher_stat_org';
		$condition = "fk_user in ($tids)";
		return $db->select($table,$condition);
	}
	public static function getVvByInfo($course_id_str){
		$db = self::InitDB("db_stat","query");
		$table = 't_course_stat';
		$items = "fk_course,(vv_live+vv_record) as vv";
		$condition = "fk_course in ($course_id_str)";
		$orderby = array("vv"=>"desc");
		return $db->select($table,$condition,$items,'',$orderby,'');
	}
	public static function getMgrCourseVvByInfo($total_count){
		$db = self::InitDB("db_stat","query");
		$table = 't_course_stat';
		$items = "fk_course,(vv_live+vv_record) as vv";
		$orderby = array("vv"=>"desc");
		if(!empty($total_count)){
			$db->setLimit($total_count);
		}
		return $db->select($table,'',$items,'',$orderby,'');
	}
	public static function addUserStat($add_data){
		$db = self::InitDB("db_stat");
		$table = "t_user_stat";
		return $db->insert($table,$add_data);
	}

	public static function updateUserStat($fk_user,$updata){
		$db = self::InitDB("db_stat");
		$table = "t_user_stat";
		$condition = "fk_user = $fk_user";
		return $db->update($table,$condition,$updata);
	}

	public static function getUserStatByFkuser($fk_user){
		$db = self::InitDB("db_stat","query");
		$table = 't_user_stat';
		$condition = "fk_user = $fk_user";
		return $db->selectOne($table,$condition);
	}

	public static function addDayUserStat($add_data){
		$db = self::InitDB("db_stat");
		$table = "t_day_user_stat";
		return $db->insert($table,$add_data);
	}

	public static function updateDayUserStat($fk_user,$pk_day,$updata){
		$db = self::InitDB("db_stat");
		$table = "t_day_user_stat";
		$condition = "fk_user = $fk_user and pk_day = '$pk_day'";
		return $db->update($table,$condition,$updata);
	}
	public static function getDayUserStatByUserAndDay($fk_user,$pk_day){
		$db = self::InitDB("db_stat","query");
		$table = 't_day_user_stat';
		$condition = "fk_user = $fk_user and pk_day = '$pk_day'";
		return $db->selectOne($table,$condition);
	}

	public static function getPlanStatByPid($pid){
		$db = self::InitDB("db_stat","query");
		$table = 't_plan_stat';
		$condition = "fk_plan = $pid ";
		return $db->selectOne($table,$condition);
	}

	public static function addPlanStat($add_data){
		$db = self::InitDB("db_stat");
		$table = "t_plan_stat";
		return $db->insert($table,$add_data);
	}

	public static function updatePlanStat($fk_plan,$updata){
		$db = self::InitDB("db_stat");
		$table = "t_plan_stat";
		$condition = "fk_plan = $fk_plan";
		return $db->update($table,$condition,$updata);
	}

	public static function getCourseStatByCid($cid){
		$db = self::InitDB("db_stat","query");
		$table = 't_course_stat';
		$condition = "fk_course = $cid ";
		return $db->selectOne($table,$condition);
	}

	public static function addCourseStat($add_data){
		$db = self::InitDB("db_stat");
		$table = "t_course_stat";
		return $db->insert($table,$add_data);
	}

	public static function updateCourseStat($fk_course,$updata){
		$db = self::InitDB("db_stat");
		$table = "t_course_stat";
		$condition = "fk_course = $fk_course";
		return $db->update($table,$condition,$updata);
	}
	
	public static function getUserPlanStatCountByPid($uid,$pidArr){
		$db = self::InitDB("db_stat");
		$table = "t_user_course_plan_stat";
		$pidStr = implode(',',$pidArr);
		$condition = "fk_plan in ($pidStr) and fk_user = $uid";
		$item = array('count(fk_plan) as count');
		return $db->select($table,$condition,$item);
	}
	
	public static function getUserPlanStatCountByUid($uid){
		$db = self::InitDB("db_stat");
		$table = "t_user_course_plan_stat";
		$condition = "fk_user = $uid";
		$item = array('count(fk_plan) as count');
		return $db->select($table,$condition,$item);
	}
	
	public static function getUserPlanStatByPidArr($uid, $pidArr){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_course_plan_stat";
		$pidStr = implode(',',$pidArr);
		$condition = "fk_plan in ($pidStr) and fk_user = $uid";
		return $db->select($table,$condition);	
	}

	public static function getUserPlanStatByPid($pid,$page=1,$length=50){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_course_plan_stat";
		$condition = "fk_plan= $pid AND order_num<>0";
		if ($page>0 && $length>0) {
			$db->setPage($page);
			$db->setLimit($length);
			$db->setCount(true);
		}
		return $db->select($table,$condition,"","","order_num ASC");
	}
	//统计用户的课程学习时间
	public static function getUserCourseTotalTime($uid,$cidArr){
		$db = self::InitDB("db_stat","query");
		$table = "t_user_course_plan_stat";
		$condition = "fk_user= $uid and fk_course in(".implode(',',$cidArr).")";
		$item = array('fk_course','sum(vt_live) as sum_live','sum(vt_record) as sum_record');
		$groupby = 'fk_course';
		return $db->select($table,$condition,$item,$groupby);
	}

    public function getOrgTeacherStat($owner, $uid){
		$db = self::InitDB("db_stat");
        $table = array("t_user_teacher_stat_org");
        $condition=array(
            'fk_user_owner'=>$owner, 
            'fk_user'=>$uid, 
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, '*');
    }

    public function getOrgStudentStat($owner, $uid){
		$db = self::InitDB("db_stat");
        $table = array("t_user_stat_org");
        $condition=array(
            'fk_user_org'=>$owner, 
            'fk_user'=>$uid, 
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, '*');
    }
	
	public static function addPlanPhraseLog($data){
		$db = self::InitDB('db_stat');
		$values = trim($data,',');
		$sql = "insert into t_course_plan_phrase_log values ".$values;
		return $db->execute($sql);
	}

	public static function getPlanPhraseLogByPid($data,$page=0,$length=0){
		$db = self::InitDB("db_stat","query");
		$table = "t_course_plan_phrase_log";

		$condition = "fk_plan= $data->pid ";
		if(!empty($data->uid)){
			$condition .= " AND fk_user= $data->uid ";
		}
		if(!empty($data->planPhraseIdStr)){
			$condition .= " AND fk_plan_phrase IN (".$data->planPhraseIdStr.")";
		}
		if ($page>0 && $length>0) {
			$db->setPage($page);
			$db->setLimit($length);
			$db->setCount(true);
		}
		return $db->select($table,$condition);
	}
    
    

	public static function dayUserStatOrgVvList($orgUser = '',$times = '',$timee = '',$seldata = array()){
		if(empty($orgUser)){
			return;
		}
		if(empty($times)){
			return;
		}
		if(empty($timee)){
			return;
		}
		$db = self::InitDB("db_stat","query");

		$table = "t_day_user_stat_org";

		$item=new stdclass;
		$item->day = "pk_day";
		$item->uid = "fk_user";
		$item->orguser = "fk_user_org";
		$item->pv = "pv";
		$item->vv_live= "vv_live";
		$item->vv_record= "vv_record";
		$item->vt_live= "vt_live";
		$item->vt_record= "vt_record";
		$item->zan ="zan";
		$item->comment ="comment";
		$item->answers ="answers";
		$item->answers_right ="answers_right";
		$item->handup ="handup";
		$item->call ="call";

		$condition = array();
		if(!empty($times)&& !empty($timee)){
			//$condition['pk_day'] = $time;
			//$time1 = "2016-03-15";
            $condition[] = "pk_day >=\"$times\"";
            $condition[] = "pk_day <=\"$timee\"";
		}
		if(!empty($orgUser)){
			$condition['fk_user_org'] = $orgUser;
		}

		$order = array();
		$orderby["fk_user"] = "desc";

		//$key =md5( "stat_db.UserStatOrglist.".$oid);
		//$v = redis_api::get($key);
		//if($v){return $v;}
		return $db->select($table,$condition,$item,"",$orderby,"");
	}


	public static function dayUserStatOrgTotalVvList($orgUser = '',$times = '',$timee = '',$seldata = array()){
		if(empty($orgUser)){
			return;
		}
		if(empty($times)){
			return;
		}
		if(empty($timee)){
			return;
		}
		$db = self::InitDB("db_stat","query");

		$table = "t_day_org_vv_stat";

		$item=new stdclass;
		$item->day = "pk_day";
		$item->orguser = "fk_user_org";
		$item->vv_1 = "vv_1";
		$item->vv_2 = "vv_2";
		$item->vv_3 = "vv_3";
		$item->vv_4 = "vv_4";
		$item->vv_5= "vv_5";
		$item->vv_6= "vv_6";
		$item->vv_7_15= "vv_7_15";
		$item->vv_16_30= "vv_16_30";
		$item->vv_31= "vv_31";

		$condition = array();
		if(!empty($times)&& !empty($timee)){
			//$condition['pk_day'] = $time;
			//$time1 = "2016-03-15";
            $condition[] = "pk_day >=\"$times\"";
            $condition[] = "pk_day <=\"$timee\"";
		}
		if(!empty($orgUser)){
			$condition['fk_user_org'] = $orgUser;
		}

		$order = array();
		$orderby["fk_user_org"] = "desc";

		//$key =md5( "stat_db.UserStatOrglist.".$oid);
		//$v = redis_api::get($key);
		//if($v){return $v;}
		return $db->select($table,$condition,$item,"",$orderby,"");
	}

	public static function planIdAddRedis($data)
	{
		redis_api::useConfig("default");

		$key = md5("plan.statistical");

		return redis_api::sAdd($key,$data);
	}

}
