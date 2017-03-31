<?php
class course_db_planAttachDao{
    public static function InitDB($dbname="db_course",$dbtype="main") {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);
        return $db;
    }

    public static function updateDownloadCount($planAttachId){
        $db = self::InitDB('db_course');
        $table = "t_course_plan_attach";
        $condition = '';
        if(!empty($planAttachId)){
            $condition .= "pk_plan_attach = $planAttachId";
        }
        $item = "download_count=download_count+1";
        return $db->update($table,$condition,$item);
    }
}
