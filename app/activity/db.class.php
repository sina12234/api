<?php
class activity_db{

	public static function InitDB($dbname="db_activity") {
		$db = new SDb();
		$db->useConfig($dbname, 'main');
		return $db;
	}
	public static function addScore($tmp){
		$table=array("2015_score_hbs");
		$db = self::InitDB();
		return $db->insert($table,array("name"=>$tmp[0],"mobile"=>$tmp[1],"id"=>$tmp[2],"number"=>$tmp[3],"score"=>$tmp[4]));
	}
	
	/**根据名字和准考证号查**/
	public static function getScoreByNameNumber($name, $numb){
		$table=array("2015_score_hbs");
		$db = self::InitDB();
		return $db->selectOne($table,array("name"=>$name,"number"=>$numb), "*");
	}
	/**根据身份证查**/
	public static function getScoreByIDCard($id){
		$table=array("2015_score_hbs");
		$db = self::InitDB();
		return $db->selectOne($table,array("id"=>$id), "*");
	}
	public static function addYCBScore($tmp){
		$table=array("2015_score_ycb");
		$db = self::InitDB();
		return $db->insert($table,array("name"=>$tmp[0],"grade"=>$tmp[1],"mobile"=>$tmp[2],"score"=>$tmp[3]));
	}
	public static function getYCBScoreByName($name){
		$table=array("2015_score_ycb");
		$db = self::InitDB();
		return $db->selectOne($table,array("name"=>$name), "*");
	}
}
