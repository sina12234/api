<?php
class message_chat{
	public function __construct($inPath){
		return;
	}

	/**
	 * @deprected
	 * 上课、下课信号
	 **/
	public function pageClass($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";

		if(empty($params->class) || empty($params->plan_id) || empty($params->user_id) || empty($params->user_token)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}

		if("start" == $params->class){
			$isStart = true;
            redis_api::useConfig("class");
            redis_api::publishAll("class", "$params->plan_id");
		}else{
			$isStart = false;
		}
		$db_ret = message_api::startCloseClass($params->plan_id, $params->user_id, $params->user_token, $isStart);
		if($db_ret){
			$ret->result->code = 0;
			$ret->data = $db_ret;
		}
		return $ret;
	}
	/**
	 * 检查用户是否禁言
	 **/
	public function pageCheckForbid($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($params->plan_id) || empty($params->user_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$message_db = new message_db;
		$data = $message_db->getSingleForbid($params->plan_id, $params->user_id);
		if("forbid" == $data["status"]){
			$ret->result->msg = "用户被禁言了";
		}else{
			$ret->result->code=0;
			$ret->result->msg="未被禁言";
		}
		return $ret;
	}
	/**
	 * 更新消息里的时间
	 */
	public function pageUpdateLiveSecond($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($params->plan_id) || empty($params->start) || empty($params->addTime)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$message_db = new message_db;
		$data = $message_db->updateTextLiveSecond($params->plan_id, $params->start, $params->addTime);
		$message_http = new message_http;
		$message_http->clearPlanMessage($params, "text");
		$ret->result->code=0;
		$ret->data = $data;
		return $ret;
	}
	/**
	 * 提供增加text,good的接口(给golang message.plan调用)
	 */
	public function pageAddMsgV2($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result=false;
		$ret->code=-1;
		$ret->code_msg="";
		$ret->message_type="";
		$ret->type=0;
		$ret->msg_id=0;

		$ret->uf_n="";//UserFromName="";
		$ret->uf_t="";//UserFromThumb="";
		$ret->uf_l=0;//UserFromLevelTitle="";
		$ret->uf_lt="";//UserFromLevelTitle="";

		$ret->ut_n="";//UserToName="";
		$ret->ut_t="";//UserToThumb="";
		$ret->ut_l=0;//UserToLevelTitle="";
		$ret->ut_lt="";//UserToLevelTitle="";

		if(empty($params->type) ||
			empty($params->plan_id) ||
			empty($params->user_from_id) ||
			empty($params->user_from_token)
		){
			$ret->code_msg = "缺少参数";
			return $ret;
		}

		$ret->message_type = $message_type = message_type::typeCategory($params->type);
		$ret->type= (int)($params->type);

		//判断用户token
		$token = user_api_token::getToken($params->user_from_token);
		if(empty($token['user_id']) || ($token['user_id'] != $params->user_from_id)){
			$ret->code=-2;
			$ret->code_msg = "用户ID和用户Token不匹配";
			return $ret;
		}
		//判断plan
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(empty($plan)){
			$ret->code_msg = "没有这个班级";
			return $ret;
		}
		//判断是否报名
		$apply=false;//是否报名
                //组管理员
                $group_teacher_list=array();
                $classid=$plan['class_id'];
                $group_teacher_list_tmp=$course_db->getclassTeachers(array('fk_class'=>$classid));
                if(!empty($group_teacher_list_tmp->items)){
                    $group_teacher_list=array_column($group_teacher_list_tmp->items,'group_teacher_id');
                }
		if(course_api::verifyPlan($params->user_from_id,$params->plan_id,$apply,$video_trial)===false && !in_array($params->user_from_id,$group_teacher_list) ){
			$ret->code=-3;
			$ret->code_msg = "用户没有报名没有发言权限";
			return $ret;
		}

		$message_db = new message_db;
		if(message_type::text == $params->type){
			if($plan["user_id"] != $params->user_from_id && $plan["user_plan_id"] != $params->user_from_id && !in_array($params->user_from_id,$group_teacher_list) ){
				$data = $message_db->getSingleForbid($params->plan_id, $params->user_from_id);
				if("forbid" == $data["status"]){
					$ret->code_msg = "用户被禁言了";
					return $ret;
				}
			}
			$pattern = $message_db->getSetting($params->plan_id);
			if($pattern){
				$p = $pattern["text_pattern"];
				if("normal" != $p){
					if($plan["user_id"] != $params->user_from_id && $plan["user_plan_id"] != $params->user_from_id && !in_array($params->user_from_id,$group_teacher_list) ){
						if("reply" == $p){
							//答题模式等会修改真实的类型，TODO 应该避免这样恶心的逻辑 1/2
							$params->type = message_type::reply_text;
						}else{
							$ret->code_msg = "禁言模式不允许学生发言";
							return $ret;
						}
					}
				}
			}
			if($plan["user_id"] != $params->user_from_id && $plan["user_plan_id"] != $params->user_from_id && $apply===false && !in_array($params->user_from_id,$group_teacher_list) ){
				$ret->code_msg = "不是老师，后者没有报名，不能发消息";
				$ret->code=-9;
				return $ret;
			}
		}
		if(message_type::text == $params->type && !empty($params->content)){
			$content = strip_tags($params->content);
			if(mb_strlen($content, "utf8") > 200){
				$ret->code_msg = "输入内容过长";
				return $ret;
			}
			$params->content = $content;
		}
		if(empty($params->user_to_id) && message_type::isAutoSetUserTo($params->type)){
			$params->fk_user_to = $plan["user_plan_id"];
		}
		if(message_type::onlyTeacher($params->type)){
			if($plan["user_plan_id"] != $params->user_from_id && !in_array($params->user_from_id,$group_teacher_list) ){
				$ret->code_msg = "类型[$params->type]消息只有教师才可以发送";
				return $ret;
			}
		}
        if(message_type::start_close == $params->type){
            if("start" == $params->content){
                redis_api::useConfig("class");
                redis_api::publishAll("class", "$params->plan_id");
            }
        }
		if(message_type::single_notalk == $params->type){
			$data = new stdclass;
			$data->fk_plan = $params->plan_id;
			$data->fk_user = $params->user_to_id;
			$data->status = $params->content;
			$message_db->addSingleForbid($data);
			$log_db = new log_db;
			$log_db->addForbidLog($data);
		}
		if(message_type::ask_cancel == $params->type && "cancel" == $params->content){
			live_api::closeChat($params->user_from_id, $params->plan_id);
		}else if(message_type::agree_refuse == $params->type && ("agree" == $params->content || "asking" == $params->content)){
			live_api::allowChat($params->user_to_id, $params->plan_id, $params->user_to_token);
		}else if(message_type::agree_refuse == $params->type && "stop" == $params->content){
			live_api::closeChat($params->user_to_id, $params->plan_id);
		}
		$message_http = new message_http;
		if(message_type::delete_text == $params->type){
			$message_db->deleteText($params->plan_id,$params->content);
			$message_http->clearPlanMessage($params, "text");
		}
		if("text" == $message_type){
			$db_ret = $message_db->addText($params);
		}else if("good" == $message_type){
			$db_ret = $message_db->addGood($params);
			$score_info = user_api::addUserScore($params->user_to_id, "ZAN");
			if($score_info->code == 0){//增加了分才广播
				$message_http->addSignalForScore($params->user_to_id, $params->plan_id, $score_info);
			}
		}else if("signal" == $message_type){
			//在golang 里已经增加了信号了
			$db_ret = 1;
		}
		$pattern = 0;
		if($params->type == message_type::pattern){
			if("normal" == $params->content){
				$pattern = "normal";
			}else 	if("reply" == $params->content){
				$pattern = "reply";
			}else	if("notalk" == $params->content){
				$pattern = "notalk";
			}
			if($pattern){
				$data2 = new stdclass;
				$data2->fk_plan = $params->plan_id;
				$data2->text_pattern = $pattern;
				$message_db->addSetting($data2);
			}
		}

		//返回调整后的type
		//答题模式等会修改真实的类型，TODO 应该避免这样恶心的逻辑 2/2
		$ret->type= (int)($params->type);

		//补全用户信息
		$user_db = new user_db ;
		if(!empty($params->user_from_id)){
			$info= $user_db->getUserLevelByUidV2($params->user_from_id);

			if(!empty($info)){
				$ret->uf_n=$info['name'];
				$ret->uf_t=$info['thumb_med'];
				$ret->uf_l=(int)($info['level']);
				$ret->uf_lt=$info['title'];
			}
		}
		if(!empty($params->user_to_id)){
			$info= $user_db->getUserLevelByUidV2($params->user_to_id);

			if(!empty($info)){
				$ret->ut_n=$info['name'];
				$ret->ut_t=$info['thumb_med'];
				$ret->ut_l=(int)($info['level']);
				$ret->ut_lt=$info['title'];
			}
		}

		if($db_ret){
			$ret->code = 0;
			$ret->result=true;
			$ret->msg_id = (int)($db_ret);
		}
		return $ret;
	}
	//专门给何涛go server用的接口
	public function pageGetText($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id)){
			return array();
		}
		$message_db = new message_db;
		$plan_id = (int)($params->plan_id);
		$data = $message_db->getText($plan_id, 0);
		if(empty($data)){
			return array();
		}
		$result = array();

		//获取用户信息
		$uids = array();
		foreach($data as $item){
			if(0 != $item["type"]){
				$uid = (int)($item["user_to_id"]);
				if($uid) $uids[$uid] = $uid;
				$uid = (int)($item["user_from_id"]);
				if($uid) $uids[$uid] = $uid;
			}
		}
		$user_db = new user_db ;
		$uids_info=array();
		$infos= $user_db->getUserLevelByUids($uids);
		if(!empty($infos)){
			foreach($infos as $info){
				$uid = $info['user_id'];
				$uids_info[$uid]=$info;
			}
		}

		//补全用户信息
		foreach($data as $item){
			if(0 != $item["type"]){
				$v = array();
				$v["MessageType"] = "text";
				$v["PlanId"] = $plan_id;
				$v["MessageId"] = (int)($item['msg_id']);

				$uid = (int)($item["user_from_id"]);
				$v["UserIdFrom"] = $uid;
				if(isset($uids_info[$uid])){
					$v['UserFromName'] = $uids_info[$uid]['name'];
					$v['UserFromThumb'] = $uids_info[$uid]['thumb_med'];
					$v['UserFromLevel'] = (int)($uids_info[$uid]['level']);
					$v['UserFromLevelTitle'] = $uids_info[$uid]['title'];
				}

				$uid = (int)($item["user_to_id"]);
				$v["UserIdTo"] = $uid;
				if(isset($uids_info[$uid])){
					$v['UserToName'] = $uids_info[$uid]['name'];
					$v['UserToThumb'] = $uids_info[$uid]['thumb_med'];
					$v['UserToLevel'] = (int)($uids_info[$uid]['level']);
					$v['UserToLevelTitle'] = $uids_info[$uid]['title'];
				}

				$v["Content"] = $item["content"];
				$v["ContentType"] = (int)($item["type"]);
				$v["LiveSecond"] = (int)($item["live_second"]);
				$v["LastUpdate"] = $item["last_updated"];
				$result[] = $v;
			}
		}
		return $result;
	}
	/**
	 * 获取100个点赞用户数
	 */
	public function pageGetGood($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->plan_id)){
			return array();
		}
		$message_db = new message_db;
		$plan_id = (int)($params->plan_id);
		$data = $message_db->getGood($plan_id, 0);
		if(empty($data)){
			return array();
		}
		//获取用户信息
		$uids = array();
		foreach($data as $item){
			if(0 != $item["type"]){
				$uid = (int)($item["user_id"]);
				if($uid) $uids[$uid] = $uid;
			}
		}
		$user_db = new user_db ;
		$uids_info=array();
		$infos= $user_db->getUserLevelByUids($uids);
		if(!empty($infos)){
			foreach($infos as $info){
				$uid = $info['user_id'];
				$uids_info[$uid]=$info;
			}
		}
		$result = array();
		foreach($data as $item){
			$v = array();
			$v["MessageType"] = "good";
			$v["MessageId"] = (int)($item['msg_id']);
			$v["PlanId"] = $plan_id;

			$uid = (int)($item["user_id"]);
			$v["UserIdTo"] = $uid;
			if(isset($uids_info[$uid])){
				$v['UserToName'] = $uids_info[$uid]['name'];
				$v['UserToThumb'] = $uids_info[$uid]['thumb_med'];
				$v['UserToLevel'] = (int)($uids_info[$uid]['level']);
				$v['UserToLevelTitle'] = $uids_info[$uid]['title'];
			}
			$v["Content"] = "".$item["num"];
			$v["ContentType"] = (int)($item["type"]);
			$v["LastUpdate"] = $item["last_updated"];
			$result[] = $v;
		}
		return $result;
	}
	/**
	 * 获取禁言的所有学生
	 */
	public function pageGetSingleForbidByPlan($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		if(empty($params->plan_id) || empty($params->user_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		if($plan["user_id"]!=$params->user_id && $plan["user_plan_id"]!=$params->user_id){
			$ret->result->msg = "用户不是这个课程的老师";
			return $ret;
		}
		$message_db = new message_db;
		$data = $message_db->getSingleForbidByPlan($params->plan_id);
		$ret->result->code = 0;
		if(empty($data->items)){
			$ret->result->msg = "没有内容";
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}
	/**
	 * 获取禁言模式
	 */
	public function pageCheckPatternByPlan($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		if(empty($params->plan_id)){
			$ret->result->msg = "缺少参数";
			return $ret;
		}
		$course_db = new course_db;
		$plan = $course_db->getPlan($params->plan_id);
		if(!$plan){
			$ret->result->msg = "没有这个班级";
			return $ret;
		}
		$message_db = new message_db;
		$data = $message_db->getSetting($params->plan_id);
		$ret->result->code = 0;
		if(empty($data)){
			$ret->result->msg = "没有内容";
			return $ret;
		}
		$ret->data = $data;
		return $ret;
	}
}
