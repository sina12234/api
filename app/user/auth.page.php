<?php
class user_auth{

	function pageVerify($inPath){
		$ret = new stdclass;
		$ret->result=array("code"=>0,"msg"=>"");
		$ret->data=array("uid"=>0,"name"=>"");
		$params=SJson::decode(utility_net::getPostData());
		if((empty($params->uid) && empty($params->uname)) || empty($params->password) || empty($params->login_ip)){
			$ret->result['code']=-1;
			return $ret;
		}
		$user_db = new user_db;
		$user_api = new user_api;
		$xplatform_api = new xplatform_api;
		if(!empty($params->uid)){
			$user_id = $params->uid;
		}else{
			$user_id=0;;
			if(utility_valid::mobile($params->uname)){
				//手机号登录
				$user_id=$user_db->getUserIDByMobile($params->uname);

				$retgetUser = user_db::getUser($user_id);
				if($retgetUser["verify_status"]==0){
					$dataUp = array("verify_status"=>1,);
					$retUpdateuser = user_db::updateUser($user_id,$dataUp);
				}
			}elseif(utility_valid::email($params->uname,false)){
				//邮箱登录
				$user_id=$user_db->getUserIDByEmail($params->uname);

				$retgetUser = user_db::getUser($user_id);
				if($retgetUser["verify_status"]==0){
					$dataUp = array("verify_status"=>1,);
					$retUpdateuser = user_db::updateUser($user_id,$dataUp);
				}
			}else{
				$ret->result['code']=-1;
				$ret->result['msg']="手机号不正确";
				return $ret;
			}
			if(empty($user_id)){
				$ret->result['code']=-3;
				$ret->result['msg']="用户不存在!";
				//TODO 加入X平台逻辑 1/2
				if(defined("XPLATFROM_LOGIN")){
					if($xplatform_api->login2($params->uname,$params->password,$ret)===true){
						//云平台登录成功，注册成为用户
						if(!empty($ret->body->nickName)){
							$user_info = new stdclass;
							$user_info->name = $ret->body->nickName;
							$user_info->mobile = $params->uname;
							$user_info->password= $params->password;
							$user_info->source=2;//2 X平台导入  https://wiki.gn100.com/doku.php?id=docs:db:user
							$r = $user_api->addUser($user_info);
							if(!empty($r->data['uid'])){
								$user_id = $r->data['uid'];
							}
						}
					}
				}else{
					return $ret;
				}
			}
		}
		$user = $user_db->getUser($user_id);
		if(empty($user)){
			$ret->result['code']=-3;
			$ret->result['msg']="用户不存在!";
			return $ret;
		}
		if(defined("XPLATFROM_LOGIN") && $xplatform_api->login2($user['mobile'],$params->password,$r)===true){
			user_db::updateUser($user_id,array("password"=>user_api::encryptPassword($params->password)));
			/* $r->body object
			{
				"nickName": "",
					"avatar": "",
					"refreshToken": "",
					"refreshTokenExpire": ,
					"onceToken": "",
					"accountId": 123457
			}
			 */
			$ret->data['xplatform']=array(
				"refreshToken"=>$r->body->refreshToken,
				"accountId"=>$r->body->accountId,
			);
		}elseif($user['password'] == user_api::encryptPassword($params->password)){
		}else{
			$ret->result['code']=-2;
			$ret->result['msg']="密码不正确";
			return $ret;
		}
        //记录登录时间
        user_db::updateUser($user_id,array("last_login"=>date('Y-m-d H:i:s')));
		$ret->data['uid']=$user_id;
		$ret->data['name']=$user['name'];
		return $ret;
	}
	function pageCheck($inPath){
		$ret = new stdclass;
		$ret->result=array("code"=>0,"msg"=>"");
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uname) && empty($params->parterner)){
			$ret->result['code']=-1;
			return $ret;
		}
		$user_db = new user_db;
		$user_id=0;
		if(!empty($params->uname)){
			if(utility_valid::mobile($params->uname)){
				//手机号登录
				//$user_id=$user_db->getUserIDByMobile($params->uname);
				$user_id = $user_db->getUserIDByMobileFromMaster($params->uname);

				$retgetUser = user_db::getUser($user_id);
				if($retgetUser["verify_status"]==0){
					$dataUp = array("verify_status"=>1,);
					$retUpdateuser = user_db::updateUser($user_id,$dataUp);
				}
			}elseif(utility_valid::email($params->uname,false)){
				//邮箱登录
				$user_id=$user_db->getUserIDByEmail($params->uname);

				$retgetUser = user_db::getUser($user_id);
				if($retgetUser["verify_status"]==0){
					$dataUp = array("verify_status"=>1,);
					$retUpdateuser = user_db::updateUser($user_id,$dataUp);
				}
			}else{
				$ret->result['code']=-1;
				$ret->result['msg']="手机号或者邮箱地址不正确";
				return $ret;
			}
		}
		if(empty($user_id)){
			$ret->result['code']=-3;
			$ret->result['msg']="用户不存在!";
			return $ret;
		}
		$user = $user_db->getUser($user_id);
		$ret->data=array("uid"=>$user_id,"name"=>$user['name']);
		return $ret;
	}
    //机构管理员、教师、助教权限判断
    function pageUserRole($inPath){
		$ret = new stdclass;
        $ret->result=new stdClass;
        $ret->result->code=-1;
        $ret->result->msg='';
		$params=SJson::decode(utility_net::getPostData());
        if(empty($params->owner)||empty($params->uid)){
            $ret->result->code=-2;
            $ret->result->msg='参数错误';
            return $ret;
        }
        $user_db=new user_db; 
        //查询用户信息
         
        //查询机构信息
        $org=$user_db->getOrgByUid($params->owner);
        if($org===false){
            $ret->result->code=-3;
            $ret->result->msg='无机构信息';
            return $ret;
        }
        $roles=array();
        //判断是否为机构的创建者 
        if($params->owner==$params->uid&&$org['status']>=1){
            $roles[]='owner';
        }
        //查询教师在机构下的设置信息
        $special=$user_db->getTeacherSpecial($org['oid'],$params->uid); 
        if(!empty($special)&&$special['status']==1&&($special['role']==2 || $special['user_role']&0x04)){
            $roles[]='admin';
        }
        if(!empty($special)&&$special['status']==1&&($special['role']==1 || $special['user_role']&0x01)){
            $roles[]='general';
        }
        if(!empty($special)&&$special['status']==1&&($special['user_role']&0x02)){
            $roles[]='assistant';
        }
        $ret->result->code=0;
        $ret->roles=$roles;
        return $ret;
    }

    public function pageOrgRole()
    {
        $params  = SJson::decode(utility_net::getPostData(), true);
        $ownerId = isset($params['ownerId']) && (int)$params['ownerId'] ? (int)$params['ownerId'] : 0;
        $userId  = isset($params['userId']) && (int)$params['userId'] ? (int)$params['userId'] : 0;
        if (!$ownerId || !$userId) return api_func::setMsg(1000);

        $userDb  = new user_db;
        $orgInfo = $userDb->getOrgByUid($params->owner);
        if (empty($orgInfo)) return api_func::setMsg(3002);

        //判断是否为机构的创建者
        $orgInfo['isOwner'] = false; //default false
        if ($ownerId == $userId && $orgInfo['status'] >= 1) {
            $orgInfo['isOwner'] = true;
        }

        return api_func::setData($orgInfo);
    }
}
