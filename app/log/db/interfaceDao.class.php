<?php

class log_db_interfaceDao
{
    const dbName = 'db_log';
    const TABLE = 't_interface_log';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    /**
     * @param $jsonStr (json_encode string)
     * @return mixed
     */
    public static function add($jsonStr)
    {
        redis_api::useConfig(self::dbName);

        $key = md5("log_db.t_interface_log");
        $res = redis_api::rPush($key, $jsonStr);

        if ($res === false) {
            SLog::fatal(
                'add interface_log into redis failed,redis key[%s], params[%s]',
                $key,
                $jsonStr
            );
        }

        return $res;
    }
}

