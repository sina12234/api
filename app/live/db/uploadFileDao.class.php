<?php

class live_db_uploadFileDao
{
    const dbName = 'db_live';

    const TABLE = 't_live_upload_file';

    public static function InitDB($dbName=self::dbName, $dbType='main')
    {
        redis_api::useConfig($dbName);
        $db = new SDb();

        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getUploadList($uid, $planIdS)
    {
        $db = self::InitDB(self::dbName, 'query');
        $condition = "fk_user={$uid} AND fk_plan IN ({$planIdS})";

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
