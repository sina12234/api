<?php

class common_user
{
    public static function userLists($params)
    {
        $cond = isset($params['condition']) && $params['condition'] ? $params['condition'] : '';
        $page = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length = isset($params['length']) && $params['length'] ? $params['length'] : 100;
        $item = isset($params['item']) && $params['item'] ? $params['item'] : '';
        $orderBy = isset($params['orderBy']) && $params['orderBy'] ? $params['orderBy'] : '';
        $groupBy = isset($params['groupBy']) && $params['groupBy'] ? $params['groupBy'] : '';

        $users = user_db_userDao::lists($cond, $page, $length, $item, $orderBy, $groupBy);

        if (empty($users->items)) return [];

        $uidArr = $list = [];
        foreach ($users->items as $user) {
            $uidArr[] = $user['pk_user'];
            $list[$user['pk_user']] = $user;
        }

        $uidStr = implode(',', $uidArr);
        // 获取t_user_mobile list
        if (!empty($params['mobile'])) {
            $userMobileCond = "fk_user IN ({$uidStr})";
            $userMobileItem = array('fk_user', 'mobile');
            $userMobileLists = user_db_mobileDao::lists($userMobileCond, $page, $length, $userMobileItem);

            if (!empty($userMobileLists->items)) {
                foreach ($userMobileLists->items as $userMobile) {
                    $list[$userMobile['fk_user']] = array_merge($list[$userMobile['fk_user']], $userMobile);
                }
            }
        }

        // 获取t_user_profile list
        if (!empty($params['profile'])) {
            $userProfileCond = "fk_user IN ({$uidStr})";
            $userProfileItem = array('fk_user', 'real_name');
            $userProfileLists = user_db_profileDao::lists($userProfileCond, $page, $length, $userProfileItem);

            if (!empty($userProfileLists->items)) {
                foreach ($userProfileLists->items as $userProfile) {
                    $list[$userProfile['fk_user']] = array_merge($list[$userProfile['fk_user']], $userProfile);
                }
            }
        }

        // 获取t_user_student_profile list
        if (!empty($params['student'])) {
            $studentCond = "fk_user IN ($uidStr)";
            $studentItem = array('fk_user', 'student_name', 'school_type','school_id');
            $studentLists = user_db_studentDao::lists($studentCond, $page, $length, $studentItem);

            if (!empty($studentLists->items)) {
                $schoolIdArr = [];
                foreach ($studentLists->items as $student) {
                    $list[$student['fk_user']] = array_merge($list[$student['fk_user']], $student);
                    $schoolIdArr[] = $student['school_id'];
                }
            }
        }

        // t_region_school list
        if (!empty($params['school']) && !empty($schoolIdArr)) {
            $schoolIdStr = implode(',', $schoolIdArr);
            $schoolCond = "pk_school IN ($schoolIdStr)";
            $schoolItem = array('pk_school', 'school_name');
            $schoolLists = utility_db_schoolDao::lists($schoolCond, $page, $length, $schoolItem);

            if (!empty($schoolLists->items)) {
                foreach ($list as &$v) {
                    foreach ($schoolLists->items as $school) {
                        if (!empty($v['school_id']) && ($school['pk_school'] == $v['school_id'])) {
                            $v['school_name'] = $school['school_name'];
                        }
                    }
                }
            }
        }
        $result = [
            'totalSize' => $users->totalSize,
            'totalPage' => $users->totalPage,
            'data' => $list
        ];

        return $result;
    }

    /**
     * get users info
     *
     * @param $userIdArr
     * @param $scope
     * @param int $page
     * @param int $length
     * @return array
     */
    public static function getUsersInfo($userIdArr, $scope, $page=1, $length=-1)
    {
        $list = [];
        if (count($userIdArr)<1) return [];

        // t_user list
        $userList = user_db_userDao::listsByUserIdArr($userIdArr, $page, $length);
        if (!empty($userList->items)) {
            foreach ($userList->items as $user) {
                if (!empty($user['pk_user'])) {
                    $list[$user['pk_user']] = $user;
                }
            }
        }

        // t_user_profile list
        if (isset($scope['profile']) && $scope['profile']) {
            $userProfileList = user_db_profileDao::listsByUserIdArr($userIdArr, $page, $length);
            if (!empty($userProfileList->items)) {
                foreach ($userProfileList->items as $userProfile) {
                    if (!empty($list[$userProfile['fk_user']])) {
                        $list[$userProfile['fk_user']] = array_merge($list[$userProfile['fk_user']], $userProfile);
                    }
                }
            }
        }

        // t_user_mobile list
        if (isset($scope['mobile']) && $scope['mobile']) {
            $userMobileList = user_db_mobileDao::listsByUserIdArr($userIdArr, $page, $length);
            if (!empty($userMobileList->items)) {
                foreach ($userMobileList->items as $userMobile) {
                    if (!empty($list[$userMobile['fk_user']])) {
                        $list[$userMobile['fk_user']] = array_merge($list[$userMobile['fk_user']], $userMobile);
                    }
                }
            }
        }

        // 获取t_user_student_profile list
        if (isset($scope['student']) && $scope['student']) {
            $userStudentLists = user_db_studentDao::listsByUserIdArr($userIdArr, $page, $length);

            if (!empty($userStudentLists->items)) {
                $schoolIdArr = [];
                foreach ($userStudentLists->items as $userStudent) {
                    if (!empty($list[$userStudent['fk_user']])) {
                        $list[$userStudent['fk_user']] = array_merge($list[$userStudent['fk_user']], $userStudent);
                        $schoolIdArr[]                 = $userStudent['school_id'];
                    }
                }
            }
        }

        // t_region_school list
        if (isset($scope['school']) && $scope['school'] && !empty($schoolIdArr)) {
            $userSchoolLists = utility_db_schoolDao::listsBySchoolIdArr($schoolIdArr, $page, $length);
            if (!empty($userSchoolLists->items)) {
                foreach ($list as &$v) {
                    foreach ($userSchoolLists->items as $school) {
                        if (!empty($v['school_id']) && ($school['pk_school'] == $v['school_id'])) {
                            $v['school_name'] = $school['school_name'];
                        }
                    }
                }
            }
        }

        return $list;
    }

    public static function getTeacherInfo($teacherId)
    {
        $userInfo = user_db_userDao::row($teacherId);
        if (empty($userInfo)) return [];

        $teacherInfo = user_db_teacherDao::row($teacherId);
        if (empty($teacherInfo)) return [];

        $userProfileInfo = user_db_profileDao::row($teacherId);
        $t = $userProfileInfo ?: [];

        return array_merge($userInfo, $teacherInfo, $t);
    }
}
