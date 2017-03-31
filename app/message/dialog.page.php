<?php

class message_dialog
{

    public function pageLists()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $maxId  = 0;

        if (empty($params['userToId'])) return api_func::setMsg(1000);
        !empty($params['maxId']) && $maxId = $params['maxId'];
        $page   = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length = isset($params['length']) && $params['length'] ? $params['length'] : 500;
		$messageType = isset($params['messageType']) ? $params['messageType'] : array();
        $res = message_db_dialogDao::lists($params['userToId'], $maxId, $page, $length,$messageType);

        if (!empty($res)) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

    public function pageAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $res = message_api::add($params);
        if ($res) return api_func::setData($res);

        return api_func::setMsg(1);
    }

    public function pageUpdate()
    {
        $params   = SJson::decode(utility_net::getPostData(), true);
        $userToId = '';

        if (empty($params['msgId'])) return api_func::setMsg(1000);

        if (isset($params['userToId']) && $params['userToId']) {
            $userToId = $params['userToId'];
        }

        $status = 'readed';
        if (isset($params['action']) && $params['action'] == 'delete') {
            $status = 'delete';
        }
        if (message_db_dialogDao::update($params['msgId'], $userToId, $status)) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageUpdateMessage()
    {
        $params   = SJson::decode(utility_net::getPostData(), true);
        $userToId = '';

        if (empty($params['userFromId'])) return api_func::setMsg(1000);

        if (isset($params['userToId']) && $params['userToId']) {
            $userToId = $params['userToId'];
        }

        $status = 'readed';
        if (isset($params['action']) && $params['action'] == 'delete') {
            $status = 'delete';
        }

        if (message_db_dialogDao::updateByUserFromId($params['userFromId'], $userToId, $status)) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }
	
	public function pageUpdateAllMessage()
    {
        $params   = SJson::decode(utility_net::getPostData(), true);
        $userToId = $params['userToId'];
		$messageType = $params['messageType'] ? (is_array($params['messageType'])? implode(',',$params['messageType']):$params['messageType']):"";
        if (empty($userToId)) return api_func::setMsg(1000);
        $status = 'readed';
        if (isset($params['action']) && $params['action'] == 'delete') {
            $status = 'delete';
        }
		$re1 = message_db_dialogDao::updateByUserToId($userToId, $messageType,$status);
		$re2 = message_db_messageUserTextGatherDao::updateAll($userToId,$messageType,$status);
        if ($re1 && $re2){
			return api_func::setMsg(0);
		}
        return api_func::setMsg(1);
    }

    public function pageGetDialogLastTotalList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (empty($params['uid'])) return api_func::setMsg(1000);

        $page   = isset($params['page']) && (int)$params['page'] ? (int)$params['page'] : 1;
        $length = isset($params['length']) && (int)$params['length'] ? (int)$params['length'] : 20;

        $res = message_db_dialogDao::getDialogLastTotalList($params['uid'], $page, $length);

        if (!empty($res)) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

    public function pageChatSingle()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r = api_func::isValidId(['userFrom', 'userToId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $maxId = 0;
        if (!empty($params['maxId'])) {
            $maxId = (int)($params['maxId']);
        }

        $page   = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length = isset($params['length']) && $params['length'] ? $params['length'] : 500;

        $res = message_db_dialogDao::chatSingle($r['userFrom'], $r['userToId'], $maxId, $page, $length);

        if (!empty($res->items)) {
            // update t_message_user_text message status into read
            if (message_db_dialogDao::chatMsgUpdateRead($r['userFrom'], $r['userToId']) === false) {
                SLog::fatal('update t_message_user_text message status into read failed,params[%s]', var_export($params, 1));
            }

            // update t_message_user_text_gather message status into read
            if (message_db_messageUserTextGatherDao::chatMsgUpdateRead($r['userFrom'], $r['userToId']) === false) {
                SLog::fatal('update t_message_user_text_gather message status into read failed,params[%s]', var_export($params, 1));
            }

            return api_func::setData($res->items);
        }

        return api_func::setMsg(3002);
    }

    public function pageGetLatestUser()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r = api_func::isValidId(['userToId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $page   = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length = isset($params['length']) && $params['length'] ? $params['length'] : 500;

        $res = message_db_messageUserTextGatherDao::getLatestUser($r['userToId'], $page, $length);

        if (!empty($res->items)) return api_func::setData($res->items);

        return api_func::setMsg(3002);
    }

}

