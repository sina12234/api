<?php

class tweeter_my
{
    /**
     * @desc 我的粉丝列表
     */
    public function pageFans()
    {
        utility_cache::pageCache(1200);
        $params = SJson::decode(utility_net::getPostData(), true);

        $uid    = isset($params['userId']) && (int)($params['userId']) ? (int)($params['userId']) : 0;
        $page = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : -1;

        $res = tweeter_user_api::getMyFollow($uid, $page, $length);
        if (empty($res) || empty($res['data'])) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    public function pageFollowNum($inPath)
    {
        $userId = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
        if (!$userId) return api_func::setMsg(1000);

        $res = user_db_userTweeterDao::row($userId);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }
}
