<?php

class Common_List
{

    public function pageOne()
    {
        $param = SJson::decode(utility_net::getPostData(), true);

        $commonDb = new common_db('db_user');

        $data = $commonDb->getRow($param);

        if (empty($data)) return api_func::setMsg(3002);

        return api_func::setData($data);
    }

    public function pageGetIdStr()
    {
        $param = SJson::decode(utility_net::getPostData(), true);

        $commonDb = new common_db($param['dbName']);

        $res = $commonDb->getIdList($param);

        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    public function pageGetUsersInfo()
    {
		utility_cache::pageCache(300);
        $scope = '';
        $page = 1;
        $length = 500;

        $param = SJson::decode(utility_net::getPostData(), true);

        if (empty($param['userIdArr'])) return api_func::setMsg(1000);

        !empty($param['scope']) && $scope = $param['scope'];
        !empty($param['page']) && $page = $param['page'];
        !empty($param['length']) && $length = $param['length'];

        $res = common_user::getUsersInfo($param['userIdArr'], $scope, $page, $length);

        if (!empty($res)) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

}
