<?php
class comment_course{
	public function __construct($inPath){
		return;
	}

	public function pageAddComment($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$data = new stdclass;
		$data->fk_user = $params->user_id;
		$data->fk_course = $params->course_id;
		$data->comment = htmlentities($params->comment);
		$data->fk_user_teacher = $params->user_teacher;
		$data->fk_plan = $params->plan_id;

		//$data->fk_user_owner = $params->user_owner;
		//check user in course?
		$comment_db = new comment_db;
		$db_ret = $comment_db->addComment($data);
		if($db_ret){
			$course_db = new course_db;
			$course_info = $course_db->getCourse($params->course_id);
			stat_api::addTeacherStatComment($params->user_teacher,1);
			stat_api::addTeacherStatOrgComment($params->user_teacher,$course_info['fk_user'],1);
			//添加comment_new的统计参数
			stat_api::addPlanCommentNew($params->plan_id,1);
			stat_api::addCourseCommentNew($params->course_id,1);
			$ret->result->code = 0;
			$ret->result->data = $db_ret;
		}
		if(!$db_ret){
			$db_ret = $comment_db->updateComment($data);
			if($db_ret){
				$ret->result->code = 0;
				$ret->result->data = $db_ret;
			}
		}
		return $ret;
	}

	public function pageGetComment($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		//if(empty($params->user_id) || empty($params->course_id)){
		if(empty($params->user_id) || empty($params->course_id)){
			return $ret;
		}
		if(!empty($params->plan_id)){
			$planId = $params->plan_id;
		}else{
			$planId = 0;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getComment($params->user_id, $params->course_id, $planId);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}

    public function pageGetCommentList()
    {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";

        $params = SJson::decode(utility_net::getPostData());

        $page = !empty($params->page) ? $params->page : 1;
        $limit = !empty($params->limit) ? $params->limit : 1;
        $condition = !empty($params->condition) ? $params->condition : '';
        $orderBy = !empty($params->orderBy) ? $params->orderBy : '';

        $comment_db = new comment_db;
        $data = $comment_db->getCommentList($page, $limit, $condition, $orderBy);

        if(empty($data->items)){
            $ret->result->msg = 'get data failed';
            return $ret;
        }

        $userIdArr = array_column($data->items, 'fk_user');
        $userLists = user_db_userDao::listsByUserIdArr($userIdArr);
        if (!empty($userLists->items)) {
            foreach ($userLists->items as $user) {
                $userInfo[$user['pk_user']] = [
                    'userName' => $user['name'],
                    'thumbMed' => $user['thumb_med'],
                    'thumbBig' => $user['thumb_big']
                ];
            }
        }

        foreach($data->items as $i => &$item){
            $item["user_name"]  = !empty($userInfo[$item["fk_user"]]["userName"]) ? $userInfo[$item["fk_user"]]["userName"] : '';
            $item["user_thumb"] = !empty($userInfo[$item["fk_user"]]["thumbMed"]) ? $userInfo[$item["fk_user"]]["thumbMed"] : '';
            $item["user_big"]   = !empty($userInfo[$item["fk_user"]]["thumbBig"]) ? $userInfo[$item["fk_user"]]["thumbBig"] : '';
        }

        return $data;
    }

	public function pageGetCommentNum($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getCommentNum($params->course_id, $params->user_id);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}

    public function pageGetCommentNumByTeacher($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getCommentNumByTeacher($params->course_id, $params->teacher_id);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}

	public function pageGetComments($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id) || !isset($params->start) || empty($params->num)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getComments($params->course_id, $params->start, $params->num, $params->user_id);
		if(empty($data->items)){
			return $ret;
		}
		foreach($data->items as $i => &$item){
			$user = user_db::getUser($item["user_id"]);
			$item["user_name"] = $user["name"];
			$item["user_thumb"] = $user["thumb_small"];
			//还差学到哪了
		}
		$ret->data = $data->items;
		return $ret;
	}

	public function pageGetCommentsDesc($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id) || empty($params->num)){
			return $ret;
		}
		$comment_db = new comment_db;
		if(isset($params->max)){
			$data = $comment_db->getCommentsDesc($params->course_id, $params->num, $params->max, $params->user_id);
		}else{
			$data = $comment_db->getCommentsDesc($params->course_id, $params->num, 0, $params->user_id);
		}
		if(empty($data->items)){
			return $ret;
		}
		foreach($data->items as $i => &$item){
			$user = user_db::getUser($item["user_id"]);
			$item["user_name"] = $user["name"];
			$item["user_thumb"] = $user["thumb_med"];
			//还差学到哪了
		}
		$ret->data = $data->items;
		return $ret;
	}

	public function pageAddDetail(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$data = new stdclass;
		$data->fk_user = $params->user_id;
		$data->fk_course = $params->course_id;
		$data->avg_score = round((($params->student_score + $params->desc_score + $params->explain_score) / 3),1);
		$data->student_score = $params->student_score;
		$data->desc_score = $params->desc_score;
		$data->explain_score = $params->explain_score;
		$data->fk_user_teacher = $params->user_teacher;
		$data->fk_plan = $params->plan_id;
		$data->fk_user_owner = $params->user_owner;

		$comment_db = new comment_db;
		$db_ret = $comment_db->addDetail($data);

		if($db_ret !== false){
			$ret->result->code = 0;
			$ret->result->data = $db_ret;
			return $ret;
		}else{
			return $ret;
		}
	}

	public function pageGetDetail($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
	//	$params->plan_id = 397;
		if(empty($params->user_id) || empty($params->course_id)){
			return $ret;
		}
		if(!empty($params->plan_id)){
			$planId = $params->plan_id;
		}else{
			$planId = 0;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getDetail($params->user_id, $params->course_id,$planId);
		if(empty($data->items)){
			return $ret;
		}
		$ret->result->code=0;
		$ret->result->msg="";
		$ret->data = $data->items;
		return $ret;
	}
	//添加打分总数  给课程  教师 
	public function pageAddCommentScore($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="fail";
		if(empty($params->fk_course)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getDetailCourse($params->fk_course);
		if($data){
			//有现有的课程打分 则更新打分及打分的人数加1
			$total_user = $data['total_user'] + 1;
			$score      = $data['score'] + $params->score;
			$ret = $comment_db->updateCourseScore($params->fk_course,$total_user,$score);
			if($ret) return json_encode(['msg'=>'success','code'=>1]);
		}elseif(empty($data)){
			//添加课程打分
			$total_user = 1;
			$score      = $params->score;
			$ret = $comment_db->insertCourseScore($params->fk_course,$total_user,$score);
			if($ret) return json_encode(['msg'=>'success','code'=>1]);
		}
	}
	//给老师打加分
	public function pageAddTeacherScore(){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="fail";
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->teacher_id) || empty($params->fk_user_owner)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getDetailTeacher($params->teacher_id,$params->fk_user_owner);
		if($data){
			//现有老师则进行打分 update
			$score       = $data['score'] + $params->score;
			$total_user  = $data['total_user'] + 1;
			$condition   = array('fk_user_teacher'=>$params->teacher_id,'fk_user_owner'=>$params->fk_user_owner);
			$item        = array('score'=>$score,'total_user'=>$total_user);
			$ret = $comment_db->updateTeacherScore($condition,$item);
			if($ret) return json_encode(['msg'=>'success','code'=>1]);
		}elseif(empty($data)){
			//没有老师则    现在添加打分
			$score       = $data['score'] + $params->score;
			$total_user  = $data['total_user'] + 1;
			$data =array(
				'score'     		=>$score,
				'total_user'		=>$total_user,
				'fk_user_teacher'   =>$params->teacher_id,
				'fk_user_owner'		=>$params->fk_user_owner,
				'avg_score'   		=>0,
				'student_score'		=>0,
				'desc_score'		=>0,
				'explain_score'		=>0,
				'service_score'		=>0
			);
			$ret = $comment_db->insertTeacherScore($data);
			if($ret) return json_encode(['msg'=>'success','code'=>1]);
		}
		
	}
	
	//老师平均分
	public function pageTeacherAverage(){
		$ret = new stdClass();
		$ret->code = -1;
		$ret->msg = 'faild';
		$params=SJson::decode(utility_net::getPostData());
		$comment_db = new comment_db;
		$data = $comment_db->selTeacherAverage($params->fk_user_teacher);
		if($data){
			$ret->result = $data;
			$ret->code = 1;
			$ret->msg = 'success';
		}
		return $ret;
	}
	
	//课程平均分
	public function pageCourseAverage(){
		$ret = new stdClass();
		$ret->code = -1;
		$ret->msg = 'faild';
		$params=SJson::decode(utility_net::getPostData());
		$comment_db = new comment_db;
		$data = $comment_db->selCourseAverage($params->fk_course);
		if($data){
			$ret->result = $data;
			$ret->code = 1;
			$ret->msg = 'success';
		}
		return $ret;
	}
	
	public function pageAddTotal($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="fail";
		if(empty($params->course_id)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getDetailCourseTotal($params->course_id);
		if(empty($data->items)){
			return $ret;
		}
		$item = $data->items[0];
		$data = new stdclass;
		$data->fk_course = $params->course_id;
	//	$data->fk_user = $params->user_id;
		$data->total_user = $item["total_user"];
		$data->avg_score = $item["avg_score"];
		$data->student_score = $item["student_score"];
		$data->desc_score = $item["desc_score"];
		$data->explain_score = $item["explain_score"];
	//	$data->service_score = $item["service_score"];
		$db_ret = $comment_db->addCourseTotal($data);
	//	$datateacher = $comment_db->getDetailTeacherTotal($params->);
		$datateacher = $comment_db->getDetailTeacherTotal($params->user_teacher,$params->user_owner);
		if(empty($datateacher->items)){
			return $ret;
		}
		$item2 = $datateacher->items[0];
		$datateacher = new stdclass;
		$datateacher->fk_user_teacher = $params->user_teacher;
		$datateacher->fk_user_owner = $params->user_owner;
		$datateacher->total_user = $item2["total_user"];
		$datateacher->avg_score = $item2["avg_score"];
		$datateacher->student_score = $item2["student_score"];
		$datateacher->desc_score = $item2["desc_score"];
		$datateacher->explain_score = $item2["explain_score"];
	//	$data->service_score = $item["service_score"];
		$db_ret = $comment_db->addTeacherTotal($datateacher);

		if($db_ret!==false){
			//修改教师和分机构教师评分和评分人数
			stat_api::setTeacherStatAvgScore($params->user_teacher);
			stat_api::setTeacherStatOrgAvgScore($params->user_teacher,$params->user_owner,$item2['avg_score'],$item2['total_user']);

			$ret->result->code = 0;
			$ret->result->data = $db_ret;
		}
		return $ret;
	}

	public function pageGetTotal($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getTotal($params->course_id);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}
	public function pageGetCommentTotal($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id)){
			return $ret;
		}
		$comment_db = new comment_db;
		$data = $comment_db->getCommentTotal($params->course_id);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}
    public function pageGetScoreCourseTotalList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['courseIdArr'])) return api_func::setMsg(1000);

        $page             = isset($params['page']) && (int)$params['page'] ? (int)$params['page'] : 1;
        $length           = isset($params['length']) && (int)$params['length'] ? (int)$params['length'] : -1;
        $scoreCourseTotal = message_db_scoreCourseTotalDao::listByCourseIdArr($params['courseIdArr'], $page, $length);
        if (empty($scoreCourseTotal->items)) return api_func::setMsg(3002);

        return api_func::setData($scoreCourseTotal);
    }

	//获取老师打分
	public function pageGetTeacherScoreComment(){
		$params=SJson::decode(utility_net::getPostData(), true);
		if (empty($params['teacherId'])) return api_func::setMsg(1000);
		$scoreCourseDetail = message_db_commentScoreDao::lists($params);
		$result['data'] = $courseIdArr = $userIdArr =  [];

		if (!empty($scoreCourseDetail->items)) {
			foreach ($scoreCourseDetail->items as $v) {
				if ($v['fk_plan'] && $v['fk_user'] && $v['fk_course']) {
					$kv = "{$v['fk_user']}_{$v['fk_plan']}";
					$result['data'][$kv] = $v;
					$courseIdArr[$v['fk_course']] = $v['fk_course'];
					$userIdArr[$v['fk_user']] = $v['fk_user'];
				}
			}
		}

		$courseIdArr = array_values($courseIdArr);
		$userIdArr = array_values($userIdArr);

		$result['data'] = array_values($result['data']);
		$result['courseIdArr'] = $courseIdArr;
		$result['userIdArr'] = $userIdArr;
		$result['totalPage'] = $scoreCourseDetail->totalPage;
		$result['totalSize'] = $scoreCourseDetail->totalSize;
		return api_func::setData($result);

	}

    public function pageGetTeacherCourseComment()
    {
        $params=SJson::decode(utility_net::getPostData(), true);

        if (empty($params['teacherId'])) return api_func::setMsg(1000);

        $scoreCourseDetail = message_db_scoreCourseDetailDao::lists($params);

        $result['data'] = $courseIdArr = $userIdArr =  [];

        if (!empty($scoreCourseDetail->items)) {
            foreach ($scoreCourseDetail->items as $v) {
                if ($v['fk_plan'] && $v['fk_user'] && $v['fk_course']) {
                    $kv = "{$v['fk_user']}_{$v['fk_plan']}";
                    $result['data'][$kv] = $v;
                    $courseIdArr[$v['fk_course']] = $v['fk_course'];
                    $userIdArr[$v['fk_user']] = $v['fk_user'];
                }
            }
        }

        $courseIdArr = array_values($courseIdArr);
        $userIdArr = array_values($userIdArr);

        $result['data'] = array_values($result['data']);
        $result['courseIdArr'] = $courseIdArr;
        $result['userIdArr'] = $userIdArr;
		$result['totalPage'] = $scoreCourseDetail->totalPage;

		return api_func::setData($result);
    }

    public function pageListCommentsByCourseId()
    {
        $params=SJson::decode(utility_net::getPostData(), true);
        if (empty($params['courseId'])) return api_func::setMsg(1000);

        $page = isset($params['page']) && (int)$params['page'] ? (int)$params['page'] : 1;
        $length = isset($params['length']) && (int)$params['length'] ? (int)$params['length'] : 20;
        $teacherId = isset($params['teacherId']) && (int)$params['teacherId'] ? $params['teacherId'] : 0;

        $scoreCourseDetail = message_db_scoreCourseDetailDao::listByCourseId($params['courseId'], $teacherId, $page, $length);

        $result['data'] = $planIdArr = $userIdArr =  [];
        $totalPage = $totalSize = 0;

        if (!empty($scoreCourseDetail->items)) {
            $totalPage = $scoreCourseDetail->totalPage;
            $totalSize = $scoreCourseDetail->totalSize;
            foreach ($scoreCourseDetail->items as $v) {
                if ($v['fk_user'] && $v['fk_plan']) {
                    $kv = "{$v['fk_user']}_{$v['fk_plan']}";
                    $result['data'][$kv] = $v;
                    $planIdArr[$v['fk_plan']] = $v['fk_plan'];
                    $userIdArr[$v['fk_user']] = $v['fk_user'];
                }
            }
        }

        $planIdArr = array_values($planIdArr);
        $userIdArr = array_values($userIdArr);

        if (count($planIdArr)<1 || count($userIdArr)<1)
            return api_func::setMsg(3002);

        $result['data'] = array_values($result['data']);
        $result['totalPage'] = $totalPage;
        $result['totalSize'] = $totalSize;
        $result['planIdArr'] = $planIdArr;
        $result['userIdArr'] = $userIdArr;

        return api_func::setData($result);
    }

	//课程打分列表
	public function pageGetCourseCommentList()
	{
		$params=SJson::decode(utility_net::getPostData(), true);
		if (empty($params['courseId'])) return api_func::setMsg(1000);

		$page = isset($params['page']) && (int)$params['page'] ? (int)$params['page'] : 1;
		$length = isset($params['length']) && (int)$params['length'] ? (int)$params['length'] : 20;
		$teacherId = isset($params['teacherId']) && (int)$params['teacherId'] ? $params['teacherId'] : 0;

		$scoreCourseDetail = message_db_commentScoreDao::listByCourseId($params['courseId'], $teacherId, $page, $length);

		$result['data'] = $planIdArr = $userIdArr =  [];
		$totalPage = $totalSize = 0;

		if (!empty($scoreCourseDetail->items)) {
			$totalPage = $scoreCourseDetail->totalPage;
			$totalSize = $scoreCourseDetail->totalSize;
			foreach ($scoreCourseDetail->items as $v) {
				if ($v['fk_user'] && $v['fk_plan']) {
					$kv = "{$v['fk_user']}_{$v['fk_plan']}";
					$result['data'][$kv] = $v;
					$planIdArr[$v['fk_plan']] = $v['fk_plan'];
					$userIdArr[$v['fk_user']] = $v['fk_user'];
				}
			}
		}

		$planIdArr = array_values($planIdArr);
		$userIdArr = array_values($userIdArr);

		if (count($planIdArr)<1 || count($userIdArr)<1)
			return api_func::setMsg(3002);

		$result['data'] = array_values($result['data']);
		$result['totalPage'] = $totalPage;
		$result['totalSize'] = $totalSize;
		$result['planIdArr'] = $planIdArr;
		$result['userIdArr'] = $userIdArr;

		return api_func::setData($result);
	}


	public function pageCheckIsComment()
	{
		$params = SJson::decode(utility_net::getPostData(), true);
		$r = api_func::isValidId(['courseId', 'uid', 'planId'], $params);
		if (!empty($r['code'])) return api_func::setMsg($r['code']);

		$res = message_db_commentCourseDao::checkIsComment($r['courseId'], $r['uid'], $r['planId']);

		if ($res === false) return api_func::setMsg(3002);

		return api_func::setData($res);
	}

	//评分检测新接口
	public function pageCheckIsAddScore(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$r = api_func::isValidId(['courseId', 'uid', 'planId'], $params);
		if (!empty($r['code'])) return api_func::setMsg($r['code']);
		$res = message_db_commentScoreDao::CheckIsAddScore($r['courseId'], $r['uid'], $r['planId']);
		if ($res === false) return api_func::setMsg(3002);
		return api_func::setData($res);
		
	}
	//评分添加
	public function pageAddScore(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$comment_db = new comment_db;
		$db_ret = $comment_db->addScore($params);
		if($db_ret !== false){
			$ret->result->code = 0;
			$ret->result->data = $db_ret;
			return $ret;
		}else{
			return $ret;
		}
		
	}
		//新的获取个人评分接口
	public function pageGetSingleCommentScore(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$comment_db = new comment_db;
		$db_ret = $comment_db->getSingleCommentScore($params);
		if($db_ret !== false){
			$ret->result->code = 0;
			$ret->result->data = $db_ret;
			return $ret;
		}else{
			return $ret;
		}
	}
	
	public function pageCheckIsCommentByPlanId()
	{
		$params = SJson::decode(utility_net::getPostData(), true);

		if (empty($params['planIdArr']))
			return api_func::setMsg(1000);

		if (!isset($params['uid']) || !(int)($params['uid']))
			return api_func::setMsg(1000);


		$planIdStr = implode(',', $params['planIdArr']);

		$res = message_db_commentScoreDao::checkIsCommentByPlanId($planIdStr, $params['uid']);

		if ($res === false) return api_func::setMsg(3002);

		return api_func::setData($res);
	}

	/*public function pageDelComment()
	{
		$params = SJson::decode(utility_net::getPostData(), true);
		$r = api_func::isValidId(['userId', 'planId', 'courseId'], $params);
		if (!empty($r['code'])) return api_func::setMsg($r['code']);

		if (message_db_commentCourseDao::delComment($r['userId'], $r['planId'], $r['courseId'])) {
			if (message_db_scoreCourseDetailDao::delCommentScoreDetail($r['userId'], $r['planId'], $r['courseId']) === false) {
				SLog::fatal('when delete t_course_comment success but failed in t_score_course_detail,params[%s]', var_export($params, 1));
			}

			return api_func::setMsg(0);
		}

		return api_func::setMsg(1);
	}*/

	//删除评论新接口
	public function pageDeleteComment()
	{
		$params = SJson::decode(utility_net::getPostData(), true);
		$r = api_func::isValidId(['userId', 'planId', 'courseId','teacherId'], $params);
		if (!empty($r['code'])) return api_func::setMsg($r['code']);
		$comment_db = new comment_db();
		$score = comment_db::getScores($r['userId'], $r['planId'], $r['courseId'],$r['teacherId']);
		if ($comment_db->deleteComment($r['userId'], $r['planId'], $r['courseId'],$r['teacherId'])) {
			//减去换一个人  减一个分
			$comment_db->ReductionCourse($r['courseId'],$score['score']);
			$comment_db->ReductionTeacher($r['teacherId'],$score['score']);
			return api_func::setMsg(0);
		}
		return api_func::setMsg(1);
	}

	/*
	 * 获取teacherId
	 */
	public function pageGetTeacherId(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$r = api_func::isValidId(['userId', 'planId', 'courseId'], $params);
		if (!empty($r['code'])) return api_func::setMsg($r['code']);
		$rest = comment_db::geTeacherId($params);
		return json_encode($rest);
	}

	/*
	 * 获取 课程评论列表
	 */
	public function pageCommentList()
	{
		$params=SJson::decode(utility_net::getPostData(), true);
		if (empty($params['courseId'])) return api_func::setMsg(1000);

		$page = isset($params['page']) && (int)$params['page'] ? (int)$params['page'] : 1;
		$length = isset($params['length']) && (int)$params['length'] ? (int)$params['length'] : 20;
		$teacherId = isset($params['teacherId']) && (int)$params['teacherId'] ? $params['teacherId'] : 0;
		$time = isset($params['time']) && (int)$params['time'] ? $params['time'] : '';
		$score = isset($params['score']) && (int)$params['score'] ? $params['score'] : '';

		$scoreCourseDetail = message_db_commentScoreDao::commentList($params['courseId'], $teacherId, $page, $length,$time,$score);

		$result['data'] = $planIdArr = $userIdArr =  [];
		$totalPage = $totalSize = 0;

		if (!empty($scoreCourseDetail->items)) {
			$totalPage = $scoreCourseDetail->totalPage;
			$totalSize = $scoreCourseDetail->totalSize;
			foreach ($scoreCourseDetail->items as $v) {
				if ($v['fk_user'] && $v['fk_plan']) {
					$kv = "{$v['fk_user']}_{$v['fk_plan']}";
					$result['data'][$kv] = $v;
					$planIdArr[$v['fk_plan']] = $v['fk_plan'];
					$userIdArr[$v['fk_user']] = $v['fk_user'];
				}
			}
		}

		$planIdArr = array_values($planIdArr);
		$userIdArr = array_values($userIdArr);

		if (count($planIdArr)<1 || count($userIdArr)<1)
			return api_func::setMsg(3002);

		$result['data'] = array_values($result['data']);
		$result['totalPage'] = $totalPage;
		$result['totalSize'] = $totalSize;
		$result['planIdArr'] = $planIdArr;
		$result['userIdArr'] = $userIdArr;

		return api_func::setData($result);
	}

	/*
	 * 插入老师回复评论的信息
	 *
	 */
	public function pageInsertCommentReplay(){
		$params=SJson::decode(utility_net::getPostData(), true);
		$params['replay_time'] = date('Y-m-d H:i:s');
		$ret = message_db_commentManageDao::InsertCommentReplay($params);
		if($ret){
			return json_encode(array('code'=>0,'result'=>$ret),JSON_UNESCAPED_UNICODE);
		}
		return json_encode(['code'=>1,'message'=>'插入失败'],JSON_UNESCAPED_UNICODE);
	}

	/*
	 * 老师删除自己的回复
	 */
	public function pageDeleteCommentReplay(){
		$params=SJson::decode(utility_net::getPostData(), true);
		$ret =  message_db_commentManageDao::DeleteCommentReplay($params);
		if($ret){
			return json_encode(array('code'=>0,'result'=>$ret,'message'=>'删除成功'),JSON_UNESCAPED_UNICODE);
		}
		return json_encode(['code'=>1,'message'=>'删除失败'],JSON_UNESCAPED_UNICODE);
	}

	/*
	 * 检测老师是否评论过
	 */

	public function pageCheckIsReplay(){
		$params = SJson::decode(utility_net::getPostData(),true);
		$ret = message_db_commentManageDao::CheckIsReplay($params);
		if($ret){
			return $ret ;
		}
		return json_encode(['code'=>1,'message'=>'未评论'],JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 获取对应老师的回复
	 */
	public function pageShowReplay(){
		$params = SJson::decode(utility_net::getPostData(),true);
		 $ret = message_db_commentManageDao::ShowReplay($params);
		if($ret){
			return $ret ;
		}
		return  json_encode(['code'=>1,'message'=>'没有匹配数据'],JSON_UNESCAPED_UNICODE);
	}
}
