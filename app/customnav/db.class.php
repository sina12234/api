<?php
class customnav_db
{
    public static function InitDB($dbName = "db_user", $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }
	//增加
	public static function addNav($data){
		$table=array("t_organization_custom_nav");
		$db = self::InitDB();
		return $db->insert($table,$data,true);
    }
	
	//修改
	public static function modNav($condition,$data){
		$table=array("t_organization_custom_nav");
		$db = self::InitDB();
		return $db->update($table,$condition,$data);
	}
	//查询 
	public static function selNav($condition){
		$db  = self::InitDB();
		$table=array("t_organization_custom_nav"); 
        return $v = $db->select($table, $condition);
        
	}

	//删除
	public static  function delNav($condition){
		$table=array("t_organization_custom_nav");
		$db  = self::InitDB();
		$item = [
			'status'=>1
		];
		return $v = $db->update($table,$condition,$item);
	}
}
?>
