<?php

class course_db_courseClassDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_class';

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

    public static function listsByUserClassId($id, $owner=0, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user_class={$id} AND status<>-1";
        if ($owner) {
            $condition .= " AND fk_user={$owner}";
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select(self::TABLE, $condition);
    }

    public static function del($id)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "pk_class={$id}";

        return $db->delete(self::TABLE, $condition);
    }

    public static function getClassByCourseId($courseId, $page = 1, $length = -1)
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

    /**
     * @desc get class list by owner or classId or userClassId or courseId (support array)
     *
     * @param $param
     * @return DbData
     */
    public static function getClassList($param)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = ['status <> -1'];

        // search by classId or classIdArr
        if (!empty($param['classId'])) {
            if (is_array($param['classId'])) {
                $classIdStr = implode(',', $param['classId']);
                array_push($condition, " pk_class IN ({$classIdStr}) ");
            } else {
                $condition['pk_class'] = $param['classId'];
            }
        }

        // search by courseId or courseIdArr
        if (!empty($param['courseId'])) {
            if (is_array($param['courseId'])) {
                $courseIdStr = implode(',', $param['courseId']);
                array_push($condition, " fk_course IN ({$courseIdStr}) ");
            } else {
                $condition['fk_course'] = $param['courseId'];
            }
        }

        // search by user class id or user class id array
        if (!empty($param['userClassId'])) {
            if (is_array($param['userClassId'])) {
                $userClassIdStr = implode(',', $param['userClassId']);
                array_push($condition, " fk_user_class IN ({$userClassIdStr}) ");
            } else {
                $condition['fk_user_class'] = $param['userClassId'];
            }
        }


        if (!empty($param['page']) && !empty($param['length'])) {
            $db->setPage($param['page']);
            $db->setLimit($param['length']);
            $db->setCount(true);
        }

        if (!empty($param['owner'])) {
            array_push($condition, " fk_user={$param['owner']}");
        }

        return $db->select(self::TABLE, $condition);
    }
    
    public static function getClassInfo($classId,$courseId=0)
    {
        $db        = self::InitDB("db_course", "query");
        $table     = 't_course_class';
        $condition = "pk_class=$classId";
		if(!empty($courseId)){
			$condition.=" and fk_course=".intval($courseId);
		}

        return $db->selectOne($table, $condition);

    }

    public static function getCourseMsg($courseId){
        $db        = self::InitDB("db_course", "query");
        $table     = 't_course';
        $condition = "pk_course=$courseId";
        return $db->selectOne($table, $condition);
    }
	//获取课程班主任
	public static function HeaderTeacher($courseId){
		if(empty($courseId)) return array();
		$courseId = is_array($courseId)?implode(',',$courseId):$courseId;
		$db        = self::InitDB("db_course", "query");
        $table     = 't_course_class';
		$items = array("fk_course","pk_class","fk_user_class");
        $condition = "fk_course in ($courseId)";
		return $db->select($table, $condition,$items);
	}
}

