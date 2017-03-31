<?php
class course_courseuser {

    public $ret;

	public function setResult($data='', $code=0, $msg='success'){
       $this->ret['result'] = array(
               'code' => $code,
               'msg' => $msg,
               'data' => $data
          );
		return $this->ret;
    }

	public function pageGetUserByMobileArr($inPath){

		$mobileArr = SJson::decode(utility_net::getPostData());
 		if(empty($mobileArr)){
       		 return $this->setResult('',-1, 'params error!');
        }
		$ret = user_db::getUserByMobileArr($mobileArr);
	 	if(!$ret){
            return $this->setResult('', -2, 'get user failed');
		}
        return $this->setResult($ret);
	}

    public function pageDelCourseUser($inPath){

		$db = new course_db;
        $params = SJson::decode(utility_net::getPostData());
        if(empty($params->courseId) || empty($params->classId) || empty($params->uidArr)){
            return $this->setResult('',-1, 'params error!');
        }
        $ret = $db->delCourseUser($params);
        if( $ret === false ){
            return $this->setResult('', -2, 'delete failed');
        }
		//更新数据,获取报名人数
		$course_user_total = $db->getRegistrationCountByCourse($params->courseId);
		$course_update = array("user_total"=>$course_user_total);

		$up_course = $db->updateCourse($params->courseId,$course_update);
		if($params->classId){
			$class_user_total = $db->getRegistrationCountByClass($params->classId);
			$course_update = array("user_total"=>$class_user_total);
			$up_class = $db->updateClass($params->classId,$course_update);
		}
		//更新教师学生数和教师分机构学生数
		$class = $db->getClass($params->classId);
		$student_count = count($params->uidArr);
		stat_api::reduceTeacherStatStudentCount($class['fk_user_class'], $student_count);
		stat_api::reduceTeacherStatOrgStudentCount($class['fk_user_class'],$class['fk_user'],$student_count);

        return $this->setResult($ret);
    }

	public function pageGetCourseUserByFkuser($inPath){
		$db = new course_db;
        $params = SJson::decode(utility_net::getPostData());
		 if(empty($params->uid) || empty($params->course_id)){
              return $this->setResult('',-1, 'params error!');
         }
         $ret = $db->getCourseUserByFkuser($params->course_id,$params->uid);
         if(!$ret){
             return $this->setResult('', -2, 'get data failed');
         }
         return $this->setResult($ret);

	}

	public function pageCheckUserRegisterCourse($inPath){
		$db = new course_db;
        $params = SJson::decode(utility_net::getPostData());
		 if(empty($params->course_id) || empty($params->class_id) || empty($params->uid)){
              return $this->setResult('',-1, 'params error!');
         }
         $ret = $db->checkUserRegisterCourse($params->course_id,$params->class_id,$params->uid,$params->owner_id);
         if(!$ret){
             return $this->setResult('', -2, 'get data failed');
         }
         return $this->setResult($ret);
	}

    public function pageListsByCourseClass()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $classId = isset($params['classId']) && (int)($params['classId']) ? $params['classId'] : 0;
        $page = isset($params['page']) && (int)($params['page']) ? $params['page'] : 1;
        $length = isset($params['length']) && (int)($params['length']) ? $params['length'] : 500;
        if (!$classId) return api_func::setMsg(1000);

        $res = course_db_courseUserDao::listsByClassId($classId, $page, $length);

        if (!empty($res->items)) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

    public function pageGetClassRegUserTotalNum($inPath)
    {
        $classId = isset($inPath[3]) && (int)($inPath[3]) ? (int)($inPath[3]) : 0;
        if (!$classId) return api_func::setMsg(1000);

        $res = course_db_courseUserDao::getClassRegUserTotalNum($classId);

        return api_func::setData($res);
    }

    public function pageListsByTeacherId()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (empty($params['teacherId'])) return api_func::setMsg(1000);

        $orgOwner = isset($params['orgOwner']) && (int)($params['orgOwner']) ? $params['orgOwner'] : 0;
        $scope = isset($params['scope']) && $params['scope'] ? $params['scope'] : '';
        $page = isset($params['page']) && (int)($params['page']) ? $params['page'] : 1;
        $length = isset($params['length']) && (int)($params['length']) ? $params['length'] : 500;

        $courseClassList = course_db_courseClassDao::listsByUserClassId($params['teacherId'], $orgOwner, $page, $length);

        if (empty($courseClassList->items)) return api_func::setMsg(3002);
        $courseIdArr = $userIdArr = [];
        foreach ($courseClassList->items as $courseClass) {
            if (!empty($courseClass['fk_course'])) {
                $courseIdArr[$courseClass['fk_course']] = $courseClass['fk_course'];
            }
        }

        $courseUserList = course_db_courseUserDao::listsByCourseIdArr($courseIdArr, $orgOwner, $page, $length);

        if (!empty($courseUserList->items)) {
            foreach ($courseUserList->items as $courseUser) {
                if (!empty($courseUser['fk_user'])) {
                    $userIdArr[$courseUser['fk_user']] = $courseUser['fk_user'];
                }
            }

            $res = common_user::getUsersInfo($userIdArr, $scope, $page, $length);

            if (!empty($res)) return api_func::setData($res);
        }

        return api_func::setMsg(3002);
    }

    public function pageGetCourseRegUser()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['courseId'])) return api_func::setMsg(1000);

        $orgOwner = isset($params['orgOwner']) && (int)($params['orgOwner']) ? $params['orgOwner'] : 0;
        $page = isset($params['page']) ? $params['page']+0 : 1;
        $length = isset($params['length']) ? $params['length']+0 : 500;

        $courseUserList = course_db_courseUserDao::listsByCourseIdArr((int)$params['courseId'], $orgOwner, $page, $length);
		if (empty($courseUserList->items)) return api_func::setMsg(3002);

        $userIdArr = [];
        foreach ($courseUserList->items as $user) {
            if ($user['fk_user']) {
                $userIdArr[$user['fk_user']] = $user['fk_user'];
            }
        }

        $userList = user_db_userDao::listsByUserIdArr($userIdArr);

        if (!empty($userList->items)) return api_func::setData($userList->items);

        return api_func::setMsg(3002);
    }

    public function pageGetUserRegCourse()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['userId'])) return api_func::setMsg(1000);

        $courseIdArr = [];
        if (!empty($params['courseIdArr'])) $courseIdArr = $params['courseIdArr'];
        $page = !empty($params['page']) ? (int)($params['page']) : 1;
        $length = !empty($params['length']) ? (int)($params['length']) : -1;
        $ownerId = !empty($params['ownerId']) ? (int)$params['ownerId'] : 0;

        $regCourseList = course_db_courseUserDao::getUserRegCourse((int)$params['userId'], $courseIdArr, $page, $length, $ownerId);
        if (empty($regCourseList->items)) return api_func::setMsg(3002);

        return api_func::setData($regCourseList);
    }

    public function pageUpdateCourseUserExpireTime()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['courseUserId']) || empty($params['expireTime']))
            return api_func::setMsg(1000);

        $regCourseList = course_db_courseUserDao::updateExpireTime(
            (int)$params['courseUserId'],
            $params['expireTime']
        );
        if ($regCourseList === false) return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

	public function pageGetUserCourseCount(){
		$params = SJson::decode(utility_net::getPostData(), true);
        $ownerId = isset($params['owner_id']) && (int)($params['owner_id']) ? $params['owner_id'] : 0;
		$userId = isset($params['uid']) && (int)($params['uid']) ? $params['uid'] : 0;
		if ( empty($userId) ) {
            return api_func::setMsg(1000);
        }
		$registRet = course_db_courseUserDao::getUserCourseCount($userId,$ownerId);
		if(!empty($registRet->items)){
			return api_func::setData($registRet->items[0]['course_count']);
		}else{
			return api_func::setMsg(3002);
		}
	}
	
	public function pageGetUserLivingCourse(){
		$params = SJson::decode(utility_net::getPostData(),true);
        $ownerId = isset($params['owner_id']) && (int)($params['owner_id']) ? $params['owner_id'] : 0;
		$userId = isset($params['uid']) && (int)($params['uid']) ? $params['uid'] : 0;
		$type = isset($params['type']) && (int)($params['type']) ? $params['type'] : 1;
		$startTime = isset($params['start_time']) ? $params['start_time'] : date('Y-m-d',time()).' 00:00:00';
		if ( empty($userId) ) {
            return api_func::setMsg(1000);
        }
		$registRet = course_api::getUserLivingCourseList($userId,$type,$startTime,$ownerId);
		if(!empty($registRet)){
			return api_func::setData($registRet);
		}else{
			return api_func::setMsg(3002);
		}
	}
	
	public function pageGetUserRegisterCourseList(){
		$params  = SJson::decode(utility_net::getPostData(),true);
	
        $ownerId = isset($params['owner_id']) && (int)($params['owner_id']) ? $params['owner_id'] : 0;
		$userId  = isset($params['uid']) && (int)($params['uid']) ? $params['uid'] : 0;
		$page    = isset($params['page']) && (int)($params['page']) ? $params['page'] :0;
		$length  = isset($params['length']) && (int)($params['length']) ? $params['length'] :0;
		$title   = isset($params['title'])?$params['title'] :'';
		$type    = !empty($params['courseType']) ? (int)$params['courseType'] : 0;
		if ( empty($userId) ) {
            return api_func::setMsg(1000);
        }
		$registRet = course_api::getUserRegisterCourseList($userId,$page,$length,$ownerId,$title,$type);
		if(!empty($registRet)){
            return api_func::setData($registRet);
		}else{
            return api_func::setMsg(3002);
		}
	}
	
	public function pageGetUserOrgCourse($inPath){
		$params  = SJson::decode(utility_net::getPostData(),true);
		$page   = !empty($inPath[3]) ? (int)$inPath[3] : -1;
		$length = !empty($inPath[4]) ? (int)$inPath[4] : 20;
		
		$data['ownerId']  = !empty($params['ownerId']) ? (int)$params['ownerId'] : 0;
		$data['courseId'] = !empty($params['courseId']) ? (int)$params['courseId'] : 0;
		$data['classId']  = !empty($params['classId']) ? (int)$params['classId'] : 0;
		if(empty($data['courseId']) || empty($data['ownerId'])) return api_func::setMsg(1000);
		
		$res = course_db_courseUserDao::getUserOrgCourse($data, $page, $length);

		if(empty($res->items)){
			return api_func::setMsg(3002);
		}
		return api_func::setData($res);
		
	}
        
        /* 获取报名人数列表 */
        public function pageGetCourseUserList($inPath){                
		$ret = new stdclass();
                $ret->result = new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                
                $params = SJson::decode(utility_net::getPostData());                  

                if (isset($params->condition)){ $conditions = $params->condition; }
                
		$ret->data = course_db::listCourseUser($conditions);

                return $ret;
        }
    public function pageGetClassRegUser($inPath)
    {
        $classId = isset($inPath[3]) && (int)($inPath[3]) ? (int)($inPath[3]) : 0;
        if (!$classId) return api_func::setMsg(1000);

        $res = course_db_courseUserDao::getClassRegUser($classId);

        return api_func::setData($res);
    }

    public function pageCheckUserIsReg($inPath){
        $params = SJson::decode(utility_net::getPostData(),true);
        $res = course_db_courseUserDao::checkUserIsRegFromMainDb($params["uid"], $params["course_id"]);
        return api_func::setData($res);
    }
}

