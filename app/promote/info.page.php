<?php 
class promote_info extends STpl{
	
	public function pageGet($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-1';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$pid = (int)($inPath[3]);
		$db = new promote_db();
		$promote = $db->getPromote($pid);
		if(empty($promote)){
			$ret->result->code = '-2';
			$ret->result->msg = 'promote does not found';
			return $ret;
		}
		$ret->result->data = $promote;
		return $ret;
	}
	//添加渠道
	public function pageChannelAdd(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $promote_db=new promote_db; 
        if(empty($params->name)){
        	$ret->result->code = -2;
        	$ret->result->msg = "name is empty";
        	return $ret;
        }
        $data = array();
        $data['name'] = $params->name;
        $data['status'] = $params->status;
        $data['create_time'] = $params->create_time;
        $channel_id = $promote_db->channelAdd($data);
        if(empty($channel_id)){
        	$ret->result->code = -3;
        	$ret->result->msg = 'insert fail';
        	return $ret;
        }else{
        	$ret->result->code = 0;
        	$ret->result->msg = 'success';
        	return $ret;
        }
	}
	//获取渠道
	public function pageChannelList(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		$where = array();
		if(isset($params->where) && $params->where){
			foreach ($params->where as $key => $value) {
				$where[$key] = $value;
			}
		}
		if(isset($params->page) && $params->page){
			$page = $params->page;
		}else{
			$page = 1;
		}
		$order = array();
		if(isset($params->orderby) && $params->orderby){
			foreach ($params->orderby as $key => $value) {
				$order[$key] = $value;
			}
		}
		if(isset($params->size) && $params->size){
			$size = $params->size;
		}else{
			$size = 10;
		}
		$promote_db = new promote_db();
		$list = $promote_db->getChannelList($where,$page,$size,$order);
		if($list->items){
			$ret->result->data = $list;
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}else{
			$ret->result->code = '-2';
			$ret->result->msg = 'data is empty';
		}
		return $ret;

	}
	public function pagegetChannel($inPath){
		$cid = isset($inPath[3]) ? (int)($inPath[3]) :0;
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$db = new promote_db();
		$channel = $db->getChannel($cid);
		if(empty($channel)){
			$ret->result->code = '-3';
			$ret->result->msg = 'promote does not found';
			return $ret;
		}
		$ret->result->data = $channel;
		return $ret;

	}

	public function pageeditChannel($inPath){
		$cid = isset($inPath[3]) ? (int)($inPath[3]) :0;
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";

		if(empty($cid) || !is_numeric($cid) || empty($params)){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$db = new promote_db();
		$ret_db = $db->updateChannel($cid,$params);
		if($ret_db){
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}
		
		return $ret;
	}
	public function pagedelChannel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-1';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$cid = (int)($inPath[3]);

		$db = new promote_db();
		$promote = $db->getPromoteByChannelId($cid);
	//	print_r($promote);

		if(!empty($promote->items)){
			$ret->result->code = '-3';
			$ret->result->msg = 'the promote is not empty';
			return $ret;
		}
		$ret_del = $db->delChannel($cid);
		if($ret_del){
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}
		
		return $ret;
	}
	//推广列表
	public function pagePromoteList($inPath){
		

		$params=SJson::decode(utility_net::getPostData());
		
		
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		$where = array();
		
		if(isset($params->where) && $params->where){
			foreach ($params->where as $key => $value) {
				$where[$key] = $value;
			}
		}
		if(isset($params->page) && $params->page){
			$page = $params->page;
		}else{
			$page = 1;
		}
		$order = array();
		if(isset($params->orderby) && $params->orderby){
			foreach ($params->orderby as $key => $value) {
				$order[$key] = $value;
			}
		}
		if(isset($params->size) && $params->size){
			$size = $params->size;
		}else{
			$size = 10;
		}
		$promote_db = new promote_db();

		

		$list = $promote_db->getPromoteList($where,$page,$size,$order);

		if($list->items){
			$ret->result->data = $list;
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}else{
			$ret->result->code = '-2';
			$ret->result->msg = 'data is empty';
		}
		return $ret;
	}
	public function pagegetPromoteListByChannelids(){
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		$params=SJson::decode(utility_net::getPostData());
		$promote_db = new promote_db();

		

		$list = $promote_db->getPromoteListByChannelids($params);
		if($list->items){
			$ret->result->data = $list;
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}else{
			$ret->result->code = '-2';
			$ret->result->msg = 'data is empty';
		}
		return $ret;
	}
	//添加渠道
	public function pageaddPromote(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $promote_db=new promote_db; 
        if(empty($params->fk_user_owner) || empty($params->subdomain) || empty($params->fk_channel)){
        	$ret->result->code = -2;
        	$ret->result->msg = "params is empty";
        	return $ret;
        }
        $data = array();
        
        $data['fk_user_owner'] = $params->fk_user_owner;
        $data['fk_channel'] = $params->fk_channel;
        $data['subdomain'] = $params->subdomain;
        $data['create_time'] = $params->create_time;
        $data['status'] = $params->status;
        //查找是否存在了同一机构和同一渠道下的推广
        $where = new stdclass;
        $where->fk_user_owner = $params->fk_user_owner;
        $where->fk_channel = $params->fk_channel;
        $promote = $promote_db->getPromoteByOther($where);

        if(isset($promote->items) && $promote->items){
        	$ret->result->code = -4;
        	$ret->result->msg = 'data is exsites';
        	return $ret;
        }
        $promote_id = $promote_db->addPromote($data);
        if(empty($promote_id)){
        	$ret->result->code = -3;
        	$ret->result->msg = 'insert fail';
        	return $ret;
        }else{
        	$ret->result->code = 0;
        	$ret->result->msg = 'success';
        	return $ret;
        }
	}
	//获取pomote
	public function pagegetPromote($inPath){
		$pid = isset($inPath[3]) ? (int)($inPath[3]) :0;
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$db = new promote_db();
		$promote = $db->getPromote($pid);
		if(empty($promote)){
			$ret->result->code = '-3';
			$ret->result->msg = 'promote does not found';
			return $ret;
		}
		$ret->result->data = $promote;
		return $ret;

	}
	//获取pomote
	public function pagegetPromoteByOther(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($params)){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		
		$db = new promote_db();
		$promote = $db->getPromoteByOther($params);
		if(empty($promote)){
			$ret->result->code = '-3';
			$ret->result->msg = 'promote does not found';
			return $ret;
		}
		$ret->result->data = $promote;
		return $ret;

	}
	//修改promote
	public function pageeditPromote($inPath){
		$pid = isset($inPath[3]) ? (int)($inPath[3]) :0;
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";

		if(empty($pid) || !is_numeric($pid) || empty($params)){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$db = new promote_db();
		$ret_db = $db->updatePromote($pid,$params);
		if($ret_db){
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}
		
		return $ret;
	}
	//删除promote
	public function pagedelPromote($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-1';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$pid = (int)($inPath[3]);
		$db = new promote_db();
		
		$ret_del = $db->delPromote($pid);
		if($ret_del){
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}
		
		return $ret;
	}
	//获取子分类渠道
	public function pagesubchannelList(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = -1;
		$ret->result->msg = "";
		$where = array();
		if(isset($params->where) && $params->where){
			foreach ($params->where as $key => $value) {
				$where[$key] = $value;
			}
		}
		if(isset($params->page) && $params->page){
			$page = $params->page;
		}else{
			$page = 1;
		}
		$order = array();
		if(isset($params->orderby) && $params->orderby){
			foreach ($params->orderby as $key => $value) {
				$order[$key] = $value;
			}
		}
		if(isset($params->size) && $params->size){
			$size = $params->size;
		}else{
			$size = 10;
		}
		$promote_db = new promote_db();
		$list = $promote_db->getsubchannelList($where,$page,$size,$order);
		if($list->items){
			$ret->result->data = $list;
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}else{
			$ret->result->code = '-2';
			$ret->result->msg = 'data is empty';
		}
		return $ret;

	}
	//添加子渠道
	public function pageSubChannelAdd(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $promote_db=new promote_db; 
        if(empty($params->name)){
        	$ret->result->code = -2;
        	$ret->result->msg = "name is empty";
        	return $ret;
        }
        $data = array();
        $data['name'] = $params->name;
        $data['status'] = $params->status;
        $data['create_time'] = $params->create_time;
       	$data['fk_channel'] = $params->fk_channel;

        $sub_channel_id = $promote_db->subchannelAdd($data);
        if(empty($sub_channel_id)){
        	$ret->result->code = -3;
        	$ret->result->msg = 'insert fail';
        	return $ret;
        }else{
        	$ret->result->code = 0;
        	$ret->result->msg = 'success';
        	return $ret;
        }
	}
	//获取子分类
	public function pagegetSubChannel($inPath){
		$cid = isset($inPath[3]) ? (int)($inPath[3]) :0;
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$db = new promote_db();
		$channel = $db->getSubChannel($cid);
		if(empty($channel)){
			$ret->result->code = '-3';
			$ret->result->msg = 'promote does not found';
			return $ret;
		}
		$ret->result->data = $channel;
		return $ret;

	}
	//修改子分类
	public function pageeditSubChannel($inPath){
		$cid = isset($inPath[3]) ? (int)($inPath[3]) :0;
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";

		if(empty($cid) || !is_numeric($cid) || empty($params)){
			$ret->result->code = '-2';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$db = new promote_db();
		$ret_db = $db->updateSubChannel($cid,$params);
		if($ret_db){
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}
		
		return $ret;
	}
	public function pagedelSubChannel($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if(empty($inPath[3]) || !is_numeric($inPath[3])){
			$ret->result->code = '-1';
			$ret->result->msg = 'params error';
			return $ret;
		}
		$cid = (int)($inPath[3]);

		$db = new promote_db();
		$promote = $db->getPromoteByChannelId($cid);
	//	print_r($promote);

		if(!empty($promote->items)){
			$ret->result->code = '-3';
			$ret->result->msg = 'the promote is not empty';
			return $ret;
		}
		$ret_del = $db->delSubChannel($cid);
		if($ret_del){
			$ret->result->code = 1;
			$ret->result->msg = 'success';
		}
		
		return $ret;
	}
	//添加注册推广用户表
	public function pageaddPromoteUser(){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $promote_db=new promote_db; 
        if(empty($params->fk_user) || empty($params->fk_promote)){
        	$ret->result->code = -2;
        	$ret->result->msg = "params is empty";
        	return $ret;
        }
        $data = array();
        
        $data['fk_user'] = $params->fk_user;
        $data['fk_promote'] = $params->fk_promote;
        $data['create_time'] = $params->create_time;

        $promote_user_id = $promote_db->addPromoteUser($data);
        if(empty($promote_user_id)){
        	$ret->result->code = -3;
        	$ret->result->msg = 'insert fail';
        	return $ret;
        }else{
        	$ret->result->code = 0;
        	$ret->result->msg = 'success';
        	return $ret;
        }
	}
}
?>
