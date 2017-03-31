<?php

class tweeter_user
{
    public function pagePullBlack()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $r = api_func::isValidId(['userId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $blackUserId = !empty($params['blackUserId']) ? (int)($params['blackUserId']) : 0;
        $blackOrgId = !empty($params['blackOrgId']) ? (int)($params['blackOrgId']) : 0;
        if (!$blackUserId && !$blackOrgId) {
            return api_func::setMsg(1000);
        }

        $insertData = [
            'fk_user'       => $r['userId'],
            'black_user_id' => $blackUserId,
            'black_org_id'  => $blackOrgId,
            'create_time'   => date('Y-m-d H:i:s')
        ];

        $res = user_db_userTweeterBlackDao::add($insertData);
        if ($res === false) return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageCreateGroup()
    {
        $r = api_func::checkParams(
            ['userId', 'groupName'],
            SJson::decode(utility_net::getPostData(), true)
        );
        if (!empty($r['code'])) return api_func::error($r['code'], $r['msg']);

        if (tweeter_user_api::createGroup($r['userId'], $r['groupName']) === false)
            return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageCreateRelation()
    {
        $r = api_func::checkParams(
            ['followId', 'groupId'],
            SJson::decode(utility_net::getPostData(), true)
        );

        $r['userId'] = isset($r['userId']) ? $r['userId'] : 0;
        $r['orgId']  = isset($r['orgId']) ? $r['orgId'] : 0;
        if (!$r['userId'] && !$r['orgId']) return api_func::error(1000, 'userId and orgId is null');
        if (!empty($r['code'])) return api_func::error($r['code'], $r['msg']);

        if (tweeter_user_api::createRelation($r) === false)
            return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageDelRelation()
    {
        $r = api_func::checkParams(
            ['followId'],
            SJson::decode(utility_net::getPostData(), true)
        );

        $r['userId'] = isset($r['userId']) ? $r['userId'] : 0;
        $r['orgId']  = isset($r['orgId']) ? $r['orgId'] : 0;
        if (!$r['userId'] && !$r['orgId']) return api_func::error(1000, 'userId and orgId is null');
        if (!empty($r['code'])) return api_func::error($r['code'], $r['msg']);

        if (tweeter_user_api::delRelation($r['followId'], $r['userId'], $r['orgId']) === false)
            return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageDelGroup()
    {
        $r = api_func::checkParams(
            ['userId', 'groupId'],
            SJson::decode(utility_net::getPostData(), true)
        );
        if (!empty($r['code'])) return api_func::error($r['code'], $r['msg']);

        if (tweeter_user_api::delGroup($r['groupId'], $r['userId']) === false)
            return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageChangeGroupName()
    {
        $r = api_func::checkParams(
            ['userId', 'groupId', 'groupName'],
            SJson::decode(utility_net::getPostData(), true)
        );
        if (!empty($r['code'])) return api_func::error($r['code'], $r['msg']);

        if (tweeter_user_api::delGroup($r['userId'], $r['groupId'], $r['groupName']) === false)
            return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageGroupList()
    {
        $r = api_func::checkParams(
            ['userId'],
            SJson::decode(utility_net::getPostData(), true)
        );
        if (!empty($r['code'])) return api_func::error($r['code'], $r['msg']);

        return api_func::setData(tweeter_user_api::groupList($r['userId'], $r['page'], $r['length']));
    }
}
