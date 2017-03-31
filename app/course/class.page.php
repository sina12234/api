<?php
class course_class{
	/**
	 * @author fanbin
	 * @return 列取班级的信息
	 */
	public function pageList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$courseId = (int)$inPath[3];
		$course_api = new course_api;
		$classlist = $course_api->getclasslist($courseId);
		if($classlist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the class is not found!";
			return $ret;
		}
		return $classlist;
	}
	/**
	 * 根据courseids   
	 * 讲课老师id 
	 * 机构id 
	 * 获取class列表
	 * @author fanbin
	 * @return 列取班级的信息
	 */
	public function pageClassListByCourseIds($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not CCund!";
		$params = SJson::decode(utility_net::getPostData());
		$page = isset($params->page) && $params->page ? $params->page : 1;
		$length = isset($params->length) && $params->length ? $params->length : 400;
		$data = array();
		if(!empty($params->cond->course_ids)){
			foreach($params->cond->course_ids as $k=>$v){
				$data["course_ids"][]= $v;
			}
		}else{
			return $ret;
		}
		
		if(empty($params->user_class_id)){
			$user_class_id = 0;
		}else{
			$user_class_id = $params->user_class_id;
		}
		if(empty($params->user_id)){
			$user_id = 0;
		}else{
			$user_id = $params->user_id;
		}
		if(($user_id==0)&&($user_class_id==0)){
			return $ret;
		}
		$course_api = new course_api;
		$orderby = array();
		
		$orderby['st'] = !empty($params->cond->st) ? $params->cond->st : '';
		$data['ut'] = isset($params->cond->ut) ? $params->cond->ut : '0';
		$data['course_type'] = !empty($params->cond->course_type) ? $params->cond->course_type : '';
		
		$classlist = $course_api->classListByCourseIds($user_id,$user_class_id,$data,$orderby,$page,$length);
		if($classlist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the class is not found!";
			return $ret;
		}
		return $classlist;
	}
	/**
	 * 根据课程id   
	 * 讲课老师id 
	 * 机构id 
	 * @author fanbin
	 * @return 列取班级的信息
	 */
	public function pageListByCond($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";

		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_id)){
			$course_id = 0;
		}else{
			$course_id = $params->course_id;
		}
		if(empty($params->user_class_id)){
			$user_class_id = 0;
		}else{
			$user_class_id = $params->user_class_id;
		}
		if(empty($params->user_id)){
			$user_id = 0;
		}else{
			$user_id = $params->user_id;
		}
		if(($user_id==0)&&($user_class_id==0)&&($course_id==0)){
			return $ret;
		}
		$course_api = new course_api;
		$classlist = $course_api->classListByCond($user_id,$user_class_id,$course_id);
		if($classlist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the class is not found!";
			return $ret;
		}
		return $classlist;
	}
	public function pageGet($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$class_id = (int)$inPath[3];		
		$course_api = new course_api;
		$classlist = $course_api->getclass($class_id);
		if($classlist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the class is not found!";
			return $ret;
		}
		return $classlist;
	}
	public function pagedel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The sid is not found!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		if (empty($inPath[4]) || !is_numeric($inPath[4])) {
			return $ret;
		}
		$cid = (int)$inPath[4];
		$class_id = (int)$inPath[3];
		//在这里强制转换了下
		$course_api = new course_api;
		$course_db = new course_db;
		$old_class = $course_api->getclass($class_id);
		$course_info = $course_db->getCourse($cid);
		$plan_info = $course_db->getPlanTeacherByClassId($class_id);
	//删除班级信息
		$ret_class = $course_api->delClass($cid,$class_id,$sid = null);

		if($ret_class == false){
			$ret->result->code = -2;
			$ret->result->msg = "fail del";
		}elseif($ret_class == "failed"){
			$ret->result->code = -4;
			$ret->result->msg ="student has change";
		}else{
			//修改班主任和代课教师和分机构总人数和课程数
			foreach($plan_info->items as $po){
				if($po['fk_user_plan'] != $old_class['user_class_id']){
					if($course_info['status'] == 3){
						stat_api::reduceTeacherStatCourseCompleteCountByClass($po['fk_user_plan'],$old_class['course_id']);
						stat_api::reduceTeacherStatOrgCourseCompleteCountByClass($po['fk_user_plan'],$course_info['fk_user'],$old_class['course_id']);
					}else{
						stat_api::reduceTeacherStatOrgCourseRemainCount($po['fk_user_plan'],$course_info['fk_user'],$old_class['course_id']);
						stat_api::reduceTeacherStatCourseRemainCount($po['fk_user_plan'],$old_class['course_id']);
					}
					if($course_info['admin_status'] == -2){
						stat_api::reduceTeacherStatCourseOffCountByClass($po['fk_user_plan'],$old_class['course_id']);
						stat_api::reduceTeacherStatOrgCourseOffCountByClass($po['fk_user_plan'],$course_info['fk_user'],$old_class['course_id']);
					}elseif($course_info['admin_status'] == 1){
						stat_api::reduceTeacherStatCourseOnCountByClass($po['fk_user_plan'],$old_class['course_id']);
						stat_api::reduceTeacherStatOrgCourseOnCountByClass($po['fk_user_plan'],$course_info['fk_user'],$old_class['course_id']);
					}
				}
			}
			if($course_info['status'] == 3){
				stat_api::reduceTeacherStatCourseCompleteCountByClass($old_class['user_class_id'],$old_class['course_id']);
				stat_api::reduceTeacherStatOrgCourseCompleteCountByClass($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
			}else{
				stat_api::reduceTeacherStatCourseRemainCount($old_class['user_class_id'],$old_class['course_id']);
				stat_api::reduceTeacherStatOrgCourseRemainCount($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
			}
			if($course_info['admin_status'] == -2){
				stat_api::reduceTeacherStatCourseOffCountByClass($old_class['user_class_id'],$old_class['course_id']);
				stat_api::reduceTeacherStatOrgCourseOffCountByClass($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
			}elseif($course_info['admin_status'] == 1){
				stat_api::reduceTeacherStatCourseOnCountByClass($old_class['user_class_id'],$old_class['course_id']);
				stat_api::reduceTeacherStatOrgCourseOnCountByClass($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
			}
			stat_api::reduceTeacherStatOrgStudentCount($old_class['user_class_id'],$course_info['fk_user'],$old_class['user_total']);
			stat_api::reduceTeacherStatStudentCount($old_class['user_class_id'], $old_class['user_total']);
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		//$updateCourseMaxUser = $this->updateMaxuser($old_class['course_id']);
		$updateCourseMaxUser = $this->updateMaxuser($cid);
		return $ret;
	}
	public function pageGenClassId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$cid = $inPath[3];
		//$user_db = new user_db;
		//$user = $user_db->getUser($cid);
		//TODO判断老师是否有权限
//		define("DEBUG",true);
		$course_api = new course_api;
		$class_id = $course_api->genclassId($cid);
		if(!empty($class_id)){
		//	unset($ret->result);
			$ret->data=array("cid"=>(int)$cid,"class_id"=>(int)$class_id);
			$ret->result->code = 0;
			$ret->result->msg= "success";
		}else{
			$ret->result->code = -2;
		}
		return $ret;
	}
	public function pageUpdate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "The id is not found!";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$class_id = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData());
//		define("DEBUG",true);
		$class = array();
		if(empty($params->name)){
			$ret->result->code = -3;
			$ret->result->msg = "the class name is empty!";
			return $ret;			
			exit();
		}else{
		//	$class = $params;
			$array_type = array("min"=>"1","max"=>"2");
	  		$time = date("Y-m-d H:i:s");
			if(isset($params->user_id)){
				$class["fk_user"] = $params->user_id;
			}
			$class["fk_user_class"] = $params->user_class_id;
			if(!empty($params->name)){
				$class["name"] = $params->name;
			}
			$class["descript"] = empty($params->descript)? '请输入章节描述':$params->descript;
			$class["last_updated"] = $time;
			$class["max_user"] = $params->max_user;
			if(isset($params->min_user)){
				$class["min_user"] = $params->min_user;
			}
			$class["status"] = $params->status;
			if(isset($params->type)){
				$class["type"] = $array_type[$params->type];
			}
			if(!empty($params->region_level0)){
				$class["region_level0"] = $params->region_level0;
			}
			if(!empty($params->region_level1)){
				$class["region_level1"] = $params->region_level1;
			}
			if(!empty($params->region_level2)){
				$class["region_level2"] = $params->region_level2;
			}
			if(!empty($params->address)){
				$class["address"] = $params->address;
			}
		}
				
		//调用统计接口
		$course_api = new course_api;
		$course_db = new course_db;
		$old_class = $course_api->getclass($class_id);
		$course_info = $course_db->getCourse($old_class['course_id']);
		if(!empty($class['fk_user_class']) && ($old_class['user_class_id'] != $class['fk_user_class'])){
			stat_api::addTeacherStatStudentCount($class['fk_user_class'], $old_class['user_total']);
			if($course_info['status'] == 3){
				stat_api::addTeacherStatCourseCompleteCountByClass($class['fk_user_class'],$old_class['course_id']);
				stat_api::addTeacherStatOrgCourseCompleteCountByClass($class['fk_user_class'],$course_info['fk_user'],$old_class['course_id']);
			}else{
				stat_api::addTeacherStatCourseRemainCount($class['fk_user_class'],$old_class['course_id']);
				stat_api::addTeacherStatOrgCourseRemainCount($class['fk_user_class'],$course_info['fk_user'],$old_class['course_id']);
			}
			if($course_info['admin_status'] == -2){
				stat_api::addTeacherStatCourseOffCountByClass($class['fk_user_class'],$old_class['course_id']);
				stat_api::addTeacherStatOrgCourseOffCountByClass($class['fk_user_class'],$course_info['fk_user'],$old_class['course_id']);
			}elseif($course_info['admin_status'] == 1){
				stat_api::addTeacherStatCourseOnCountByClass($class['fk_user_class'],$old_class['course_id']);
				stat_api::addTeacherStatOrgCourseOnCountByClass($class['fk_user_class'],$course_info['fk_user'],$old_class['course_id']);
			}
			stat_api::addTeacherStatOrgStudentCount($class['fk_user_class'],$course_info['fk_user'],$old_class['user_total']);
		}
		$updateRet = $course_api->updateclass($class_id,$class);
		//define("DEBUG",true);
		if($updateRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$updateCourseMaxUser = $this->updateMaxuser($old_class["course_id"]);
			//修改排课老师
			if(!empty($old_class['user_class_id']) && ($old_class['user_class_id'] != $class['fk_user_class'])){
				$updata['fk_user_plan'] = $class['fk_user_class'];
				$courseDb = new course_db;
				$courseDb->updatePlanTeacher($old_class['course_id'],$class_id, $old_class['user_class_id'],$updata);
			}	
			if(!empty($class['fk_user_class']) && ($old_class['user_class_id'] != $class['fk_user_class'])){
				if($course_info['status'] == 3){
					stat_api::reduceTeacherStatCourseCompleteCountByClass($old_class['user_class_id'],$old_class['course_id']);
					stat_api::reduceTeacherStatOrgCourseCompleteCountByClass($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
				}else{
					stat_api::reduceTeacherStatCourseRemainCount($old_class['user_class_id'],$old_class['course_id']);
					stat_api::reduceTeacherStatOrgCourseRemainCount($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
				}
				if($course_info['admin_status'] == -2){
					stat_api::reduceTeacherStatCourseOffCountByClass($old_class['user_class_id'],$old_class['course_id']);
					stat_api::reduceTeacherStatOrgCourseOffCountByClass($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);

				}elseif($course_info['admin_status'] == 1){
					stat_api::reduceTeacherStatCourseOnCountByClass($old_class['user_class_id'],$old_class['course_id']);
					stat_api::reduceTeacherStatOrgCourseOnCountByClass($old_class['user_class_id'],$course_info['fk_user'],$old_class['course_id']);
				}
				stat_api::reduceTeacherStatOrgStudentCount($old_class['user_class_id'],$course_info['fk_user'],$old_class['user_total']);
				stat_api::reduceTeacherStatStudentCount($old_class['user_class_id'], $old_class['user_total']);
			}
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function updateMaxuser($courseId){
		$course_db = new course_db;
		$data = $course_db->getclassMaxuserTotal($courseId);
		if(empty($data->items)){
			return;
		}
		$item = $data->items[0];
		$data = new stdclass;
		$data->max_user = $item["sum_max_user"];
		$db_ret = $course_db->updatecourse($courseId,$data);
	}
	
	public function pageAdd($inPath)
	{	
		if(empty($inPath[3])) return api_func::setMsg(1000);
		$courseId = (int)$inPath[3];
		
		$params = SJson::decode(utility_net::getPostData(), true);
		if(empty($params['user_id'])) return api_func::setMsg(1000);
		
		$data = [
			'fk_user'     => $params['user_id'],
			'fk_course'   => $courseId,
			'name'        => $params['name'],
			'max_user'    => !empty($params['maxUser']) ? $params['maxUser'] : 50,
            'fk_user_class'=> $params['user_class_id'],
			'create_time' => date("Y-m-d H:i:s")
		];
		$data['min_user'] = !empty($params['min_user']) ? $params['min_user'] : 10;
        $data['descript'] = !empty($params['descript']) ? $params['descript'] : '请填写班级介绍';
		if(!empty($params['region_level0'])){
			$data['region_level0'] = $params['region_level0'];
		}
		if(!empty($params['region_level1'])){
			$data['region_level1'] = $params['region_level1'];
		}
		if(!empty($params['region_level2'])){
			$data['region_level2'] = $params['region_level2'];
		}
		if(!empty($params['address'])){
			$data['address'] = $params['address'];
		}
        $data['progress_plan'] = 0;
		$db = new course_db;
		if($classId = $db->addclass($data)){
			//统计创建的班级
			$stat_api = new stat_api;
			$stat_api->setCourseStatClassCount($courseId, array('count'=>1));
			$this->updateMaxuser($courseId);
			return api_func::setData(array('classId'=>$classId));
		}
		
		return api_func::setMsg(1);
	}

    public function pageGetUserList()
    {
        $params = SJson::decode(utility_net::getPostData());
        if (empty($params)) {
            return $this->setResult('', -100645, 'param error');
        }

        $condition = isset($params->condition) && $params->condition ? $params->condition : '';
        $page = isset($params->page) && $params->page ? $params->page : 1;
        $length = isset($params->length) && $params->length ? $params->length : 100;
        $item = isset($params->item) && $params->item ? $params->item : '';
        $orderBy = isset($params->orderBy) && $params->orderBy ? $params->orderBy : '';
        $groupBy = isset($params->groupBy) && $params->groupBy ? $params->groupBy : '';

        $courseDb = new course_db();
        $courseUserList = $courseDb->getClassUserList($condition, $page, $length, $item, $orderBy, $groupBy);

        if (empty($courseUserList->items))
            return $this->setResult('', -1007, 'get course user list failed');

        $uidArr = $userRes = $userMobileRes = $data = array();
        foreach ($courseUserList->items as $v) {
            $uidArr[] = $v['fk_user'];
        }

        $uidStr = implode(',', $uidArr);
        $userDb = new user_db();
        $userCond = "pk_user IN ({$uidStr})";

        $userItem = array('pk_user', 'gender', 'name');
        $userList = $userDb->getUserList($userCond, $userItem);

        if (empty($userList->items))
            return $this->setResult('', -1008, 'get user list failed');

        foreach ($userList->items as $user) {
            $userRes[$user['pk_user']] = $user;
        }

        $userMobileCond = "fk_user IN ({$uidStr})";
        $userMobileItem = array('fk_user', 'mobile');
        $userMobileList = $userDb->getUserMobileList($userMobileCond, $userMobileItem);

        if (empty($userMobileList->items))
            return $this->setResult('', -100978, 'get user mobile list failed');

        foreach ($userMobileList->items as $userMobile) {
            $userMobileRes[$userMobile['fk_user']] = $userMobile;
        }

        foreach ($uidArr as $v) {
            if (!empty($userMobileRes[$v])) {
                $data[$v] = array_merge($userRes[$v], $userMobileRes[$v]);
            } else {
                $data[$v] = array_merge($userRes[$v], array());
            }
        }

        return $this->setResult($data);
    }

    public function setResult($data='', $code=1, $msg='success')
    {
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
    }

	public function pageGetClassByClassIdArr($inPath){

		$course_db = new course_db();
		$user_db   = new user_db();
        $class_id_arr = SJson::decode(utility_net::getPostData(),true);
		
        if( empty($class_id_arr)){
        	return $this->setResult('',-1, 'params error!');
        }
		$class_id_str = implode(',',$class_id_arr);
        $ret = $course_db->listClasses($class_id_str);
        if(!$ret && empty($ret->items)){
        	return $this->setResult('', -2, 'get data failed');
        }
		$class_ret = array();
		$user_class_arr = array();
		foreach( $ret->items as $k=>$v ){
			$user_class_arr[] = $v['fk_user_class'];
		}
		$user_class_str = implode(',',$user_class_arr);
		$user_info = $user_db->listUsersByUserIds($user_class_str);
		$user_ret = array();
		if(!empty($user_info->items)){
			foreach($user_info->items as $uv){
				$user_ret[$uv['pk_user']] = $uv;
			}
		}
		
		foreach($ret->items as $cv){
			$cv['teacher_name']      = !empty($user_ret[$cv['fk_user_class']]['name'])?$user_ret[$cv['fk_user_class']]['name']:'';
			$cv['teacher_real_name'] = !empty($user_ret[$cv['fk_user_class']]['real_name'])?$user_ret[$cv['fk_user_class']]['real_name']:'';
			$class_ret[] = $cv;
		}
		
        return $this->setResult($class_ret);	

	}

	public function pageGetTeacherClassByUidArr($inPath){
			
		
        $fk_user_class = SJson::decode(utility_net::getPostData(),true);
        if( empty($fk_user_class)){
        	return $this->setResult('',-1, 'params error!');
		}

		$fk_user_class_str = implode(',',$fk_user_class);
		$ret = course_db::getTeacherClassByUidArr($fk_user_class_str);
		if(!$ret){
        	return $this->setResult('', -2, 'get data failed');
		}else{
        	return $this->setResult($ret);	
		}
	}

    public function pageGetClassList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['classIdArr']) || count($params['classIdArr']) < 1)
            return api_func::setMsg(1000);

        $list = course_class_api::getClassList($params['classIdArr']);

        if (!empty($list)) return api_func::setData($list);

        return api_func::setMsg(3002);
    }
	
	public function pageGetClassAndCourseList($inPath)
	{
		$page   = !empty($inPath[3]) ? (int)$inPath[3] : 1;
		$length = !empty($inPath[4]) ? (int)$inPath[4] : -1;
		$params = SJson::decode(utility_net::getPostData(), true);
		
		$data = array();
		if(!empty($params['courseId'])){
			$data['courseId'] = $params['courseId'];
		}
		if(!empty($params['userClassId'])){
			$data['userClassId'] = $params['userClassId'];
		}
		if(!empty($params['userId'])){
			$data['userId'] = $params['userId'];
		}
		if(!empty($params['courseType'])){
			$data['courseType'] = $params['courseType'];
		}
		if(!empty($params['sort'])){
			$data['sort'] = $params['sort'];
		}
        if(!empty($params['status'])){
            $data['status'] = (int)$params['status'];
        }
        if(!empty($params['progressStatus'])){
            $data['progress_status'] = (int)$params['progressStatus'];
        }
        if(!empty($params['classId'])){
            $data['classId'] = $params['classId'];
        }
		if(empty($data)){
			return api_func::setMsg(1000);
		}
		
		$res = course_db::classAndCourseList($data,$page,$length);
	
		if (!empty($res)) return api_func::setData($res);

        return api_func::setMsg(3002);
	}
	public function pageSetProgress($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($inPath[3]) || empty($params->progress_plan)){
			$ret->result->code = -1;
			$ret->result->msg = "params error!";
			return $ret;
		}
		$course_db  = new course_db();
		$classId = $inPath[3];
		$data = array();
		$data['progress_plan'] = $params->progress_plan;
		if(isset($params->progress_percent)){
			$data['progress_percent'] = $params->progress_percent;
		}
		$updateRet = $course_db->updateClass($classId,$data);
		if($updateRet !== false){
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}else{
			$ret->result->code = -2;
			$ret->result->msg ="update fail";
		}
		return $ret;
	}
}
