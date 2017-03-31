<?php
require("region.php");
require("subject.php");
class seek_teacher{
	var $attrs = array(
		'teacher_id'=>'pk_user',
		'org_id'=>'org_id',
		'role'=>'role',
		'name'=>'name',
		'visiable'=>'visiable',
		'real_name'=>'real_name',
		'birthday'=>'birthday',
		'gender'=>'gender',
		'user_status'=>'user_status',
		'verify_status'=>'verify_status',
		'teacher_status'=>'teacher_status',
		'thumb_big'=>'thumb_big',
		'thumb_med'=>'thumb_med',
		'thumb_sma'=>'thumb_small',
		'register_ip'=>'register_ip',
		'create_time'=>'create_time',
		'last_login'=>'last_login',
		'mobile'=>'mobile',
		'title'=>'title',
		'college'=>'college',
		'years'=>'years',
		'diploma'=>'diploma',
		'subject_id'=>'subject_id',
		'grade_id'=>'grade_id',
		'desc'=>'descript',
		'brief_desc'=>'brief_desc',
		'org_name'=>'org_name',
		'org_subname'=>'org_subname',
		'province'=>'province',
		'city'=>'city',
		'course_count' => 'course_count',
		'course_on_count'=> 'course_on_count',
		'course_off_count'=> 'course_off_count',
		'course_complete_count' => 'course_complete_count',
		'course_remain_count' => 'course_remain_count',
		'student_count'=>'student_count',
		'avg_score'=>'avg_score',
		'score_user_count' => 'score_user_count',
		'student_score'=>'student_score',
		'desc_score'=>'desc_score',
		'explain_score'=>'explain_score',
		'totaltime'=> 'totaltime',
		'comment' => 'comment',
		'weight' => 'weight',
		'search_field'=>'search_field',
	);
	var $timesArr = array('last_login'=>0,
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
				'teacher_id',
				'name',
				'real_name',
				'last_login',
				'create_time',
			);
		}else{
			foreach($params->f as $k=>$v){
				$field[] = $v;
			}
		}
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$address = $conf->seek_teacher_ip;
		$port = (int)($conf->seek_teacher_port);
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
			if(isset($queryArr['teacher_id'])){
				$qArr = explode(',', $queryArr['teacher_id']);
				$cl->setFilter($tmpAttrs['teacher_id'], $qArr);	
			}
			if(isset($queryArr['org_id'])){
				$qArr = explode(',', $queryArr['org_id']);
				$cl->setFilter($tmpAttrs['org_id'], $qArr);	
			}
			if(isset($queryArr['grade_id'])){
				$qArr = explode(',', $queryArr['grade_id']);
				$cl->setFilter($tmpAttrs['grade_id'], $qArr);	
			}
			if(isset($queryArr['subject_id'])){
				$qArr = explode(',', $queryArr['subject_id']);
				$cl->setFilter($tmpAttrs['subject_id'], $qArr);	
			}
			if(isset($queryArr['role'])){
				$qArr = explode(',', $queryArr['role']);
				$cl->setFilter($tmpAttrs['role'], $qArr);	
			}
			if(isset($queryArr['gender'])){
				$qArr = explode(',', $queryArr['gender']);
				$cl->setFilter($tmpAttrs['gender'], $qArr);	
			}
			if(isset($queryArr['user_status'])){
				$qArr = explode(',', $queryArr['user_status']);
				$cl->setFilter($tmpAttrs['user_status'], $qArr);	
			}
			if(isset($queryArr['verify_status'])){
				$qArr = explode(',', $queryArr['verify_status']);
				$cl->setFilter($tmpAttrs['verify_status'], $qArr);	
			}
			if(isset($queryArr['visiable'])){
				$qArr = explode(',', $queryArr['visiable']);
				$cl->setFilter($tmpAttrs['visiable'], $qArr);	
			}
			if(isset($queryArr['teacher_status'])){
				$qArr = explode(',', $queryArr['teacher_status']);
				$cl->setFilter($tmpAttrs['teacher_status'], $qArr);	
			}
			if(isset($queryArr['course_count'])){
				$qArr = explode(',', $queryArr['course_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['course_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
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
			if(isset($queryArr['course_on_count'])){
				$qArr = explode(',', $queryArr['course_on_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['course_on_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'course_on_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['course_on_count'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'course_on_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['course_off_count'])){
				$qArr = explode(',', $queryArr['course_off_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['course_off_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'course_off_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['course_off_count'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'course_off_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['course_complete_count'])){
				$qArr = explode(',', $queryArr['course_complete_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['course_complete_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'course_complete_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['course_complete_count'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'course_complete_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['course_remain_count'])){
				$qArr = explode(',', $queryArr['course_remain_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['course_remain_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'course_remain_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['course_remain_count'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'course_remain_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['student_count'])){
				$qArr = explode(',', $queryArr['student_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['student_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'student_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['student_count'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'student_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['avg_score'])){
				$qArr = explode(',', $queryArr['avg_score']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['avg_score'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'avg_score syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['avg_score'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'avg_score syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['score_user_count'])){
				$qArr = explode(',', $queryArr['score_user_count']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['score_user_count'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'score_user_count syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['score_user_count'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'score_user_count syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['student_score'])){
				$qArr = explode(',', $queryArr['student_score']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['student_score'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'student_score syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['student_score'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'student_score syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['desc_score'])){
				$qArr = explode(',', $queryArr['desc_score']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['desc_score'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'desc_score syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['desc_score'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'desc_score syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['explain_score'])){
				$qArr = explode(',', $queryArr['explain_score']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['explain_score'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'explain_score syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['explain_score'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'explain_score syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['totaltime'])){
				$qArr = explode(',', $queryArr['totaltime']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['totaltime'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'totaltime syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['totaltime'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'totaltime syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['comment'])){
				$qArr = explode(',', $queryArr['comment']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['comment'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
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
			if(isset($queryArr['weight'])){
				$qArr = explode(',', $queryArr['weight']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['weight'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'weight syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['weight'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'weight syntax error';
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
			if(isset($queryArr['last_login'])){
				$qArr = explode(',', $queryArr['last_login']);
				if(count($qArr)<>2){
					$ret['error'] = 1;
					$ret['error_desc'] = 'last_login syntax error';
					return $ret;
				}
				$tmpBegin = strtotime($qArr[0]);
				$tmpEnd = strtotime($qArr[1]);
				if(FALSE === $tmpBegin || FALSE === $tmpEnd){
					$ret['error'] = 1;
					$ret['error_desc'] = 'last_login syntax error';
					return $ret;
				}
				$cl->setFilterRange($tmpAttrs['last_login'], $tmpBegin, $tmpEnd);	
			}
			if(isset($queryArr['name'])){
				$queriesStr .=" @name ".$queryArr['name'];
			}
			if(isset($queryArr['real_name'])){
				$queriesStr .=" @real_name ".$queryArr['real_name'];
			}
			if(isset($queryArr['title'])){
				$queriesStr .=" @title ".$queryArr['title'];
			}
			if(isset($queryArr['org_name'])){
				$queriesStr .=" @org_name ".$queryArr['org_name'];
			}
			if(isset($queryArr['org_subname'])){
				$queriesStr .=" @org_subname ".$queryArr['org_subname'];
			}
			if(isset($queryArr['mobile'])){
				$queriesStr .=" @mobile ".$queryArr['mobile'];
			}
			if(isset($queryArr['search_field'])){
				$queriesStr .=" @search_field ".$queryArr['search_field'];
			}
			if(isset($queryArr['province'])){
				$queriesStr .=" @province ".$queryArr['province'];
			}
			if(isset($queryArr['city'])){
				$queriesStr .=" @city ".$queryArr['city'];
			}
		}
		//设置page
		$beginOff = ($page-1)*$pageLength;
		$cl->setLimits((int)($beginOff), (int)($pageLength));
		//设置string query
		$res = $cl->Query($queriesStr,"teacher");
		//get matches
		$data = array();
		$statField = array('course_count','course_complete_count','course_remain_count','course_on_count','course_off_count',
					'student_count','avg_score','score_user_count','totaltime','comment');
		if(isset($res['matches']) && count($res['matches']) > 0){
		    $orgTeachers = $tmpIdArr = array();
            if(in_array('org_teacher', $field)){
                foreach($res['matches'] as $id=>$val){
                	$tmpIdArr[$val['attrs']['pk_user']] = 0;
                }
                $IdStr = implode(',', array_keys($tmpIdArr));

				$dbStat = new stat_db;
				$teacherStatList = $dbStat->getTeacherStatOrgByTids( $IdStr );
            	if(isset($teacherStatList->items) && count($teacherStatList->items)>0){
                	foreach($teacherStatList->items as $teacherStat){
						$statKey = $teacherStat['fk_user'].$teacherStat['fk_user_owner'];
                    	$teacherStatData[$statKey] = $teacherStat;
                	}
            	}else{
                	$teacherStatData = array();
            	}

                $dbUser = new user_db;
                $teacherOrgList = $dbUser->listOrgTeachersByUserIds( $IdStr );
            	if(isset($teacherOrgList->items) && count($teacherOrgList->items)>0){
                	foreach($teacherOrgList->items as $teacherOrg){
						$tempKey = $teacherOrg['fk_user'].$teacherOrg['fk_user_owner'];
						if(!empty($teacherStatData[$tempKey])){
							foreach($statField as $fname){
								if($fname == 'course_count'){
									$teacherOrg[$fname] = $teacherStatData[$tempKey]['course_on_count']+$teacherStatData[$tempKey]['course_off_count'];
								}else{
									$teacherOrg[$fname] = $teacherStatData[$tempKey][$fname];
								}
							}	
						}else{
							foreach($statField as $fname){
								$teacherOrg[$fname] = 0;
							}
						}
                    	$teacherOrgData[$teacherOrg['fk_user']][] = $teacherOrg;
                	}
            	}else{
                	$teacherOrgData = array();
            	}
            }
			$grades = $subjects = array();
			$groupconf =  SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$tmpIdArr = array();
			if(in_array('grade', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['grade_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $gradeId){
							$tmpIdArr[$gradeId] = 0;
						}
					}
				}
				$gradeIdStr = implode(',', array_keys($tmpIdArr));
				$dbTag = new tag_db;
				$tagList = $dbTag->getUserTagListByTids($gradeIdStr,$groupconf->grade);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpGrade['grade_id'] = $val['pk_tag'];
						$tmpGrade['grade_name'] = $val['name'];
						$grades[$val['fk_user']][] = $tmpGrade;
					}
				}
			}

			$tmpIdArr = array();
			if(in_array('subject', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['subject_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $subjectId){
							$tmpIdArr[$subjectId] = 0;
						}
					}
				}
				$subjectIdStr = implode(',', array_keys($tmpIdArr));
				$dbTag = new tag_db;
				$tagList = $dbTag->getUserTagListByTids($subjectIdStr,$groupconf->subject);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpSubject['subject_id'] = $val['pk_tag'];
						$tmpSubject['subject_name'] = $val['name'];
						$subjects[$val['fk_user']][] = $tmpSubject;
					}
				}
			}
			
			$i = 0;
			foreach($res['matches'] as $id=>$val){
				foreach($field as $k){
					if(isset($timesArr[$k])){
						$data[$i][$k] = date('Y-m-d H:i:s', $val['attrs'][$tmpAttrs[$k]]);	
					}elseif($k === 'org_teacher'){
							if(isset($teacherOrgData[$val['attrs']['pk_user']]))
								$data[$i][$k] = $teacherOrgData[$val['attrs']['pk_user']];
							else
								$data[$i][$k] = array();
					}elseif($k==='grade'){
						if(isset($grades[$val['attrs']['pk_user']]))
							$data[$i][$k] = $grades[$val['attrs']['pk_user']];
						else
							$data[$i][$k] = array();
					}elseif($k==='subject'){
						if(isset($subjects[$val['attrs']['pk_user']]))
							$data[$i][$k] = $subjects[$val['attrs']['pk_user']];
						else
							$data[$i][$k] = array();
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
		<sphinx:field name="real_name"/>
		<sphinx:field name="title"/>
		<sphinx:field name="org_name"/>
		<sphinx:field name="org_subname"/>
		<sphinx:field name="mobile"/>
		<sphinx:field name="province"/>
		<sphinx:field name="city"/>
		<sphinx:field name="search_field"/>
		<sphinx:attr name="name" type="string"/>
		<sphinx:attr name="real_name" type="string"/>
		<sphinx:attr name="title" type="string"/>
		<sphinx:attr name="org_name" type="string"/>
		<sphinx:attr name="org_subname" type="string"/>
		<sphinx:attr name="thumb_big" type="string"/>
		<sphinx:attr name="thumb_med" type="string"/>
		<sphinx:attr name="thumb_small" type="string"/>
		<sphinx:attr name="register_ip" type="string"/>
		<sphinx:attr name="mobile" type="string"/>
		<sphinx:attr name="province" type="string"/>
		<sphinx:attr name="city" type="string"/>
		<sphinx:attr name="search_field" type="string"/>
		<sphinx:attr name="years" type="string"/>
		<sphinx:attr name="college" type="string"/>
		<sphinx:attr name="diploma" type="string"/>
		<sphinx:attr name="descript" type="string"/>
		<sphinx:attr name="brief_desc" type="string"/>
		<sphinx:attr name="subject_name" type="string"/>
		<sphinx:attr name="grade_name" type="string"/>
		<sphinx:attr name="subject_id" type="multi"/>
		<sphinx:attr name="grade_id" type="multi"/>

		<sphinx:attr name="pk_user" type="int"/>
		<sphinx:attr name="org_id" type="multi"/>
		<sphinx:attr name="role" type="multi"/>
		<sphinx:attr name="gender" type="int"/>
		<sphinx:attr name="visiable" type="int"/>
		<sphinx:attr name="user_status" type="bigint"/>
		<sphinx:attr name="verify_status" type="bigint"/>
		<sphinx:attr name="teacher_status" type="bigint"/>
		<sphinx:attr name="major" type="int"/>
		<sphinx:attr name="birthday" type="bigint"/>
		<sphinx:attr name="create_time" type="bigint"/>
		<sphinx:attr name="last_login" type="bigint"/>

		<sphinx:attr name="course_count" type="int"/>
		<sphinx:attr name="course_complete_count" type="int"/>
		<sphinx:attr name="course_remain_count" type="int"/>
		<sphinx:attr name="course_on_count" type="int"/>
		<sphinx:attr name="course_off_count" type="int"/>
		<sphinx:attr name="student_count" type="int"/>
		<sphinx:attr name="avg_score" type="float"/>
		<sphinx:attr name="score_user_count" type="int"/>
		<sphinx:attr name="desc_score" type="int"/>
		<sphinx:attr name="student_score" type="int"/>
		<sphinx:attr name="explain_score" type="int"/>
		<sphinx:attr name="totaltime" type="int"/>
		<sphinx:attr name="comment" type="int"/>
		<sphinx:attr name="weight" type="int"/>

		</sphinx:schema>';
		
		$dbTag  = new tag_db;
		$dbStat = new stat_db;
		$dbMessage = new message_db;
		$dbUser = new user_db;
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_teacher_length;
		$teacherList = $dbUser->listTeachers( 1, $pageLength );
		$totalPage = $teacherList->totalPage;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$teacherList = $dbUser->listTeachersNew($page,$pageLength);
			$userIdArr = $orgIdArr = array();
			foreach($teacherList->items as $k=>$v){
				$userIdArr[$v['pk_user']] = 0;
			}
			$userIdStr = implode(',', array_keys($userIdArr));
			$teacherOrgList =  $dbUser->listOrgTeachersByUserIds( $userIdStr );
			if(isset($teacherOrgList->items) && count($teacherOrgList->items)>0){
				foreach($teacherOrgList->items as $teacherOrg){
					$teacherOrgData[$teacherOrg['fk_user']][] = $teacherOrg;
				}
			}else{
				$teacherOrgData = array();
			}
			$teacherMobileList =  $dbUser->listMobilesByUserIds( $userIdStr );
			if(isset($teacherMobileList->items) && count($teacherMobileList->items)>0){
				foreach($teacherMobileList->items as $teacherMobile){
					$teacherMobileData[$teacherMobile['fk_user']][] = $teacherMobile;
				}
			}else{
				$teacherMobileData = array();
			}

			$teacherStatList = $dbStat->getTeacherStatByTids( $userIdStr );
			if(isset($teacherStatList->items) && count($teacherStatList->items)>0){
				foreach($teacherStatList->items as $teacherStat){
					$teacherStatData[$teacherStat['fk_user']][] = $teacherStat;
				}
			}else{
				$teacherStatData = array();
			}

			$teacherScoreList = $dbMessage->getTeacherScoreByTidArr( $userIdStr );
			$teacherAvgScoreList = array();
			if(isset($teacherScoreList->items) && count($teacherScoreList->items)>0){
				foreach($teacherScoreList->items as $teacherScore){
					$teacherScoreData[$teacherScore['teacher_id']][] = $teacherScore;
				}
				if(empty($teacherAvgScoreList[$teacherScore['teacher_id']])){
						$teacherAvgScoreList[$teacherScore['teacher_id']] =array(
							'total_user' =>0,
							'avg_score' =>0,
						);
				}
				$teacherAvgScoreList[$teacherScore['teacher_id']]['total_user'] += $teacherScore['total_user'];
				$teacherAvgScoreList[$teacherScore['teacher_id']]['avg_score']  += $teacherScore['avg_score'];
			}else{
				$teacherScoreData = array();
			}

			$groupInfo = $dbTag->getGroupInfo('科目');
			$teacherSubjectList = $dbTag->getTagUserInUids($userIdStr,$groupInfo['pk_group']);
			if(isset($teacherSubjectList->items) && count($teacherSubjectList->items)>0){
				$tid_arr = array();
				foreach($teacherSubjectList->items as $teacherSubject){
					$tid_arr[$teacherSubject['fk_tag']] = $teacherSubject['fk_tag'];
				}
				$taginfo = $dbTag::getTagByTidArr($tid_arr);
				$tagData = array();
				if(!empty($taginfo->items)){
					foreach( $taginfo->items as $to){
						$tagData[$to['pk_tag']] = $to['name'];
					}			
				}
				foreach($teacherSubjectList->items as $teacherSubject){
					$teacherSubjectData[$teacherSubject['fk_user']]['subject'][] = $teacherSubject['fk_tag'];
					if(!empty($tagData[$teacherSubject['fk_tag']])){
						$teacherSubjectData[$teacherSubject['fk_user']]['name'][] = $tagData[$teacherSubject['fk_tag']];
					}else{
						$teacherSubjectData[$teacherSubject['fk_user']]['name'][] = '';
					}
				}
			}else{
				$teacherSubjectData = array();
			}
				

			$groupInfo = $dbTag->getGroupInfo('年级');
			$teacherScopeList = $dbTag->getTagUserInUids($userIdStr,$groupInfo['pk_group']);
			if(isset($teacherScopeList->items) && count($teacherScopeList->items)>0){
				$tid_arr = array();
				foreach($teacherScopeList->items as $teacherScope){
					$tid_arr[$teacherScope['fk_tag']] = $teacherScope['fk_tag'];
				}
				$taginfo = $dbTag::getTagByTidArr($tid_arr);
				$tagData = array();
				if(!empty($taginfo->items)){
					foreach( $taginfo->items as $to){
						$tagData[$to['pk_tag']] = $to['name'];
					}			
				}
				foreach($teacherScopeList->items as $teacherScope){
					$teacherScopeData[$teacherScope['fk_user']]['scope'][] = $teacherScope['fk_tag'];
					if(!empty($tagData[$teacherScope['fk_tag']])){
						$teacherScopeData[$teacherScope['fk_user']]['name'][] = $tagData[$teacherScope['fk_tag']];
					}else{
						$teacherScopeData[$teacherScope['fk_user']]['name'][] = '';
					}
				}
			}else{
				$teacherScopeData = array();
			}
			if(isset($teacherList->items) && count($teacherList->items)>0){
				foreach($teacherList->items as $teacher ){
					echo '<sphinx:document id="'.$teacher['pk_user'].'">'."\n";
					if(isset($teacherOrgData[$teacher['pk_user']]) && count($teacherOrgData[$teacher['pk_user']])>0){
						$teacher['org_name'] = '';
						$teacher['org_subname'] = '';
						$teacher['org_id'] = array();
						$teacher['role'] = array();
						$teacher['visiable']  = 0;
						$teacher['teacher_status'] = -1;
						foreach($teacherOrgData[$teacher['pk_user']] as $teacherOrg){
							if(isset($teacherOrg['name']))
								$teacher['org_name'] .= $teacherOrg['name'];
							if(isset($teacherOrg['subname']))
								$teacher['org_subname'] .= $teacherOrg['subname'];
							if(isset($teacherOrg['pk_org']))
								$teacher['org_id'][] = $teacherOrg['pk_org'];
							if($teacherOrg['visiable'] == 1 && $teacherOrg['teacher_status'] == 1){
								$teacher['visiable'] = 1;
							} 
							if($teacherOrg['teacher_status'] == 1){
								$teacher['teacher_status'] = 1;
							}
							if($teacherOrg['user_role']&0x01){
								$tmpRole = $teacherOrg['user_role']&0x01;
								if(!in_array($tmpRole,$teacher['role'])){
									$teacher['role'][] = $tmpRole;
								}
							} 
							if($teacherOrg['user_role'] &0x02){
								$tmpRole = $teacherOrg['user_role']&0x02;
								if(!in_array($tmpRole,$teacher['role'])){
									$teacher['role'][] = $tmpRole;
								}
							} 
							if($teacherOrg['user_role']&0x04){
								$tmpRole = $teacherOrg['user_role']&0x04;
								if(!in_array($tmpRole,$teacher['role'])){
									$teacher['role'][] = $tmpRole;
								}
							} 
						}
					}else{
						$teacher['org_name'] = '';
						$teacher['org_subname'] = '';
						$teacher['org_id'] = array();
						$teacher['role'] = array();
						$teacher['teacher_status'] = -1;
						$teacher['visiable'] = 0;
					}
					if(isset($teacherMobileData[$teacher['pk_user']]) && count($teacherMobileData[$teacher['pk_user']])>0){
						$teacher['mobile'] = '';
						$teacher['province'] = '';
						$teacher['city'] = '';
						foreach($teacherMobileData[$teacher['pk_user']] as $teacherMobile){
							if(isset($teacherMobile['mobile']))
								$teacher['mobile'] .= $teacherMobile['mobile'];
							if(isset($teacherMobile['province']))
								$teacher['province'] .= $teacherMobile['province'];
							if(isset($teacherMobile['city']))
								$teacher['city'] .= $teacherMobile['city'];
						}
					}else{
						$teacher['mobile'] = '';
						$teacher['province'] = '';
						$teacher['city'] = '';
					}

					if(isset($teacherStatData[$teacher['pk_user']]) && count($teacherStatData[$teacher['pk_user']])>0 ){
						foreach($teacherStatData[$teacher['pk_user']] as $teacherStat){
							$teacher['course_count'] = $teacherStat['course_on_count']+$teacherStat['course_off_count'];
							$teacher['course_complete_count'] = $teacherStat['course_complete_count'];
							$teacher['course_remain_count'] = $teacherStat['course_remain_count'];
							$teacher['course_on_count'] = $teacherStat['course_on_count'];
							$teacher['course_off_count'] = $teacherStat['course_off_count'];
							$teacher['student_count'] = $teacherStat['student_count'];
							$teacher['totaltime'] = $teacherStat['totaltime'];
							if(empty($teacherAvgScoreList[$teacher['pk_user']])){
								$teacher['avg_score'] = 0;
							}else{
								$teacher['avg_score'] = empty($teacherAvgScoreList[$teacher['pk_user']]['total_user'])?0:sprintf(',',$teacherAvgScoreList[$teacher['pk_user']]['avg_score']/$teacherAvgScoreList[$teacher['pk_user']]['total_user']);
							}
							$teacher_data = $dbMessage ->getTeacherScore($teacher['pk_user']);
							$teacher['score_user_count'] = 0;
							$total_score = 0;
							$teacher['avg_score'] = 0;
							if(!empty($teacher_data)){
								foreach($teacher_data->items as $v){
									$teacher['score_user_count'] += $v['total_user'];
									$total_score  += $v['score'];
								}
								if($teacher['score_user_count'] !=0 ){
									$teacher['avg_score'] = round($total_score/$teacher['score_user_count'],1);
								}

							}
							$teacher['comment'] = $teacherStat['comment'];
							$teacher['weight'] = ($teacher['avg_score']*0.5+$teacher['score_user_count']*1+
												$teacher['course_count']*0.3+$teacher['student_count']*0.003)*1000;
						}
					}else{
						$teacher['course_count'] = 0;
						$teacher['course_complete_count'] = 0;
						$teacher['course_remain_count'] = 0;
						$teacher['course_on_count'] = 0;
						$teacher['course_off_count'] = 0;
						$teacher['student_count'] = 0;
						$teacher['totaltime'] = 0;
						$teacher['avg_score'] = 0;
						$teacher['score_user_count'] = 0;	
						$teacher['comment'] = 0;	
						$teacher['weight'] = 0;
					}
					if(isset($teacherSubjectData[$teacher['pk_user']]) && count($teacherSubjectData[$teacher['pk_user']]) >0 ){
						if(!empty($teacherSubjectData[$teacher['pk_user']]['subject'])){
							$teacher['subject_id'] = implode(',',$teacherSubjectData[$teacher['pk_user']]['subject']);
						}else{
							$teacher['subject_id'] = '';
						}
						if(!empty($teacherSubjectData[$teacher['pk_user']]['name'])){
							$teacher['subject_name'] = implode(' ',$teacherSubjectData[$teacher['pk_user']]['name']);
						}else{
							$teacher['subject_name'] = '';
						}
					}else{
						$teacher['subject_id'] = '';
						$teacher['subject_name'] = '';
					}

					if(isset($teacherScopeData[$teacher['pk_user']]) && count($teacherScopeData[$teacher['pk_user']]) >0 ){
						if(!empty($teacherScopeData[$teacher['pk_user']]['scope'])){
							$teacher['grade_id'] = implode(',',$teacherScopeData[$teacher['pk_user']]['scope']);
						}else{
							$teacher['grade_id'] = '';
						}
						if(!empty($teacherScopeData[$teacher['pk_user']]['name'])){
							$teacher['grade_name'] = implode(' ',$teacherScopeData[$teacher['pk_user']]['name']);
						}else{
							$teacher['grade_name'] = '';
						}
					}else{
						$teacher['grade_id'] = '';
						$teacher['grade_name'] = '';
					}

					$teacher['student_score'] = 0;
					$teacher['desc_score'] = 0;
					$teacher['explain_score'] = 0;
					$total_user = 0;
					if(isset($teacherScoreData[$teacher['pk_user']]) && count($teacherScoreData[$teacher['pk_user']])>0 ){
						foreach($teacherScoreData[$teacher['pk_user']] as $teacherScore){
							$teacher['student_score'] += $teacherScore['student_score'];
							$teacher['desc_score'] += $teacherScore['desc_score'];
							$teacher['explain_score'] += $teacherScore['explain_score'];
							$total_user += $teacherScore['total_user'];
						}
						if( !empty($total_user) ){
							$teacher['student_score'] = floor(($teacher['student_score']/$total_user)*10);
							$teacher['desc_score'] = floor(($teacher['desc_score']/$total_user) * 10);
							$teacher['explain_score'] = floor(($teacher['explain_score']/$total_user)*10);
						}else{
							$teacher['student_score'] = 0;
							$teacher['desc_score'] = 0;
							$teacher['explain_score'] = 0;
						}
					}

					$teacher['search_field'] = $teacher['grade_name'].' '.$teacher['subject_name'].' '.$teacher['name'].' '.$teacher['real_name'].' '.$teacher['org_name'].' '.$teacher['org_subname'].' '.$teacher['province'].' '.$teacher['city'].' '.$teacher['mobile'];
					foreach($teacher as $k=>$v){
						if($k == 'desc')
							$k='descript';
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
	public function pageGendelta($inPath){
    	echo '<?xml version="1.0" encoding="utf-8"?>
	    <sphinx:docset>
		<sphinx:schema>
		<sphinx:field name="name"/>
		<sphinx:field name="real_name"/>
		<sphinx:field name="title"/>
		<sphinx:field name="org_name"/>
		<sphinx:field name="org_subname"/>
		<sphinx:field name="mobile"/>
		<sphinx:field name="province"/>
		<sphinx:field name="city"/>
		<sphinx:field name="search_field"/>
		<sphinx:attr name="name" type="string"/>
		<sphinx:attr name="real_name" type="string"/>
		<sphinx:attr name="title" type="string"/>
		<sphinx:attr name="org_name" type="string"/>
		<sphinx:attr name="org_subname" type="string"/>
		<sphinx:attr name="thumb_big" type="string"/>
		<sphinx:attr name="thumb_med" type="string"/>
		<sphinx:attr name="thumb_small" type="string"/>
		<sphinx:attr name="register_ip" type="string"/>
		<sphinx:attr name="mobile" type="string"/>
		<sphinx:attr name="province" type="string"/>
		<sphinx:attr name="city" type="string"/>
		<sphinx:attr name="search_field" type="string"/>
		<sphinx:attr name="years" type="string"/>
		<sphinx:attr name="college" type="string"/>
		<sphinx:attr name="diploma" type="string"/>
		<sphinx:attr name="descript" type="string"/>
		<sphinx:attr name="brief_desc" type="string"/>
		<sphinx:attr name="subject_name" type="string"/>
		<sphinx:attr name="grade_name" type="string"/>
		<sphinx:attr name="subject_id" type="multi"/>
		<sphinx:attr name="grade_id" type="multi"/>

		<sphinx:attr name="pk_user" type="int"/>
		<sphinx:attr name="org_id" type="multi"/>
		<sphinx:attr name="role" type="multi"/>
		<sphinx:attr name="gender" type="int"/>
		<sphinx:attr name="visiable" type="int"/>
		<sphinx:attr name="user_status" type="bigint"/>
		<sphinx:attr name="verify_status" type="bigint"/>
		<sphinx:attr name="teacher_status" type="bigint"/>
		<sphinx:attr name="major" type="int"/>
		<sphinx:attr name="birthday" type="bigint"/>
		<sphinx:attr name="create_time" type="bigint"/>
		<sphinx:attr name="last_login" type="bigint"/>

		<sphinx:attr name="course_count" type="int"/>
		<sphinx:attr name="course_complete_count" type="int"/>
		<sphinx:attr name="course_remain_count" type="int"/>
		<sphinx:attr name="course_on_count" type="int"/>
		<sphinx:attr name="course_off_count" type="int"/>
		<sphinx:attr name="student_count" type="int"/>
		<sphinx:attr name="avg_score" type="float"/>
		<sphinx:attr name="score_user_count" type="int"/>
		<sphinx:attr name="desc_score" type="int"/>
		<sphinx:attr name="student_score" type="int"/>
		<sphinx:attr name="explain_score" type="int"/>
		<sphinx:attr name="totaltime" type="int"/>
		<sphinx:attr name="comment" type="int"/>
		<sphinx:attr name="weight" type="int"/>

		</sphinx:schema>';
		
		$dbTag  = new tag_db;
		$dbStat = new stat_db;
		$dbMessage = new message_db;
		$dbUser = new user_db;
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_teacher_length;
		$teacherList = $dbUser->listTeachers( 1, $pageLength, $conf->seek_teacher_delta );
		$totalPage = $teacherList->totalPage;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$teacherList = $dbUser->listTeachersNew($page,$pageLength, $conf->seek_teacher_delta);
			$userIdArr = $orgIdArr = array();
			foreach($teacherList->items as $k=>$v){
				$userIdArr[$v['pk_user']] = 0;
			}
			$userIdStr = implode(',', array_keys($userIdArr));
			$teacherOrgList =  $dbUser->listOrgTeachersByUserIds( $userIdStr );
			if(isset($teacherOrgList->items) && count($teacherOrgList->items)>0){
				foreach($teacherOrgList->items as $teacherOrg){
					$teacherOrgData[$teacherOrg['fk_user']][] = $teacherOrg;
				}
			}else{
				$teacherOrgData = array();
			}
			$teacherMobileList =  $dbUser->listMobilesByUserIds( $userIdStr );
			if(isset($teacherMobileList->items) && count($teacherMobileList->items)>0){
				foreach($teacherMobileList->items as $teacherMobile){
					$teacherMobileData[$teacherMobile['fk_user']][] = $teacherMobile;
				}
			}else{
				$teacherMobileData = array();
			}

			$teacherStatList = $dbStat->getTeacherStatByTids( $userIdStr );
			if(isset($teacherStatList->items) && count($teacherStatList->items)>0){
				foreach($teacherStatList->items as $teacherStat){
					$teacherStatData[$teacherStat['fk_user']][] = $teacherStat;
				}
			}else{
				$teacherStatData = array();
			}

			$teacherScoreList = $dbMessage->getTeacherScoreByTidArr( $userIdStr );
			$teacherAvgScoreList = array();
			if(isset($teacherScoreList->items) && count($teacherScoreList->items)>0){
				foreach($teacherScoreList->items as $teacherScore){
					$teacherScoreData[$teacherScore['teacher_id']][] = $teacherScore;
					if(empty($teacherAvgScoreList[$teacherScore['teacher_id']])){
						$teacherAvgScoreList[$teacherScore['teacher_id']] =array(
							'total_user' =>0,
							'avg_score' =>0,
						);
					}
					$teacherAvgScoreList[$teacherScore['teacher_id']]['total_user'] += $teacherScore['total_user'];
					$teacherAvgScoreList[$teacherScore['teacher_id']]['avg_score']  += $teacherScore['avg_score'];
				}
			}else{
				$teacherScoreData = array();
			}

			$groupconf =  SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$tagTeacherList = $dbTag->getTagUserByUids($userIdStr);
			$gradeData = array();
			$subjectData = array();
			if(isset($tagTeacherList->items) && count($tagTeacherList->items)>0){	
				foreach($tagTeacherList->items as $tag){
					if($tag['fk_group'] == $groupconf->grade){
						$gradeData[$tag['fk_user']]['grade_id'][] = $tag['fk_tag'];
						$gradeData[$tag['fk_user']]['grade_name'][] = $tag['name'];	
					}
					if($tag['fk_group'] == $groupconf->subject){
						$subjectData[$tag['fk_user']]['subject_id'][] = $tag['fk_tag'];
						$subjectData[$tag['fk_user']]['subject_name'][] = $tag['name'];	
					}
				}
			}
			if(isset($teacherList->items) && count($teacherList->items)>0){	
				foreach($teacherList->items as $teacher ){
					echo '<sphinx:document id="'.$teacher['pk_user'].'">'."\n";
					if(isset($teacherOrgData[$teacher['pk_user']]) && count($teacherOrgData[$teacher['pk_user']])>0){
						$teacher['org_name'] = '';
						$teacher['org_subname'] = '';
						$teacher['org_id'] = array();
						$teacher['role'] = array();
						$teacher['visiable']  = 0;
						$teacher['teacher_status'] = -1;
						foreach($teacherOrgData[$teacher['pk_user']] as $teacherOrg){
							if(isset($teacherOrg['name']))
								$teacher['org_name'] .= $teacherOrg['name'];
							if(isset($teacherOrg['subname']))
								$teacher['org_subname'] .= $teacherOrg['subname'];
							if(isset($teacherOrg['pk_org']))
								$teacher['org_id'][] = $teacherOrg['pk_org'];
							if($teacherOrg['visiable'] == 1 && $teacherOrg['teacher_status'] == 1){
								$teacher['visiable'] = 1;
							} 
							if($teacherOrg['teacher_status'] == 1){
								$teacher['teacher_status'] = 1;
							}
							if($teacherOrg['user_role']&0x01){
								$tmpRole = $teacherOrg['user_role']&0x01;
								if(!in_array($tmpRole,$teacher['role'])){
									$teacher['role'][] = $tmpRole;
								}
							} 
							if($teacherOrg['user_role'] &0x02){
								$tmpRole = $teacherOrg['user_role']&0x02;
								if(!in_array($tmpRole,$teacher['role'])){
									$teacher['role'][] = $tmpRole;
								}
							} 
							if($teacherOrg['user_role']&0x04){
								$tmpRole = $teacherOrg['user_role']&0x04;
								if(!in_array($tmpRole,$teacher['role'])){
									$teacher['role'][] = $tmpRole;
								}
							} 
						}
					}else{
						$teacher['org_name'] = '';
						$teacher['org_subname'] = '';
						$teacher['org_id'] = array();
						$teacher['role'] = array();
						$teacher['teacher_status'] = -1;
						$teacher['visiable'] = 0;
					}
					if(isset($teacherMobileData[$teacher['pk_user']]) && count($teacherMobileData[$teacher['pk_user']])>0){
						$teacher['mobile'] = '';
						$teacher['province'] = '';
						$teacher['city'] = '';
						foreach($teacherMobileData[$teacher['pk_user']] as $teacherMobile){
							if(isset($teacherMobile['mobile']))
								$teacher['mobile'] .= $teacherMobile['mobile'];
							if(isset($teacherMobile['province']))
								$teacher['province'] .= $teacherMobile['province'];
							if(isset($teacherMobile['city']))
								$teacher['city'] .= $teacherMobile['city'];
						}
					}else{
						$teacher['mobile'] = '';
						$teacher['province'] = '';
						$teacher['city'] = '';
					}

					if(isset($teacherStatData[$teacher['pk_user']]) && count($teacherStatData[$teacher['pk_user']])>0 ){
						foreach($teacherStatData[$teacher['pk_user']] as $teacherStat){
							$teacher['course_count'] = $teacherStat['course_on_count']+$teacherStat['course_off_count'];
							$teacher['course_complete_count'] = $teacherStat['course_complete_count'];
							$teacher['course_remain_count'] = $teacherStat['course_remain_count'];
							$teacher['course_on_count'] = $teacherStat['course_on_count'];
							$teacher['course_off_count'] = $teacherStat['course_off_count'];
							$teacher['student_count'] = $teacherStat['student_count'];
							$teacher['totaltime'] = $teacherStat['totaltime'];
							if(empty($teacherAvgScoreList[$teacher['pk_user']])){
								$teacher['avg_score'] = 0;
							}else{
								$teacher['avg_score'] = empty($teacherAvgScoreList[$teacher['pk_user']]['total_user'])?0:sprintf('%.1f',$teacherAvgScoreList[$teacher['pk_user']]['avg_score']/$teacherAvgScoreList[$teacher['pk_user']]['total_user']);
							}
							$teacher_data = $dbMessage ->getTeacherScore($teacher['pk_user']);
							$teacher['score_user_count'] = 0;
							$total_score = 0;
							$teacher['avg_score'] = 0;
							if(!empty($teacher_data)){
								foreach($teacher_data->items as $v){
									$teacher['score_user_count'] += $v['total_user'];
									$total_score  += $v['score'];
								}
								if($teacher['score_user_count'] !=0 ){
									$teacher['avg_score'] = round($total_score/$teacher['score_user_count'],1);
								}

							}
							$teacher['comment'] = $teacherStat['comment'];
							$teacher['weight'] = ($teacher['avg_score']*0.5+$teacher['score_user_count']*1+
												$teacher['course_count']*0.3+$teacher['student_count']*0.003)*1000;
						}
					}else{
						$teacher['course_count'] = 0;
						$teacher['course_complete_count'] = 0;
						$teacher['course_remain_count'] = 0;
						$teacher['course_on_count'] = 0;
						$teacher['course_off_count'] = 0;
						$teacher['student_count'] = 0;
						$teacher['totaltime'] = 0;
						$teacher['avg_score'] = 0;
						$teacher['score_user_count'] = 0;	
						$teacher['comment'] = 0;	
						$teacher['weight'] = 0;
					}
					if(isset($subjectData[$teacher['pk_user']]) && count($subjectData[$teacher['pk_user']]) >0 ){
						if(!empty($subjectData[$teacher['pk_user']]['subject_id'])){
							$teacher['subject_id'] = implode(',',$subjectData[$teacher['pk_user']]['subject_id']);
						}else{
							$teacher['subject_id'] = '';
						}
						if(!empty($subjectData[$teacher['pk_user']]['subject_name'])){
							$teacher['subject_name'] = implode(' ',$subjectData[$teacher['pk_user']]['subject_name']);
						}else{
							$teacher['subject_name'] = '';
						}
					}else{
						$teacher['subject_id'] = '';
						$teacher['subject_name'] = '';
					}

					if(isset($gradeData[$teacher['pk_user']]) && count($gradeData[$teacher['pk_user']]) >0 ){
						if(!empty($gradeData[$teacher['pk_user']]['grade_id'])){
							$teacher['grade_id'] = implode(',',$gradeData[$teacher['pk_user']]['grade_id']);
						}else{
							$teacher['grade_id'] = '';
						}
						if(!empty($gradeData[$teacher['pk_user']]['grade_name'])){
							$teacher['grade_name'] = implode(' ',$gradeData[$teacher['pk_user']]['grade_name']);
						}else{
							$teacher['grade_name'] = '';
						}
					}else{
						$teacher['grade_id'] = '';
						$teacher['grade_name'] = '';
					}

					$teacher['student_score'] = 0;
					$teacher['desc_score'] = 0;
					$teacher['explain_score'] = 0;
					$total_user = 0;
					if(isset($teacherScoreData[$teacher['pk_user']]) && count($teacherScoreData[$teacher['pk_user']])>0 ){
						foreach($teacherScoreData[$teacher['pk_user']] as $teacherScore){
							$teacher['student_score'] += $teacherScore['student_score'];
							$teacher['desc_score'] += $teacherScore['desc_score'];
							$teacher['explain_score'] += $teacherScore['explain_score'];
							$total_user += $teacherScore['total_user'];
						}
						if(!empty($total_user)){
							$teacher['student_score'] = floor(($teacher['student_score']/$total_user)*10);
							$teacher['desc_score'] = floor(($teacher['desc_score']/$total_user)*10);
							$teacher['explain_score'] = floor(($teacher['explain_score']/$total_user)*10);
						}else{
							$teacher['student_score'] = 0;
							$teacher['desc_score'] = 0;
							$teacher['explain_score'] = 0;
						}
					}

					$teacher['search_field'] = $teacher['grade_name'].' '.$teacher['subject_name'].' '.$teacher['name'].' '.$teacher['real_name'].' '.$teacher['org_name'].' '.$teacher['org_subname'].' '.$teacher['province'].' '.$teacher['city'];
					foreach($teacher as $k=>$v){
						if($k == 'desc')
							$k='descript';
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
