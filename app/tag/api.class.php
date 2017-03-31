<?php
class tag_api{

	public static function addMappingUser($user_id,$group_id,$tag_id_arr){
		if(empty($user_id) || empty($group_id) || empty($tag_id_arr)){
			return false;
		}
		$addRet = array();
		$ret = tag_db::getMappingUserByUidAndGid($user_id,$group_id);
		if(!empty($ret->items)){
			$exist_tag = array();
			$del_tag = array();
			$add_tag = array();
			foreach($ret->items as $vo){
				if(in_array($vo['fk_tag'],$tag_id_arr)){
					$exist_tag[] = $vo['fk_tag'];
				}else{
					$del_tag[] = $vo['fk_tag'];
				}
			}
			if(!empty($exist_tag)){
				$add_tag = array_diff($tag_id_arr,$exist_tag);
			}else{
				$add_tag = $tag_id_arr;
			}
			if(!empty($del_tag)){
				tag_db::delMappingUserByUserAndTag($user_id,$group_id,$del_tag);		
			}
			if(!empty($add_tag)){
				$data = array();
				foreach($add_tag as $tag_id){
					if(!empty($tag_id)){
						$data['fk_tag']  = $tag_id;
						$data['fk_user'] = $user_id;
						$data['fk_group'] = $group_id;
						$addRet[] = tag_db::addMappingUser($data);
					}
				}
				return $addRet;
			}else{
				return true;
			}
			
		}else{
			$data = array();
			foreach($tag_id_arr as $tag_id){
				if(!empty($tag_id)){
					$data['fk_tag']  = $tag_id;
					$data['fk_user'] = $user_id;
					$data['fk_group'] = $group_id;
					$addRet[] = tag_db::addMappingUser($data);
				}
			}
			return $addRet;
		}
	}

	public static function addMappingCourse($course_id,$group_id,$tag_id_arr){
		if(empty($course_id) || empty($group_id) || empty($tag_id_arr)){
			return false;
		}
		$addRet = array();
		$ret = tag_db::getMappingCourseByCidAndGid($course_id,$group_id);
		if(!empty($ret->items)){
			$exist_tag = array();
			$del_tag = array();
			$add_tag = array();
			foreach($ret->items as $vo){
				if(in_array($vo['fk_tag'],$tag_id_arr)){
					$exist_tag[] = $vo['fk_tag'];
				}else{
					$del_tag[] = $vo['fk_tag'];
				}
			}
			if(!empty($exist_tag)){
				$add_tag = array_diff($tag_id_arr,$exist_tag);
			}else{
				$add_tag = $tag_id_arr;
			}
			if(!empty($del_tag)){
				tag_db::delMappingCourseByCidAndTidArr($course_id,$group_id,$del_tag);		
			}
			if(!empty($add_tag)){
				foreach($add_tag as $tag_id){
					if(!empty($tag_id)){
						$data = array();
						$data['fk_tag']  = $tag_id;
						$data['fk_course'] = $course_id;
						$data['fk_group'] = $group_id;
						$addRet[] = tag_db::addMappingCourse($data);
					}
				}
				return $addRet;
			}else{
				return true;
			}
			
		}else{
			foreach($tag_id_arr as $tag_id){
				if(!empty($tag_id)){
					$data = array();
					$data['fk_tag']  = $tag_id;
					$data['fk_course'] = $course_id;
					$data['fk_group'] = $group_id;
					$addRet[] = tag_db::addMappingCourse($data);
				}
			}
			return $addRet;
		}
	}

	public static function addMappingPlan($plan_id,$group_id,$tag_id_arr){
		if(empty($plan_id) || empty($group_id) || empty($tag_id_arr)){
			return false;
		}
		$addRet = array();
		$ret = tag_db::getMappingPlanByPidAndGid($plan_id,$group_id);
		if(!empty($ret->items)){
			$exist_tag = array();
			$del_tag = array();
			$add_tag = array();
			foreach($ret->items as $vo){
				if(in_array($vo['fk_tag'],$tag_id_arr)){
					$exist_tag[] = $vo['fk_tag'];
				}else{
					$del_tag[] = $vo['fk_tag'];
				}
			}
			if(!empty($exist_tag)){
				$add_tag = array_diff($tag_id_arr,$exist_tag);
			}else{
				$add_tag = $tag_id_arr;
			}
			if(!empty($del_tag)){
				tag_db::delMappingPlanByPidAndTidArr($plan_id,$group_id,$del_tag);		
			}
			if(!empty($add_tag)){
				$data = array();
				foreach($add_tag as $tag_id){
					if(!empty($tag_id) && !empty($group_id) && !empty($plan_id)){
						$data['fk_tag']  = $tag_id;
						$data['fk_plan'] = $plan_id;
						$data['fk_group'] = $group_id;
						$addRet[] = tag_db::addMappingPlan($data);
					}
				}
				return $addRet;
			}else{
				return true;
			}	
		}else{
			$data = array();
			foreach($tag_id_arr as $tag_id){
				if(!empty($tag_id)){
					$data['fk_tag']  = $tag_id;
					$data['fk_plan'] = $plan_id;
					$data['fk_group'] = $group_id;
					$addRet[] = tag_db::addMappingPlan($data);
				}
			}
			return $addRet;
		}
	}

	public static function addMappingArticle($aid,$uid,$tid){
		if(empty($aid) || empty($uid) || empty($tid)){
			return false;
		}
		$ret = tag_db::getMappingArticleByAidAndTid($aid,$uid,$tid);
		if(empty($ret)){
			$data = array();
			$data['fk_tag']  = $tid;
			$data['fk_article'] = $aid;
			$data['fk_user'] = $uid;
			$addRet = tag_db::addMappingArticle($data);
			return $addRet;
		}else{
			return true;
		}
	}

	public static function delMappingUserByUserId($user_id){
		if(!empty($user_id)){
			$del_ret = tag_db::delMappingUserByUserId($user_id);
			return $del_ret;
		}else{
			return false;
		}
	}
	
	public static function delMappingPlanByPidArr($plan_id_arr){
		if(!empty($plan_id_arr)){
			$del_ret = tag_db::delMappingPlanByPidArr($plan_id_arr);
			return $del_ret;
		}else{
			return false;
		}
	}

	public static function delMappingArticleByAid($aid){
		if(!empty($aid)){
			$del_ret = tag_db::delMappingArticleByAid($aid);
			return $del_ret;
		}else{
			return false;
		}
	}
	public static function getTagInfoByGroupId($group_id){

		$subject_arr1 = tag_db::getTagGroupInfo($group_id);
		if ($subject_arr1 === false) return false;
        $subject_arr = $subject_arr1->items;
		
		$ret        = new stdClass;
		$ret->data  = $subject_arr;
		$ret->page  = $subject_arr1->page;
		$ret->size  = $subject_arr1->pageSize;
		$ret->total = $subject_arr1->totalPage;
		$ret->totalSize = $subject_arr1->totalSize;
        return $ret;
	}

	public static function addTagBelongGroup($groupId,$tagNameArr){		
		if(empty($groupId) || empty($tagNameArr)){
			return false;
		}
		$ret = array();
		$tagName = array();
		foreach($tagNameArr as $tname){
			$tagName[] = '\''.$tname.'\'';
		}
		$tagRet = tag_db::getTagByNameArr($tagName);
		$existTag = array();
		$addTag   = array();
		if(!empty($tagRet->items)){
			foreach($tagRet->items as $to){
				$existTag['name'][] = $to['name'];
				$existTag['tag_id'][] = $to['pk_tag'];
			}
			$addTag = array_diff($tagNameArr,$existTag['name']);
			$blong=array();
			foreach($existTag['tag_id'] as $tid){
				if(!empty($tid)){
					$belong = array();
					$blong['fk_tag'] = $tid;
					$blong['fk_group'] = $groupId;
					$ret[] = tag_db::addbelongTagGroup($blong);
				}	
			}
		}else{
			$addTag = $tagNameArr;
		}
		if(!empty($addTag)){
			$tagData = array();
			$tagIdArr= array();
			foreach($addTag as $ao){
				if(!empty(trim($ao))){
					$tagData['name'] = $ao;
					$tagIdArr[] = tag_db::addTag($tagData);
				}
			}
			if(!empty($tagIdArr)){
				$blong=array();
				foreach($tagIdArr as $vo){
					if(!empty($vo)){
						$belong = array();
						$blong['fk_tag'] = $vo;
						$blong['fk_group']=$groupId;
						$ret[] = tag_db::addbelongTagGroup($blong);
					}
				}
			}
		}
		return $ret;
	}
	
	/*
	 *mgr后台修改group的所有标签
	 */
	public static function updateTagBelongGroup($groupId,$tagNameStr){		
		if(empty($groupId)){
			return false;
		}
		$tagNameArr = array();
		$tempName = explode("\n",$tagNameStr);
		if(!empty($tempName)){
			foreach($tempName as $to){
				if(!empty(trim($to))){
					$tagNameArr[] = trim($to);
				}
			}
		}
		$belongRet =tag_db::getTagGroupInfo($groupId);
		if(!empty($belongRet->items)){
			$existTagName = array();
			$addTagName = array();
			foreach($belongRet->items as $bo){
				if(!in_array($bo['tag_name'],$tagNameArr)){
					$delTagIdArr[] = $bo['fk_tag'];  
				}else{
					$existTagName[] = $bo['tag_name'];
				}
			}
			if(!empty($existTagName)){
				$addTagName = array_diff($tagNameArr,$existTagName);
			}else{
				$addTagName = $tagNameArr;
			}
			if(!empty($addTagName)){
				$addRet = self::addTagBelongGroup($groupId,$addTagName);
			}
			if(!empty($delTagIdArr)){
				$delRet = tag_db::delTagBelongGroupByGidAndTidArr($groupId,$delTagIdArr);
			}
			if(empty($addRet)){
				$ret = $delRet;
			}else{
				$ret = $addRet;
			}
		}elseif(!empty($tagNameArr)){
			$ret = self::addTagBelongGroup($groupId,$tagNameArr);
		}else{
			$ret = true;
		}
		return $ret;
	}

	/*
	 *添加课程标签添加tag并添加tag与group的关系
	 */
	public static function addCourseTagBelongGroup($courseId,$groupId,$tagNameArr){		
		if(empty($courseId) || empty($groupId) || empty($tagNameArr)){
			return false;
		}
		$tagIdArr = self::addTag($tagNameArr);
		if(!empty($tagIdArr)){		
			$belongRet =tag_db::getTagGroupInfo($groupId);
			if(!empty($belongRet->items)){
				$exist = array();
				$add = array();
				foreach($belongRet->items as $bo){
					if(in_array($bo['fk_tag'],$tagIdArr)){
						$exist[] = $bo['fk_tag'];  
					}
				}
				if(!empty($exist)){
					$exist = array_diff($tagIdArr,$exist);
				}else{
					$add = $tagIdArr;
				}
				if(!empty($add)){
					foreach($add as $tid){
						if(!empty($tid)){
							$belong = array();
							$blong['fk_tag'] = $tid;
							$blong['fk_group'] = $groupId;
							tag_db::addbelongTagGroup($blong);
						}	
					}
				}
			}else{
				foreach($tagIdArr as $tid){
					if(!empty($tid)){
						$blong = array();
						$blong['fk_tag'] = $tid;
						$blong['fk_group'] = $groupId;
						tag_db::addbelongTagGroup($blong);
					}	
				}
			}
			$ret = self::addMappingCourse($courseId,$groupId,$tagIdArr);
			return $ret;
		}else{
			return false;
		}
		
	}
	
	public static function addTag($tagNameArr){
		if( empty($tagNameArr) ){
			return false;
		}
		$tagName = array();
		foreach($tagNameArr as $tname){
			$tagName[] = '\''.$tname.'\'';
		}
		$tagRet = tag_db::getTagByNameArr($tagName);
		$existTag = array();
		$existTagIdArr = array();
		$addTag   = array();
		$tagIdArr= array();
		if(!empty($tagRet->items)){
			foreach($tagRet->items as $to){
				$existTag['name'][] = $to['name'];
				$existTagIdArr[] = $to['pk_tag'];
			}
			$addTag = array_diff($tagNameArr,$existTag['name']);
		}else{
			$addTag = $tagNameArr;
		}
		if(!empty($addTag)){
			$tagData = array();
			foreach($addTag as $ao){
				if(!empty(trim($ao))){
					$tagData['name'] = $ao;
					$tagIdArr[] = tag_db::addTag($tagData);
				}
			}
		}
		$ret = array_merge($tagIdArr,$existTagIdArr);
		return $ret;
	}

    public static function getTweeter($tagId, $page = 1, $length = -1)
    {
        $res = tag_db_mappingTagTweeterDao::getTweeter($tagId, $page, $length);
        if (empty($res->items)) return [];

        return $res;
    }
}
