<?php

class tweeter_info
{
    public function pageGet($inPath)
    {
        $tweeterId = isset($inPath[3]) && (int)($inPath[3]) ? (int)($inPath[3]) : 0;
        if (!$tweeterId) return api_func::setMsg(1000);

        $info = tweeter_api::getTwInfo($tweeterId);
        if (empty($info)) return api_func::setMsg(3002);

        return api_func::setData($info);
    }

    public function pageZan()
    {
        $params    = SJson::decode(utility_net::getPostData(), true);
        $tweeterId = isset($params['tweeterId']) && (int)($params['tweeterId']) ? (int)($params['tweeterId']) : 0;
        $userId = isset($params['userId']) && (int)($params['userId']) ? (int)($params['userId']) : 0;

        if (!$tweeterId || !$userId) return api_func::setMsg(1000);
        $res = tweeter_db_tweeterDao::updateZanNum($tweeterId);
        if ($res === false) return api_func::setMsg(1);

        tweeter_db_tweeterDao::addZanUserIntoRedis($tweeterId, $userId);
        return api_func::setMsg(0);
    }


    public function pagePicList($inPath)
    {
        $tweeterId = isset($inPath[3]) && (int)($inPath[3]) ? (int)($inPath[3]) : 0;
        if (!$tweeterId) return api_func::setMsg(1000);

        $list = tweeter_api::getPicList($tweeterId, 1, 9);
        if (empty($list)) return api_func::setMsg(3002);

        return api_func::setData($list);
    }

    public function pageGetFeeds()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $uid    = isset($params['userId']) && (int)($params['userId']) ? (int)($params['userId']) : 0;
        $orgId  = isset($params['orgId']) && (int)($params['orgId']) ? (int)($params['orgId']) : 0;
        $scope  = isset($params['scope']) && (int)($params['scope']) ? (int)($params['scope']) : 3;
        $page   = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : -1;

        $res = tweeter_api::getFeeds($uid, $scope, $orgId, 1, $page, $length);

        return api_func::setData($res);
    }

    public function pageGetComments()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $tweeterId    = isset($params['tweeterId']) && (int)($params['tweeterId']) ? (int)($params['tweeterId']) : 0;
        $page   = isset($params['page']) && (int)($params['page']) ? (int)($params['page']) : 1;
        $length = isset($params['length']) && (int)($params['length']) ? (int)($params['length']) : -1;
        if (!$tweeterId) return api_func::setMsg(1000);

        $res = tweeter_api::getComments($tweeterId, $page, $length);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

}
