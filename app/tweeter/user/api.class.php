<?php

class tweeter_user_api
{
    /**
     * @desc record user feed
     *
     * @param $userId
     * @return bool
     */
    public static function userFeedRecord($userId)
    {
        $insertData = [
            'fk_user'      => (int)$userId,
            'tweet_count'  => 0,
            'fan_count'    => 0,
            'follow_count' => 0,
            'status'       => 1,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        $updateData = ["tweet_count=tweet_count+１"];

        $res = user_db_userTweeterDao::add($insertData, $updateData);
        if ($res === false) return false;

        return true;
    }

    /**
     * @desc record org feed
     *
     * @param $orgId
     * @return bool
     */
    public static function orgFeedRecord($orgId)
    {
        $insertData = [
            'fk_org'       => $orgId,
            'tweet_count'  => 0,
            'fan_count'    => 0,
            'follow_count' => 0,
            'status'       => 1,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        $updateData = ["tweet_count=tweet_count+１",];

        $res = user_db_organizationTweeterDao::add($insertData, $updateData);
        if ($res === false) return false;

        return true;
    }

    /**
     * @desc create group
     *
     * @param $uid
     * @param $groupName
     * @return bool|int
     */
    public static function createGroup($uid, $groupName)
    {
        $data = [
            'fk_user'          => $uid,
            'group_name'       => $groupName,
            'sort'             => 0,
            'group_user_count' => 0,
            'create_time'      => date('Y-m-d H:i:s')
        ];

        return user_db_userTweeterGroupDao::add($data);
    }

    /**
     * @desc create relation
     *
     * @param $data
     * @return bool
     */
    public static function createRelation($data)
    {
        $userId = $orgId = 0;
        if (empty($data['followId']) || !(int)($data['followId']))
            return false;

        if (empty($data['groupId']) || !(int)($data['groupId']))
            return false;

        if (!empty($data['userId']) && (int)($data['userId']))
            $userId = (int)($data['userId']);

        if (!empty($data['orgId']) && (int)($data['orgId']))
            $orgId = (int)($data['orgId']);

        if (!$userId && !$orgId) return false;

        $groupId = (int)$data['groupId'];
        $followId = (int)$data['followId'];
        $data    = [
            'fk_user'     => $userId,
            'fk_org'      => $orgId,
            'follower_id' => (int)($data['followId']),
            'fk_group'    => $groupId,
            'create_time' => date('Y-m-d H:i:s')
        ];

        if (user_db_userTweeterRelationDao::add($data)) {
            // 关注成功之后，更新group num
            if (user_db_userTweeterGroupDao::updateGroupNum($groupId) === false) {
                SLog::fatal('update group num failed,params[%s]', var_export($data, 1));
            }

            // 在关注成功之后，把关注人发的默认最新动态写进redis中，异步入t_tweeter_feed(这里默认取关注人的50条动态)
            user_db_userTweeterRelationDao::addFollowTwIntoRedis($userId, $followId, $orgId, 1, 1, 50);
            return true;
        }

        return false;
    }

    public static function delRelation($followId, $uid, $orgId = 0)
    {
        $groupId = user_db_userTweeterRelationDao::getGroupId($followId, $uid);
        if (!$groupId) return false;

        if (user_db_userTweeterRelationDao::del($followId, $uid, $orgId)) {
            if (user_db_userTweeterGroupDao::updateGroupNum($groupId, -1) === false) {
                SLog::fatal(
                    'update group num failed, loginId[%d], uid[%d], orgId[%d]',
                    $followId,
                    $uid,
                    $orgId
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @desc delete group
     *
     * @param $groupId
     * @param $uid
     * @return bool
     */
    public static function delGroup($groupId, $uid)
    {
        if (user_db_userTweeterGroupDao::del($uid, $groupId)) {
            // check group member
            $groupMember = user_db_userTweeterRelationDao::getGroupMember($uid, $groupId);
            if (!empty($groupMember->items)) {
                // move group member into default group
                $uidArr = array_column($groupMember->items, 'fk_user');
                if (user_db_userTweeterRelationDao::updateBatch($uid, $uidArr) === false) {
                    SLog::fatal(
                        'batch move failed,uid[%d],groupId[%d],uidArrStr[%s]',
                        $uid,
                        $groupId,
                        var_export($uidArr, 1)
                    );

                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * ＠desc change group name by user id group id
     *
     * @param $uid
     * @param $gid
     * @param $name
     * @return bool|int
     */
    public static function changeGroupName($uid, $gid, $name)
    {
        return user_db_userTweeterGroupDao::update($uid, $gid, ['group_name' => $name]);
    }

    public static function groupList($uid, $page = 1, $length = -1)
    {
        $list      = user_db_userTweeterGroupDao::groupList($uid, $page, $length);
        $groupData = [];
        if (!empty($list->items)) {
            $groupData = $list->items;
        }

        $default = [
            /*[
                'pk_user_tweeter_group' => -1,
                'group_name' => '最近联系人',
                'group_user_count' => $latestTotalNum
            ],*/
            [
                'pk_user_tweeter_group' => -2,
                'group_name'            => '我的机构',
                'group_user_count'      => 0
            ],
            [
                'pk_user_tweeter_group' => -3,
                'group_name'            => '我的老师',
                'group_user_count'      => 0
            ],
            [
                'pk_user_tweeter_group' => -4,
                'group_name'            => '默认分组',
                'group_user_count'      => 0
            ]
        ];

        $black = [
            [
                'pk_user_tweeter_group' => -5,
                'group_name'            => '黑名单',
                'group_user_count'      => 0
            ]
        ];

        return array_merge_recursive($default, $groupData, $black);
    }

    public static function getMyFollow($uid, $page = 1, $length = -1)
    {
        $followList = user_db_userTweeterRelationDao::getMyFollow($uid, $page, $length);
        if (empty($followList->items)) return [];

        $orgIdArr = $userIdArr = $list = [];
        foreach ($followList->items as $v) {
            if ($v['fk_org']) {
                $orgIdArr[$v['fk_org']] = $v['fk_org'];
            }

            if ($v['fk_user']) {
                $userIdArr[$v['fk_user']] = $v['fk_user'];
            }

            $list['user']['fk_user']['followTime'] = $v['create_time'];
            $list['org']['fk_org']['followTime']   = $v['create_time'];
        }

        $userInfo  = user_db_userDao::listsByUserIdArr($userIdArr);
        if (!empty($userInfo->items)) {
            foreach ($userInfo->items as $user) {
                $userItem = [
                    'realName'  => $user['real_name'],
                    'userId'    => $user['pk_user'],
                    'userThumb' => $user['thumb_big']
                ];

                if (!empty($user['type']) && $user['type'] & 0x01) {
                    $userItem['types']['student'] = true;
                }

                if (!empty($user['type']) && $user['type'] & 0x02) {
                    $userItem['types']['teacher'] = true;
                }

                if (!empty($user['type']) && $user['type'] & 0x04) {
                    $userItem['types']['organization'] = true;
                }

                if (!empty($user['pk_user'])) {
                    $list['user'][$user['pk_user']] = $userItem;
                }
            }
        }

        $userLevel = user_db_userScoreDao::getUserLevelList($userIdArr);
        if (!empty($userLevel->items)) {
            foreach ($userLevel->items as $level) {
                $levelItem = [
                    'levelTitle' => $level['title'],
                ];

                if (!empty($list['user'][$level['fk_user']])) {
                    $list['user'][$level['fk_user']] = array_merge($list['user'][$level['fk_user']], $levelItem);
                }
            }
        }

        $studentProfile = user_db_studentDao::listsByUserIdArr($userIdArr);
        if (!empty($studentProfile->items)) {
            $region = region_geo::$region;
            foreach ($studentProfile->items as $student) {
                $regionLevel0 = !empty($region[$student['region_level0']]) ? $region[$student['region_level0']] : '';
                $regionLevel1 = !empty($region[$student['region_level1']]) ? $region[$student['region_level1']] : '';
                $studentItem = [
                    'studentAddress' => $regionLevel0.$regionLevel1,
                ];

                if (!empty($list['user'][$student['fk_user']])) {
                    $list['user'][$student['fk_user']] = array_merge($list['user'][$student['fk_user']], $studentItem);
                }
            }
            unset($region);
        }

        $teacherProfile = user_db_teacherProfileDao::listsByUserIdArr($userIdArr);
        if (!empty($teacherProfile->items)) {
            foreach ($teacherProfile->items as $teacher) {
                $teacherItem = [
                    'briefDesc' => !empty($teacher['brief_desc']) ? $teacher['brief_desc'] : '一句话介绍自己',
                ];

                if (!empty($list['user'][$teacher['fk_user']])) {
                    $list['user'][$teacher['fk_user']] = array_merge($list['user'][$teacher['fk_user']], $teacherItem);
                }
            }
        }

        $userProfile = user_db_profileDao::listsByUserIdArr($userIdArr);
        if (!empty($userProfile->items)) {
            foreach ($userProfile->items as $profile) {
                $profileItem = [
                    'profileDesc' => $profile['desc'],
                ];

                if (!empty($list['user'][$profile['fk_user']])) {
                    $list['user'][$profile['fk_user']] = array_merge($list['user'][$profile['fk_user']], $profileItem);
                }
            }
        }

        $organization = user_db_organizationDao::listsByOrgIdArr($orgIdArr);
        if (!empty($organization->items)) {
            foreach ($organization->items as $org) {
                $orgItem = [
                    'orgId'   => $org['pk_org'],
                    'orgName' => $org['name'],
                ];

                if (!empty($list['org'][$org['pk_org']])) {
                    $list['org'][$org['pk_org']] = array_merge($list['org'][$org['pk_org']], $orgItem);
                }
            }
        }

        $organizationProfile = user_db_organizationProfileDao::listsByOrgIdArr($orgIdArr);
        if (!empty($organizationProfile->items)) {
            foreach ($organizationProfile->items as $orgProfile) {
                $orgProfileItem = [
                    'orgId'      => $orgProfile['fk_org'],
                    'orgSubName' => $orgProfile['subname'],
                ];

                if (!empty($list['org'][$orgProfile['fk_org']])) {
                    $list['org'][$orgProfile['fk_org']] = array_merge($list['org'][$orgProfile['fk_org']], $orgProfileItem);
                }
            }
        }

        return [
            'totalPage' => $followList->totalPage,
            'totalSize' => $followList->totalSize,
            'data'      => array_merge($list['user'], $list['org'])
        ];
    }
}
