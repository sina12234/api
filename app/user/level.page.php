<?php
class user_level{

	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	
	public function pageChangeUserLevelAndScore($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->fk_user) || empty($params->rule_name)){
			return $this->setResult('',-1,'params is error');	
		}	
		$res = user_api::addUserScore($params->fk_user,$params->rule_name);
		return $res;
	}
	
	public function pageAddUserSign($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->day) || empty($params->fk_user) || empty($params->combo)){
			return $this->setResult('',-1,'params is error');	
		}
		$data = array();
		$data['day'] = $params->day;
		$data['fk_user'] = $params->fk_user;
		$data['combo'] = $params->combo;
		$data['create_time'] = date('Y-m-d H:i:s',time());
		$user_db = new user_db;
		$res = $user_db->addUserSign($data);
		
		if($res !== false){
			return $this->setResult(1);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageAddUserScore($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->fk_level) || empty($params->fk_user) || empty($params->title) || empty($params->score)){
			return $this->setResult('',-1,'params is error');	
		}
		$data = array();
		$data['fk_level'] = $params->fk_level;
		$data['fk_user'] = $params->fk_user;
		$data['title'] = $params->title;
		$data['score'] = $params->score;
		$user_db = new user_db;
		$res = $user_db->addUserScore($data);
		if($res !== false){
			return $this->setResult(1);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageUpdateUserScore($inPath){
		$params=SJson::decode(utility_net::getPostData(),true);
		$uid = $inPath[3];
		if(empty($params) || empty($uid)) {
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->updateUserScore($uid,$params);
		if($res === false){
			return $this->setResult('',-2,'get data is failed');
		}else{
			return $this->setResult(1);
		}
	}

	public function pageAddUserScoreLog($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->day) || empty($params->fk_user) || empty($params->fk_rule) || empty($params->score)){
			return $this->setResult('',-1,'params is error');	
		}
		$data = array();
		$data['day'] = $params->day;
		$data['fk_user'] = $params->fk_user;
		$data['fk_rule'] = $params->fk_rule;
		$data['score'] = $params->score;
		$user_db = new user_db;
		$res = $user_db->addUserScoreLog($data);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetUserLevel($inPath){
		$uid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($uid) && !is_numeric($uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getUserLevelByUid($uid);
		
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetNextLevel($inPath){
		$fk_level = !empty($inPath[3])?$inPath[3]:'';
		if(empty($fk_level) && !is_numeric($fk_level)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getNextLevel($fk_level);
		
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetPreAndNextLevel($inPath){
		$pk_level = !empty($inPath[3])?$inPath[3]:'';
		if(empty($pk_level) && !is_numeric($pk_level)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getPreAndNextLevel($pk_level);
		
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetUserSignByDay($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->day) && empty($params->uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getUserSignByDay($params->day,$params->uid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}

	public function pageGetLastUserSign($inPath){
		$uid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($uid) && !is_numeric($uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getLastUserSign($uid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	public function pageGetScoreRuleByName($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getScoreRuleByName($params->name);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetGtUserScoreCount($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uid) && empty($params->score)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getGtUserScoreCount($params->uid,$params->score);
		if(!empty($res->items[0])){
			return $this->setResult($res->items[0]['count']);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetAllUserCount($inPath){
		
		$user_db = new user_db;
		$res = $user_db->getAllUserCount();
		if(!empty($res->items[0])){
			return $this->setResult($res->items[0]['count']);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetUserRankByDate($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->start_date) && empty($params->end_date)){
			return $this->setResult('',-1,'params is error');	
		}
		$page = isset($params->page)?$params->page:'';
		$length = isset($params->length)?$params->length:'';
		$user_db = new user_db;
		$res = $user_db->getUserRankByDate($params->start_date,$params->end_date,$page,$length);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetUserSortByDate($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->start_date) && empty($params->end_date) && empty($params->uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getUserSortByDate($params->uid,$params->start_date,$params->end_date);
		if(!empty($res)){
			return $this->setResult($res['sort']);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetAllUserRank($inPath){
		$user_db = new user_db;
		$params=SJson::decode(utility_net::getPostData());
		$page = isset($params->page)?$params->page:'';
		$length = isset($params->length)?$params->length:'';
		$res = $user_db->getAllUserRank($page,$length);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetUserAllSortByUid($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->uid)){
			return $this->setResult('',-1,'params is error');	
		}
		$user_db = new user_db;
		$res = $user_db->getUserAllSortByUid($params->uid);
		if(!empty($res)){
			return $this->setResult($res['sort']);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	
}
