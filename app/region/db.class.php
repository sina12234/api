<?php
/**
  * 禁忌词类
  **/
class region_db{
	var $_db;
	var $_config;
	public function __construct($dbname="db_utility"){
		$this->_db = new SDb;
		$this->_db->useConfig($dbname);
	}
	public function listRegion($condi){
		$item	=array(
				"region_id"=>"pk_region",
				"parent_region_id"=>"parent_fk_region",
				"name",
				"level",
			);
		return $this->_db->select("t_region",$condi,$item);
	}
	public function listSchool($condi){
		$item	=array(
				"school_id"=>"pk_school",
				"school_name"=>"school_name",
				"school_type"=>"school_type",
			);
		return $this->_db->select("t_region_school",$condi,$item);
	}
	public function scoolByRegionIds($condi){
		$item	=array(
				"school_id"=>"pk_school",
				"school_name"=>"school_name",
				"school_type"=>"school_type",
			);
		return $this->_db->select("t_region_school",$condi,$item);
	}
	
	public function getRegionByRegionIdArr(array $regionIdArr){
		if(empty($regionIdArr)) return false;
		$regionIds = implode(',',$regionIdArr);
		$condition = "pk_region IN ($regionIds)";
		return $this->_db->select("t_region",$condition);
	}
}
