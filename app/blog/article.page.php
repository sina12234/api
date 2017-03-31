<?php

class blog_article
{

    public function pageLists()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['teacherId']) || !$params['teacherId']) return api_func::setMsg(1000);

        $data['teacherId'] = $params['teacherId'];
        isset($params['tagId']) && (int)($params['tagId']) && $data['tagId'] = $params['tagId'];
        isset($params['type']) && (int)($params['type']) && $data['type'] = $params['type'];
        isset($params['draft']) && (int)($params['draft']) && $data['draft'] = $params['draft'];
        isset($params['top']) && (int)($params['top']) && $data['top'] = $params['top'];

        $articleList = blog_db_articleDao::lists($data);

        $result = [
            'articleList' => !empty($articleList->items) ? $articleList->items : [],
            'tagLists'    => blog_api::getTagNameTotalList($params['teacherId']),
            'totalSize'   => $articleList->totalSize
        ];

        return api_func::setData($result);
    }

    public function pageAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (empty($params['teacherId']) || empty($params['tagId']) || empty($params['content'])) {
            return api_func::setMsg(1000);
        }

        $data = [
            'fk_user'     => $params['teacherId'],
            'title'       => $params['title'],
            'summary'     => isset($params['summary']) ? $params['summary'] : '',
            'thumb'       => isset($params['thumb']) ? $params['thumb'] : '',
            'content'     => htmlentities($params['content']),
            'fk_tag'      => $params['tagId'],
            'type'        => isset($params['type']) ? $params['type'] : 1,
            'status'      => isset($params['status']) ? $params['status'] : 1,
            'top'         => isset($params['top']) ? $params['top'] : 0,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        $res = blog_db_articleDao::add($data);
        if ($res) return api_func::setData(['articleId'=>$res]);

        return api_func::setMsg(1);
    }

    public function pageUpdate()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (empty($params['articleId']) || empty($params['tagId']) || empty($params['content'])) {
            return api_func::setMsg(1000);
        }

        $data = [
            'fk_user'     => $params['teacherId'],
            'title'       => $params['title'],
            'summary'     => isset($params['summary']) ? $params['summary'] : '',
            'thumb'       => isset($params['thumb']) ? $params['thumb'] : '',
            'content'     => htmlentities($params['content']),
            'fk_tag'      => $params['tagId'],
            'type'        => isset($params['type']) ? $params['type'] : 1,
            'status'      => isset($params['status']) ? $params['status'] : 1,
            'top'         => isset($params['top']) ? $params['top'] : 0
        ];

        $res = blog_db_articleDao::update($params['articleId'], $data);
        if ($res === false) {
            return api_func::setMsg(1);
        }

        return api_func::setMsg(0);
    }

    public function pageRow($inPath)
    {
        $id = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
        if (!$id) return api_func::setMsg(1000);

        $res = blog_db_articleDao::row($id);
        if (empty($res)) return api_func::setMsg(3002);
        $tagName = tag_db_tagDao::row($res['fk_tag']);

        $res['tagName'] = !empty($tagName['name']) ? $tagName['name'] : '';

        return api_func::setData($res);
    }

    public function pageGetCommentList($inPath)
    {
        $id     = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
        $page   = isset($inPath[4]) && $inPath[4] ? (int)($inPath[4]) : 1;
        $length = isset($inPath[5]) && $inPath[5] ? (int)($inPath[5]) : 20;
        if (!$id) return api_func::setMsg(1000);

        $list = blog_db_commentDao::listsByArticleId($id, $page, $length);
        if (empty($list->items)) return api_func::setMsg(3002);

        $usersIdArr = $result = [];
        foreach ($list->items as $v) {
            $userIdArr[]         = $v->fk_user;
            $result[$v->fk_user] = $v;
        }

        $usersInfo = user_db::listUsersByUserIds($usersIdArr);

        if (!empty($usersInfo->items)) {
            foreach ($usersInfo->items as $m) {
                $result[$m->fk_user] = array_merge($result[$m->fk_user], $m);
            }
        }

        return api_func::setData($result);
    }

    public function pageGetArticleList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (!isset($params['teacherId']) || !$params['teacherId']) return api_func::setMsg(1000);
        $page   = !empty($params['page']) ? (int)($params['page']) : 1;
        $length = !empty($params['length']) ? (int)($params['length']) : 20;

        $articleList = blog_db_articleDao::listsByUserId($params['teacherId'], $page, $length);
        if (empty($articleList->items)) return api_func::setMsg(3002);

        return api_func::setData($articleList->items);
    }

    public function pageGetImgList()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (!isset($params['teacherId']) || !$params['teacherId']) return api_func::setMsg(1000);
        $page   = !empty($params['page']) ? (int)($params['page']) : 1;
        $length = !empty($params['length']) ? (int)($params['length']) : 20;

        $imgList = blog_db_teacherImgDao::listsByUserId($params['teacherId'], $page, $length);
        if (empty($imgList->items)) return api_func::setMsg(3002);

        return api_func::setData($imgList->items);
    }

    /**
     * @desc get tag name total list by teacher id
     *
     * @param $inPath
     * @return array
     */
    public function pageGetTagNameTotalList($inPath)
    {
        if (!isset($inPath[3]) || !is_numeric($inPath[3]) || !(int)($inPath[3]))
            return api_func::setMsg(1000);

        $res = blog_api::getTagNameTotalList($inPath[3]);

        if (empty($res))return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    public function pageGetArticleComment()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        if (!isset($params['articleId']) || !$params['articleId']) return api_func::setMsg(1000);
        $page = !empty($params['page']) ? (int)($params['page']) : 1;
        $length = !empty($params['length']) ? (int)($params['length']) : 20;

        $commentList = blog_db_commentDao::listsByArticleId($params['articleId'], $page, $length);
        if (empty($commentList->items)) return api_func::setMsg(3002);

        $userIdArr = $list = [];
        foreach ($commentList->items as $v) {
            $userIdArr[$v['fk_user']] = $v['fk_user'];
        }

        $userLists = user_db_userDao::listsByUserIdArr($userIdArr, $page, $length);
        if (!empty($userLists->items)) {
            foreach ($userLists->items as $user) {
                $userInfo[$user['pk_user']] = [
                    'userName' => $user['name'],
                    'thumb_med' => $user['thumb_med']
                ];
            }
        }

        foreach ($commentList->items as $comment) {
            $list[] = [
                'id' => $comment['pk_article_comment'],
                'name' => !empty($userInfo[$comment['fk_user']])
                        ? $userInfo[$comment['fk_user']]['userName']
                        : '',
                'thumb' => !empty($userInfo[$comment['fk_user']])
                    ? $userInfo[$comment['fk_user']]['thumb_med']
                    : '',
                'comment' => $comment['comment'],
                'time' => $comment['create_time']
            ];
        }

        return api_func::setData(
            [
                'total' => $commentList->totalSize,
                'list' => $list
            ]
        );
    }

    public function pageAddComment()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userId']) || !$params['userId']) return api_func::setMsg(1000);
        if (!isset($params['articleId']) || !$params['articleId']) return api_func::setMsg(1000);
        if (!isset($params['comment']) || !$params['comment']) return api_func::setMsg(1000);

        $data = [
            'comment_parent_id' => 0,
            'fk_user' => $params['userId'],
            'fk_article' => $params['articleId'],
            'comment' => $params['comment']
        ];

        if (blog_db_commentDao::add($data)) {
            if (blog_db_articleDao::updateCommentNum($params['articleId']) === false) {
                SLog::fatal('update comment num failed,params[%s]', var_export($data, 1));
            }
            return api_func::setMsg(0);
        }

        return api_func::setMsg(1);
    }

    public function pageUpdateCommentNum()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['articleId']) || !(int)$params['articleId']) return api_func::setMsg(1000);

        if (blog_db_articleDao::updateCommentNum($params['articleId'])) return api_func::setMsg(0);
        return api_func::setMsg(1);
    }

    public function pageAddFavTeacher()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userId']) || !(int)$params['userId']) return api_func::setMsg(1000);
        if (!isset($params['teacherId']) || !(int)$params['teacherId']) return api_func::setMsg(1000);

        $data = [
            'fk_user' => $params['userId'],
            'teacher_id' => $params['teacherId'],
        ];

        if (!empty(user_db_favTeacherDao::addFav($data))) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageCancelFav()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userId']) || !(int)$params['userId']) return api_func::setMsg(1000);
        if (!isset($params['teacherId']) || !(int)$params['teacherId']) return api_func::setMsg(1000);

        if (user_db_favTeacherDao::cancelFav($params['userId'], $params['teacherId']))
            return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageCheckTeacherFav()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userId']) || !(int)$params['userId']) return api_func::setMsg(1000);
        if (!isset($params['teacherId']) || !(int)$params['teacherId']) return api_func::setMsg(1000);

        $res = user_db_favTeacherDao::checkTeacherFav($params['userId'], $params['teacherId']);
        if (!empty($res->items)) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageGetTeacherFavTotal($inPath)
    {
        if (!isset($inPath[3]) || !is_numeric($inPath[3]) || !(int)($inPath[3]))
            return api_func::setMsg(1000);

        $res = user_db_favTeacherDao::getFavTotalByTeacherId($inPath[3]);

        if ($res) return api_func::setData([$res]);

        return api_func::setMsg(1);
    }

    public function pageDelArticle()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['articleId']) || !(int)$params['articleId']) return api_func::setMsg(1000);

        if (blog_db_articleDao::del($params['articleId'])) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageGetTagListByTeacherId($inPath)
    {
        if (!isset($inPath[3]) || !(int)$inPath[3]) return api_func::setMsg(1000);

        $res = tag_db_tagDao::getTagsByUserId($inPath[3]);
        if (empty($res->items)) return api_func::setMsg(3002);

        return api_func::setData($res->items);
    }

    public function pageAddTag()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['userId']) || !(int)$params['userId']) return api_func::setMsg(1000);
        if (!isset($params['tagName']) || !$params['tagName']) return api_func::setMsg(1000);

        $data = [
            'fk_user' => $params['userId'],
            'name' => $params['tagName'],
            'desc' => '',
            'status' => 0
        ];

        $res = tag_db_tagDao::addTag($data);
        if ($res) return api_func::setData(['tagId'=>$res]);

        return api_func::setMsg(1);
    }

    public function pageAddMapTagArticle()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (
            !isset($params['userId'], $params['tagId'],$params['articleId']) ||
            (!(int)($params['userId']) || !(int)($params['userId']) || !(int)($params['articleId']))
        ) {
            return api_func::setMsg(1000);
        }

        $data = [
            'fk_user' => $params['userId'],
            'fk_tag' => $params['tagId'],
            'fk_article' => $params['articleId'],
            'status' => $params['status']
        ];

        $res = tag_db_mappingTagArticleDao::add($data);
        if ($res) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageUpdateMapTagArticle()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['articleId']) || !(int)$params['articleId']) return api_func::setMsg(1000);
        if (!isset($params['tagId']) || !(int)$params['tagId']) return api_func::setMsg(1000);

        if (tag_db_mappingTagArticleDao::updateMapTagArticle($params['articleId'], $params['tagId'])) {
            return api_func::setMsg(0);
        }

        return api_func::setMsg(1);
    }

    public function pageDelMapTagArticle()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['articleId']) || !(int)$params['articleId']) return api_func::setMsg(1000);
        if (!isset($params['tagId']) || !(int)$params['tagId']) return api_func::setMsg(1000);
        if (!isset($params['uid']) || !(int)$params['uid']) return api_func::setMsg(1000);

        if (tag_db_mappingTagArticleDao::del($params['uid'], $params['tagId'], $params['articleId'])) {
            return api_func::setMsg(0);
        }

        return api_func::setMsg(1);
    }
}

