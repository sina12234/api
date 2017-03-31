<?php
class file_db{
	public static function InitDB($dbname="db_utility") {
		$db = new SDb();
		$db->useConfig($dbname);
		return $db;
	}
	public function addFile($File=array()){
		$table=array("t_weedfs_file");
		$db = self::InitDB();
		return $db->insert($table,$File,true);
	}
	public function addVolume($Volume=array()){
		$table=array("t_weedfs_volume");
		$db = self::InitDB();
		return $db->insert($table,$Volume,true);
	}
	public function getFile($fid){
		$table=array("t_weedfs_file");
		$db = self::InitDB();
		return $db->selectOne($table,array("fid"=>$fid));
	}
	public function getVolume($volume_id){
		$table=array("t_weedfs_volume");
		$db = self::InitDB();
		return $db->selectOne($table,array("volume_id"=>$volume_id));
	}
	public function listFile($uid,$type,$page=1,$size=20){
		$table=array("t_weedfs_file");
		$db = self::InitDB();
		$db->setLimit($size);
		$db->setPage($page);
		$condi = array("fk_user"=>$uid,"type"=>$type);
		return $db->select($table,$condi,"","","pk_file desc");
	}
	public function getFileByFidArr($fidArr){
		$table=array("t_weedfs_file");
		$db = self::InitDB();
		$fidStr = implode(',',$fidArr);		
		$condition = "fid in ($fidStr)";
		return $db->select($table,$condition);
	}

	public function getFileByFid($fid){
		$table=array("t_weedfs_file");
		$db = self::InitDB();

		$condition = "fid='{$fid}'";
		return $db->selectOne($table,$condition);
	}

	public function getUrlByVolume($volume)
	{
		$table=array("t_weedfs_volume");
		$db = self::InitDB();
		$condition = "pk_volume={$volume}";
		return $db->selectOne($table,$condition);
	}
}
