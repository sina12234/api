<?php
class user_organization{
	public function setResult($data='',$code=0,$msg='success'){
	 	$ret = new stdclass;
	 	$ret->result = new stdclass;
		$ret->result->code =$code;
		$ret->result->data =$data;
		$ret->result->msg= $msg;
		return $ret;    
	}   
	public function pageOrglist($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
	    $params = SJson::decode(utility_net::getPostData());
		$uid = $inPath[3];
		//page 页数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
		//length 每页显示数
		if(empty($inPath[5])){$length = 100;}else{$length = $inPath[5];}
		$user_api = new user_api;
		$listorg= $user_api->getlistorg($uid,$page,$length);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$listorg1 = SJson::decode($listorg);
		//	return $courselist1->data[0];
		return $listorg1;
	}
	
	public function pageOrgMgrList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		//page 页数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
		//length 每页显示数
		if(empty($inPath[5])){$length = 20;}else{$length = $inPath[5];}

		$user_api = new user_api;
		//$courselist = $course_api->getpfcourselist($page,$length,$fee);
		//$listorg= $user_api->listorg($uid,$page,$length);
		$listorg = $user_api->getchecklistorginfo($uid,$inPath[3],$length);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$listorg1 = SJson::decode($listorg);
		//	return $courselist1->data[0];
		return $listorg1;
	}
	
	public function pageresultOrgList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
	    $params = SJson::decode(utility_net::getPostData());
		//$uid = !empty($inPath[3]) ? $inPath[3] : '';
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[5])){$length =20;}else{$length = $inPath[5];}
		
		$user_api = new user_api;
		
		$listorg = $user_api->getlistorginfo($page,$params,$length);
		
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$listorg1 = SJson::decode($listorg);
		return $listorg1;
	}
	/*
     * 统计按地区机构总数
     * @author zhengtianlong
     */
    public function pageGetCount($inPath)
    {
        $ret = new stdclass;
        if(!isset($inPath[3]))
        {
            $ret->result = array('code'=>-1,'msg'=>'cityId id error');
            return $ret;
        }
        $user_api = new user_api;
        $num = $user_api->getOrgNum($inPath[3]);
        return $num[0];
    }

    public function pageGetOrgByCity($inPath)
    {
        $ret = new stdclass;
        if(!isset($inPath[3]))
        {
            $ret->result = array('code'=>-1,'msg'=>'cityId id error');
            return $ret;
        }
		
		$page   = !empty($inPath[4])?$inPath[4]:1;
		$length = !empty($inPath[5])?$inPath[5]:-1;
        $user_api = new user_api;
        $res = $user_api->getOrgByCity($inPath[3],$page,$length);
		$ret->data = $res;
		if(empty($res))
		{
			$ret->result = array('code'=>-2,'msg'=>'data is error');
            return $ret;
		}
		
		return $ret;
	}
	
	public function pageGetOrgByCid($inPath)
    {
        $ret = new stdclass;
        if(!isset($inPath[3]))
        {
            $ret->result = array('code'=>-1,'msg'=>'cityId id error');
            return $ret;
        }
		
		$page   = !empty($inPath[4])?$inPath[4]:1;
		$length = !empty($inPath[5])?$inPath[5]:20;
        
		$user_db = new user_db;
        $res = $user_db->getOrgByCid($inPath[3],$page,$length);
		if(empty($res->items))
		{
			$ret->result = array('code'=>-2,'msg'=>'data is error');
            return $ret;
		}
		$ret->page = $res->page;
		$ret->pageSize  = $res->pageSize;
		$ret->totalPage = $res->totalPage;
		$ret->totalSize = $res->totalSize;
		$ret->data = $res->items;
		return $ret;
	}
	
	public function pageorgAboutProfileInfo($inPath){
        $ret = new stdclass;
        $ownerId = !empty($inPath[3]) ? (int)$inPath[3] : 0;
        $res = user_db::orgAboutProfileInfo($ownerId);
		$ret->data = $res;
		if(empty($res)){
			$ret->result = array('code'=>-2,'msg'=>'data is error');
            return $ret;
		}
		return $ret;
	}
	public function pagegetOrgAllName(){
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
	
	public function pageGet($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$oid = $inPath[3];
		$user_api = new user_api;
		$listorg = $user_api->getorg($oid);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listorg;
	}
	public function pageGetByUid($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$listorg = $user_api->getOrgByUid($uid);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listorg;
	}
	
	public function pagegetCheckListByUid($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$listorg = $user_api->getOrgByUidTmp($uid);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listorg;
	}

	public function pagedelCheckInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3])){
			$ret->result->code=-1;
			return $ret;
		}
		$bid = $inPath[3];
		$data = array();
		//首先删除
		$org = user_db::delCheckInfo($bid);
		if($org==false){
			$ret->result->code=-3;
			return $ret;
		}
		$ret->result->code=0;
		return $ret;
	}	
	public function pageGetByUidTmp($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$listorg = $user_api->getOrgByUidTmp($uid);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listorg;
	}
	public function pageGetOrgShowInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
        $profile_info = array();
		$profile_info = user_db::getMgrOrgVerify($uid);
		$verify = user_db::getmgrOrgVerifySubdomain($uid);
		$result = user_db::getOrgProfileByUid($profile_info['fk_user_owner']);
		$user_info = user_db::getUser($profile_info['fk_user_owner']);
		$profile_info['user_name'] = !empty($user_info['name']) ? $user_info['name'] : '';
		$profile_info['mobile']    = !empty($result['mobile']) ? $result['mobile'] : '';
		$profile_info['province']  = !empty($result['province']) ? $result['province'] : '';
		$profile_info['city']      = !empty($result['city']) ? $result['city'] : '';
		$profile_info['scopes']      = $result['scopes'];
		$profile_info['email']      = $verify['email'];
		return $profile_info;
	}
	
	
	public function pageGetOrgProArr($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$listorg = $user_api->getOrgByUidTmp($uid);

		$str = $listorg->data['province'].",".$listorg->data['city'].",".$listorg->data['county'];
		$condition = "pk_region in({$str})";
		$r_db = new region_db;
		$pro = $r_db->listRegion($condition);
		/*$profile_info = new stdclass;
		$profile_info = (object)$result;
		$profile_info->name = $user_info['name'];
		
		if( $profile_info === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}*/
		return $pro;
	}	

	public function  pagesearchData($inPath){
	    $user_api = new user_api;
	    $tmp = SJson::decode(utility_net::getPostData());
		$params = $tmp;
		$page=1;
		$limit=5;
		$ret = user_db::getsearchDataList($page,$limit,$params,$orby='');
		return $ret;
	}

	public function pageUpdateOrg($inPath){
		$params= SJson::decode(utility_net::getPostData());
		if(empty($params)){
		 return $this->setResult('',-1,'param is empty');
		}
		$sid=$inPath[3];
		if(empty($sid)){
		 return $this->setResult('',-1,'sid is empty');
		}
		$par 	  = array(
					"verify_status"=>$params->verify_status,
					"descript"=>$params->descript,
					"last_updated"=>date("Y-m-d H:i:s")
				  );
		$status   = array(
					"status"=>$params->verify_status,
					"last_updated"=>date("Y-m-d H:i:s")
				  );
		$template = array();		  
		$template = array(
						'title'=>"全部课程",
						'fk_user_owner'=>!empty($params->fk_user) ? (int)$params->fk_user : 0,
						'row_count'=>8,
						'recommend'=>1,
						'query_str'=>"course_type:1,fee_type:0",
						'order_by'=>"create_time:desc",
						'sort'=>1,
						'course_ids'=>"",
						'create_time' => date('Y-m-d H:i:s'),
						'last_updated'=>date('Y-m-d H:i:s'),
						'set_url'=>'',
						"type"=>0,	
					);
		$updateRet=user_db::updateorginfo($sid,$par);
		//$orgSubdomain = user_db::getmgrOrgVerifySubdomain($sid);
		if($updateRet==1){
		$organzi = user_db::updatestatus($sid,$status);
		}elseif($updateRet=='0'){
		$organzi = user_db::updatestatus($sid,$status);
		}
		$orgSubdomain = user_db::getmgrOrgVerifySubdomain($sid);
		$condition= array("subdomain"=>$orgSubdomain['subdomain'],"status"=>1);
		$subInfo = user_db::getSubdomainByNameIsExist($condition);
		$user= array();
		
		if($orgSubdomain['verify_status']=='1' || $orgSubdomain['verify_status']=='2' || $orgSubdomain['verify_status']=='0'){
			
			//通过
			if(!empty($subInfo['subdomain'])){
				return $this->setResult('',-3,'subdomain is exist');
			}else{
                $status = array("subdomain"=>$orgSubdomain['subdomain']);
                $tmpStatus =user_db::getSubdomainByNameIsExist($status);
                if(!empty($tmpStatus)){
                    $subResult = user_db::updateSubdomainStatus(array("status"=>1),array("subdomain"=>$tmpStatus['subdomain'],"fk_user"=>$orgSubdomain['fk_user_owner']));
                }else{
                    $data['subdomain'] = $orgSubdomain['subdomain'];
                    $data['fk_user'] = $orgSubdomain['fk_user_owner'];
                    $data['status'] = 1;
                    $result = user_db::addmgrSubmain($data);
                }
				
				
			}
			
			$user['type']=7;
			$fk_user= $orgSubdomain['fk_user_owner'];
			$user_info = user_db::updateUserTypeByInfo($fk_user,$user);
			$org_info = user_db::getmgrOrgInfo($sid,$fk_user);
			if(empty($org_info)){
				$org = array();
				$org['fk_org']=$sid;
				$org['fk_user']=$fk_user;
				$org['status']=1;
				$org['user_role']=1;
				$org['create_time']=date("Y-m-d H:i:s");
				$add = user_db::addOrganizationUser($org);
			}
			//通过或者通过不显示添加默认模板
			$fkUserData = user_db::getTemplateCheck($params->fk_user);
			if(empty($fkUserData->items)){
				$tid = user_db::addOrgTemplate($template);
				if($tid > 0){
					$template['fk_template'] = $tid;
					user_db::addTemplateData($template);
				}
			}
		}else{
			//不通过
			if(!empty($subInfo['subdomain'])){
				$result = user_db::updateSubdomainStatus(array("status"=>-1),array("subdomain"=>$orgSubdomain['subdomain'],"fk_user"=>$orgSubdomain['fk_user_owner']));
				$UserRoleArr= array("fk_org"=>$orgSubdomain['fk_org'],"fk_user"=>$orgSubdomain['fk_user_owner']);
				$del = user_db::delMgrOrgUserRole($UserRoleArr);
				$user['type']=1;
				$fk_user= $orgSubdomain['fk_user_owner'];
				$user_info = user_db::updateUserTypeByInfo($fk_user,$user);
				return $this->setResult($user_info,200,'success');
			}
		}
		if(($updateRet===false) || ($organzi===false)){
			return $this->setResult('',-3,'update faild');
		}else{
			return $this->setResult($updateRet);
			
		}
	}

	public function pagegetOrgSetHotType($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$orgInfo = $user_api->getOrgSetHotType($uid);
		if($orgInfo === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $orgInfo;
	}
	
	public function pagegetOrgTagInfo($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$orgId = !empty($params->oid) ? (int)$params->oid : 0;
		$orgInfo = user_db_organizationTagDao::getOrgTagByOrgId($orgId);
		if($orgInfo->items === false){
			return $this->setResult('',-100,'the data is not found');
		}
		if(!empty($orgInfo->items)){
			foreach($orgInfo->items as $k=>$v){
				$idArr[] = !empty($v['fk_tag']) ? $v['fk_tag'] : 0;
			}
			$idStr = implode(",",$idArr);
			$tagInfo = tag_db::getTagNameInfo($idStr);
		}
		if(!empty($tagInfo->items)){
			return $this->setResult($tagInfo->items);
		}else{
			return $this->setResult('',-100,'the data is not found');
		}
	}
	public function pageUpdatehotType($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$bid=$inPath[3];
		if(empty($params)){
		 return $this->setResult('',-1,'param is empty');
		}
		
		if(empty($bid)){
		 return $this->setResult('',-1,'bid is empty');
		}

		$update_ret=user_db::updatehotType($bid,$params);
		if($update_ret==0 || $update_ret==1){
			return $this->setResult($update_ret);
		}else{
			return $this->setResult('',-3,'update faild');
		}
	}	

	public function pageUpdateOrgCheck($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$bid=$inPath[3];
		if(empty($params)){
		 return $this->setResult('',-1,'param is empty');
		}
		
		if(empty($bid)){
		 return $this->setResult('',-1,'bid is empty');
		}

		$sid=$inPath[3];
		//$par= " status='".$params->verify_status."'";
		$par = array("tmp_status"=>$params->tmp_status,"desc"=>$params->error_msg);
		$update_ret=user_db::updateOrgProfileTmp($bid,$params);
		
		if($update_ret===false){
			return $this->setResult('',-3,'update faild');
		}else{
			return $this->setResult($update_ret);
			
		}
	}


	public function pageUpdateOrgCheckData($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$bid=$inPath[3];
		if(empty($params)){
		 return $this->setResult('',-1,'param is empty');
		}
		
		if(empty($bid)){
		 return $this->setResult('',-1,'bid is empty');
		}

		$update_ret=user_db::updateOrgProfile($bid,$params);
		
		if($update_ret===false){
			return $this->setResult('',-3,'update faild');
		}else{
			return $this->setResult($update_ret);
			
		}
	}


	public function pageUpdateOrgCheckArr($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$bid=$inPath[3];
		if(empty($params)){
		 return $this->setResult('',-1,'param is empty');
		}
		
		if(empty($bid)){
		 return $this->setResult('',-1,'bid is empty');
		}
		$user_api = new user_api;
		$listorg = $user_api->getorgByUid($bid);
		$profile_arr = array(
						"name"=>$params->name,
						"thumb_big"=>$params->thumb_big,
						"thumb_med"=>$params->thumb_med,
						"thumb_small"=>$params->thumb_small,
						"last_updated"=>date("Y-m-d H:i:s"),
						"desc"=>$params->desc
						);
		$p_arr = array(
					"subname"=>$params->subname,
					"scopes"=>$params->scopes,
					"announce"=>$params->announce,
					"thumb_nav"=>$params->thumb_nav,
					"company"=>$params->company,
					"province"=>$params->province,
					"city"=>$params->city,
					"county"=>$params->county,
					"address"=>$params->address,
					"areacode"=>$params->areacode,
					"hotline"=>$params->hotline,
					"email"=>$params->email,
					"last_updated"=>$params->last_updated
					);
		
		$add_data = array(
					"fk_org"=>$listorg->data['oid'],
					"fk_user_owner"=>$bid,
					"subname"=>$params->subname,
					"scopes"=>$params->scopes,
					"announce"=>$params->announce,
					"thumb_nav"=>$params->thumb_nav,
					"company"=>$params->company,
					"province"=>$params->province,
					"city"=>$params->city,
					"county"=>$params->county,
					"address"=>$params->address,
					"areacode"=>$params->areacode,
					"hotline"=>$params->hotline,
					"email"=>$params->email,
					"last_updated"=>$params->last_updated
					);
		
		$user_db=new user_db;
		$update_ret = $user_db->updateorg($bid,$profile_arr);
		$profile = user_db::getOrgProfileByUid($bid);
		if($profile===false){
			$tmp = user_db::addOrgProfile($add_data);
		}else{
			$p_data = $user_db->updateOrgProfile($bid,$p_arr);
			
		}
		
		if(($update_ret===false) || ($p_data===false) ||($tmp !=0)){
			return $this->setResult('',-3,'update faild');
		}else{
			return $this->setResult($update_ret);
			
		}
	}
	
	public function pageUpdateSubInfo($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$bid=$inPath[3];
		if(empty($params)){
		 return $this->setResult('',-1,'param is empty');
		}
		if(empty($bid)){
		 return $this->setResult('',-1,'bid is empty');
		}
        $user_db = new user_db;
		$data = array();
		$orgInfo = user_db::getorg($bid);
		$data['fk_org'] = $bid;
		$data['fk_user_owner'] = !empty($orgInfo['fk_user_owner']) ? $orgInfo['fk_user_owner'] : 0;
		$data['subname']= !empty($params->subname) ? $params->subname : '';
		$data['company']= !empty($params->company) ? $params->company : '';
		$data['email']= !empty($params->email) ? $params->email : '';
		$data['areacode']= !empty($params->areacode) ? $params->areacode : '';
		$data['hotline']= !empty($params->hotline) ? $params->hotline : '';
		$data['address']= !empty($params->address) ? $params->address : '';
		$data['last_updated']= date("Y-m-d H:i:s");
		$getProfile = user_db::getMgrOrgProfileByInfo($bid);
		if($getProfile===false){
			$addProfile = user_db::addOrgProfile($data);
		}else{
			$updateProfile=$user_db->updateSubInfo($bid,$params);
		}
		$getverify = user_db::getmgrOrgVerifySubdomain($bid);
		
		$verify = array();
		$verify['fk_org']=$bid;
		$verify['fk_user_owner']=!empty($orgInfo['fk_user_owner']) ? $orgInfo['fk_user_owner'] : 0;
		$verify['email']=!empty($params->email) ? $params->email : '';
		//$verify['last_updated']=date("Y-m-d H:i:s");
		
		if($getverify===false){
			$addVerify = user_db::addOrgInfoVerify($verify);
		}else{
			$updateVerify = user_db::updateorginfo($bid,$verify);
		}
		return $this->setResult($orgInfo);
			
		
	}
	 public function pageGetOrgInfoByUidArr($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->message = '';

        $uidArr = SJson::decode(utility_net::getPostData(),true);
        if (!$uidArr) {
            $ret->result->code = -1006;
            $ret->result->message = 'params error';

            return $ret;
        }
        $orgInfoList = user_db::getOrginfoByUidArr($uidArr);
        if (!$orgInfoList) {
            $ret->result->code = -1007;
            $ret->result->message = 'get data list failed';

            return $ret;
        }

        return $orgInfoList;
    }
    public function pagegetOrgSubdomainName($inPath){
        //$params= SJson::decode(utility_net::getPostData());
		$fk_user= !empty($inPath[3]) ? $inPath[3] : ''; 
        $info = user_db::getmgrSubmainName($fk_user);
		$subdomain = !empty($info) ? $info : '';
        return $subdomain;
    }
    public function pageGetOrgInfoByOidArr($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->message = '';

        $params = SJson::decode(utility_net::getPostData(),true);
        if (empty($params['oidArr'])) {
            $ret->result->code = -1006;
            $ret->result->message = 'params error';

            return $ret;
        }
		$join = true;
		if (isset($params['join']) && $params['join'] == false) $join = false;
        $orgInfoList = user_db::getOrgInfoByOidArr($params['oidArr'], $join);
        if ($orgInfoList===false) {
            $ret->result->code = -1007;
            $ret->result->message = 'get data list failed';
            return $ret;
        }

        return $orgInfoList;
    }

	public function pageGetOrgAbout($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$db_ret = $user_api->getOrgAbout($uid);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $db_ret;
	}
	public function pagegetOrgByOwner($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$orgInfo = $user_api->getOrgByOwner($uid);
		if($orgInfo === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $orgInfo;
	}
	public function pagegetOrgNameInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$orgInfo = $user_api->getOrgNameInfo($uid);
		if($orgInfo === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $orgInfo;
	}
	public function pagegetOrgByOwnerTmp($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_api = new user_api;
		$orgInfo = $user_api->getOrgByOwnerTmp($uid);
		if($orgInfo === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $orgInfo;
	}
	public function pageGetByTeacher($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
	//	$user_db= new user_db;
		$listorg = user_db::getOrgByTeacher($uid);
		if($listorg === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return array("code"=>0,"data"=>$listorg);
	}
	public function pageSet($inPath){

		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}

		$uid = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData(),true);
		//		define("DEBUG",true);
		$user_api = new user_api;
		$user_api->updateorg($uid,$params);
		if($user_api === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	
	public function pagesetTmp($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}
		$user_db = new user_db;
		$uid = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData(),true);
		$data = array();
	if(empty($params["name"])){
			$ret->result->code = -3;
			$ret->result->msg = "the name is empty!";
			return $ret;
		}else{
			$data = $params;
		}
		$orginfo = $user_db::getOrgByOwner($inPath[3]);
		$data['fk_org'] = $orginfo['oid'];

		$user_api = new user_api;
		$data['fk_user_owner'] = $uid;
		$org_info =$user_db::getOrgProfileByUidTmp($uid);
		if($org_info === false){
		    $user_db::addOrgProfileTmp($data);
			$ret->result->code = 1;
			$ret->result->msg = "add is success";
		}else{
			$user_db::updateOrgProfileTmp($uid,$data);
			$ret->result->code = 0;
			$ret->result->msg =" update is success";
		}
		return $ret;
	}


	public function pagesetInfoStatus($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "dataa inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}

		$uid = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData(),true);
		//		define("DEBUG",true);
		$data = array();
	if(!isset($params["tmp_status"])){
			$ret->result->code = -3;
			$ret->result->msg = "the tmp_status is empty!";
			return $ret;
		}else{
			$data = $params;
		}
		$user_api = new user_api;
		$user_db = new user_db;
		$org_info =$user_db::getOrgProfileByUidInfo($uid,$data);
		if($org_info === false){
			$ret->result->code = 1;
			$ret->result->msg = " is error";
		}else{
			$user_db::updateOrgProfileTmp($uid,$data);
			$ret->result->code = 0;
			$ret->result->msg =" update is success";
			$ret->result->data = $org_info;
		}
		return $ret;
	}
	public function pagesetOrgProfile($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData(),true);
		$data = array();
		if(empty($params['subname'])){
			$ret->result->code = -3;
			$ret->result->msg = "the subname is empty!";
			return $ret;
		}else{
			$data = $params;
		}
		$user_api = new user_api;
		$db_ret=$user_api->updateOrgProfile((int)$inPath[3],$data);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagesetOrgProfileRelateInfo($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->result->code = -2;
            $ret->result->msg= "update inpath is empty!";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData(),true);
        $data = array();
        if(empty($params['subname'])){
            $ret->result->code = -3;
            $ret->result->msg = "the subname is empty!";
            return $ret;
        }else{
            $data = $params;
        }
        $user_api = new user_api;
        $db_ret=$user_api->updateOrgProfileTmp((int)$inPath[3],$data);
        if($db_ret === false){
            $ret->result->code = -2;
            $ret->result->msg = "fail update";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        return $ret;
    }
    public function pagesetOrgProfileTmp($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData(),true);
		$data = array();
		if(empty($params['company'])){
			$ret->result->code = -3;
			$ret->result->msg = "the company is empty!";
			return $ret;
		}else{
			$data = $params;
		}
		$user_api = new user_api;
		$db_ret=$user_api->updateOrgProfileTmp((int)$inPath[3],$data);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
   
	public function pageaddOrgSlide($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "inpath is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData());
        $data=array();
        $data['fk_user']=$inPath[3];
        $data['slide_url']=!empty($params->slide_url)?$params->slide_url:'';
        $data['slide_link']=!empty($params->slide_link)?$params->slide_link:'';
        $data['slide_title']=!empty($params->slide_title)?$params->slide_title:'';
        $data['rgb']=!empty($params->rgb)?$params->rgb:'';
		$user_api = new user_api;
		$db_ret=$user_api->addOrgSlide($data);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageupdateOrgSlide($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData(),true);
		$user_api = new user_api;
		$db_ret=$user_api->updateOrgSlide((int)$inPath[3],$params);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagedelOrgSlide($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$user_api = new user_api;
		$db_ret=$user_api->delOrgSlide((int)$inPath[3]);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageupdateOrgLogo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData(),true);
		$user_api = new user_api;
		$db_ret=$user_api->updateOrgLogoTmp((int)$inPath[3],$params);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagegetOrgSlidelist($inPath){
		$ret = new stdclass;
		$uid = empty($inPath[3])?0:$inPath[3];
		//page 页数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
		//length 每页显示数
		if(empty($inPath[5])||!is_numeric($inPath[5])){$length = 4;}else{$length = $inPath[5];}
		$user_api = new user_api;
		$listorg= $user_api->getOrgSlideList($uid,$page,$length);
		if($listorg === false){
			$ret->result =  new stdclass;
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$ret->data=$listorg->items;
		return $ret;
	}
	public function pagegetOrgSlide($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$user_api = new user_api;
		$orgInfo = $user_api->getOrgSlide((int)$inPath[3]);
		if($orgInfo === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $orgInfo;
	}
	public function pageSetLOGO($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return $ret;
		}

		$uid = (int)$inPath[3];
		$params = SJson::decode(utility_net::getPostData(),true);
		//		define("DEBUG",true);
		$data = array();
		$data = $params;
		$user_api = new user_api;
		$user_api->updateorg($uid,$data);
		if($user_api === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagegetOrgLogo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
        if(empty($inPath[3])){
		    $ret->result->code = -2;
	    	$ret->result->msg= "error!";
            return $ret;
        }
		$user_api = new user_api;
		$orgInfo = $user_api->getOrgByOwner((int)$inPath[3]);
		if($orgInfo === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $orgInfo;
	}
	/**
	 * 机构用户列表
	 */
	public function pageUserlist($inPath){
		$ret = new stdclass;
		$oid = empty($inPath[3])?0:$inPath[3];
		//page 页数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
		//length 每页显示数
		if(empty($inPath[5])||!is_numeric($inPath[5])){$length = 4;}else{$length = $inPath[5];}

		$user_api = new user_api;
		//$all=0;
		//$star=0;
		$params = SJson::decode(utility_net::getPostData());
		/*if(!empty($params->all)){
			$all=1;
		}*/
		/*if(!empty($params->is_star)){
			$star=1;
		}*/
		$listorg= $user_api->listOrgUser($oid,$params->all,$params->is_star,$page,$length);
		if($listorg === false){
			$ret->result =  new stdclass;
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$ret->data=$listorg->items;
		return $ret;
	}

	public function pagegetTeacherRealName($inPath){
		$ret = new stdclass;
		$uidArr = empty($inPath[3])?0:$inPath[3];
		$user_db = new user_db;
		$params = SJson::decode(utility_net::getPostData());
		$listorg= $user_db::listProfilesByUserIds($uidArr);
		if($listorg === false){
			$ret->result =  new stdclass;
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$ret->data=$listorg->items;
		return $ret;
	}
	public function pagedataOrgTeacherCount($inPath){
		$ret = new stdclass;
		$oid = !empty($inPath[3]) ? (int)$inPath[3] : 0;
		$listorg= user_db::dataOrgTeacherCount($oid);
		if($listorg===false){
			$ret->result =  new stdclass;
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$ret->data = $listorg;
		return $ret;
	}
    public function pageGetOrgUserList($inPath)
    {
        $page = isset($inPath[4]) && (int)($inPath[4]) ? (int)($inPath[4]) : 1;
        $length = isset($inPath[5]) && (int)($inPath[5]) ? (int)($inPath[5]) : 100;
        $ret = new stdclass;

        $params = SJson::decode(utility_net::getPostData());
        $condition = $orderBy = '';
        if (!empty($params)) {
            $condition = $params->condition;
            $orderBy = $params->orderBy;
        }

        $user_db = new user_db;
        $orgUser = $user_db->getOrgUserListByOid($condition, 'fk_user', $orderBy, $page, $length);
        if (empty($orgUser)) {
            $ret->code = -2001;
            $ret->message = 'get orgUser data failed';

            return $ret;
        }
        $uidArr = $list = $userRes = $result = array();
        foreach ($orgUser->items as $v) {
             $uidArr[] = (int)$v['fk_user'];
             $list[] = $v;
        }

        $uidStr = implode(',', $uidArr);
        $userCond = "pk_user IN ($uidStr)";
        $userItem = array('pk_user','name','real_name','thumb_big');
        $userList = $user_db->getUserList($userCond, $userItem);
        if (!empty($userList->items)) {
            foreach ($userList->items as $user) {
                $offset = array_keys($uidArr, $user['pk_user']);
                $userRes[$user['pk_user']] = array_merge($list[$offset[0]], $user);
            }
        }

        /*$userProfile=$user_db->getUserProfileByUidArr($uidArr);
        if (!empty($userProfile->items)) {
            foreach ($userProfile->items as $profile) {
                if (!empty($userRes[$profile['user_id']])) {
                    $userRes[$profile['user_id']]= array_merge($userRes[$profile['user_id']], $profile);
                }
            }
        }*/

        $teacherCond = "fk_user IN ($uidStr)";
        $teacherItem = array('fk_user', 'title','college','desc','major');
        $teacherList = $user_db->getTeacherProfileList($teacherCond, $teacherItem);
        $result=$userRes;
        if (!empty($teacherList->items)) {
            foreach ($teacherList->items as $teacher) {
                if (!empty($userRes[$teacher['fk_user']])) {
                    $result[$teacher['fk_user']] = array_merge($userRes[$teacher['fk_user']], $teacher);
                }
            }
        }


        $ret->data = $result;
        return $ret;
    }

	public function pagegetOrgUserinfo($inPath){
		$ret = new stdclass;
		$oid = empty($inPath[3])?0:$inPath[3];
		//page 页数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
		//length 每页显示数
		if(empty($inPath[5])||!is_numeric($inPath[5])){$length = 4;}else{$length = $inPath[5];}

		$user_api = new user_api;
		$all=0;
		$params = SJson::decode(utility_net::getPostData());
		$uid = $params->uid;
		if(!empty($params->all)){
			$all=1;
		}
		$listorg= $user_api->getOrgUserinfo($oid,$uid,$page,$length);
		if($listorg === false){
			$ret->result =  new stdclass;
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$ret->data=$listorg;
		return $ret;
	}
	public function pageUserDel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3])){
			$ret->result->code=-1;
			return $ret;
		}
		$oid = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		$teacherId = !empty($params->teacher_id) ? $params->teacher_id : '';
		$org = user_db::delOrgUser($oid,$teacherId);
		if($org>0){
			$teach =tag_api::delMappingUserByUserId($teacherId);
		}
		$idArr = explode(",",$teacherId);
		if(!empty($idArr)){
			foreach($idArr as $v){
				$retUser = user_db::getteacherOrgArr($v);
				//该用户在别的机构下是否是老师
				if(!empty($retUser->items)){
					$countOrgUser = count($retUser->items);
				}else{
					$countOrgUser = 0;
				}
				//如果不是老师就把该用户老师权限去掉
				if(0==$countOrgUser){
					$userData = user_db::getUser($v);
					$type = $userData["type"]-2;
					$data["type"] = $type;
					user_db::updateUser($v,$data);
				}
			}
		}
		if($org==false){
			$ret->result->code=-3;
			return $ret;
		}
		$ret->result->code=0;
		return $ret;
	}
	/**
	 * 多个教师展现设置
	 */
	public function pagesetteacherDisplay($inPath){
		$teacher = new stdclass;
		$teacher->result =  new stdclass;
		if(empty($inPath[3])){
			$teacher->result->code=-1;
			return $teacher;
		}
		$data = array();
		$oid = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		$data["visiable"] = isset($params->visiable)? $params->visiable : 0;
		$teacher_id = isset($params->teacher_id)? $params->teacher_id : 0;
		$data["last_updated"] = date("Y-m-d H:i:s",time());
		$result = user_db::delOrgUser($oid,$teacher_id,$data);
		if($result === false){
			$teacher->result->code = -2;
			return $teacher;
		}else{
			$teacher->result->code = 0;
		}
		return $teacher;
	}
	/**
	 * 机构用户设置
	 */
	public function pageUserSet($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3]) || empty($inPath[4])){
			$ret->result->code=-1;
			return $ret;
		}
		$data = array();
		$oid = $inPath[3];
		//获取机构信息
		$user_db = new user_db;
		$org = $user_db->getOrg($oid);
		if($org==false){
			$ret->result->code=-3;
			return $ret;
		}
		$user_id = $inPath[4];
		$params = SJson::decode(utility_net::getPostData());
		$data["sort"] = empty($params->sort)?0:$params->sort;
		$data["is_star"] = empty($params->is_star)?0:$params->is_star;
		$data["role"] = empty($params->role)?0:$params->role;
		$listorg= $user_db->setOrgUser($oid,$user_id,$data);
		if($listorg === false){
			$ret->result->code = -2;
			return $ret;
		}else{
			$ret->result->code = 0;
		}
		return $ret;
	}

	public function pageaddTeacherUser($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3]) || empty($inPath[4])){
			$ret->result->code=-1;
			return $ret;
		}
		$data = array();
		$oid = $inPath[3];
		//获取机构信息
		$user_db = new user_db;
		$org = $user_db->getOrg($oid);
		if($org==false){
			$ret->result->code=-3;
			return $ret;
		}
		$user_id = $inPath[4];
		
		
		$data["sort"] = $params->sort;
		$data["is_star"] = empty($params->is_star)?0:1;
		$data["role"] = !empty($params->role)?$params->role:0;
		$sort = empty($inPath[5])?0:$inPath[5];
		$listorg= $user_db->setOrgUser($oid,$user_id,$data);
		if($listorg === false){
			$ret->result->code = -2;
			return $ret;
		}else{
			$ret->result->code = 0;
		}
		return $ret;
	}
	/**
	 * 机构用户排序设置
	 */
	public function pageUserSetData($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3]) || empty($inPath[4])){
			$ret->result->code=-1;
			return $ret;
		}

		//$oid = 104;
		//$user_id = 153;
		$oid = $inPath[3];
		$user_id = $inPath[4];
		//$sort = empty($inPath[5])?1:$inPath[5];
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		$data["sort"] = empty($params->sort)?0:$params->sort;
		$data["is_star"] = empty($params->is_star)?0:1;

		$org = user_db::getOrg($oid);
		if($org==false){
			$ret->result->code=-3;
			return $ret;
		}
		$user_api = new user_api();
		$user_db = new user_db();
	//	$updateRet = $user_api->usersetdata($oid,$user_id,$data); //本接口是自动排序接口
		$updateRet = $user_db->setOrgUserdata($oid,$user_id,$data);
		if($updateRet === false){
			$ret->result->code = -2;
			return $ret;
		}else{
			$ret->result->code = 0;
			$ret->result->msg = "success";
		}
		return $ret;
	}
	/**
	 * 根据域名获取用户ID，这个方法可以缓存至少1小时
	 */
	public function pageGetUserIdBySubDomain($inPath){
		utility_cache::pageCache(3600);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "subdomain is empty!";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->subdomain)){
			return $ret;
		}
		$user_api = new user_api;
		$subdomain = $params->subdomain;
		$SubUserid = $user_api->getUserIdBySubDomain($subdomain);
		if($SubUserid === false){
			$ret->result->code = -2;
			$ret->result->msg = "the Userid is not found!";
			return $ret;
		}
		return array("code"=>0,"data"=>$SubUserid);
	}
	public function pageGetSubDomainByUserId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
		    $ret->result->msg= "uid is empty!";
			return $ret;
		}
		$user_db = new user_db;
		$subdomain = $user_db->getSubDomainByUserId($inPath[3]);
		if($subdomain === false){
			$ret->result->code = -2;
			$ret->result->msg = "the Userid is not found!";
			return $ret;
		}
		return array("code"=>0,"data"=>$subdomain);
	}

	public function pageGetOrgProfileByUidArr($inPath){
		
		$ret = new stdclass;
        $ret->result = new stdclass;
        $uidArr = SJson::decode(utility_net::getPostData(),true);
        if (!$uidArr) {
        	$ret->result->code = -1006;
            $ret->result->message = 'params error';
            return $ret;
        }
		$user_db = new user_db();
        $org_ret = $user_db->getOrgProfileByUidArr($uidArr);
        if (!$org_ret) {
            $ret->result->code = -1007;
            $ret->result->message = 'get data list failed';
            return $ret;
        }
        return $org_ret;
    }

	public function pageGetOrgProfileByOidArr($inPath){
		
		$ret = new stdclass;
        $ret->result = new stdclass;
        $oidArr = SJson::decode(utility_net::getPostData(),true);
        if (!$oidArr) {
        	$ret->result->code = -1006;
            $ret->result->message = 'params error';
            return $ret;
        }
		$user_db = new user_db();
        //查询机构详情
        $org_ret = $user_db->getOrgProfileByOidArr($oidArr);
        if (empty($org_ret->items)){
            $ret->result->code = -1007;
            $ret->result->message = 'get data list failed';
            return $ret;
        }
        $ownerArr=array();
        foreach($org_ret->items as $ov){
            $ownerArr[$ov['fk_user_owner']]=$ov['fk_user_owner'];
        }
        //查询subdomain 
        $subdomains=array();
        $subdomain_ret=$user_db->getSubdomainByUidArr($ownerArr);
        if(!empty($subdomain_ret->items)){
            foreach($subdomain_ret->items as $sv){
               $subdomains[$sv['fk_user']]=$sv['subdomain'];
            }
        }
        $profile=array();
        foreach($org_ret->items as $k=>$v){
            $profile[$k]['oid']=$v['fk_org'];
            $profile[$k]['owner']=$v['fk_user_owner'];
            $profile[$k]['subname']=$v['subname'];
            $profile[$k]['scopes']=$v['scopes'];
            $profile[$k]['company']=$v['company'];
            $profile[$k]['province']=$v['province'];
            $profile[$k]['city']=$v['city'];
            $profile[$k]['county']=$v['county'];
            $profile[$k]['address']=$v['address'];
            $profile[$k]['areacode']=$v['areacode'];
            $profile[$k]['hotline']=$v['hotline'];
            $profile[$k]['extension']=$v['extension'];
            $profile[$k]['policy']=$v['policy'];
            $profile[$k]['email']=$v['email'];
            $profile[$k]['mobile']=$v['mobile'];
            $profile[$k]['hot_type']=$v['hot_type'];
            $profile[$k]['subdomain']=isset($subdomains[$v['fk_user_owner']])?$subdomains[$v['fk_user_owner']]:'';
            $profile[$k]['last_updated']=$v['last_updated'];
        }
        $ret->data=$profile;
        return $ret;
    }

	public function pageSetOrgUserData($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3]) || empty($inPath[4])){
			$ret->result->code=-1;
			$ret->result->msg='id error';
			return $ret;
		}
		$oid = $inPath[3];
		$user_id = $inPath[4];
		$params = SJson::decode(utility_net::getPostData());
		$org = user_db::getOrg($oid);
		if($org==false){
			$ret->result->code=-2;
			$ret->result->msg='org is empty';
			return $ret;
		}
		$user_db = new user_db();
		$updateRet = $user_db->setOrgUserData($oid,$user_id,$params);
		if($updateRet === false){
			$ret->result->code = -3;
			$ret->result->msg = "db error";
			return $ret;
		}else{
			$ret->result->code = 0;
			$ret->result->msg = "success";
		}
		return $ret;
	}
    public function pageDelHistoryStarTeacher($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $params = SJson::decode(utility_net::getPostData());
        $user_db = new user_db;
        $update_r = $user_db->delHistoryStarTeacher($inPath[3],$params->fk_user);
        if($update_r=== false){
            $ret->result->code = -2;
            $ret->result->msg = "fail update";
        }else{
            $ret->result->code = 0;
            $ret->result->msg ="success";
        }
        return $ret;
    }

	public function pageGetNewJoinOrg($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $page   = $inPath[3];
		$length = $inPath[4];
        if (empty($length) || empty($page)) {
        	$ret->result->code = -1006;
            $ret->result->message = 'params error';
            return $ret;
        }
        $user_db = new user_db();
		$condition = "status = 1 AND name <> ''";
		$orderby = array('create_time'=>'desc');
        $org_ret = $user_db::getOrgList($page,$length,$condition,$orderby);
        if (!$org_ret) {
            $ret->result->code = -1007;
            $ret->result->message = 'get data list failed';
            return $ret;
        }
        return $org_ret;
    }

	public function pageSetOrgUser($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($inPath[3])||empty($inPath[4])||empty($params)){
			$ret->result->code=-1;
			$ret->result->msg='id error';
			return $ret;
		}
		$oid = $inPath[3];
		$user_id = $inPath[4];
		$user_db = new user_db();
		$db_ret = $user_db->setOrgUser($oid,$user_id,$params);
		if($db_ret === false){
			$ret->result->code = -3;
			$ret->result->msg = "db error";
			return $ret;
		}else{
			$ret->result->code = 0;
			$ret->result->msg = "success";
		}
		return $ret;
	}
	public function pageCountOrgRole($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$oid = $inPath[3];
		$user_db = new user_db;
		$db_ret = $user_db->countOrgRole($oid);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $db_ret;
	}
	public function pageAddOrg($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($params)){
			$ret->result->code=-1;
			$ret->result->msg='params is empty';
			return $ret;
		}
		$user_db = new user_db;
        $oid = $user_db->addOrg($params);
		if($oid === false){
			$ret->result->code = -2;
			$ret->result->msg='db error';
		    return $ret;
		}
        return $oid;
	}
	public function pageAddOrgVerify($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($params)){
			$ret->result->code=-1;
			$ret->result->msg='params is empty';
			return $ret;
		}
		$user_db = new user_db;
        $addOrg = $user_db->addOrgVerify($params);
		if($addOrg === false){
			$ret->result->code = -2;
			$ret->result->msg='db error';
		}else{
			$ret->result->code = 0;
		}
		return $ret;
	}
	public function pageGetOrgVerify($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$uid = $inPath[3];
		$user_db = new user_db;
		$db_ret = $user_db->getOrgVerify($uid);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        $ret->data=$db_ret;
        return $ret;
	//	return $db_ret;
    }
	public function pagegetOrgOfNavList($inPath){
		$ret = new stdclass;
		$oid = isset($inPath[3]) ? (int)$inPath[3] : 0;
		$res = user_db::getOrgOfNavList($oid);
		if($res === false){
			$ret->result->code = -100;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        $ret->data=$res;
        return $ret;
    }
	public function pageupdateOrgOfNavOneInfo($inPath){
		$ret = new stdclass;
		$ret->code = 0;
		$ret->msg = 'success';
		$ret->data = '';
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		$pid 				= !empty($params->pk_nav_id) ? $params->pk_nav_id : '';
		$data['fk_org']	= !empty($params->fk_org) ? $params->fk_org : '';
		$data['nav_name']	= !empty($params->nav_name) ? $params->nav_name : '';
		$data['url']		= !empty($params->url) ? $params->url : '';
		$data['create_time']= date("Y-m-d H:i:s");
		$info = user_db::updateOrgOfNavOneInfo($pid,$data);
		if(!empty($info)){
			$ret->data = $info;
			return $ret;
		}else{
			$ret->code = -100;
			$ret->msg = 'get data failed';
			return $ret;
		}
	}
	public function pageaddOrgOfNav($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data  = array();
		$data['nav_name']	= !empty($params->nav_name) ? $params->nav_name : '';
		$data['url']		= !empty($params->url) ? $params->url : '';
		$data['fk_org']		= !empty($params->fk_org) ? $params->fk_org : '';
		$data['create_time']= date("Y-m-d H:i:s");
		$orgza 				=	user_db::addOrgOfNav($data);
		if($orgza === false){
			$ret->result->code = -2;
			$ret->result->msg = "add fail...";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagedelOrgOfNav($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data  = array();
		$data['pk_nav_id']	= !empty($params->pk_nav_id) ? $params->pk_nav_id : '';
		$data['fk_org']		= !empty($params->fk_org) ? $params->fk_org : '';
		$orgza 				=	user_db::delOrgOfNav($data);
		if($orgza === false){
			$ret->result->code = -2;
			$ret->result->msg = "del data fail...";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageGetOrgVerifyBySubDomain($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
        if(empty($params->subdomain)){
			$ret->result->code = -2;
			$ret->result->msg = "the subdomain is not empty!";
			return $ret;
        }
		$user_db = new user_db;
		$db_ret = $user_db->getOrgVerifyBySubDomain($params->subdomain);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        $ret->data=$db_ret;
        return $ret;
	//	return $db_ret;
    }
    public function pagegetOrgByid($inPath){
		$ret = new stdclass;
		$uid = !empty($inPath[3]) ? (int)$inPath[3] : 0;
		$user_db = new user_db;
		$orgInfo = user_db::getorg($uid);
		$profileInfo = user_db::getMgrOrgProfileByInfo($uid);
        $profile= array("subname"=>$profileInfo['subname'],
                        "company"=>$profileInfo['company'],
                        "scopes"=>$profileInfo['scopes'],
                        "address"=>$profileInfo['address'],
                        "areacode"=>$profileInfo['areacode'],
                        "hotline"=>$profileInfo['hotline']
                        );
        $verifyInfo = user_db::getmgrOrgVerifySubdomain($uid);
        $verify = array("email"=>$verifyInfo['email'],
                        "verify_subdomain"=>$verifyInfo['subdomain'],
                        "idcard_pic"=>$verifyInfo['idcard_pic'],
                        "qualify_pic"=>$verifyInfo['qualify_pic'],
                        "descript"=>$verifyInfo['descript'],
                        "verify_status"=>$verifyInfo['verify_status'],
                        "last_updated"=>$verifyInfo['last_updated']
                    );
		$subdomain= user_db::getSubDomainByUserId($orgInfo['fk_user_owner']);
		$orgInfo['subdomain']=!empty($subdomain['subdomain']) ? $subdomain['subdomain'] : '';
		$userId = !empty($orgInfo['fk_user_owner']) ? $orgInfo['fk_user_owner'] : 0;
		$realName = user_db::getUserProfile($userId);
		$mobile = user_db::getUserMobileByID($userId);
		$orgInfo['real_name'] = !empty($realName['real_name']) ? $realName['real_name'] : '';
		$orgInfo['mobile'] = !empty($mobile['mobile']) ? $mobile['mobile'] : '';
        $result = array_merge($orgInfo,$profile,$verify);
        return $result;
    }
	public function pageGetOrgIdsByUid($inPath){
		$ret = new stdclass;
        $ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
        if (empty($inPath[3])) {
        	$ret->result->code = -2;
            $ret->result->message = 'uid error';
            return $ret;
        }
		$user_db = new user_db();
        $org_ret = $user_db->getOrgIdsByUid($inPath[3]);
        if (!$org_ret) {
            $ret->result->code = -3;
            $ret->result->message = 'get data list failed';
            return $ret;
        }
        $ret->data=$org_ret->items;
        return $ret;
    }
	public function pageGetSubDomainByOid($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
		    $ret->result->msg= "uid is empty!";
			return $ret;
		}
		$user_db = new user_db;
		$subdomain = $user_db->getSubDomainByOid($inPath[3]);
		if($subdomain === false){
			$ret->result->code = -2;
			$ret->result->msg = "the Userid is not found!";
			return $ret;
		}
		return array("code"=>0,"data"=>$subdomain);
	}
	public function pageGetOrgTeacherCount($inPath){
		$oid_arr = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		$ret->result =  new stdclass;
		if(empty($oid_arr)){
			$ret->result->code=-1;
			$ret->result->msg='params is empty';
			return $ret;
		}
		$user_db = new user_db;
        $count_data = $user_db->getOrgTeacherCount($oid_arr);
		if(empty($count_data)){
			$ret->result->code = -2;
			$ret->result->msg='get data failed';
		}else{
			$ret->result->code = 0;
			$ret->result->data = $count_data;
		}
		return $ret;
	}
	
	public function pageGetAllOrg($inPath){
		$ret = new stdclass;
		$ret->code = 0;
		$ret->msg = 'success';
		$ret->data = '';
		$userDb = new user_db;
		$orgRet = $userDb->getAllOrg();
		if(!empty($orgRet->items)){
			$ret->data = $orgRet->items;
			return $ret;
		}else{
			$ret->code = -2;
			$ret->msg = 'get data failed';
			return $ret;
		}
	}
    /**
     * 获取机构模版
     * @param $uid
     * @return false or obiect
     * @author Panda <zhangtaifeng@gn100.com>
     */
	public function pageGetOrgTemplate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
            $ret->result->code = -2;
            $ret->result->msg= "owner is empty";
            return $ret;
		}
        $ownerId=(int)$inPath[3];
		$user_db = new user_db;
		$dbRet = $user_db->getOrgTemplate($ownerId);
		if(empty($dbRet->items)){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        foreach($dbRet->items as $k=>$v){
            if(!empty($v['query_str'])){
                $queryArr=array();
                $tmpArr=explode(',',$v['query_str']); 
                foreach($tmpArr as $tv){
                    $arr=explode(':',$tv);
					$queryArr[$arr[0]]= !empty($arr[1]) ? $arr[1] : '0';
                }
                $dbRet->items[$k]['query_arr']=$queryArr;
            }
            if(!empty($v['course_ids'])){
                $courseArr=explode(',',$v['course_ids']);
                $dbRet->items[$k]['course_arr']=$courseArr;
            }
        }
        $ret->result->code = 0;
        $ret->data=$dbRet->items;
        return $ret;
	}
	/**
     * 从临时表t_organization_template_check获取机构模板课程
     * @param $ownerId
     * @return false or obiect
     */
	public function pagegetTemplateCheck($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
            $ret->result->code = -2;
            $ret->result->msg= "data is empty";
            return $ret;
		}
        $ownerId=(int)$inPath[3];
		$user_db = new user_db;
		$dbRet = $user_db->getTemplateCheck($ownerId);
		if(empty($dbRet->items)){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        foreach($dbRet->items as $k=>$v){
            if(!empty($v['query_str'])){
                $queryArr=array();
                $tmpArr=explode(',',$v['query_str']); 
                foreach($tmpArr as $tv){
                    $arr=explode(':',$tv);
					$queryArr[$arr[0]]= !empty($arr[1]) ? $arr[1] : '0';
                }
                $dbRet->items[$k]['query_arr']=$queryArr;
            }
            if(!empty($v['course_ids'])){
                $courseArr=explode(',',$v['course_ids']);
                $dbRet->items[$k]['course_arr']=$courseArr;
            }
        }
        $ret->result->code = 0;
        $ret->data=$dbRet->items;
        return $ret;
	}
    /**
     * 获取机构模版详情
     * @param $tid
     * @return false or obiect
     * @author Panda <zhangtaifeng@gn100.com>
     */
	public function pageGetOrgTemplateInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
            $ret->result->code = -2;
            $ret->result->msg= "tid is empty";
            return $ret;
		}
        $tid=(int)$inPath[3];
		$user_db = new user_db;
		$dbRet = $user_db->getOrgTemplateInfo($tid);
		if($dbRet===false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        if(!empty($dbRet['query_str'])){
            $queryArr=array();
            $tmpArr1=explode(',',$dbRet['query_str']); 
            foreach($tmpArr1 as $tv1){
                $arr1=explode(':',$tv1);
                $queryArr[$arr1[0]]= !empty($arr1[1]) ? $arr1[1] : '0';
            }
            $dbRet['query_arr']=$queryArr;
        }
        if(!empty($dbRet['course_ids'])){
            $tmpArr2=explode(',',$dbRet['course_ids']); 
            $dbRet['course_arr']=$tmpArr2;
        }
        $ret->result->code = 0;
        $ret->data=$dbRet;
        return $ret;
	}
	public function pageUpdateOrgTemplate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$params = SJson::decode(utility_net::getPostData(),true);
		$user_db = new user_db;
		$db_ret=$user_db->updateOrgTemplate((int)$inPath[3],$params);
		if($db_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageAddOrgTemplate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "params is empty!";
			return $ret;
		}
		$userDb = new user_db;
		$dbRet=$userDb->addOrgTemplate($params);
		if($dbRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageDeleteOrgTemplate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "update inpath is empty!";
			return $ret;
		}
		$userDb = new user_db;
        $dbRet=$userDb->deleteOrgTemplate((int)$inPath[3]);
		if($dbRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagedeleteOrgTemplateMoreInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$pid 	= !empty($params->fk_template) ? $params->fk_template : 0;
		$userDb = new user_db;
        $dbRet=$userDb->deleteOrgTemplateMoreInfo($pid);
		if($dbRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageGetOrgByProvince($inPath){
		$ret = new stdclass;
  
		$province = isset($inPath[3]) ? (int)($inPath[3]) : 0;
		$page   = !empty($inPath[4]) ? $inPath[4] : 1;
		$length = !empty($inPath[5]) ? $inPath[5] : -1;
		
		$res = user_db::getOrgByProvince($province,$page,$length);
		
		$ret->data = $res;
		if(empty($res)){
			$ret->result = array('code'=>-2,'msg'=>'data is error');
            return $ret;
		}
		return $ret;
	}
    public function pagegetApplyOrgSubdomainOfUser($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3])){
		    $ret->result->msg= "userid is empty!";
			return $ret;
		}
		$user_db = new user_db;
		$subdomain = $user_db->getApplyOrgSubdomainOfUser($inPath[3]);
		if($subdomain === false){
			$ret->result->code = -2;
			$ret->result->msg = "userid not found!";
			return $ret;
		}
		return array("code"=>0,"data"=>$subdomain);
	}
	public function pagecustomerServicesQqList($inPath){
		$ret = new stdclass;
        $params = SJson::decode(utility_net::getPostData());
        $orgId = !empty($inPath[3]) ? $inPath[3] : '';
		$info = user_db::customerServicesQqList($orgId);
        $arr = array();
        if(!empty($info->items)){
            foreach($info->items as $k=>$v){
                if(!empty($v['type'])&&$v['type']==1){
                    $arr['weima'][]= $v;
                }
                if(!empty($v['type'])&&$v['type']==2){
                    $arr['tel'][]= $v;
                }
                if(!empty($v['type'])&&$v['type']==3){
                    $arr['qq'][]= $v;
                }
                if(!empty($v['type'])&&$v['type']==4){
                    $arr['qqun'][]= $v;
                }
            }
			$ret->data = $arr;
        }else{
			$ret->data="customerService is empty";
		}
		return $ret;
	}
	public function pageaddOrgCustomerInfo(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data['type'] = !empty($params->type) ? $params->type : '';
		$data['type_value'] = !empty($params->type_value) ? $params->type_value : '';
		$data['type_name'] = !empty($params->type_name) ? $params->type_name : '';
		$data['type_code'] = !empty($params->type_code) ? $params->type_code : '';
		$data['fk_user_owner'] = !empty($params->fk_user_owner) ? $params->fk_user_owner : '';
		$data['create_time'] = date("Y-m-d H:i:s",time());
		$info = user_db::addOrgCustomerInfo($data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 100;
			$ret->result->msg =" add success";
		}
		return $ret;
	}
	public function pageupdateOrgCustomerInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$pid = !empty($inPath[3]) ? $inPath[3] : '';
		$params = SJson::decode(utility_net::getPostData());
		$data['type_value'] = !empty($params->type_value) ? $params->type_value : '';
		$data['type_name'] = !empty($params->type_name) ? $params->type_name : '';
		$data['type_code'] = !empty($params->type_code) ? $params->type_code : '';
		$data['fk_user_owner'] = !empty($params->fk_user_owner) ? $params->fk_user_owner : '';
		$data['last_updated'] = date("Y-m-d H:i:s",time());
		$info = user_db::updateOrgCustomerInfo($pid,$data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 100;
			$ret->result->msg =" update success";
		}
		return $ret;
	}
	public function pagegetOrgCustomerInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		$data['pk_customer'] = !empty($params->pk_customer) ? (int)$params->pk_customer : '';
		if(!empty($params->type)){
			$data['type'] = $params->type;
		}
		$data['fk_user_owner'] = !empty($params->fk_user_owner) ? $params->fk_user_owner : '';
		$data['status'] =1;
		$info = user_db::getOrgCustomerInfo($data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->msg = "is data empty";
		}else{
			$ret->result->code = 100;
			$ret->result->msg =" info is existed";
		}
		return $ret;
	}
	public function pagedelOrgCustomerInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$pid = !empty($inPath[3]) ? $inPath[3] : '';
		$data = array();
		$data['last_updated'] = date("Y-m-d H:i:s",time());
		$data['status'] = -1;
		$info = user_db::delOrgCustomerInfo($pid,$data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 100;
			$ret->result->msg =" del success";
		}
		return $ret;
	}

	public function pagesearchOrgTeacherNameOrMobileInfo($inPath){
		$user_db = new user_db();
		$params = SJson::decode(utility_net::getPostData());
		$data['keyword'] = !empty($params->keyword) ? $params->keyword : '';
		$data['fk_org'] = !empty($inPath[3]) ? $inPath[3] : '';
		$info=$user_db->searchOrgTeacherNameOrMobileInfo($data);
        if(!empty($info->items)){
            foreach($info->items as $k=>$v){
                $info->items[$k]['roles']=array();
               if($v['user_role']&0x01||$v['role']==1||$v['role']==0){
                    $info->items[$k]['roles'][]='general';
               } 
               if($v['user_role'] &0x02){
                    $info->items[$k]['roles'][]='assistant';
               } 
               if($v['user_role']&0x04||$v['role']==2){
                    $info->items[$k]['roles'][]='admin';
               } 
            }
        }
        return $info;
	}

    /*
     * 查询机构教师数量 
     * @param  $owner,$status
     * @return int
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageCountTeacherByOid($inPath){
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
        $db = new user_db;
        $res= $db->countTeacherByOid($inPath[3], $params);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->data = $res;
        return $ret;
    }
    public function pageGetOrgTemplateMaxSort($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";
        if (empty($inPath[3])) {
            $ret->result->code = -1;
            $ret->result->msg  = "owner is empty";
            return $ret;
        }
        $db = new user_db;
        $res= $db->getOrgTemplateMaxSort($inPath[3]);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->data = $res;
        return $ret;
    }
	public function pageajaxAddNoticeCategory(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data['name'] = !empty($params->name) ? $params->name : '';
		$data['fk_org'] = !empty($params->fk_org) ? $params->fk_org : '';
		$data['fk_user'] = !empty($params->fk_user) ? $params->fk_user : '';
		$data['status'] = 1;
		$data['create_time'] = date("Y-m-d H:i:s",time());
		$info = user_db::AddNoticeCategory($data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->data = "";
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 100;
			$ret->result->data = (int)$info;
			$ret->result->msg =" add success";
		}
		return $ret;
	}
	public function pagenoticeCategoryList($inPath){
		$params= SJson::decode(utility_net::getPostData());
		$data = array("fk_org"=>!empty($params->fk_org) ? $params->fk_org : '',
					  "status"=> 1
				);
		$list = user_db::noticeCategoryList($data);
		return $list;
	}
	public function pagegetCateNameInfo(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data = array("pk_cate"=>!empty($params->pk_cate) ? $params->pk_cate : '',
					  "fk_org"=>!empty($params->fk_org) ? $params->fk_org : ''
				);
		$info = user_db::getCateNameInfo($data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->msg = "data is empty";
		}else{
			$ret->result->code = 100;
			$ret->result->msg =" have data";
		}
		return $ret;
	}
	public function pageupdateNoticeCate(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data = array(
					  "fk_org"=>!empty($params->fk_org) ? $params->fk_org : '',
					  "name"=>!empty($params->name) ? $params->name : '',
					  "last_updated"=>date("Y-m-d H:i:s",time()),
					  "fk_user"=>!empty($params->fk_user) ? $params->fk_user : ''
				);
		$cid = !empty($params->pk_cate) ? $params->pk_cate : '';
		$info = user_db::updateNoticeCate($cid,$data);
		if($info === false){
			$ret->result->code = -1;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 100;
			$ret->result->msg =" update success";
		}
		return $ret;
	}
	public function pagedelnoticeCateInfo(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data = array("pk_cate"=>!empty($params->pk_cate) ? $params->pk_cate : '',
					  "fk_org"=>!empty($params->fk_org) ? $params->fk_org : ''
				);
		$info = user_db::getCateNameInfo($data);
		$cid = !empty($params->pk_cate) ? $params->pk_cate : '';
		$condition = array("status"=>-1);
        $noticeData = array("fk_cate"=>!empty($params->pk_cate) ? $params->pk_cate : '',
                            "fk_user_id"=>!empty($params->fk_user_id) ? $params->fk_user_id: ''
                    );
        
		if(!empty($info)){
			$status = user_db::updateNoticeCate($cid,$condition);
            $conditionCateInfo = user_db::getNoticeConditionInfo($noticeData);
            if(!empty($conditionCateInfo->items)){
                $fkCate = array("fk_cate"=>0,"update_time"=>date("Y-m-d H:i:s",time()));
                $da = user_db::updateConditionOfCateNotice($noticeData,$fkCate);
            }
			if($status === false){
				$ret->result->code = -1;
				$ret->result->msg = "failed";
			}else{
				$ret->result->code = 100;
				$ret->result->msg =" del success";
			}
		}
		return $ret;
	}
	public function pagegetCourseFirstCateInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$level = $inPath[3];
		$params = SJson::decode(utility_net::getPostData());
		$con = array("level"=>$params->level);
		$cateInfo = course_db::getCourseFirstCateInfo($con);
		return $cateInfo;
	}
	
	public function pagegetCourseCateSomeName($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		$pkCate = !empty($params->cate_id) ? $params->cate_id : '';
		$cateInfo = course_db::getCourseCateSomeName($pkCate);
		return $cateInfo;
	}
	public function pageaddOrgIsRecommend($inPath){
		$ret 				= new stdclass;
		$ret->result 		=  new stdclass;
		$ret->result->code 	= -1;
		$ret->result->msg	= "";
		$params 			= 	SJson::decode(utility_net::getPostData());
        $data				=	array();
        $data['org_sort']	=	!empty($params->org_sort)	?	$params->org_sort	:'';
        $oid				=	!empty($params->pk_org)		?	$params->pk_org:'';
		$dbInfo				=	user_db::addOrgIsRecommend($oid,$data);
		if($dbInfo === false){
			$ret->result->code = -100;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagecancelOrgRecommend($inPath){
		$ret 				= new stdclass;
		$ret->result 		=  new stdclass;
		$ret->result->code 	= -1;
		$ret->result->msg	= "";
		$data 				= array();
		$data['org_sort']	= 0;
        $oid				=	!empty($inPath[3])		?	$inPath[3] :	'';
		$dbInfo				=	user_db::addOrgIsRecommend($oid,$data);
		if($dbInfo === false){
			$ret->result->code = -100;
			$ret->result->msg = " cancel fail";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagegetOrgRecommendList($inPath){
		$ret 				= new stdclass;
		$ret->result 		=  new stdclass;
		$ret->result->code 	= -1;
		$ret->result->msg= "";
		$condition 			=	"t_organization.`status` >0 AND t_organization.`org_sort` >0";
		$dbInfo				=	user_db::getOrgRecommendList($condition);
		if($dbInfo === false){
			$ret->result->code = -2;
			$ret->result->msg  = "is not found data";
			$ret->result->data = "";
		}else{
			$ret->result->code = 0;
			$ret->result->data = $dbInfo;
		}
		return $ret;
	}
	public function pagegetInternationalCodeByInfo(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		if(empty($params->cid)){
			$data = array("type"=>1);
		}
		$info = user_db::getInternationalCodeByInfo($data);
		return $info;
	}
	public function pagegetUserVerifyCodeLoginSms(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		$data['mobile']=!empty($params->mobile) ? $params->mobile : '';
		$data['tagCode']=!empty($params->tagCode) ? $params->tagCode : '';
		$data['code']=!empty($params->code) ? $params->code : '';
		$data['now']= date("Y-m-d H:i:s",strtotime("-10 min"));
		if($data['tagCode']=='1'){
			$result =user_db::getUserVerifyCodeLoginSms($data);
			if(!empty($result)){
				$num = strlen($result['code']);
				if($num !=6){
					$ret->result->data = "";
					$ret->result->code = -100;
					return $ret;
				}else{
					if($result !=false){
						$ret->result->data = $result;
						$ret->result->code = 100;
					}else{
						$ret->result->data = "";
						$ret->result->code = -100;
					}
						return $ret;
				}
			}else{
				$ret->result->data = "";
				$ret->result->code = -100;
				return $ret;
			}
		}else{
			$result =user_db::getUserVerifyCodeLoginSms($data);
			if($result !=false){
						$ret->result->data = $result;
						$ret->result->code = 100;
			}else{
						$ret->result->data = "";
						$ret->result->code = -100;
			}
						return $ret;
		}
	}

	public function pageGetOrgProfileInfo($inPath)
	{
		$orgId = isset($inPath[3]) && (int)$inPath[3] ? (int)$inPath[3] : 0;
		if (!$orgId) return api_func::setMsg(1000);

		$res = user_db::getMgrOrgProfileByInfo($orgId);
		if (empty($res)) return api_func::setMsg(3002);

		return api_func::setData($res);
	}
	
	public function pagegetSubdomainByNameIsExist($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$subdomain = isset($params->subdomain) ? $params->subdomain : '';
		$data 	= "subdomain Like '%".$subdomain."%'";
		$result = user_db::getSubdomainByNameIsExist($data);
		if($result===false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		$ret->result->code = 0;
        $ret->data=$result;
		return $ret;
	}
	
	/**
     * 获取自定义模版指定信息
     * @param $tid,$ownerId
     * @return false or obiect
     */
	public function pagegetTemplateData($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($inPath[3])){
            $ret->result->code = -2;
            $ret->result->msg= "tid is empty";
            return $ret;
		}
        $tid=(int)$inPath[3];
		$user_db = new user_db;
		$ownerId = isset($params) ? $params : 0;
		$dbRet = $user_db->getTemplateData($tid,$ownerId);
		if($dbRet===false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        if(!empty($dbRet['query_str'])){
            $queryArr=array();
            $tmpArr1=explode(',',$dbRet['query_str']); 
            foreach($tmpArr1 as $tv1){
                $arr1=explode(':',$tv1);
                $queryArr[$arr1[0]]= !empty($arr1[1]) ? $arr1[1] : '0';
            }
            $dbRet['query_arr']=$queryArr;
        }
        if(!empty($dbRet['course_ids'])){
            $tmpArr2=explode(',',$dbRet['course_ids']); 
            $dbRet['course_arr']=$tmpArr2;
        }
        $ret->result->code = 0;
        $ret->data=$dbRet;
        return $ret;
	}
	/**
     * 修改自定义模版指定信息
     * @param $data
     * @return false or obiect $ret
     */
	public function pageupdateTemplateData($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->pk_template) || empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "pk_template or fk_user_owner is not cant empty!";
			return $ret;
		}
		$tid 		 = !empty($params->pk_template) ? $params->pk_template : 0;
		$ownerId	 = !empty($params->fk_user_owner) ? $params->fk_user_owner : 0;
		$data		 = array( 
							  "recommend"		=>	isset($params->recommend) ? $params->recommend : 1,
							  "title"			=>	isset($params->title) ? $params->title : '',
							  "row_count"		=>	isset($params->row_count) ? $params->row_count : 0,
							  "query_str"		=>	isset($params->query_str) ? $params->query_str : '',
							  "order_by"		=>	isset($params->order_by) ? $params->order_by : '',
							  "course_ids"		=>	isset($params->course_ids) ? $params->course_ids : '',
							  "sort"			=>	isset($params->sort) ? $params->sort : 1,
							  "create_time"		=>	isset($params->create_time) ? $params->create_time : '',
							  "last_updated"	=>	isset($params->last_updated) ? $params->last_updated : '',
							  "set_url"			=>	isset($params->set_url) ? $params->set_url : '',
							  "type"			=>	isset($params->type) ? $params->type : '',
							  "thumb_left"		=>	isset($params->thumb_left) ? $params->thumb_left : '',
							  "thumb_right"		=>	isset($params->thumb_right) ? $params->thumb_right : '',
							  "thumb_left_url"	=>	isset($params->thumb_left_url) ? $params->thumb_left_url : '',
							  "thumb_right_url"	=>	isset($params->thumb_right_url) ? $params->thumb_right_url : ''
										  );
		$dbRet	=	user_db::updateTemplateData($tid,$ownerId,$data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	/**
     * 增加自定义模版
     * @param $data
     * @return false or obiect $ret
     */
	public function pageaddTemplateData($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->pk_template) || empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "pk_template or fk_user_owner is not cant empty!";
			return $ret;
		}
		
		$data		 = array(  
							  "fk_template"		=>	isset($params->pk_template) ? $params->pk_template : 0,
							  "fk_user_owner"	=>	isset($params->fk_user_owner) ? $params->fk_user_owner : 0,
							  "recommend"		=>	isset($params->recommend) ? $params->recommend : 1,
							  "title"			=>	isset($params->title) ? $params->title : '',
							  "row_count"		=>	isset($params->row_count) ? $params->row_count : 0,
							  "query_str"		=>	isset($params->query_str) ? $params->query_str : '',
							  "order_by"		=>	isset($params->order_by) ? $params->order_by : '',
							  "course_ids"		=>	isset($params->course_ids) ? $params->course_ids : '',
							  "sort"			=>	isset($params->sort) ? $params->sort : 1,
							  "create_time"		=>	isset($params->create_time) ? $params->create_time : '',
							  "last_updated"	=>	isset($params->last_updated) ? $params->last_updated : '',
							  "set_url"			=>	isset($params->set_url) ? $params->set_url : '',
							  "type"			=>	isset($params->type) ? $params->type : '',
							  "thumb_left"		=>	isset($params->thumb_left) ? $params->thumb_left : '',
							  "thumb_right"		=>	isset($params->thumb_right) ? $params->thumb_right : '',
							  "thumb_left_url"	=>	isset($params->thumb_left_url) ? $params->thumb_left_url : '',
							  "thumb_right_url"	=>	isset($params->thumb_right_url) ? $params->thumb_right_url : ''
						);
		$dbRet	=	user_db::addTemplateData($data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageupdateThumbPic($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->pk_template) || empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "pk_template or fk_user_owner is not cant empty!";
			return $ret;
		}
		$tid 		 = !empty($params->pk_template) ? $params->pk_template : 0;
		$dbRet	=	user_db::updateOrgTemplate($tid,$params);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}

	//获取机构数量
	public function pageGetOrgCount($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params["minDate"])){
			$params["minDate"]=0;
		}
		if(empty($params["maxDate"])){
			$params["maxDate"]=0;
		}
		return user_db_organizationDao::getOrgCount($params);
	}

	//获取机构列表
	public function pageGetOrgListByMgr($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params["minDate"])){
			$params["minDate"]=0;
		}
		if(empty($params["maxDate"])){
			$params["maxDate"]=0;
		}
		if(empty($params["length"])){
			$params["length"]=0;
		}
		if(empty($params["page"])){
			$params["page"]=0;
		}
		return user_db_organizationDao::getOrgListByMgr($params);
	}
}
