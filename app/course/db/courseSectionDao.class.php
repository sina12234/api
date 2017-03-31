<?php

class course_db_courseSectionDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_section';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getSectionBySectionId($sectionId, $page = 1, $length = -1)
    {
        $db        = self::InitDB(self::dbName, 'query');
        $condition = 'status <> -1';

        if (is_array($sectionId) && count($sectionId) > 0) {
            $sectionStr = implode(',', $sectionId);
            $condition .= " AND pk_section IN ({$sectionStr}) ";
        } else {
            $condition .= " AND pk_section={$sectionId}";
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

    public static function getSectionByCourseId($courseId, $page = 1, $length = -1)
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
     * @desc get section list by sectionId or courseId (support array)
     *
     * @param $param
     * @return DbData
     */
    public static function getSectionList($param)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = ['status <> -1'];

        // search by sectionId or sectionIdArr
        if (!empty($param['sectionId'])) {
            if (is_array($param['sectionId'])) {
                $sectionIdStr = implode(',', $param['sectionId']);
                $condition['pk_section'] = " pk_section IN ({$sectionIdStr}) ";
            } else {
                $condition['pk_section'] = $param['sectionId'];
            }
        }

        // search by courseId or courseIdArr
        if (!empty($param['courseId'])) {
            if (is_array($param['courseId'])) {
                $courseIdStr = implode(',', $param['courseId']);
                $condition['fk_course'] = " fk_course IN ({$courseIdStr}) ";
            } else {
                $condition['fk_course'] = $param['courseId'];
            }
        }

        if (!empty($param['page']) && !empty($param['length'])) {
            $db->setPage($param['page']);
            $db->setLimit($param['length']);
            $db->setCount(true);
        }

        return $db->select(self::TABLE, $condition);
    }
	
	//huoqu courseId对应的章节信息
	public static function getSectionInfo($courseId){
		if(empty($courseId)) return array();
		$db = self::InitDB(self::dbName, 'query');
		$condition = "fk_course = {$courseId}";
		$item = array('name');
		return $db->selectOne(self::TABLE, $condition,$item);
	}
}

