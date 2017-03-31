<?php

class video_info
{
    public function pageGetListByVideoArr()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty(($params['videoIdArr']))) {
            return api_func::setMsg(1000);
        }

        $list = video_db::getListByVideoArr($params['videoIdArr']);
        if (empty($list)) return api_func::setMsg(3002);

        return api_func::setData($list);
    }
}
