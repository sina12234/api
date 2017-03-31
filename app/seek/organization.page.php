<?php
require("region.php");
require("subject.php");
class seek_organization{
	var $attrs = array(
		'org_id'=>'pk_org',
		'user_owner_id'=>'user_owner_id',
		'name'=>'name',
		'subname'=>'subname',
		'subdomain'=>'subdomain',
		'desc'=>'descript',
		'hotline'=>'hotline',
		'email'=>'email',
		'scopes'=>'scopes',
		'hot_type'=>'hot_type',
		'thumb_big'=>'thumb_big',
		'thumb_med'=>'thumb_med',
		'thumb_sma'=>'thumb_small',
		'create_time'=>'create_time',
		'province'=>'province',
		'city'=>'city',
		'address'=>'address',
		'status'=>'status',
		'vv' => 'vv',
		'vv_live' => 'vv_live',
		'vv_record' => 'vv_record',
		'vt' => 'vt',
		'vt_record' => 'vt_record',
		'vt_live' => 'vt_live',
		'zan' => 'zan',
		'is_pro' => 'is_pro',
		'have_app' => 'have_app',
		'comment_count'=> 'comment_count',
		'teacher_count' => 'teacher_count',
		'visiable_teacher_count'=>'visiable_teacher_count',
		'course_count' => 'course_count',
		'class_count' => 'class_count',
		'student_count'=>'student_count',
		'member_count'=>'member_count',
		'order_count' => 'order_count',
		'discuss' => 'discuss',
		'balance' => 'balance',
		'income_all' => 'income_all',
		'withdraw' => 'withdraw',
		'income_last_week' => 'income_last_week',
		'orders_last_week' => 'orders_last_week',
		'income_last_month' => 'income_last_month',
		'orders_last_month' => 'orders_last_month',
		'search_field'=>'search_field',
	);
	var $timesArr = array(
						  'create_time'=>0,
					     );
	public function pageList($inPath){
		utility_cache::pageCache(60);
		$timesArr = $this->timesArr;
		$tmpAttrs = $this->attrs;
		//获取列表字段
		$params = SJson::decode(utility_net::getPostData());
		$field = $order = array();

		if(empty((array)($params->f))){
			$field = array(
				'org_id',
				'name',
				'subname',
				'subdomain',
				'create_time',
			);
		}else{
			foreach($params->f as $k=>$v){
				$field[] = $v;
			}
		}
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$address = $conf->seek_organization_ip;
		$port = intval($conf->seek_organization_port);
		$cl = new SSphinx();
		$cl->SetServer ( $address, $port);
		$cl->ResetFilters();
		$cl->ResetGroupBy();
		$cl->SetArrayResult(true);
		$cl->SetMatchMode ( $mode=SPH_MATCH_EXTENDED );
		//获取查询条件
		if(!empty($params->q)){
			foreach($tmpAttrs as $k=>$v){
				if(isset($params->q->$k)){
					$queryArr[$k] = $params->q->$k;
				}
			}
		}else{
			$queryArr = array();
		}
		//new获取order by 信息
		if(!empty($params->ob)){
			foreach($tmpAttrs as $k=>$v){
				if(isset($params->ob->$k)){
					$order[$k] = $params->ob->$k;
				}
			}
		}
		if(count($order) > 0){
			$orderStr = '';
			foreach($order as $orderk=>$orderv){
				$orderStr .= $this->attrs[$orderk].' '.$orderv.',';
			}
			$orderStr = substr($orderStr,0,-1);
			$cl->SetSortMode (SPH_SORT_EXTENDED ,$orderStr);
		}else{
			$cl->SetSortMode (SPH_SORT_EXTENDED ,"create_time desc");
		}

		//new获取翻页信息
		if(empty($params->p)){
			$page = 1;
		}else{
			$page = $params->p;
		}
		if(empty($params->pl)){
			$pageLength = 1;
		}else{
			$pageLength = $params->pl;
		}
		//设置attr query
		$queriesStr= '';
		if(count($queryArr)>0){
			if(isset($queryArr['org_id'])){
				$qArr = explode(',', $queryArr['org_id']);
				$cl->setFilter($tmpAttrs['org_id'], $qArr);	
			}
			if(isset($queryArr['scopes'])){
				$qArr = explode(',', $queryArr['scopes']);
				$cl->setFilter($tmpAttrs['scopes'], $qArr);
			}
			if(isset($queryArr['user_owner_id'])){
				$qArr = explode(',', $queryArr['user_owner_id']);
				$cl->setFilter($tmpAttrs['user_owner_id'], $qArr);
			}
			if(isset($queryArr['is_pro'])){
				$qArr = explode(',', $queryArr['is_pro']);
				$cl->setFilter($tmpAttrs['is_pro'], $qArr);
			}
			if(isset($queryArr['have_app'])){
				$qArr = explode(',', $queryArr['have_app']);
				$cl->setFilter($tmpAttrs['have_app'], $qArr);
			}
			if(isset($queryArr['comment'])){
				$qArr = explode(',', $queryArr['comment']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['comment'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'comment syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['comment'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'comment syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['create_time'])){
				$qArr = explode(',', $queryArr['create_time']);
				if(count($qArr)<>2){
					$ret['error'] = 1;
					$ret['error_desc'] = 'create_time syntax error';
					return $ret;
				}
				$tmpBegin = strtotime($qArr[0]);
				$tmpEnd = strtotime($qArr[1]);
				if(FALSE === $tmpBegin || FALSE === $tmpEnd){
					$ret['error'] = 1;
					$ret['error_desc'] = 'create_time syntax error';
					return $ret;
				}
				$cl->setFilterRange($tmpAttrs['create_time'], $tmpBegin, $tmpEnd);	
			}
			if(isset($queryArr['hot_type'])){
				$qArr = explode(',', $queryArr['hot_type']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['hot_type'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'hot_type syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['hot_type'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'hot_type syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['province'])){
				$qArr = explode(',', $queryArr['province']);
				$cl->setFilter($tmpAttrs['province'], $qArr);
			}
			if(isset($queryArr['city'])){
				$qArr = explode(',', $queryArr['city']);
				$cl->setFilter($tmpAttrs['city'], $qArr);
			}
			if(isset($queryArr['status'])){
				$qArr = explode(',', $queryArr['status']);
				$cl->setFilter($tmpAttrs['status'], $qArr);
			}
			if(isset($queryArr['vv'])){
				$qArr = explode(',', $queryArr['vv']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['vv'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'vv syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['vv'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'vv syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['vv_live'])){
				$qArr = explode(',', $queryArr['vv_live']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['vv_live'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'vv_live syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['vv_live'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'vv_live syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['vv_record'])){
				$qArr = explode(',', $queryArr['vv_record']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['vv_record'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'vv_record syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['vv_record'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'vv_record syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['vt'])){
				$qArr = explode(',', $queryArr['vt']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['vt'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'vt syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['vt'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'vt syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['vt_record'])){
				$qArr = explode(',', $queryArr['vt_record']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['vt_record'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'vt_record syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['vt_record'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'vt_record syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['vt_live'])){
				$qArr = explode(',', $queryArr['vt_live']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['vt_live'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'vt_live syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['vt_live'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'vt_live syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['zan'])){
				$qArr = explode(',', $queryArr['zan']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['zan'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'zan syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['zan'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'zan syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['course_count'])){
				$qArr = explode(',', $queryArr['course_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['course_count'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'course_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['course_count'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'course_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['class_count'])){
				$qArr = explode(',', $queryArr['class_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['class_count'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'class_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['class_count'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'class_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['order_count'])){
				$qArr = explode(',', $queryArr['order_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['order_count'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'order_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['order_count'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'order_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['discuss'])){
				$qArr = explode(',', $queryArr['discuss']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['discuss'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'discuss syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['discuss'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'discuss syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['balance'])){
				$qArr = explode(',', $queryArr['balance']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['balance'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'balance syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['balance'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'balance syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['income_all'])){
				$qArr = explode(',', $queryArr['income_all']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['income_all'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'income_all syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['income_all'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'income_all syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['withdraw'])){
				$qArr = explode(',', $queryArr['withdraw']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['withdraw'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'withdraw syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['withdraw'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'withdraw syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['income_last_week'])){
				$qArr = explode(',', $queryArr['income_last_week']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['income_last_week'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'income_last_week syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['income_last_week'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'income_last_week syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['orders_last_week'])){
				$qArr = explode(',', $queryArr['orders_last_week']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['orders_last_week'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'orders_last_week syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['orders_last_week'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'orders_last_week syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['income_last_month'])){
				$qArr = explode(',', $queryArr['income_last_month']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['income_last_month'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'income_last_month syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['income_last_month'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'income_last_month syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['orders_last_month'])){
				$qArr = explode(',', $queryArr['orders_last_month']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['orders_last_month'], $qArr);
				}else if(count($qArr) ==2 ){
					$tmpBegin = intval($qArr[0]);
					$tmpEnd = intval($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'orders_last_month syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['orders_last_month'], $tmpBegin, $tmpEnd);
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'orders_last_month syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['name'])){
				$queriesStr .=" @name ".$queryArr['name'];
			}
			if(isset($queryArr['subname'])){
				$queriesStr .=" @subname ".$queryArr['subname'];
			}
			if(isset($queryArr['desc'])){
				$queriesStr .=" @descript ".$queryArr['desc'];
			}
			if(isset($queryArr['hotline'])){
				$queriesStr .=" @hotline ".$queryArr['hotline'];
			}
			if(isset($queryArr['email'])){
				$queriesStr .=" @email ".$queryArr['email'];
			}
			if(isset($queryArr['address'])){
				$queriesStr .=" @address ".$queryArr['address'];
			}
			if(isset($queryArr['search_field'])){
				$queriesStr .=" @search_field ".$queryArr['search_field'];
			}
		}
		//设置page
		$beginOff = ($page-1)*$pageLength;
		$cl->setLimits(intval($beginOff), intval($pageLength));
		//设置string query
		$res = $cl->Query($queriesStr,"org");
		//get matches
		$data = array();
		if(isset($res['matches']) && count($res['matches']) > 0){
			$i = 0;
			foreach($res['matches'] as $id=>$val){
				foreach($field as $k){
					if(isset($timesArr[$k])){
						$data[$i][$k] = date('Y-m-d H:i:s', $val['attrs'][$tmpAttrs[$k]]);	
					}else{
						$data[$i][$k] = $val['attrs'][$tmpAttrs[$k]];
					}
				}
				$i++;
			}
		}
		$ret = array();
		$ret['data'] = $data;
		$ret['total'] = $res['total'];
		$ret['page'] = $page;
		$ret['pagelength'] = $pageLength;
		$ret['time'] = $res['time'];
		return $ret;
	}
	public function pageGenerate($inPath){
    	echo '<?xml version="1.0" encoding="utf-8"?>
	    <sphinx:docset>
		<sphinx:schema>
		<sphinx:field name="name"/>
		<sphinx:field name="subname"/>
		<sphinx:field name="subdomain"/>
		<sphinx:field name="province"/>
		<sphinx:field name="city"/>
		<sphinx:field name="search_field"/>
		<sphinx:attr name="name" type="string"/>
		<sphinx:attr name="subname" type="string"/>
		<sphinx:attr name="thumb_big" type="string"/>
		<sphinx:attr name="thumb_med" type="string"/>
		<sphinx:attr name="thumb_small" type="string"/>
		<sphinx:attr name="descript" type="string"/>
		<sphinx:attr name="subdomain" type="string"/>
		<sphinx:attr name="province" type="string"/>
		<sphinx:attr name="city" type="string"/>
		<sphinx:attr name="address" type="string"/>
		<sphinx:attr name="search_field" type="string"/>
		<sphinx:attr name="hotline" type="string"/>
		<sphinx:attr name="email" type="string"/>
		<sphinx:attr name="scopes" type="multi"/>
		<sphinx:attr name="pk_org" type="int"/>
		<sphinx:attr name="user_owner_id" type="int"/>
		<sphinx:attr name="hot_type" type="int"/>
		<sphinx:attr name="teacher_count" type="int"/>
		<sphinx:attr name="visiable_teacher_count" type="int"/>
		<sphinx:attr name="status" type="bigint"/>
		<sphinx:attr name="vv_live" type="int"/>
		<sphinx:attr name="vv_record" type="int"/>
		<sphinx:attr name="vt_live" type="int"/>
		<sphinx:attr name="vt_record" type="int"/>
		<sphinx:attr name="vv" type="int"/>
		<sphinx:attr name="vt" type="int"/>
		<sphinx:attr name="zan" type="int"/>
		<sphinx:attr name="is_pro" type="int"/>
		<sphinx:attr name="have_app" type="int"/>
		<sphinx:attr name="comment_count" type="int"/>
		<sphinx:attr name="course_count" type="int"/>
		<sphinx:attr name="class_count" type="int"/>
		<sphinx:attr name="student_count" type="int"/>
		<sphinx:attr name="member_count" type="int"/>
		<sphinx:attr name="order_count" type="int"/>
		<sphinx:attr name="discuss" type="int"/>
		<sphinx:attr name="balance" type="bigint"/>
		<sphinx:attr name="income_all" type="bigint"/>
		<sphinx:attr name="withdraw" type="bigint"/>
		<sphinx:attr name="income_last_week" type="bigint"/>
		<sphinx:attr name="orders_last_week" type="bigint"/>
		<sphinx:attr name="income_last_month" type="bigint"/>
		<sphinx:attr name="orders_last_month" type="bigint"/>
		<sphinx:attr name="create_time" type="bigint"/>

		</sphinx:schema>';
		
		$dbStat = new stat_db;
		$dbUser = new user_db;
		$dbMemberSet = new user_db_orgMemberSetDao();
		$dbMember = new user_db_orgMemberDao();
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_organization_length;
		$orgList = $dbUser->listOrganizations( 1, $pageLength );
		$totalPage = $orgList->totalPage;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$orgList = $dbUser->listOrganizations($page,$pageLength);
			$ownerIdArr = $orgIdArr = $memberSetIdArr=  $cateIdArr= array();
			foreach($orgList->items as $k=>$v){
				$ownerIdArr[$v['user_owner_id']] = $v['pk_org'];
				$orgIdArr[$v['pk_org']] = $v['pk_org'];
			}
			$ownerIdStr = implode(',', array_keys($ownerIdArr));
			$orgIdStr = array_keys($orgIdArr);
			$memberSetList = $dbMemberSet->getListByOrgIds(implode(',',$orgIdStr));
			if(isset($memberSetList->items) && count($memberSetList->items)>0){
				foreach($memberSetList->items as $member){
					$memberSetIdArr[$member['pk_member_set']] = $member['fk_org'];
				}
			}
			if(count($memberSetIdArr)>0){
				$memberCountList = $dbMember->getUserCountByMemberSetArr(array_keys($memberSetIdArr));
				if(isset($memberCountList->items) && count($memberCountList->items)>0){
					foreach($memberCountList->items as $memberCount){
						if(!isset($memberCountData[$memberSetIdArr[$memberCount['fk_member_set']]]))
							$memberCountData[$memberSetIdArr[$memberCount['fk_member_set']]] = $memberCount['user_count'];
					}
				}else{
					$memberCountData = '';
				}
			}
			$teacherCountList = $dbUser->getOrgTeacherCount($orgIdStr);
			if(isset($teacherCountList->items) && count($teacherCountList->items)>0){
				foreach($teacherCountList->items as $teacher){
					if(!isset($teacherCountData[$orgIdArr[$teacher['fk_org']]]))
						$teacherCountData[$orgIdArr[$teacher['fk_org']]] = $teacher['teacher_count'];
				}
			}else{
				$teacherCountData = '';
			}
			$visiableTeacherCountList = $dbUser->getOrgTeacherCount($orgIdStr,1);
			if(isset($visiableTeacherCountList->items) && count($visiableTeacherCountList->items)>0){
				foreach($visiableTeacherCountList->items as $visiableTeacher){
					if(!isset($visiableTeacherCountData[$orgIdArr[$visiableTeacher['fk_org']]]))
						$visiableTeacherCountData[$orgIdArr[$visiableTeacher['fk_org']]] = $visiableTeacher['teacher_count'];
				}
			}else{
				$visiableTeacherCountData = '';
			}

			$domainList =  $dbUser->listDomainsByOwnerIds( $ownerIdStr );
			if(isset($domainList->items) && count($domainList->items)>0){
				foreach($domainList->items as $domain){
					if(!isset($domainData[$ownerIdArr[$domain['fk_user']]]))
						$domainData[$ownerIdArr[$domain['fk_user']]] = $domain['subdomain'];
				}
			}else{
				$domainData = '';
			}
			$orgStatList = $dbStat->listOrgstatByIds( $ownerIdStr );
			if(isset($orgStatList->items) && count($orgStatList->items)>0){
				foreach($orgStatList->items as $orgStat){
					$orgStatData[$ownerIdArr[$orgStat['fk_user']]] = $orgStat;
				}
			}else{
				$orgStatData = array();
			}
			$courseCount = course_db::getSeekOrgCourseCount(explode(",",$ownerIdStr));
			if(!empty($courseCount->items)){
				foreach($courseCount->items as $course){
					$orgStatData[$ownerIdArr[$course['fk_user']]]["course_count"] = $course["course_count"];
				}
			}
			if(isset($orgList->items) && count($orgList->items)>0){
				foreach($orgList->items as $org ){
					echo '<sphinx:document id="'.$org['pk_org'].'">'."\n";
					if(isset($domainData[$org['pk_org']])){
						$org['subdomain'] = $domainData[$org['pk_org']];	
					}else{
						$org['subdomain'] = '';
					}
					if(isset($memberCountData[$org['pk_org']])){
						$org['member_count'] = $memberCountData[$org['pk_org']];
					}else{
						$org['member_count'] = 0;
					}
					if(isset($teacherCountData[$org['pk_org']])){
						$org['teacher_count'] = $teacherCountData[$org['pk_org']];
					}else{
						$org['teacher_count'] = 0;
					}
					if(isset($visiableTeacherCountData[$org['pk_org']])){
						$org['visiable_teacher_count'] = $visiableTeacherCountData[$org['pk_org']];
					}else{
						$org['visiable_teacher_count'] = 0;
					}
					if(isset($orgStatData[$org['pk_org']])){
						if(!empty($orgStatData[$org['pk_org']]['vv_record'])){
							$org['vv_record'] = $orgStatData[$org['pk_org']]['vv_record'];
						}else{
							$org['vv_record'] =0;
						}
						if(!empty($orgStatData[$org['pk_org']]['vv_live'])) {
							$org['vv_live'] = $orgStatData[$org['pk_org']]['vv_live'];
						}else{
							$org['vv_live']=0;
						}
						if(!empty($orgStatData[$org['pk_org']]['vt_record'])) {
							$org['vt_record'] = $orgStatData[$org['pk_org']]['vt_record'];
						}else{
							$org['vt_record']=0;
						}
						if(!empty($orgStatData[$org['pk_org']]['zan'])) {
							$org['zan'] = $orgStatData[$org['pk_org']]['zan'];
						}else{
							$org['zan']=0;
						}
						if(!empty($orgStatData[$org['pk_org']]['comment'])) {
							$org['comment_count'] = $orgStatData[$org['pk_org']]['comment'];
						}else{
							$org['comment_count']=0;
						}
						if(!empty($orgStatData[$org['pk_org']]['discuss'])) {
							$org['discuss'] = $orgStatData[$org['pk_org']]['discuss'];
						}else{
							$org['discuss']=0;
						}
						if(!empty($orgStatData[$org['pk_org']]['course_count'])) {
							$org['course_count'] = $orgStatData[$org['pk_org']]['course_count'];
						}else{
							$org['course_count']=0;
						}
						if(!empty($orgStatData[$org['pk_org']]['class_count'])) {
							$org['class_count'] = $orgStatData[$org['pk_org']]['class_count'];
						}else{
							$org['class_count']=0;
						}
						if(empty($orgStatData[$org['pk_org']]['vv_live'])){
							$orgStatData[$org['pk_org']]['vv_live']=0;
						}
						if(empty($orgStatData[$org['pk_org']]['vv_record'])){
							$orgStatData[$org['pk_org']]['vv_record']=0;
						}
						$org['vv'] = $orgStatData[$org['pk_org']]['vv_live']+$orgStatData[$org['pk_org']]['vv_record'];
						if(empty($orgStatData[$org['pk_org']]['vt_live'])){
							$orgStatData[$org['pk_org']]['vt_live']=0;
						}
						if(empty($orgStatData[$org['pk_org']]['vt_record'])){
							$orgStatData[$org['pk_org']]['vt_record']=0;
						}
						$org['vt'] = $orgStatData[$org['pk_org']]['vt_live']+$orgStatData[$org['pk_org']]['vt_record'];
						if(empty($orgStatData[$org['pk_org']]['student_new'])){
							$orgStatData[$org['pk_org']]['student_new']=0;
						}
						$org['student_count']=$orgStatData[$org['pk_org']]['student_new'];
					}else{
						$org['vv'] = 0;
						$org['vv_live'] = 0;
						$org['vv_record'] = 0;
						$org['vt'] = 0;
						$org['vt_live'] = 0;
						$org['vt_record'] = 0;
						$org['zan'] = 0;
						$org['comment_count'] = 0;
						$org['discuss'] = 0;
						$org['course_count'] = 0;
						$org['class_count'] = 0;
						$org['student_count']=0;
					}
					if(NULL === $org['subname'])
						$org['subname'] = '';
					if(NULL === $org['descript'])
						$org['descript'] = '';
					if(NULL === $org['city'])
						$org['city'] = '';
					if(NULL === $org['province'])
						$org['province'] = '';
					if(NULL === $org['address'])
						$org['address'] = '';
					if(NULL === $org['hotline'])
						$org['hotline'] = '';
					if(NULL === $org['email'])
						$org['email'] = '';
					//数据库有NULL数据，该字段看以后是否去除
					if(NULL === $org['scopes'])
						$org['scopes'] = array(1);
					else
						$org['scopes'] = explode(',',$org['scopes']);
					if(NULL === $org['hot_type'])
						$org['hot_type'] = 1;
					if(NULL === $org['balance'])
						$org['balance'] = 0;
					if(NULL === $org['income_all'])
						$org['income_all'] = 0;
					if(NULL === $org['order_count'])
						$org['order_count'] = 0;
					if(NULL === $org['withdraw'])
						$org['withdraw'] = 0;
					if(NULL === $org['income_last_week'])
						$org['income_last_week'] = 0;
					if(NULL === $org['income_last_month'])
						$org['income_last_month'] = 0;
					if(NULL === $org['orders_last_week'])
						$org['orders_last_week'] = 0;
					if(NULL === $org['orders_last_month'])
						$org['orders_last_month'] = 0;
					$org['search_field'] = $org['subname'].' '.$org['descript'];
					foreach($org as $k=>$v){
						if(isset($timesArr[$k]))
							$v = strtotime($v);
						echo "\t<$k>";
						if(is_numeric($v)){
							echo $v;
						}elseif(is_array($v)){
							echo implode(',', $v);
						}else{
							echo "<![CDATA[$v]]>";
						}
						echo "</$k>\n";
					}
					echo "</sphinx:document>\n\n";
				}
			}
		}	
		echo '</sphinx:docset>';
	}
}
