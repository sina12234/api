<?php

class tweeter_api
{
    /**
     * @desc insert t_tweeter
     *
     * @param $content
     * @param $uid
     * @param $orgId
     * @return bool|int
     */
    public static function insertTweeter($content, $uid, $orgId)
    {
        if (!$uid && !$orgId) return false;

        $insertData = [
            'fk_user'     => $uid,
            'fk_org'      => $orgId,
            'content'     => $content,
            'create_time' => date('Y-m-d H:i:s')
        ];

        $tweeterId = tweeter_db_tweeterDao::add($insertData);
        if ($tweeterId) {
            $feedData = [
                'userId'    => $uid,
                'tweeterId' => $tweeterId,
                'orgId'     => $orgId,
            ];

            return self::insertFeed($feedData);
        }

        return false;
    }

    /**
     * @desc insert t_tweeter_feed
     *
     * @param $data
     * @return bool|int
     */
    public static function insertFeed($data)
    {
        $userId = !empty($data['userId']) ? (int)($data['userId']) : 0;
        $orgId  = !empty($data['orgId']) ? (int)($data['orgId']) : 0;
        if ((!$userId && !$orgId) || empty($data['tweeterId'])) return false;

        $insertData = [
            'fk_user'        => $userId,
            'fk_tweeter'     => $data['tweeterId'],
            'fk_source'      => 1,
            'fk_author_user' => $userId,
            'fk_author_org'  => $orgId,
            'create_time'    => date('Y-m-d H:i:s')
        ];

        return tweeter_db_feedDao::add($insertData);
    }

    /**
     * @param $uid
     * @param int $scope // 1,全部动态，2,我关注的动态，3,我的动态
     * @param int $orgId
     * @param int $source
     * @param int $page
     * @param int $length
     * @return array
     */
    public static function getFeeds($uid, $scope=3, $orgId = 0, $source = 1, $page = 1, $length = -1)
    {
        if (!$uid && !$orgId) return [];

        switch ($scope) {
            case 1: //全部动态
                $feeds = tweeter_db_feedDao::getAllFeeds($uid, $orgId, $source, $page, $length);
                break;
            case 2: // 我关注的动态
                $feeds = tweeter_db_feedDao::getFollowFeeds($uid, $source, $page, $length);
                break;
            default:
                // 我的动态
                $feeds = tweeter_db_feedDao::getMyFeeds($uid, $orgId, $source, $page, $length);
                break;
        }

        if (empty($feeds->items)) return [];

        $result['totalPage'] = $feeds->totalPage;
        $result['totalSize'] = $feeds->totalSize;
        $tweeterIdArr = array_column($feeds->items, 'fk_tweeter');

        $result['data'] = self::getTwInfoList($tweeterIdArr);

        return $result;
    }

    /**
     * @desc get tweeter info by tweeter id
     *
     * @param $tweeterId
     * @return array|bool
     */
    public static function getTwInfo($tweeterId)
    {
        if (!(int)($tweeterId)) return [];
        $info = tweeter_db_tweeterDao::row((int)($tweeterId));
        if (empty($info)) return [];

        $userInfo      = self::getUserNameAndThumb($info['fk_user'], $info['fk_org']);
        $info['name']  = $userInfo['name'];
        $info['thumb'] = $userInfo['thumb'];

        return $info;
    }

    public static function getTwInfoList($tweeterIdArr, $page = 1, $length = -1)
    {
        $twList = tweeter_db_tweeterDao::getTwInfoList($tweeterIdArr, $page, $length);
        if (empty($twList->items)) return [];

        $picList = self::getPicList($tweeterIdArr);
        if (empty($picList)) {
            $picImgList = [];
            foreach ($picList as $pic) {
                $picImgList[$pic['fk_tweeter']][] = $pic;
            }
        }

        // 获取mapping tweeter 中tag id list
        $tagIdList = tag_db_mappingTagTweeterDao::getTagId($tweeterIdArr);
        if (!empty($tagIdList->items)) {
            $tagIdArr = array_column($tagIdList->items, 'fk_tag');

            // 获取标签名称
            $tagNameList = tag_db_tagDao::getTagName($tagIdArr);
            if (!empty($tagNameList->items)) {
                $tagNameArr = array_column($tagNameList->items, 'name', 'pk_tag');
            }
        }

        // 获取发布tweeter 用户信息
        $userIdArr = array_column($twList->items, 'fk_user');
        $userList = user_db_userDao::listsByUserIdArr($userIdArr);
        if (!empty($userList->items)) {
            foreach ($userList->items as $item) {
                $userInfo[$item['pk_user']] = [
                    'userName' => $item['name'],
                    'userThumb' => $item['thumb_med']
                ];
            }
        }

        //$orgIdArr = array_column($twList->items, 'fk_org');
        $data = [];
        foreach ($twList->items as $v) {
            $data[] = [
                'tweeterId'  => $v['pk_tweeter'],
                'userName'   => !empty($userInfo[$v['fk_user']]['userName']) ? $userInfo[$v['fk_user']]['userName'] : '',
                'userThumb'  => !empty($userInfo[$v['fk_user']]['userThumb']) ? $userInfo[$v['fk_user']]['userThumb'] : '',
                'tagName'    => !empty($tagNameArr[$v['pk_tweeter']]) ? $tagNameArr[$v['pk_tweeter']] : '',
                'picList'    => !empty($picImgList[$v['pk_tweeter']]) ? $picImgList[$v['pk_tweeter']] : [],
                'content'    => $v['content'],
                'commentNum' => $v['comment_count'],
                'zanNum'     => $v['zan_count'],
                'viewNum'    => $v['view_count'],
                'time'       => $v['create_time']
            ];
        }

        return $data;
    }

    /**
     * @desc get user name and thumb
     *
     * @param $userId
     * @param int $orgId
     * @return array
     */
    public static function getUserNameAndThumb($userId, $orgId = 0)
    {
        $data = [
            'name'  => '',
            'thumb' => ''
        ];

        if (!empty($userId)) {
            $userInfo = user_db_userDao::row($userId);
            if (!empty($userInfo)) {
                $data['name'] = !empty($userInfo['real_name'])
                    ? $userInfo['real_name']
                    : (!empty($userInfo['name']) ? $userInfo['name'] : '');

                $data['thumb'] = $userInfo['thumb_med'];
            }
        }

        if (!empty($orgId)) {
            $orgInfo = user_db_organizationUserDao::row($orgId);
            if (!empty($orgInfo)) {
                $data['name']  = $orgInfo['real_name'];
                $data['thumb'] = $orgInfo['thumb_med'];
            }
        }

        return $data;
    }

    /**
     * @desc get tweeter comments list
     *
     * @param $tweeterId
     * @param int $page
     * @param int $length
     * @return array
     */
    public static function getComments($tweeterId, $page = 1, $length = -1)
    {
        if (!(int)($tweeterId)) return [];

        $lists = tweeter_db_commentDao::getComments($tweeterId, $page, $length);

        if (empty($lists->items)) return [];
        $userIdArr = $orgIdArr = [];
        foreach ($lists->items as $item) {
            $userIdArr[$item->fk_user]    = $item->fk_user;
            $userIdArr[$item->reply_user] = $item->reply_user;
            $orgIdArr[$item->fk_org]      = $item->fk_org;
            $orgIdArr[$item->reply_org]   = $item->reply_org;
        }

        $userLists = user_db_userDao::listsByUserIdArr($userIdArr, 1, -1);
        $userInfo  = $orgInfo = [];
        if (!empty($userLists->items)) {
            foreach ($userLists->items as $user) {
                $userInfo[$user['pk_user']] = $user;
            }
        }

        $orgLists = user_db_organizationUserDao::listsByOrgIdArr($orgIdArr);
        if (!empty($orgLists->items)) {
            foreach ($orgLists->items as $org) {
                $orgInfo[$org['pk_org']] = $org;
            }
        }

        foreach ($lists->items as &$item) {
            $item['user_info'] = $userInfo;
            $item['org_info']  = $orgInfo;
        }

        return $lists->items;
    }

    /**
     * @desc get feed pic list
     *
     * @param $tweeterId
     * @param int $page
     * @param int $length
     * @return array
     */
    public static function getPicList($tweeterId, $page = 1, $length = -1)
    {
        if (!(int)($tweeterId)) return [];

        $lists = tweeter_db_picDao::getPicList($tweeterId, $page, $length);

        if (empty($lists->items)) return [];

        return $lists->items;
    }

    public static function patchTag($data)
    {
        if (empty($data['userId']) && empty($data['orgId']))
            return false;

        $data = [
            'fk_tag'     => $data['tagId'],
            'fk_tweeter' => $data['tweeterId'],
            'fk_user'    => $data['userId'],
            'fk_org'     => $data['orgId'],
            'status'     => 1
        ];

        $res = tag_db_tweeterDao::add($data);

        return $res ? api_func::setData(['tagId' => $res]) : api_func::setMsg(1);
    }
}
