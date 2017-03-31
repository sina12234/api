<?php

class user_profile
{
    public function pageInfo($inPath)
    {
        $uid = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
        if (!$uid) return api_func::setMsg(1000);

        $res = user_db_profileDao::row($uid);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    public function pageUpdateRealName()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (empty($params['mobile']) || empty($params['userName'])) {
            return api_func::setMsg(1000);
        }

        $data = [
            'real_name' => $params['userName']
        ];

        if (user_db_userDao::updateRealName($params['mobile'], $data)) return api_func::setMsg(0);

        SLog::fatal('update t_user table real_name failed,params[%s]', var_export($params, 1));
        return api_func::setMsg(1);
    }

    public function pageTeacherProfile()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['userIdArr']) || count($params['userIdArr']) < 1)
            return api_func::setMsg(1000);

        $res = user_db_teacherProfileDao::listsByUserIdArr($params['userIdArr']);
        if (!empty($res->items)) return api_func::setData($res->items);

        return api_func::setMsg(3002);
    }
}
