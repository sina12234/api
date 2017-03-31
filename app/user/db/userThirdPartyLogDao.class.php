<?php

class user_db_userThirdPartyLogDao
{
    const dbName = 'db_user';
    const TABLE = 't_user_third_party_log';

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

        $res = $db->insert(self::TABLE, $data);

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }


    public static function checkTransactionId($transactionId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "transaction_id={$transactionId}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function lists($cond, $page=1, $length=-1, $item='*', $orderBy='', $groupBy='')
    {
        $db = self::InitDB(self::dbName, 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $res = $db->select(self::TABLE, $cond, $item, $groupBy, $orderBy);

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
