<?php

class tweeter_action
{
    /**
     * t_tweeter insert action
     */
    public function pagePublish()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $userId = isset($params['userId']) ? (int)($params['userId']) : 0;
        $orgId  = isset($params['orgId']) ? (int)($params['orgId']) : 0;

        if (!$userId && !$orgId) return api_func::setMsg(1000);
        if (!isset($params['content']) || !strlen(trim($params['content'])))
            return api_func::setMsg(1000);

        $data = [
            'userId'  => $params['userId'],
            'orgId'   => $orgId,
            'content' => trim($params['content']),
        ];

        $res = user_db_userTweeterDao::add($data);
        if ($res === false) return api_func::setMsg(1);

        $userId && tweeter_user_api::userFeedRecord($userId);
        $orgId && tweeter_user_api::orgFeedRecord($orgId);

        return api_func::setMsg(0);
    }

    /**
     * t_tweeter_comment insert action
     */
    public function pageComment()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['tweeterId'])) return api_func::setMsg(1000);
        if (!isset($params['content']) || !strlen(trim($params['content'])))
            return api_func::setMsg(1000);

        $userId    = isset($params['userId']) ? (int)($params['userId']) : 0;
        $orgId     = isset($params['orgId']) ? (int)($params['orgId']) : 0;
        $replyUser = isset($params['replyUser']) ? (int)($params['replyUser']) : 0;
        $replyOrg  = isset($params['replyOrg']) ? (int)($params['replyOrg']) : 0;

        $insertData = [
            'fk_tweeter'  => $params['tweeterId'],
            'fk_user'     => $userId,
            'fk_org'      => $orgId,
            'content'     => trim($params['content']),
            'reply_user'  => $replyUser,
            'reply_org'   => $replyOrg,
            'create_time' => date('Y-m-d H:i:s')
        ];

        $res = tweeter_db_commentDao::add($insertData);
        if ($res === false) return api_func::setMsg(1);

        tweeter_db_tweeterDao::updateCommentNum($res);

        return api_func::setMsg(0);
    }

    public function pageDelComment()
    {
        $params    = SJson::decode(utility_net::getPostData(), true);
        $commentId = isset($params['commentId']) && (int)$params['commentId'] ? (int)$params['commentId'] : 0;
        $userId    = isset($params['userId']) && (int)$params['userId'] ? (int)$params['userId'] : 0;
        $orgId     = isset($params['orgId']) && (int)$params['orgId'] ? (int)$params['orgId'] : 0;
        if (!$commentId) return api_func::setMsg(1000);

        $res = tweeter_db_commentDao::del($commentId, $userId, $orgId);
        if ($res === false) return api_func::setMsg(1);

        tweeter_db_tweeterDao::updateCommentNum($res, -1);

        return api_func::setMsg(0);
    }

    public function pagePic()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['tweeterId'])) return api_func::setMsg(1000);

        $insertData = [
            'fk_tweeter'  => $params['tweeterId'],
            'pic_raw'     => $params['picRaw'],
            'pic_big'     => $params['picBig'],
            'pic_mid'     => $params['picMid'],
            'pic_sma'     => $params['picSma'],
            'sort'        => 0,
            'create_time' => date('Y-m-d H:i:s')
        ];

        $res = tweeter_db_picDao::add($insertData);
        if ($res === false) return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageAddTag()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (!isset($params['tagId']) || !(int)$params['tagId']) return api_func::setMsg(1000);
        if (!isset($params['tweeterId']) || !$params['tweeterId']) return api_func::setMsg(1000);

        $userId = !empty($params['userId']) ? $params['userId'] : 0;
        $orgId  = !empty($params['orgId']) ? $params['orgId'] : 0;
        if (!$userId && !$orgId) return api_func::setMsg(1000);

        $data = [
            'userId'    => $userId,
            'orgId'     => $orgId,
            'tagId'     => $params['tagId'],
            'tweeterId' => $params['tweeterId'],
        ];

        $res = tweeter_api::patchTag($data);
        if ($res) return api_func::setData(['tagId' => $res]);

        return api_func::setMsg(1);
    }
}
