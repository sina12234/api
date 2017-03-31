<?php
/**
  * 用户API 
  * @link http://wiki.gn100.com/doku.php?id=docs:api:user
  **/
class user_teacher{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	 public $ret;
	public function __construct($inPath){
		$this->ret = new stdclass;
		$this->ret->result = new stdclass;
		$this->ret->result->code=-1;
		$this->ret->result->msg="参数不对";
		$this->ret->data="";
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
		$user_info = $user_db->getTeacherProfile($uid);
		if(empty($user_info)){
			$ret->result->code = -2; 
			$ret->result->msg= "profile do not exists"; 
			return $ret;
		}
		$ret->data=array();
        $ret->data['scopes']=array();
        if ($user_info['scopes'] & 0x01) {
            $ret->data['scopes'][]='preschool';
        } 
        if ($user_info['scopes'] & 0x02) {
            $ret->data['scopes'][]='primary';
        } 
        if ($user_info['scopes'] & 0x04) {
            $ret->data['scopes'][]='junior';
        } 
        if ($user_info['scopes'] & 0x08) {
            $ret->data['scopes'][]='senior';
        } 
        //$ret->data['roles']=array();
        //if ($user_info['roles'] & 0x01) {
            //$ret->data['roles'][]='admin';
        //} 
		$ret->data['title']	=	$user_info['title'];
		$ret->data['college']	=	$user_info['college'];
		$ret->data['years']	=	$user_info['years'];
		$ret->data['diploma']	=	$user_info['diploma'];
		$ret->data['desc']	=	$user_info['desc'];
		$ret->data['major']	=	$user_info['major'];
		$ret->data['good_subject']	=	$user_info['good_subject'];
		$ret->data['brief_desc']	=	$user_info['brief_desc'];
		return $ret;
	}
	function pageGetTeacherInfoByIds($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params=SJson::decode(utility_net::getPostData(),true);
		if(empty($params)){
			return $ret;
		}
		$user_db = new user_db;
		$user_info = $user_db->getTeacherInfoByIds($params);
		if(empty($user_info->items)){
			$ret->result->code = -2; 
			$ret->result->msg= "profile do not exists"; 
			return $ret;
		}
        $data=array();
        foreach($user_info->items as $v){
            $data[$v['pk_user']]=$v;
        }
        $ret->data=$data;
		return $ret;
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
		if(empty($inPath[3])||empty($params)){
		    $ret->result->msg="empty";
			return $ret;
		}
		//echo "<pre>";print_r($params);die;
		$uid = $inPath[3];
		$user_db = new user_db;
		$user_api = new user_api;
		$user_info = $user_db->getUser($uid);
		if(empty($user_info)){
			$ret->result->code = -2; 
			$ret->result->msg= "user do not exists"; 
			return $ret;
		}
        //echo "<pre>";print_r($params);die;
		$params->fk_user = $uid;
		if(!empty($params->good_subject)){
			$params->good_subject=implode(",",$params->good_subject);
		}
        //更新last_updated字段 
        $params->last_updated=date('Y-m-d H:i:s',time());
		$db_ret = $user_db->setTeacherProfile($uid,$params);
		$tag_id_arr=array();  
		if(!empty($params->good_subject)&& !is_array($params->good_subject)){
			$subject_arr = explode(",",$params->good_subject);
		}else{
			$subject_arr = $params->good_subject;
		}
		if($params->scopes & 0x01){ 
            $tag_id_arr[] = 4000;
        }
        if($params->scopes & 0x02){
            array_push($tag_id_arr,1000,1001,1002,1003,1004,1005,1006);
        }
        if($params->scopes & 0x04){
            array_push($tag_id_arr,2000,2001,2002,2003);
        }
        if($params->scopes & 0x08){
            array_push($tag_id_arr,3000,3001,3002,3003);
        }
		$group = SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
		if($db_ret){
			$teach =tag_api::addMappingUser($uid,$group->grade,$tag_id_arr);
			$subject =tag_api::addMappingUser($uid,$group->subject,$subject_arr);
			$ret->result->code = 0; 
		}
		return $ret;
	}
	
	function pageSetv2($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		if(empty($inPath[3])||empty($params)){
		    $ret->result->msg = "empty";
			return $ret;
		}
		$uid = (int)($inPath[3]);
		$user_db = new user_db;
		//获取用户是否存在
		$user_info = $user_db->getUser($uid);

		if(empty($user_info)){
			$ret->result->code = -2; 
			$ret->result->msg = "user do not exists"; 
			return $ret;
		}
        
        $params->last_updated = date('Y-m-d H:i:s',time());
		
		if(!empty($params->good_subject)){
			$subject_arr = explode(',',$params->good_subject);
		}
		
		$tag_id_arr = array();
		if(!empty($params->scopes)){
			if($params->scopes & 0x01){ 
				$tag_id_arr[] = 4000;
			}
			if($params->scopes & 0x02){
				array_push($tag_id_arr,1000,1001,1002,1003,1004,1005,1006);
			}
			if($params->scopes & 0x04){
				array_push($tag_id_arr,2000,2001,2002,2003);
			}
			if($params->scopes & 0x08){
				array_push($tag_id_arr,3000,3001,3002,3003);
			}
		}
	
		$group = SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
		//查看teacherprofile是否有信息
		$res_teacher_profile = $user_db->getTeacherProfile($uid);
		
		if($res_teacher_profile == false){
			$params->fk_user = $uid;
			// setTeacherProfile方法中$uid (不知有什么用??)
			$db_ret = $user_db->setTeacherProfile($uid,$params);
			if($db_ret){
				//修改年级
				if(!empty($tag_id_arr)){
					$teach   = tag_api::addMappingUser($uid,$group->grade,$tag_id_arr);
				}
				//修改科目
				if(!empty($subject_arr)){
					$subject = tag_api::addMappingUser($uid,$group->subject,$subject_arr);
				}
				$ret->result->code = 0; 
			}
		}else{
			$db_ret = $user_db->updateTeacherProfile($uid,$params);
			if($db_ret){
				//修改年级
				if(!empty($tag_id_arr)){
					$teach   = tag_api::addMappingUser($uid,$group->grade,$tag_id_arr);
				}
				//修改科目
				if(!empty($subject_arr)){
					$subject = tag_api::addMappingUser($uid,$group->subject,$subject_arr);
				}
				$ret->result->code = 0; 
			}
		}
		return $ret;
	}
	
	function pageupdateTagMapUser($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($inPath[3])||empty($params)){
		    $ret->result->msg="empty";
			return $ret;
		}
		$uid = $inPath[3];
		$user_db = new user_db;
		$user_api = new user_api;
		$user_info = $user_db->getUser($uid);
		if(empty($user_info)){
			$ret->result->code = -2; 
			$ret->result->msg= "user do not exists"; 
			return $ret;
		}
		$params->fk_user = $uid;
		if(!empty($params->good_subject)){
			$params->good_subject=implode(",",$params->good_subject);
		}
        //更新last_updated字段 
        $params->last_updated=date('Y-m-d H:i:s',time());
		$tag_id_arr=array();  
		if(!empty($params->good_subject)&& !is_array($params->good_subject)){
			$subject_arr = explode(",",$params->good_subject);
		}else{
			$subject_arr = $params->good_subject;
		}
		
		$group = SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
		
			//$teach =tag_api::addMappingUser($uid,$group->grade,$tag_id_arr);
			$subject =tag_api::addMappingUser($uid,$group->subject,$subject_arr);
			$ret->result->code = 0; 
		
		return $ret;
	}
    function pageSetTeacherSort($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            return $ret;
        }
//		define("DEBUG",true);
        $uid = $inPath[3];
        $org_id = empty($params->oid)?"0":$params->oid;
		$org_info = user_db::getOrgUser($uid);
		if($org_info["org_id"]!=$org_id){
	//	$user_info = user_db::getUser($uid);
     //   if(empty($user_info)){
            $ret->result->code = -2;
            $ret->result->msg= "user do not exists";
            return $ret;
        }
        $sort = empty($params->sort)?"0":$params->sort;
		$data = array("sort"=>$sort);
        $update_sort_ret = user_db::UpdateUser($uid,$data);
        if($update_sort_ret=== false){
            $ret->result->code = -2;
            $ret->result->msg = "fail update";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        return $ret;
    }
	function pageGetUserOrg($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($inPath[3])){
			return $ret;
		}
		$uid = $inPath[3];
		$user_db = new user_db;
		$user_info = $user_db->getOrgUser($uid);
		if(empty($user_info)){
			$ret->result->code = -2; 
			$ret->result->msg= "profile do not exists"; 
			return $ret;
		}
		$ret->data=array();
		$ret->data = $user_info;
		return $ret;
	}
	function pageGetTeacherSpecial($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($inPath[3])||empty($inPath[4])){
		    $ret->result->code=-2;
		    $ret->result->msg="error";
			return $ret;
		}
		$oid = $inPath[3];
		$uid = $inPath[4];
		$user_db = new user_db;
		$special = $user_db->getTeacherSpecial($oid,$uid);
		if(empty($special)){
			$ret->result->code = -2; 
			$ret->result->msg= "no data"; 
			return $ret;
		}
        //兼容老版本role
        if ($special['user_role']&0x01||$special['role']==1||$special['role']==0) {
            $special['roles'][]='general';
        } 
        if ($special['user_role'] & 0x02) {
            $special['roles'][]='assistant';
        } 
        if ($special['user_role']&0x04||$special['role']==2) {
            $special['roles'][]='admin';
        } 
        
		$ret->data=$special;
		return $ret;
	}
    public function pageSetTeacherSpecial($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
        $params = json_decode(utility_net::getPostData());
        if (empty($inPath[3]) || empty($inPath[4]) || empty($params)) {
		    $ret->result->code=-2;
		    $ret->result->msg="invalid parameter";
            return $ret;
        }
        $user_db = new user_db;
        //更新last_updated字段
        $params->last_updated=date('Y-m-d H:i:s',time());
        $db_ret = $user_db->setOrgUserData((int)$inPath[3],(int)$inPath[4],$params);
        if ($db_ret===false) {
			$ret->result->code = -2; 
			$ret->result->msg= "update error"; 
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!"; 
        }
        return $ret;
        
    }
	//按照课程取班主任老师
	public function pageGetHeaderTeacher($courseId){
		$params = json_decode(utility_net::getPostData());
		if(empty($params->courseId)){
			 return $this->ret;
		}
		$this->ret->result->code=0;
		$this->ret->result->msg="成功";
		$this->ret->data = course_db_courseClassDao::HeaderTeacher($params->courseId);
		return $this->ret;
	}

}
