<?php
class promote_db{
	const CHANNEL_TABLE='t_mgr_channel';
	const PROMOTE_TABLE = 't_mgr_promote';
	const SUB_CHANNEL_TABLE='t_mgr_subchannel';

	public static function InitDB($dbname="db_mgr",$dbtype="main") {
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
	public static function getPromote($pid){
		$table=array("t_mgr_promote");
		$db = self::InitDB('db_mgr','query');
		//$leftjoin = self::SUB_CHANNEL_TABLE." ON ".self::SUB_CHANNEL_TABLE.".pk_sub_channel=".self::PROMOTE_TABLE.".fk_channel";
        $item = array(
        	"pk_promote",
        	"fk_user_owner",
        	"subdomain",
        	"t_mgr_subchannel.fk_channel as fk_channel",

        	"promote_code",
        	"t_mgr_promote.status as `status`",
        	"t_mgr_promote.create_time as `create_time`",
        	"t_mgr_promote.update_time as `update_time`",
        	"t_mgr_subchannel.pk_sub_channel as `pk_sub_channel`",
        	"t_mgr_subchannel.name as `name`",
        	"t_mgr_subchannel.last_update as `last_update`"
        	);
        $leftjoin = new stdclass;
        $leftjoin->t_mgr_subchannel = "t_mgr_subchannel.pk_sub_channel=t_mgr_promote.fk_channel";
		return $db->selectOne($table,array("pk_promote"=>$pid),$item,'','',$leftjoin);
	}
	public static function getPromoteByOther($where){
		//define(DEBUG, true);
		$table=array("t_mgr_promote");
		$params = array();
		if(isset($where->fk_user_owner)){
			$params['fk_user_owner'] = $where->fk_user_owner;
		}
		if(isset($where->fk_channel)){
			$params['fk_channel'] = $where->fk_channel;
		}
		if(isset($where->promote_code)){
			$params['promote_code'] = $where->promote_code;
		}
		if(isset($where->fk_channel_in)){
			$params[] = $where->fk_channel_in;
		}
		$db = self::InitDB('db_mgr','query');
		//$leftjoin = self::SUB_CHANNEL_TABLE." ON ".self::SUB_CHANNEL_TABLE.".pk_sub_channel=".self::PROMOTE_TABLE.".fk_channel";
        
        
		return $db->select($table,$params,'','','');
	}
	public function channelAdd($data){
		$table=array("t_mgr_channel");
		$db = self::InitDB('db_mgr');
		return $db->insert($table,$data);
	}
	public function getChannelList($where = array(),$page = 1,$size = 10,$order=array()){
		$db    = self::InitDB('db_mgr','query');
        $table = 't_mgr_channel';
        $db->setPage($page);
        $db->setLimit($size);

        return $db->select($table, $where, '*', '', $order);
	}
	public function getChannel($cid){
		$db    = self::InitDB('db_mgr','query');
		$table = array("t_mgr_channel");
		return $db->selectOne($table,array('pk_channel'=>$cid));
	}
	public function delChannel($cid){
		$db = self::InitDB('db_mgr');
        $table = array('t_mgr_channel');
        
		$condition = array('pk_channel' => $cid);
 		return $db->delete($table,$condition);
	}
	public static function getPromoteByChannelId($cid){
		$table=array("t_mgr_promote");
		$db = self::InitDB('db_mgr','query');
		return $db->select($table,array("fk_channel"=>$cid));
	}
	public static function updateChannel($cid,$data){

		$db = self::InitDB('db_mgr');
        $table = array('t_mgr_channel');
		$condition = array('pk_channel' => $cid);

		return $db->update($table, $condition, $data);
	}
	public function getPromoteList($where = array(),$page = 1,$size = 10,$order=array()){
		//define('DEBUG',true);
		$db    = self::InitDB('db_mgr','query');
        $table = array('t_mgr_promote');
        $db->setPage($page);
        $db->setLimit($size);
        $condition = array();
        
        if(isset($where['fk_user_owner']) && $where['fk_user_owner']){
        	$condition['fk_user_owner'] = $where['fk_user_owner'];
        }
        if(isset($where['fk_channel']) && $where['fk_channel']){
        	$condition[self::PROMOTE_TABLE.".fk_channel"] = $where['fk_channel'];
        }
		if(isset($where['fk_channel_in']) && $where['fk_channel_in']){
			$condition[] = self::PROMOTE_TABLE.".".$where['fk_channel_in'];
		}
        $item = array(
        	"pk_promote",
        	"fk_user_owner",
        	"subdomain",
        	"t_mgr_promote.fk_channel",
        	"promote_code",
        	"t_mgr_promote.status as `status`",
        	"t_mgr_promote.create_time as `create_time`",
        	"t_mgr_promote.update_time as `update_time`",
        	"t_mgr_subchannel.pk_sub_channel as `pk_sub_channel`",
        	"t_mgr_subchannel.name as `name`",
			"t_mgr_subchannel.fk_channel as `parent_channel`",
        	"t_mgr_subchannel.last_update as `last_update`"
        	);

        //$leftjoin = self::SUB_CHANNEL_TABLE." ON ".self::SUB_CHANNEL_TABLE.".pk_sub_channel=".self::PROMOTE_TABLE.".fk_channel";
        
        $leftjoin = new stdclass;
        $leftjoin->t_mgr_subchannel = "t_mgr_subchannel.pk_sub_channel=t_mgr_promote.fk_channel";
        
        return $db->select($table, $condition, $item, '', $order,$leftjoin);
	}
	public function getPromoteListByChannelids($condition){
		$db    = self::InitDB('db_mgr','query');
        $table = 't_mgr_promote';

        return $db->select($table, $condition->fk_channel);
	}
	public function addPromote($data){
		$table=array("t_mgr_promote");
		$db = self::InitDB('db_mgr');

		$pid = $db->insert($table,$data);
		$code = '';
		$count = 4-strlen($pid);
		for($i=0;$i<$count;$i++){
			 $code .= chr(rand(97, 122));
		}
		$code =$code.$pid;
		self::updatePromote($pid,array('promote_code'=>$code));
		return $pid;
	}
	//修改promote
	public static function updatePromote($pid,$data){

		$db = self::InitDB('db_mgr');
        $table = array('t_mgr_promote');
		$condition = array('pk_promote' => $pid);

		return $db->update($table, $condition, $data);
	}
	//删除promote
	public function delPromote($pid){
		$db = self::InitDB('db_mgr');
        $table = array('t_mgr_promote');
        
		$condition = array('pk_promote' => $pid);
 		return $db->delete($table,$condition);
	}
	//获取子渠道
	public function getsubchannelList($where = array(),$page = 1,$size = 10,$order=array()){
		$db    = self::InitDB('db_mgr','query');
        $table = 't_mgr_subchannel';
        $db->setPage($page);
        $db->setLimit($size);

        return $db->select($table, $where, '*', '', $order);
	}
	//添加子渠道
	public function subchannelAdd($data){
		$table=array("t_mgr_subchannel");
		$db = self::InitDB('db_mgr');

		return $db->insert($table,$data);
	}
	//获取子分类
	public function getSubChannel($cid){
		//define('DEBUG',true);
		$db    = self::InitDB('db_mgr','query');
		$table = array("t_mgr_subchannel");
		return $db->selectOne($table,array('pk_sub_channel'=>$cid));
	}
	//修改子分类
	public static function updateSubChannel($cid,$data){

		$db = self::InitDB('db_mgr');
        $table = array('t_mgr_subchannel');
		$condition = array('pk_sub_channel' => $cid);

		return $db->update($table, $condition, $data);
	}
	//删除子分类
	public function delSubChannel($cid){
		$db = self::InitDB('db_mgr');
        $table = array('t_mgr_subchannel');
        
		$condition = array('pk_sub_channel' => $cid);
 		return $db->delete($table,$condition);
	}
	//添加推广注册用户表
	public function addPromoteUser($data){
		$table=array("t_mgr_promote_user");
		$db = self::InitDB('db_mgr');
		return $db->insert($table,$data);
	}

}
?>
