<?php
/**
  * 禁忌词类
  **/
class censor_db{
	var $_db;
	var $_config;
	public function __construct($dbname="db_utility"){
		$this->_db = new SDb;
		$this->_db->useConfig($dbname);
	}
	public function addWord($word){
		$data=array("content"=>$word);
		return $this->_db->insert("t_censor_words",$data);
	}
	public function delWord($word){
		$data=array("content"=>$word);
		return $this->_db->delete("t_censor_words",$data);
	}
	public function searchWord($word){
		$word=addslashes ($word);
		$data=array("locate(content,'$word')");
		return $this->_db->selectOne("t_censor_words",$data);
	}
}
