<?php
class stat_db_dayOrgDao{
    const dbName = 'db_stat';

    const TABLE = 't_day_org_stat';

    public static function InitDB($dbname="db_stat",$dbtype="main") {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);
        return $db;
    }

    public static function getOrgStat($params)
    {
        $db = self::InitDB(self::dbName);
        $item = !empty($params["item"])?$params["item"]:'*';
        $condition = !empty($params["condition"])?$params["condition"]:'';
        $order = !empty($params["order"])?$params["order"]:'';
        $group = !empty($params["group"])?$params["group"]:'';
        if(isset($params["page"])&&isset($params["length"])) {
            $db->setPage($params["page"]);
            $db->setLimit($params["length"]);
            $db->setCount(true);
        }
        return $db->select(self::TABLE, $condition,$item,$group,$order);

    }

}
