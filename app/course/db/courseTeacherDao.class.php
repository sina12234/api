<?php

class course_db_courseTeacherDao
{
    const dbName = 'db_course';
    const TABLE = 't_course_teacher';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }
	
	public static function getCourseTeacher($data, $page = 1, $length = -1)
    {
        $db        = self::InitDB(self::dbName, 'query');
        $condition = 'status <> -1';
		
        if(!empty($data['courseId'])){
            if (is_array($data['courseId']) && count($data['courseId']) > 0) {
                $courseStr = implode(',', $data['courseId']);
                $condition .= " AND fk_course IN ({$courseStr}) ";
            } else {
                $condition .= " AND fk_course = {$data['courseId']}";
            }
        }
        if(!empty($data['teacherId'])){
            $condition .= " AND fk_user_teacher = {$data['teacherId']}";
        }

        $db->setPage($page);
		$db->setLimit($length);
		$db->setCount(true);

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data, false, false, $data);
    }
	
	public static function update($courseId, $teacherId, $data)
	{
		$db = self::InitDB("db_course");
		$condition = array("fk_course" => $courseId, "fk_user_teacher"=>$teacherId);
		return $db->update("t_course_teacher", $condition, $data);
	}
}

