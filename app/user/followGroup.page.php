<?php

class user_followGroup
{
    // ====================follow group action===========================

    /**
     * t_user_follow_group name update
     */
    public function pageChangeName()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if ((!isset($params['groupId']) || !(int)$params['groupId'])) return api_func::setMsg(1000);
        if ((!isset($params['groupName']) || !$params['groupName'])) return api_func::setMsg(1000);
        if ((!isset($params['userId']) || !$params['userId'])) return api_func::setMsg(1000);

        if (mb_strlen($params['groupName'], 'UTF-8') > 20) {
            return api_func::setMsg(1043);
        }

        $data = [
            'group_name' => $params['groupName']
        ];

        if (user_db_userFollowGroupDao::update($params['userId'], $params['groupId'], $data)) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    /**
     * t_user_follow_group del by pk_msg_user_group
     */
    public function pageDel()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if ((!isset($params['groupId']) || !(int)$params['groupId'])) return api_func::setMsg(1000);
        if ((!isset($params['userId']) || !$params['userId'])) return api_func::setMsg(1000);

        if (user_db_userFollowGroupDao::del($params['userId'], $params['groupId'])) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    /**
     * t_user_follow_group add
     */
    public function pageAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if ((!isset($params['userId']) || !(int)$params['userId'])) return api_func::setMsg(1000);
        if ((!isset($params['groupName']) || !$params['groupName'])) return api_func::setMsg(1000);

        $data = [
            'fk_user'        => $params['userId'],
            'group_name'     => $params['groupName'],
            'sort'           => 0,
            'group_user_num' => 0,
            'create_time'    => date('Y-m-d H:i:s')
        ];

        $res = user_db_userFollowGroupDao::add($data);
        if ($res) return api_func::setData(['groupId' => $res]);

        return api_func::setMsg(1);
    }

    /**
     * get group list
     */
    public function pageList($inPath)
    {
        if ((!isset($inPath[3]) || !(int)($inPath[3]))) return api_func::setMsg(1000);
        $page = 1;
        $length = 500;
        if (isset($inPath[4]) && (int)($inPath[4])) $page = (int)($inPath[4]);
        if (isset($inPath[5]) && (int)($inPath[5])) $length = (int)($inPath[5]);

        $groupData = [];
        $list = user_db_userFollowGroupDao::groupList($inPath[3], $page, $length);
        if (!empty($list->items)) {
            $groupData = $list->items;
        }

        /*$LatestUserRes = message_db_messageUserTextGatherDao::getLatestUser($inPath[3], 1, 20);
        $latestTotalNum = 0;
        if (!empty($LatestUserRes->totalSize)) {
            $latestTotalNum = $LatestUserRes->totalSize > 20 ? 20 : $LatestUserRes->totalSize;
        }*/

        $default = [
            /*[
                'pk_user_follow_group' => -1,
                'group_name' => '最近联系人',
                'group_user_num' => $latestTotalNum
            ],*/
            [
                'pk_user_follow_group' => -2,
                'group_name' => '我的机构',
                'group_user_num' => 0
            ],
            [
                'pk_user_follow_group' => -3,
                'group_name' => '我的老师',
                'group_user_num' => 0
            ],
            [
                'pk_user_follow_group' => -4,
                'group_name' => '默认分组',
                'group_user_num' => 0
            ]
        ];

        $black = [
            [
                'pk_user_follow_group' => -5,
                'group_name' => '黑名单',
                'group_user_num' => 0
            ]
        ];
        $data = array_merge_recursive($default, $groupData, $black);

        return api_func::setData($data);
    }

    // ====================follow group contacts action===========================

    /**
     * t_user_follow_group_contacts move
     */
    public function pageUserMove()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $r = api_func::isValidId(['userId', 'groupId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        if (!isset($params['userIdArr']) || !is_array($params['userIdArr']))
            return api_func::setMsg(1000);

        $groupId         = $r['groupId'];
        $loginUser       = $r['userId'];
        $addContactsData = [
            'fk_user'              => $params['userIdArr'][0], // to do no support batch
            'fk_user_follow_group' => $groupId,
            'fk_user_owner'        => $loginUser,
            'status'               => 0,
            'create_time'          => date('Y-m-d H:i:s')
        ];

        if (!empty(user_db_userFollowGroupContactsDao::checkUserIsExist($params['userIdArr'][0], $loginUser))) {
            $res = user_db_userFollowGroupContactsDao::move($params['userIdArr'], $groupId, $loginUser);
        } else {
            $res = user_db_userFollowGroupContactsDao::add($addContactsData);
        }

        if ($res === false) return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    /**
     * user follow group contacts
     */
    public function pageListGroupContactsByGroupId()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r      = api_func::isValidId(['groupId', 'userId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $page    = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length  = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : 50;
        $groupId = $r['groupId'];
        $userId  = $r['userId'];

        $list = user_db_userFollowGroupContactsDao::listsByGroupId($groupId, $userId, $page, $length);

        if (!empty($list->items)) return api_func::setData($list->items);

        return api_func::setMsg(3002);
    }

    public function pageGetDefaultAndBlackGroupList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r      = api_func::isValidId(['userId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $page   = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : 100;

        $userId = $r['userId'];

        $list = user_db_userFollowGroupContactsDao::getDefaultAndBlackGroupList($userId, $page, $length);

        if (!empty($list->items)) return api_func::setData($list->items);

        return api_func::setMsg(3002);
    }

    public function pageDelContact()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r = api_func::isValidId(['userId', 'linkMan'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        // Delete contacts, need to clear the message
        if (user_db_userFollowGroupContactsDao::delContact($r['userId'], $r['linkMan'])) {
            // update user_message_text status
            if (message_db_dialogDao::msgDel($r['linkMan'], $r['userId'], message_type::SYSTEM_CONTACT_INFORMATION) === false) {
                SLog::fatal('when delete contact success but update t_user_message_text failed,params[%s]', var_export($params, 1));
            }
            // update user_message_text_gather status
            if (message_db_messageUserTextGatherDao::msgDel($r['linkMan'], $r['userId'], message_type::SYSTEM_CONTACT_INFORMATION) === false) {
                SLog::fatal('when delete contact success but update t_user_message_text_gather failed,params[%s]', var_export($params, 1));
            }
            return api_func::setMsg(0);
        }

        SLog::fatal('delete contact failed,params[%s]', var_export($params, 1));

        return api_func::setMsg(1);
    }

    public function pageGetUserRelation()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r = api_func::isValidId(['userId', 'linkMan'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $res = user_db_userFollowGroupContactsDao::checkUserIsExist($r['linkMan'], $r['userId']);

        if ($res === false) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    // ================== message user text gather action ====================

    /**
     * get messages
     */
    public function pageGetMessages()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userToId']) || !(int)($params['userToId']))
            return api_func::setMsg(1000);

        $page = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : 50;

        $list = message_db_messageUserTextGatherDao::getMyMessages($params['userToId'], $page, $length);
        if (!empty($list->items)) {
            $data['totalPage'] = $list->totalPage;
            $data['totalSize'] = $list->totalSize;
            $data['data'] = $list->items;
            return api_func::setData($data);
        }

        return api_func::setMsg(3002);
    }


    /**
     * is_top action
     */
    public function pageMsgTop()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userFrom'])) return api_func::setMsg(1000);
        if (!isset($params['msgType'])) return api_func::setMsg(1000);
        if ((!isset($params['userTo']) || !(int)$params['userTo'])) return api_func::setMsg(1000);

        if (!isset($params['type']) || !in_array($params['type'], [0,1]))
            return api_func::setMsg(1000);

        if (message_db_messageUserTextGatherDao::msgTop($params['userFrom'], $params['userTo'],$params['msgType'], $params['type']))
            return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    /**
     * is_remind action
     */
    public function pageMsgRemind()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userFrom'])) return api_func::setMsg(1000);
        if (!isset($params['msgType'])) return api_func::setMsg(1000);
        if ((!isset($params['userTo']) || !(int)$params['userTo'])) return api_func::setMsg(1000);

        if (!isset($params['type']) || !in_array($params['type'], [0, 1]))
            return api_func::setMsg(1000);

        if (message_db_messageUserTextGatherDao::msgRemind($params['userFrom'], $params['userTo'],$params['msgType'], $params['type']))
            return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    /**
     * update msg status into delete and update master message table status into delete
     */
    public function pageMsgDel()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userFrom'])) return api_func::setMsg(1000);
        if (!isset($params['msgType'])) return api_func::setMsg(1000);
        if ((!isset($params['userTo']) || !(int)$params['userTo'])) return api_func::setMsg(1000);

        if (message_db_dialogDao::msgDel($params['userFrom'], $params['userTo'],$params['msgType']) !== false) {
            // update user_message_text status
            if (message_db_messageUserTextGatherDao::msgDel($params['userFrom'], $params['userTo'],$params['msgType']) === false) {
                SLog::fatal('delete t_user_message_text success but update t_user_message_text_gather failed,params[%s]', var_export($params, 1));
            }
            return api_func::setMsg(0);
        }
        SLog::fatal('delete t_user_message_text failed,params[%s]', var_export($params, 1));

        return api_func::setMsg(1);
    }

    // ================== message user text gather action ====================

    /**
     * get msg detail list and update message status into read
     */
    public function pageMsgDetail()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userFrom'])) return api_func::setMsg(1000);
        if (!isset($params['msgType'])) return api_func::setMsg(1000);
        if ((!isset($params['userTo']) || !(int)$params['userTo'])) return api_func::setMsg(1000);

        $page = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : 50;

        $list = message_db_dialogDao::getUnreadMsg($params['userFrom'], $params['userTo'], $params['msgType'], $page, $length);

        if (!empty($list->items)) {
            // update t_message_user_text message status into read
            if (message_db_dialogDao::msgUpdateRead($params['userFrom'], $params['userTo'], $params['msgType']) === false) {
                SLog::fatal('update t_message_user_text message status into read failed,params[%s]', var_export($params, 1));
            }

            // update t_message_user_text_gather message status into read
            if (message_db_messageUserTextGatherDao::msgUpdateRead($params['userFrom'], $params['userTo'], $params['msgType']) === false) {
                SLog::fatal('update t_message_user_text_gather message status into read failed,params[%s]', var_export($params, 1));
            }

            $data['totalPage'] = $list->totalPage;
            $data['totalSize'] = $list->totalSize;
            $data['data'] = $list->items;

            return api_func::setData($data);
        }

        return api_func::setMsg(3002);
    }

    public function pageGetUserByUserIds()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['userIdArr']) || count($params['userIdArr']) < 1) {
            return api_func::setMsg(1000);
        }
        $userIdStr = implode(',', $params['userIdArr']);

        $res = user_db_userFollowGroupContactsDao::getUserByUserIds($userIdStr);

        if (!empty($res->items)) {
            return api_func::setData($res->items);
        }

        return api_func::setMsg(3002);
    }

    public function pageGetEachGroupNum($inPath)
    {
        if ((!isset($inPath[3]) || !(int)($inPath[3]))) return api_func::setMsg(1000);

        $res = user_db_userFollowGroupContactsDao::getEachGroupNum((int)($inPath[3]));
        if (!empty($res->items)) {
            return api_func::setData($res->items);
        }

        return api_func::setMsg(3002);
    }
}
