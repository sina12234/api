<?php

class tag_db{


	public static function InitDB($dbname="db_tag",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}

	public static function getMappingUserByUidAndGid($user_id,$group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_user";
		$condition = "fk_user = $user_id and fk_group = $group_id";
		return $db->select($table,$condition);
	}

	public static function addMappingUser($data){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_user";
		return $db->insert($table,$data);
	}

	public static function delMappingUserByUserAndTag($user_id,$group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_user";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_user = $user_id and fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}
	
	public static function delMappingUserByGidAndTidArr($group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_user";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}

	public static function delMappingUserByUserId($user_id){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_user";
		$condition = "fk_user IN(".$user_id.")";
		return $db->delete($table,$condition);
	}

	public static function delMappingUserByUserIdArr($user_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_user";
		$user_id_str = implode(',',$user_id_arr);
		$condition = "fk_user in ($user_id_str)";
		return $db->delete($table,$condition);
	}

	public static function delMappingQuestionByGidAndTidArr($group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_question";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}
	
	public static function getMappingCourseByCidAndGid($course_id,$group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_course";
		$condition = "fk_course = $course_id and fk_group = $group_id and status = 0";
		return $db->select($table,$condition);
	}
	public static function getMgrClassTagCourse($condi){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_course";
		$condition = '';
		$arr[] = $condi['fk_grade'];
		$arr[] = $condi['subject'];
		if(!empty($condi['fk_grade'])&&!empty($condi['subject'])){
			$str = implode(",",$arr);
		}else{
			$ids = implode(",",$arr);
			$str = trim($ids,",");
		}
		$condition.="fk_tag in(".$str.") and status=0";
		return $db->select($table,$condition);
	}
	public static function addMappingCourse($data){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_course";
		return $db->insert($table,$data);
	}

	public static function delMappingCourseByCidAndTidArr($course_id,$group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_course";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_course = $course_id and fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}
	
	public static function delMappingCourseByGidAndTidArr($group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_course";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}
	
	public static function delMappingCourseByCidAndGroupId($course_id,$group_id){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_course";
		$condition = "fk_course = $course_id and fk_group = $group_id";
		return $db->delete($table,$condition);
	}

	public static function getMappingPlanByPidAndGid($plan_id,$group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_plan";
		$condition = "fk_plan = $plan_id and group_id = $group_id";
		return $db->select($table,$condition);
	}

	public static function addMappingPlan($data){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_plan";
		return $db->insert($table,$data);
	}

	public static function delMappingPlanByPidAndTidArr($plan_id,$group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_plan";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_plan = $plan_id and fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}
	
	public static function delMappingPlanByGidAndTidArr($group_id,$tag_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_plan";
		$tag_id_str = implode(',',$tag_id_arr);
		$condition = "fk_tag in ($tag_id_str) and fk_group = $group_id";
		return $db->delete($table,$condition);
	}
	
	public static function delMappingPlanByPid($plan_id){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_plan";
		$condition = "fk_plan = $plan_id";
		return $db->delete($table,$condition);
	}

	public static function delMappingPlanByPidArr($plan_id_arr){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_plan";
		$plan_id_str = implode(',',$plan_id_arr);
		$condition = "fk_plan in ($plan_id_str)";
		return $db->delete($table,$condition);
	}

	public static function getOneMappingArticleByTid($tagId){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_article";
		$condition = "fk_tag = $tagId";
		return $db->selectOne($table,$condition);
	}
	
	public static function getMappingArticleByAidAndTid($aid,$uid,$tagId){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_article";
		$condition = "fk_article = $aid and fk_tag = $tagId and fk_group = $uid";
		return $db->selectOne($table,$condition);
	}

	public static function addMappingArticle($data){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_article";
		return $db->insert($table,$data);
	}

	public static function delMappingArticleByAidAndTid($aid,$uid,$tid){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_article";
		$condition = "fk_article = $aid and fk_tag = $tid and fk_user = $uid";
		return $db->delete($table,$condition);
	}

	public static function delMappingArticleByAid($aid){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_article";
		$condition = "fk_article = $aid";
		return $db->delete($table,$condition);
	}

	public static function getTagUserInUids($uids,$groupid)
	{
		$db        = self::InitDB("db_tag", "query");
		$table     = "t_mapping_tag_user";
		$item      = ['t_mapping_tag_user.fk_tag','t_mapping_tag_user.fk_group','t_mapping_tag_user.fk_user','t_tag.name'];
		$condition = " t_mapping_tag_user.fk_group=".$groupid." and t_mapping_tag_user.fk_user in (".$uids.") and t_mapping_tag_user.status <>-1";
		$left=new stdclass;
		$left->t_tag="t_mapping_tag_user.fk_tag = t_tag.pk_tag";
        return $db->select($table, $condition, $item,'','',$left);
	}

	public static function getBelongTagByGropId($groupId)
	{
		$db = self::InitDB("db_tag", "query");
		$table=array("t_tag");
		$item = [
			't_tag.pk_tag',
			't_tag.name'
		];
		$left=new stdclass;
		$left->t_tag_belong_group="t_tag_belong_group.fk_tag = t_tag.pk_tag";
		$condition = "t_tag_belong_group.fk_group={$groupId}";
		return $db->select( $table, $condition, $item ,"", "", $left);
	}

	public static function getTagList($page,$length){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag";
		if($page && $length){
			$db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
		}
		$condition=array("t_tag.status"=>0);
		$v= $db->select($table,$condition,'','',"pk_tag desc");
		return $v;
	}

	public static function getGroupList($page,$length,$name){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag_group";
		if($page && $length){
			$db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
		}
		$condition=array("t_tag_group.status"=>0);
		if( !empty($name) ){
			array_push($condition, 'name like \'%'.$name.'%\'');
		}
		$orderBy = array('pk_group'=>'desc');
		return $db->select($table,$condition,'','',$orderBy);

	}
	public static function searchTagShow($page,$length,$params){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag";
		$left=new stdclass;
		$item = array('t_tag.pk_tag','t_tag.name','t_tag.desc','t_tag.lastupdated','t_tag_belong_group.fk_group');
		$left->t_tag_belong_group="t_tag_belong_group.fk_tag = t_tag.pk_tag";
		$condition = "t_tag.status=0";
		if(!empty($params->pk_group)){
			$condition.=" and t_tag_belong_group.fk_group=".$params->pk_group;
		}
		if(!empty($params->name)){
			$condition.=" and t_tag.name like '%{$params->name}%'";
		}
		if($page && $length){
			$db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
		}
		$v= $db->select($table,$condition,$item,"","t_tag.pk_tag desc",$left);
		return $v;
	}
    public static function getsubjectTag($params){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag";
		$left=new stdclass;
		$item='';
		$item="t_tag.*,";
		$item.="t_tag_belong_group.*";
		$left->t_tag_belong_group="t_tag_belong_group.fk_tag = t_tag.pk_tag";
		$v= $db->select($table,array("t_tag_belong_group.fk_group"=>$params,"t_tag.status"=>0),$item,"","",$left);
		return $v;
	}
	public static function addTag($data){
		$table=array("t_tag");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
	public static function addTagCourse($data){
		$table=array("t_mapping_tag_course");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
    public static function addTagPlan($data){
		$table=array("t_mapping_tag_plan");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
    public static function addTagUser($data){
		$table=array("t_mapping_tag_user");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
	public static function addGroup($data){
		$table=array("t_tag_group");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}

	public static function getTagInfo($name){
		$db = self::InitDB("db_tag","query");
		$table=array("t_tag");
		return  $db->selectOne($table,array("name"=>$name));
	}
	public static function getGroupInfo($name, $groupId = 0){
		$db = self::InitDB("db_tag","query");
		$table=array("t_tag_group");
		$condition= array("name"=>$name); 
		if(!empty($groupId)){
			array_push($condition,"pk_group <> $groupId");
		}
		return  $db->selectOne($table,$condition);
	}
    public static function getTagGroupById($groupId){
		$db = self::InitDB("db_tag","query");
		$table=array("t_tag_group");
		return  $db->selectOne($table,array("pk_group"=>$groupId));
	}
	public static function updateTagGroup($groupId,$data){
		$table=array("t_tag_group");
		$db = self::initdb();
		return $db->update($table,array("pk_group"=>$groupId),$data);
	}
    public static function delGroup($groupId){
		$table=array("t_tag_group");
		$db = self::initdb();
		return $db->delete($table,array("pk_group"=>$groupId));
	}
	public static function delBelongTagGroup($groupId){
		$table=array("t_tag_belong_group");
		$db = self::initdb();
		return $db->delete($table,array("fk_group"=>$groupId));
	}
	
	public static function delTagBelongGroupByGidAndTidArr($groupId,$tagIdArr){
		$table=array("t_tag_belong_group");
		$db = self::initdb();
		$tagIdStr = implode(',',$tagIdArr);
		$condition = array("fk_group"=>$groupId,"fk_tag IN ($tagIdStr)");
		return $db->delete($table,$condition);
	}
	
	public static function updateBlongTagGroup($groupId,$fk_tag,$data){
		$table=array("t_tag_belong_group");
		$db = self::initdb();
		return $db->update($table,array("fk_group"=>$groupId,"fk_tag"=>$fk_tag),$data);
	}
	public static function updateTagCourse($groupId,$fk_tag,$data){
		$table=array("t_mapping_tag_course");
		$db = self::initdb();
		return $db->update($table,array("fk_group"=>$groupId,"fk_tag"=>$fk_tag),$data);
	}
	public static function delTagCourse($groupId){
		$table=array("t_mapping_tag_course");
		$db = self::initdb();
		return $db->delete($table,array("fk_group"=>$groupId));
	}
	public static function delTagPlan($groupId){
		$table=array("t_mapping_tag_plan");
		$db = self::initdb();
		return $db->delete($table,array("fk_group"=>$groupId));
	}
	public static function delTagUser($groupId){
		$table=array("t_mapping_tag_user");
		$db = self::initdb();
		return $db->delete($table,array("fk_group"=>$groupId));
	}
	public static function delTagQuestion($groupId){
		$table=array("t_mapping_tag_question");
		$db = self::initdb();
		return $db->delete($table,array("fk_group"=>$groupId));
	}
	public static function getbelongTagGroup($group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag_belong_group";
		$condition=array();
		$condition=array("status"=>0,"fk_group"=>$group_id);
		$v= $db->select($table,$condition,"","","");
		return $v;
	}
	public static function gettagCourse($group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_course";
		$condition=array();
		$condition=array("status"=>0,"fk_group"=>$group_id);
		$v= $db->select($table,$condition,"","","");
		return $v;
	}
	public static function gettagCourseById($fk_tag,$fk_group){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_course";
		$condition=array();
		$condition=array("status"=>0,"fk_group"=>$group_id);
		$v= $db->select($table,$condition,"","","");
		return $v;
	}
	public static function gettagPlan($group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_plan";
		$condition=array();
		$condition=array("status"=>0,"fk_group"=>$group_id);
		$v= $db->select($table,$condition,"","","");
		return $v;
	}
	public static function gettagUser($group_id){
		$db = self::InitDB("db_tag","query");
		$table = "t_mapping_tag_user";
		$condition=array();
		$condition=array("status"=>0,"fk_group"=>$group_id);
		$v= $db->select($table,$condition,"","","");
		return $v;
	}
	public static function addbelongTagGroup($data){
		$table=array("t_tag_belong_group");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
    public static function getTagGroupInfo($groupId){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag_group";
        $item='';
		$item.="t_tag_belong_group.*,";
        $item.="t_tag_group.name group_name,";
        $item.="t_tag.name tag_name";
		$left=new stdclass;
        $left->t_tag_belong_group="t_tag_group.pk_group=t_tag_belong_group.fk_group";
		$left->t_tag="t_tag.pk_tag=t_tag_belong_group.fk_tag";
		$condition=array();
		$condition=array("t_tag_belong_group.status"=>0,"t_tag_group.pk_group"=>$groupId);
		$v= $db->select($table,$condition,$item,"","",$left);
		return $v;
	}
	public static function getTagGroupInfoTmp($uid){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag_group";
        $item='';
		$item.="t_tag_belong_group.*,";
        $item.="t_tag_group.name group_name,";
        $item.="t_tag.name tag_name";
		$left=new stdclass;
        $left->t_tag_belong_group="t_tag_group.pk_group=t_tag_belong_group.fk_group";
		$left->t_tag="t_tag.pk_tag=t_tag_belong_group.fk_tag";
		$condition=array();
		$condition=array("t_tag_group.pk_group"=>$uid);
		$v= $db->select($table,$condition,$item,"","",$left);
		return $v;
	}
	public static function getTagGroupName($data){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag_group";
        $item='';
		$item.="t_tag_belong_group.*,";
        $item.="t_tag_group.name group_name,";
        $item.="t_tag.name tag_name";
		$left=new stdclass;
        $left->t_tag_belong_group="t_tag_group.pk_group=t_tag_belong_group.fk_group";
		$left->t_tag="t_tag.pk_tag=t_tag_belong_group.fk_tag";
		$condition='';
		$condition="t_tag.name in(".$data.") and t_tag.status=0";
		$v= $db->select($table,$condition,$item,"","",$left);
		return $v;
	}
	public static function getTagCourseName($data){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag_group";
        $item='';
		$item.="t_mapping_tag_course.*,";
        $item.="t_tag_group.name group_name,";
        $item.="t_tag.name tag_name";
		$left=new stdclass;
        $left->t_mapping_tag_course="t_tag_group.pk_group=t_mapping_tag_course.fk_group";
		$left->t_tag="t_tag.pk_tag=t_mapping_tag_course.fk_tag";
		$condition='';
		$condition="t_tag.name in(".$data.") and t_tag.status=0";
		$v= $db->select($table,$condition,$item,"","",$left);
		return $v;
	}

	public static function getTagByTidArr($tid_arr){
		$db = self::InitDB("db_tag","query");
		$table = "t_tag";
		$tid_str = implode(',',$tid_arr);
		$condition = "pk_tag in ($tid_str)";
		return $db->select($table,$condition);	
	}
	
	//for sphinx indexing course
	public static function getTagCourseByCids($cids){
		$db        = self::InitDB("db_tag", "query");
		$table     = "t_mapping_tag_course";
		$condition = "t_mapping_tag_course.fk_course in (".$cids.") and t_mapping_tag_course.status <>-1";
		$item      = ['t_mapping_tag_course.fk_tag','t_mapping_tag_course.fk_group','t_mapping_tag_course.fk_course','t_tag.name'];
		$left=new stdclass;
		$left->t_tag="t_mapping_tag_course.fk_tag = t_tag.pk_tag";
        return $db->select($table, $condition, $item,'','',$left);
	}
	
	//for sphinx indexing plan
	public static function getTagPlanByPids($pids){
		$db        = self::InitDB("db_tag", "query");
		$table     = "t_mapping_tag_plan";
		$condition = "t_mapping_tag_plan.fk_plan in (".$pids.") and t_mapping_tag_plan.status <>-1";
		$item      = ['t_mapping_tag_plan.fk_tag','t_mapping_tag_plan.fk_group','t_mapping_tag_plan.fk_plan','t_tag.name'];
		$left=new stdclass;
		$left->t_tag="t_mapping_tag_plan.fk_tag = t_tag.pk_tag";
		return $db->select($table, $condition, $item,'','',$left);
	}
	
	//for sphinx indexing question
	public static function getTagQuestionByQids($qids){
		$db        = self::InitDB("db_tag", "query");
		$table     = "t_mapping_tag_question";
		$condition = "t_mapping_tag_question.fk_question in (".$qids.") and t_mapping_tag_question.status <>-1";
		$item      = ['t_mapping_tag_question.fk_tag','t_mapping_tag_question.fk_group','t_mapping_tag_question.fk_question','t_tag.name'];
        $left=new stdclass;
		$left->t_tag="t_mapping_tag_question.fk_tag = t_tag.pk_tag";
		return $db->select($table, $condition, $item,'','',$left);
	}
	
	//for sphinx indexing user
	public static function getTagUserByUids($uids){
		$db        = self::InitDB("db_tag", "query");
		$table     = "t_mapping_tag_user";
		$condition = "fk_user in (".$uids.") and status <>-1";
		$item      = ['t_mapping_tag_user.fk_tag','t_mapping_tag_user.fk_group','t_mapping_tag_user.fk_user','t_tag.name'];
        $left=new stdclass;
		$left->t_tag="t_mapping_tag_user.fk_tag = t_tag.pk_tag";
		return $db->select($table, $condition, $item,'','',$left);
	}

	public static function getCourseTagListByTids($tids,$group_id){
		$db = self::InitDB("db_tag", "query");
		$table = "t_tag";
		$left=new stdclass;
		$left->t_mapping_tag_course = "t_tag.pk_tag = t_mapping_tag_course.fk_tag";
		$items = array("t_tag.name","t_tag.pk_tag","t_mapping_tag_course.fk_course","t_mapping_tag_course.fk_group");
		$condition = "t_tag.pk_tag in ($tids) and  t_mapping_tag_course.status <> -1 and t_mapping_tag_course.fk_group = $group_id";
		return $db->select($table,$condition,$items,'','',$left);
	}

	public static function getPlanTagListByTids($tids,$group_id){
		$db = self::InitDB("db_tag", "query");
		$table = "t_tag";
		$left=new stdclass;
		$left->t_mapping_tag_plan = "t_tag.pk_tag = t_mapping_tag_plan.fk_tag";
		$items = array("t_tag.name","t_tag.pk_tag","t_mapping_tag_plan.fk_plan","t_mapping_tag_plan.fk_group");
		$condition = "t_tag.pk_tag in ($tids) and  t_mapping_tag_plan.status <> -1 and t_mapping_tag_plan.fk_group = $group_id";
		return $db->select($table,$condition,$items,'','',$left);
	}
	
	public static function getUserTagListByTids($tids,$group_id){
		$db = self::InitDB("db_tag", "query");
		$table = "t_tag";
		$left=new stdclass;
		$left->t_mapping_tag_user = "t_tag.pk_tag = t_mapping_tag_user.fk_tag";
		$items = array("t_tag.name","t_tag.pk_tag","t_mapping_tag_user.fk_user","t_mapping_tag_user.fk_group");
		$condition = "t_tag.pk_tag in ($tids) and  t_mapping_tag_user.status <> -1 and t_mapping_tag_user.fk_group = $group_id";
		return $db->select($table,$condition,$items,'','',$left);
	}
	
	public static function getQuestionTagListByTids($tids,$group_id){
		$db = self::InitDB("db_tag", "query");
		$table = "t_tag";
		$left=new stdclass;
		$left->t_mapping_tag_question = "t_tag.pk_tag = t_mapping_tag_question.fk_tag";
		$items = array("t_tag.name","t_tag.pk_tag","t_mapping_tag_question.fk_question","t_mapping_tag_question.fk_group");
		$condition = "t_tag.pk_tag in ($tids) and  t_mapping_tag_question.status <> -1 and t_mapping_tag_question.fk_group = $group_id";
		return $db->select($table,$condition,$items,'','',$left);
	}
	
	public static function getTagByNameArr($nameArr){
		$db = self::InitDB("db_tag","query");
		$table=array("t_tag");
		$nameStr = implode(',',$nameArr);
		$condition = "name in ($nameStr)";
		return  $db->select($table,$condition);
	}
	
	public static function getOneBleongTagGroupByTid($tagId){
		$db = self::InitDB("db_tag", "query");
		$table = "t_tag_belong_group";
		$condition = array('fk_tag'=>$tagId);
		return $db->selectOne($table,$condition);
	}
	
	public static function getTagNameInfo($fkTagIdStr){
		$db = self::InitDB("db_tag","query");
		$table=array("t_tag");
		$condition = "pk_tag in ($fkTagIdStr) and status='0'";
		return  $db->select($table,$condition);
	}
	
	public static function getUserSelectedCourseTag($params){
		$db = self::InitDB("db_tag","query");
		$table=array("t_mapping_tag_course");
		$left=new stdclass;
		$left->t_tag = "t_tag.pk_tag=t_mapping_tag_course.fk_tag";
		$items = ["t_tag.pk_tag","t_tag.name","t_mapping_tag_course.fk_course"];
		return  $db->select($table,$params,$items,"","",$left);
	}
	public static function delMappingTagCourseData($tagConfId,$courseId){
		$db = self::InitDB("db_tag");
		$table = "t_mapping_tag_course";
		$condition = "fk_group=$tagConfId and fk_course=$courseId";
		return $db->delete($table,$condition);
	}
}
