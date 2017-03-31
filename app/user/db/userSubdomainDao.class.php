<?php

/**
 * @desc table t_user_score curd
 *
 * Class user_db_userScoreDao
 */
class user_db_userSubdomainDao
{
    const dbName = 'db_user';

    const TABLE = 't_user_subdomain';

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

    public static function UserSubdomain($fk_user)
    {

        if (empty($fk_user)) return false;
        $db = self::InitDB(self::dbName, 'query');
        $condition = "fk_user = $fk_user";
        return $db->select(self::TABLE, $condition);
    }



}
