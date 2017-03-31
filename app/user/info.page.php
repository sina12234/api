<?php
/**
  * 用户API
  * @link http://wiki.gn100.com/doku.php?id=docs:api:user
  **/
class user_info{
	/**
	 * 校验提交的服务器权限，仅在配置文件里的才可以提交
	 * */
	public function __construct($inPath){
	}
	/**
	  * 用户创建立接口
	  */
	function pageteacherCreate($inPath){

		$params=SJson::decode(utility_net::getPostData());
		$params->type = 3;
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$user_id = user_api::addUser($params);
		if($user_id >0){//user_id
			$ret->result->code = 0;
			$ret->data=new stdclass;
			$ret->data->uid = $user_id;
		}else{//error code
			$ret->result->code = $user_id;
		}
		return $ret;
	}
	function pagestudentCreate($inPath){
		return $this->pageCreate($inPath);
	}
	function pageCreate($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$user_id = user_api::addUser($params);
		if($user_id >0){//user_id
			$ret->result->code = 0;
			$ret->data=new stdclass;
			$ret->data->uid = $user_id;
		}else{//error code
			$ret->result->code = $user_id;
		}
		return $ret;
	}

	/**
	 * 更新用户信息
	 * @param $inPath
	 * @param user object
	 */
	public function pageUpdate($inPath){
		$params = json_decode(utility_net::getPostData(), true);
        if (empty($inPath[3]) || !is_numeric($inPath[3]) || empty($params)) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
        //更新last_updated
        $params['last_updated']=date('Y-m-d H:i:s',0);
        $ret = $user_api->update((int)$inPath[3], $params);
        if (isset($ret['code'])) {
			return array(
				'result' => $ret,
			);
        }
		if ($ret == false) {
			return array('result' => array(
				'code' => -1,
				'msg' => 'failed',
			),);
		}
		return array('result' => array(
			'code' => 0,
			'msg' => 'ok',
		),);
	}

	public function pageUpdateteacherInfo($inPath){
		$params = json_decode(utility_net::getPostData(), true);
        if (empty($inPath[3]) || !is_numeric($inPath[3]) || empty($params)) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
        $ret = $user_api->updateteacherInfo((int)$inPath[3], $params);
        if (isset($ret['code'])) {
			return array(
				'result' => $ret,
			);
        }
		if ($ret == false) {
			return array('result' => array(
				'code' => -1,
				'msg' => 'failed',
			),);
		}
		return array('result' => array(
			'code' => 0,
			'msg' => 'ok',
		),);
	}	
	public function pageupdateteacherUserRealName($inPath){
		$params = json_decode(utility_net::getPostData(), true);
		if (empty($inPath[3]) || !is_numeric($inPath[3]) || empty($params)) {
			return array(
				'result' => array(
					'code' => -1,
					'msg' => 'invalid parameter',
				),
			);
        }
		$uid =(int)$inPath[3];
		$user_db = new user_db;
        $data=array();
        $data['real_name']=$params['name'];
        //更新last_updated字段
        $data['last_updated']=date('Y-m-d H:i:s',time());
		$res = $user_db::updateUserProfile($uid,$data);
		if ($res == false) {
			return array('result' => array(
				'code' => -1,
				'msg' => 'failed',
			),);
		}
		return array('result' => array(
			'code' => 0,
			'msg' => 'ok',
		),);
	}
	public function pageupdatestudentInfo($inPath){
		$params = json_decode(utility_net::getPostData(), true);
        if (empty($inPath[3]) || !is_numeric($inPath[3]) || empty($params)) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
        $ret = $user_api->update((int)$inPath[3], $params);
        if (isset($ret['code'])) {
			return array(
				'result' => $ret,
			);
        }
		if ($ret == false) {
			return array('result' => array(
				'code' => -1,
				'msg' => 'failed',
			),);
		}
		return array('result' => array(
			'code' => 0,
			'msg' => 'ok',
		),);
	}
	
	public function pageGet($inPath){
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
		$user = $user_api->get((int)$inPath[3]);
		if (empty($user)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);
		}
		return array(
			'data' => $user,
		);
	}

    public function pageGetUserIdByMobile($inPath)
    {
        $data = array(
            'code' => -1,
            'msg' => 'get data failure'
        );
        if (empty($inPath[3]) || !utility_valid::mobile($inPath[3])) {
            $data['code'] = 2012;
            $data['msg'] = 'Is not valid mobile phone number';
        }

        $ret = user_db::getUserIDByMobile($inPath[3]);

        if ($ret) {
            $data['code'] = 0;
            $data['msg'] = 'success';
            $data['uid'] = $ret;
        }

        return $data;
    }

	public function pageSearch($inPath){
		$user_api = new user_api;
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params)){
			$params = $_GET;
		}
		return $user_api->search($params);
	}

	public function pagesearchShow($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
        $user_api = new user_api;
		$user_list = $user_api->getsearchShow($params);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}

	public function pagesearchTeacherShow($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
        $user_api = new user_api;
		$user_list = $user_api->getsearchTeacherShow($params);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}	
	public function pagegetNormalOrgNameByInfo($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$data = !empty($params->org_name) ? $params->org_name : '';
		$orgInfo = user_db::getNormalOrgNameByInfo($data);
		if (empty($orgInfo)) {
			return array("code" => -100,"msg" => 'is not found data ');
		}
		return $orgInfo;
	}	
	public function pageList($inPath){
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return array(
				'result' => array(
					'code' => -201,
					'msg' => 'invalid parameter',
				),
			);
        }
		$pn = (int)$inPath[4];
		if (empty($pn)) {
			$pn = 20;
		}
		$user_api = new user_api;
		$user_list = $user_api->getlist((int)$inPath[3], $pn);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}
	
	public function pageteacherList($inPath){
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return array(
				'result' => array(
					'code' => -201,
					'msg' => 'invalid parameter',
				),
			);
        }
		$pn = (int)$inPath[4];
		if (empty($pn)) {
			$pn = 20;
		}
		$user_api = new user_api;
		$user_list = $user_api->getteacherList((int)$inPath[3], $pn);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}
	
	public function pagestudentList($inPath){
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			return array(
				'result' => array(
					'code' => -201,
					'msg' => 'invalid parameter',
				),
			);
        }
		$pn = (int)$inPath[4];
		if (empty($pn)) {
			$pn = 20;
		}
		$user_api = new user_api;
		$user_list = $user_api->studentList((int)$inPath[3], $pn);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}
	public function pageaddFav($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "the data is empty!";
		$user_api = new user_api;
		$params = SJson::decode(utility_net::getPostData(),true);
//		$data = $params;
		if(empty($params["course_id"]) || empty($params["user_id"])){
			return $ret;
		}
/*		if($params["course_id"] == 0 || $params["user_id"]== 0){
			return $ret;
		}
*/
		$data["course_id"] = empty($params)? "1":$params["course_id"];
		$data["user_id"] = empty($params)?"1":$params["user_id"];
		//当传过来的值为空，不能插入成功
		//define("DEBUG",true);
		$fav_ret = $user_api->addFav($data);
		if($fav_ret === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
		}else{
			$ret->result->code = 0;
			$ret->result->msg = "success!";
		}
		return $ret;
	}

    public function pageDelFav()
    {
        $data = array(
            'code' => 3033,
            'msg' => 'operate failure'
        );

        $params = SJson::decode(utility_net::getPostData(),true);

        if (!empty($params['uid']) && !empty($params['cid'])) {
            $ret = user_db::delFav(
                array(
                    'fk_user'=> $params['uid'],
                    'fk_course' => $params['cid']
                )
            );

            if ($ret) {
                $data['code'] = 0;
                $data['msg'] = 'success';
            }

            return $data;
        }
    }
	public function pagesingleTeacherInfoHave($inPath){
		$params = json_decode(utility_net::getPostData(), true);
        if (empty($inPath[3]) || !is_numeric($inPath[3]) || empty($params)) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
        $params['last_updated']=date('Y-m-d H:i:s');
        $ret = $user_api->singleTeacherInfoHave((int)$inPath[3], $params);
        if (isset($ret['code'])) {
			return array(
				'result' => $ret,
			);
        }
		if ($ret == false) {
			return array('result' => array(
				'code' => -1,
				'msg' => 'failed',
			),);
		}
		return array('result' => array(
			'code' => 0,
			'msg' => 'ok',
		),);
	}
	public function pagelistFav($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 10;}else{$length = $inPath[4];}

		$user_api = new user_api;
//		$cid = 116;
//		$uid = 153;
		$params = SJson::decode(utility_net::getPostData());
		$cid = isset($params->cid)? $params->cid:null;
		$uid = isset($params->uid)? $params->uid:null;
		$listfav = $user_api->listfav($cid,$uid,$page,$length);
		if($listfav === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $listfav;
	}
    //添加公告
    public function pageaddNotice($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "the data is empty!";
        $user_api=new user_api;
        $params=SJson::decode(utility_net::getPostData(),true);
        $notice_ret=$user_api->addNotice($params);
        if($notice_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }
    //修改公告
    public function pageupdateNotice($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "the data is empty!";
        $user_api=new user_api;
        $params=SJson::decode(utility_net::getPostData(),true);
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            return array(
                'result' => array(
                    'code' => -11,
                    'msg' => 'invalid parameter',
                ),
            );
        }
        $notice_ret=$user_api->updateNotice($inPath[3],$params);
        if($notice_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }
    //删除公告
    public function pagedelNotice($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "";
        $user_api=new user_api;
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret -> result -> msg = 'invalid parameter';
            return $ret;
        }
        $notice_ret=$user_api->delNotice($inPath[3]);
        if($notice_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }
    //置顶公告
    public function pagetopNotice($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "";
        $user_api=new user_api;
        if (empty($inPath[3]) || empty($inPath[4])) {
            $ret -> result -> msg = 'invalid parameter';
            return $ret;
        }
        $notice_ret=$user_api->topNotice($inPath[3],$inPath[4]);
        if($notice_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }
    //取消置顶公告
    public function pagenoTopNotice($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "";
        $user_api=new user_api;
        if (empty($inPath[3])) {
            $ret -> result -> msg = 'invalid parameter';
            return $ret;
        }
        $notice_ret=$user_api->noTopNotice($inPath[3]);
        if($notice_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }
    //公告列表
    public function pagegetNoticeList($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		//page 页数
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		//length 每页显示数
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = 10;}else{$length = $inPath[4];}
		$user_api = new user_api;
		$params = SJson::decode(utility_net::getPostData());
		$uid = isset($params->uid)? $params->uid:null;
		$cateId = isset($params->cateid)? $params->cateid : 0;
		$orgId = isset($params->orgId)? $params->orgId : 0;
		$notice_list = $user_api->getNoticeList($page,$length,$uid,$cateId,$orgId);
		if($notice_list === false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
		return $notice_list;
    }
    //获取公告
	public function pagegetNotice($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "invalid parameter";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -101;
		    $ret->result->msg= "invalid parameter";
            return $ret;
        }
		$user_api = new user_api;
		$r= $user_api->getNotice((int)$inPath[3]);
		if (empty($r)) {
		    $ret->result->code = -102;
		    $ret->result->msg= "the data is not found!";
            return $ret;
		}else{
            return $r;
        }
	}

	public function pageGetBasicUser($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "invalid parameter";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
		    $ret->result->code = -101;
		    $ret->result->msg= "invalid parameter";
            return $ret;
        }
		$r= user_db::getUser($inPath[3]);
		if (empty($r)) {
		    $ret->result->code = -102;
		    $ret->result->msg= "the data is not found!";
            return $ret;
		}else{
            return $r;
        }
	}

	public function pageGetBasicUserAndMobile($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "invalid parameter";
		if (empty($inPath[3]) || !is_numeric($inPath[3])) {
			$ret->result->code = -101;
			$ret->result->msg= "invalid parameter";
			return $ret;
		}
		$r= user_db::getUser($inPath[3]);
		if (empty($r)) {
			$ret->result->code = -102;
			$ret->result->msg= "the data is not found!";
			return $ret;
		}
		$data = user_db::getUserMobileByID($inPath[3]);
		if(empty($data)){
			$r["mobile"] = "no mobile";
		}else{
			$r["mobile"] = $data["mobile"];
		}
		$ret->result->code = 0;
		$ret->data = $r;
		return $ret;
	}

	public function pageGetSubdomainByUidArr($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->data = '';
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params['uidArr'])) {
    		$ret->result->code = -101;
    		$ret->result->msg  = "invalid parameter";
    		return $ret;
		}
		$user_db = new user_db();
		$r= $user_db->getSubdomainByUidArr($params['uidArr']);
		if (empty($r)) {
    		$ret->result->code = -102;
    		$ret->result->msg  = "the data is not found!";
    		return $ret;
		}else{
    		$ret->result->code = 0;
    		$ret->result->msg  = "success!";
			$ret->result->data = $r;
    		return $ret;
		}
	}

	public function pageGetUserMobileByUidArr($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params)) {
    		$ret->result->code = -101;
    		$ret->result->msg  = "invalid parameter";
    		return $ret;
		}
		$user_db = new user_db();
		$r= $user_db->getUserMobileByUidArr($params);
		if ($r===false) {
    		$ret->result->code = -102;
    		$ret->result->msg  = "the data is not found!";
    		return $ret;
		}else{
    		$ret->result->code = 0;
    		$ret->result->msg  = "success!";
			$ret->data = $r->items;
    		return $ret;
		}
	}

    public function pageupdateUserProfile($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "the data is empty!";
        $params=SJson::decode(utility_net::getPostData(),true);
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret -> result -> code = -2;
            $ret -> result -> msg = "invalid parameter";
            return $ret;
        }
        $uid=$inPath[3];
        $user_db=new user_db;
        if (!empty(user_db::getUserProfile($uid))) {
            $dbRet = user_db::updateUserProfile($uid, $params);
        } else {
            $params['fk_user'] = $uid;
            $dbRet = user_db::addUserProfile($params);
        }
        if($dbRet===false){
            $ret->result->code=-3;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }
	/**
	 * 这个方法只更新 last_updated,last_login
	 */
    public function pageUpdateUser($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "the data is empty!";
        $params=SJson::decode(utility_net::getPostData());
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret -> result -> code = -2;
            $ret -> result -> msg = "invalid parameter";
            return $ret;
        }
        if (empty($params->last_updated) && empty($params->last_login)){
            $ret -> result -> code = -3;
            $ret -> result -> msg = "only update last_updated or last_login";
            return $ret;
        }
        $uid=$inPath[3];
        $user_db=new user_db;
		$updates=array();
		if(!empty($params->last_updated)){$updates['last_updated'] = $params->last_updated;}
		if(!empty($params->last_login)){$updates['last_login'] = $params->last_login;}
        $dbRet = user_db::updateUser($uid, $updates);
        if($dbRet===false){
            $ret->result->code=-3;
            $ret->result->msg="db error!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }

    public  function pageListUsersByUserIds()
    {
        $idArr = SJson::decode(utility_net::getPostData(), true);
        $idStr = implode(',', $idArr);
        $res = user_db::listUsersByUserIds($idStr);

        if (!empty($res->items)) return api_func::setData($res->items);
        return api_func::setMsg(3002);
    }

    public function pageGetLists()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $res = common_user::userLists($params);

        if (!empty($res['data'])) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

	public function pagegetstudent($inpath){
        if (empty($inpath[3]) || !is_numeric($inpath[3])) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
		$user = $user_api->getstudent((int)$inpath[3]);
		if (empty($user)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);
		}
		return array(
			'data' => $user,
		);
	}	

	public function pagegetteacher($inpath){
        if (empty($inpath[3]) || !is_numeric($inpath[3])) {
			return array(
				'result' => array(
					'code' => -11,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
		$user = $user_api->getteacher((int)$inpath[3]);
		if (empty($user)) {
			return array(
				"code" => '-102',
				"msg" => 'the teacher does not exist',
			);
		}
		return array(
			'data' => $user,
		);
	}	

	
	public function msgMobile($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code=-1;
		if(empty($params->mobile) || empty($params->tpl_id) || empty($params->msg)){
			$ret->result->msg='参数错误';
			return $ret;
		}
		
		parse_str($params->msg,$msg_info);
		if(empty($msg_info)){
			$ret->result->msg='参数内容msg格式错误';
			return $ret;
		}

		if(!empty($msg_info['#code#'])){
			//判断IP限制
			$ct = verify_api::getVerifyCodeLogCt($params->mobile,"",$params->sender_ip);
			if($ct>10){
				$ret->result->msg="您的发送了太多的验证码，请15分钟后再试！";
				return $ret;
			}
		}
		$r = verify_api::sendSMS($params->mobile,$params->msg,$params->tpl_id);
		$ret->result->sms_code=$r->code;
		$ret->result->sms_msg=$r->msg;
		if(!empty($msg_info['#code#'])){
			//验证码服务，写验证码表
			$Verify = array();
			$Verify['code']=$msg_info['#code#'];
			$Verify['fk_user']=0;
			$Verify['mobile']=$params->mobile;
			$verify_db = new verify_db;
			$verifyId = $verify_db->addVerifyCode($Verify);
			$ret->result->verify_log=$verifyId;
			//记防刷日志
			verify_api::addVerifyCodeLog($params->mobile,$email="",$params->sender_ip);
		}
		$ret->result->code=$r->code;
		if($r->code!=0){
			$ret->result->msg=$r->msg;
		}
		return $ret;

	}
	
	public function pagegetmgrUser($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if (empty($params)){
			return array(
				'result' => array(
					'code' => -1,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
        $ret = $user_api->getmgrUser((int)$inPath[3], $params);
		return $ret;
	}
	
	public function pagemgrAddOrg($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if (empty($params)){
			return array(
				'result' => array(
					'code' => -1,
					'msg' => 'invalid parameter',
				),
			);
        }
		$user_api = new user_api;
        $ret = $user_api->mgrAddOrg($params);
		return $ret;
	}
	public function pageCheckName($inPath){

		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		if(empty($params->nickname)){
			$ret->code = -2;
			$ret->msg  = 'nickname is empty';
		}else{
			$user_db = new user_db;
			$check_ret = $user_db->checkName($params->uid,$params->nickname);	
			if(!empty($check_ret)){
				$ret->code = -1;
				$ret->msg = 'name reuse';
			}else{
				$ret->code = 0;
				$ret->msg  = 'success';
			}
		}
		return $ret;
	}

	public function pagegetUserProfileByUidArr($inPath){
		//$uidArr = array("153","159");
		$params=SJson::decode(utility_net::getPostData());
		$uidArr  = array();
		foreach($params->uids as $k=>$v){
			$uidArr[] = $v;
		}
		$ret = new stdclass;
		$user_api = new user_api;
		$user = $user_api->getUserProfileByUidArr($uidArr);
		if (empty($user)) {
			return array(
				"code" => '-102',
				"msg" => 'the user does not exist',
			);
		}
		return $user;
	}
	public function pagelistUserIdsBylikeMobileArr($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uids)){ 
			return array(
				"code" => -1,
				"msg" => 'empty uids ',
			);
		}
		$uidsArr  = array();
		$mobile = $params->mobile;
		foreach($params->uids as $k=>$v){
			$uidsArr[] = $v;
		}
/*
		$uidArr = array(
			"153",
			"159",
			"152",
			"138",
			"148",
			"496",
			"497",
			"498",
			"499",
		);
		$mobile = "13";

*/
		$user_api = new user_api;
		$user_list = $user_api->listUserIdsBylikeMobileArr($uidsArr, $mobile);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}

	public function pagelistUserIdsBylikeNameArr($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uids)){ 
			return array(
				"code" => -1,
				"msg" => 'empty uids ',
			);
		}
		$uidsArr  = array();
		$real_name = $params->real_name;
		foreach($params->uids as $k=>$v){
			$uidsArr[] = $v;
		}
/*
		$uidsArr = array(
			"153",
			"159",
			"152",
			"138",
			"148",
			"496",
			"497",
			"498",
			"499",
		);
		$real_name = "彬彬";
*/

		$user_api = new user_api;
		$user_list = $user_api->listUserIdsBylikeNameArr($uidsArr, $real_name);
		if (empty($user_list)) {
			return array(
				"code" => -202,
				"msg" => 'empty data ',
			);
		}
		return $user_list;
	}
    
   	public function pageCheckPassword($inPath){
		if (empty($inPath[3])) return api_func::setMsg(1000);
		
        return api_func::setData(
			[
				'str' => user_api::encryptPassword($inPath[3])
			]
		);
    }
	
	/**
	 *批量获取用户真实姓名手机号昵称等信息
	 */
	public function pageGetUserInfoByUidArr($inPath){
		$ret = new stdclass;
		$ret->data = '';
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params['uidArr'])) {
    		$ret->code = -101;
    		$ret->msg  = "invalid parameter";
    		return $ret;
		}
		$user_db = new user_db();
		$r= $user_db::getStudentUsers($params['uidArr']);
		if (empty($r)) {
    		$ret->code = -102;
    		$ret->msg  = "the data is not found!";
    		return $ret;
		}else{
    		$ret->code = 0;
    		$ret->msg  = "success!";
			$ret->data = $r;
    		return $ret;
		}
	}

	public function pageGetUserInfoByMobile($inPath)
	{
		if (empty($inPath[3]) || !utility_valid::mobile($inPath[3])) {
			return api_func::setMsg(2012);
		}

		$res = user_db_userDao::getUserInfoByMobile($inPath[3]);

		if (!empty($res)) return api_func::setData($res);

		return api_func::setMsg(3002);
	}

	public function pageUpdateUserSource()
	{
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params['userId']) || empty($params['source']))
			return api_func::setMsg(1000);

		if (user_db_userDao::updateUserSource($params['userId'], $params['source']))
			return api_func::setMsg(0);

		return api_func::setMsg(1);
	}

	public function pageSearchUserByKeyword()
	{
		$params = SJson::decode(utility_net::getPostData(),true);
		if (empty($params['keyword']) || empty($params['keyword']))
			return api_func::setMsg(1000);

		$res = user_db_userDao::searchUserByKeyword($params['keyword']);
		if (!empty($res->items))
			return api_func::setData($res->items);

		return api_func::setMsg(3002);
	}
    /*
     * 获取用户引导信息
     * @param $uid,$gid
     * @return array
     * @author Panda <zhangtaifeng@gn100.com>
     */
	public function pageGetUserGuideByUid($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
        if(empty($inPath[3]) || empty($inPath[4])){
            $ret->result->code=-1; 
            $ret->result->msg='param error';
            return $ret;
        }
		$user_db = new user_db();
		$r= $user_db::getUserGuideByUid($inPath[3],$inPath[4]);
		if (empty($r)) {
    		$ret->result->code = -2;
    		$ret->result->msg  = "the data is not found!";
    		return $ret;
		}
        $ret->result->code = 0;
        $ret->result->msg  = "success!";
        $ret->data = $r;
        return $ret;
	}
    /*
     * 添加用户引导信息
     * @param $params
     * @return array
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageAddUserGuide($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $data=SJson::decode(utility_net::getPostData(),true);
        if(empty($data['uid'])||empty($data['gid'])){
            $ret -> result -> code = -1;
            $ret -> result -> msg = "the data is empty!";
            return $ret;
        }
        $params=array(
                'fk_user'=>$data['uid'], 
                'fk_guide'=>$data['gid'], 
                'status'=>!empty($data['status'])?$data['status']:0, 
                'show_count'=>!empty($data['show_count'])?$data['show_count']:1, 
            );
        $db=new user_db;
        $r=$db->addUserGuide($params);
        if($r===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
            return $ret;
        }
        $ret->result->code=0;
        $ret->result->msg="success!";
        return $ret;
    }
    public function pageupdateUserGuide($inPath){
        $ret = new stdclass;
		$ret->result =  new stdclass;
        $data=SJson::decode(utility_net::getPostData(),true);
        if (empty($inPath[3]) || empty($inPath[4]) || empty($data)) {
            $ret -> result -> code = -1;
            $ret -> result -> msg = "the data is empty!";
            return $ret;
        }
        $params=array(
                'status'=>!empty($data['status'])?$data['status']:0, 
                'show_count'=>!empty($data['show_count'])?$data['show_count']:1,
				'last_updated'=>date("Y-m-d H:i:s") 
            );
        $db=new user_db;
        $r=$db->updateUserGuide($inPath[3],$inPath[4],$params);
        if($r===false){
            $ret->result->code=-2;
            $ret->result->msg="db error!";
            return $ret;
        }
        $ret->result->code=0;
        $ret->result->msg="success!";
        return $ret;
    }
	public function pageLikeMobile($inPath)
	{
		if (empty($inPath[3])) {
			return api_func::setMsg(1000);
		}
		$res = user_db::geteUserIdByLikeMobile($inPath[3]);
		if (!empty($res)) return api_func::setData($res);
		return api_func::setMsg(3002);
	}
	
	public function pageLikeName($inPath)
	{
		if (empty($inPath[3])) {
			return api_func::setMsg(1000);
		}
		$res = user_db::geteUserIdByLikeName($inPath[3]);
		if (!empty($res)) return api_func::setData($res);
		return api_func::setMsg(3002);
	}


	public function pageUserSubdomain($inPath)
	{
		if (empty($inPath[3])) {
			return api_func::setMsg(1000);
		}
		$res = user_db_userSubdomainDao::UserSubdomain($inPath[3]);
		if (!empty($res)) return api_func::setData($res);
		return api_func::setMsg(3002);
	}
}
