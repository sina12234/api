<?php

/**
 * @desc table t_fav_teacher curd
 *
 * Class user_db_favTeacherDao
 */
class user_db_favTeacherDao
{
    const dbName = 'db_user';

    const TABLE = 't_fav_teacher';

    /**
     * @desc db instance
     *
     * @param string $dbName
     * @param string $dbType
     * @return SDb
     */
    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    /**
     * @desc add data into t_fav_teacher table
     *
     * @param $data
     * @return bool|int
     */
    public static function addFav($data)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data);
    }

    /**
     * @desc update t_fav_teacher table
     *
     * @param $userId
     * @param $teacherId
     * @return bool|int
     */
    public static function cancelFav($userId, $teacherId)
    {
        $db = self::InitDB(self::dbName);

        $condition = [
            'fk_user' => $userId,
            'teacher_id' => $teacherId
        ];

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc check teacher fav
     *
     * @param $userId
     * @param $teacherId
     * @return DbData
     */
    public static function checkTeacherFav($userId, $teacherId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = [
            'fk_user' => $userId,
            'teacher_id' => $teacherId
        ];

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @desc get teacher fav total num
     *
     * @param $tid
     * @return int
     */
    public static function getFavTotalByTeacherId($tid)
    {
        $db    = self::InitDB(self::dbName, 'query');
        $table = self::TABLE;
        $sql   = "select  count(*) as totalNum from {$table} where `teacher_id`={$tid}";

        $res = $db->execute($sql);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));

            return 0;
        }

        return $res[0]['totalNum'];
    }



}
