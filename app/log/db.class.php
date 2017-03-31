<?php

/**
 * @author wangshaogang
 */
class log_db{
	/*var $_db;
	var $_config;
	public function __construct($dbname="db_log"){
		$this->_db = new SDb;
		$this->_db->useConfig($dbname,"main");
	}*/
	public static function InitDB($dbname="db_log",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
	public function addPlayLog($data){
        $db = self::InitDB("db_log");
		/*if(openlog("t_play_log",LOG_ODELAY,LOG_LOCAL4)){
			syslog(LOG_INFO, SJson::encode($data));
			closelog();
		}*/
		$key = md5("log_db.t_play_log");
		$data->now = time(null);
		//$v = SJson::encode($data);
		return redis_api::rPushByJson($key, $data);
		//$table = array("t_play_log");
		//return $db->insert($table, $data);
	}
    public function addPlayLogExtra($data){
        $db = self::InitDB("db_log");
		$key = md5("log_db.t_play_log_extra");
		$data->now = time(null);
		return redis_api::rPushByJson($key, $data);
    }
	public function addThumbLog($params){
        $db = self::InitDB("db_log");
		$data = new stdclass;
		$data->fk_video = $params->video_id;
		$data->thumbs = $params->thumbs;
		$table = array("t_thumb_log");
		$ret = $db->insert($table, $data);
		return $ret;
	}
    public function getAreaList($intervals,$min_date,$max_date){
        $db = self::InitDB("db_log","query");
		//define("DEBUG",true);
        $table=array("t_play_stat_log");
        $condition=array(
                    "intervals='".$intervals."'",
                    'starttime>=\''.$min_date.'\'',
                    'starttime<=\''.$max_date.'\'',

                );
        $items=array('areaname');
        $group_by=array('areaname');
        return $db->select($table,$condition,$items,$group_by,"","");

    }
    //报表
    public function getChartsData($params){
        $db = self::InitDB("db_log","query");
		//define("DEBUG",true);
        $table=array("t_play_stat_log");
        $condition=array();
        $condition=array(
                    "areaname='".$params['areaname']."'",
                    "intervals='".$params['intervals']."'",
                    "streamtype='".$params['streamtype']."'",
                    "opname='".$params['opname']."'",
                    "cdnid='".$params['cdnid']."'",
                    "playmode='".$params['playmode']."'",
                    "starttime>='".$params['min_date']."'",
                    "starttime<='".$params['max_date']."'"
                );
        $order_by=array(
                    'starttime'=>'desc'
                );
        return $db->select($table,$condition,"","",$order_by,"");
    }
    public function getReportByArea($params){
        $db = self::InitDB("db_log","query");
		//define("DEBUG",true);
        $table=array('t_play_stat_log');
        $condition=array();
        $condition=array(
                    "intervals='".$params['intervals']."'",
                    "streamtype='".$params['streamtype']."'",
                    "opname='".$params['opname']."'",
                    "cdnid='".$params['cdnid']."'",
                    "playmode='".$params['playmode']."'",
                    "areaname<>'all'",
                    "starttime>='".$params['min_date']."'",
                    "starttime<='".$params['max_date']."'"
                );
        $order_by=array(
                    'starttime'=>'desc'
                );
        return $db->select($table,$condition,"","",$order_by,"");
    }
    public function getReportByOp($params){
        $db = self::InitDB("db_log","query");
		//define("DEBUG",true);
        $table=array('t_play_stat_log');
        $condition=array();
        $condition=array(
                    "intervals='".$params['intervals']."'",
                    "areaname='".$params['areaname']."'",
                    "streamtype='".$params['streamtype']."'",
                    "cdnid='".$params['cdnid']."'",
                    "playmode='".$params['playmode']."'",
                    "starttime>='".$params['min_date']."'",
                    "starttime<='".$params['max_date']."'"
                );
        $order_by=array(
                    'starttime'=>'desc'
                );
        return $db->select($table,$condition,"","",$order_by,"");
    }
	public function addForbidLog($data){
		$db = self::InitDB("db_log");
		$table = array("t_message_plan_text_forbid_log");
		$ret = $db->insert($table, $data);
		return $ret;
	}

    /*
    * 统计成功报表
    * @param   string  $db     数据库名
    * @param   string  $table  表名
    * @param   int     $type   状态值
    * @return  array
    */
    public function getCountData($db,$table,$type='',$item='count(*) as num')
    {
        $db    = self::InitDB($db,'query');
        $tb    = array($table);
        $where = array($type);
        $items = array($item);
        $res   = $db->select($tb,$where,$items);
		if(empty($res->items[0])){
			$res->items[0]['num'] = 0;
		}
        return $res->items[0]['num'];
    }
	public function getPiceUser($condition,$groupBy)
	{
		$db    = self::InitDB("db_order",'query');
        $item  = 'fk_user';
		if(empty($condition)){
			$condition = '';
		}
		if(empty($groupBy)){
			$groupBy = '';
		}
		$table = array("t_order");
		return $db->select($table,$condition,$item,$groupBy);
	}
	public function addPromoteLog($data){
        $db = self::InitDB("db_log");
        $item['fk_promote'] = (int)($data['promote']);
        $item['fk_user_owner'] = (int)($data['fk_user_owner']);
        $item['client_ip'] = utility_ip::toLong($data['client_ip']);
        $item['referer'] = $data['referer'];
        $item['type'] = (int)($data['type']);
		$item['create_time'] = isset($data['create_time'])?$data['create_time']:date('Y-m-d H:i:s',time());
		$item['fk_user'] = isset($data['fk_user']) ? $data['fk_user'] :0;
		$table = array("t_promote_log");
		return $db->insert($table, $item);
	}
    public function getPromoteLog($params){
        $db = self::InitDB('db_log', 'query');
        $table = array('t_promote_log');
        $condition = array();
        foreach ($params as $key => $value) {
            if($key == 'client_ip'){
                $condition['client_ip'] = utility_ip::toLong($value);
            }
			if($key == 'type'){
				$condition['type'] = (int)($value);
			}
        }
		//print_r($condition);
        $item['fk_prmote'] = "fk_promote";
        $item['fk_user_owner'] = "fk_user_owner";
        $item['client_ip'] = "client_ip";
        $item['type'] = "type";
        $item['create_time'] = "create_time";
		//print_r($db->selectOne($table,$condition,$item));
        return $db->selectOne($table, $condition, $item,'',array('pk_log'=>'desc'));
    }

}
?>
