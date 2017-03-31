<?php

class course_db_coursePlanDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_plan';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data);
    }

    public static function getPlanByCourseId($courseId, $page = 1, $length = -1)
    {
        $db        = self::InitDB(self::dbName, 'query');
        $condition = 'status <> -1';

        if (is_array($courseId) && count($courseId) > 0) {
            $courseStr = implode(',', $courseId);
            $condition .= " AND fk_course IN ({$courseStr}) ";
        } else {
            $condition .= " AND fk_course={$courseId}";
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function planList($param)
    {
        $db        = self::InitDB(self::dbName, 'query');
        $condition = ['status <> -1'];

        // search by planId or planIdArr
        if (!empty($param['planId'])) {
            if (is_array($param['planId'])) {
                $planIdStr = implode(',', $param['planId']);
                $condition[] = " pk_plan IN ({$planIdStr}) ";
            } else {
                $condition['pk_plan'] = $param['pk_plan'];
            }
        }

        // search by courseId or courseIdArr
        if (!empty($param['courseId'])) {
            if (is_array($param['courseId'])) {
                $courseIdStr = implode(',', $param['courseId']);
                $condition[] = " fk_course IN ({$courseIdStr}) ";
            } else {
                $condition['fk_course'] = $param['courseId'];
            }
        }

        // search by classId or classIdArr
        if (!empty($param['classId'])) {
            if (is_array($param['classId'])) {
                $classIdStr = implode(',', $param['classId']);
                $condition[] = " fk_class IN ({$classIdStr}) ";
            } else {
                $condition['fk_class'] = $param['classId'];
            }
        }

        // search by sectionId or sectionIdArr
        if (!empty($param['sectionId'])) {
            if (is_array($param['sectionId'])) {
                $sectionIdStr = implode(',', $param['sectionId']);
                $condition[] = " fk_section IN ({$sectionIdStr}) ";
            } else {
                $condition['fk_section'] = $param['sectionId'];
            }
        }

        // search by plan status or plan status array
        if (!empty($param['planStatus'])) {
            if (is_array($param['planStatus'])) {
                $planStatusStr = implode(',', $param['planStatus']);
                $condition[] = " status IN ({$planStatusStr}) ";
            } else {
                $condition['status'] = $param['planStatus'];
            }
        }

        !empty($param['ownerId']) && $condition["fk_user"] = $param['ownerId'];
        !empty($param['userPlanId']) && $condition["fk_user_plan"] = $param['userPlanId'];

        !empty($param['page']) && $db->setPage($param['page']);
        !empty($param['length']) && $db->setLimit($param['length']);

        $orderBy = '';
        !empty($param['orderBy']) && $orderBy = $param['orderBy'];

        return $db->select(self::TABLE, $condition, '', '', $orderBy);
    }

    public static function getPlanById($Id)
    {
        $db        = self::InitDB(self::dbName, 'query');
        $condition = 'pk_plan = '.$Id.' AND status <> -1';

        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
    public static function getPlanList($conditions,$orderBy)
    {
        $db = self::InitDB(self::dbName, 'query');

        if(empty($orderBy)) $orderBy = 'pk_plan desc';
        $res = $db->select(self::TABLE, $conditions, '', '', $orderBy);

        return $res;
    }
    //取讲课老师
    public static function getTeacherByCourseId($courseId){
        if(empty($courseId)) return array();
        $item      = array("distinct(fk_user_plan) as fk_user_plan");
        if(is_array($courseId)){
            $conditions = "fk_course in (".implode(',',$courseId).")";
        }else{
            $conditions = "fk_course = (".$courseId.")";
        }
        $db = self::InitDB(self::dbName, 'query');
        $res = $db->select(self::TABLE, $conditions, $item, '', '');
        return $res;
    }
    //获取planid的相关信息
    public static function getplanidInfo($planId){
        if(empty($planId)) return array();
        $condition = "pk_plan = {$planId}";
        $db = self::InitDB(self::dbName, 'query');
        $res = $db->selectOne(self::TABLE, $condition);
        return $res;
    }

    //获取plan信息
    public static function getCoursePlan($courseId){

        if(empty($courseId)) return array();
        $item = array('fk_course','fk_user_plan','fk_class');
        if(is_array($courseId)){
            $conditions = "fk_course in (".implode(',',$courseId).")  and status > 0";
        }else{
            $conditions = "fk_course = $courseId and status > 0";
        }
        $db = self::InitDB(self::dbName, 'query');
        $res = $db->select(self::TABLE, $conditions,$item);
        return $res;
    }

}

