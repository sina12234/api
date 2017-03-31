<?php

class course_student
{
    public $ret;

    public function __construct()
    {
        $this->userDb = new user_db();
    }

    public function setResult($data='', $code=1, $msg='success')
    {
        $this->ret = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data
        );

        return $this->ret;
    }

    public function pageList()
    {
        $params = SJson::decode(utility_net::getPostData());
        if (empty($params)) {
             return $this->setResult('', -1006, 'param error');
        }

        $condition = isset($params->condition) && $params->condition ? $params->condition : '';
        $page = isset($params->page) && $params->page ? $params->page : 1;
        $length = isset($params->length) && $params->length ? $params->length : 100;
        $item = isset($params->item) && $params->item ? $params->item : '';
        $orderBy = isset($params->orderBy) && $params->orderBy ? $params->orderBy : '';
        $groupBy = isset($params->groupBy) && $params->groupBy ? $params->groupBy : '';
        $orgOwner = isset($params->orgOwner) && $params->orgOwner ? $params->orgOwner : 0;

        $courseDb = new course_db();
        $courseUserList = $courseDb->listCourseUser($condition, $page, $length, $item, $orderBy, $groupBy);
        $total = $courseDb->getCourseUserTotal($orgOwner);

        $uidArr = $list = [];
        if (!empty($courseUserList->items)) {
            foreach ($courseUserList->items as $v) {
                $uidArr[] = $v['fk_user'];
                $list[$v['fk_user']] = $v;
            }
        }
        $uidStr = implode(',', $uidArr);
        $userCond = "pk_user IN ({$uidStr})";

        $userItem = array('pk_user', 'birthday', 'gender', 'thumb_small', 'name', 'real_name', 'mobile');
        $userList = $this->userDb->getUserList($userCond, $userItem);

        if (!empty($userList->items)) {
            foreach ($userList->items as $user) {
                $list[$user['pk_user']] = array_merge($list[$user['pk_user']], $user);
            }
        }

        $studentCond = "fk_user IN ($uidStr)";
        $studentItem = array('fk_user', 'student_name', 'school_type','school_id');
        $studentList = $this->userDb->getStudentProfileList($studentCond, $studentItem);

        $schoolIdArr = array();
        if (!empty($studentList->items)) {
            foreach ($studentList->items as $student) {
                $list[$student['fk_user']] = array_merge($list[$student['fk_user']], $student);
                $schoolIdArr[] = $student['school_id'];
            }
        }

        $schoolIdStr = implode(',', $schoolIdArr);
        $schoolCond = "pk_school IN ($schoolIdStr)";
        $schoolItem = array('pk_school', 'school_name');
        $schoolDb = new utility_db();
        $schoolList = $schoolDb->getSchoolList($schoolCond, $schoolItem);

        if (!empty($schoolList->items)) {
            foreach ($list as &$v) {
                foreach ($schoolList->items as $school) {
                    if (!empty($v['school_id']) && ($school['pk_school'] == $v['school_id'])) {
                         $v['school_name'] = $school['school_name'];
                    }
                }
            }
        }

        return $this->setResult(['list'=>$list, 'total'=>$total]);
    }

    public function pageSearchUserData()
    {
        $param = SJson::decode(utility_net::getPostData(), true);
        /*$param = array(
            'condition' => "student_name LIKE '%赵山根%'",
            'orgOwner' => 159
        );*/

        if (empty($param)) return array();
        $condition = $param['condition'];

        $uidArr = $list = [];

        // 获取t_user下的list
        $userItem = array('pk_user', 'birthday', 'gender', 'thumb_small', 'name', 'real_name', 'mobile');
        $userList = $this->userDb->getUserList($condition, $userItem);

        if (!empty($userList->items)) {
            foreach ($userList->items as $user) {
                $uidArr[]               = $user['pk_user'];
                $list[$user['pk_user']] = $user;
            }
        }
        $uidStr = implode(',', $uidArr);

        // 获取t_course_user下的list
        $courseDb = new course_db();

        $courseUserCond = "fk_user IN ({$uidStr}) AND fk_user_owner={$param['orgOwner']}";
        $groupBy        = array('fk_user');
        $item           = array('fk_user', 'count(*) as courseNum');
        $courseUserList = $courseDb->listCourseUser($courseUserCond, '', '', $item, '', $groupBy);

        if (empty($courseUserList->items))
            return $this->setResult(['list' => []]);

        $newUidArr = $newList = [];
        foreach ($courseUserList->items as $courseUser) {
            $newUidArr[]                       = $courseUser['fk_user'];
            $newList[$courseUser['fk_user']] = array_merge($list[$courseUser['fk_user']], $courseUser);
        }

        $newUidStr = implode(',', $newUidArr);
        // t_user_student_profile list
        $studentCond        = "fk_user IN ({$newUidStr})";
        $studentItem        = array('fk_user', 'school_id');
        $StudentProfileData = $this->userDb->getStudentProfileList($studentCond, $studentItem);

        $schoolIdArr = array();
        if (!empty($StudentProfileData->items)) {
            foreach ($StudentProfileData->items as $student) {
                $schoolIdArr[]                = $student['school_id'];
                $newList[$student['fk_user']] = array_merge($newList[$student['fk_user']], $student);
            }
        }

        // t_region_school list
        $schoolIdStr = implode(',', $schoolIdArr);
        $schoolCond  = "pk_school IN ($schoolIdStr)";
        $schoolItem  = array('pk_school', 'school_name');
        $schoolDb    = new utility_db();
        $schoolList  = $schoolDb->getSchoolList($schoolCond, $schoolItem);

        if (!empty($schoolList->items)) {
            foreach ($newList as &$v) {
                foreach ($schoolList->items as $school) {
                    if (!empty($v['school_id']) && ($school['pk_school'] == $v['school_id'])) {
                        $v['school_name'] = $school['school_name'];
                    }
                }
            }
        }

        unset($list);
        return $this->setResult(['list'=>$newList]);
    }

	//机构下导出学生数据
	public function pageexportOfStudentData(){
        $params = SJson::decode(utility_net::getPostData());
        if (empty($params)) {
             return $this->setResult('', -1, 'param is error');
        }
        $condition = isset($params->condition) && $params->condition ? $params->condition : '';
        $page = isset($params->page) && $params->page ? $params->page : 1;
        $length = isset($params->length) && $params->length ? $params->length : 2000;
        $item = isset($params->item) && $params->item ? $params->item : '';
        $orderBy = isset($params->orderBy) && $params->orderBy ? $params->orderBy : '';
        $groupBy = isset($params->groupBy) && $params->groupBy ? $params->groupBy : '';
        $orgOwner = isset($params->orgOwner) && $params->orgOwner ? $params->orgOwner : 0;
        $courseDb = new course_db();
        $courseUserList = $courseDb->listCourseUser($condition, $page, $length, $item, $orderBy, $groupBy);
        $total = $courseDb->getCourseUserTotal($orgOwner);
        $uidArr = $list = [];
        if (!empty($courseUserList->items)) {
            foreach ($courseUserList->items as $v) {
                $uidArr[] = $v['fk_user'];
                $list[$v['fk_user']] = $v;
            }
        }
        $uidStr = implode(',', $uidArr);
        $userCond = "pk_user IN ({$uidStr})";
        $userItem = array('pk_user', 'birthday', 'gender', 'thumb_small', 'name');
        $userList = $this->userDb->getUserList($userCond, $userItem,$orderBy='',$page, $length);
        if (!empty($userList->items)) {
            foreach ($userList->items as $user) {
                $list[$user['pk_user']] = array_merge($list[$user['pk_user']], $user);
            }
        }
        $userMobileCond = "fk_user IN ({$uidStr})";
        $userMobileItem = array('fk_user', 'mobile');
        $userMobileList = $this->userDb->getUserMobileList($userMobileCond, $userMobileItem,$orderBy='', $page, $length);

        if (!empty($userMobileList->items)) {
            foreach ($userMobileList->items as $userMobile) {
                $list[$userMobile['fk_user']] = array_merge($list[$userMobile['fk_user']], $userMobile);
            }
        }
        $userProfileCond = "fk_user IN ({$uidStr})";
        $userProfileItem = array('fk_user', 'real_name');
        $userProfileList = $this->userDb->getUserProfileList($userProfileCond, $userProfileItem,$orderBy='', $page, $length);
        if (!empty($userProfileList->items)) {
            foreach ($userProfileList->items as $userProfile) {
                $list[$userProfile['fk_user']] = array_merge($list[$userProfile['fk_user']], $userProfile);
            }
        }
        $studentCond = "fk_user IN ($uidStr)";
        $studentItem = array('fk_user', 'student_name', 'school_type','school_id');
        $studentList = $this->userDb->getStudentProfileList($studentCond, $studentItem,$orderBy='', $page, $length);
        $schoolIdArr = array();
        if (!empty($studentList->items)) {
            foreach ($studentList->items as $student) {
                $list[$student['fk_user']] = array_merge($list[$student['fk_user']], $student);
                $schoolIdArr[] = $student['school_id'];
            }
        }
        $schoolIdStr = implode(',', $schoolIdArr);
        $schoolCond = "pk_school IN ($schoolIdStr)";
        $schoolItem = array('pk_school', 'school_name');
        $schoolDb = new utility_db();
        $schoolList = $schoolDb->getSchoolList($schoolCond, $schoolItem,$orderBy='', $page, $length);
        if (!empty($schoolList->items)) {
            foreach ($list as &$v) {
                foreach ($schoolList->items as $school) {
                    if (!empty($v['school_id']) && ($school['pk_school'] == $v['school_id'])) {
                         $v['school_name'] = $school['school_name'];
                    }
                }
            }
        }
        return $this->setResult(['list'=>$list, 'total'=>$total]);
    }

	public function pageGetstudentCourse($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3]))
		{
			return $ret;
		}
		$result = course_db::getstudentCourse($inPath[3]);
		if(empty($result->items))
		{
			$ret->result->code = -2;
			$ret->result->msg= "not data";
		}
		$ret->result->code = 0;
		$ret->result->msg= "success";
		$ret->data = $result->items;
		return $ret;
	}

	public function pageGetStudentByMobile($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3]))
		{
			return $ret;
		}
		$result = user_db::geteUserIdByLikeMobile($inPath[3]);
		if(empty($result->items))
		{
			$ret->result->code = -2;
			$ret->result->msg= "not data";
		}
		$ret->result->code = 0;
		$ret->result->msg= "success";
		$ret->data = $result->items;
		return $ret;
	}
    /* 查询学生课程数量
     * @param  $owner,uid,$status
     * @return int
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageCountStudentCourseByUid($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";
        if (empty($inPath[3])) {
            $ret->result->code = -1;
            $ret->result->msg  = "oid is empty";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData());
        $db = new course_db;
        $res= $db->countStudentCourseByUid($inPath[3], $params);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->data = $res;
        return $ret;
    }

    /* 查询学生排课数量
     * @param  $owner,uid,$status
     * @return int
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageCountStudentPlanByUid($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";
        if (empty($inPath[3])) {
            $ret->result->code = -1;
            $ret->result->msg  = "oid is empty";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData());
        $db = new course_db;
        $res= $db->countStudentPlanByUid($inPath[3], $params);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->data = $res;
        return $ret;
    }
    /* 得到一个课程报名的所有学生id和手机号
     */
    public function pageStudentsMobileByCourse($inPath){
        $params = SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";

        if(empty($params->course_id)){
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->result->code = 0;
        $db = new course_db;
        $course = $db->getCourseUser($params->course_id);
        if(empty($course->items)){
            $ret->result->msg  = "no students in course!";
            return $ret;
        }
        $uids = array();
        foreach($course->items as $k=>$v){
            array_push($uids, strval($v["user_id"]));
        }
        $uidsStr = implode(",", $uids);
        $db = new user_db;
        $data = $db->listMobilesByUserIds($uidsStr);
        if(empty($data->items)){
            return $ret;
        }
        $ret->data = $data->items;
        return $ret;
    }

	public function pageGetStudentByOwnerId($inPath){		
		$ownerId = !empty($inPath[3])?$inPath[3]:0;
		if(empty($ownerId)){
			return api_func::setMsg(1000);
		}
		$ret = course_db_courseUserDao::getStudentByOwnerId($ownerId);
		if(!empty($ret->items)){
			return api_func::setData($ret->items);
		}else{
			return api_func::setMsg(3002);
		}	
	}

}
