<?php

class user_score
{
    public function pageListByUidArr()
    {
		utility_cache::pageCache(300);
        $params = SJson::decode(utility_net::getPostData(), true);

        if (empty($params['userIdArr']) || !count($params['userIdArr'])) {
            return api_func::setMsg(1000);
        }

        $page   = !empty($params['page']) ? (int)($params['page']) : 1;
        $length = !empty($params['length']) ? (int)($params['length']) : 20;

        $levelList = user_db_userScoreDao::getUserLevelList($params['userIdArr'], $page, $length);
        if (empty($levelList->items)) return api_func::setMsg(3002);

        return api_func::setData($levelList->items);
    }
}
