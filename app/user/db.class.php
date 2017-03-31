<?php
class user_db{

	static  $userProfileItems = array(
		"user_id"=>"pk_user",
		"t_user_student_profile.grade",
		"t_user_student_profile.student_name",
		"t_user_student_profile.region_level0",
		"t_user_student_profile.region_level1",
		"t_user_student_profile.region_level2",
		"t_user_student_profile.school_type",
		"t_user_student_profile.school_id",
		"t_user.name",
        "t_user.birthday",
        "t_user.last_login",
		"t_user.gender",
		"t_user.thumb_big",
		"t_user.thumb_small",
		"t_user.thumb_med",
		"t_user.register_ip",
		"t_user.create_time",
		"t_user_mobile.mobile",
		"t_user_mobile.supplier",
		"t_user_mobile.province",
		"t_user_mobile.city",
        "t_user_profile.real_name",
		"t_user.real_name"
	);   
	public static function InitDB($dbname="db_user",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}

	public static function addUser($data){
		$table=array("t_user");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}

	public static function addUserParterner($data){
		$table=array("t_user_parterner");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}

	public static function updateUserParterner($source,$uid,$data){
		$table=array("t_user_parterner");
		$db = self::InitDB();
		return $db->update($table, array('parterner_uid' => $uid,"source"=>$source), $data);
	}

	public static function addUserEmail($data){
		$table=array("t_user_email");
		$db = self::InitDB();
		return $db->insert($table,$data,true);
    }

	public static function addUserMobile($data){
		$data['mobile'] = utility_valid::getMobile($data['mobile']);
		$table=array("t_user_mobile");
		$db = self::InitDB();
		return $db->insert($table,$data,true);
	}

	public static function addUserProfile($data){
		$table=array("t_user_profile");
		$db = self::InitDB();
		return $db->insert($table,$data,true);
	}

	public static function updateUser($uid, $data){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$key_2 =md5( "user_db.t_user_student_profile.".$uid);
		$v = redis_api::del($key);
		$v = redis_api::del($key_2);

		$table=array("t_user");
		return $db->update($table, array('pk_user' => $uid), $data);
	}

	public static function updateUserMobile($uid, $data){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::del($key);
		$table=array("t_user_mobile");
		return $db->update($table, array('fk_user' => $uid), $data);
	}

	public static function updateUserEmail($uid, $data){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::del($key);
		$table=array("t_user_email");
		return $db->update($table, array('fk_user' => $uid), $data);
	}

	public static function updateUserProfile($uid, $data){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::del($key);
		$table=array("t_user_profile");
		return $db->update($table, array('fk_user' => $uid), $data);
	}
	public static function updateUserTypeByInfo($uid, $data){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::del($key);
		$table=array("t_user");
		return $db->update($table, array('pk_user' => $uid), $data);
	}
	public static function getUser($uid){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::get($key);
		if($v){return $v;}
		$table=array("t_user");
		$v = $db->selectOne($table,array("pk_user"=>$uid,"status"=>1), "*");
		redis_api::set($key,$v,300);
		return $v;
	}
	
	public static function getrealNameSubdomain($uid){
		$db = self::InitDB("db_user","query");
		$table=array("t_organization");
		$items="t_organization.fk_user_owner,";
		$items.="t_user_subdomain.fk_user,t_user_subdomain.subdomain,";
		$items.="t_user_profile.fk_user,t_user_profile.real_name,";
		$items.="t_user.pk_user,t_user.name,";
		$items.="t_organization_profile.subname,t_organization_profile.fk_user_owner";
		$left=new stdclass;
		$left->t_user_subdomain="t_user_subdomain.fk_user=t_organization.fk_user_owner";
		$left->t_user_profile="t_user_profile.fk_user=t_organization.fk_user_owner";
		$left->t_user="t_user.pk_user=t_organization.fk_user_owner";
		$left->t_organization_profile="t_organization_profile.fk_user_owner=t_organization.fk_user_owner";
		$v = $db->selectOne($table,array("t_organization.fk_user_owner"=>$uid),$items,'','',$left);
		return $v;
	}
	
	public static function getBasicUser($uid){
		return self::getUser($uid);
	}
	public static function getStudentUser($id){
		$db = self::InitDB("db_user","query");
		$keyHash =md5( "user_db.t_user_student_profile.v2.".$id);
		$v = redis_api::get($keyHash);
		if($v!==false){return $v;}

		$table=array("t_user");
		$left=new stdclass;
		$left->t_user_student_profile="t_user_student_profile.fk_user = t_user.pk_user";
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_profile.fk_user= t_user.pk_user";
		$v = $db->selectOne(
			$table,array("pk_user"=>$id), self::$userProfileItems, "","",$left
		);

		if(!$v)$v=0;
		$r = redis_api::set($keyHash,$v,300);
		return $v;
	}

	public function getstuShowInfo($uid){
		$db = self::InitDB("db_user","query");
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::get($key);
		if($v){return $v;}
		$table=array("t_user");
		$v = $db->selectOne($table,array("pk_user"=>$uid), "*");
		redis_api::set($key,$v,300);
		return $v;
	}	

	public function getstudentCourse($id)
    {
        $db  = self::InitDB("db_course", "query");
		$table=array("t_course");
        $condition              = array();
        $condition              = array("status <> -1");
        $condition["fk_user"] = $id;
        $v                      = $db->select($table, $condition);
        return $v;
    }	

	/**
	 * 根据一批用户ID获取用户数据
	 * 这里批量获取允许缓存10分钟 @by hetal
	 * 进行分页获取,每页500个
	 */
	public static function getStudentUsers($ids){
		if(empty($ids))return false;

		$ret=array();

		$ids_chunk = array_chunk($ids,500);
		foreach($ids_chunk as $chunk){
			$ret_chunk = self::_getStudentUsers($chunk);
			$ret+=$ret_chunk;
		}
		return $ret;
	}
	private static function _getStudentUsers($ids){
		if(empty($ids))return false;
		$db = self::InitDB("db_user","query");

		$key = md5("user_db.t_user_student_profile.v3.".implode(",",$ids));
		$ret = redis_api::get($key);
		if($ret !== false){
			return $ret;
		}else{
	
			$table=array("t_user");
			$left=new stdclass;
			$left->t_user_student_profile="t_user_student_profile.fk_user = t_user.pk_user";
			$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
			$left->t_user_profile="t_user_profile.fk_user= t_user.pk_user";
			$v = $db->select( $table, array("pk_user in(".implode(",",$ids).")"),self::$userProfileItems ,"","",$left);
			if(!empty($v->items)){
				foreach($v->items as $item){
					$id = $item['user_id'];
					$key_t =md5( "user_db.t_user_student_profile.v2.".$id);
					$ret[$key_t]=$item;
				}
				redis_api::set($key,$ret);
				redis_api::expire($key,3600);
				return $ret;
			}
		}
		return array();
	}
	public static function getUserProfile($id){
		$table=array("t_user_profile");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("fk_user"=>$id), "*");
	}
    public static function getorgUserTeacher($id){
		$table=array("t_organization_user");
        $db = self::InitDB("db_user","query");
		return $db->select($table,array("fk_user"=>$id,"status"=>1), "*");
	}
    public static function updateorgUserTeacher($uid,$id1,$id2, $status){
		$db = self::InitDB();
		$key =md5( "user_db.t_user.".$uid);
		$v = redis_api::del($key);
		$table=array("t_organization_user");
		return $db->update($table, array('fk_user' => $uid,"status"=>$status,"fk_org"=>$id2), array("fk_org"=>$id1,"role"=>1));
	}
	public static function getUserIDByEmail($email){
		$table=array("t_user_email");
        $db = self::InitDB("db_user","query");
		$ret = $db->selectOne($table,array("email"=>strtolower($email)));
		if(!empty($ret['fk_user'])){
			return $ret['fk_user'];
		}
		return 0;
	}
	public static function getUserIDByMobile($mobile){
		$mobile = utility_valid::getMobile($mobile);
		$table=array("t_user_mobile");
        $db = self::InitDB("db_user","query");
		$ret = $db->selectOne($table,array("mobile"=>$mobile));
		if(!empty($ret['fk_user'])){
			return $ret['fk_user'];
		}
		return 0;
	}
	/*
	 * 模糊查询手机号
	 */
	public static function geteUserIdByLikeMobile($str)
	{
		$table = array("t_user_mobile");
        $db    = self::InitDB("db_user","query");
		$condition = "mobile LIKE '%{$str}%'";
		return $db->select($table, $condition);
	}
	/*
	 * 模糊查询用户名
	 */
	public static function geteUserIdByLikeName($str)
	{
		$table = array("t_user");
        $db    = self::InitDB("db_user","query");
		$condition = "real_name LIKE '%{$str}%'";
		return $db->select($table, $condition);
	}
	
    //从主库查询 注册专用 create by zhangtaifeng 2015/09/17
	public static function getUserIDByMobileFromMaster($mobile){
		$mobile = utility_valid::getMobile($mobile);
		$table=array("t_user_mobile");
        $db = self::InitDB("db_user");
		$ret = $db->selectOne($table,array("mobile"=>$mobile));
		if(!empty($ret['fk_user'])){
			return $ret['fk_user'];
		}
		return 0;
	}

	//这个方法有问题，mobile是varchar，不能用 IN ，comments by hetao 2015/8/24
 	public static function getUserByMobileArr($mobileArr){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_user_mobile');
        $mobileStr = implode(',', $mobileArr);
        $condition = "mobile IN ($mobileStr)";

        return $db->select($table, $condition);
    }

	public static function getUserIDByPaterner($source,$uid){
		$ret = self::getUserByPaterner($source,$uid);
		if(!empty($ret['fk_user'])){
			return $ret['fk_user'];
		}
		return 0;
	}
	public static function getUserParterner($source,$uid){
		$table=array("t_user_parterner");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("source"=>$source,"parterner_uid"=>$uid));
	}
	public static function bindParterner($parterner_id,$uid){
		$table=array("t_user_parterner");
        $db = self::InitDB("db_user");
		return $db->update($table,array("pk_parterner"=>$parterner_id),array("fk_user"=>$uid));
	}
	public static function getUserParternerById($parterner_id){
		$table=array("t_user_parterner");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("pk_parterner"=>$parterner_id));
	}
	public static function getUserParternerByUId($source,$uid){
		$table=array("t_user_parterner");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("fk_user"=>$uid,"source"=>$source));
	}

	public static function getUserEmailByID($id){
		$table=array("t_user_email");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("fk_user"=>$id), "*");
	}

	public static function getUserMobileByID($id){
		$table=array("t_user_mobile");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("fk_user"=>$id), "*");
	}

	public static function getUserProfileByID($id){
		$table=array("t_user_profile");
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("fk_user"=>$id), "*");
	}

	public static function listUser($page = 1, $length = 20){
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		$db->setPage($page);
		$db->setLimit($length);
		$item = 't_user.*,t_user_email.email';
		$item .= ',t_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
        $item.=',t_user_student_profile.student_name,';
		$item.='t_organization.name org_name';
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		$left->t_user_student_profile="t_user_student_profile.fk_user = t_user.pk_user";
		$left->t_organization ="t_user.pk_user=t_organization.fk_user_owner";
		return $db->select($table, '', $item, '', 'pk_user desc',$left);
	}

	public static function teacherListUser($page = 1, $length = 20){
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		$db->setPage($page);
		$db->setLimit($length);
		$item = 't_user.pk_user,t_user.name,t_user.real_name,t_user.password,t_user.birthday,t_user.gender,t_user.status,t_user.type,t_user.verify_status,t_user.source,t_user.thumb_big,t_user.thumb_med,t_user.small,t_user.register_ip,t_user.create_time,t_user.last_updated,t_user.last_login,,t_user_email.email';
		$item .= ',t_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
        $item.=',t_user_profile.real_name,';
		$item.='t_organization.name org_name';
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$left->t_organization ="t_user.pk_user=t_organization.fk_user_owner";
		$condition = array();
		$condition = array("type & 0x02 > 0");
		return $db->select($table, $condition, $item, '', 'pk_user desc',$left);
	}
	
	public static function getsearchShow($params){
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		if(!empty($params['page']) && !empty($params['num'])){
			$db->setPage($params['page']);
			$db->setLimit($params['num']);
		}
		$item = 't_user.*,t_user_email.email';
		$item .= ',t_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
        //$item.=',t_user_profile.real_name';
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		//$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$condition = '1=1 ';
		if(!empty($params['uidArr'])){
			$uidStr = implode(',',$params['uidArr']);
			$condition .= " and t_user.`pk_user` in ($uidStr)";
		}
		if(!empty($params['starttime']) && empty($params['endtime'])){
			$params['starttime'] = $params['starttime']." 00:00:00";
			$condition .= " and `create_time` >'".$params['starttime']."'";
		}
		if(!empty($params['starttime']) && !empty($params['endtime'])){
			$params['starttime'] = $params['starttime']." 00:00:00";
			$params['endtime'] = $params['endtime']." 23:59:59";
			$condition .= " and `create_time` >'".$params['starttime'] ."' and `create_time`< '".$params['endtime']."'";
		}
		if(!empty($params['area'])){
			$condition.= " and (`city`='".$params['area']."' or `province`='".$params['area']."')";
		}
        if(!empty($params['status'])){
			$condition.= " and `t_user`.`status`='".$params['status']."'";
		}
		if(!empty($params['mobile'])){
			if(preg_match("/^1[34578]\d{9}$/",$params['mobile'])){
        		$condition.= " and t_user.`mobile`='".$params['mobile']."'";
 			}else{
        		$condition.= " and (t_user.`name`='".$params['mobile']."' or t_user.`real_name`='".$params['mobile']."')";
 			}
		} 
		return $db->select($table,$condition, $item, '', 'pk_user desc',$left);
	}	
	
	public static function getsearchTeacherShow($params){
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		$db->setPage($params['page']);
		$db->setLimit($params['num']);
		$item = 't_user.*,t_user_email.email';
		$item .= ',t_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
        $item.=',t_user_profile.real_name';
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$condition = '1=1 ';
		if(!empty($params['fk_org'])){
			$ids=array();
				$res =self::getteacherInfo($params['fk_org']);
				if(!empty($res->items)){
					foreach($res->items as $k=>$v){
							$ids[]=$v['fk_user'];
					}
				$str = implode(",",$ids);
				}
				$condition.= " and pk_user IN(".$str.")";
						
		}
		if(!empty($params['mobile'])){
			if(preg_match("/^1[34578]\d{9}$/",$params['mobile'])){
        		$condition.= " and `t_user`.`mobile` ='".$params['mobile']."'";
 			}else{
        		$condition.=" and (t_user.`name` LIKE '%".$params['mobile']."%' or t_user.`real_name` LIKE '%".$params['mobile']."%') and t_user.type & 0x02>0";
 			}
		}
		if(!empty($params['starttime']) && empty($params['endtime'])){
			$params['starttime'] = $params['starttime']." 00:00:00";
			$condition.= " and `create_time` >'".$params['starttime']."' and type & 0x02 > 0";
		}
		if(!empty($params['starttime']) && !empty($params['endtime'])){
			$params['starttime'] = $params['starttime']." 00:00:00";
			$params['endtime'] = $params['endtime']." 23:59:59";
			$condition.= " and `create_time` >'".$params['starttime'] ."' and `create_time`< '".$params['endtime']."'";
		}
		if(!empty($params['status'])){
			$condition.= " and `t_user`.`status`='".$params['status']."'";
		}
		return $db->select($table,$condition, $item, '', 'pk_user desc',$left);
	}	
	
	

	public static function listTeacher($page = 1, $length = 20){
		//$table=array("t_user","t_user_teacher_profile");
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		$db->setPage($page);
		$db->setLimit($length);
		$item = 't_user.*,t_user_email.email,';
		$item .= 't_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city,';
		$item .= 't_user_teacher_profile.title,';
		$item .= 't_user_teacher_profile.college,';
		$item .= 't_user_teacher_profile.years,';
		$item .= 't_user_teacher_profile.diploma';
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		$left->t_user_teacher_profile="t_user_teacher_profile.fk_user = t_user.pk_user";
		$orderby = array("pk_user"=>"desc");
		return $db->select($table, array('type & 0x02 > 0',"t_user_teacher_profile.fk_user=t_user.pk_user"), $item, '',$orderby,$left);
	}

	public static function listOrganization($page = 1, $length = 20){
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		$db->setPage($page);
		$db->setLimit($length);
		$item = 't_user.*,t_user_email.email,';
		$item .= 't_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
		//$item .= 't_user_parterner.source,t_user_parterner.parterner_uid,t_user_parterner.thumb_url,';
		//$item .= 't_user_parterner.auth_code,t_user_student_profile.grade';
		/*
		$item = 't_user_profile.real_name,t_user_profile.address,';
		$item = 't_user_profile.birth_place,t_user_profile.desc,t_user_profile.zip_code,';
		*/
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		$left->t_user_parterner="t_user_parterner.fk_user = t_user.pk_user";
		//$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$left->t_user_student_profile="t_user_student_profile.fk_user = t_user.pk_user";
		return $db->select($table, 'type & 0x04 > 0', $item, '', 'pk_user desc',$left);
	}

	public static function listStudent($page = 1, $length = 20){
		$table=array("t_user");
        $db = self::InitDB("db_user","query");
		$db->setPage($page);
		$db->setLimit($length);
		$item = 't_user.*,t_user_email.email,';
		$item .= 't_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
		//$item .= 't_user_parterner.source,t_user_parterner.parterner_uid,t_user_parterner.thumb_url,';
		//$item .= 't_user_parterner.auth_code,t_user_student_profile.grade';
		/*
		$item = 't_user_profile.real_name,t_user_profile.address,';
		$item = 't_user_profile.birth_place,t_user_profile.desc,t_user_profile.zip_code,';
		*/
		$left=new stdclass;
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$left->t_user_email="t_user_email.fk_user = t_user.pk_user";
		//$left->t_user_parterner="t_user_parterner.fk_user = t_user.pk_user";
		//$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$left->t_user_student_profile="t_user_student_profile.fk_user = t_user.pk_user";
		return $db->select($table, 'type & 0x01 > 0', $item, '', 'pk_user desc',$left);
	}

	public static function listUserByGrade($grade, $page = 1, $length = 20){
		if (empty($grade) || !is_int($grade)) {
			$condition = array();
		} else {
			$condition = array('grade'=>$grade);
		}
		$table=array("t_user_student_profile");
        $db = self::InitDB("db_user","query");
		$db->setPage($page);
		$db->setLimit($length);
		$item = 't_user.*,t_user_email.email,';
		$item .= 't_user_mobile.mobile,t_user_mobile.supplier,t_user_mobile.province,t_user_mobile.city';
		//$item .= 't_user_parterner.source,t_user_parterner.parterner_uid,t_user_parterner.thumb_url,';
		//$item .= 't_user_parterner.auth_code,t_user_student_profile.grade, t_user_student_profile.fk_user';
		$left=new stdclass;
		$left->t_user="t_user.pk_user = t_user_student_profile.fk_user";
		$left->t_user_mobile="t_user_mobile.fk_user = t_user_student_profile.fk_user";
		$left->t_user_email="t_user_email.fk_user = t_user_student_profile.fk_user";
		//$left->t_user_parterner="t_user_parterner.fk_user = t_user_student_profile.fk_user";
		return $db->select($table, $condition, $item, '', 'fk_user desc',$left);
	}
	//for sphinx indexing course,plan
	public static function listUsersByUserIds( $idsStr ){
        $db = self::InitDB("db_user","query_sphinx");
		$condition = array("pk_user in ( $idsStr )");
		$item = array('pk_user','name','thumb_big','thumb_med','thumb_small','real_name','mobile');
		$table = array("t_user");
		return $db->select($table, $condition, $item);
	}
	//for sphinx indexing organization
	public static function listDomainsByOwnerIds( $idsStr ){
        $db = self::InitDB("db_user","query_sphinx");
		$condition = array("fk_user in ( $idsStr ) AND status=1");
		$item = array('fk_user','subdomain');
		$table = array("t_user_subdomain");
		return $db->select($table, $condition, $item);
	}
	//for sphinx indexing course,plan//subdomain,org_subname
	public static function listDomainsByUserIds( $idsStr ){
        $db = self::InitDB("db_user","query_sphinx");
		$condition = array("fk_user in ( $idsStr )",'t_user_subdomain.status=1');
		$item = array('t_user_subdomain.fk_user','subdomain','t_organization_profile.subname as org_subname','fk_org','t_organization.status as org_status');
		$left = new stdclass;
		$left->t_organization_profile = "t_organization_profile.fk_user_owner = t_user_subdomain.fk_user";
		$left->t_organization = "t_organization.fk_user_owner = t_user_subdomain.fk_user";
		$table = array("t_user_subdomain");
		return $db->select($table, $condition, $item, '', '', $left);
	}
	
	public static function listProfilesByUserIds( $idsStr ){
        $db = self::InitDB("db_user","query");
		$condition = array("fk_user in ( $idsStr )");
		$item = array('fk_user','real_name','address');
		$table = array("t_user_profile");
		return $db->select($table, $condition, $item, '', '', '');
	}
	
	public static function setStudentProfile($data){
		$key =md5( "user_db.t_user.".$data['fk_user']);
		$keyHash =md5( "user_db.t_user_student_profile.v2.".$data["fk_user"]);
		redis_api::del($key);
		redis_api::del($keyHash);
		$table=array("t_user_student_profile");
		$db = self::InitDB();
		return $db->insert($table,$data,true);
	}
	
	public static function setStudentProfile2($uid,$data){
		$key =md5( "user_db.t_user.".$uid);
		$keyHash =md5( "user_db.t_user_student_profile.v2.".$uid);
		redis_api::del($key);
		redis_api::del($keyHash);
		$table=array("t_user_student_profile");
		$db = self::InitDB();
		return $db->update($table,array('fk_user'=>$uid),$data);
	}
	
	public static function getStudentProfile($id){
		return self::getStudentUser($id);
/*
		//$key =md5( "user_db.t_user.".$id);
		$keyHash =md5( "user_db.t_user_student_profile.".$id);
		$v = redis_api::get($keyHash);
		if($v!==false){return $v;}
		$table=array("t_user_student_profile");
		$item=array(
			"user_id"=>"fk_user",
			"grade",
			"student_name",
			"region_level0",
			"region_level1",
			"region_level2",
			"school_type",
			"school_id",
		);
        $db = self::InitDB("db_user","query");
		return $db->selectOne($table,array("fk_user"=>$id),$item);
*/
	}
	public static function getTeacherProfile($uid,$desc=true){
		$table=array("t_user_teacher_profile");
        $db = self::InitDB("db_user","query");
		$item=new stdclass;
		$item->title= "title";
		$item->college= "college";
		$item->years= "years";
		$item->diploma= "diploma";
		$item->desc= "desc";
		$item->major= "major";
		$item->scopes= "scopes";
		$item->good_subject= "good_subject";
		$item->brief_desc= "brief_desc";
		return $db->selectOne($table,array("fk_user"=>$uid),$item);
	}
	public static function getTeacherProfileByIds($idsArr){
        $db = self::InitDB("db_user","query");
		$table=array("t_user_teacher_profile");
        $uidStr = implode(',', $idsArr);
        $condition = "t_user_teacher_profile.fk_user IN ($uidStr)";
        $left=new stdClass;
		$left->t_user="t_user_teacher_profile.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_teacher_profile.fk_user = t_user_profile.fk_user";
        $item=array(
               't_user.name', 
               't_user.thumb_small',
			   't_user.thumb_med',
               't_user_profile.real_name',
               't_user_teacher_profile.*', 
            );
 	    return $db->select($table,$condition,$item,'','',$left);
	}
	public static function getTeacherInfoByIds($idsArr){
        $db = self::InitDB("db_user","query");
		$table=array("t_user");
        $uidStr = implode(',', $idsArr);
        $condition = "t_user.pk_user IN ($uidStr)";
        $left=new stdClass;
		$left->t_user_teacher_profile="t_user.pk_user = t_user_teacher_profile.fk_user";
		$left->t_user_profile="t_user.pk_user = t_user_profile.fk_user";
        $item=array(
			    't_user.pk_user', 
               't_user.name', 
               't_user.thumb_small',
			   't_user.thumb_med',
               't_user_profile.real_name',
               't_user_teacher_profile.*', 
            );
 	    return $db->select($table,$condition,$item,'','',$left);
	}
	public static function setTeacherProfile($uid,$data){
		$table=array("t_user_teacher_profile");
		$db = self::InitDB();
		return $db->insert($table,$data,true);
	}
	
	public static function updateTeacherProfile($uid,$data){
		$table=array("t_user_teacher_profile");
		$db = self::InitDB();
		return $db->update($table,array('fk_user'=>$uid),$data);
	}
	
	
	public static function updateToken($token,$Token){
		$db = self::InitDB();
		//{{{
		$key = "t_token.$token";
		return redis_api::set($key,$Token,3600*24*30);//存在30天
		//}}}
		//TODO 未来token不需要存在数据库里
		//$table=array("t_token");
		//return $db->update($table,array("token"=>$token),$Token);
	}
	public static function addToken($data){
		$db = self::InitDB();
		//{{{
		$key =md5( "t_token.".$data['token']);
		return redis_api::set($key,$data,3600*24*30);
		//}}}
	}
	public static function delToken($token){
		$db = self::InitDB();
		//{{{
		$key =md5( "t_token.".$token);
		return $r = redis_api::delete($key);
		//}}}
	}
	public static function addmgrSubmain($data){
		$table=array("t_user_subdomain");
		$db = self::InitDB();

		$key ="t_user_subdomain";
		redis_api::hDelAll($key);

		return $db->insert($table,$data);
	}
	public static function getToken($token){
		$db = self::InitDB();
		//{{{
		$key = "t_token.$token";
		$key =md5( "t_token.".$token);
		return redis_api::get($key);
		//}}}

		//$table=array("t_token");
		//return $db->selectone($table,array("token"=>$token));
	}
	/* 列取所有机构
	 * 根据id列取机构
	 */
	public static function listorg($uid = null,$page = null,$length =null){
        $db = self::InitDB("db_user","query");


		$key = "t_organization";
		$hash=md5( "db_user.t_organization.$uid.$page.$length");
		$v = redis_api::hGet($key,$hash);
		if($v)return $v;

		$table=array("t_organization");
		$condition  = array();
		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
	//	$condition = array("status"=>"1");//1:通过审核 0: 未通过审核
		if($uid == "0"){$oid = null;}
		//	if($oid)$condition["pk_org"]=$uid;
		if($uid)$condition["fk_user_owner"]=$uid;
		$item = array(
			"pk_org",
			"fk_user_owner",
			"thumb_big",
			"thumb_med",
			"thumb_small",
			"desc",
			"status",
			"name",
			"create_time",
			"last_updated"
		);
		$orderby = array("pk_org"=>"desc");
		$v = $db->select($table,$condition,$item,"",$orderby,"");
		redis_api::hSet($key,$hash,$v);
		redis_api::expire($key,86400);
		return $v;
	}

	/* 根据老师所在id查询机构名称
	 */
	public static function teacherOrgIn($uid){
        $db = self::InitDB("db_user","query");

		$key =md5( "db_user.t_organization.$uid");
		$table=array("t_organization");
		$condition = array();
		if($uid == "0"){$oid = null;}
		if($uid)$condition = array('t_organization.status >= 1');//1:通过审核 0: 未通过审核
		array_push($condition,"pk_org IN (".$uid.")");
		$item = array(
			"pk_org",
			"fk_user_owner",
			"thumb_big",
			"thumb_med",
			"thumb_small",
			"desc",
			"name",
			"create_time",
			"subdomain"
		);
		$left = new  stdclass;
		$left->t_user_subdomain="t_user_subdomain.fk_user=t_organization.fk_user_owner";
		$orderby = array("pk_org"=>"desc");
		$v = $db->select($table,$condition,$item,"",$orderby,$left);
		return $v;
	}


	public static function listorginfo($page = null,$params,$length =null){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization");

		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
		$condition = '';
		$item = array("t_organization.pk_org","t_organization.fk_user_owner","t_organization.name","t_organization_profile.areacode","t_organization_profile.hotline","t_organization.thumb_big","t_organization.thumb_med","thumb_small","status","create_time","t_organization.last_updated","t_organization_verify.idcard_pic","t_organization_verify.qualify_pic","t_organization_verify.verify_status");
		$left = new  stdclass;
		$left->t_organization_verify='t_organization_verify.fk_org=t_organization.pk_org';
		$left->t_organization_profile='t_organization_profile.fk_org=t_organization.pk_org';
		$orderby = array("t_organization.last_updated"=>"desc");
		if(is_numeric($params->status)){
			$condition = 'status='.$params->status;
		}elseif(is_string($params->status)){
			$condition = 'status IN (-1,0,1,2)';
		}else{
			$condition = '';
		}
		if(!empty($params->subname)&&preg_match('/[\d]$/',$params->subname)){
			$condition.="and t_organization_profile.fk_org='".$params->subname."'";
		}else{
            $condition.= " and t_organization_profile.subname LIKE '%".$params->subname."%'";
        }
		$v = $db->select($table,$condition,$item,"",$orderby,$left);
		return $v;
	}
	public static function selectCheckListTmp($uid = null,$page = null,$length =null){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_profile_check");
		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
		if($uid == "0"){$oid = null;}
		$orderby = array("last_updated"=>"desc");
		$v = $db->select($table,"","","",$orderby,"");
		return $v;
	}
	public static function getResultOrg($uid = null,$page = null,$length =null,$status){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization");
		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
		$condition = array("status"=>$status);//1:通过审核 0: 未通过审核
		if($uid == "0"){$oid = null;}
		if($uid)$condition["fk_user_owner"]=$uid;
		$item = array("pk_org","fk_user_owner",
					  "thumb_big","thumb_med","thumb_small",
					  "desc","status","name",
					  "create_time","last_updated");
		$orderby = array("pk_org"=>"desc");
		$v = $db->select($table,$condition,$item,"",$orderby,"");
		return $v;
	}
	/*根据机构id查出用户
	 *
	 */
	public static function listOrgUser($oid=0 ,$all=1,$star=-1,$page = null,$length =null){
		$table=array("t_organization_user","t_user","t_user_mobile");
        $db = self::InitDB("db_user","query");
		if($page){
            $db->setPage($page);
        }
		if($length){
            $db->setLimit($length);
        }
		$condition = array();
		$condition = array("t_organization_user.status <> -1");
		if($oid){
            $condition["fk_org"]=$oid;
        }
		$condition[]="pk_user=t_organization_user.fk_user";
		$condition[]="pk_user=t_user_mobile.fk_user";
        //是否全显示
		if(!$all){
            $condition[]="t_organization_user.visiable > 0 ";
        }
		if($star==1){
            $condition[]="t_organization_user.is_star>0 ";
        }
        if($star==0){
            $condition[]="t_organization_user.is_star=0 ";
        }
		$left=new stdclass;
		$left->t_user_teacher_profile="t_user_teacher_profile.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$item = array(
            "org_id"=>"fk_org",
            "t_user_mobile.mobile",
            "t_user_profile.real_name",
			"user_id"=>"t_organization_user.fk_user",
			"name","thumb_med","thumb_small","thumb_big","gender","last_login",
			"t_organization_user.status",
			"t_organization_user.role",
			"t_organization_user.user_role",
			"t_user_teacher_profile.title",
			"t_user_teacher_profile.college",
			"t_user_teacher_profile.years",
			"t_user_teacher_profile.diploma",
			"t_user_teacher_profile.desc",
			"t_user_teacher_profile.good_subject",
			"t_user_teacher_profile.major",
			"t_organization_user.announce",
			"t_organization_user.thumb_nav",
			"t_organization_user.is_star",
            "t_organization_user.visiable",
			"t_organization_user.sort"
        );
		$orderby = array("t_organization_user.is_star"=>"asc","t_organization_user.last_updated"=>"asc");
		return $db->select($table,$condition,$item,"",$orderby,$left);
	}


    public function getOrgUserListByOid($condition, $item, $orderBy='', $page=1, $length=100)
    {
        $table = array("t_organization_user");
        $db = self::InitDB('db_user', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }


    public function getUserList($condition, $item='', $orderBy='', $page=1, $length=-1)
    {
        $table = array("t_user");
        $db = self::InitDB('db_user', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }

    public function getTeacherProfileList($condition, $item='', $orderBy='', $page=1, $length=-1)
    {
        $table = array("t_user_teacher_profile");
        $db = self::InitDB('db_user', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }
	public static function dataOrgTeacherCount($oid){
		$table=array("t_organization_user","t_user","t_user_mobile");
        $db = self::InitDB("db_user","query");
		$all=1;
		$star=-1;
		$db->setCount(true);
		$condition = array();
		$condition = array("t_organization_user.status <> -1");
		if($oid){
            $condition["fk_org"]=$oid;
        }
		$condition[]="pk_user=t_organization_user.fk_user";
		$condition[]="pk_user=t_user_mobile.fk_user";
		if(!$all){
            $condition[]="t_organization_user.visiable > 0 ";
        }
		if($star==1){
            $condition[]="t_organization_user.is_star>0 ";
        }
        if($star==0){
            $condition[]="t_organization_user.is_star=0 ";
        }
		$left=new stdclass;
		$left->t_user_teacher_profile="t_user_teacher_profile.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$item = array(
            "org_id"=>"fk_org",
            "t_user_mobile.mobile",
            "t_user_profile.real_name",
			"user_id"=>"t_organization_user.fk_user",
			"name","thumb_med","thumb_small","thumb_big","gender","last_login",
			"t_organization_user.status",
			"t_organization_user.role",
			"t_organization_user.user_role",
			"t_user_teacher_profile.title",
			"t_user_teacher_profile.college",
			"t_user_teacher_profile.years",
			"t_user_teacher_profile.diploma",
			"t_user_teacher_profile.desc",
			"t_user_teacher_profile.good_subject",
			"t_user_teacher_profile.major",
			"t_organization_user.announce",
			"t_organization_user.thumb_nav",
			"t_organization_user.is_star",
            "t_organization_user.visiable",
			"t_organization_user.sort"
        );
		$orderby = array("t_organization_user.is_star"=>"asc");
		return $db->select($table,$condition,$item,"",$orderby,$left);
	}

    public function getUserMobileList($condition, $item='', $orderBy='', $page=1, $length=100)
    {
        $table = array("t_user_mobile");
        $db = self::InitDB('db_user', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }
	public static function searchOrgTeacherNameOrMobileInfo($data){
		$table=array("t_organization_user","t_user","t_user_mobile");
        $db = self::InitDB("db_user","query");
		$all=1;
		$star=-1;
		$db->setCount(true);
		$condition = array();
		$condition = array("t_organization_user.status <> -1");
		if(!empty($data['fk_org'])){
            $condition["fk_org"]=$data['fk_org'];
        }
		$condition[]="pk_user=t_organization_user.fk_user";
		$condition[]="pk_user=t_user_mobile.fk_user";
		if(!$all){
            $condition[]="t_organization_user.visiable > 0 ";
        }
		if($star==1){
            $condition[]="t_organization_user.is_star>0 ";
        }
        if($star==0){
            $condition[]="t_organization_user.is_star=0 ";
        }
		if(!empty($data['keyword'])){
			$condition[]="((t_user.mobile like '%".$data['keyword']."%') OR (t_user.real_name like '%".$data['keyword']."%'))";
		}
		$left=new stdclass;
		$left->t_user_teacher_profile="t_user_teacher_profile.fk_user = t_user.pk_user";
		$left->t_user_profile="t_user_profile.fk_user = t_user.pk_user";
		$item = array(
            "org_id"=>"fk_org",
            "t_user_mobile.mobile",
            "t_user_profile.real_name",
			"user_id"=>"t_organization_user.fk_user",
			"name","thumb_med","thumb_small","thumb_big","gender","last_login",
			"t_organization_user.status",
			"t_organization_user.role",
			"t_organization_user.user_role",
			"t_user_teacher_profile.title",
			"t_user_teacher_profile.college",
			"t_user_teacher_profile.years",
			"t_user_teacher_profile.diploma",
			"t_user_teacher_profile.desc",
			"t_user_teacher_profile.good_subject",
			"t_user_teacher_profile.major",
			"t_organization_user.announce",
			"t_organization_user.thumb_nav",
			"t_organization_user.is_star",
            "t_organization_user.visiable",
			"t_organization_user.sort"
        );
		$orderby = array("t_organization_user.is_star"=>"asc");
		return $db->select($table,$condition,$item,"",$orderby,$left);
	}

    public function getStudentProfileList($condition, $item='', $orderBy='', $page=1, $length=100)
    {
        $table = array("t_user_student_profile");
        $db = self::InitDB('db_user', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }

    public function getUserProfileList($condition, $item = '', $orderBy = '', $page = 1, $length = 100)
    {
        $table = array("t_user_profile");
        $db    = self::InitDB('db_user', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }

	/*根据user_id查出用户
	 *
	 */
	public static function listOrgUserByUid($uid=0 ,$page = null,$length =null){
		$table=array("t_organization_user","t_user","t_user_mobile");
        $db = self::InitDB("db_user","query");
		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
		$condition = array();
		$condition = array("t_organization_user.status <> -1");
		if($uid)$condition["t_organization_user.fk_user"]=$uid;
		$condition[]="pk_user=t_organization_user.fk_user";
		$condition[]="pk_user=t_user_mobile.fk_user";
		$left=new stdclass;
		$left->t_user_teacher_profile="t_user_teacher_profile.fk_user = t_user.pk_user";
		$item = array("org_id"=>"fk_org","t_user_mobile.mobile",
			"user_id"=>"t_organization_user.fk_user",
			"name","thumb_med","thumb_small","thumb_big",
			"t_organization_user.status",
			"t_user_teacher_profile.title",
			"t_user_teacher_profile.college",
			"t_user_teacher_profile.years",
			"t_user_teacher_profile.diploma",
			"t_user_teacher_profile.desc",
			"t_user_teacher_profile.major",
			"announce",
			"thumb_nav",
			"is_star",
			"sort");
		$orderby = array("t_organization_user.sort"=>"asc");
		return $db->select($table,$condition,$item,"",$orderby,$left);
	}
	/*根据机构id 科目查出用户
	 *
	 */
	/*
	 *根据用户ID 查出所属机构
	 */
	public static function getOrgUser($uid ,$page = null,$length =null){
		$table=array("t_organization_user");
        $db = self::InitDB("db_user","query");
		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
		$condition["t_organization_user.fk_user"]=$uid;
		$item = array(
			"org_id"=>"fk_org",
			"org_user"=>"t_organization_user.fk_user",
			"org_status"=>"t_organization_user.status",
			"org_name"=>"t_organization.name",
			//"org_thumb_big"=>"t_organization.thumb_big",
			//"org_thumb_med"=>"t_organization.thumb_med",
			//"org_thumb_small"=>"t_organization.thumb_small",
			"user_name"=>"t_user.name",
			"thumb_big"=>"t_user.thumb_big",
			"thumb_med"=>"t_user.thumb_med",
			"thumb_small"=>"t_user.thumb_small",
		);
		$orderby = array("t_organization_user.last_updated"=>"desc");
		$left=new stdclass;
		$left->t_user="t_organization_user.fk_user = t_user.pk_user";
		$left->t_organization="t_organization.fk_user_owner= t_user.pk_user";
		$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		return $db->selectone($table,$condition,$item,"",$orderby,$left);
	}

	public function getOrgCount($city)
    {
        $db = self::InitDB('db_user','query');
        $table = array('t_organization_profile');
        if($city==0)
        {
            $condition = '';
        }else{
            $condition = array("province=$city");
        }
        $item = array('count(*) as num');
 	    return $db->select($table,$condition,$item);

    }

    public function getOrgByCityId($city, $page = 1, $length = -1)
    {
        $db = self::InitDB('db_user','query');
        $table = array('t_organization_profile');
        if($city==0)
        {
            $condition = '';
        }else{
            $condition = array("province=$city");
        }
		$db->setPage($page);
        $db->setLimit($length);
        $item = array('fk_user_owner'=>'fk_user_owner','subname'=>'subname','scopes'=>'scopes');
        return $db->select($table,$condition,$item);
    }
		
	public static function getOrgUserinfo($oid=0,$uid=0,$page = null,$length =null){
		$table=array("t_organization_user","t_user","t_user_mobile");
        $db = self::InitDB("db_user","query");
		if($page){$db->setPage($page);}
		if($length){$db->setLimit($length);}
		$condition = array();
		$condition = array("t_organization_user.status <> -1");
		if($oid)$condition["fk_org"]=$oid;
		if($uid)$condition["t_organization_user.fk_user"]=$uid;
		$condition[]="pk_user=t_organization_user.fk_user";
		$condition[]="pk_user=t_user_mobile.fk_user";
		$left=new stdclass;
		$left->t_user_teacher_profile="t_user_teacher_profile.fk_user = t_user.pk_user";
		//$left->t_user_mobile="t_user_mobile.fk_user = t_user.pk_user";
		$item = array("org_id"=>"fk_org","t_user_mobile.mobile",
			"user_id"=>"t_organization_user.fk_user",
			"name","thumb_med","thumb_small","thumb_big",
			"t_organization_user.status",
			"t_user_teacher_profile.title",
			"t_user_teacher_profile.college",
			"t_user_teacher_profile.years",
			"t_user_teacher_profile.diploma",
			"t_user_teacher_profile.desc",
			"announce",
			"thumb_nav",
			"sort");
		$orderby = array("t_organization_user.sort"=>"desc");
		return $db->selectone($table,$condition,$item,"",$orderby,$left);
	}
	public static function setOrgUser($oid ,$user_id,$data, $status=1){
		$table=array("t_organization_user");
		$db = self::InitDB();
		$item=array(
                "fk_org"=>$oid,
                "fk_user"=>$user_id,
                "status"=>$status,
                "create_time"=>date("Y-m-d H:i:s"),
                "last_updated"=>date("Y-m-d H:i:s")
            );
        if(isset($data['sort'])){
            $item['sort']=$data['sort'];
        }
        if(isset($data['is_star'])){
            $item['is_star']=$data['is_star'];
        }
        if(isset($data['user_role'])){
            $item['user_role']=$data['user_role'];
        }
        if(isset($data['role'])){
            $item['role']=$data['role'];
        }
		return $db->insert($table,$item,true);
	}
	public static function setOrgUserData($oid ,$user_id,$data){
		$table=array("t_organization_user");
		$db = self::InitDB();
		$item=array("fk_org"=>$oid,"fk_user"=>$user_id);
		return $db->update($table,$item,$data);
	}
	public static function setOtherOrgUser($uparray,$where){
		$table=array("t_organization_user");
		$db = self::InitDB();
		return $db->update($table,$where,$uparray);
	}
	public static function delOrgUser($oid ,$teacherId,$data=array()){
		$table=array("t_organization_user");
		$db = self::InitDB();
		$condition = "";
		$condition = "fk_org=$oid  AND fk_user IN(".$teacherId.")";
		$uparray = array();
		if(!empty($data)){
			$uparray = $data;
		}else{
			$uparray = array("status"=>"-1");
		}
		return $db->update($table,$condition,$uparray);
	}
	public static function updateMgrUser($uid ,$data){
		$table=array("t_user");
		$db = self::InitDB();
		$item=array("pk_user"=>$uid);
		return $db->update($table,$item,$data);
	}
	public static function addorg($org){
		$table=array("t_organization");
		$db = self::InitDB();
		return $db->insert($table,$org);
	}
	public static function addOrganizationUser($org){
		$table=array("t_organization_user");
		$db = self::InitDB();
		return $db->insert($table,$org);
	}
	public static function getorg($oid){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,array("pk_org"=>$oid));
	}
	public static function getmgrOrgUser($uid){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,array("fk_user_owner"=>$uid));
	}
	public static function getmgrSubmain($uid){
		$table=array("t_user_subdomain");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,array("fk_user"=>$uid,"status"=>1));
	}
	public static function getmgrSubmainName($fk_user){
		$table=array("t_user_subdomain");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,array("fk_user"=>$fk_user));
	}
	public static function getSubdomainByNameIsExist($data){
		$table=array("t_user_subdomain");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,$data);
	}
	public static function updateSubdomainStatus($data,$where){
		$table=array("t_user_subdomain");
		$db = self::InitDB();

		$key ="t_user_subdomain";
		redis_api::hDelAll($key);

		return $db->update($table,$where,$data);
	}
	public static function getOrgVerify($uid){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");

		$item = array(
            "t_organization.pk_org","t_organization.fk_user_owner","t_organization.name","t_organization.thumb_big","t_organization.thumb_med","t_organization.thumb_small",
            "t_organization.desc","t_organization.status","t_organization.create_time","t_organization.last_updated",
            "t_organization_profile.subname","t_organization_profile.scopes","t_organization_profile.company","t_organization_profile.province",
            "t_organization_profile.city","t_organization_profile.county","t_organization_profile.address","t_organization_profile.areacode",
            "t_organization_profile.hotline",
            "t_organization_verify.subdomain","t_organization_verify.idcard_pic","t_organization_verify.qualify_pic","t_organization_verify.verify_status","t_organization_verify.email"
        );
		$left = new  stdclass;
		$left->t_organization_profile='t_organization_profile.fk_org=t_organization.pk_org';
		$left->t_organization_verify='t_organization_verify.fk_org=t_organization.pk_org';
		return $db->selectone($table,array("t_organization.fk_user_owner"=>$uid),$item,"","",$left);
	}
	public static function getMgrOrgVerify($uid){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");

		$item = array(
            "t_organization.pk_org","t_organization.fk_user_owner","t_organization.name","t_organization.thumb_big","t_organization.thumb_med","t_organization.thumb_small",
            "t_organization.status","t_organization.create_time","t_organization.last_updated",
            "t_organization_profile.subname",
            "t_organization_profile.statistic",
			"t_organization_profile.company",
			"t_organization_profile.address",
			"t_organization_profile.email",
			"t_organization_profile.areacode",
			"t_organization_profile.hotline",
        );
		$left = new  stdclass;
		$left->t_organization_profile='t_organization_profile.fk_org=t_organization.pk_org';
		return $db->selectone($table,array("t_organization.pk_org"=>$uid),$item,"","",$left);
	}
	
	public static function getOrgByCid($cid,$page,$length){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");

		$item = array(
            "t_organization.pk_org",
			"t_organization.fk_user_owner",
			"t_organization.name",
			"t_organization.thumb_big",
			"t_organization.thumb_med",
			"t_organization.thumb_small",
            "t_organization.status",
			"t_organization.create_time",
			"t_organization.last_updated",
			"t_organization_profile.scopes",
            "t_organization_profile.subname",
			"t_organization_profile.company",
			"t_organization_profile.address",
			"t_organization_profile.email",
			"t_organization_profile.areacode",
			"t_organization_profile.hotline",
        );
		$left = new  stdclass;
		$left->t_organization_profile='t_organization_profile.fk_org=t_organization.pk_org';
		$db->setPage($page);
        $db->setLimit($length);
		return $db->select($table,array("t_organization_profile.province"=>$cid,"t_organization.status"=>1),$item,"","",$left);
	}
	public static function getNormalOrgNameByInfo($data){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");
		$item = array(
            "t_organization.pk_org",
			"t_organization.fk_user_owner",
			"t_organization.name",
			"t_organization.status",
            "t_organization_profile.subname"
        );
		$left = new  stdclass;
		$left->t_organization_profile='t_organization_profile.fk_org=t_organization.pk_org';
		$condition = "t_organization_profile.subname like '%".$data."%' and t_organization.status IN('1','2')";
		return $db->select($table,$condition,$item,"","",$left);
	}
	public static function getOrgProfileByUid($uid){
        $db = self::InitDB("db_user","query");
		//$key =md5("db_user.t_organization_profile.$uid");
		//$v = redis_api::get($key);
	//	if($v!==false)return $v;

		$table=array("t_organization_profile");
        $condition=array("fk_user_owner"=>$uid);
		$v = $db->selectone($table,$condition,"*","","","");
		//if(!$v)$v=0;
		//redis_api::set($key,$v,120);
		return $v;
	}
	public static function getMgrOrgProfileByInfo($uid){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_profile");
        $condition=array("fk_org"=>$uid);
		$v = $db->selectone($table,$condition,"*","","","");
		return $v;
	}
	//查询机构审临时表是否uid存在
	public static function getOrgProfileByUidTmp($uid){
        $db = self::InitDB("db_user","query");
		//$key =md5("db_user.t_organization_profile.$uid");
		//$v = redis_api::get($key);
	//	if($v!==false)return $v;
		$table=array("t_organization_profile_check");
        $condition=array("fk_user_owner"=>$uid);
		
		$v = $db->selectone($table,$condition,"*","","","");
		//if(!$v)$v=0;
		//redis_api::set($key,$v,120);
		return $v;
	}

	public static function getOrgProfileByUidInfo($uid,$data){
        $db = self::InitDB("db_user","query");
		//$key =md5("db_user.t_organization_profile.$uid");
		//$v = redis_api::get($key);
	//	if($v!==false)return $v;
		$table=array("t_organization_profile_check");
        $condition=array("fk_user_owner"=>$uid);
		
		$v = $db->selectone($table,$condition,"*","","","");
		//if(!$v)$v=0;
		//redis_api::set($key,$v,120);
		return $v;
	}
	public static function addOrgProfile($data){
		$table=array("t_organization_profile");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
	

	//机构审核临时表插入数据
	public static function addOrgProfileTmp($data){
		$table=array("t_organization_profile_check");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
	
	public static function updateOrgProfileTmp($uid,$data){
		//$key =md5("db_user.t_organization_profile.$uid");
		//redis_api::del($key);
		$table=array("t_organization_profile_check");
		$db = self::initdb();
		return $db->update($table,array("fk_user_owner"=>$uid),$data);
	}
	public function updateOrgProfile($uid,$data){
		//$key =md5("db_user.t_organization_profile.$uid");
		//redis_api::del($key);
		$table=array("t_organization_profile");
		$db = self::initdb();
		return $db->update($table,array("fk_user_owner"=>$uid),$data);
	}
	public function getOrgSlideList($uid,$page,$length){
		$table=array("t_organization_banner");
        $db = self::InitDB("db_user","query");
        $condition=array("fk_user"=>$uid);
		$db->setPage($page);
		$db->setLimit($length);
		return $db->select($table,$condition,"","","","");
	}
    public function addOrgSlide($data){
		$table=array("t_organization_banner");
        $db=self::initdb();
        return $db->insert($table,$data);

    }
	public static function getOrgOfNavList($oid){
        $table 		= array("t_organization_nav");
        $db    		= self::InitDB('db_user', 'query');
		$condition 	= array("fk_org"=>$oid);
		$item 		= array("pk_nav_id","nav_name","url","fk_org","create_time");
        return $db->select($table, $condition, $item);
    }
	
	public static function addOrgOfNav($data){
		$table=array("t_organization_nav");
        $db=self::initdb();
        return $db->insert($table,$data);
    }
	public static function updateOrgOfNavOneInfo($pid,$data){
		$table=array("t_organization_nav");
		$db = self::initdb();
		return $db->update($table,array("pk_nav_id"=>$pid),$data);
	}
	public static function delOrgOfNav($data){
		$table=array("t_organization_nav");
		$db = self::InitDB();
		return $db->delete($table,$data);
	}
	public function getOrgSlide($sid){
		$table=array("t_organization_banner");
        $db=self::initdb();
        $condition=array("pk_slide"=>$sid);
		return $db->selectone($table,$condition,"","","","");
	}
	public static function orgAboutProfileInfo($ownerId){
		$table=array("t_organization_profile");
        $db=self::initdb();
        $condition=array("fk_user_owner"=>$ownerId);
		return $db->selectone($table,$condition,"","","","");
	}
	public static function delOrgSlide($sid){
		$table=array("t_organization_banner");
		$db = self::InitDB();
		return $db->delete($table,array("pk_slide"=>$sid));
	}
	public static function delCheckInfo($bid){
		$table=array("t_organization_profile_check");
		$db = self::InitDB();
		return $db->delete($table,array("fk_user_owner"=>$bid));
	}

	public static function updatehotType($uid,$data){
		$table=array("t_organization_profile");
		$db = self::initdb();
		return $db->update($table,array("fk_user_owner"=>$uid),$data);
	}

	public function getOrgSlideBySidUid($sid,$uid){
		$table=array("t_organization_banner");
        $db=self::initdb();
        $condition=array(
                "pk_slide"=>$sid,
                "fk_user"=>$uid,
            );
		return $db->selectone($table,$condition,"","","","");
	}
	public function updateOrgLogo($uid,$data){
		$table=array("t_organization");
		$db = self::initdb();
		return $db->update($table,array("fk_user_owner"=>$uid),$data);
	}
	public function updateOrgSlide($sid,$data){
		$table=array("t_organization_banner");
		$db = self::initdb();
		return $db->update($table,array("pk_slide"=>$sid),$data);
	}
	public static function getOrgByUid($uid){
        $db = self::InitDB("db_user","query");
		//$key =md5( "db_user.t_organization.$uid");
		//$v = redis_api::get($key);
		//if($v!==false)return $v;

		$table=array("t_organization");
        $condition=array("fk_user_owner"=>$uid);
		$item= array(
			"user_owner_id"=>"fk_user_owner",
			"oid"=>"pk_org",
			"name"=>"name",
			"is_pro"=>"is_pro",
			"desc"=>"desc",
			"thumb_big"=>"thumb_big",
			"thumb_med"=>"thumb_med",
			"thumb_small"=>"thumb_small",
			"status"=>"status",
			"create_time"=>"create_time",
			"last_updated"=>"last_updated",
		);
		$v = $db->selectone($table,$condition,$item,"","","");
		if(!$v)$v=0;
	//	redis_api::set($key,$v,120);
		return $v;
	}

	public static function getOrgNameInfo($uid){
        $db = self::InitDB("db_user","query");

		$table=array("t_organization");
        $condition=array("pk_org"=>$uid);
		$item= array(
			"user_owner_id"=>"fk_user_owner",
			"oid"=>"pk_org",
			"name"=>"name",
			"desc"=>"desc",
			"thumb_big"=>"thumb_big",
			"thumb_med"=>"thumb_med",
			"thumb_small"=>"thumb_small",
			"status"=>"status",
			"create_time"=>"create_time",
			"last_updated"=>"last_updated",
			"last_updated"=>"last_updated",
		);
		$v = $db->selectone($table,$condition,$item,"","","");
		return $v;
	}

	public static function getOrgNameInfoTmp($uid){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization");
        $condition=array("t_organization.pk_org"=>$uid);
		$item= array(
			"user_owner_id"=>"t_organization.fk_user_owner",
			"oid"=>"t_organization.pk_org",
			"name"=>"t_organization.name",
			"subname"=>"t_organization_profile.subname",
			"company"=>"t_organization_profile.company",
			"scopes"=>"t_organization_profile.scopes",
			"province"=>"t_organization_profile.province",
			"city"=>"t_organization_profile.city",
			"county"=>"t_organization_profile.county",
			"address"=>"t_organization_profile.address",
			"areacode"=>"t_organization_profile.areacode",
			"hotline"=>"t_organization_profile.hotline",
			"policy"=>"t_organization_profile.policy",
			"email"=>"t_organization_profile.email",
			"desc"=>"t_organization.desc",
			"thumb_big"=>"t_organization.thumb_big",
			"thumb_med"=>"t_organization.thumb_med",
			"thumb_small"=>"t_organization.thumb_small",
			"status"=>"t_organization.status",
			"create_time"=>"t_organization.create_time",
			"last_updated"=>"t_organization.last_updated",
			"hot_type"=>"hot_type"
		);
        $left=array(
                't_organization_profile'=>'t_organization_profile.fk_org=t_organization.pk_org'
            );
		$v = $db->selectone($table,$condition,$item,"","",$left);
		//if(!$v)$v=0;
		//redis_api::set($key,$v,120);
		return $v;
	}
    public static function orgSearchByNameOrId($key){
		$db = self::InitDB('db_user', 'query');
        $table = array('t_organization');	
		$condition = '';
        if (is_numeric($key)) {
            $condition = "t_organization.fk_user_owner={$key} AND ";
        } else {
            $condition = "t_organization_profile.subname LIKE '%{$key}%' AND ";
        }
		$left = new stdclass;
		$left->t_organization_profile = 't_organization_profile.fk_user_owner = t_organization.fk_user_owner';
		$item = array('t_organization.pk_org','t_organization.fk_user_owner','t_organization.name','t_organization_profile.subname');
		$condition .= "t_organization.status >= 1";
		$orderby = array('t_organization.pk_org' => 'asc');
		return $db->select($table,$condition,$item,'',$orderby,$left);

        return $db->select($table, $condition);
    }

    public static function getOrgInfoByUidArr($arr){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization');
        $uidStr = implode(',', $arr);
        $condition = "t_organization.fk_user_owner IN ($uidStr)";
		$item= array(
			"user_owner"=>"t_organization.fk_user_owner",
			"oid"=>"t_organization.pk_org",
			"name"=>"t_organization.name",
			"subname"=>"t_organization_profile.subname",
			"company"=>"t_organization_profile.company",
			"scopes"=>"t_organization_profile.scopes",
			"province"=>"t_organization_profile.province",
			"city"=>"t_organization_profile.city",
			"county"=>"t_organization_profile.county",
			"address"=>"t_organization_profile.address",
			"areacode"=>"t_organization_profile.areacode",
			"hotline"=>"t_organization_profile.hotline",
			"policy"=>"t_organization_profile.policy",
			"email"=>"t_organization_profile.email",
			"desc"=>"t_organization.desc",
			"thumb_big"=>"t_organization.thumb_big",
			"thumb_med"=>"t_organization.thumb_med",
			"thumb_small"=>"t_organization.thumb_small",
			"status"=>"t_organization.status",
			"create_time"=>"t_organization.create_time",
			"last_updated"=>"t_organization.last_updated",
		);
        $left=array(
                't_organization_profile'=>'t_organization_profile.fk_org=t_organization.pk_org'
            );
        return $db->select($table,$condition,$item,'','',$left);
    }
    public static function getOrgInfoByOidArr($oidArr, $join = true){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization');
        $oidStr = implode(',', $oidArr);
        $condition = "pk_org IN ($oidStr)";
		$items = array("t_organization.pk_org",
					"t_organization.fk_user_owner",
					"name","thumb_big","thumb_med","thumb_small","desc","status","create_time"
					);
		$left = '';
		if ($join) {
			$items[] = "t_organization_profile.subname";
			$left=array(
				't_organization_profile'=>'t_organization_profile.fk_org=t_organization.pk_org'
			);
		}

        return $db->select($table, $condition,$items,'','',$left);
    }

    public static function getOrgList($page, $limit, $condition='', $orderBy='')
    {
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization');
		
        if ($page && $limit) {
            $db->setPage($page);
            $db->setLimit($limit);
            $db->setCount(true);
        }

        return $db->select($table, $condition,'', '', $orderBy);
    }

	public static function getOrgAllName()
    {
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization');
        return $db->select($table, '','', '', '');
    }

	public static function getsearchDataList($page, $limit, $condition='', $orderBy='')
    {
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization');
		
        if ($page && $limit) {
            $db->setPage($page);
            $db->setLimit($limit);
            $db->setCount(true);
        }

        return $db->select($table, $condition,'', '', $orderBy);
    }

    public static function searchUser($table, $params=array())
    {
        $db = self::InitDB('db_user', 'query');

        $condition = isset($params['condition']) && $params['condition'] ? $params['condition'] : '';
        $item      = isset($params['item']) && $params['item'] ? $params['item'] : '';
        $orderBy   = isset($params['orderBy']) && $params['orderBy'] ? $params['orderBy'] : '';
        $groupBy   = isset($params['groupBy']) && $params['groupBy'] ? $params['groupBy'] : '';
        $leftJoin  = isset($params['leftJoin']) && $params['leftJoin'] ? $params['leftJoin'] : '';
        $page      = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length    = isset($params['length']) && $params['length'] ? $params['length'] : 100;

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, $groupBy, $orderBy, $leftJoin);
    }

	public static function getOrgAbout($uid){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization");
        $condition=array("fk_user_owner"=>$uid);
		$item= array(
			"name"=>"name",
			"desc"=>"desc",
		);
		$v = $db->selectone($table,$condition,$item,"","","");
		return $v;
	}
	public static function getOrgByOwner($uid){
        $db = self::InitDB("db_user","query");

		$key ="t_organization";
		
		$hash =md5( "db_user.t_organization_info.$uid");
		/*$v = redis_api::hGet($key,$hash);
		if($v!==false)return $v;*/
		$table=array("t_organization");
        $condition=array("t_organization.fk_user_owner"=>$uid);
		$item= array(
			"user_owner_id"=>"t_organization.fk_user_owner",
			"oid"=>"t_organization.pk_org",
			"name"=>"t_organization.name",
			"subname"=>"t_organization_profile.subname",
			"company"=>"t_organization_profile.company",
			"scopes"=>"t_organization_profile.scopes",
			"province"=>"t_organization_profile.province",
			"city"=>"t_organization_profile.city",
			"county"=>"t_organization_profile.county",
			"address"=>"t_organization_profile.address",
			"areacode"=>"t_organization_profile.areacode",
			"statistic"=>"t_organization_profile.statistic",
			"hotline"=>"t_organization_profile.hotline",
			"policy"=>"t_organization_profile.policy",
			"email"=>"t_organization_profile.email",
			"desc"=>"t_organization.desc",
			"thumb_big"=>"t_organization.thumb_big",
			"thumb_med"=>"t_organization.thumb_med",
			"thumb_small"=>"t_organization.thumb_small",
			"status"=>"t_organization.status",
			"create_time"=>"t_organization.create_time",
			"last_updated"=>"t_organization.last_updated",
			"hot_type"=>"hot_type",
			"is_pro"=>"is_pro",
			"have_app"=>"have_app",
		);
        $left=array(
                't_organization_profile'=>'t_organization_profile.fk_org=t_organization.pk_org'
            );
		$v = $db->selectone($table,$condition,$item,"","",$left);
		if($v!==false){
			redis_api::hSet($key,$hash,$v);
			redis_api::expire($key,86400);
		}
		return $v;
	}
	public static function getOrgByTeacher($uid){
		$table=array("t_organization","t_organization_user");
		$item= array(
			"user_owner_id"=>"fk_user_owner",
			"oid"=>"pk_org",
			"name"=>"name",
			"thumb_big"=>"thumb_big",
			"desc"=>"desc",
			"thumb_med"=>"thumb_med",
			"thumb_small"=>"thumb_small",
			"status"=>"t_organization.status",
			"create_time"=>"t_organization.create_time",
			"last_updated"=>"t_organization.last_updated",
		);
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,array("fk_user"=>$uid,"t_organization.pk_org=t_organization_user.fk_org"),$item);
	}
	public function updateorg($uid,$org){
		//$key =md5( "db_user.t_organization.$uid");
		$table=array("t_organization");
		$db = self::initdb();


		$key ="t_organization";
		redis_api::hDelAll($key);

		return $db->update($table,array("fk_user_owner"=>$uid),$org);
	}
	
	public static function updateorginfo($uid,$org){
		//$key =md5( "db_user.t_organization_verify.$uid");
		//redis_api::del($key);
		$table=array("t_organization_verify");
		$db = self::initdb();
		return $db->update($table,array("fk_org"=>$uid),$org);
	}

	
	public function updateSubInfo($uid,$data){
		$table=array("t_organization_profile");
		$db = self::initdb();

		$key ="t_organization";
		$profile = $db->selectOne($table,array("fk_org"=>$uid));
		if(!empty($profile)){
			$hash =md5( "db_user.t_organization_info.{$profile['fk_user_owner']}");
			$v = redis_api::hDel($key,$hash);
		}

		return $db->update($table,array("fk_org"=>$uid),$data);
	}
	public  static function updatestatus($uid,$para){
		$table=array("t_organization");

		$key ="t_organization";
		redis_api::hDelAll($key);

		$db = self::initdb();
		return $db->update($table,array("pk_org"=>$uid),$para);
	}
	public static function getTokenStatus($livePlanId, $userId){
		$item = new stdclass;
		$item->live_status = "live_status";
		$item->user_status = "user_status";
		$table = array("t_token");
		$condition = array("fk_user"=>$userId);
        $db = self::InitDB("db_user","query");
		return $db->select($table, $condition, $item);
	}
	public static function addFav($data){
		$table=array("t_fav_course");
		$db = self::InitDB();
		return $db->insert($table,$data);
	}
	public static function updatefav($favid,$data){
		$table=array("t_fav_course");
		$db = self::initdb();
		return $db->update($table,array("pk_fav"=>$favid),$data);
	}

    public static function delFav($condition)
    {
        $table = array("t_fav_course");
        $db    = self::initdb();

        return $db->delete($table, $condition);
    }

	public static function listFav($cid = null,$uid=null,$page,$length){
		$table=array("t_fav_course");
		$item = new stdclass;
		$item->fav_id = "pk_fav";
		$item->user_id = "fk_user";
		$item->course_id = "fk_course";
		$condition = array();
		if($cid){
			$condition["fk_course"]=$cid;
		}
		if($uid){
			$condition["fk_user"] =$uid;
		}
        $db = self::InitDB("db_user","query");
		if($page){
			$db->setPage($page);
		}
		if($length){
			$db->setLimit($length);
		}
		$orderby = array("pk_fav"=>"DESC");
		return $db->select($table,$condition,$item,"",$orderby,"");
	}
	public function getOrgSetHotType($fk_user_owner){
		$db = self::InitDB("db_user","query");
		$table = array("t_organization");
		$left=new stdclass;
		$left->t_organization_profile="t_organization_profile.fk_user_owner=t_organization.fk_user_owner";
		$condition = "t_organization.fk_user_owner='$fk_user_owner'";
		return $db->selectOne($table,$condition,"","","",$left);
	}
    //添加公告
    public function addNotice($data){
        $table=array("t_notice");
        $db=self::initdb();
        return $db->insert($table,$data);

    }
    //修改公告
    public function updateNotice($nid,$data){
        $table=array("t_notice");
        $db=self::initdb();
        return $db->update($table,array('pk_notice_id'=>$nid),$data);

    }
    public static function updateConditionOfCateNotice($where,$data){
        $table=array("t_notice");
        $db=self::initdb();
        return $db->update($table,$where,$data);

    }
    //删除公告
    public function delNotice($nid){
        $table=array("t_notice");
        $db=self::initdb();
        return $db->delete($table,array('pk_notice_id'=>$nid));

    }
	 public static function delMgrOrgUserRole($data){
        $table=array("t_organization_user");
        $db=self::initdb();
        return $db->delete($table,$data);

    }
    //置顶公告
    public function topNotice($nid,$uid){
        $db=self::initdb();
        $sql='update t_notice set sort=(select max(sort) from (select * from t_notice where fk_user_id='.$uid.') as a)+1 where pk_notice_id='.$nid;
        return $db->execute($sql);

    }
    // 取消置顶公告
    public function noTopNotice($nid){
        $table=array("t_notice");
        $db=self::initdb();
        return $db->update($table,array('pk_notice_id'=>$nid),array('sort'=>0));

    }
    //公告列表
	public function getNoticeList($page,$length,$uid=null,$cateId = 0,$condition=array()){
		$table=array("t_notice");
		$db = self::InitDB("db_user","query");
		if($uid){
			$condition["fk_user_id"] =$uid;
		}
        if($cateId ==-1){
            $condition["fk_cate"] = 0;
        }elseif($cateId==0){
			$condition["fk_user_id"] =$uid;
		}else{
			 $condition["fk_cate"] = $cateId;
		}
		if($page){
			$db->setPage($page);
		}
		if($length){
			$db->setLimit($length);
		}
        
		$left = new  stdclass;
		$left->t_notice_category='t_notice_category.pk_cate=t_notice.fk_cate';
		$orderby = array("t_notice.sort"=>"DESC","t_notice.create_time"=>"DESC");
		return $db->select($table,$condition,"","",$orderby,$left);
	}
	public function getNoticeCateEmpty($uid=null,$cateId = 0){
		$table=array("t_notice");
        $db = self::InitDB("db_user","query");
		$left = new  stdclass;
		$left->t_notice_category='t_notice_category.pk_cate=t_notice.fk_cate';
		$groupBy = "t_notice.fk_cate";
		return $db->select($table,array("fk_user_id"=>$uid),"",$groupBy,"",$left);
	}
    public static function getNoticeConditionInfo($condition){
		$table=array("t_notice");
        $db = self::InitDB("db_user","query");
		return $db->select($table,$condition);
	}
    //获取公告
	public function getNotice($nid){
		$table=array("t_notice");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,array("pk_notice_id"=>$nid));
	}
	//GET USERID BY SUBDOMAIN
	public static function getUserIdBySubDomain($subdomain){
		$db = self::InitDB("db_user","query");
		$key ="t_user_subdomain";
		$hash = md5( "user_db.t_user_subdomain.".$subdomain);
		$v = redis_api::hGet($key,$hash);
		if($v !== FALSE){
			return $v;
		}
		$table=array("t_user_subdomain");
		$v = $db->selectOne($table,array("subdomain"=>$subdomain,"status"=>1 ), array('userId'=>'fk_user','subdomain','status'));
		if($v == FALSE){
			$v = 0;
		}
		redis_api::hSet($key,$hash,$v);
		redis_api::expire($key,86400);
		return $v;
	}
	public static function getSubDomainByUserId($user_id){
		$db = self::InitDB("db_user","query");
		$key = "t_user_subdomain";
		$hash =md5( "user_db.t_user_subdomain.user_id.".$user_id);
		$v = redis_api::hGet($key,$hash);
		if($v !== FALSE){
			return $v;
		}
		$table=array("t_user_subdomain");
		$v = $db->selectOne($table,array("fk_user"=>$user_id,"status"=>1 ), array('subdomain'=>'subdomain'));
        if($v == FALSE){
            $v = 0;
		}
		redis_api::hSet($key,$hash,$v);
		redis_api::expire($key,86400);
		return $v;
	}
	//for sphinx indexing teacher
    public function listTeachers( $page, $length, $minute=NULL ){
        $left=new stdclass;
        $left->t_organization_user="t_user.pk_user=t_organization_user.fk_user";
        $left->t_user_mobile="t_user.pk_user=t_user_mobile.fk_user";
        $left->t_user_teacher_profile="t_user.pk_user=t_user_teacher_profile.fk_user";
        $db = self::InitDB("db_user","query_sphinx");
        $db->setPage($page);
        $db->setLimit($length);
        $condition = array("type & 0x02 > 0");
		if(NULL !== $minute && (int)($minute)){
            $condition[] = "t_user.last_updated > DATE_SUB(NOW(),INTERVAL $minute MINUTE)";
        }
        $orderby = array( "pk_user"=>"asc" );
        $item = array("pk_user","t_user.name","birthday","gender","t_user.status as user_status","verify_status","thumb_big",
"thumb_med","thumb_small","register_ip","t_user.create_time","last_login","fk_org","t_organization_user.status as teacher_status",
"role","user_role","sort","is_star","t_user_mobile.mobile","t_user_mobile.province as mobile_province","t_user_mobile.city as mobile_city","title","college","years",
"diploma","major","t_user_teacher_profile.desc","t_user_teacher_profile.brief_desc","t_user.real_name");
        return $db->select("t_user",$condition,$item,"",$orderby,$left);
	}
	//for sphinx indexing organization
    public function listOrganizations( $page, $length, $minute=NULL ){
        $left=new stdclass;
        $left->t_organization_profile="t_organization.pk_org=t_organization_profile.fk_org";
        $left->t_organization_account="t_organization.pk_org=t_organization_account.fk_org";
        $db = self::InitDB("db_user","query_sphinx");
        $db->setPage($page);
        $db->setLimit($length);
        $condition = array();
		if(NULL !== $minute && (int)($minute)){
            $condition[] = "t_organization.last_updated > DATE_SUB(NOW(),INTERVAL $minute MINUTE)";
        }
        $orderby = array( "pk_org"=>"asc" );
		$item = array("pk_org","t_organization.fk_user_owner as user_owner_id","name","thumb_big","thumb_med","thumb_small",
			"t_organization.desc as descript","t_organization.status","t_organization.create_time","subname","scopes","city",
			"province","address","hotline","email","hot_type","balance","income_all","order_count","withdraw",
			"income_last_week","income_last_month","orders_last_week","orders_last_month","is_pro","have_app");
        return $db->select("t_organization",$condition,$item,"",$orderby,$left);
    }
    public function listTeachersNew( $page, $length, $minute=NULL ){
        $left=new stdclass;
        $left->t_user_teacher_profile="t_user.pk_user=t_user_teacher_profile.fk_user";
        $db = self::InitDB("db_user","query");
        $db->setPage($page);
        $db->setLimit($length);
        $condition = array("type & 0x02 > 0");
		if(NULL !== $minute && (int)($minute)){
            $condition[] = "t_user.last_updated > DATE_SUB(NOW(),INTERVAL $minute MINUTE)";
        }
        $orderby = array( "pk_user"=>"asc" );
        $item = array("pk_user","t_user.name","birthday","gender","t_user.status as user_status",
	"verify_status","thumb_big","thumb_med","thumb_small","register_ip","t_user.create_time","last_login",
	"title","college","years","diploma","major","t_user_teacher_profile.desc","t_user_teacher_profile.brief_desc","t_user.real_name");
        return $db->select("t_user",$condition,$item,"",$orderby,$left);
    }
	public static function listOrgTeachersByUserIds( $idsStr ){
		if($idsStr === '')
			return FALSE;
        $db = self::InitDB("db_user","query");
        $left=new stdclass;
		$left->t_organization = "t_organization.pk_org=t_organization_user.fk_org";
		$left->t_organization_profile = "t_organization_profile.fk_org=t_organization_user.fk_org";
        $condition = array("fk_user in ( $idsStr )","t_organization_user.status <> -1");
        $item = array('fk_user','t_organization_user.status as teacher_status','role','user_role','sort','is_star','visiable','name',
						'thumb_big','thumb_med','thumb_small','pk_org','t_organization.fk_user_owner','t_organization_profile.subname');
        $table = array("t_organization_user");
        return $db->select($table, $condition, $item, '', '', $left);
    }
	public static function listMobilesByUserIds( $idsStr ){
		if($idsStr === '')
			return FALSE;
        $db = self::InitDB("db_user","query");
        $condition = array("fk_user in ( $idsStr )");
        $item = array('fk_user','mobile','province','city');
        $table = array("t_user_mobile");
        return $db->select($table, $condition, $item, '', '', '');
    }
    /*
     * @desc 模糊搜索手机号 (机构下)
     * @param $uidsArr 用户id
     * @param $mobile  手机号
     */
	public static function listUserIdsBylikeMobileArr($uidsArr,$mobile){
		$db = self::InitDB('db_user', 'query');
		$table = array('t_user_mobile');
		$uidstr = implode(',', $uidsArr);
		$condition[] = "t_user_mobile.fk_user IN ($uidstr)";
		$condition[] = "t_user_mobile.mobile LIKE '%{$mobile}%'";
        $item = array('user_id'=>'t_user_mobile.fk_user','t_user_mobile.mobile','province','city','t_user_profile.real_name','t_user.name');
		$left=new stdclass;
		$left->t_user_profile = "t_user_profile.fk_user=t_user_mobile.fk_user";
		$left->t_user = "t_user.pk_user=t_user_mobile.fk_user";
		return $db->select($table, $condition,$item,'','',$left);
    }
    /*
     * @desc 模糊搜索用户名 (机构下)
     * @param $uidsArr 用户id
     * @param $name    用户名
     */
	public static function listUserIdsBylikeNameArr($uidsArr,$name){
		$db = self::InitDB('db_user', 'query');
		$table = array('t_user_profile');
		$uidstr = implode(',', $uidsArr);
		$condition[] = "t_user_profile.fk_user IN ($uidstr)";
		$condition[] = "t_user_profile.real_name LIKE '%{$name}%'";
        $item = array('user_id'=>'t_user_profile.fk_user','t_user_profile.real_name','t_user_profile.desc','t_user.name','t_user_profile.real_name');
		$left=new stdclass;
		$left->t_user = "t_user.pk_user=t_user_profile.fk_user";
		return $db->select($table, $condition,$item,"","",$left);
	}

	public function addUserFeedback($params){
		$db = self::InitDB('db_user');
		$table = 't_user_feedback';
		$item = array(
			'fk_user' => $params->fk_user,
			'ques_type' => $params->ques_type,
			'content' => $params->content,
			'mobile'  => $params->mobile,
			'create_time' => $params->create_time,
		);
		return $db->insert($table, $item);
	}
	
	public function SetFeedback($params)
	{
		$db = self::InitDB('db_user');
		$table = 't_user_feedback';
		$data = [
			'reply'      => $params->reply,
			'reply_time' => date('Y-m-d H:i:s')
		];
		$condition = array('pk_fdk'=> $params->fdkId);
		return $db->update($table, $condition, $data);
	}
	

	public function getUserFeedbackByUid($uid){
		$db = self::InitDB('db_user', 'query');
		$table = 't_user_feedback';
		$condition = "fk_user = $uid";
		$orderby  = array('create_time'=>'desc');
		return $db->select($table, $condition,'','',$orderby);
	}
	
	public function getUserFeedbackByFdkId($fdkId)
	{
		$db = self::InitDB('db_user', 'query');
		$table = 't_user_feedback';
		$condition = "pk_fdk = $fdkId";
		return $db->selectOne($table, $condition);
	}
	
	public function getUserFeedbackList($page,$length,$params)
	{
		$db = self::InitDB('db_user', 'query');
		$condition = '';
		
		if(!empty($params['type'])){
			$condition.= " FIND_IN_SET({$params['type']},ques_type) ";
		}
		
		if(!empty($params['starttime']) && !empty($params['endtime']) && !empty($params['type'])){
			$condition.= " and create_time >= '".$params['starttime']."' and ";
		}elseif(!empty($params['starttime']) && !empty($params['endtime']) && empty($params['type'])){
			$condition.= " create_time >= '".$params['starttime']."' and ";
		}elseif(!empty($params['starttime']) && empty($params['endtime']) && empty($params['type'])){
			$condition.= " create_time >= '".$params['starttime']."' ";
		}elseif(!empty($params['starttime']) && empty($params['endtime']) && !empty($params['type'])){
			$condition.= " and create_time >= '".$params['starttime']."' ";
		}
		
		if(!empty($params['endtime']) && !empty($params['type']) && empty($params['starttime'])){
			$condition.= " and create_time <= '".$params['endtime']."' ";
		}elseif(!empty($params['endtime'])){
			$condition.= " create_time <= '".$params['endtime']."' ";
		}
		
		$db->setPage($page);
		$db->setLimit($length);
		$table = 't_user_feedback';
		$orderby  = array('create_time'=>'desc');
		return $db->select($table, $condition,'','',$orderby);
	}
	

	//for sphinx indexing teacher
	public static function listOrgsByOrgIds( $idsStr ){
        $db = self::InitDB("db_user","query_sphinx");
		$left=new stdclass;
		$left->t_organization_profile = "t_organization.pk_org=t_organization_profile.fk_org";
		$condition = array("pk_org in ( $idsStr )");
		$item = array("pk_org","name","thumb_big","company","province","city","county","areacode","hotline");
		return $db->select("t_organization", $condition, $item, '', '', $left);
	}
	
	public function getSubdomainByUidArr($uidArr){
		
		$db = self::InitDB('db_user', 'query');
		$table = 't_user_subdomain';
		$uidStr = implode(',', $uidArr);
		$condition = "fk_user IN ($uidStr)";
		return $db->select($table, $condition);

	}
	
	public function getOrgProfileByUidArr($uidArr){
        $db = self::InitDB('db_user', 'query');
        $table = 't_organization_profile';
        $uidStr = implode(',', $uidArr);
        $condition = "fk_user_owner IN ($uidStr)";
        return $db->select($table, $condition);
    }

	public function getOrgProfileByOidArr($oidArr){
        $db = self::InitDB('db_user', 'query');
        $table = 't_organization_profile';
        $oidStr = implode(',', $oidArr);
        $condition = "fk_org IN ($oidStr)";
        return $db->select($table, $condition);
    }

    public function delHistoryStarTeacher($oid,$fk_user){
        $db = self::InitDB("db_user");
        return $db->update("t_organization_user",array("fk_org"=>$oid,'fk_user'=>$fk_user),array('is_star'=>0));  
    }

    public static function addUserThumb($data){
		$db = self::InitDB('db_user');
        $table = array('t_user_thumb');
        return $db->insert($table,$data);
    }
	
	public static function updateUserThumb($thumb_id,$data){
         $db = self::InitDB('db_user');
         $table = array('t_user_thumb');
		 $condition = array('pk_thumb'=> $thumb_id);
         return $db->update($table,$condition,$data);
    }

	public static function getUserThumbByUid($page,$limit,$uid){
    	$db = self::InitDB('db_user','query');
        $table = array('t_user_thumb');
        $condition = array('fk_user' => $uid);
		$order = array('create_time'=>'desc');
		if($page && $limit){
			$db->setPage($page);
			$db->setLimit($limit);
			$db->setCount(true);
		}
        return $db->select($table,$condition,'','',$order);
    }

	public static function checkUserThumb($data){
    	$db = self::InitDB('db_user','query');
        $table = array('t_user_thumb');
        $condition = array(
						'fk_user' => $data->fk_user,
						'thumb_big' => $data->thumb_big,
						'thumb_med' => $data->thumb_med,
						'thumb_small' => $data->thumb_small,
					);
		return $db->selectOne($table,$condition);
	}
	public static function getTeacherSpecial($oid,$uid){
		$table=array('t_organization_user');
        $db = self::InitDB('db_user','query');
		$item=array('fk_org','fk_user','role','user_role','status','sort','is_star','visiable');
		return $db->selectOne($table,array('fk_org'=>$oid,'fk_user'=>$uid,'status'=>1),$item);
	}
	public static function getSpecial($uid){
		$table=array('t_organization_user');
        $db = self::InitDB('db_user','query');
		return $db->selectOne($table,array('fk_user'=>$uid));
	}
	public static function countOrgRole($oid){
		$table=array('t_organization_user');
        $db = self::InitDB('db_user','query');
        $condition="fk_org={$oid} AND (user_role=&0x04 OR role=2) AND status=1";
		$item=array('count(fk_user) as sum');
		return $db->selectOne($table,$condition,$item);
	}
	public static function getmgrOrgInfo($sid,$fk_user){
		$table=array('t_organization_user');
        $db = self::InitDB('db_user','query');
        $condition="fk_org={$sid} AND fk_user={$fk_user} AND status=1";
		return $db->selectOne($table,$condition,'');
	}
	public static function getAllOrgManage($fk_org,$fk_user){
		$table=array('t_organization_user');
        $db = self::InitDB('db_user','query');
        $condition="fk_org={$fk_org} AND (user_role='&0x04' OR role=2 AND status=1) OR (fk_user=".$fk_user." and status=1)";
		$item=array('fk_user');
		return $db->select($table,$condition,$item);
	}
	public static function getOrgTeacherCount($oid_arr,$visiable=null){
		$table=array('t_organization_user');
        $db = self::InitDB('db_user','query');
		$item=array('count(t_organization_user.fk_user) as teacher_count','t_organization_user.fk_org');
		$oid_str = implode(',',$oid_arr);
		$condition = "t_organization_user.fk_org IN ($oid_str) AND t_organization_user.status <> -1 AND t_user.type & 0x02 > 0";
		$left=array(
			't_user'=>'t_organization_user.fk_user=t_user.pk_user'
		);
		if(!empty($visiable)){
			$condition .= "  AND visiable >0";
		}
		$group = array('fk_org');
		return $db->select($table,$condition,$item,$group,'',$left);
	}
	public function getUserMobileByUidArr($uidArr){
		$db = self::InitDB('db_user', 'query');
		$table = 't_user_mobile';
		$uidStr = implode(',', $uidArr);
		$condition = "fk_user IN ($uidStr)";
		return $db->select($table, $condition);

	}
	public static function addorgVerify($verify){
		$table=array("t_organization_verify");
		$db = self::InitDB();
		return $db->insert($table,$verify,true);
	}
	
	public static function getOrgVerifyBySubDomain($subdomain){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_verify");
        $condition=array("subdomain"=>$subdomain);
		$v = $db->selectone($table,$condition,"*","","","");
		return $v;
	}
	public static function getOrgSubdomain($fk_user){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_verify");
        $condition=array("fk_user_owner"=>$fk_user);
		$v = $db->selectone($table,$condition,"*","","","");
		return $v;
	}
	public static function getmgrOrgVerifySubdomain($fk_org){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_verify");
        $condition=array("fk_org"=>$fk_org);
		$v = $db->selectone($table,$condition,"*","","","");
		return $v;
	}
	public static function addOrgInfoVerify($verify){
		$table=array("t_organization_verify");
		$db = self::InitDB();
		return $db->insert($table,$verify);
	}
    //获取用户所属机构
	public static function getOrgIdsByUid($uid){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_user");
        $condition=array("fk_user"=>$uid);
		$v = $db->select($table,$condition,"*","","","");
		return $v;
	}
	
	public static function getteacherOrgArr($str){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_user");
		$condition= array("fk_user IN(".$str.")");
        $condition['status']=1;
		$v = $db->select($table,$condition,"*","","","");
		return $v;
	}
	public static function getteacherInfo($uid){
        $db = self::InitDB("db_user","query");
		$table=array("t_organization_user");
        $condition=array("fk_org"=>$uid,'status'=>1);
		$v = $db->select($table,$condition,"*","","","");
		return $v;
	}
	public static function delOrgteacherUser($sid,$data){
		$table=array("t_organization_user");
		$db = self::InitDB();
		return $db->delete($table,array("fk_user"=>$sid),$data);
	}


    //根据机构id获取subdomain
	public static function getSubDomainByOid($oid){
		$db = self::InitDB("db_user","query");
		$key = "t_user_subdomain";
		$hash =md5( "user_db.t_user_subdomain.oid.".$oid);
		$v = redis_api::hGet($key,$hash);
		if($v !== FALSE){
			return $v;
		}
		$table=array("t_user_subdomain");
        $condition=array(
                't_organization.pk_org'=>$oid,
                't_user_subdomain.status'=>1,
            );
        $items=array(
                'subdomain'=>'t_user_subdomain.subdomain',
                'owner_id'=>'t_user_subdomain.fk_user'
            );
        $left=array(
                't_organization'=>'t_organization.fk_user_owner=t_user_subdomain.fk_user'
            );
		$v = $db->selectOne($table,$condition,$items,'','',$left);
        if($v == FALSE){
            $v = 0;
		}
		redis_api::hSet($key,$hash,$v);
		redis_api::expire($key,86400);
		return $v;
	}
	

	public function checkName($uid,$name){
		$db = self::InitDB("db_user","query");
		$table = array("t_user");
		$condition = "name = '$name' and pk_user <> $uid";
		return $db->selectOne($table,$condition);
	}
    public static function getUserProfileByUidArr($arr){  
        $table=array("t_user_profile");
        $db = self::InitDB("db_user","query");
        $uidStr = implode(',', $arr);
        $condition='fk_user IN ('.$uidStr.')';
        $items=array(
                'user_id'=>'t_user_profile.fk_user',
				't_user_profile.real_name',
				't_user_profile.address',
				't_user_profile.birth_place',
				't_user_profile.desc',
				't_user_profile.zip_code',
				't_user.name',
            );
		$left=array(
			't_user'=>'t_user_profile.fk_user=t_user.pk_user'
		);
        return $db->select($table,$condition,$items,'','',$left);
    }

	public static function getUserStudentProfile ($uid)
	{
		$table=array("t_user_student_profile");
        $db = self::InitDB("db_user","query");
        $condition="fk_user=$uid";
		return $db->selectOne($table,$condition);
	}
	/**
	 * by hetal ,for golang message
	 * 根据用户ids，获取用户名和level信息
	 */
	public function getUserLevelByUids(array $uids){
		sort($uids);
        $db = self::InitDB('db_user','query');
		$key = implode(",",$uids);
		$v = redis_api::get($key);
		if($v){return $v;}

		$table=array("t_user");
		$left=new stdclass;
		$item = new stdclass;
		$item->user_id = "pk_user";
		$item->name = "real_name";
		$item->title= "title";
		$item->level= "fk_level";
		$item->thumb_med= "thumb_med";
		$item->score= "score";
		$left->t_user_score ="t_user_score.fk_user = t_user.pk_user";

		$data=array();
		$uids_array = array_chunk($uids,500);//一次只取500个用户
		foreach($uids_array as $uids){
			$ids = implode(",",$uids);
			$condition = "pk_user IN ($ids)";
			$v = $db->select($table, $condition, $item, '', 'fk_user desc',$left);
			if(!empty($v->items)){
				$data=array_merge($data,$v->items);
			}
		}
		redis_api::set($key,$data,3600*10);
		return $data;
    }
	public function getUserLevelByUidV2($uid){
		$infos = $this->getUserLevelByUids(array($uid));
		if(!empty($infos)){
			return $infos[0];
		}
		return array();
	}
	
	public function getUserLevelByUid($uid){
		$table=array("t_user_score");
        $db = self::InitDB("db_user","query");
		//$key =md5( "db_user.t_user_score.".$uid);
		//$v = redis_api::get($key);
		//if($v){return $v;}
        $condition="fk_user=$uid";
		$v = $db->selectOne($table,$condition);	
		//redis_api::set($key,$v,86400);
		return $v;
	}

	public function getNextLevel($fk_level){
		$table=array("t_level");
        $db = self::InitDB("db_user","query");
        $condition="pk_level=$fk_level+1";
		return $db->selectOne($table,$condition);	
	}

	public function getLevelByScore($score){
		$table=array("t_level");
        $db = self::InitDB("db_user","query");
        $condition="score_min <= $score and score_max >= $score";
		return $db->selectOne($table,$condition);	
	}
	
	public function getPreAndNextLevel($pk_level){
		$table=array("t_level");
        $db = self::InitDB("db_user","query");
		$mod = $pk_level%3;
		if( $mod == 2 ){
        	$condition="pk_level in ($pk_level,$pk_level+1,$pk_level+2,$pk_level+3)";
		}elseif($mod == 1){
        	$condition="pk_level in ($pk_level-1,$pk_level,$pk_level+1,$pk_level+2)";
		}elseif($mod == 0){
        	$condition="pk_level in ($pk_level-2,$pk_level-1,$pk_level,$pk_level+1)";
		}
		return $db->select($table,$condition);	
	}

	public function getUserSignByDay($day,$uid){
		$table=array("t_user_sign");
        $db = self::InitDB("db_user","query");
        $condition="`fk_user` = $uid and `day` = '$day'";
		return $db->selectOne($table,$condition);	
	}

	public function getLastUserSign($uid){
		$table=array("t_user_sign");
        $db = self::InitDB("db_user","query");
        $condition="fk_user=$uid";
		$order = array('day'=>'desc');
		return $db->selectOne($table,$condition,'','',$order);	
	}

	public function getScoreRuleByName($name){
		$table=array("t_score_rule");
        $db = self::InitDB("db_user","query");
        $condition="name='$name' and status = 1";
		return $db->selectOne($table,$condition);	
	}
	
	public function addUserSign($data){
        $db = self::InitDB("db_user");
		$table=array("t_user_sign");
		return $db->insert($table,$data);	
	}

	public function addUserScore($data){
        $db = self::InitDB("db_user");
		$table=array("t_user_score");
		return $db->insert($table,$data);	
	}

	public function updateUserScore($uid,$data){
        $db = self::InitDB("db_user");
		$table=array("t_user_score");
		//$key =md5( "db_user.t_user_score.".$uid);
		//$v = redis_api::del($key);
		$condition = "fk_user = $uid";
		return $db->update($table,$condition,$data);	
	}

	public function addUserScoreLog($data){
        $db = self::InitDB("db_user");
		$table=array("t_user_score_log");
		return $db->insert($table,$data);	
	}

	public function getAllUserCount(){
        $db = self::InitDB('db_user','query');
		$key =md5( "user_db.t_user."."count(*)");
		$v = redis_api::get($key);
		if($v){return $v;}
        $table = array('t_user');
        $condition = "status =1";
        $item = array('count(*) as count');
		$ret = $db->select($table,$condition,$item);
		redis_api::set($key,$ret,3600);
		return $ret;
    }
	
	public function getGtUserScoreCount($uid,$score){
        $db = self::InitDB('db_user','query');
        $table = array('t_user_score');
        $condition = "fk_user <> $uid and score > $score";     
        $item = array('count(*) as count');
 	    return $db->select($table,$condition,$item);
    }
	
	public function getUserRankByDate($start_date,$end_date,$page,$length ){
        $db = self::InitDB('db_user','query');
        $table = array('t_user_rank');
		$left = new stdclass;
		$left->t_user = 't_user.pk_user = t_user_rank.fk_user';
		$left->t_user_score = 't_user_score.fk_user = t_user_rank.fk_user';
        $condition = "t_user_rank.start_date = '$start_date' and  t_user_rank.end_date = '$end_date'";
        $item = array('t_user_rank.fk_user','t_user_rank.score as user_score','t_user_rank.sort','t_user.name','t_user.thumb_small','t_user.thumb_med','t_user.thumb_big','t_user_score.fk_level');
		$order = array('t_user_rank.sort' => 'asc');
		if($page && $length){
			$db->setPage($page);
			$db->setLimit($length);
		}
 	    return $db->select($table,$condition,$item,'',$order,$left);
    }
	
	public function getUserSortByDate($uid,$start_date,$end_date ){
        $db = self::InitDB('db_user','query');
        $table = array('t_user_rank');
        $condition = "fk_user = $uid and start_date = '$start_date' and end_date = '$end_date'";
        $item = array('fk_user','sort');
 	    return $db->selectOne($table,$condition,$item);
    }
	
	public function getUserAllSortByUid($uid){
        $db = self::InitDB('db_user','query');
        $table = array('t_user_score');
        $condition = "fk_user = $uid";
        $item = array('fk_user','sort');
 	    return $db->selectOne($table,$condition,$item);
    }
	
	public function getAllUserRank($page,$length){
        $db = self::InitDB('db_user','query');
        $table = array('t_user_score');
		$left = new stdclass;
		$left->t_user = 't_user.pk_user = t_user_score.fk_user';    
        $item = array('t_user_score.fk_user','t_user_score.score','t_user_score.fk_level','t_user_score.sort','t_user.name','t_user.thumb_small','t_user.thumb_med','t_user.thumb_big');
		$order = array('t_user_score.sort' => 'asc');
		$condition = array('t_user_score.sort <> 0');
		if($page && $length){
			$db->setPage($page);
			$db->setLimit($length);
		}
 	    return $db->select($table,$condition,$item,'',$order,$left);
    }
	
	public function getUserScoreLogCountbyDay($day,$rid,$uid){
        $db = self::InitDB('db_user','query');
        $table = array('t_user_score_log');
        $condition = "day='$day' and fk_rule=$rid and fk_user = $uid and status = 1";   		
        $item = array('sum(score) as score_count');
 	    return $db->select($table,$condition,$item);
    }
	
	public function getAllOrg(){
		$db = self::InitDB('db_user','query');
        $table = array('t_organization');
		$left = new stdclass;
		$left->t_organization_profile = 't_organization_profile.fk_user_owner = t_organization.fk_user_owner';
		$left->t_user_subdomain = 't_user_subdomain.fk_user = t_organization.fk_user_owner';
		$item = array('t_organization.pk_org','t_organization.fk_user_owner','t_organization.name','t_organization_profile.subname','t_user_subdomain.subdomain');
		$condition = "t_organization.status >= 1";
		$orderby = array('t_organization.pk_org' => 'asc');
		
		return $db->select($table,$condition,$item,'',$orderby,$left);
	}
	
	public function getOrgInfo(){
		$db = self::InitDB('db_user','query');
        $table = array('t_organization');
		$left = new stdclass;
		$left->t_organization_profile = 't_organization_profile.fk_user_owner = t_organization.fk_user_owner';
		$item = array('t_organization.pk_org','t_organization.fk_user_owner','t_organization.name','t_organization_profile.subname');
		$condition = "t_organization.status >= 1";
		$orderby = array('t_organization.pk_org' => 'asc');
		return $db->select($table,$condition,$item,'',$orderby,$left);
	}
	
	
    public static function getOrgTemplate($ownerId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_template');
        $condition=array('fk_user_owner'=>$ownerId);
        $item=array(
                'template_id'=>'fk_template',
                'owner_id'=>'fk_user_owner',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
				'sort'=>'sort',
				'type'=>'type',
				'thumb_left'=>'thumb_left',
				'thumb_right'=>'thumb_right',
				"thumb_left_url"=>"thumb_left_url",
				"thumb_right_url"=>"thumb_right_url"
            );
        $orderby=array('sort'=>'asc');
        return $db->select($table, $condition,$item,'',$orderby);
    }
	public static function getTemplateCheck($ownerId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_template_check');
        $condition=array('fk_user_owner'=>$ownerId);
        $item=array(
                'template_id'=>'pk_template',
                'owner_id'=>'fk_user_owner',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
				'sort'=>'sort',
				'type'=>'type',
				'thumb_left'=>'thumb_left',
				'thumb_right'=>'thumb_right',
				'thumb_left_url'=>'thumb_left_url',
				'thumb_right_url'=>'thumb_right_url',
				
            );
        $orderby=array('sort'=>'asc');
        return $db->select($table, $condition,$item,'',$orderby);
    }
    public static function getOrgTemplateInfo($tid){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_template_check');
        $condition=array('pk_template'=>$tid);
        $item=array(
                'template_id'=>'pk_template',
                'owner_id'=>'fk_user_owner',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
				'type'=>'type',
				'thumb_left'=>'thumb_left',
				'thumb_right'=>'thumb_right',
				'thumb_left_url'=>'thumb_left_url',
				'thumb_right_url'=>'thumb_right_url',
            );
        return $db->selectOne($table, $condition,$item);
    }
	public static function updateOrgTemplate($tid, $data){
		$db = self::InitDB();
		$table=array("t_organization_template_check");
		return $db->update($table, array('pk_template' => $tid), $data);
	}
	public static function addOrgTemplate($data){
		$db = self::InitDB();
		$table=array("t_organization_template_check");
		return $db->insert($table,$data);
	}
	public static function deleteOrgTemplate($tid){
		$table=array("t_organization_template_check");
		$db = self::InitDB();
		return $db->delete($table,array("pk_template"=>$tid));
	}
	public static function deleteOrgTemplateMoreInfo($tidStr){
		$table		=	array("t_organization_template");
		$db 		= 	self::InitDB();
		$condition	=	"fk_template IN(".$tidStr.")";
		return $db->delete($table,$condition);
	}
	public static function addOrgIsRecommend($oid,$data){
		$db = self::InitDB();
		$table=array("t_organization");
		return $db->update($table, array('pk_org' => $oid), $data);
	}
	public static function getOrgRecommendList($condition){
		$table=array("t_organization");
        $db = self::InitDB("db_user","query");
		$orderBy = array("org_sort"=>"asc","t_organization.`last_updated`"=>"asc");
		$left=new stdclass;
		$item = array("pk_org","name","t_organization.`last_updated`","org_sort","subdomain","thumb_big","thumb_med");
		$left->t_user_subdomain="t_user_subdomain.fk_user=t_organization.fk_user_owner";
		return $db->select($table,$condition,$item, '', $orderBy,$left);
	}
	public static function getOrgByProvince($province,$page,$length){
		$db = self::InitDB("db_user","query");
		$table=array("t_organization");
		$items="t_organization.pk_org,t_organization.fk_user_owner,t_organization.name,t_organization.thumb_med,";
		$items.="t_organization_profile.fk_org,t_organization_profile.scopes,t_organization_profile.subname,t_organization_profile.fk_user_owner,t_organization_profile.company";
		if($province == 1){
			$condition = ["t_organization.status"=>1,"t_organization_profile.province"=>$province];
        }elseif($province > 1){
            $condition = ["t_organization.status"=>1,"t_organization_profile.city"=>$province];
		}else{
			$condition = ["t_organization.status"=>1];
		}
		
		$left=new stdclass;
		$left->t_organization_profile="t_organization_profile.fk_org=t_organization.pk_org";
		
		$db->setPage($page);
		$db->setLimit($length);
		return $db->select($table,$condition,$items,'','',$left);
	}

	public static function customerServicesQqList($orgId){
		$table=array("t_organization_customer_service");
        $db = self::InitDB("db_user","query");
		//$condition = array("status"=>1,"fk_user_owner"=>$orgId,"type"=>"1,2");
                $condition="status=1 AND fk_user_owner=".$orgId." AND type IN(1,2)";
		return $db->select($table,$condition,'', '', 'last_updated desc','');
	}
	public static function getOrgCustomerInfo($condition){
		$table=array("t_organization_customer_service");
        $db = self::InitDB("db_user","query");
		return $db->selectone($table,$condition,'*');
	}
	public static function addOrgCustomerInfo($params){
		$db = self::InitDB();
		$table=array("t_organization_customer_service");
		return $db->insert($table,$params);
	}
	public static function updateOrgCustomerInfo($pid, $data){
		$db = self::InitDB();
		$table=array("t_organization_customer_service");
		return $db->update($table, array('pk_customer' => $pid), $data);
	}
	public static function delOrgCustomerInfo($pid,$data){
		$db = self::InitDB();
		$table=array("t_organization_customer_service");
		return $db->update($table, array("pk_customer"=>$pid),$data);
	}
    public static function getApplyOrgSubdomainOfUser($userId){
		$db = self::InitDB("db_user","query");
		$table=array("t_user_subdomain");
		$v = $db->selectOne($table,array("fk_user"=>$userId,"status"=>1 ), array('subdomain'=>'subdomain'));
		return $v;
	}
    public function countTeacherByOid($oid, $params){
        $table = array("t_organization_user");
        $condition = 'fk_org='.$oid;
        if (!empty($params->status)) {
            $condition .= ' AND status='.$params->status;
        }
        $items = array(
            'count(fk_user) as count',
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items);
    }
	public static function getUserGuideByUid($uid,$gid){
		$db = self::InitDB();
		$key =md5( "user_db.t_user_guide.".$uid.'_'.$gid);
		$v = redis_api::get($key);
		if($v){return $v;}
		$table=array("t_user_guide");
		$v = $db->selectOne($table,array("fk_user"=>$uid,"fk_guide"=>$gid), "*");
		redis_api::set($key,$v,300);
		return $v;
	}
	public static function addUserGuide($params){
		$db = self::InitDB();
		$table=array("t_user_guide");
		return $db->insert($table,$params);
	}
    public function updateUserGuide($uid,$gid,$params){
        $db=self::initdb();
		$key=md5("user_db.t_user_guide.".$uid.'_'.$gid);
		$v=redis_api::del($key);
        $table=array("t_user_guide");
        return $db->update($table,array('fk_user'=>$uid,'fk_guide'=>$gid),$params);
    }
    public function getOrgTemplateMaxSort($owner){
        $table = array("t_organization_template_check");
        $condition = 'fk_user_owner='.$owner;
        if (!empty($params->status)) {
            $condition .= ' AND status='.$params->status;
        }
        $items = array(
            'max(sort) as sort',
        );
        $db    = self::InitDB();
        return $db->selectOne($table, $condition, $items);
    }
	public static function AddNoticeCategory($params){
		$db = self::InitDB();
		$table=array("t_notice_category");
		return $db->insert($table,$params);
	}
	public static function noticeCategoryList($condition){
		$table=array("t_notice_category");
        $db = self::InitDB("db_user","query");
		return $db->select($table,$condition,'', '', 'last_updated desc','');
	}
	public static function getCateNameInfo($condition){
        $db = self::InitDB('db_user', 'query');
        $table = 't_notice_category';
        return $db->selectone($table, $condition);
    }
	public static function updateNoticeCate($cid,$data){
		$db = self::InitDB();
		$table=array("t_notice_category");
		return $db->update($table, array("pk_cate"=>$cid),$data);
	}
	
	public static function searchOrgTeacherByRealName($oid ,$realName){
		$table=array("t_organization_user");
        $db = self::InitDB("db_user","query");
		$condition = array(
				"t_organization_user.status = 1",
				't_organization_user.fk_org'=>$oid,
				't_user.real_name'=>"$realName",
				);
		$left=new stdclass;
		$left->t_user="t_organization_user.fk_user = t_user.pk_user";
		$item = array(
            "t_user.real_name",
            "t_user.thumb_big",
			"t_organization_user.fk_user as teacher_id"
        );
		return $db->select($table,$condition,$item,"",'',$left);
	}
	public static function getUserVerifyCodeLoginSms($data){
        $db = self::InitDB('db_user', 'query');
        $table = 't_user_verify_code';
		$condition = '';
		if(!empty($data['mobile']) && empty($data['code'])){
			$condition ="mobile='".$data['mobile']."'  and last_updated > '".$data['now']."'";
		}
		
		if(!empty($data['mobile']) && !empty($data['code'])){
			$condition ="mobile='".$data['mobile']."' and code='".$data['code']."' and last_updated > '".$data['now']."'";
		}
        return $db->selectone($table, $condition,"","","last_updated desc","");
    }
	public static function getTemplateData($tid,$ownerId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_template');
        $condition=array('fk_template'=>$tid,"fk_user_owner"=>$ownerId);
        $item=array(
                'template_id'=>'fk_template',
                'owner_id'=>'fk_user_owner',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
            );
        return $db->selectOne($table, $condition,$item);
    }
	public static function updateTemplateData($tid,$ownerId,$data){
		$db = self::InitDB();
		$table=array("t_organization_template");
		return $db->update($table, array('fk_template' => $tid,"fk_user_owner"=>$ownerId), $data);
	}
	public static function addTemplateData($data){
		$db = self::InitDB();
		$table=array("t_organization_template");
		return $db->insert($table,$data);
	}
        
        //机构管理员列表
        public static function OrgRoleList($oid){
		$table=array('t_organization_user');
                $db = self::InitDB('db_user','query');
                $condition="fk_org={$oid} AND (user_role='&0x04' OR role=2) AND status=1";
		
		return $db->select($table,$condition);
	}
        
}

