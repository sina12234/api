<?php
class message_api{
	/**
	 * 上课、下课信号
	 **/
	public static function startCloseClass($plan_id, $user_id, $user_token, $isStart){
		$params = new stdclass;
		$params->plan_id = $plan_id;
		$params->message_type = "signal";
		$params->type = message_type::start_close;
		if($isStart){
			$params->content = "start";
		}else{
			$params->content = "close";
			//课程结束时将课堂统计需要的plan id存入redis
			stat_db::planIdAddRedis($plan_id);
		}
		$params->user_from_id = 0;
		$params->user_from_token = "";
		$message_http = new message_http;
		$ret = $message_http->addPlanMsg($params);
		return message_api::setPattern($plan_id, "normal");
	}
	/**
	 * 设置禁言模式
	 */
	public static function setPattern($plan_id, $pattern){
		if("normal"!=$pattern && "reply"!=$pattern && "notalk"!=$pattern){
			return false;
		}
		$params = new stdclass;
		$params->plan_id = $plan_id;
		$params->message_type = "signal";
		$params->type = message_type::pattern;
		$params->content = $pattern;
		$params->user_from_id = 0;
		$params->user_from_token = "";
		$message_http = new message_http;
		$message_http->addPlanMsg($params);
		$message_db = new message_db;
		$data = new stdclass;
		$data->fk_plan = $plan_id;
		$data->text_pattern = $pattern;
		$message_db->addSetting($data);
		return true;
	}

	//目前给一个class_id的所有plan都发消息，以后可以优化
	/**
	 * 新学生报名发信号/或者调班发信号
	 */
	public static function modifyStudent($class_id, $user_id, $isAdd){
		$course_db = new course_db;
		$plans = $course_db->getPlansByClassId($class_id);
		if(!$plans){
			return;
		}
		$params = new stdclass;
		//$params->plan_id = $plan_id;
		$params->message_type = "signal";
		$params->type = message_type::modify_student;
		if($isAdd){
			$params->content = "add";
		}else{
			$params->content = "delete";
		}
		$params->user_from_id = $user_id;
		$params->user_from_token = "";
		$message_http = new message_http;
		foreach($plans as $plan){
			$params->plan_id = $plan["plan_id"];
			$wsg = $message_http->addPlanMsg($params);
		}
	}

    public static function add($data)
    {
        $source  = message_type::SOURCE_WEB;
        $msgType = !empty($data['msgType']) ?$data['msgType'] : message_type::SYSTEM_CONTACT_INFORMATION; // default chat type

        $userFrom = 0;
        isset($data['userFrom']) && $userFrom = (int)$data['userFrom'];

        if (!isset($data['userTo']) || !(int)($data['userTo'])) {
            SLog::fatal('userToId is empty,params[%s]', var_export($data, 1));
            return false;
        }
        $userTo = (int)($data['userTo']);

        if (!isset($data['content']) || !strlen(trim($data['content']))) {
            SLog::fatal('content is empty,params[%s]', var_export($data, 1));
            return false;
        }
        $content = trim($data['content']);

        $title = '';
        if (isset($data['title']) && strlen(trim($data['title'])))
            $title = trim($data['title']);

        if (isset($data['msgType']) && in_array($data['msgType'], message_type::$messageType, true))
            $msgType = $data['msgType'];

        if (isset($data['source']) && in_array($data['source'], message_type::$source, true))
            $source = $data['source'];

        $data = [
            'fk_user_from' => $userFrom,
            'fk_user_to'   => $userTo,
            'content'      => htmlentities($content),
            'title'        => $title,
            'source'       => $source,
            'message_type' => $msgType,
            'status'       => 'unread'
        ];

        $res = message_db_dialogDao::add($data);
        if ($res) {
            // insert t_message_user_text_gather
            $insertData = [
                'fk_user_to'   => $userTo,
                'fk_user_from' => $userFrom,
                'last_message' => htmlentities($content),
                'message_num'  => 1,
                'message_type' => $msgType,
                'is_top'       => 0,
                'is_remind'    => 1,
                'status'       => 'unread',
                'create_time'  => date('Y-m-d H:i:s')
            ];

            $updateData = [
                'last_message' => htmlentities($content),
                'message_num=message_num+1',
                'create_time'  => date('Y-m-d H:i:s'),
                'status'       => 'unread'
            ];

            if (message_db_messageUserTextGatherDao::add($insertData, $updateData) === false) {
                SLog::fatal('insert t_message_user_text_gather failed,params[%s]', var_export($insertData, 1));
            }

            return $res;
        }

        return false;
    }
}
