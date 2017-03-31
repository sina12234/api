<?php

class course_db_discountCodeUsedDao
{
    const dbName = 'db_course';
    const TABLE = 't_discount_code_used';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getDiscountCodeUsedInfo($orderId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_order='{$orderId}'";

        return $db->selectOne(self::TABLE, $condition);
    }
}

