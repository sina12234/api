<?php
/**
  * 用户API
  * @link http://wiki.gn100.com/doku.php?id=docs:api:user
  **/
class user_student{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	/**
	  * 用户创建立接口
	  */
	function pageSet($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($inPath[3])){
			return $ret;
		}
		$user_db = new user_db;
		$user_info = $user_db->getUser($inPath[3]);
		if(empty($user_info)){
			$ret->result->code = -2;
			$ret->result->msg= "user do not exists";
			return $ret;
		}
		$student_info=array();
		if(!empty($params->grade)){
			$student_info['grade'] = $params->grade;
		}
		if(!empty($params->student_name)){
			$student_info['student_name'] = $params->student_name;
		}
		if(!empty($params->region_level0)){ $student_info['region_level0'] = $params->region_level0; }
		if(!empty($params->region_level1)){ $student_info['region_level1'] = $params->region_level1; }
		if(!empty($params->region_level2)){ $student_info['region_level2'] = $params->region_level2; }
		if(!empty($params->school_type)){ $student_info['school_type'] = $params->school_type; }
		if(!empty($params->school_id)){ $student_info['school_id'] = $params->school_id; }
		
        /*
		if(empty($student_info)){
			$ret->result->code = -3;
			$ret->result->msg= "not update info";
			return $ret;
		}
         */
		$student_info['fk_user']=$user_info['pk_user'];
		$db_ret = $user_db->setStudentProfile($student_info);
		
		if($db_ret){
			$ret->result->code = 0;
		}
		return $ret;
	}
	
	/*
	 * 1.REPLACE INTO `t_user_student_profile` SET `region_level0` = "1" , `region_level1` = "36" , `school_type` = "6" , `school_id` = "137" , `fk_user` = "304"
	 * 2.REPLACE INTO `t_user_student_profile` SET `grade` = "2003" , `fk_user` = "304"
	 * pageSet方法 执行2的时候会把1修改的数据制空
	 */
	function pageSet2($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		
		if(empty($inPath[3])){
			return $ret;
		}

		$user_db = new user_db;
		$user_info = $user_db->getUser($inPath[3]);
		
		if(empty($user_info)){
			$ret->result->code = -2;
			$ret->result->msg= "user do not exists";
			return $ret;
		}
		$student_info=array();
		if(!empty($params->grade)){
			$student_info['grade'] = $params->grade;
		}
		if(!empty($params->student_name)){
			$student_info['student_name'] = $params->student_name;
		}
		if(!empty($params->region_level0)){ $student_info['region_level0'] = $params->region_level0; }
		if(!empty($params->region_level1)){ $student_info['region_level1'] = $params->region_level1; }
		if(isset($params->region_level2)){ $student_info['region_level2'] = $params->region_level2; }
		if(!empty($params->school_type)){ $student_info['school_type'] = $params->school_type; }
		if(!empty($params->school_id)){ $student_info['school_id'] = $params->school_id; }
		
		$res_userstudent = $user_db::getUserStudentProfile($user_info['pk_user']);
		if($res_userstudent==false)
		{
			$student_info['fk_user']=$user_info['pk_user'];
			$db_ret = $user_db->setStudentProfile($student_info);
		}else
		{
			$db_ret = $user_db->setStudentProfile2($user_info['pk_user'],$student_info);
		}
	
		if($db_ret){
			$ret->result->code = 0;
		}
		return $ret;
	}
	
	function pageGet($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($inPath[3])){
			return $ret;
		}
		$uid = $inPath[3];
		$user_db = new user_db;
		$user_info = $user_db->getStudentProfile($uid);
		if(empty($user_info)){
			$ret->result->code = -2;
			$ret->result->msg= "profile do not exists";
			return $ret;
		}
		$ret->data=$user_info;
		return $ret;
	}

	public function pageGetRegistrationUser($inPath)
	{
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($inPath[3])){
			return $ret;
		}
		$uid = $inPath[3];
		$user_db = new user_db;
		$user_info = $user_db->listUsersByUserIds($uid);
		$user_profile = $user_db->listProfilesByUserIds($uid);
		if(empty($user_info)){
			$ret->result->code = -2;
			$ret->result->msg= "profile do not exists";
			return $ret;
		}

		$ret->page  = $user_info->page;
		$ret->total = $user_info->totalSize;
		$ret->totalPage = $user_info->totalPage;
		if(!empty($user_info->items) || !empty($user_profile->items))
		{
			$ret->data = [
				'user_info' => $user_info->items,
				'user_profile' =>$user_profile->items
			];
		}

		return $ret;
	}
}
