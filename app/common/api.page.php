<?php

class Common_Api
{

    public function pageListsByUserClassId()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['teacherId'])) return api_func::setMsg(1000);

        $owner = isset($params['orgOwner']) && (int)($params['orgOwner']) ? $params['orgOwner'] : 0;
        $page = isset($params['page']) && (int)($params['page']) ? $params['page'] : 1;
        $length = isset($params['length']) && (int)($params['length']) ? $params['length'] : 500;

        $courseClassList = course_db_courseClassDao::listsByUserClassId($params['teacherId'], $owner, $page, $length);

        if (empty($courseClassList->items)) return api_func::setMsg(3002);

        return api_func::setData($courseClassList->items);
    }

    public function pageGetTeacherInfo($inPath)
    {
        if (!isset($inPath[3]) || !(int)($inPath[3])) return api_func::setMsg(1000);
        $teacherId = (int)($inPath[3]);

        $res = common_user::getTeacherInfo($teacherId);

        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }



}
