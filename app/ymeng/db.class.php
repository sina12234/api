<?php
class ymeng_db{
	var $_db;
	public function __construct($dbname="db_message"){
		$this->_db = new SDb;
		$this->_db->useConfig($dbname,"main");
	}

	public function addMessage($data){
		$table = array("t_message_common_text");
		$ret = $this->_db->insert($table, $data);
		if($ret){
			return $ret;
		}
	}
        
        //获取友盟支持app配置文件
        public static function getYmengConfig(){
            $conf = SConfig::getConfig(ROOT_CONFIG."/key.conf","ymeng");
            if(empty($conf)){
                return array();
            }
            return $conf;
        }
	
}

