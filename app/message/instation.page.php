<?php
class message_instation{
	public function __construct($inPath){
	}
	/**
	 * 获取没有读取的消息，可以缓存1分钟
	 */
	public function pageGetUnreadInstationNum($inPath){
		utility_cache::pageCache(60);
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$ret->total = 0;
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->user_id)){
			return $ret;
		}
		$messageType = isset($params->messageType)?$params->messageType:array();
		$ret->total = message_db_dialogDao::getDialogLastTotal($params->user_id,$messageType);
		$ret->result->code = 0;
		return $ret;
	}


	public function pagegetSendMsg($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$data = array();
		$message_db = new message_db;
		$data['userTo'] = $inPath[3];
		$data['title'] = "资料信息修改";
		$subname_info = user_db::getOrgProfileByUid($data['userTo']);
		$fkOrg = !empty($subname_info['fk_org']) ? $subname_info['fk_org'] : '';
		$fkUser = $data['userTo'];
		$allManage = user_db::getAllOrgManage($fkOrg,$fkUser);
		$fkUserArr = array();
		if(!empty($allManage->items)){
			foreach($allManage->items as $k=>$v){
				$fkUserArr[]=!empty($v['fk_user']) ? $v['fk_user'] : 0;
			}
		}
		$fkUserArr = array_unique($fkUserArr);
		$subname=!empty($subname_info['subname']) ? $subname_info['subname'] : '';
		$orgInfo = user_db::getmgrSubmain($data['userTo']);
		$subNameInfo = !empty($orgInfo['subdomain']) ? $orgInfo['subdomain'] : '';
		$pos = strpos($subNameInfo, ".com");
		if($pos===false){
			$subdomain = $subNameInfo.".gn100.com";
		}else{
			$subdomain = $subNameInfo;
		}
		$msgType = message_type::ORG_DATA_INFO_VERIFY;
		$data['msgType'] = $msgType;
		if(!empty($fkUserArr)){
			foreach($fkUserArr as $k=>$v){
				$data['userTo']= $v;
				if($params->tmp_status==-1){
					$data['content'] = "很抱歉，“".$subname."”机构资料审核修改没有通过，具体原因:".$params->content."<a href='http://".$subdomain."/org.main.info?act=edit'>【请去机构管理中查看】</a>";
				}elseif($params->tmp_status==1){
					$data['content'] = "恭喜您，“".$subname."”机构资料审核修改已经通过，<a href='http://".$subdomain."/org.main.info'>【请去机构管理中查看】</a>";
				}
				$dbRet = message_api::add($data);
			}
		}
		if($dbRet){
			$ret->result->code = 0;
			$ret->result->data = $dbRet;
		}
		return $ret;
	}

	public function pageRemindClass($inPath){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->user) || empty($params->weixin) || empty($params->heading) || empty($params->text)){
			$ret->result->msg="缺少参数";
			return $ret;
		}

		$user_db = new user_db;
		$parterner = $user_db->getUserParternerByUId(2, $params->user);
		if($parterner){
			$info = SJson::decode($parterner["parterner_uinfo"]);
			$weixin = weixin_api::sendCustomTextMessage($info->openid, $params->weixin, $result);
			$log_text = "remindclass(".date("Y-m-d H:i:s")."): content=[$params->weixin] text=[$params->text] user=[$params->user] name=[".$parterner["nickname"]."] result=[".var_export($result, true)."] weixin=[".var_export($weixin, true)."]";
			$log_text = str_replace(array("\r", "\n", "\r\n"), " ", $log_text);
			error_log($log_text."\n", 3, "/tmp/remindclass.log_".date("Y-m-d"));
			if(!empty($weixin)){
				$ret->result->code = 0;
				return $ret;
			}
		}

		$data = [
			'userFrom' => 0,
			'userTo'   => $params->user,
			'content'  => $params->text,
			'title'    => $params->heading,
			'source'   => message_type::SOURCE_WEI_XIN,
			'msgType'  => message_type::SYSTEM_CLASS_REMIND,
		];
		if (message_api::add($data) === false) {
			SLog::fatal('remind class add instation failed,params[%s]', var_export($data, 1));
		}

		$ret->result->code = 0;
		return $ret;
	}
	
	public function pagegetMgrOrgSendMsg($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$data = array();
		$message_db = new message_db;
		$data['userTo'] = $inPath[3];
		$data['title'] = "机构入驻审核";
		$msgType = message_type::ORG_JOIN_VERIFY;
		$data['msgType'] = $msgType;
		$orgInfo = user_db::getOrgSubdomain($data['userTo']);
		$subname_info = user_db::getOrgProfileByUid($data['userTo']);
		$subname=!empty($subname_info['subname']) ? $subname_info['subname'] : '';
		$subdomain = !empty($orgInfo['subdomain']) ? $orgInfo['subdomain'] : '';
		if($params->verify_status==-1){
			$data['content'] = "您的“".$subname."”机构入驻申请没有通过，具体原因:".$params->content."<a href='/index.join.step4'>【请重新申请】</a>";
		}elseif($params->verify_status==1){
			$data['content'] = "您的“".$subname."”机构入驻申请成功通过，<a href='http://".$subdomain."/org' target='_blank'>【请去机构管理中查看】</a>";
		}elseif($params->verify_status==-2){
			$data['content'] = "您的机构入驻申请已冻结,具体原因:";
		}
		$dbRet = message_api::add($data);
		if($dbRet){
			$ret->result->code = 0;
			$ret->result->data = $dbRet;
		}
		return $ret;
	}
	
	public function pageMgrSettleSendMsg($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->code=-1;
		$ret->msg="add is faild";
		$data = array();
		$user = isset($inPath[3])?$inPath[3]:0;
		if(empty($params->msgtype) || empty($user)){
			$ret->msg = "params is error";
			return $ret;
		}
		$data['userTo'] = $inPath[3];
		$data['title'] = $params->title;
		$data['content'] = $params->content;
		$data['msgType'] = $params->msgtype;
		$dbRet = message_api::add($data);
		if($dbRet){
			$ret->code = 0;
			$ret->data = $dbRet;
		}
		return $ret;
	}
}

