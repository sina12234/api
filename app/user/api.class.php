<?php
class user_api{

	const USER_NAME_MAX = 20;
	const USER_NAME_MIN = 1;
	const USER_PWD_MAX = 16;
	const USER_PWD_MIN = 6;

	private static $gender = array('male', 'female');
	private static $status = array('normal', 'disabled');
	private static $site = array(1, 2);
	
	private static $func_list = array(
		//'name' => 'checkUserName',			
		'gender' => 'checkGender',			
		'status' => 'checkStatus',			
		'password' => 'checkPassword',			
		'mobile' => 'checkMobile',			
		'email' => 'checkEmail',			
		'birthday' => 'checkBirthday',			
		'site' => 'checkSite',			
	);
	
	private static $user_resource = array(
		'uid' => '',
		'name' => '',
		'real_name' => '',
		'birthday' => '',	
		'site' => 1,	
		'gender' => '',	
		'status' => 'normal',	
		'mobile' => '',
		'email' => '',	
		'register_ip' => '',	
		'create_time' => '',	
		'last_updated' => '',	
		'last_login' => 'last_login',	
		'avatar' => array(
			'large' => '',	
			'medium' => '',	
			'small' => '',	
		),
		'types' => array(
			'student' => false,
			'teacher' => false,
			'organization' => false,
		),	
		'profile' => array(
			'real_name' => '',
			'address' => '',
			'birth_place' => '',
			'desc' => '',
			'zip_code' => '',
		),	
		'student' => array(
			'student_name' => '',
		),	
	);

	private static $user_resmp = array(
		'basic' => array(
			'uid' => 'pk_user',	
			'name' => 'name',	
			'birthday' => 'birthday',	
			'site' => 'site',	
			'email' => 'email',	
			'mobile' => 'mobile',	
			'register_ip' => 'register_ip',	
			'create_time' => 'create_time',	
			'last_updated' => 'last_updated',	
			'last_login' => 'last_login',	
			'gender' => array(1 => 'male', 2=> 'female',),	
			'status' => array(1 => 'normal', 2 => 'disabled',),	
		),
		'parterner' => array(
			'source' => 'source',	
			'uid' => 'parterner_uid',	
			'thumb_url' => 'thumb_url',	
			'auth_code' => 'auth_code',	
		),
		'avatar' => array(
			'large' => 'thumb_big',	
			'medium' => 'thumb_med',	
			'small' => 'thumb_small',	
		),
		'mobile' => array(
			'mobile_supplier' => 'supplier',	
			'mobile_province' => 'province',	
			'mobile_city' => 'city',	
		),
		'profile' => array(
			'real_name' => 'real_name',
			'address' => 'address',
			'birth_place' => 'birth_place',
			'desc' => 'desc',
			'zip_code' => 'zip_code',
		),	
		'student' => array(
			'student_name' => 'student_name',
		),	
		'teacher' => array(
			'title' => 'title',
			'college' => 'college',
			'years' => 'years',
			'diploma' => 'diploma',
		),	
	);

	private static $grade_mp = array(
		1001 => '小学一年级',
		1002 => '小学二年级',
		1003 => '小学三年级',
		1004 => '小学四年级',
		1005 => '小学五年级',
		1006 => '小学六年级',
		2001 => '初中一年级',
		2002 => '初中二年级',
		2003 => '初中三年级',
		3001 => '高中一年级',
		3002 => '高中二年级',
		3003 => '高中三年级',
	);

	private static	$array_org_list = array(
		"fk_user_owner"=>"user_owner_id",
		"pk_org"=>"oid",
		"name"=>"name",
		"thumb_big"=>"thumb_big",
		"thumb_med"=>"thumb_med",
		"thumb_small"=>"thumb_small",
		"desc"=>"desc",
		"status"=>"status",
		"create_time"=>"create_time",
		"last_updated"=>"last_updated",
	);
	public static function encryptPassword($password){
		return md5($password."GN100".md5($password));
	}

	/**
	 * 获取用户信息
	 * @param int $uid 用户ID
	 * @return boolean|array
	 */
	public function get($uid){
		if (empty($uid) || !is_int($uid)) {
			return false;		
		}
		$user_db = new user_db;
		$basic_data = $user_db->getUser($uid);
		if (empty($basic_data)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);	
		}
		$user = self::$user_resource;
		foreach (self::$user_resmp['basic'] as $k => $v) {
			if (is_array($v)) {
				if (isset($v[$basic_data[$k]])) {
					$user[$k] = $v[$basic_data[$k]];
				}
			} else {
				if (isset($basic_data[$v])) {
					$user[$k] = $basic_data[$v];
				}
			}
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x01) {
			$user['types']['student'] = true;
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x02) {
			$user['types']['teacher'] = true;
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x04) {
			$user['types']['organization'] = true;
		}
		if($user['types']['student']){
			$user['student']=user_db::getStudentProfile($uid);
		}
		if($user['types']['teacher']){
			$user['teacher']=user_db::getTeacherProfile($uid,false);
		}
		if($user['types']['organization']){
			$user['organization']=user_db::getOrgByUid($uid,false);
		}
		foreach (self::$user_resmp['avatar'] as $k => $v) {
			$user['avatar'][$k] = $basic_data[$v];
		}
		$email_data = user_db::getUserEmailByID($uid);
		if (!empty($email_data['email'])) {
			$user['email'] = $email_data['email'];
		}
		$mobile_data = user_db::getUserMobileByID($uid);
		if (!empty($mobile_data['mobile'])) {
			$user['mobile'] = $mobile_data['mobile'];
		}
		$profile_data = user_db::getUserProfileByID($uid);
		if (!empty($profile_data)) {
			foreach (self::$user_resmp['profile'] as $k => $v) {
				if (!isset($profile_data[$k])) {
					continue;
				}
				$user['profile'][$k] = $profile_data[$v];
			}
		}
		return $user;
	}
	
	public function getstudent($uid){
		if (empty($uid) || !is_int($uid)) {
			return false;		
		}
		$user_db = new user_db;
		$course_db = new course_db;
		$basic_data = $user_db->getstuShowInfo($uid);
		if (empty($basic_data)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);	
		}
		$user = self::$user_resource;
		foreach (self::$user_resmp['basic'] as $k => $v) {
			if (is_array($v)) {
				if (isset($v[$basic_data[$k]])) {
					$user[$k] = $v[$basic_data[$k]];
				}
			} else {
				if (isset($basic_data[$v])) {
					$user[$k] = $basic_data[$v];
				}
			}
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x01) {
			$user['types']['student'] = true;
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x02) {
			$user['types']['teacher'] = true;
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x04) {
			$user['types']['organization'] = true;
		}
		if($user['types']['student']){
			$user['student']=user_db::getStudentProfile($uid);
		}
		if($user['types']['teacher']){
			$user['teacher']=user_db::getTeacherProfile($uid,false);
		}
		if($user['types']['organization']){
			$user['organization']=user_db::getOrgByUid($uid,false);
		}
		foreach (self::$user_resmp['avatar'] as $k => $v) {
			$user['avatar'][$k] = $basic_data[$v];
		}
		$email_data = user_db::getUserEmailByID($uid);
		if (!empty($email_data['email'])) {
			$user['email'] = $email_data['email'];
		}
		$mobile_data = user_db::getUserMobileByID($uid);
		if (!empty($mobile_data['mobile'])) {
			$user['mobile'] = $mobile_data['mobile'];
		}
		$s_course = course_db::getstudentCourse($uid);
		$course_arr= array();
		if(!empty($s_course)){
			foreach($s_course->items as $key=>$val){
				$course_arr[]=$val['fk_course'];
			}
		}
		$str=implode(",",$course_arr);
		$course = course_db::getstudentCount($str);
		$tmp = array();
		$met = array();
		if(!empty($course)){
			foreach($course->items as $m=>$n){
				$tmp['pk_course']=$n['pk_course'];
				$tmp['title']= $n['title'];
				$tmp['create_time'] = $n['create_time'];
				$met[]=$tmp;
			}
		}
		$profile_data = user_db::getUserProfileByID($uid);
		if (!empty($profile_data)) {
			foreach (self::$user_resmp['profile'] as $k => $v) {
				if (!isset($profile_data[$k])) {
					continue;
				}
				$user['profile'][$k] = $profile_data[$v];
			}
		}
		return $user;
	}

	public function getteacher($uid){
		if (empty($uid) || !is_int($uid)) {
			return false;		
		}
		$user_db = new user_db;
		$course_db = new course_db;
		$basic_data = $user_db->getstuShowInfo($uid);
		if (empty($basic_data)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);	
		}
		$user = self::$user_resource;
		foreach (self::$user_resmp['basic'] as $k => $v) {
			if (is_array($v)) {
				if (isset($v[$basic_data[$k]])) {
					$user[$k] = $v[$basic_data[$k]];
				}
			} else {
				if (isset($basic_data[$v])) {
					$user[$k] = $basic_data[$v];
				}
			}
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x01) {
			$user['types']['student'] = true;
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x02) {
			$user['types']['teacher'] = true;
		}
		if (!empty($basic_data['type']) && $basic_data['type'] & 0x04) {
			$user['types']['organization'] = true;
		}
		if($user['types']['student']){
			$user['student']=user_db::getStudentProfile($uid);
		}
		if($user['types']['teacher']){
			$user['teacher']=user_db::getTeacherProfile($uid,false);
		}
		if($user['types']['organization']){
			$user['organization']=user_db::getOrgByUid($uid,false);
		}
		foreach (self::$user_resmp['avatar'] as $k => $v) {
			$user['avatar'][$k] = $basic_data[$v];
		}
		$email_data = user_db::getUserEmailByID($uid);
		if (!empty($email_data['email'])) {
			$user['email'] = $email_data['email'];
		}
		$mobile_data = user_db::getUserMobileByID($uid);
		if (!empty($mobile_data['mobile'])) {
			$user['mobile'] = $mobile_data['mobile'];
		}
		$s_course = course_db::getstudentCourse($uid);
		$course_arr= array();
		if(!empty($s_course)){
			foreach($s_course->items as $key=>$val){
				$course_arr[]=$val['fk_course'];
			}
		}
		$str=implode(",",$course_arr);
		$course = course_db::getstudentCount($str);
		$tmp = array();
		$met = array();
		if(!empty($course)){
			foreach($course->items as $m=>$n){
				$tmp['pk_course']=$n['pk_course'];
				$tmp['title']= $n['title'];
				$tmp['create_time'] = $n['create_time'];
				$met[]=$tmp;
			}
		}
		$profile_data = user_db::getUserProfileByID($uid);
		if (!empty($profile_data)) {
			foreach (self::$user_resmp['profile'] as $k => $v) {
				if (!isset($profile_data[$k])) {
					continue;
				}
				$user['profile'][$k] = $profile_data[$v];
			}
		}
		$orgRet =$user_db->getAllOrg();
		if(!empty($orgRet->items)){
			foreach($orgRet->items as $org){
				$user['org_name'][$org['fk_user_owner']]=$org;
			}	
		}else{
			$user['org_name'] = '';
		}
		$org_myself = array();
		$teacherOrgRet = $user_db->listOrgTeachersByUserIds($uid);
		if(!empty($teacherOrgRet->items)){
			foreach($teacherOrgRet->items as $tk=>$tv ){
				if(!empty($tv['pk_org'])){
					$org_myself[$tk]['pk_org']=$tv['pk_org'];
					$org_myself[$tk]['fk_user_owner']=$tv['fk_user_owner'];
					$org_myself[$tk]['org_name']=$tv['name'];
					$org_myself[$tk]['org_subname']=$tv['subname'];
				}
			}
		}
        $user['org_myself'] = $org_myself;
		return $user;
	}	
	public function getmgrUser($uid,$params){
		if (empty($uid) || !is_int($uid)) {
			return false;		
		}
		$user = user_db::getUser($uid);
		if(!empty($user)){
			if($user['type'] & 0x02 >0){
				$org_id =user_db::getmgrOrgUser($uid);
				$sub_id =user_db::getmgrSubmain($uid);
				$profile_id=user_db::getOrgProfileByUid($uid);
				if(empty($org_id) && empty($sub_id) && empty($profile_id)){
					$res = array();
					$res['fk_user_owner']=$uid;
					$res['create_time']=date("Y-m-d H:i:s");
					$res['status']=1;
					$mk = user_db::addorg($res);
					if($mk>0){
						$org_profile = array();
						$org_profile['fk_org']= $mk;
						$org_profile['fk_user_owner']=$uid;
						$org_profile['subname']=$params->subname;
						$res_profile = user_db::addOrgProfile($org_profile);
						$user_teacher = array();
						$user_teacher['role']=1;
						$org_teacher= user_db::setOrgUser($mk ,$uid,$user_teacher, $status=1);
						$dat =array();
						$dat['fk_user']=$uid;
						$dat['subdomain']=$params->subdomain;
						$dat['status']=1;
						$sub = user_db::addmgrSubmain($dat);
					}
				}else{
					$tmp= array('result' => array(
								  "code"  => '-100',
								   "msg"  => '已经添加为机构!',
					));
					
				}
			}
			$update =user_db::updateMgrUser($uid,array("type"=>7));
			if(!empty(user_db::getUserProfile($uid))){
				$ret = user_db::updateUserProfile($uid,array("real_name"=>$params->real_name));
			}else{
				$profile_update = array();
				$profile_update['real_name']=$params->real_name;
				$profile_update['fk_user'] = $uid;
				$ret = user_db::addUserProfile($profile_update);
			}
		}
		return $tmp;
		
	}

	/**
	 * 增加用户(共三种模式)
	 * 1.手机号注册模式
	 * 2.邮箱注册模式
	 * 3.parterner_id注册模式

	 * @param object $params
	 * $params->name //必须 
	 * $params->mobile | $params->email | $params->parterner_id //必须
	 * $params->password
	 * $params->gender
	 * $params->real_name
	 * $params->register_ip
	 * $params->thumb_big
	 * $params->thumb_big
	 * $params->type
	 * $params->status
	 * $params->thumb_med
	 * $params->thumb_small
	 * $params->source // https://wiki.gn100.com/doku.php?id=docs:db:user 
	 * $params->owner_from //机构所有者
	 * @return int $user_id
	 * $user_id > 0 成功，是用户ID
	 * $user_id <=0 失败，错误代码
	 */
	public static function AddUser($params){
		if(empty($params->name)){
			return -1;
		}
		if(empty($params->real_name)) $params->real_name = $params->name;

		$mode = 0;//注册模式
		if(!empty($params->mobile)){
			$mode = 1;
		}elseif(!empty($params->email)){//暂时不提供支持
			$mode = 2;
			return -22;
		}elseif(!empty($params->parterner_id)){
			$mode = 3;
		}else{
			return -1;
		}
		//{{{检查敏感词
/*
		$censor_db = new censor_db;
		$db_ret = $censor_db->searchWord($params->name);
		if(!empty($db_ret)){
			return -3;
		}
 */
		//}}}
		//{{{校验内容
		$user_db = new user_db;
		if($mode==1){//校验手机信息
			//手机号校验
			if(utility_valid::mobile($params->mobile)===false){//不是手机号
				return -6;
			}
			$user_id = $user_db->getUserIDByMobileFromMaster($params->mobile);
			if($user_id === false){//数据库错误
				return -5;
			}elseif($user_id>0){//手机号已经注册
				return -2;
			}
		}elseif($mode==2){//保留
			return -22;
		}elseif($mode==3){
			$parter_info = $user_db->getUserParternerById($params->parterner_id);
			if(empty($parter_info)){
				return -5;
			}
		}
		//}}}

		//{{{创建立用户
		$user_info=array();
		$user_info['name']=$params->name;
		$user_info['real_name']=$params->real_name;
		$user_info['mobile'] = !empty($params->mobile)?$params->mobile:"";
		if(!empty($params->password)){
			$user_info['password']=user_api::encryptPassword($params->password);;
		}

		$user_info['create_time']=date("Y-m-d H:i:s");
		if(isset($params->status)){
			$user_info['status']	=$params->status;
		}else{
			$user_info['status']	=user_const::ENABLED;
		}
		if(isset($params->type)){
			$user_info['type']		=$params->type;
		}else{
			$user_info['type']		=user_const::TYPE_USER;
		}

		//用户校验状态
		if($mode==1){
			$user_info['verify_status']=user_const::VERIFY_INNER | user_const::VERIFY_MOBILE;
		}elseif($mode==2){
			$user_info['verify_status']=user_const::VERIFY_INNER | user_const::VERIFY_EMAIL;
		}elseif($mode==3){
			$user_info['verify_status']=user_const::VERIFY_PARTERNER;
		}
		if(!empty($params->register_ip)){
			$user_info['register_ip']=utility_ip::toLong($params->register_ip);
		}

		if(isset($params->gender)){
			if(is_string($params->gender)){
				if($params->gender=='male'){
					$params->gender=1;
				}else{
					$params->gender=2;
				}
			}
			$user_info['gender']=$params->gender; 
		}
		if(!empty($params->thumb_big)){
			$user_info['thumb_big']=$params->thumb_big; 
		}else{
			$user_info['thumb_big']="5,05844dc4357b";
		}
		if(!empty($params->thumb_med)){ 
			$user_info['thumb_med']=$params->thumb_med; 
		}else{
			$user_info['thumb_med']="4,058582450327";
		}
		if(!empty($params->thumb_small)){ 
			$user_info['thumb_small']=$params->thumb_small; 
		}else{
			$user_info['thumb_small']="7,05863b61ed03";
		}
		if(!empty($params->source)){ $user_info['source']=$params->source; }

		$user_id = $user_db->addUser($user_info);
		if(empty($user_id)){
			return -7;
		}
		//}}}

		//{{{添加绑定关系
		if($mode==1){//添加手机号
			$user_mobile= array();
			$user_mobile['fk_user']=$user_id;
			$user_mobile['mobile']=$params->mobile;
			$db_ret = $user_db->addUserMobile($user_mobile);
			if($db_ret===false){
				return -21;
			}
		}elseif($mode==2){//添加邮箱
				return -22;
		}elseif($mode==3){//更新绑定关系
			$db_ret = $user_db->bindParterner($params->parterner_id,$user_id);
			if($db_ret===false){
				return -23;
			}
		}
		//}}}


		$userProfile=array();
		$userProfile['fk_user'] = $user_id;
		$userProfile['real_name'] = $params->real_name;
		if($user_db->addUserProfile($userProfile)===false){
		}
		//add owner_from create by ztf 2015/12/16
		$stat=array();
		$stat['fk_user']=$user_id;
		if(empty($params->owner_from))$params->owner_from = 0;
		$stat['owner_from']=$params->owner_from;
		$stat_api = new stat_api;
		if($stat_api->addUserStat($stat)===false){
		}
		//TODO X平台增加用户
		if(defined("XPLATFROM_LOGIN") && $mode == 1){
			$xplatform_api = new xplatform_api;
			if($xplatform_api->getUserInfo($params->mobile,$ret)===false){
				//增加用户
				$xplatform_api->register2ByPhone($params->mobile, $params->password,$params->real_name);
			}else{
				//修改密码
				if(!empty($params->password)){
					$xplatform_api->changePwd2($params->mobile, $params->password);
				}
			}
		}

		return $user_id;
	}
	/**
	 * 后台增加机构
	 */
	public function mgrAddOrg($params){
		$ret =new stdClass;
		$user_info = new stdclass;
		$password = substr($params->mobile,5,6);
		$user_info->name = !empty($params->mobile) ? $params->mobile : '';
		$user_info->password = $password;
		$user_info->create_time=date("Y-m-d H:i:s");
		$user_info->status	=user_const::ENABLED;
		$user_info->type		=7;
		$user_id = self::addUser($user_info);
		if($user_id){
			$res = array();
			$res['fk_user_owner']=$user_id;
			$res['create_time']=date("Y-m-d H:i:s");
			$res['status']=1;
		
			$org_id = user_db::addorg($res);
			$org_profile = array();
			$org_profile['fk_org']= $org_id;
			$org_profile['fk_user_owner']=$user_id;
			$org_profile['subname']=!empty($params->subname) ? $params->subname : '';
			$res_profile = user_db::addOrgProfile($org_profile);
			$user_teacher = array();
			$user_teacher['role']=1;
			$org_teacher= user_db::setOrgUser($org_id ,$user_id,$user_teacher, $status=1);
			$dat =array();
			$dat['fk_user']=$user_id;
			$dat['subdomain']=!empty($params->subdomain) ? $params->subdomain : '';
			$dat['status']=1;
			$sub = user_db::addmgrSubmain($dat);
			
		}
		return $sub;
		
	}
	
	public function getOrgAllName(){
		$org_arr = user_db::getOrgAllName();
		$res = array();
		$ids= array();
		if(!empty($org_arr->items)){
			foreach($org_arr->items as $k=>$v){
				$ids['pk_org']= $v['pk_org'];
				$ids['name'] = $v['name'];
				$res[]= $ids;
			}
		}
		return $res;
	}

	public function search($params){
		if (isset($params['email']) || isset($params['mobile'])) {
			if (isset($params['email'])) {
				$uid = 	user_db::getUserIDByEmail($params['email']);
			} else {
				$uid = 	user_db::getUserIDByMobile($params['mobile']);
			}
			$user = user_db::getUser($uid);
			if (empty($user)) {
				return array('result' => array(
					"code" => '-102',
					"msg" => 'the user does not exist',
				));
			}
			$ret = $this->formatUserList($user);
			return array(
				'data' => array($ret),	
				'page' => 1, 'size' => 1, 'total' => 1,
			);
		} else if (isset($params['grade'])) {
			$grade = empty($params['grade']) ? "" : $params['grade'];
			$page = empty($params['page']) ? 1 : $params['page'];
			$length = empty($params['length']) ? 20 : $params['length'];
			$user_list = $this->getlistByGrade((int)$grade, $page, $length);
			if (empty($user_list)) {
				return array('result' => array(
					"code" => '-202',
					"msg" => 'empty data',
				));
			}
			return $user_list;
		} else if (isset($params['type'])) {
			$page = empty($params['page']) ? 1 : $params['page'];
			$length = empty($params['length']) ? 20 : $params['length'];
			$user_list = $this->getlistByType($params['type'], $page, $length);
			if (empty($user_list)) {
				return array('result' => array(
					"code" => '-202',
					"msg" => 'empty data',
				));
			}
			return $user_list;
		}
		return array('result' => array(
			"code" => '-101',
			"msg" => 'invalid parameter',
		));
	}


	

	/**
	 * 更新用户信息
	 * @param int $uid 用户ID
	 * @param array $data 用户数据
	 * @return boolean|array 
	 */
	public function update($uid, array $data){
		if (empty($uid) || !is_int($uid) || empty($data)) {
			return array(
				"code" => '-101',
				"msg" => 'invalid parameter',
			);	
		}
		$user = user_db::getUser($uid);
		$mobile_data = user_db::getUserMobileByID($uid);
		$email_data = user_db::getUserEmailByID($uid);
		if (empty($user)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);	
		}
		$ret = $this->check($data);
		if ($ret !== true) {
			return $ret;	
		}
		if (isset($data['email']) && !empty($email_data['email']) &&
			$email_data['email'] != $data['email'] &&
		   	!empty(user_db::getUserIDByEmail($data['email']))) {
			return array(
				"code" => '-102',
				"msg" => 'the email has been registered',
			);	
		}
		if (isset($data['mobile']) && !empty($mobile_data['mobile']) &&
			$mobile_data['mobile'] != $data['mobile'] &&
		   	!empty(user_db::getUserIDByMobile($data['mobile']))) {
			return array(
				"code" => '-103',
				"msg" => 'the mobile has been registered',
			);	
		}
		$basic_update = $this->parseBasic($data);
		if (isset($data['types'])) {
			$type = $this->parseTypes($user['type'], $data['types']);
			if ($type !== false) {
				$basic_update['type']  = $type;
			}
		}
		if ($basic_update == false) {
			$basic_update=array();
		}

		if (!empty($basic_update['password'])) {
			//TODO 加入X平台逻辑 - 修改密码
			if(defined("XPLATFROM_LOGIN")){
				$xplatform_api = new xplatform_api;
				$r_2 = $xplatform_api->changePwd2($mobile_data['mobile'], $basic_update['password'],$r);
				error_log(var_export($r_2,true),3,"/tmp/u.log");
				error_log(var_export($r,true),3,"/tmp/u.log");
			}
			$basic_update['password'] = self::encryptPassword($basic_update['password']);
		}

		$ret = false;
		if (!empty($data['mobile'])) {
			if (empty($mobile_data)) {
				$ret = user_db::addUserMobile(array('fk_user' => $uid,'mobile'=>$data['mobile']));
			} else {
				$ret = user_db::updateUserMobile($uid, array('mobile'=>$data['mobile']));
			}	
		}
		if (!empty($data['email'])) {
			if (empty($email_data)) {
				$ret = user_db::addUserEmail(array('fk_user' => $uid,'email'=>$data['email']));
			} else {
				$ret = user_db::updateUserEmail($uid, array('email'=>$data['email']));
			}	
		}
		if (isset($data['profile'])) {
			$profile_update = $this->parseProfile($data['profile']);
			if ($profile_update != false) {
                $profile_update['last_updated']=date('Y-m-d H:i:s',time()); 
				if (!empty(user_db::getUserProfile($uid))) {
					$ret = user_db::updateUserProfile($uid, $profile_update);
				} else {
					$profile_update['fk_user'] = $uid;
					$ret = user_db::addUserProfile($profile_update);
				}
			}
		}
		if (isset($data['student'])) {
            $student=array();
		    foreach ($data['student'] as $k => $v) {
				$student[$k]=$v;
		    }
		    $student['fk_user']=$uid;
		    $ret = user_db::setStudentProfile($student);
        }
		if(!empty($basic_update)){
            $basic_update['last_updated']=date('Y-m-d H:i:s',time());
			$ret = user_db::updateUser($uid, $basic_update);
		}
		return $ret!==false;
	}
	/**
	 * 更新老师信息
	 */
	public function updateteacherInfo($uid, $data){
		//如果设置了机构，更新老师机构列表
		$isTeacher=false;
		if(!empty($data['org_name'])){
			$mt = array();
			$org_info = user_db::getorgUserTeacher($uid);
			if(!empty($org_info->items)){

				foreach($org_info->items as $a=>$b){
					$mt[]=$b['fk_org'];
				}
				$o_id = explode(",",$data['org_name']);
				$c=count($o_id);
				$arr =array();
				if($c==count($mt)){
					foreach($o_id as $k=>$v){
						$arr[$k]['id1']=!empty($o_id[$k]) ? $o_id[$k] : '';
						$arr[$k]['id2']=!empty($mt[$k]) ? $mt[$k] : '';
					}
					foreach($arr as $a=>$b){
						$dm= user_db::updateorgUserTeacher($uid,$b['id1'],$b['id2'],$status=1);  
						$isTeacher=true;
					}
				}else{
					$res = user_db::delOrgteacherUser($uid,array("status"=>1));
					$data['role']=1;
					foreach($o_id as $v){
						$res =user_db::setOrgUser($v,$uid,$data);
						$isTeacher=true;
					}
				}
			}else{
				if(!empty($data['org_name'])){
					$o_id = explode(",",$data['org_name']);
					foreach($o_id as $v){
						$res =user_db::setOrgUser($v,$uid,$data);
						$isTeacher=true;
					} 
				}	
			}
		}
		//if($isTeacher){
		//	$data['types']=array("teacher"=>true,"student"=>true);//设置老师类型
		//}else{
		//	$data['types']=array("teacher"=>false,"student"=>true);//设置老师类型
		//}
		$ret = $this->update($uid,$data);
		return $ret!==false;
	}


	public function singleTeacherInfoHave($uid, $data){
		if (empty($uid) || !is_int($uid) || empty($data)) {
			return array(
				"code" => '-101',
				"msg" => 'invalid parameter',
			);	
		}
		$con= array();
		if($data['gender']=='male'){
			$con['gender']=1;
		}elseif($data['gender']=='female'){
			$con['gender']=2;
		}
		$user = user_db::getUser($uid);
		$ret = false;
		
		$real = array();
		if (isset($data['real_name'])) {
			//$profile_update = $this->parseProfile($data['profile']);
                $real['last_updated']=date('Y-m-d H:i:s',time()); 
				$real['real_name']=$data['real_name'];
				if (!empty(user_db::getUserProfile($uid))) {
					$ret = user_db::updateUserProfile($uid, $real);
				} else {
					$real['fk_user'] = $uid;
					$ret = user_db::addUserProfile($real);
				}
		}
		
		if(isset($data['gender'])&&!empty($data['gender'])){
            $con['last_updated']=date('Y-m-d H:i:s',time());
			$ret = user_db::updateUser($uid, $con);
		}
		return $ret!==false;
	}

	/**
	 * 获取用户列表
	 * @param int $page 页数
	 * @param int $length 每页数据
	 * @return boolean|array
	 */
	public function getlist($page = 1, $length = 20){
		$ret = user_db::listUser($page, $length);
		if (empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$user_list1[] = $this->formatUserList($user);
		}	
			$user_list=$user_list1;
		$zxcity = array("北京","上海","重庆","天津"); 
		foreach($user_list1 as $key=>$v){	
			if(empty($v["mobile_province"])) $v["mobile_province"]="暂无";
			if(empty($v["mobile_city"])) $v["mobile_city"]="暂无";
			if(in_array($key["mobile_province"],$zxcity)){
				$user_list[$key]["mobile_city"] = $v["mobile_province"];
				$user_list[$key]["mobile_province"] = $v["mobile_province"];
			}else{
				$user_list[$key]["mobile_province"] = $v["mobile_province"];
				$user_list[$key]["mobile_city"] = $v["mobile_city"];
			}
		}	

		$data = array(
			'page' => $ret->page,
			'size' => $ret->pageSize,
			'total' => $ret->totalPage,
			'data' => $user_list,
		);
		return $data;
	}

	public function getteacherList($page = 1, $length = 20){
		$ret = user_db::teacherListUser($page, $length);
		$user_db= new user_db;
		$pk_data = array();
		if (empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$userList[$user['pk_user']] = $this->formatUserList($user);
		}	
		if(!empty($ret->items)){
			foreach($ret->items as $a=>$b){
				$pk_data[]=$b['pk_user'];
			}
		}
		$str=implode(",",$pk_data);
		$teacherOrgRet = $user_db->listOrgTeachersByUserIds($str);
		if(!empty($teacherOrgRet->items)){
			foreach($teacherOrgRet->items as $to){
				if($to['fk_user_owner']){
					$ownerIdArr[$to['fk_user_owner']] = $to['fk_user_owner'];
				}
			}
			$subdomainList = $user_db->getSubdomainByUidArr($ownerIdArr);		
			if(!empty($subdomainList->items)){
				foreach($subdomainList->items as $so){
					$domainList[$so['fk_user']] = $so['subdomain'];
				}
				foreach($teacherOrgRet->items as $org){
					if(!empty($domainList[$org['fk_user_owner']])){
						$org['subdomain'] = $domainList[$org['fk_user_owner']];
					}else{
						$org['subdomain'] = '';
					}
					$teacherOrgInfo[$org['fk_user']][] = $org;
				}
			}
			if(!empty($userList)){
				foreach($userList as $uid=>$vo){
					if(!empty($teacherOrgInfo[$uid])){
						$userList[$uid]['org_info'] = $teacherOrgInfo[$uid];
					}
				}
			}
		}
		$userRet = $userList;
		$zxcity = array("北京","上海","重庆","天津"); 
		foreach($userList as $key=>$v){	
			if(empty($v["mobile_province"])) $v["mobile_province"]="暂无";
			if(empty($v["mobile_city"])) $v["mobile_city"]="暂无";
			if(in_array($key["mobile_province"],$zxcity)){
				$userRet[$key]["mobile_city"] = $v["mobile_province"];
				$userRet[$key]["mobile_province"] = $v["mobile_province"];
			}else{
				$userRet[$key]["mobile_province"] = $v["mobile_province"];
				$userRet[$key]["mobile_city"] = $v["mobile_city"];
			}
		}	
		$data = array(
			'page'  => $ret->page,
			'size'  => $ret->pageSize,
			'total' => $ret->totalPage,
			'data'  => $userRet,
		);
		return $data;
	}

	public function studentList($page = 1, $length = 20){
		$ret = user_db::studentList($page, $length);
		if (empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$user_list1[] = $this->formatUserList($user);
		}	
			$user_list=$user_list1;
		$zxcity = array("北京","上海","重庆","天津"); 
		foreach($user_list1 as $key=>$v){	
			if(empty($v["mobile_province"])) $v["mobile_province"]="暂无";
			if(empty($v["mobile_city"])) $v["mobile_city"]="暂无";
			if(in_array($key["mobile_province"],$zxcity)){
				$user_list[$key]["mobile_city"] = $v["mobile_province"];
				$user_list[$key]["mobile_province"] = $v["mobile_province"];
			}else{
				$user_list[$key]["mobile_province"] = $v["mobile_province"];
				$user_list[$key]["mobile_city"] = $v["mobile_city"];
			}
		}	

		$data = array(
			'page' => $ret->page,
			'size' => $ret->pageSize,
			'total' => $ret->totalPage,
			'data' => $user_list,
		);
		return $data;
	}

	public function getsearchTeacherShow($params){
		$ret = user_db::getsearchteacherShow($params);
		
		$user_db= new user_db;
		$pk_data = array();
		if (empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$userList[$user['pk_user']] = $this->formatUserList($user);
		}	
		if(!empty($ret->items)){
			foreach($ret->items as $a=>$b){
				$pk_data[]=$b['pk_user'];
			}
		}
		$str=implode(",",$pk_data);
		$teacherOrgRet = $user_db->listOrgTeachersByUserIds($str);
		if(!empty($teacherOrgRet->items)){
			foreach($teacherOrgRet->items as $to){
				if($to['fk_user_owner']){
					$ownerIdArr[$to['fk_user_owner']] = $to['fk_user_owner'];
				}
			}
			$subdomainList = $user_db->getSubdomainByUidArr($ownerIdArr);		
			if(!empty($subdomainList->items)){
				foreach($subdomainList->items as $so){
					$domainList[$so['fk_user']] = $so['subdomain'];
				}
				foreach($teacherOrgRet->items as $org){
					if(!empty($domainList[$org['fk_user_owner']])){
						$org['subdomain'] = $domainList[$org['fk_user_owner']];
					}else{
						$org['subdomain'] = '';
					}
					$teacherOrgInfo[$org['fk_user']][] = $org;
				}
			}
			if(!empty($userList)){
				foreach($userList as $uid=>$vo){
					if(!empty($teacherOrgInfo[$uid])){
						$userList[$uid]['org_info'] = $teacherOrgInfo[$uid];
					}
				}
			}
		}
		$userRet = $userList;
		$zxcity = array("北京","上海","重庆","天津"); 
		foreach($userList as $key=>$v){	
			if(empty($v["mobile_province"])) $v["mobile_province"]="暂无";
			if(empty($v["mobile_city"])) $v["mobile_city"]="暂无";
			if(in_array($key["mobile_province"],$zxcity)){
				$userRet[$key]["mobile_city"] = $v["mobile_province"];
				$userRet[$key]["mobile_province"] = $v["mobile_province"];
			}else{
				$userRet[$key]["mobile_province"] = $v["mobile_province"];
				$userRet[$key]["mobile_city"] = $v["mobile_city"];
			}
		}	
		$data = array(
			'page'  => $ret->page,
			'size'  => $ret->pageSize,
			'total' => $ret->totalPage,
			'data'  => $userRet,
		);
		return $data;
	}

	public function getsearchShow($params){
		$ret = user_db::getsearchShow($params);
		if (empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$user_list1[] = $this->formatUserList($user);
		}	
			$user_list=$user_list1;
		$zxcity = array("北京","上海","重庆","天津"); 
		foreach($user_list1 as $key=>$v){	
			if(empty($v["mobile_province"])) $v["mobile_province"]="暂无";
			if(empty($v["mobile_city"])) $v["mobile_city"]="暂无";
			if(in_array($key["mobile_province"],$zxcity)){
				$user_list[$key]["mobile_city"] = $v["mobile_province"];
				$user_list[$key]["mobile_province"] = $v["mobile_province"];
			}else{
				$user_list[$key]["mobile_province"] = $v["mobile_province"];
				$user_list[$key]["mobile_city"] = $v["mobile_city"];
			}
		}	

		$data = array(
			'page' => $ret->page,
			'size' => $ret->pageSize,
			'total' => $ret->totalPage,
			'data' => $user_list,
		);
		return $data;
	}	

	
	public function getlistByGrade($grade, $page = 1, $length = 20){
		$ret = user_db::listUserByGrade($grade, $page, $length);
		if (empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$user_list[] = $this->formatUserList($user);
		}
		$data = array(
			'page' => $ret->page,
			'size' => $ret->pageSize,
			'total' => $ret->totalPage,
			'data' => $user_list,
		);
		return $data;
	}
	
	private function getlistByType($type, $page = 1, $length = 20){
		if ($type == 'teacher') {
			$ret = user_db::listTeacher($page, $length);
		} else if ($type == 'organization') {
			$ret = user_db::listOrganization($page, $length);
		} else if ($type == 'student') {
			$ret = user_db::listStudent($page, $length);
		}
		if (empty($ret) || empty($ret->items)) {
			return false;	
		}
		foreach ($ret->items as $user) {
			$r = $this->formatUserList($user);
			$user_list[] = $r;
		}
		$data = array(
			'page' => $ret->page,
			'size' => $ret->pageSize,
			'total' => $ret->totalPage,
			'data' => $user_list,
		);
		return $data;
	}
	/**
	 * 格式化用户数据
	 * @param array $data
	 */
	private function formatUserList($data){
		if (empty($data)) {
			return false;	
		}
		$user = self::$user_resource;
		$basic = $this->getDataByMP(self::$user_resmp['basic'], $data);
		if (!empty($basic)) {
			$user = array_merge($user, $basic);
		}
		$user['avatar']= $this->getDataByMP(self::$user_resmp['avatar'], $data);
		$mobile_info = $this->getDataByMP(self::$user_resmp['mobile'], $data);
		if (!empty($mobile_info)) {
			$user = array_merge($user, $mobile_info);
		}
		if (!empty($data['type']) && $data['type'] & 0x01) {
			$user['types']['student'] = true;
			$student= $this->getDataByMP(self::$user_resmp['student'], $data);
			if (!empty($student)) {
				$user['student'] = $student;
			}
		}
		if (!empty($data['type']) && $data['type'] & 0x02) {
			$user['types']['teacher'] = true;
			$teacher= $this->getDataByMP(self::$user_resmp['teacher'], $data);
			if (!empty($teacher)) {
				$user['teacher'] = $teacher;
			}
		}
		if (!empty($data['type']) && $data['type'] & 0x04) {
			$user['types']['organization'] = true;
		}
		if (isset($data['grade']) && !empty(self::$grade_mp[$data['grade']]) ) {
			$user['grade'] = self::$grade_mp[$data['grade']];
		}
		$parterner = $this->getDataByMP(self::$user_resmp['parterner'], $data);
		if (!empty($parterner)) {
			$user['parterner'] = $parterner;
		}
		$user['real_name'] = $data['real_name'];
		unset($user['profile']);
		return $user;
	}

	private function getDataByMP($mp, $data){
		$ret = array();
		if (empty($data) || empty($mp) ||
			!is_array($data) || !is_array($mp)) {
			return $ret;
		}
		foreach ($mp as $k => $v) {
			if (is_array($v)) {
				if (isset($v[$data[$k]])) {
					$ret[$k] = $v[$data[$k]];
				}
			} else {
				if (isset($data[$v])) {
					$ret[$k] = $data[$v];
				}
			}
		}
		return $ret;
	}

	/**
	 * 解析基本信息
	 * @param array $data 基本信息
	 * @return 
	 */
	private function parseBasic($data){
		if (empty($data)) {
			return false;	
		}
		$mp = array(
			'name' => 'name',	
			'password' => 'password',
			'real_name' => 'real_name',
			'birthday' => 'birthday',	
			'site' => 'site',	
			'gender' => array('male' => 1,'female' => 2,),	
			'status' => array('normal' => 1,'disabled' => 2,),	
		);
		$update = array();	
		foreach ($mp as $k => $v) {
			if (isset($data[$k])) {
				if (is_array($v)) {
					if (isset($v[$data[$k]])) {
						$update[$k]	= $v[$data[$k]];
					}
				} else {
					if (isset($data[$v])) {
						$update[$k]	= $data[$v];
					}
				}
			}
		}
		if (isset($data['avatar'])) {
			$avatar = $this->parseAvatar($data['avatar']);
			if ($avatar !== false) {
				$update = array_merge($update, $avatar);
			}
		}
		if (empty($update)) {
			return false;	
		}
		return $update;
	}


	/**
	 * 解析用户详细信息
	 * @param array $profile 详细信息
	 * @return boolean|array 
	 */
	private function parseProfile($profile){
		if (empty($profile)) {
			return false;	
		}
		$mp = array(
			'real_name' => 'real_name',	
			'address' => 'address',	
			'birth_place' => 'birth_place',	
			'desc' => 'desc',	
			'zip_code' => 'zip_code',	
		);
		$update = array();
		foreach ($mp as $k => $v) {
			if (isset($profile[$k])) {
				$update[$v]	= $profile[$k];
			}
		}
		if (empty($update)) {
			return false;	
		}
		return $update;
	}

	/**
	 * 解析用户类型
	 * @param array $types 用户类型
	 * @return boolean|array false解析失败 array更新数据
	 */
	private function parseTypes($utype, $types){
		if (empty($types)) {
			return false;	
		}
		$mp = array(
			'student' => 0x01,
			'teacher' => 0x02,
			'organization' => 0x04,
		);
		foreach ($types as $k => $v) {
			if (isset($mp[$k])) {
				$utype = $v ? ($utype | $mp[$k]) : ($utype & ~$mp[$k]); 
			}
		}
		return $utype;
	}

	/**
	 * 解析头像参数
	 * @param array $avatar  头像大中小
	 * @return boolean|array false解析失败 array更新数据
	 */
	private function parseAvatar($avatar){
		if (empty($avatar)) {
			return false;	
		}
		$mp = array(
			'large' => 'thumb_big',	
			'medium' => 'thumb_med',	
			'small' => 'thumb_small',	
		);
		$update = array();
		foreach ($mp as $k => $v) {
			if (isset($avatar[$k])) {
				$update[$v] = $avatar[$k];
			}
		}
		if (empty($update)) {
			return false;	
		}
		return $update;	
	}

	/**
	 * 校验用户信息是否正确
	 * @param arry $data 用户信息
	 * @return boolean|array true表示正确，否则返回error array
	 */
	public function check($data){
		foreach (self::$func_list as $name => $func) {
			if (isset($data[$name])) {
				$ret = $this->$func($data[$name]);	
				if ($ret !== true) {
					return $ret;	
				}
			}
		}
		return true;
	}

	/**
	 * 校验用户名
	 * @param string $name 用户名
	 * @return boolean|array true表示正确，否则返回error
	 */
	private function checkUserName($name){
		$regex = '/^[_\w\d\x{4e00}-\x{9fa5}]+/iu';
		if (empty(trim($name))) {
			return array(
				"code"	=> '111',
				"msg"	=> 'the username can not empty',
			);	
		}
		//验证是否合法
		if (!preg_match($regex, $name)) {
			return array(
				"code"	=> '112',
				"msg"	=> 'invalid username ',
			);	
		}
		//验证最小长度
		if (mb_strlen($name, 'UTF-8') < self::USER_NAME_MIN) {
			return array(
				"code"	=> '113',
				"msg"	=> 'the username at least 2 word',
			);	
		}
		//最大长度
		if (mb_strlen($name, 'UTF-8') > self::USER_NAME_MAX) {
			return array(
				"code"	=> '114',
				"msg"	=> 'the username at most 10 word',
			);	
		}
		return true;
	}

	/**
	 * 验证密码是否正确
	 * @param string $password 用户密码
	 * @return boolean|array  ture表示正确,否则返回error array
	 */
	private function checkPassword($password){
		$regex = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]+$/';
		if (empty(trim($password))) {
			return array(
				"code"	=> '121',
				"msg"	=> 'password can not empty',
			);	
		}
		//校验密码是否合法
		if (!preg_match($regex, $password)) {
			return array(
				"code"	=> '122',
				"msg"	=> 'invalid password ',
			);	
		}
		//最小长度
		if (mb_strlen($password, 'UTF-8') < self::USER_PWD_MIN) {
			return array(
				"code"	=> '123',
				"msg"	=> 'the password at least 6 word',
			);	
		}
		//最大长度
		if (mb_strlen($password, 'UTF-8') > self::USER_PWD_MAX) {
			return array(
				"code"	=> '124',
				"msg"	=> 'the password at most 16 word',
			);	
		}
		return true;
	}
	
	/**
	 * 验证状态是否正确
	 * @param string $status 用户状态
	 * @return boolean|array  ture表示正确,否则返回error array
	 */
	private function checkStatus($status){
		if (!empty($status) && !in_array($status,
			self::$status)) {
			return array(
				"code"	=> '131',
				"msg"	=> 'invalid status parameter',
			);	
		}
		return true;
	}

	/**
	 * 验证性别是否正确
	 * @param string $gender 用户性别
	 * @return boolean|array  ture表示正确,否则返回error array
	 */
	private function checkGender($gender){
		if (!empty($gender) && !in_array($gender,
			self::$gender)) {
			return array(
				"code"	=> '141',
				"msg"	=> 'invalid gender parameter',
			);	
		}
		return true;
	}

	/**
	 * 验证手机号是否正确
	 * @param string $mobile 用户手机
	 * @return boolean|array  ture表示正确,否则返回error array
	 */
	private function checkMobile($mobile){
		$regex = '/^(13\d{9})|(15\d{9})|(18\d{9})$/';
		if (!empty(trim($mobile)) &&
		   	!preg_match($regex, $mobile)) {
			return array(
				"code"	=> '151',
				"msg"	=> 'invalid mobile parameter',
			);	
		}
		return true;
	}

	/**
	 * 验证邮箱是否正确
	 * @param string $email 用户邮箱
	 * @return boolean|array  ture表示正确,否则返回error array
	 */
	private function checkEmail($email){
		$regex = '/^[\w\d]+[\w\d-.]*@[\w\d-.]+\.[\w\d]{2,10}$/i';
		if (!empty(trim($email)) &&
		   	!preg_match($regex, $email)) {
			return array(
				"code"	=> '161',
				"msg"	=> 'invalid mobile parameter',
			);	
		}
		return true;
	}

	private function checkSite($site){
		if (!empty($site) && !in_array($site,
			self::$site)) {
			return array(
				"code"	=> '171',
				"msg"	=> 'invalid site parameter',
			);	
		}
		return true;
	}

	private function checkBirthday($birthday){
		if (!empty($birthday) && empty(strtotime($birthday))) {
			return array(
				"code"	=> '181',
				"msg"	=> 'invalid birthday parameter',
			);	
		}
		return true;	
	}
	/*
	 *获取机构信息 
	 *				$oid    机构id  
	 *				$page   页数
	 *				$length 每页显示个数
	 * TODO
	 */
	public function getlistorg($uid = null,$page = null,$length = null){
		$user_db = new user_db();
		$listorg1 = $user_db->listorg($uid,$page,$length);
		$listorg = $listorg1->items;
		$count = count($listorg);
		for($i = 0;$i<$count;$i++){
			$relist[$i]["org_id"]=  	$listorg[$i]["pk_org"];
			$relist[$i]["user_owner"]=	$listorg[$i]["fk_user_owner"];
			$relist[$i]["name"]=		$listorg[$i]["name"];
			$relist[$i]["thumb_big"]=	$listorg[$i]["thumb_big"];
			$relist[$i]["thumb_med"]=	$listorg[$i]["thumb_med"];
			$relist[$i]["thumb_small"]=	$listorg[$i]["thumb_small"];
			$relist[$i]["desc"]=	$listorg[$i]["desc"];
			$relist[$i]["status"]=		$listorg[$i]["status"];
			$relist[$i]["create_time"]=	$listorg[$i]["create_time"];
			$relist[$i]["last_updated"]=$listorg[$i]["last_updated"];
//			$relist[$i]["status"]=$array_status[$listorg[$i]["status"]];
			// "admin_status",这个字段数据库没有 意义：管理员审核状态
		}
		
		$ret = new stdClass;
		if(empty($relist)){$relist = 0;}
		$ret->data = $relist;
		$ret->page = $listorg1->page;
		$ret->size = $listorg1->pageSize;
		$ret->total = $listorg1->totalPage;
		$relist = SJson::encode($ret);	
		return $relist;
	}


	public function getlistorginfo($page,$params,$length = 8){
		$user_db = new user_db();
		$list_org1 = $user_db->listorginfo($page,$params,$length);
		$relist = array();
		if(!empty($list_org1->items)){
			$list_org = $list_org1->items;
			$count = count($list_org);
			for($i = 0;$i<$count;$i++){
				$relist[$i]["pk_org"]=  	$list_org[$i]["pk_org"];
				$relist[$i]["fk_user_owner"]=	$list_org[$i]["fk_user_owner"];
				$relist[$i]["name"]=		$list_org[$i]["name"];
				$relist[$i]["idcard_pic"]=		$list_org[$i]["idcard_pic"];
				$relist[$i]["qualify_pic"]=		$list_org[$i]["qualify_pic"];
				$relist[$i]["status"]=	$list_org[$i]["status"];
				$relist[$i]["verify_status"]=	$list_org[$i]["verify_status"];
				$relist[$i]["last_updated"]=	$list_org[$i]["last_updated"];
				$relist[$i]["create_time"]=		$list_org[$i]["create_time"];
				$arr = user_db::getrealNameSubdomain($list_org[$i]["fk_user_owner"]);
				$relist[$i]['plat_name'] = $arr['name'];
				$relist[$i]['subname'] = $arr['subname'];
				$relist[$i]['subdomain'] = $arr['subdomain'];
				$relist[$i]['real_name'] = $arr['real_name'];
				$relist[$i]['hot_phone'] = $list_org[$i]['hotline'];
			}
		}
		$ret = new stdClass;
		if(empty($relist)){$relist = 0;}
		$ret->data = $relist;
		$ret->page = $list_org1->page;
		$ret->size = $list_org1->limit;
		$ret->total = $list_org1->totalPage;
		$relist = SJson::encode($ret);	
		return $relist;
	}

	public function getchecklistorginfo($uid = null,$page = 1,$length = 8){
		$user_db = new user_db();
		$list_org1 = $user_db->selectCheckListTmp($uid,$page,$length);
		$list_org = $list_org1->items;
		$count = count($list_org);
		/*for($i = 0;$i<$count;$i++){
			$relist[$i]["pk_org"]=  	$list_org[$i]["pk_org"];
			$relist[$i]["fk_user_owner"]=	$list_org[$i]["fk_user_owner"];
			$relist[$i]["name"]=		$list_org[$i]["name"];
			$relist[$i]["idcard_pic"]=		$list_org[$i]["idcard_pic"];
			$relist[$i]["qualify_pic"]=		$list_org[$i]["qualify_pic"];
			$relist[$i]["status"]=	$list_org[$i]["status"];
			$relist[$i]["last_updated"]=		$list_org[$i]["last_updated"];
//			$relist[$i]["status"]=$array_status[$listorg[$i]["status"]];
			// "admin_status",这个字段数据库没有 意义：管理员审核状态
		}*/
		$ret = new stdClass;
		$ret->data = $list_org;
		$ret->page = $list_org1->page;
		$ret->size = $list_org1->pageSize;
		$ret->total = $list_org1->totalPage;
		$list_org1 = SJson::encode($ret);	
		return $list_org1;
	}
	/**
	 * 获取机构信息
	 * @param int $uid userid
	 * @return boolean|array
	 */
	public function getorgByUid($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$get_org = $user_db->getOrgByUid($uid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}


	public function getorgByUidTmp($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$get_org = $user_db->getOrgProfileByUidTmp($uid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}
	public function getOrgByOwner($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$get_org = $user_db->getorgByOwner($uid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}
	public function getOrgSetHotType($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$get_org = $user_db->getOrgSetHotType($uid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}
	public function getOrgNameInfo($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
        $get_org = $user_db->getOrgNameInfo($uid);
        if(empty($get_org)){
            $org_info = array();
            $org_info["pk_org"] = $uid;
            $addorg = $user_db->addorg($org_info);
            if(!$addorg){               
                return false;
            }
        }
		$get_org = $user_db->getOrgNameInfoTmp($uid);
		$tmp = $user_db:: getSubDomainByUserId($get_org['user_owner_id']);
		$dis =$tmp['subdomain'];
		$get_org['subdomain']=$dis;
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}

	public function getOrgByOwnerTmp($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
        $get_org = $user_db->getOrgProfileByUidTmp($uid);
        /*if(empty($get_org)){
            $org_info = array();
            $org_info["fk_user_owner"] = $uid;
            $addorg = $user_db->addorg($org_info);
            if(!$addorg){               
                return false;
            }
        }*/
		//$get_org = $user_db->getorgByOwner($uid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}
	public function getorg($oid){
		if (empty($oid) || !is_numeric($oid)) {
			return false;		
		}
		$ret = new stdClass;
		$user_db = new user_db();
		$get_org = $user_db->getorg($oid);
		if(empty($get_org)){
				return false;
		}
		$ret->data = $get_org;
		return $ret;
	}
    //获取机构公告
	public function getOrgAbout($uid){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$get_org = $user_db->getorgAbout($uid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}
	public function updateOrg($uid,$data){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$uid = (int)$uid;
		$user_db = new user_db();
		$get_org = $user_db->getorgByUid($uid);
		if(empty($get_org)){
			$org_info = array();
			$org_info["fk_user_owner"] = $uid;
			$addorg = $user_db->addorg($org_info);
			if(!$addorg){				
				return false;
			}
		}
		$ret = new stdClass;
		$org =array();
		foreach(self::$array_org_list as $key=>$value){
			if(isset($data[$value])){
				$org[$key] = $data[$value];
			}
		};
		$time = date("Y-m-d H:i:s");
		$org["last_updated"] = $time;
		$set_org = $user_db->updateOrg($uid,$org);
		return $set_org;
	}


	public function updateOrgTmp($uid,$data){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$uid = (int)$uid;
		$user_db = new user_db();
		$get_org = $user_db->getorgProfileByUidTmp($uid);
		if(empty($get_org)){
			$org_info = array();
			$org_info["fk_user_owner"] = $uid;
			$addorg = $user_db->addorg($org_info);
			if(!$addorg){				
				return false;
			}
		}
		$ret = new stdClass;
		$org =array();
		foreach(self::$array_org_list as $key=>$value){
			if(isset($data[$value])){
				$org[$key] = $data[$value];
			}
		};
		$time = date("Y-m-d H:i:s");
		$org["last_updated"] = $time;
		$set_org = $user_db->updateOrg($uid,$org);
		return $set_org;
	}
	public function updateOrgProfile($uid,$data){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$uid = (int)$uid;
		$user_db = new user_db();
		$org_profile=$user_db->getOrgProfileByUid($uid);
		if(empty($org_profile)){
            $orginfo=$user_db->getOrgByOwner($uid);
			$data["fk_org"] = $orginfo['oid'];
			$data["fk_user_owner"] = $uid;
			return $user_db->addOrgProfile($data);
		}
		$data["last_updated"] = date("Y-m-d H:i:s");
		return $user_db->updateOrgProfile($uid,$data);
	}
	

	public function updateOrgProfileTmp($uid,$data){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$uid = (int)$uid;
		$user_db = new user_db();
		$org_profile=$user_db->getOrgProfileByUidTmp($uid);
		if(empty($org_profile)){
            $orginfo=$user_db->getOrgByOwner($uid);
			$data["fk_org"] = $orginfo['oid'];
			$data["fk_user_owner"] = $uid;
			$data['tmp_status'] = 0;
			return $user_db->addOrgProfileTmp($data);
		}
		$data["last_updated"] = date("Y-m-d H:i:s");
		return $user_db->updateOrgProfileTmp($uid,$data);
	}


	public function updateOrgLogoTmp($uid,$data){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$org_profile=$user_db->getOrgProfileByUidTmp($uid);
		if(empty($org_profile)){
            $orginfo=$user_db->getOrgByOwner($uid);
			$data["fk_org"] = $orginfo['oid'];
			$data["fk_user_owner"] = $uid;
			$data['tmp_status'] = 0;
			return $user_db->addOrgProfileTmp($data);
		}
		return $user_db->updateOrgProfileTmp($uid,$data);
	}

	public function getOrgSlide($sid){
		if (empty($sid) || !is_numeric($sid)) {
			return false;		
		}
		$user_db = new user_db();
		$get_org = $user_db->getOrgSlide($sid);
		$ret = new stdClass;
		$ret->data = $get_org;
		return $ret;
	}
	public function getOrgSlideList($uid,$page,$length){
		$user_db = new user_db();
		return $user_db->getOrgSlideList($uid,$page,$length);
	}
	public function addOrgSLide($data){
		$user_db = new user_db();
		$db_ret = $user_db->addOrgSlide($data);
		return $db_ret;
	}
	public function updateOrgSlide($sid,$data){
		if (empty($sid) || !is_numeric($sid)) {
			return false;		
		}
		$user_db = new user_db();
		$db_ret = $user_db->updateOrgSlide($sid,$data);
		return $db_ret;
	}
    public function delOrgSlide($sid){
        if(empty($sid)){
            return false;
        }
        $user_db=new user_db;
        return $user_db->delOrgSlide($sid);
    }
	public function updateOrgLogo($uid,$data){
		if (empty($uid) || !is_numeric($uid)) {
			return false;		
		}
		$user_db = new user_db();
		$db_ret = $user_db->updateOrgLogo($uid,$data);
		return $db_ret;
	}
	
	
	public function listOrgUser($oid,$all,$star,$page,$pageSize){
		$user_db = new user_db();
		$db_ret=$user_db->listOrgUser($oid,$all,$star,$page,$pageSize);
        if(!empty($db_ret->items)){
            foreach($db_ret->items as $k=>$v){
                $db_ret->items[$k]['roles']=array();
               if($v['user_role']&0x01||$v['role']==1||$v['role']==0){
                    $db_ret->items[$k]['roles'][]='general';
               } 
               if($v['user_role'] &0x02){
                    $db_ret->items[$k]['roles'][]='assistant';
               } 
               if($v['user_role']&0x04||$v['role']==2){
                    $db_ret->items[$k]['roles'][]='admin';
               } 
            }
        }
        return $db_ret;
	}

    public function getListOrgUserByMajor($oid,$major,$all,$star,$page,$pageSize){
        $user_db = new user_db();
        return $user_db->getListOrgUserByMajor($oid,$major,$all,$star,$page,$pageSize);
    }

	public function getOrgUserinfo($oid,$uid,$page,$pageSize){
		return user_db::getOrgUserinfo($oid,$uid,$page,$pageSize);
	}
	public function addFav($fdata){
		if (empty($fdata)){
			return false;		
		}
	//	define("DEBUG",true);
		$time = date("Y-m-d H:i:s");
		$data["fk_user"] = $fdata["user_id"];
		$data["fk_course"] = $fdata["course_id"];
		$data["last_updated"] = $time;
		$data["del_fav"] = 0;//为0表示不是删除的数据
		$ret = user_db::addFav($data);

		return $ret;
	}
	
	public function getOrgNum($cityId)
    {
       $user_db = new user_db();
       $res = $user_db->getOrgCount($cityId);
       return $res->items;
    }

    public function getOrgByCity($cityId)
    {
        $user_db = new user_db();
       
        $res = $user_db->getOrgByCityId($cityId);
        return $res->items;
    }

	public function listFav($cid,$uid,$page,$length){
		//define("DEBUG",true);
		$time = date("Y-m-d H:i:s");
		$user_db = new user_db();
		$ret_fav1 = $user_db->listFav($cid,$uid,$page,$length);
		$ret_fav = $ret_fav1->items;
		if(empty($ret_fav)){$ret_fav = 0;}
		$ret = new stdClass;
		$ret->data = $ret_fav;
		$ret->page = $ret_fav1->page;
		$ret->size = $ret_fav1->pageSize;
		$ret->total = $ret_fav1->totalPage;
		return $ret;
	}
    //添加公告
    public function addNotice($notice_data){
        if(empty($notice_data)){
            return false;
        }
        $user_db=new user_db;
        return $user_db->addNotice($notice_data);
    }
    //修改公告
    public function updateNotice($nid,$notice_data){
        if(empty($notice_data)){
            return false;
        }
        $user_db=new user_db;
        return $user_db->updateNotice($nid,$notice_data);
    }
    //删除公告
    public function delNotice($nid){
        if(empty($nid)){
            return false;
        }
        $user_db=new user_db;
        return $user_db->delNotice($nid);
    }
    //置顶公告
    public function topNotice($nid,$uid){
        if(empty($nid)){
            return false;
        }
        $user_db=new user_db;
        return $user_db->topNotice($nid,$uid);
    }
    //取消置顶公告
    public function noTopNotice($nid){
        if(empty($nid)){
            return false;
        }
        $user_db=new user_db;
        return $user_db->noTopNotice($nid);
    }
    //公告列表
	public function getNoticeList($page,$length,$uid,$catId,$orgId){
		$user_db = new user_db();
		$notice_result = $user_db->getNoticeList($page,$length,$uid,$catId);
		$cateInfo = $user_db->getNoticeCateEmpty($uid,$catId);
		$cateArr = array();
		if(!empty($cateInfo->items)){
			foreach($cateInfo->items as $k=>$v){
				$cateArr[] = isset($v['fk_cate']) ? $v['fk_cate'] : '';
			}
		}
		$cateData = user_db::noticeCategoryList(array("fk_org"=>$orgId,"status"=>1));
		$inArrayCate = array();
		if(!empty($cateData->items)){
			foreach($cateData->items as $a=>$b){
				$inArrayCate[$a]['pk_cate'] = isset($b['pk_cate']) ? $b['pk_cate'] : 0;
				$inArrayCate[$a]['name'] = isset($b['name']) ? $b['name'] : '';
			}
		}
		$exitCate = array();
		if(!empty($inArrayCate)){
			foreach($inArrayCate as $m=>$n){
				if(in_array($n['pk_cate'],$cateArr)){
					$exitCate[$m] = $n;
				}
			}
		}
		
		if(empty($notice_result)){
            return false;     
        }
        foreach($notice_result->items as $k=>$v){
            $notice_result->items[$k]['notice_title_sub50']=mb_substr($v['notice_title'],0,50,'utf-8').'...';
            $notice_result->items[$k]['notice_title_sub20']=mb_substr($v['notice_title'],0,20,'utf-8').'...';
        }
		$ret = new stdClass;
		$ret->data = $notice_result->items;
		$ret->page = $notice_result->page;
		$ret->size = $notice_result->pageSize;
		$ret->total = $notice_result->totalPage;
		$ret->cateInfo = $exitCate;
		return $ret;
	}
    //获取公告
	public function getNotice($nid){
		if (empty($nid) || !is_numeric($nid)) {
			return false;		
		}
		$ret = new stdClass;
		$user_db = new user_db();
		$get_notice = $user_db->getNotice($nid);
		if(empty($get_notice)){
				return false;
		}
		$ret->data=$get_notice;
		return $ret;
	}
	public function getUserIdBySubDomain($subdomain){
		if (empty($subdomain)){
			return false;		
		}
		$ret = new stdClass;
		$user_db = new user_db();
		$getUserIdBySubDomain = $user_db->getUserIdBySubDomain($subdomain);
		if(empty($getUserIdBySubDomain)){
				return false;
		}
		$ret= $getUserIdBySubDomain;
		return $ret;
	}
	/*
	 *机构设置老师排序
	 */
	public function Usersetsort($oid,$user_id,$newSort){
		$ret = new stdClass;
		//列取机构用户信息
		$teacherSOrtArr =user_db::listOrgUser($oid);
		if(!empty($teacherSOrtArr->items)){
			//获取当前用户信息
			$getorgUserinfo =user_db::getorgUserinfo($oid,$user_id);
			$oldSort = $getorgUserinfo["sort"];
			$maxSort = $teacherSOrtArr->items[0]['sort'];
			$minSort = end($teacherSOrtArr->items)['sort'];
			//判断是正序还是倒叙 预防排序更换
			if($maxSort > $minSort){
				if($newSort>$maxSort) $newSort = $maxSort;
				if($newSort<$minSort) $newSort = $minSort;
				$maxSortdata = $teacherSOrtArr->items[0];
				$minSortdata = end($teacherSOrtArr->items);
			}else{
				if($newSort<$maxSort) $newSort = $maxSort;
				if($newSort>$minSort) $newSort = $minSort;
				$minSortdata = $teacherSOrtArr->items[0];
				$maxSortdata = end($teacherSOrtArr->items);
			}
			//如果输入的排序数字比最大值还大或者比最小值还小就取最大或最小值
			if($newSort >= $oldSort ){
				$uparray = array("`sort` = `sort` - 1");
				$where = array("sort > ".$oldSort);
				$where[] = "sort <= ".$newSort;
				$where["fk_org"] = $oid;
				//define("DEBUG",true);
				$ret1= user_db::setOtherOrgUser($uparray,$where);	
				if($ret1!==false){
					$ret2 = user_db::setorgUsersort($oid,$user_id,$newSort);
				}
			}else{
				$uparray = array("`sort` = `sort` + 1");
				$where = array("sort >= ".$newSort);
				$where[] = "sort < ".$oldSort;
				$where["fk_org"] = $oid;
				//				define("DEBUG",true);
				$ret1= user_db::setOtherOrgUser($uparray,$where);	
				if($ret1!==false){
					$ret2 = user_db::setorgUsersort($oid,$user_id,$newSort);
				}
			}
			if($ret2){
				return $ret2;
			}else{
				$ret->result->code=-2;
				$ret->result->msg="fail updated!";
				return $ret;

			}
		}else{
			$ret->result->code=-3;
			$ret->result->msg="not have data!";
			return $ret;
		}
	}
    public function getOwnerIdbyAdminId($oid,$adminId){
        $ret=user_db::getTeacherSpecial($oid,$adminId);
        if($ret===false || $ret['role']!=2){
            return false;
        }
        $orgInfo=user_db::getOrgByTeacher($ret['fk_user']);    
        if(!empty($orgInfo)){
            return $orgInfo['user_owner_id'];
        }else{
            return false;
        }
    }
    public function getUserProfileByUidArr($arr){
        $ret=user_db::getUserProfileByUidArr($arr);
        if($ret===false){
            return false;
		}
		$retdata = array();
		if(!empty($ret->items)){
			$retdata["data"] = $ret->items;
		}
		return $retdata;
    }
	public function listUserIdsBylikeMobileArr($uidsArr, $mobile){
		$ret = user_db::listUserIdsBylikeMobileArr($uidsArr,$mobile);
		if (empty($ret->items)) {
			return false;	
		}
		$data = array(
			'data' => $ret->items,
		);
		return $data;
	}
	public function listUserIdsBylikeNameArr($uidsArr, $name){
		$ret = user_db::listUserIdsBylikeNameArr($uidsArr,$name);
		if (empty($ret->items)) {
			return false;	
		}
		$data = array(
			'data' => $ret->items,
		);
		return $data;
	}
	

	/*
	 *用户统一加经验值方法
	 *@params $uid int 用户id
	 *@params $ruleName string 添加分数对应规则的名称
	 *@return $ret object
	 */
	public static function addUserScore($uid,$ruleName){
		$ret = new stdclass;
		$ret->code = 0;
		$ret->msg = 'success';
		$ret->data = '';
		if(empty($uid) || empty($ruleName)){
			$ret->code = -1;
			$ret->msg = 'params is empty';	
			return $ret;
		}
		$userDb = new user_db;
		$ruleInfo = $userDb->getScoreRuleByName($ruleName);
		if(empty($ruleInfo)){
			$ret->code = -2;
			$ret->msg = 'ruleName is error';	
			return $ret;
		}
		//判断用户添加经验值是否超过上限
		$addFlag = 1;
		$day = date('Y-m-d',time());
		if( $ruleInfo['type'] == 1 ){
			$scoreLimit = $ruleInfo['score_limit'] - $ruleInfo['score'];
			$scoreLog = $userDb->getUserScoreLogCountByDay($day, $ruleInfo['pk_rule'], $uid);
			if(!empty($scoreLog) && !empty($scoreLog->items[0]['score_count'])){
				$todayScore = $scoreLog->items[0]['score_count'];
				if( $todayScore > $scoreLimit ){
					$addFlag = 0;
					$data = array('day'=>$day,'fk_user'=>$uid,'fk_rule'=>$ruleInfo['pk_rule'],'score'=>$ruleInfo['score'],'status'=>0);
					$userDb->addUserScoreLog($data);
					$ret->code = -3;
					$ret->msg = 'score reached limit!';
					return $ret;
				}
			}
		}
		if($addFlag == 1){
			//增加用户经验值日志
			if($ruleName == 'SIGN'){
				$yesterday = date('Y-m-d',time()-3600*24);
				$addScore = $ruleInfo['score'];
				$userLastSign = $userDb->getLastUserSign($uid);
				$oldCombo = 0;
				if(!empty($userLastSign) && $userLastSign['day'] == $yesterday){
					$oldCombo = $userLastSign['combo'];
				}
				if($oldCombo == 4){
					$addSignComboFlag = 1;
					$signComboRule = $userDb->getScoreRuleByName('SIGN_COMBO');
					if($signComboRule['type'] == 1){
						$scoreLimit = $signComboRule['score_limit'] - $signComboRule['score'];
						$comboScoreLog = $userDb->getUserScoreLogCountByDay($day, $signComboRule['pk_rule'], $uid);
						if(!empty($comboScoreLog) && !empty($comboScoreLog->items[0]['score_count'])){
							$todayScore = $comboScoreLog->items[0]['score_count'];
							if( $todayScore > $scoreLimit ){
								$addSignComboFlag = 0;
								$dataCombo = array('day'=>$day,'fk_user'=>$uid,'fk_rule'=>$signComboRule['pk_rule'],'score'=>$signComboRule['score'],'status'=>0);
								$userDb->addUserScoreLog($dataCombo);
							}
						}
					}
					if($addSignComboFlag == 1){
						$addScore = $ruleInfo['score'] + $signComboRule['score'];
						$dataCombo = array('day'=>$day,'fk_user'=>$uid,'fk_rule'=>$signComboRule['pk_rule'],'score'=>$signComboRule['score']);
						$userDb->addUserScoreLog($dataCombo);
					}	
				}
				$data = array('day'=>$day,'fk_user'=>$uid,'fk_rule'=>$ruleInfo['pk_rule'],'score'=>$ruleInfo['score']);
				$userDb->addUserScoreLog($data);
				$newCombo = $oldCombo+1;
				if($newCombo > 5 ){
					$newCombo = 1;
				}
				$signData = array('day'=>$day,'fk_user'=>$uid,'combo'=>$newCombo,'create_time'=>date('Y-m-d H:i:s',time()));
				$signRet = $userDb->addUserSign($signData);
			}else{
				$data = array('day'=>$day,'fk_user'=>$uid,'fk_rule'=>$ruleInfo['pk_rule'],'score'=>$ruleInfo['score']);
				$userDb->addUserScoreLog($data);
				$addScore = $ruleInfo['score'];
			}
			//增加用户经验值
			$levelFlag = 1;
			$userLevel = $userDb->getUserLevelByUid($uid);	
			if(empty($userLevel)){
				$levelFlag = 0;
				$userLevel = array('score' => 0,'fk_level'=>1,'title'=>'书生1','fk_user'=>$uid);
			}
			$currUserScore = $userLevel['score']+$addScore;
			$currUserLevel = $userDb->getLevelByScore($currUserScore);
		
			if($levelFlag == 1){
				$scoreUpdateData = array('fk_level'=>$currUserLevel['pk_level'],'title'=>$currUserLevel['title'],'score'=>$currUserScore);
				$scoreRet = $userDb->updateUserScore($uid,$scoreUpdateData);
			}else{
				$scoreAddData = array('fk_user' => $uid,'fk_level'=>$currUserLevel['pk_level'],'title'=>$currUserLevel['title'],'score'=>$currUserScore);
				$scoreRet = $userDb->addUserScore($scoreAddData);
			}
						
			//判断用户是否升级（小升级还是大升级）
			$upType = 0;
			if($currUserLevel['pk_level'] != $userLevel['fk_level']){	
				$newTitle = $currUserLevel['title'];	
				$tempNewTitle = mb_substr($newTitle,0,2,'utf-8');
				$tempOldTitle = mb_substr($userLevel['title'],0,2,'utf-8');
				$tempNewGrade = mb_substr($newTitle,2,1,'utf-8');
				$tempOldGrade = mb_substr($userLevel['title'],2,1,'utf-8');
				if( $tempNewTitle != $tempOldTitle ){
					$upType = 2;
				}elseif($tempNewTitle == $tempOldTitle && $tempNewGrade != $tempOldGrade ){
					$upType = 1;
				}else{
					$upType = 0;
				}
			}
			if($ruleName == 'SIGN'){
				if($signRet !== false && $scoreRet !== false){
					$ret->data = array(
							'up_type'  => $upType,
							'fk_user'  => $uid,
							'fk_level' => $currUserLevel['pk_level'],
							'title'    => $currUserLevel['title'],
							'score'    => $currUserScore,
							'combo'    => $newCombo,
							'add_score' => $addScore,
						);
					return $ret;
				}else{
					$ret->code = -2;
					$ret->msg = 'add score is failed!';	
					return $ret;	
				}	
			}else{
				if( $scoreRet !== false ){
					$ret->data = array(
							'up_type'  => $upType,
							'fk_user'  => $uid,
							'fk_level' => $currUserLevel['pk_level'],
							'title'    => $currUserLevel['title'],
							'score'    => $currUserScore,
							'add_score' => $addScore,
						);
					return $ret;
				}else{
					$ret->code = -2;
					$ret->msg = 'add score is failed!';	
					return $ret;
				}
			}	
		}
	}
	
	public static function updateOrganizationTag($orgId, $tagNameArr){
		if(empty($orgId) || empty($tagNameArr)){
			return false;
		}
		$tagIdArr = tag_api::addTag($tagNameArr);
		if(!empty($tagIdArr)){
			$orgTagRet = user_db_organizationTagDao::getAllOrgTagByOrgId($orgId);
			$exist = array();
			$update = array();
			$add = array();
			if(!empty($orgTagRet->items)){
				foreach($orgTagRet->items as $tag){
					if(in_array($tag['fk_tag'],$tagIdArr)){
						if($tag['status'] == 0){
							$update[] = $tag['fk_tag'];
						}
						$exist[] = $tag['fk_tag'];
					}else{
						$del[] = $tag['fk_tag'];
					}
				}
				$add = array_diff($tagIdArr,$exist);
				if(!empty($del)){
					$data['status'] = 0;
					$updateRet = user_db_organizationTagDao::update($orgId, $del, $data);
				}
				if(!empty($update)){
					$data['status'] = 1;
					$updateRet = user_db_organizationTagDao::update($orgId, $update, $data);
				}
				if(!empty($del) || !empty($update)){
					$ret = $updateRet;
				}else{
					$ret = true;
				}
			}else{
				$add = $tagIdArr;
			}
			if(!empty($add)){
				foreach($add as $tid){
					$addData['fk_org'] = $orgId;
					$addData['fk_tag'] = $tid;
					$addData['status'] = 1;
					$addRet[] = user_db_organizationTagDao::add($addData);
				}
				return $addRet;
			}else{
				return $ret;
			}
		}else{
			return false;
		}
	}

	public static function getDayPercent($orgId){
		if(empty($orgId)){
			return false;
		}
		$ret = array();
		$start = date('Y-m-d',strtotime("-1 day")).' 00:00:00';
		$end   = date('Y-m-d',strtotime("-1 day")).' 23:59:59';
		$todayRet = user_db_orgAccountOrderContentDao::getOrgIncomeAllByTime($orgId,$start,$end);
		$start = date('Y-m-d',strtotime("-2 day")).' 00:00:00';
		$end   = date('Y-m-d',strtotime("-2 day")).' 23:59:59';
		$lastRet = user_db_orgAccountOrderContentDao::getOrgIncomeAllByTime($orgId,$start,$end);
		if(empty($lastRet['income_all']) && !empty($todayRet['income_all'])){
			$ret['percent'] = '100%';
			$ret['status']  = 1;
		}elseif(!empty($lastRet['income_all']) && empty($todayRet['income_all'])){
			$ret['percent'] = '100%';
			$ret['status']  = -1;
		}elseif(empty($lastRet['income_all']) && empty($todayRet['income_all'])){
			$ret['percent'] = '----';
			$ret['status']  = 0;
		}elseif(!empty($lastRet['income_all']) && !empty($todayRet['income_all'])){
			if($lastRet['income_all'] > $todayRet['income_all']){
				$cha = $lastRet['income_all'] - $todayRet['income_all'];
				$ret['percent'] = floor(($cha/$lastRet['income_all'])*100) . '%';
				$ret['status']  = -1;
			}elseif($lastRet['income_all'] < $todayRet['income_all']){
				$cha = $todayRet['income_all'] - $lastRet['income_all'];
				$ret['percent'] = floor(($cha/$lastRet['income_all'])*100) . '%';
				$ret['status']  = 1;
			}elseif($lastRet['income_all'] == $todayRet['income_all']){
				$ret['percent'] = '0%';
				$ret['status']  = 1;
			}
		}
		return $ret;
	}
	
	public static function getWeekPercent($orgId){
		if(empty($orgId)){
			return false;
		}
		$ret   = array();
		$start = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("w")+1-7,date("Y"))).' 00:00:00';
        $end   = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"))).' 23:59:59';
		$todayRet = user_db_orgAccountOrderContentDao::getOrgIncomeAllByTime($orgId,$start,$end);
		
		$start = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("w")+1-14,date("Y"))).' 00:00:00';
        $end   = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+7-14,date("Y"))).' 23:59:59';
		$lastRet = user_db_orgAccountOrderContentDao::getOrgIncomeAllByTime($orgId,$start,$end);
		if(empty($lastRet['income_all']) && !empty($todayRet['income_all'])){
			$ret['percent'] = '100%';
			$ret['status']  = 1;
		}elseif(!empty($lastRet['income_all']) && empty($todayRet['income_all'])){
			$ret['percent'] = '100%';
			$ret['status']  = -1;
		}elseif(empty($lastRet['income_all']) && empty($todayRet['income_all'])){
			$ret['percent'] = '----';
			$ret['status']  = 0;
		}elseif(!empty($lastRet['income_all']) && !empty($todayRet['income_all'])){
			if($lastRet['income_all'] > $todayRet['income_all']){
				$cha = $lastRet['income_all'] - $todayRet['income_all'];
				$ret['percent'] = floor(($cha/$lastRet['income_all'])*100) . '%';
				$ret['status']  = -1;
			}elseif($lastRet['income_all'] < $todayRet['income_all']){
				$cha = $todayRet['income_all'] - $lastRet['income_all'];
				$ret['percent'] = floor(($cha/$lastRet['income_all'])*100) . '%';
				$ret['status']  = 1;
			}elseif($lastRet['income_all'] == $todayRet['income_all']){
				$ret['percent'] = '0%';
				$ret['status']  = 1;
			}
		}
		return $ret;
	}
	
	public static function getMonthPercent($orgId){
		if(empty($orgId)){
			return false;
		}
		$ret = array();
		$start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y")));
        $end   = date("Y-m-d H:i:s",mktime(23,59,59,date("m") ,0,date("Y")));
		$todayRet = user_db_orgAccountOrderContentDao::getOrgIncomeAllByTime($orgId,$start,$end);
		$start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-2,1,date("Y")));
        $end   = date("Y-m-d H:i:s",mktime(23,59,59,date("m")-1 ,0,date("Y")));
		$lastRet = user_db_orgAccountOrderContentDao::getOrgIncomeAllByTime($orgId,$start,$end);
		if(empty($lastRet['income_all']) && !empty($todayRet['income_all'])){
			$ret['percent'] = '100%';
			$ret['status']  = 1;
		}elseif(!empty($lastRet['income_all']) && empty($todayRet['income_all'])){
			$ret['percent'] = '100%';
			$ret['status']  = -1;
		}elseif(empty($lastRet['income_all']) && empty($todayRet['income_all'])){
			$ret['percent'] = '----';
			$ret['status']  = 0;
		}elseif(!empty($lastRet['income_all']) && !empty($todayRet['income_all'])){
			if($lastRet['income_all'] > $todayRet['income_all']){
				$cha = $lastRet['income_all'] - $todayRet['income_all'];
				$ret['percent'] = floor(($cha/$lastRet['income_all'])*100) . '%';
				$ret['status']  = -1;
			}elseif($lastRet['income_all'] < $todayRet['income_all']){
				$cha = $todayRet['income_all'] - $lastRet['income_all'];
				$ret['percent'] = floor(($cha/$lastRet['income_all'])*100) . '%';
				$ret['status']  = 1;
			}elseif($lastRet['income_all'] == $todayRet['income_all']){
				$ret['percent'] = '0%';
				$ret['status']  = 1;
			}
		}
		return $ret;
	}
	
	public static function getOrderCountByOrgId($orgId,$startTime,$endTime){
		if(empty($orgId)){
			return false;
		}
		$orderRet = user_db_orgAccountOrderContentDao::getOrgOrderCountByOrgId($orgId,$startTime,$endTime);
		if(!empty($orderRet->items)){
			$count = count($orderRet->items);
		}else{
			$count = 0;
		}
		return $count;
	}
	
	public static function getMemberSetListByOrgId($orgId,$status=''){
		if(empty($orgId)){
			return false;
		}
		$msetRet = user_db_orgMemberSetDao::getListByOrgId($orgId,$status);
		if(!empty($msetRet->items)){
			foreach($msetRet->items as $mo){
				$msetIdArr[] = $mo['pk_member_set'];
			}
			$openRet = user_db_orgMemberDao::getUserCountByMemberSetArr($msetIdArr);
			$endTime = date('Y-m-d H:i:s',time());
			$validRet = user_db_orgMemberDao::getUserCountByMemberSetArr($msetIdArr,$endTime);
			$msetIdStr = implode(',',$msetIdArr);
			$courseRet = user_db_orgMemberPriorityDao::getMemberPriorityCountBySetIds($msetIdStr,1);
			
			$courseMember = array();
			$openUser     = array();
			$validUser    = array();
			if(!empty($openRet->items)){
				foreach($openRet->items as $po){
					$openUser[$po['fk_member_set']] = $po['user_count'];
				}
			}
			if(!empty($validRet->items)){
				foreach($validRet->items as $vo){
					$validUser[$vo['fk_member_set']] = $vo['user_count'];
				}
			}
			if(!empty($courseRet)){
				foreach($courseRet->items as $co){
					$courseMember[$co['fk_member_set']] = $co['course_count'];
				}
			}
			foreach($msetRet->items as $mo){
				if(!empty($openUser[$mo['pk_member_set']])){
					$mo['open_user'] = $openUser[$mo['pk_member_set']];
				}else{
					$mo['open_user'] =0;
				}
				if(!empty($validUser[$mo['pk_member_set']])){
					$mo['valid_user'] = $validUser[$mo['pk_member_set']];
				}else{
					$mo['valid_user'] =0;
				}
				if(!empty($courseMember[$mo['pk_member_set']])){
					$mo['course_count'] = $courseMember[$mo['pk_member_set']];
				}else{
					$mo['course_count'] =0;
				}
				$res[] = $mo;
			}
			
		}else{
			$res = false;
		}
		return $res;
	}
	public static function getMemberPriority($setId,$type,$page=1,$length='-1'){
		if(empty($setId)){
			return false;
		}
		$priorityRet = user_db_orgMemberPriorityDao::getMemberPriority($setId,$type,$page,$length);
		$res = array();
		if(!empty($priorityRet->items) && $type == 1){
			foreach($priorityRet->items as $po){
				$courseIdArr[] = $po['object_id'];
				$res[] = $po;
			}
			$courseDb = new course_db;
			$courseInfo = $courseDb->getCourseByCids($courseIdArr);
			if(!empty($courseInfo->items)){
				foreach($courseInfo->items as $co){
					$courseData[$co['course_id']] = $co['course_name'];
				}
				foreach($res as &$ro){
					$ro['course_name'] = $courseData[$ro['object_id']];
				}
			}
		}
		$res['page']      = $priorityRet->page;
		$res['totalPage'] = $priorityRet->totalPage;
		$res['totalSize'] = $priorityRet->totalSize;
		return $res;
	}
	
	public static function updateMemberPriority($setId,$objectIds,$params){
		if(empty($setId) || empty($objectIds)){
			return false;
		}
		$objectIdArr = explode(',',$objectIds);
		$exsitRet = user_db_orgMemberPriorityDao::getMemberPriority($setId,$params['type']);
		$exist = array();
		$update = array();
		$add = array();
		if(!empty($exsitRet->items)){
			foreach($exsitRet->items as $vo){
				if(in_array($vo['object_id'],$objectIdArr)){
					if($vo['status'] != $params['status']){
						$update[] = $vo['object_id'];
					}
					$exist[] = $vo['object_id'];
				}else{
					$del[] = $vo['object_id'];
				}
			}
			$add = array_diff($objectIdArr,$exist);
			if(!empty($del)){
				$updateRet = user_db_orgMemberPriorityDao::del($setId,$params['type'],$del);
			}
			if(!empty($update)){
				$data['status'] = $params['status'];
				$updateRet = user_db_orgMemberPriorityDao::update($setId,$data,$update);
			}
			if(!empty($del) || !empty($update)){
				$ret = $updateRet;
			}else{
				$ret = true;
			}
		}else{
			$add = $objectIdArr;
		}
		if(!empty($add)){
			foreach($add as $id){
				$addData['fk_member_set'] = $setId;
				$addData['object_id'] = $id;
				$addData['type'] = $params['type'];
				$addData['status'] = $params['status'];
				$addData['create_time'] = date('Y-m-d H:i:s',time());
				$addRet[] = user_db_orgMemberPriorityDao::add($addData);
			}
			return $addRet;
		}else{
			return $ret;
		}
	}
	
	public static function ListUserProfileByOids($oids){
		$exsitRet = user_db_organizationDao::ListsByOrgIdArr($oids);
		$userIdArr = array();
		if(!empty($exsitRet->items)){
			foreach($exsitRet->items as $vo){
				if($vo['fk_user_owner']){
					$userIdArr[$vo['fk_user_owner']] = $vo['fk_user_owner'];
				}
			}
		}
		if(!empty($userIdArr)){
			$tmp = array();
			$retUserData = user_db_userDao::listsByUserIdArr($userIdArr);
			if(!empty($retUserData->items)){
				foreach($retUserData->items as $retk=>$retv){
					$tmp[$retv["pk_user"]] = $retv["real_name"];
				}
			}
		}
		if(!empty($exsitRet->items)){
			foreach($exsitRet->items as $ek=>&$ev){
				$ev["org_user_real_name"] = $tmp[$ev["fk_user_owner"]];
			}
		}
		return $exsitRet;
	}
	public static function updateMemberPriorityByObjectId($objectId,$type,$setData){
		if(empty($setData) || empty($objectId)){
			return false;
		}
		$setIdArr = array();
		$setTemp = array();
		foreach($setData as $do){
			$setIdArr[] = $do['setId'];
			$setTemp[$do['setId']] = $do['status'];
		}
		$exsitRet = user_db_orgMemberPriorityDao::getMemberPriorityByObjectId($objectId,$type);
		$exist = array();
		$update = array();
		$add = array();
		if(!empty($exsitRet->items)){
			foreach($exsitRet->items as $vo){
				if(in_array($vo['fk_member_set'],$setIdArr)){
					$exist[] = $vo['fk_member_set'];
				}else{
					$del[] = $vo['fk_member_set'];
				}
			}
			$add = array_diff($setIdArr,$exist);
			if(!empty($del)){
				$delRet = user_db_orgMemberPriorityDao::delByObjectId($objectId,$type,$del);
			}
			if(!empty($del)){
				$ret = $delRet;
			}else{
				$ret = true;
			}
		}else{
			$add = $setIdArr;
		}
		if(!empty($add)){
			foreach($add as $sid){
				$addData['fk_member_set'] = $sid;
				$addData['object_id'] = $objectId;
				$addData['type'] = $type;
				$addData['status'] = $setTemp[$sid];
				$addData['create_time'] = date('Y-m-d H:i:s',time());
				$addRet[] = user_db_orgMemberPriorityDao::add($addData);
			}
			return $addRet;
		}else{
			return $ret;
		}
	}
	
	
	
	
}
