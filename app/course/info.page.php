<?php
class course_info{
	public function pageGenId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$uid = $inPath[3];
		$user_db = new user_db;
		$user = user_db::getUser($uid);
		//TODO判断老师是否有权限
		$course_api = new course_api;
		$course_id = $course_api->genId($uid);
		if(!empty($course_id)){
			//	unset($ret->result);
			$ret->data=array("uid"=>(int)$uid,"course_id"=>(int)$course_id);
			$ret->result->code = 0;
			$ret->result->msg= "success";
		}else{
			$ret->result->code = -2;
		}
		return $ret;
	}
	public function pageGet($inPath){
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return array(
				'result' => array(
					'code' => -1,	
					'msg' => 'invalid parameter',	
				),	
			);
		}
		$course_api = new course_api;
		$course = $course_api->get((int)$inPath[3]);
		if (empty($course['course_id'])) {
			return array(
				"code" => '-2',
				"msg" => 'the course does not exist',
			);	
		}
		$course['status']=course_status::name($course['status']);
		return array(
			'data' =>$course,
		);
	}
	/*
		批量修删除冻结功能
	 */
	public function pageSetDeleted($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$course_id = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		//$params = new stdclass;
		//$params->deleted = 0;
		if(!isset($params->deleted)){
			$ret->result->code = -1;
			$ret->result->msg= "admin status is empty!";
			return $ret;
		}
		$deleted = $params->deleted;
		$coursein = array("deleted"=>$deleted);
		$course_api = new course_api;
		$course_db = new course_db;
		$updatedel = $course_api->updatecheckStatus($course_id,$coursein);
		$updateplandel = $course_db->MgrdeletePlan($course_id,$deleted);
		if($updateplandel=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	/*
		审核状态
	 */
	public function pageSetCheckStatus($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$course_id = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		//$params = new stdclass;
		//$params->checkstatus = 0;
		if(!isset($params->checkstatus)){
			$ret->result->code = -1;
			$ret->result->msg= "admin status is empty!";
			return $ret;
		}
		$check_status = $params->checkstatus;
		$coursein = array("check_status"=>$check_status);
		$course_api = new course_api;
		$updatecheck = $course_api->updatecheckStatus($course_id,$coursein);
		if($updatecheck=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageSetAdminStatus($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$course_id = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->admin_status)){
			$ret->result->code = -1;
			$ret->result->msg= "admin status is empty!";
		}

		$admin_status = course_adminstatus::key($params->admin_status);
		if($admin_status===false){
			$ret->result->code = -2;
			$ret->result->msg= "status is not supported!";
		}
		$course_db = new course_db;
		$course_info = $course_db->getCourse($course_id);
		$class_info  = $course_db->classList($course_id);
		$plan_info   = $course_db->getPlanTeacherByCourseId($course_id);

		$update_r = $course_db->updateCourseAdminStatus($course_id,$admin_status);
		if($update_r=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
			
			$tid_arr = array();
			foreach($class_info->items as $so){
				$tid_arr[$so['fk_user_class']] = $so['fk_user_class'];
			}
			foreach($plan_info->items as $po){
				$tid_arr[$po['fk_user_plan']] = $po['fk_user_plan'];
			}
			if($course_info['admin_status'] == 0 && $admin_status == 1){
				stat_api::addTeacherStatCourseOnCount($tid_arr);		
				stat_api::addTeacherStatOrgCourseOnCount($tid_arr, $course_info['fk_user']);		
			}elseif($course_info['admin_status'] == 1 && $admin_status == -2){
				stat_api::reduceTeacherStatCourseOnCount($tid_arr);		
				stat_api::reduceTeacherStatOrgCourseOnCount($tid_arr,$course_info['fk_user']);		
				stat_api::addTeacherStatCourseOffCount($tid_arr);		
				stat_api::addTeacherStatOrgCourseOffCount($tid_arr, $course_info['fk_user']);		
			}elseif($course_info['admin_status'] == -2 && $admin_status == 1){
				stat_api::addTeacherStatCourseOnCount($tid_arr);		
				stat_api::addTeacherStatOrgCourseOnCount($tid_arr, $course_info['fk_user']);		
				stat_api::reduceTeacherStatCourseOffCount($tid_arr);		
				stat_api::reduceTeacherStatOrgCourseOffCount($tid_arr, $course_info['fk_user']);		
			}

		}
		return $ret;
	}

	public function pageaddCOurseTop($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$course_api = new course_api;
		$update_r = $course_api->addCOurseTop($inPath[3]);

		//	print_r($course_api);
		if($update_r=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagedelCOurseTop($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$course_api = new course_api;
		$update_r = $course_api->delCOurseTop($inPath[3]);

		//	print_r($course_api);
		if($update_r=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    //需要改几个字段就传几个字段 
	public function pageSetCOurse($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
        $course_db = new course_db;
        $db_ret=$course_db->updateCourse($inPath[3],$params); 
		if($db_ret=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageUpdate($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->msg= "course_id is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->title)){
			$ret->result->code = -1;
			$ret->result->msg= "title is empty!";
		}
		$course_id = (int)$inPath[3];
		$course = array();
		$time = date("Y-m-d H:i:s");
        if(!empty($params->title)){
            $course['title'] = $params->title;
        }
        if(!empty($params->scope)){
            $course['scope'] = $params->scope;
        }
        if(!empty($params->descript)){
            $course['descript'] = $params->descript;
        }
        if(!empty($params->fk_grade)){
            $course['fk_grade'] = $params->fk_grade;
        }
        if(!empty($params->thumb_big)){
            $course['thumb_big'] = $params->thumb_big;
        }
        if(!empty($params->thumb_med)){
            $course['thumb_med'] = $params->thumb_med;
        }
        if(!empty($params->thumb_small)){
            $course['thumb_small'] = $params->thumb_small;
        }
        if(!empty($params->start_time)){
            $course['start_time'] = $params->start_time;
        }
        if(!empty($params->end_time)){
            $course['end_time'] = $params->end_time;
        }
        if(!empty($params->tags)){
            $course['tags'] = $params->tags;
        }
        if(!empty($params->type)){
            $course['type'] = $params->type;
        }
        if(!empty($params->last_updated)){
            $course['last_updated'] = $time;
        }
        if(!empty($params->cate_id)){
            $course['fk_cate'] = $params->cate_id;
        }
        if(!empty($params->first_cate)){
            $course['first_cate'] = $params->first_cate;
        }
        if(!empty($params->second_cate)){
            $course['second_cate'] = $params->second_cate;
        }
        if(!empty($params->third_cate)){
            $course['third_cate'] = $params->third_cate;
        }
        if(isset($params->fee_type)){
            $course['fee_type'] = (int)$params->fee_type;
        }
        if(isset($params->fee->price)){
            $course['price'] = $params->fee->price * 100;
        }
		$course_api = new course_api;
		$update_r = $course_api->update($course_id,$course);

		if($update_r=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			//修改t_mapping_course_attr_value
			$groupGetArr = SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$groupSub = $groupGetArr->subject;
			$groupGrade = $groupGetArr->grade;
			$courseDb = new course_db;
			$tagDb = new tag_db;
			if(!empty($params->attr->attr_value_ids)){
				$attrValueIds = $params->attr->attr_value_ids;
				$retattr = course_api::addMappingCourseAttrValue($attrValueIds,$course_id);	
				//修改t_mapping_tag_course
				$attrValueRet= $courseDb->getAttrValueByAttrValueIds($attrValueIds);
				if(!empty($attrValueRet->items)){
					$attrValueNameArr = array();
					foreach($attrValueRet->items as $value){
						$attrValueNameArr[] = '\''.$value['name'].'\'';
					}
					$tagRet = $tagDb->getTagByNameArr($attrValueNameArr);
					if(!empty($tagRet->items)){
						$tagIdArr = array();
						foreach($tagRet->items as $tag){
							$tagIdArr[] = $tag['pk_tag']; 
						}
						tag_api::addmappingcourse($course_id,$groupSub,$tagIdArr);
					}else{
						$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupSub);
					}
				}
			}else{
				$delData = array('status' => -1);
				$courseDb->updateMappingCourseAttrValueByCid($course_id,$delData);
				$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupSub);
			}
			
			if(!empty($course['second_cate'])){
				$cateIdArr = array();
				$cateIdArr[$course['second_cate']] = $course['second_cate'];
				if(!empty($course['third_cate'])){
					$cateIdArr[$course['third_cate']] = $course['third_cate'];
				}
				$cateRet = $courseDb->getCateByCateIdArr($cateIdArr);
				if(!empty($cateRet->items)){
					$cateNameArr = array();
					foreach($cateRet->items as $cate){
						$cateNameArr[] = '\''.$cate['name_display'].'\'';
					}
					$tagRet = $tagDb->getTagByNameArr($cateNameArr);
					if(!empty($tagRet->items)){
						$tagIdArr = array();
						foreach($tagRet->items as $tag){
							$tagIdArr[] = $tag['pk_tag']; 
						}
						tag_api::addMappingCourse($course_id,$groupGrade,$tagIdArr);
					}else{
						$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupGrade);
					}
				}
			}else{
				$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupGrade);
			}
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	//兼容updateCourse(修改科目)
	public function pageSetCourseImg($inPath){
		
		$courseId = !empty($inPath[3]) ? (int)$inPath[3] : 0;
		if(empty($courseId)) return api_func::setMsg(1000);
		
		$course = array();
		$params = SJson::decode(utility_net::getPostData());
		
		if(!empty($params->scope)){
            $course['scope'] = $params->scope;
        }
		if(!empty($params->descript)){
            $course['descript'] = $params->descript;
        }
		if(!empty($params->thumb_big)){
            $course['thumb_big'] = $params->thumb_big;
        }
        if(!empty($params->thumb_med)){
            $course['thumb_med'] = $params->thumb_med;
        }
        if(!empty($params->thumb_small)){
            $course['thumb_small'] = $params->thumb_small;
        }
		
		if(empty($course)) return api_func::setMsg(1000);

		$course_api = new course_api;
		$update_r = $course_api->update($courseId, $course);
		
		if($update_r == false) return api_func::setMsg(1);
		
		return api_func::setMsg(0);
	}
	
	public function pageAddCourse($inPath){

		$ret = new stdclass;
		$ret->data = '';
		$ret->code = -1;
		$ret->msg  = 'add inpath is empty!';
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->title)){
			$ret->code = -1;
			$ret->msg= "title is empty!";
			return $ret;
		}
		if(empty($params->user_id)){
			$ret->code = -2;
			$ret->msg= "user_id is empty!";
			return $ret;
		}
		$course = array();
		$time = date("Y-m-d H:i:s");
		$course["title"] 		= $params->title;
		$course["descript"] 	= empty($params->descript)? '':$params->descript;
		$course["scope"] 	    = empty($params->scope)? '':$params->scope;
		$course["max_user"] 	= empty($params->max_user)? '0': $params->max_user;
		$course["min_user"] 	= empty($params->min_user)? '0':$params->min_user;
		$course["thumb_big"] 	= empty($params->thumb_big)? '':$params->thumb_big;
		$course["thumb_med"] 	= empty($params->thumb_med)? '':$params->thumb_med;
		$course["thumb_small"]  = empty($params->thumb_small)? '':$params->thumb_small;
		$course["start_time"]  	= empty($params->start_time)? date('Y-m-d H:i:s') :date("Y-m-d H:i:s",strtotime($params->start_time));
		$course["end_time"]  	= empty($params->end_time)? date('Y-m-d H:i:s') :date("Y-m-d H:i:s",strtotime($params->end_time));
		$course["tags"] 		= empty($params->tags)? '':$params->tags;
		$course["type"] 		= empty($params->type)? '1':$params->type;
		$course["fee_type"] 	= empty($params->fee_type)? '0':$params->fee_type;
		$course["first_cate"]   = empty($params->first_cate)? '0':$params->first_cate;
		$course["second_cate"]  = empty($params->second_cate)? '0':$params->second_cate;
		$course["third_cate"]   = empty($params->third_cate)? '0':$params->third_cate;
		$course['fk_user']      = $params->user_id;
        $course['status']       = 0;
		$course['create_time']  = $time;
		
		if(empty($params->fee->price) || empty((int)($params->fee->price*100))){
			$course['fee_type'] = 0;
		}
		
		if($course['fee_type'] && !empty($params->fee->price)){
			$course['price'] = $params->fee->price * 100;
			$course ["price_market"] = $params->fee->price * 100;
		}
		if(empty($course['fee_type'])){
			$course ["price"] = 0;
			$course ["price_market"] = 0;
		}
	
		$course_db = new course_db;
		$course_id = $course_db->addCourse($course);

		if(empty($course_id)){
			$ret->code = -3;
			$ret->msg = "fail add";
		}else{
			$stat_api      = new stat_api;
			$data['count'] = 1;
 			$stat_api->setUserOrgStatCourseCount($course['fk_user'], $data);
			//修改t_mapping_course_attr_value
			$groupGetArr = SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$groupSub = $groupGetArr->subject;
			$groupGrade = $groupGetArr->grade;
			$courseDb = new course_db;
			$tagDb = new tag_db;
			if(!empty($params->attr->attr_value_ids)){
				$attrValueIds = $params->attr->attr_value_ids;
				$retattr = course_api::addMappingCourseAttrValue($attrValueIds,$course_id);	
				//修改t_mapping_tag_course
				$attrValueRet= $courseDb->getAttrValueByAttrValueIds($attrValueIds);
				if(!empty($attrValueRet->items)){
					$attrValueNameArr = array();
					foreach($attrValueRet->items as $value){
						$attrValueNameArr[] = '\''.$value['name'].'\'';
					}
					$tagRet = $tagDb->getTagByNameArr($attrValueNameArr);
					if(!empty($tagRet->items)){
						$tagIdArr = array();
						foreach($tagRet->items as $tag){
							$tagIdArr[] = $tag['pk_tag']; 
						}
						tag_api::addmappingcourse($course_id,$groupSub,$tagIdArr);
					}else{
						$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupSub);
					}
				}
			}else{
				$delData = array('status' => -1);
				$courseDb->updateMappingCourseAttrValueByCid($course_id,$delData);
				$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupSub);
			}
			
			if(!empty($course['second_cate'])){
				$cateIdArr = array();
				$cateIdArr[$course['second_cate']] = $course['second_cate'];
				if(!empty($course['third_cate'])){
					$cateIdArr[$course['third_cate']] = $course['third_cate'];
				}
				$cateRet = $courseDb->getCateByCateIdArr($cateIdArr);
				if(!empty($cateRet->items)){
					$cateNameArr = array();
					foreach($cateRet->items as $cate){
						$cateNameArr[] = '\''.$cate['name_display'].'\'';
					}
					$tagRet = $tagDb->getTagByNameArr($cateNameArr);
					if(!empty($tagRet->items)){
						$tagIdArr = array();
						foreach($tagRet->items as $tag){
							$tagIdArr[] = $tag['pk_tag']; 
						}
						tag_api::addMappingCourse($course_id,$groupGrade,$tagIdArr);
					}else{
						$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupGrade);
					}
				}
			}else{
				$tagDb::delMappingCourseByCidAndGroupId($course_id,$groupGrade);
			}
			$ret->data = $course_id;
			$ret->code = 0;
			$ret->msg ="success";
		}
		return $ret;
	}
	
	public function pageDelete($inPath){
		$params = Sjson::decode(utility_net::getPostData());
		$ret = new stdclass;
		return $ret;
	}

	public function pageCourseLikeList($inPath){	
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$course_api = new course_api;
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		$orderby = array();
		$user_id = 0;
		$course_ids = array();
		if(!empty($params->cond->search)){
			$data["search"] =  $params->cond->search;
		}
		if(!empty($params->cond->course_ids)){
			foreach($params->cond->course_ids as $k=>$v){
				$course_ids[] = $v;
			}
		}
		if(!empty($params->cond->user_id)){
			$user_id = $params->cond->user_id;
		}
		$orderby['st'] = !empty($params->cond->st) ? $params->cond->st : '';
		$courselist = $course_api->courseLikeList($user_id,$course_ids,$data,$orderby);
		if($courselist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $courselist;
	}

	public function pageMgrCourseList($inPath){	
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 10;}else{$length = $inPath[4];}

		$course_api = new course_api;
		$params = SJson::decode(utility_net::getPostData());

		$cond = array();
		$orderBy = array();
		$groupBy = array();
		//cond
		if(!empty($params->cond->starttime1)){
			$cond["starttime1"] =  $params->cond->starttime1;
		}
		if(!empty($params->cond->starttime2)){
			$cond["starttime2"] =  $params->cond->starttime2;
		}
		if(isset($params->cond->status)){
			$cond["status"] =  $params->cond->status;
		}
		if(isset($params->cond->admin_status)){
			$cond["admin_status"] =  $params->cond->admin_status;
		}
		if(!empty($params->cond->user_id)){
			$cond["user_id"] =  $params->cond->user_id;
		}
		//order
		if(!empty($params->orderBy->starttime)){
			if($params->orderBy->starttime == 1){
				$orderBy["starttime"] = "asc";
			}else{ 
				$orderBy["starttime"] = "desc";
			}
		}
		if(!empty($params->orderBy->status)){
			if($params->orderBy->status == 1){
				$orderBy["status"] = "asc";
			}else{ 
				$orderBy["status"] = "desc";
			}
		}
		if(!empty($params->orderBy->usertotal)){
			if($params->orderBy->usertotal == 1){
				$orderBy["usertotal"] = "asc";
			}else{ 
				$orderBy["usertotal"] = "desc";
			}
		}
		if(!empty($params->orderBy->feetype)){
			if($params->orderBy->feetype == 1){
				$orderBy["feetype"] = "asc";
			}else{ 
				$orderBy["feetype"] = "desc";
			}
		}
		$courselist = $course_api->MgrcourseList($cond,$orderBy,$groupBy,$page,$length);
		/*
			if($courselist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		 */
		return $courselist;
	}
	public function pageCourselist($inPath){	
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 4;}else{$length = $inPath[4];}

		$course_api = new course_api;
		$params = SJson::decode(utility_net::getPostData());
		$status = !isset($params->status)? null :$params->status;
		$status = course_status::key($status);
		$data = array();
		if(!empty($params->create_time)){
			$data["create_time"] = $params->create_time;
		}
		if(!empty($params->user_total)){
			$data["user_total"] = $params->user_total;
		}
		if(isset($params->search)){
			$data["search"] = $params->search;
		}else{
			$data["search"] = null;
		}
		if(!empty($params->type) && (is_numeric($params->type))){
			$data["type"] = $params->type;
		}else{
			$data["type"] = null;
		}
		if($status==false){
			$status=null;
		}
		if(empty($params->fee_type)){
			$fee = null;
		}else{
			$fee = $params->fee_type;
		}
		if(empty($params->oid)){
			$oid = null;
		}else{
			$oid = $params->oid;
		}
		if(empty($params->grade_id)){
			$grade_id = null;
		}else{
			$grade_id = $params->grade_id;
		}
		if(empty($params->week)){
			$week = false;
		}else{
			$week = $params->week;
		}
		if(empty($params->shelf)){
			$shelf = false;
		}else{
			$shelf = $params->shelf;
		}
		$courselist = $course_api->getcourselist($page,$length,$fee,$oid,$grade_id,$status,$week,$shelf,$data);
		if($courselist === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		//$courselist1 = SJson::decode($courselist);
		//	return $courselist1->data[0];
		return $courselist;
	}
	/**
	 * 报名接口，增加报名数据
	 * 同时更新报名里的数据
	 */
	public function pageaddRegistration($inPath){
		$ret = new stdclass;
		$course_api = new course_api;
		$params = SJson::decode(utility_net::getPostData());
		//当传过来的值为空，不能插入成功
		if(empty($params->course_id) || empty($params->uid)){
			$ret->result = array("code"=>-1,"msg"=>"params is error");
			return $ret;
		}
		$db = new course_db;
		$course = $db->getCourse($params->course_id);
		if(empty($course)){
			$ret->result = array("code"=>-2,"msg"=>"course info not found");
			return $ret;
		}
		$class_id = 0;
		if(!empty($params->class_id)){
			// todo 这里可进行验证
			$class = $db->getClass($params->class_id);
			if(empty($class['fk_course'])){
				$ret->result = array("code"=>-3,"msg"=>"class info not found");
				return $ret;
			}
			if($class['fk_course']!=$params->course_id){
				$ret->result = array("code"=>-4,"msg"=>"this class is not in course");
				return $ret;
			}
			$class_id = $params->class_id;
		}
		$reg_data = array();
		$reg_data['fk_course']=$params->course_id;
		$reg_data['fk_user']=$params->uid;
		$reg_data['fk_user_owner']=$params->user_owner;
		$reg_data['fk_class']=$class_id;
		$reg_data['source'] = empty($params->source) ? 1 : $params->source;
		$reg_data['status']=empty($params->status)?0:$params->status;
		$reg_data['create_time']=date("Y-m-d H:i:s");

		//判断是否是会员身份报名课程
		$isMember = user_db_orgMemberDao::checkUserMemberCourse($params->uid,$params->course_id,1);
		if(!empty($isMember)){
			$reg_data['source'] = 2;
			$reg_data['expire_time'] = $isMember['end_time'];
		}

		//判断之前是否以会员身份报名已经报名
		$flagReg = 0;
		$updateData = array();
		$isRegister = course_db_courseUserDao::checkUserIsRegFromMainDb($params->uid, $params->course_id);
		if(!empty($isRegister)){
			$nowTime = time();
			if($isRegister['source'] == 2){
				$updateData['source'] = $reg_data['source'];
				$updateData['expire_time'] = '0000-00-00 00:00:00';
				$flagReg = 1;
			}
		}
		if($flagReg == 1){
			$reg_ret = course_db_courseUserDao::updateRegistration($isRegister['pk_course_user'], $updateData);
		}else{
			if(!empty($params->class_id)){
				if ($class['user_total'] >= $class['max_user']) {
					$ret->result = array("code"=>-5,"msg"=>"reg user has reached the ceiling");
					return $ret;
				}
			}
			$reg_ret = $course_api->addRegistration($reg_data);
		}
		if($reg_ret === false){
			$ret->result = array("code" => -3,"msg"=>"add error");
		}else{
			$ret->result = array("code" => 0,"msg"=>"success");
			//更新数据,获取报名人数
			if($flagReg == 0){
				$course_user_total = $db->getRegistrationCountByCourse($params->course_id);
				$course_update = array("user_total"=>$course_user_total);

				$up_course = $db->updateCourse($params->course_id,$course_update);
				$ret->update=array("course_count" => $up_course);
				if($class_id){
					$class_user_total = $db->getRegistrationCountByClass($class_id);
					$course_update = array("user_total"=>$class_user_total);
					$up_class = $db->updateClass($class_id,$course_update);
					$ret->update['class_count']= $up_class;
				}
				//更新统计教师学生数和分机构教师学生总数
				stat_api::addTeacherStatStudentCount($class['fk_user_class'], 1);
				stat_api::addTeacherStatOrgStudentCount($class['fk_user_class'],$params->user_owner,1);

				message_api::modifyStudent($class_id, $params->uid, true);
			}
			if($course['fee_type']==1){
				//付费课程给班主任老师，机构管理员，学生发站内信
				// 	$params->uid 学生
				//	$headTeacher 班主任老师id
				//	$course['fk_user'] 机构userId
				$studentId = empty($params->uid)?0:intval($params->uid);
				if(!empty($studentId)){
					$messageStudent =array(
						'msgType'=>'10024',//报名信息
						'userFrom'=>$course['fk_user'],
						'userTo'=>$studentId,
						'content'=>"【报名成功】恭喜你成功报名了课程：[".$course['title']."]。",
						'title'=>'报名成功',
						'source'=>'20000',
					);
					$result=message_api::add($messageStudent);
				}
				$headTeacher = empty($class['fk_user_class'])?0:$class['fk_user_class'];
				$studentInfo = user_db_userDao::getUserInfoByUserId($studentId);
				if(!empty($headTeacher)){
					$messageTeacher =array(
						'msgType'=>'10024',//报名信息
						'userFrom'=>$course['fk_user'],
						'userTo'=>$headTeacher,
						'content'=>"【报名情况】".$studentInfo['real_name']."（联系方式：".$studentInfo['mobile']."）报名了[".$course['title']."]".$class['name'],
						'title'=>'报名成功',
						'source'=>'20000',
					);
					message_api::add($messageTeacher);
				}
				$orgUserId = empty($course['fk_user'])?0:$course['fk_user'];
				$orgInfo = user_db_organizationDao::getOrgInfoByUserId($orgUserId);
				$adminOrg 	=	array();
				$adminOrgs	= 	array();
				if($orgInfo->items){
					$adminOrg = user_db_organizationUserDao::getAdminByOrgId($orgInfo->items[0]['pk_org']);
				}
				if($adminOrg){
					foreach($adminOrg as $v){
						$adminOrgs[$v['fk_user']] = $v['fk_user'];
					}
				}
				$adminOrgs[$course['fk_user']] = $course['fk_user'];
				if($adminOrgs){
					foreach($adminOrgs as $adminUserId){
						if($adminUserId == $headTeacher){
								continue;
						}
						$messageTeacher =array(
							'msgType'=>'10024',//报名信息
							'userFrom'=>0,
							'userTo'=>$adminUserId,
							'content'=>"【报名情况】".$studentInfo['mobile']."（联系方式：".$studentInfo['mobile']."）报名了[".$course['title']."]".$class['name'],
							'title'=>'报名成功',
							'source'=>'20000',
						);
						message_api::add($messageTeacher);
					}
					
				}
				
			}
		}
		return $ret;
	}
	public function pagelistRegistrationBycond($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//page 页数
		if(!isset($inPath[3])||!is_numeric($inPath[3])){$page = 0;}else{$page = $inPath[3];}
		//length 每页显示数
		if(!isset($inPath[4])||!is_numeric($inPath[4])){$length = 0;}else{$length = $inPath[4];}

		$course_api = new course_api;
		$params = SJson::decode(utility_net::getPostData());
		$uids = isset($params->uids)?$params->uids:0;
		$course_ids = isset($params->course_ids)?$params->course_ids:0;
		$class_id	= isset($params->class_id)?$params->class_id:0;
		$user_owner	= isset($params->user_owner)?$params->user_owner:0;

		if(!empty($params->uids)){
			$uids = array();
			foreach($params->uids as $k=>$v){
				$uids[] = $v;
			}
		}
		$listreg = $course_api->listRegistrationbycond($course_ids,$class_id,$uids,$user_owner,$page,$length);
		if($listreg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listreg;
	}
	public function pagelistRegistration($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//page 页数
		if(!isset($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(!isset($inPath[4])||!is_numeric($inPath[4])){$length = 20;}else{$length = $inPath[4];}

		$course_api = new course_api;
		$params = SJson::decode(utility_net::getPostData());
		$uid = isset($params->uid)?$params->uid:0;
		$course_id = isset($params->course_id)?$params->course_id:0;
		$class_id	= isset($params->class_id)?$params->class_id:0;
		$user_owner	= isset($params->user_owner)?$params->user_owner:0;
		$listreg = $course_api->listRegistration($course_id,$class_id,$uid,$user_owner,$page,$length);
		if($listreg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listreg;
	}
	/*
	 *更新报名人数
	 */
	public function pageupdateRegCount($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		$params = SJson::decode(utility_net::getPostData());
		$old_class_id = 0;
		$new_class_id = 0;
		$new_course_id =  $params->new_course_id;//目标课程id
		$new_class_id = $params->new_class_id;//目标班级id
		$old_course_id =  $params->old_course_id;//原课程id
		$old_class_id = $params->old_class_id;//原班级id
		$course_db = new course_db;
		//更新原班级数据,获取报名人数
		//define("DEBUG",true);
		$old_course_user_total = $course_db->getRegistrationCountByCourse($old_course_id);
		$old_course_update = array("user_total"=>$old_course_user_total);
		$old_up_course = $course_db->updateCourse($old_course_id,$old_course_update);
		$ret->update=array("old_course_count" => $old_up_course);
		if($old_class_id){
			$old_class_user_total = $course_db->getRegistrationCountByClass($old_class_id);
			$old_class_update = array("user_total"=>$old_class_user_total);
			$old_up_class = $course_db->updateClass($old_class_id,$old_class_update);
			$ret->update['old_class_count']= $old_up_class;
		}
		//更新目标班级数据,获取报名人数
		$new_course_user_total = $course_db->getRegistrationCountByCourse($new_course_id);
		$new_course_update = array("user_total"=>$new_course_user_total);
		$new_up_course = $course_db->updateCourse($new_course_id,$new_course_update);
		$ret->update['new_course_count'] = $new_up_course;
		if($new_class_id){
			$new_class_user_total = $course_db->getRegistrationCountByClass($new_class_id);
			$new_class_update = array("user_total"=>$new_class_user_total);
			$new_up_class = $course_db->updateClass($new_class_id,$new_class_update);
			$ret->update['new_class_count']= $new_up_class;
		}

		if($new_class_update=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageupdateRegClass($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->course_user_id)){
			$ret->result->code = -1;
			$ret->result->msg= "admin status is empty!";
		//	return $ret;
		}
		$course_user_id = $params->course_user_id;
	//	$course_user_id = "103";
		$course_db = new course_db;
		//define("DEBUG",true);
		$upregdata = array(
			"course_id"=>$params->course_id,
			"class_id"=>$params->class_id,
			"old_course_id"=>$params->old_course_id,
			"old_class_id"=>$params->old_class_id,
	//		"course_id"=>"131",
	//		"class_id"=>"52",
		);
		$regData = $course_db->getRegistrationbyPk($course_user_id);
		$uid = $regData["uid"];
		$oldClassId= $regData["class_id"];
		$update_reg_class = $course_db->updateRegClass($course_user_id,$upregdata);
		if($update_reg_class=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
			message_api::modifyStudent($oldClassId, $uid, false);
			message_api::modifyStudent($params->class_id, $uid, true);
		}
		return $ret;
	}

    public function pageUpdateUserClass()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['condition']) || empty($params['data'])) {
            return api_func::setMsg(1000);
        }

        $condition = $params['condition'];
        $data = $params['data'];

		$old_course_id = $params['params']['originCid'];
		$old_class_id = $params['params']['originClassId'];
		$course_id = $params['params']['cid'];
		$class_id = $params['params']['classId'];
		
		$course_api =new course_api;	
        $course_db = new course_db;
		$old_class = $course_api->getclass($old_class_id);
		$class_info = $course_api->getclass($class_id);

        $res = $course_db->updateUserClass($condition, $data);
		if ($res === false) return api_func::setMsg(1);

		//更新以前班级和课程的报名人数
		$course_user_total = $course_db->getRegistrationCountByCourse($old_course_id);
		$course_update = array("user_total"=>$course_user_total);
		$up_course = $course_db->updateCourse($old_course_id,$course_update);
		$class_user_total = $course_db->getRegistrationCountByClass($old_class_id);
		$course_update = array("user_total"=>$class_user_total);
		$up_class = $course_db->updateClass($old_class_id,$course_update);

		//更新更改之后的班级课程的报名人数
		$course_user_total = $course_db->getRegistrationCountByCourse($course_id);
		$course_update = array("user_total"=>$course_user_total);
		$up_course = $course_db->updateCourse($course_id,$course_update);
		$class_user_total = $course_db->getRegistrationCountByClass($class_id);
		$course_update = array("user_total"=>$class_user_total);
		$up_class = $course_db->updateClass($class_id,$course_update);

		//更新统计数据
		stat_api::addTeacherStatStudentCount($class_info['user_class_id'], 1);
		stat_api::addTeacherStatOrgStudentCount($class_info['user_class_id'],$class_info['user_id'],1);
		stat_api::reduceTeacherStatStudentCount($old_class['user_class_id'], 1);
		stat_api::reduceTeacherStatOrgStudentCount($old_class['user_class_id'],$old_class['user_id'],1);

		return api_func::setMsg(0);
    }

    public function pageGetTopCourseByOwner($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
        if(empty($inPath[3])){
			$ret->result->code = -2;
			$ret->result->msg = "faild uid";
            return $ret;
        } 
		$course_db = new course_db;
        $db_ret=$course_db->getTopCourseByOwner($inPath[3]);
        if($db_ret===false){
			$ret->result->code = -3;
			$ret->result->msg = "data is empty";
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
    }
    public function pagegetMgrCourseByInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
        if(empty($inPath[3])){
			$ret->result->code = -2;
			$ret->result->msg = "faild uid";
            return $ret;
        } 
		$course_db = new course_db;
        $db_ret=$course_db->getMgrCourseByInfo($inPath[3]);
        if($db_ret===false){
			$ret->result->code = -3;
			$ret->result->msg = "data is empty";
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
    }
	public function pagePlanUser($inPath){
		$ret = new stdclass;
		if(empty($inPath[3])){
			$ret->result =  new stdclass;
			$ret->result->code = -1;
			$ret->result->msg= "";
			return $ret;
		}
		$plan_id = $inPath[3];
		$users = course_api::listPlanUser($plan_id);
		if(!empty($users->items)){
			foreach($users->items as &$item){
				$item['online']=1;
			}
			$ret->data = $users->items;
			return $ret;
		}
		$ret->data=array();
		return $ret;
	}
	public function pagecountStudent($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData(),true);
        $cids=implode(',',$params);
		$course_db = new course_db;
        $r=$course_db->countStudent($cids);
        if(empty($r->items)){
		    $ret->result->code = -1;
		    $ret->result->msg= "data is empty!";
            return $ret;
        }else{
            return $r->items;
        }
	}
	public function pagegetStudentsByCid($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
			$ret->result->msg= "uid is empty!";
			return $ret;
		}
		$course_db = new course_db;
        $r=$course_db->getStudentsByCid($inPath[3]);
        if(empty($r->items)){
		    $ret->result->code = -1;
		    $ret->result->msg= "data is empty!";
            return $ret;
        }else{
            return $r->items;
        }
	}
    public function pageCountPlanByOwner($inPath){
        $ret=new stdClass;
	    $ret->result =  new stdclass;
		$ret->result->code = -0;
		$ret->result->msg= "";
		if(empty($inPath[3])){
		    $ret->result->code = -1;
			$ret->result->msg= "id is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
        $res=$course_db->countPlanByOwner($inPath[3],$params);
        if(empty($res)){
		    $ret->result->code = -2;
		    $ret->result->msg= "data is empty!";
            return $ret;
        }
        $ret->data=$res;
        return $ret;
    }
    public function pageCountStudentByOwner($inPath){
        $ret=new stdClass;
	    $ret->result =  new stdclass;
		$ret->result->code = -0;
		$ret->result->msg= "";
		if(empty($inPath[3])){
		    $ret->result->code = -1;
			$ret->result->msg= "id is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
        $res=$course_db->countStudentByOwner($inPath[3],$params);
        if(empty($res)){
		    $ret->result->code = -2;
		    $ret->result->msg= "data is empty!";
            return $ret;
        }
        $ret->data=$res;
        return $ret;
    }
	public function pageGetCourseByCids($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData(),true);
		$course_db = new course_db;
		$orgUserId = 0;
		if(!empty($params['orgUserId'])){
			$orgUserId = $params['orgUserId'];
			unset($params['orgUserId']);
		}
        $res=$course_db->getCourseByCids($params,$orgUserId);
        if(empty($res->items)){
		    $ret->result->code = -1;
		    $ret->result->msg= "data is empty!";
            return $ret;
        }
		$ret->result->code = 0;
		$ret->result->msg= "success";
        $ret->data=$res->items;
        return $ret;
	}
	public function pageCountStudentByClassIds($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData(),true);
		$course_db = new course_db;
        $res=$course_db->countStudentByClassIds($params);
        if(empty($res)){
		    $ret->result->code = -1;
		    $ret->result->msg= "data is empty!";
            return $ret;
        }
        $ret->data=$res;
        return $ret;
	}
	public function pageDelHistoryTopCourse($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$update_r = $course_db->delHistoryTopCOurse($inPath[3],$params->top);
		if($update_r=== false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageGetOrgCourseCount($inPath){
		$uid_arr = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($uid_arr)){
			$ret->result->code=-1;
			$ret->result->msg='params is empty';
			return $ret;
		}
		$course_db = new course_db;
        $count_data = $course_db->getOrgCourseCount($uid_arr);
		if(empty($count_data)){
			$ret->result->code = -2;
			$ret->result->msg='get data failed';
		}else{
			$ret->result->code = 0;
			$ret->result->data = $count_data;
		}
		return $ret;
	}

    public function pageCheckUserIsReg()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $r = api_func::isValidId(['uid', 'courseId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $res = course_db_courseUserDao::checkUserIsRegFromMainDb($r['uid'], $r['courseId']);        
        
        if ($res === false) return api_func::setMsg(1);
        
        $ret_class = course_db_courseClassDao::getClassInfo($res['fk_class']);               
        $res['class_name'] = $ret_class['name'];

        return api_func::setData($res);
    }

    public function pageGetOrgIdByCourseId($inPath)
    {
        $orgId = 0;
        $cid = isset($inPath[3]) && (int)$inPath[3] ? (int)$inPath[3] : 0;
        if (!$cid) return api_func::setMsg(1000);

        $courseDb   = new course_db;
        $courseInfo = $courseDb->getCourse($cid);
        if (empty($courseInfo)) return api_func::setData(['orgId' => $orgId]);

        $userDb  = new user_db;
        $orgInfo = $userDb->getOrgByUid($courseInfo['fk_user']);
        if (!empty($orgInfo) && $orgInfo['status']==1) {
            $orgId = $orgInfo['oid'];
        }

        return api_func::setData(['orgId' => $orgId]);
    }

    public function pageGetCourseBasic($inPath)
    {
        $cid   = isset($inPath[3]) && (int)$inPath[3] ? (int)$inPath[3] : 0;
        if (!$cid) return api_func::setMsg(1000);

        $courseDb   = new course_db;
        $courseInfo = $courseDb->getCourse($cid);
        if (empty($courseInfo)) return api_func::setMsg(3002);

        return api_func::setData($courseInfo);
    }
	
	public function pageSetCourseTeacher($inPath){
		$params = SJson::decode(utility_net::getPostData(), true);
		
		if(empty($params['courseId']) || empty($params['teacherId'])){
			return api_func::setMsg(1000);
		}
		$courseId  = (int)$params['courseId'];
		$teacherId = (int)$params['teacherId'];
		$data['status'] = !empty($params['status']) ? $params['status'] : '-1';
		
		$res = course_db_courseTeacherDao::update($courseId, $teacherId, $data);
		if($res === false) return api_func::setMsg(3002);
 
		return api_func::setData($res);
	}
	
	public function pageAddCourseTeacher(){
		$params = SJson::decode(utility_net::getPostData(), true);
		
		if(empty($params['courseId']) || empty($params['teacherId'])){
			return api_func::setMsg(1000);
		}
		
		$time = date('Y-m-d H:i:s');
		$data = [
			'fk_course'       => (int)$params['courseId'],
			'fk_user_teacher' => (int)$params['teacherId'],
			'status'          => 1,
			'create_time'     => $time
		];

		$res = course_db_courseTeacherDao::add($data);
		if($res === false) return api_func::setMsg(3002);
 
		return api_func::setData($res);
	}
	
	public function pageCourseTeacher($inPath){
        $page   = !empty($inPath[3]) ? (int)$inPath[3] : 1;
        $length = !empty($inPath[4]) ? (int)$inPath[4] : '-1';
		$params = SJson::decode(utility_net::getPostData(), true);
		
		if(empty($params)){
			return api_func::setMsg(1000);
		}
		
		$res = course_db_courseTeacherDao::getCourseTeacher($params, $page, $length);
		if(empty($res->items)) return api_func::setMsg(3002);
		
        //根据课程去老师(暂时最多5个)无分页
        if(!empty($params['courseId'])){
            return api_func::setData($res->items);
        }
        return api_func::setData($res);
	}
	
	//兼容老数据(获取第一个班级信息)
	public function pageClassIndex(){
		$params = SJson::decode(utility_net::getPostData(), true);
		if(empty($params['courseId'])) return api_func::setMsg(1000);
		
		$courseDb = new course_db;
		$res = $courseDb->getClassindex($params['courseId']);

		if($res === false) return api_func::setMsg(3002);
		
		return api_func::setData($res);
	}
	//取课程科目
	public function pageGetCourseSubject($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$courseIds = empty($params->courseIds)?array():$params->courseIds;
 		if(empty($courseIds)){
       		return api_func::setMsg(1000);
        }
		$ret = course_db::getCourseAttrValueByCourseIds($courseIds);
		return api_func::setData($ret);
	}

	public function pagegetCourse($info){
		$courseId = !empty($info[3])?$info[3]:0;
		if(empty($courseId)){
			return api_func::setMsg(1000);
		}
		$ret = course_db_courseClassDao::getCourseMsg($courseId);
		return api_func::setData($ret);
	}
	
	public function pageGetCourseMaxId($inPath){
		$ownerId    = !empty($inPath[3]) ? (int)$inPath[3] : 0;
		$courseType = !empty($inPath[4]) ? (int)$inPath[4] : 0;
		
		if(empty($ownerId) || empty($courseType)) return api_func::setMsg(1000);
		
		$db = new course_db;
		$courseId = $db->getMaxCourseIdByUid($ownerId,$courseType);

		if($courseId == false){
			return api_func::setMsg(3002);
		}
		
		return api_func::setData(array('courseId'=>$courseId));
	}
}
