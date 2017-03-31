<?php

/**
 * @desc table t_user_score curd
 *
 * Class user_db_userScoreDao
 */
class user_db_userScoreDao
{
    const dbName = 'db_user';

    const TABLE = 't_user_score';

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

    public static function getUserLevelList($userIdArr, $page = 1, $length = -1)
    {
        if (count($userIdArr) < 1) return false;

        $db = self::InitDB(self::dbName, 'query');
        $condition = 'fk_user IN ('.implode(',', $userIdArr).')';

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select(self::TABLE, $condition);
    }



}
