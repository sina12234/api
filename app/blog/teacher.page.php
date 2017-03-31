<?php

class blog_teacher
{

    public function pageGetImgList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (!isset($params['teacherId']) || !$params['teacherId'])
            return api_func::setMsg(1000);

        $page = !empty($params['page']) ? (int)($params['page']) : 1;
        $length = !empty($params['length']) ? (int)($params['length']) : 20;

        $imgList = blog_db_teacherImgDao::listsByUserId($params['teacherId'], $page, $length);
        if (empty($imgList->items)) return api_func::setMsg(3002);

        return api_func::setData($imgList->items);
    }

    public function pageGetImgOne($inPath)
    {
        if (!isset($inPath[3]) || !(int)($inPath[3]))
            return api_func::setMsg(1000);

        $imgInfo = blog_db_teacherImgDao::row($inPath[3]);
        if (empty($imgInfo)) return api_func::setMsg(3002);

        return api_func::setData($imgInfo);
    }

    public function pageUpdateBannerImg()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['teacherId']) || !(int)$params['teacherId'])
            return api_func::setMsg(1000);

        if (!isset($params['imgPath']) || !$params['imgPath'])
            return api_func::setMsg(1000);

        if (user_db_teacherDao::updateBanner($params['teacherId'], $params['imgPath'])) {
            return api_func::setMsg(0);
        }

        return api_func::setMsg(1);
    }

    public function pageUpdateTeacherImgName()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['teacherId']) || !(int)$params['teacherId'])  return api_func::setMsg(1000);
        if (!isset($params['imgName']) || !$params['imgName'])  return api_func::setMsg(1000);

        if (blog_db_teacherImgDao::updateImgName($params['teacherId'], $params['imgName'])) {
            return api_func::setMsg(0);
        }

        return api_func::setMsg(1);
    }

    public function pageGetTeacherImgBanner($inPath)
    {
        if (!isset($inPath[3]) || !(int)$inPath[3]) return api_func::setMsg(1000);
        $res = user_db_teacherDao::row($inPath[3]);

        if (!empty($res)) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

    public function pageDelImg()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['imgId']) || !(int)$params['imgId']) return api_func::setMsg(1000);

        if (blog_db_teacherImgDao::del($params['imgId'])) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageAddTeacherImg()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (!isset($params['teacherId']) || !(int)($params['teacherId'])) return api_func::setMsg(1000);
        if (!isset($params['imgName']) || !$params['imgName']) return api_func::setMsg(1000);
        if (!isset($params['thumbMed']) || !$params['thumbMed']) return api_func::setMsg(1000);
        if (!isset($params['thumbBig']) || !$params['thumbBig']) return api_func::setMsg(1000);
        if (!isset($params['thumbOrigin']) || !$params['thumbOrigin']) return api_func::setMsg(1000);

        $data = [
            'fk_user'      => $params['teacherId'],
            'image_name'   => $params['imgName'],
            'thumb_med'    => $params['thumbMed'],
            'thumb_big'    => $params['thumbBig'],
            'thumb_origin' => $params['thumbOrigin'],
            'create_time'  => date('Y-m-d H:i:s')
        ];

        if (blog_db_teacherImgDao::add($data)) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }


}

