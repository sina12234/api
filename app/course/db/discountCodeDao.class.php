<?php

class course_db_discountCodeDao
{
    const dbName = 'db_course';
    const TABLE = 't_discount_code';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function checkDiscountCode($discountCode)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "discount_code='{$discountCode}' and status <> -1";

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getDiscountCodeInfo($pKey)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "pk_discount_code='{$pKey}'";

        return $db->selectOne(self::TABLE, $condition);
    }
}

