<?php
class comment_db{
	public static function addComment($data){
		$db = self::InitDB();
		$table = array("t_comment_course");
		$ret = $db->insert($table, $data);
		if($ret){
			return $ret;
		}
	}
	public static function getCommentTotal($fk_course){
		$db = self::InitDB("db_message","query");
		$table = array("t_comment_score");
		$item = new stdclass;
		$item->total = "count(1)";
		$condition = " status=1 and fk_course=".intval($fk_course);
		return $db->select($table, $condition, $item);
	}
	public static function updateComment($data){
		$db = self::InitDB();
		$table = array("t_comment_course");
		$condition["fk_user"] = $data->fk_user;
		$condition["fk_plan"] = $data->fk_plan;
		$item = array("comment"=>$data->comment);
		$item['fk_plan'] = $data->fk_plan;
		$item['fk_user_teacher'] = $data->fk_user_teacher;
		return $db->update($table, $condition, $item);
	}
	public static function addDetail($data){
		$db = self::InitDB();
		$table = array("t_score_course_detail");
		return $db->insert($table, $data);
	}

	//删除评论新接口
	public   static function deleteComment($userId, $planId, $courseId,$teacherId){
		$db = self::InitDB();
		$table = array('t_comment_score');
		$condition = "fk_user={$userId} AND fk_plan={$planId} AND fk_course={$courseId} and teacher_id={$teacherId} and status=1";
		$item = array('status'=>0);
		$res = $db->update($table, $condition,$item);
		if ($res === false) {
			SLog::fatal('db error[%s]', var_export($db->error(), 1));
		}
		return $res;
	}

	//新接口  减去打的分
	public static function getScores($userId, $planId, $courseId,$teacherId){
		$db = self::InitDB("db_message","query");
		$table = array('t_comment_score');
		$condition = "fk_user={$userId} AND fk_plan={$planId} AND fk_course={$courseId} and teacher_id={$teacherId} and status=1";
		$item = array('score');
		$ret = $db->selectOne($table,$condition,$item);
		if ($ret === false) {
			SLog::fatal('db error[%s]', var_export($db->error(), 1));
		}

		return $ret;
	}


	//减去课程分
	public  static function reductionCourse($fk_course,$score){
		$db = self::InitDB();
		$sql = "update t_score_course_total set score=score -{$score},total_user= total_user -1  WHERE fk_course={$fk_course}";
		return $db->execute($sql);
	}

	//老师减分
	public static function reductionTeacher($fk_user_teacher,$score){
		$db = self::InitDB();
		$sql = "update t_score_teacher_total set score=score - {$score},total_user=total_user -1 WHERE fk_user_teacher={$fk_user_teacher}";
		return $db->execute($sql);
	}

	//评分添加新接口
	public  static function addScore($data){
		$db = self::InitDB();
		$table = array("t_comment_score");
		return $db->insert($table, $data);
	}
//新获取个人评论接口
	public static function getSingleCommentScore($condition){
		$db = self::InitDB("db_message","query");
		$table = array("t_comment_score");
		return $db->selectOne($table, $condition);
	}
	public  static function updateDetail($data){
		$db = self::InitDB();
		$table = array("t_score_course_detail");
		$condition["fk_user"] = $data->fk_user;
		$condition["fk_plan"] = $data->fk_plan;
		$condition["fk_user_owner"] = $data->fk_user_owner;
		$item = array("avg_score"=>$data->avg_score, "student_score"=>$data->student_score, "desc_score"=>$data->desc_score, "explain_score"=>$data->explain_score);
		$item['fk_plan'] = $data->fk_plan;
		$item['fk_user_teacher'] = $data->fk_user_teacher;
		$item['fk_user_owner'] = $data->fk_user_owner;
		return $db->update($table, $condition, $item);
	}
	public static function addCourseTotal($data){
		$db = self::InitDB();
		$table = array("t_score_course_total");
		$condition = "fk_course=$data->fk_course";
		$item = array("total_user"=>$data->total_user, "avg_score"=>$data->avg_score, "student_score"=>$data->student_score, "desc_score"=>$data->desc_score, "explain_score"=>$data->explain_score);
		$ret = $db->insert($table, $data);
		if($ret!==false){
			return $ret;
		}
		return $db->update($table, $condition, $item);
	}
	public static function addTeacherTotal($data){
		$db = self::InitDB();
		$table = array("t_score_teacher_total");
		$condition["fk_user_teacher"] = $data->fk_user_teacher;
		$condition["fk_user_owner"] = $data->fk_user_owner;
		$item = array("total_user"=>$data->total_user, "avg_score"=>$data->avg_score, "student_score"=>$data->student_score, "desc_score"=>$data->desc_score, "explain_score"=>$data->explain_score);
		$ret =  $db->insert($table, $data);
		if($ret!==false){
			return $ret;
		}
		return $db->update($table, $condition, $item);
	}
	public static function getComment($userId, $courseId,$planId){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->comment_id = "pk_comment";
		$item->user_id = "fk_user";
		$item->course_id = "fk_course";
		$item->user_teacher = "fk_user_teacher";
		$item->plan_id = "fk_plan";
		$item->comment = "comment";
		$item->last = "last_updated";
		$table = array("t_comment_course");
		$condition["fk_user"] = $userId;
		$condition["fk_course"] = $courseId;
		if(!empty($planId)){
			$condition["fk_plan"] = $planId;
		}
		return $db->select($table, $condition, $item);
	}
	public static function getCommentNum($courseId, $user){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->total = "count(1)";
		$table = array("t_comment_course");
		$condition = "fk_course=$courseId and fk_user!=$user";
		return $db->select($table, $condition, $item);
	}
    /*
     * 查询老师对应的课的评论数
     * @author zhengtianlong
     */
    public static function getCommentNumByTeacher($courseId,$teacherId)
    {
		$db = self::InitDB("db_message","query");
        $item = new stdclass;
        $item->total = "count(1)";
        $table = array("t_comment_course");
        $condition = "fk_course={$courseId} and fk_user_teacher={$teacherId}";
        return $db->select($table, $condition, $item);
    }

	public static function getComments($courseId, $start, $num, $user){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->comment_id = "pk_comment";
		$item->user_id = "fk_user";
		$item->course_id = "fk_course";
		$item->comment = "comment";
		$item->last = "last_updated";
		$table = array("t_comment_course");
		$condition = "fk_course=$courseId and pk_comment>=$start and fk_user!=$user";
		$db->setLimit($num);
		$ret = $db->select($table, $condition, $item, "", "pk_comment desc");
		return $ret;
	}
	public static function getCommentsDesc($courseId, $num, $max, $user){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->comment_id = "pk_comment";
		$item->user_id = "fk_user";
		$item->course_id = "fk_course";
		$item->comment = "comment";
		$item->last = "last_updated";
		$table = array("t_comment_course");
		if($max){
			$condition = "fk_course=$courseId and pk_comment<$max and fk_user!=$user";
		}else{
			$condition = "fk_course=$courseId and fk_user!=$user";
		}
		$db->setLimit($num);
		$ret = $db->select($table, $condition, $item, "", "pk_comment desc");
		return $ret;
	}
	public static function getDetail($userId, $courseId,$planId){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->detail_id = "pk_comment";
		$item->user_id = "fk_user";
		$item->course_id = "fk_course";
		$item->plan_id = "fk_plan";
		$item->user_teacher = "teacher_id";
		$item->avg_score = "score";
		$item->last_updated = "last_updated";
		$table = array("t_comment_score");
		$condition["fk_user"] = $userId;
		$condition["fk_course"] = $courseId;
		if(!empty($planId)){
			$condition["fk_plan"] = $planId;
		}
		$condition["status"] = 1;
		return $db->select($table, $condition, $item);
	}
	public static function getDetailCourseTotal($courseId){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->total_user = "count(1)";
		$item->avg_score = "sum(avg_score)";
		$item->student_score = "sum(student_score)";
		$item->desc_score = "sum(desc_score)";
		$item->explain_score = "sum(explain_score)";
	//	$item->service_score = "sum(service_score)";
		$table = array("t_score_course_detail");
		$condition = "fk_course=$courseId";
		return $db->select($table, $condition, $item);
	}
	//评分接口 获取课程打分详情
	public static function getDetailCourse($courseId){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_course_total");
		$condition = "fk_course=$courseId";
		return $db->selectOne($table, $condition);
		
	}
	
	//更新coursetotal 总分
	public static function updateCourseScore($courseId,$total_user,$score){
		$db = self::InitDB("db_message","main");
		$table = array("t_score_course_total");
		$condition = "fk_course=$courseId";
		$item = array('total_user'=>$total_user,'score'=>$score); 
		return $db->update($table, $condition,$item);
	}
	
	//没有的课程进行插入
	public static function insertCourseScore($courseId,$total_user,$score){
		$db = self::InitDB("db_message","main");
		$table = array("t_score_course_total");
		$data = array('fk_course'=>$courseId,'total_user'=>$total_user,
						'score'=>$score,'avg_score'=>0,'student_score'=>0,'desc_score'=>0,'explain_score'=>0,'service_score'=>0); 
		return $db->insert($table,$data);
	}
	//获取老师 详情
	public static function getDetailTeacher($teacher_id,$fk_org){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_teacher_total");
		$condition = "fk_user_teacher=$teacher_id and fk_user_owner=$fk_org";
		return $db->selectOne($table, $condition);
	}
	
	//更新老师 总分
	public static function updateTeacherScore($condition,$item){
		$db = self::InitDB("db_message","main");
		$table = array("t_score_teacher_total");
		return $db->update($table,$condition,$item);
	}
	
	//添加老师打分
	public static function insertTeacherScore($data){
		$db = self::InitDB("db_message","main");
		$table = array("t_score_teacher_total");
		return $db->insert($table,$data);
	}
	
	//老师平均分
	public static function selTeacherAverage($teacher_id){
		$db = self::InitDB("db_message","query");
		$table =  array("t_score_teacher_total");
		$condition = "fk_user_teacher=$teacher_id";
		return $db->select($table,$condition);
	}
	
	//课程平均分
	public static function selCourseAverage($fk_course){
		$db = self::InitDB("db_message","query");
		$table = array("t_score_course_total");
		$condition = "fk_course = $fk_course";
		return $db->selectOne($table,$condition);
	}
	
	
	public static function getDetailTeacherTotal($teacherId,$userOwner){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->total_user = "count(1)";
		$item->avg_score = "sum(avg_score)";
		$item->student_score = "sum(student_score)";
		$item->desc_score = "sum(desc_score)";
		$item->explain_score = "sum(explain_score)";
	//	$item->service_score = "sum(service_score)";
		$table = array("t_score_course_detail");
		$condition["fk_user_teacher"] = $teacherId;
		$condition["fk_user_owner"] = $userOwner;
		return $db->select($table, $condition, $item);
	}
	public static function getTotal($courseId){
		$db = self::InitDB("db_message","query");
		$item = new stdclass;
		$item->total_id = "pk_total";
		$item->course_id = "fk_course";
		$item->total_user = "total_user";
		$item->avg_score = "avg_score";
		$item->student_score = "student_score";
		$item->desc_score = "desc_score";
		$item->explain_score = "explain_score";
		$item->score = "score";
		$item->last_updated = "last_updated";
		$table = array("t_score_course_total");
		$condition = "fk_course=$courseId";
		return $db->select($table, $condition, $item);
	}

    public static function getCommentList($page, $limit, $condition='', $orderBy='')
    {
		$db = self::InitDB("db_message","query");
        $table = array("t_comment_score");
        if ($page && $limit) {
            $db->setPage($page);
			$db->setLimit($limit);
            $db->setCount(true);
        }
		if(empty($condition)){
			$condition = " status=1";
		}else{
			$condition .= " and status=1";
		}
        return  $db->select($table, $condition, '', '', $orderBy);
    }
	/*
	 * huoqu teacherId
	 */
	public static function geTeacherId($params){
		$db = self::InitDB("db_message","query");
		$table = array("t_comment_score");
		$fk_course = $params['courseId'];
		$fk_plan = $params['planId'];
		$condition = "fk_course = {$fk_course} and fk_plan = {$fk_plan} and status = 1";
		$item= array('teacher_id');
		return $db->selectOne($table,$condition,$item);

	}
	public static function InitDB($dbname="db_message",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
}

