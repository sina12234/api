<?php

class course_db_discountDao
{
    const dbName = 'db_course';
    const TABLE = 't_discount';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getDiscountInfo($pKey)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "pk_discount='{$pKey}'";

        return $db->selectOne(self::TABLE, $condition);
    }
}

