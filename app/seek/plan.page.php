<?php
class seek_plan{
	var $attrs = array(
		'plan_id'=>'pk_plan',
		'video_id'=>'video_id',
		'course_id'=>'fk_course',
		'section_id'=>'fk_section',
		'class_id'=>'fk_class',
		'teacher_id'=>'fk_user_plan',
		'admin_id'=>'fk_user_class',
		'owner_id'=>'fk_user_owner',
		'first_cate'=>'first_cate',
		'second_cate'=>'second_cate',
		'third_cate'=>'third_cate',
		'first_cate_name'=>'first_cate_name',
		'second_cate_name'=>'second_cate_name',
		'third_cate_name'=>'third_cate_name',
		'first_cate_name_display'=>'first_cate_name_display',
		'second_cate_name_display'=>'second_cate_name_display',
		'third_cate_name_display'=>'third_cate_name_display',
		'subject_id'=>'subject_id',
		'grade_id'=>'grade_id',
		'live_public_type'=>'live_public_type',
		'video_public_type'=>'video_public_type',
		'video_trial_time'=>'video_trial_time',
		'admin_name'=>'admin_name',
		'admin_real_name'=>'admin_real_name',
		'admin_thumb_big'=>'admin_thumb_big',
		'admin_thumb_med'=>'admin_thumb_med',
		'admin_thumb_sma'=>'admin_thumb_small',
		'teacher_name'=>'teacher_name',
		'teacher_real_name'=>'teacher_real_name',
		'teacher_thumb_big'=>'teacher_thumb_big',
		'teacher_thumb_med'=>'teacher_thumb_med',
		'teacher_thumb_sma'=>'teacher_thumb_small',
		'course_name'=>'course_name',
		'section_name'=>'section_name',
		'section_desc'=>'section_desc',
		'class_name'=>'class_name',
		'region_level0'=>'region_level0',
		'region_level1'=>'region_level1',
		'region_level2'=>'region_level2',
		'address'=>'address',
		'max_user'=>'max_user',
		'user_total'=>'user_total',
		'status'=>'status',
		'course_status'=>'course_status',
		'admin_status'=>'admin_status',
		'course_type'=>'course_type',
		'fee_type'=>'fee_type',
		'try' => 'try',
		'totaltime' => 'totaltime',
		'vv'=>'vv',
		'vv_live'=>'vv_live',
		'vv_record'=>'vv_record',
		'vt'=>'vt',
		'vt_live'=>'vt_live',
		'vt_record'=>'vt_record',
		'comment'=>'comment',
		'discuss'=>'discuss',
		'start_time'=>'start_time',
		'end_time'=>'end_time',
		'create_time'=>'create_time',
		'last_updated'=>'last_updated',
		'subdomain'=>'subdomain',
		'org_subname'=>'org_subname',
		'org_id'=>'org_id',
		'org_status'=>'org_status',
		'course_thumb_big'=>'course_thumb_big',
		'course_thumb_med'=>'course_thumb_med',
		'course_thumb_small'=>'course_thumb_small',
		'section_order_no'=>'section_order_no',
	);
	var $timesArr = array('start_time'=>0,
						  'end_time'=>0,
						  'create_time'=>0,
						  'last_updated'=>0
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
				'plan_id',
				'teacher_name',
				'teacher_real_name',
				'start_time',
				'create_time',
			);
		}else{
			foreach($params->f as $k=>$v){
				$field[] = $v;
			}
		}
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$address = $conf->seek_plan_ip;
		$port = (int)($conf->seek_plan_port);
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
			if(isset($queryArr['plan_id'])){
				$qArr = explode(',', $queryArr['plan_id']);
				$cl->setFilter($tmpAttrs['plan_id'], $qArr);	
			}
			if(isset($queryArr['org_id'])){
				$qArr = explode(',', $queryArr['org_id']);
				$cl->setFilter($tmpAttrs['org_id'], $qArr);	
			}
			if(isset($queryArr['video_id'])){
				$qArr = explode(',', $queryArr['video_id']);
				$cl->setFilter($tmpAttrs['video_id'], $qArr);	
			}
			if(isset($queryArr['course_id'])){
				$qArr = explode(',', $queryArr['course_id']);
				$cl->setFilter($tmpAttrs['course_id'], $qArr);	
			}
			if(isset($queryArr['section_id'])){
				$qArr = explode(',', $queryArr['section_id']);
				$cl->setFilter($tmpAttrs['section_id'], $qArr);
			}
			if(isset($queryArr['class_id'])){
				$qArr = explode(',', $queryArr['class_id']);
				$cl->setFilter($tmpAttrs['class_id'], $qArr);	
			}
			if(isset($queryArr['teacher_id'])){
				$qArr = explode(',', $queryArr['teacher_id']);
				$cl->setFilter($tmpAttrs['teacher_id'], $qArr);	
			}
			if(isset($queryArr['admin_id'])){
				$qArr = explode(',', $queryArr['admin_id']);
				$cl->setFilter($tmpAttrs['admin_id'], $qArr);	
			}
			if(isset($queryArr['owner_id'])){
				$qArr = explode(',', $queryArr['owner_id']);
				$cl->setFilter($tmpAttrs['owner_id'], $qArr);	
			}
			if(isset($queryArr['subject_id'])){
				$qArr = explode(',', $queryArr['subject_id']);
				$cl->setFilter($tmpAttrs['subject_id'], $qArr);	
			}
			if(isset($queryArr['grade_id'])){
				$qArr = explode(',', $queryArr['grade_id']);
				$cl->setFilter($tmpAttrs['grade_id'], $qArr);	
			}
			if(isset($queryArr['first_cate'])){
				$qArr = explode(',', $queryArr['first_cate']);
				$cl->setFilter($tmpAttrs['first_cate'], $qArr);	
			}
			if(isset($queryArr['second_cate'])){
				$qArr = explode(',', $queryArr['second_cate']);
				$cl->setFilter($tmpAttrs['second_cate'], $qArr);	
			}
			if(isset($queryArr['third_cate'])){
				$qArr = explode(',', $queryArr['third_cate']);
				$cl->setFilter($tmpAttrs['third_cate'], $qArr);	
			}
			if(isset($queryArr['try'])){
				$qArr = explode(',', $queryArr['try']);
				$cl->setFilter($tmpAttrs['try'], $qArr);	
			}
			if(isset($queryArr['live_public_type'])){
				$qArr = explode(',', $queryArr['live_public_type']);
				$cl->setFilter($tmpAttrs['live_public_type'], $qArr);	
			}
			if(isset($queryArr['video_public_type'])){
				$qArr = explode(',', $queryArr['video_public_type']);
				$cl->setFilter($tmpAttrs['video_public_type'], $qArr);	
			}
			if(isset($queryArr['video_trial_time'])){
				$qArr = explode(',', $queryArr['video_trial_time']);
				$cl->setFilter($tmpAttrs['video_trial_time'], $qArr);	
			}
			if(isset($queryArr['max_user'])){
				$qArr = explode(',', $queryArr['max_user']);
				$cl->setFilter($tmpAttrs['max_user'], $qArr);	
			}
			if(isset($queryArr['user_total'])){
				$qArr = explode(',', $queryArr['user_total']);
				$cl->setFilter($tmpAttrs['user_total'], $qArr);	
			}
			if(isset($queryArr['status'])){
				$qArr = explode(',', $queryArr['status']);
				$cl->setFilter($tmpAttrs['status'], $qArr);	
			}
			if(isset($queryArr['org_status'])){
				$qArr = explode(',', $queryArr['org_status']);
				$cl->setFilter($tmpAttrs['org_status'], $qArr);	
			}
			if(isset($queryArr['course_status'])){
				$qArr = explode(',', $queryArr['course_status']);
				$cl->setFilter($tmpAttrs['course_status'], $qArr);	
			}
			if(isset($queryArr['admin_status'])){
				$qArr = explode(',', $queryArr['admin_status']);
				$cl->setFilter($tmpAttrs['admin_status'], $qArr);	
			}
			if(isset($queryArr['course_type'])){
				$qArr = explode(',', $queryArr['course_type']);
				$cl->setFilter($tmpAttrs['course_type'], $qArr);	
			}
			if(isset($queryArr['fee_type'])){
				$qArr = explode(',', $queryArr['fee_type']);
				$cl->setFilter($tmpAttrs['fee_type'], $qArr);	
			}
			if(isset($queryArr['region_level0'])){
				$qArr = explode(',', $queryArr['region_level0']);
				$cl->setFilter($tmpAttrs['region_level0'], $qArr);	
			}
			if(isset($queryArr['region_level1'])){
				$qArr = explode(',', $queryArr['region_level1']);
				$cl->setFilter($tmpAttrs['region_level1'], $qArr);	
			}
			if(isset($queryArr['region_level2'])){
				$qArr = explode(',', $queryArr['region_level2']);
				$cl->setFilter($tmpAttrs['region_level2'], $qArr);	
			}
			if(isset($queryArr['start_time'])){
				$qArr = explode(',', $queryArr['start_time']);
				if(count($qArr)<>2){
					$ret['error'] = 1;
					$ret['error_desc'] = 'start_time syntax error';
					return $ret;
				}
				$tmpBegin = strtotime($qArr[0]);
				$tmpEnd = strtotime($qArr[1]);
				if(FALSE === $tmpBegin || FALSE === $tmpEnd){
					$ret['error'] = 1;
					$ret['error_desc'] = 'start_time syntax error';
					return $ret;
				}
				$cl->setFilterRange($tmpAttrs['start_time'], $tmpBegin, $tmpEnd);	
			}
			if(isset($queryArr['end_time'])){
				$qArr = explode(',', $queryArr['end_time']);
				if(count($qArr)<>2){
					$ret['error'] = 1;
					$ret['error_desc'] = 'end_time syntax error';
					return $ret;
				}
				$tmpBegin = strtotime($qArr[0]);
				$tmpEnd = strtotime($qArr[1]);
				if(FALSE === $tmpBegin || FALSE === $tmpEnd){
					$ret['error'] = 1;
					$ret['error_desc'] = 'end_time syntax error';
					return $ret;
				}
				$cl->setFilterRange($tmpAttrs['end_time'], $tmpBegin, $tmpEnd);	
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
			if(isset($queryArr['last_updated'])){
				$qArr = explode(',', $queryArr['last_updated']);
				if(count($qArr)<>2){
					$ret['error'] = 1;
					$ret['error_desc'] = 'last_updated syntax error';
					return $ret;
				}
				$tmpBegin = strtotime($qArr[0]);
				$tmpEnd = strtotime($qArr[1]);
				if(FALSE === $tmpBegin || FALSE === $tmpEnd){
					$ret['error'] = 1;
					$ret['error_desc'] = 'last_updated syntax error';
					return $ret;
				}
				$cl->setFilterRange($tmpAttrs['last_updated'], $tmpBegin, $tmpEnd);	
			}
			if(isset($queryArr['admin_name'])){
				$queriesStr .=" @admin_name ".$queryArr['admin_name'];
			}
			if(isset($queryArr['teacher_name'])){
				$queriesStr .=" @teacher_name ".$queryArr['teacher_name'];
			}
			if(isset($queryArr['admin_real_name'])){
				$queriesStr .=" @admin_real_name ".$queryArr['admin_real_name'];
			}
			if(isset($queryArr['teacher_real_name'])){
				$queriesStr .=" @teacher_real_name ".$queryArr['teacher_real_name'];
			}
			if(isset($queryArr['course_name'])){
				$queriesStr .=" @course_name ".$queryArr['course_name'];
			}
			if(isset($queryArr['section_name'])){
				$queriesStr .=" @section_name ".$queryArr['section_name'];
			}
			if(isset($queryArr['section_desc'])){
				$queriesStr .=" @section_desc ".$queryArr['section_desc'];
			}
			if(isset($queryArr['class_name'])){
				$queriesStr .=" @class_name ".$queryArr['class_name'];
			}
			if(isset($queryArr['address'])){
				$queriesStr .=" @address ".$queryArr['address'];
			}
			if(isset($queryArr['first_cate_name'])){
				$queriesStr .=" @first_cate_name ".$queryArr['first_cate_name'];
			}
			if(isset($queryArr['second_cate_name'])){
				$queriesStr .=" @second_cate_name ".$queryArr['second_cate_name'];
			}
			if(isset($queryArr['third_cate_name'])){
				$queriesStr .=" @third_cate_name ".$queryArr['third_cate_name'];
			}
			if(isset($queryArr['first_cate_name_display'])){
				$queriesStr .=" @first_cate_name_display ".$queryArr['first_cate_name_display'];
			}
			if(isset($queryArr['second_cate_name_display'])){
				$queriesStr .=" @second_cate_name_display ".$queryArr['second_cate_name_display'];
			}
			if(isset($queryArr['third_cate_name_display'])){
				$queriesStr .=" @third_cate_name_display ".$queryArr['third_cate_name_display'];
			}
		}
		//设置page
		$beginOff = ($page-1)*$pageLength;
		$cl->setLimits((int)($beginOff), (int)($pageLength));
		//设置string query
		$res = $cl->Query($queriesStr,"plan");
		//get matches
		$data = array();
		if(isset($res['matches']) && count($res['matches']) > 0){
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
				$tagList = $dbTag->getPlanTagListByTids($gradeIdStr,$groupconf->grade);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpGrade['grade_id'] = $val['pk_tag'];
						$tmpGrade['grade_name'] = $val['name'];
						$grades[$val['fk_plan']][] = $tmpGrade;
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
				$tagList = $dbTag->getPlanTagListByTids($subjectIdStr,$groupconf->subject);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpSubject['subject_id'] = $val['pk_tag'];
						$tmpSubject['subject_name'] = $val['name'];
						$subjects[$val['fk_plan']][] = $tmpSubject;
					}
				}
			}
			
			$i = 0;
			$standardTime = strtotime('2000-01-01 00:00:00');
			foreach($res['matches'] as $id=>$val){
				foreach($field as $k){
					if(isset($timesArr[$k])){
						if($val['attrs'][$tmpAttrs[$k]] < $standardTime){
							$data[$i][$k] = '';
						}else{
							$data[$i][$k] = date('Y-m-d H:i:s', $val['attrs'][$tmpAttrs[$k]]);	
						}
					}elseif($k==='grade'){
						if(isset($grades[$val['attrs']['pk_plan']]))
							$data[$i][$k] = $grades[$val['attrs']['pk_plan']];
						else
							$data[$i][$k] = array();
					}elseif($k==='subject'){
						if(isset($subjects[$val['attrs']['pk_plan']]))
							$data[$i][$k] = $subjects[$val['attrs']['pk_plan']];
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
		<sphinx:field name="course_name"/>
		<sphinx:field name="section_name"/>
		<sphinx:field name="section_desc"/>
		<sphinx:field name="class_name"/>
		<sphinx:field name="admin_name"/>
		<sphinx:field name="admin_real_name"/>
		<sphinx:field name="teacher_name"/>
		<sphinx:field name="teacher_real_name"/>
		<sphinx:attr name="course_name" type="string"/>
		<sphinx:attr name="section_name" type="string"/>
		<sphinx:attr name="section_desc" type="string"/>
		<sphinx:attr name="class_name" type="string"/>
		<sphinx:attr name="region_level0" type="int"/>
		<sphinx:attr name="region_level1" type="int"/>
		<sphinx:attr name="region_level2" type="int"/>
		<sphinx:attr name="address" type="string"/>
		<sphinx:attr name="teacher_name" type="string"/>
		<sphinx:attr name="subject_name" type="string"/>
		<sphinx:attr name="grade_name" type="string"/>
		<sphinx:attr name="teacher_real_name" type="string"/>
		<sphinx:attr name="teacher_thumb_big" type="string"/>
		<sphinx:attr name="teacher_thumb_med" type="string"/>
		<sphinx:attr name="teacher_thumb_small" type="string"/>
		<sphinx:attr name="admin_name" type="string"/>
		<sphinx:attr name="admin_real_name" type="string"/>
		<sphinx:attr name="admin_thumb_big" type="string"/>
		<sphinx:attr name="admin_thumb_med" type="string"/>
		<sphinx:attr name="admin_thumb_small" type="string"/>

		<sphinx:attr name="pk_plan" type="int"/>
		<sphinx:attr name="video_id" type="int"/>
		<sphinx:attr name="fk_user_plan" type="int"/>
		<sphinx:attr name="fk_user_class" type="int"/>
		<sphinx:attr name="fk_user_owner" type="int"/>
		<sphinx:attr name="first_cate" type="int"/>
		<sphinx:attr name="second_cate" type="int"/>
		<sphinx:attr name="third_cate" type="int"/>
		<sphinx:attr name="first_cate_name" type="string"/>
		<sphinx:attr name="second_cate_name" type="string"/>
		<sphinx:attr name="third_cate_name" type="string"/>
		<sphinx:attr name="first_cate_name_display" type="string"/>
		<sphinx:attr name="second_cate_name_display" type="string"/>
		<sphinx:attr name="third_cate_name_display" type="string"/>
		<sphinx:attr name="subdomain" type="string"/>
		<sphinx:attr name="org_subname" type="string"/>
		<sphinx:attr name="org_id" type="int"/>
		<sphinx:attr name="fk_cate" type="int"/>
		<sphinx:attr name="fk_grade" type="int"/>
		<sphinx:attr name="fk_course" type="int"/>
		<sphinx:attr name="subject_id" type="multi"/>
		<sphinx:attr name="grade_id" type="multi"/>
		<sphinx:attr name="fk_section" type="int"/>
		<sphinx:attr name="fk_class" type="int"/>
		<sphinx:attr name="max_user" type="int"/>
		<sphinx:attr name="user_total" type="int"/>
		<sphinx:attr name="live_public_type" type="bigint"/>
		<sphinx:attr name="video_public_type" type="bigint"/>
		<sphinx:attr name="video_trial_time" type="int"/>
		<sphinx:attr name="totaltime" type="int"/>
		<sphinx:attr name="try" type="int"/>
		<sphinx:attr name="status" type="bigint"/>
		<sphinx:attr name="course_status" type="bigint"/>
		<sphinx:attr name="org_status" type="bigint"/>
		<sphinx:attr name="admin_status" type="bigint"/>
		<sphinx:attr name="course_type" type="int"/>
		<sphinx:attr name="fee_type" type="int"/>
		<sphinx:attr name="vv" type="int"/>
		<sphinx:attr name="vv_live" type="int"/>
		<sphinx:attr name="vv_record" type="int"/>
		<sphinx:attr name="vt" type="int"/>
		<sphinx:attr name="vt_live" type="int"/>
		<sphinx:attr name="vt_record" type="int"/>
		<sphinx:attr name="comment" type="int"/>
		<sphinx:attr name="discuss" type="int"/>
		<sphinx:attr name="start_time" type="bigint"/>
		<sphinx:attr name="end_time" type="bigint"/>
		<sphinx:attr name="create_time" type="bigint"/>
		<sphinx:attr name="last_updated" type="bigint"/>
		<sphinx:attr name="course_thumb_big" type="string"/>
		<sphinx:attr name="course_thumb_med" type="string"/>
		<sphinx:attr name="course_thumb_small" type="string"/>
		<sphinx:attr name="section_order_no" type="int"/>
		
		</sphinx:schema>';
		
		$dbTag  = new tag_db;
		$dbCourse = new course_db;
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_plan_length;
		$planList = $dbCourse->listPlans( 1, $pageLength );
		$totalPage = $planList->totalPage;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$planList = $dbCourse->listPlans($page,$pageLength);
			$userIdArr = $planIdArr = $videoIdArr = $cateIdArr = array();
			foreach($planList->items as $k=>$v){
				if((int)($v['fk_user_plan']))
					$userIdArr[$v['fk_user_plan']] = 0;
				if((int)($v['fk_user_class']))
					$userIdArr[$v['fk_user_class']] = 0;
				if((int)($v['fk_user_owner'])){
					$userIdArr[$v['fk_user_owner']] = 0;
					$userOwnerIdArr[$v['fk_user_owner']] = 0;
				}	
				$planIdArr[$v['pk_plan']] = 0;
				$cateIdArr[$v['first_cate']] = 0;
				$cateIdArr[$v['second_cate']] = 0;
				$cateIdArr[$v['third_cate']] = 0;
				if($v['video_id'] >0){
					$videoIdArr[$v['video_id']] = $v['pk_plan'];
				}
			}
			$userIdStr = implode(',', array_keys($userIdArr));
			$planIdStr = implode(',', array_keys($planIdArr));
			$cateIdStr = implode(',', array_keys($cateIdArr));
			$userOwnerIdStr = implode(',', array_keys($userOwnerIdArr));
			//get totaltime from db_video
			$videoIdStr= implode(',', array_keys($videoIdArr));
			$dbVideo = new video_db;
			$videoList = $dbVideo->listVideosByVideoIds( $videoIdStr );
			$videoData = array();
			if(!empty($videoList)){
				foreach($videoList->items as $videos){
					//$videoData[$videoIdArr[$videos['pk_video']]]['totaltime'] = $videos['totaltime'];
					$videoData[$videoIdArr[$videos['pk_video']]]['totaltime'] = !empty($videos['segs_totaltime']) ? $videos['segs_totaltime'] : $videos['totaltime'];
				}
			}
			$dbUser = new user_db;
			//get domain info random domain
			$domainList = $dbUser->listDomainsByUserIds( $userOwnerIdStr );
			$domainData = array();
			if(!empty($domainList)){
				foreach($domainList->items as $domains){
					$domainData[$domains['fk_user']]['subdomain'] = $domains['subdomain'];
					$domainData[$domains['fk_user']]['org_subname'] = $domains['org_subname'];
					$domainData[$domains['fk_user']]['org_id'] = $domains['fk_org'];
					$domainData[$domains['fk_user']]['org_status'] = $domains['org_status'];
				}
			}
			//get user info 
			$userList = $dbUser-> listUsersByUserIds( $userIdStr );
			if(isset($userList->items) && count($userList->items)>0){
				foreach($userList->items as $user){
					$userData[$user['pk_user']]['user_id'] = $user['pk_user'];
					$userData[$user['pk_user']]['user_name'] = $user['name'];
					$userData[$user['pk_user']]['real_name'] = $user['real_name'];
					$userData[$user['pk_user']]['user_thumb_big'] = $user['thumb_big'];
					$userData[$user['pk_user']]['user_thumb_med'] = $user['thumb_med'];
					$userData[$user['pk_user']]['user_thumb_small'] = $user['thumb_small'];
					if(isset($domainData[$user['pk_user']])){
						$userData[$user['pk_user']]['subdomain'] = $domainData[$user['pk_user']]['subdomain'];
						$userData[$user['pk_user']]['org_subname'] = $domainData[$user['pk_user']]['org_subname'];
						$userData[$user['pk_user']]['org_id'] = $domainData[$user['pk_user']]['org_id'];
						$userData[$user['pk_user']]['org_status'] = $domainData[$user['pk_user']]['org_status'];
					}else{
						$userData[$user['pk_user']]['subdomain'] = 'www.yunke.com';
						$userData[$user['pk_user']]['org_subname'] = '云课';
						$userData[$user['pk_user']]['org_id'] = 0;
						$userData[$user['pk_user']]['org_status'] = 0;
					}	
				}
			}else{
				$userData = array();
			}
			$dbStat = new stat_db;
			$statList = $dbStat->listPlanStatByIds( $planIdStr );
			if(isset($statList->items) && count($statList->items)>0){
				foreach($statList->items as $stat){
					$statData[$stat['fk_plan']]['vv'] = $stat['vv_live']+$stat['vv_record'];
					$statData[$stat['fk_plan']]['vv_live'] = $stat['vv_live'];
					$statData[$stat['fk_plan']]['vv_record'] = $stat['vv_record'];
					$statData[$stat['fk_plan']]['vt'] = $stat['vt_live'] + $stat['vt_record'];
					$statData[$stat['fk_plan']]['vt_live'] = $stat['vt_live'];
					$statData[$stat['fk_plan']]['vt_record'] = $stat['vt_record'];
					$statData[$stat['fk_plan']]['comment'] = $stat['comment_new'];
					$statData[$stat['fk_plan']]['discuss'] = $stat['comment'];
				}
			}else{
				$statData = array();
			}
			
			$cateList = $dbCourse->getCateByCateIdStr($cateIdStr);
			$cateData = array();
			if(!empty($cateList->items)){
				foreach($cateList->items as $cate){
					$cateData[$cate['pk_cate']]['cate_id'] = $cate['pk_cate'];
					$cateData[$cate['pk_cate']]['name'] = $cate['name'];
					$cateData[$cate['pk_cate']]['name_display'] = $cate['name_display'];
				}
			}
			
			$groupconf =  SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$tagPlanList = $dbTag->getTagPlanByPids($planIdStr);
			$gradeData = array();
			$subjectData = array();
			if(isset($tagPlanList->items) && count($tagPlanList->items)>0){	
				foreach($tagPlanList->items as $tag){
					if($tag['fk_group'] == $groupconf->grade){
						$gradeData[$tag['fk_plan']]['grade_id'][] = $tag['fk_tag'];
						$gradeData[$tag['fk_plan']]['grade_name'][] = $tag['name'];	
					}
					if($tag['fk_group'] == $groupconf->subject){
						$subjectData[$tag['fk_plan']]['subject_id'][] = $tag['fk_tag'];
						$subjectData[$tag['fk_plan']]['subject_name'][] = $tag['name'];	
					}
				}
			}
			if(isset($planList->items) && count($planList->items)>0){	
				foreach($planList->items as $plan ){
					echo '<sphinx:document id="'.$plan['pk_plan'].'">'."\n";
					if(isset($userData[$plan['fk_user_plan']])){
						$plan['teacher_name'] =  $userData[$plan['fk_user_plan']]['user_name'];
						$plan['teacher_real_name'] =  $userData[$plan['fk_user_plan']]['real_name'];
						$plan['teacher_thumb_big'] =  $userData[$plan['fk_user_plan']]['user_thumb_big'];
						$plan['teacher_thumb_med'] =  $userData[$plan['fk_user_plan']]['user_thumb_med'];
						$plan['teacher_thumb_small'] =  $userData[$plan['fk_user_plan']]['user_thumb_small'];
					}else{
						$plan['teacher_name'] = '';
						$plan['teacher_thumb_big'] = '';
						$plan['teacher_thumb_med'] = '';
						$plan['teacher_thumb_small'] = '';
					}
					if(isset($userData[$plan['fk_user_class']])){
						$plan['admin_name'] =  $userData[$plan['fk_user_class']]['user_name'];
						$plan['admin_real_name'] =  $userData[$plan['fk_user_class']]['real_name'];
						$plan['admin_thumb_big'] =  $userData[$plan['fk_user_class']]['user_thumb_big'];
						$plan['admin_thumb_med'] =  $userData[$plan['fk_user_class']]['user_thumb_med'];
						$plan['admin_thumb_small'] =  $userData[$plan['fk_user_class']]['user_thumb_small'];
					}else{
						$plan['admin_name'] = '';
						$plan['admin_thumb_big'] = '';
						$plan['admin_thumb_med'] = '';
						$plan['admin_thumb_small'] = '';
					}
					if(isset($userData[$plan['fk_user_owner']])){
						$plan['subdomain'] =  $userData[$plan['fk_user_owner']]['subdomain'];
						$plan['org_subname'] =  $userData[$plan['fk_user_owner']]['org_subname'];
						$plan['org_id'] =  $userData[$plan['fk_user_owner']]['org_id'];
						$plan['org_status'] =  $userData[$plan['fk_user_owner']]['org_status'];
					}else{
						$plan['subdomain'] =  'www.yunke.com';
						$plan['org_subname'] =  '云课';
						$plan['org_id'] =  0;
						$plan['org_status'] =  0;
					}	
					if(isset($statData[$plan['pk_plan']])){
						$plan['vv'] = $statData[$plan['pk_plan']]['vv'];
						$plan['vv_live'] = $statData[$plan['pk_plan']]['vv_live'];
						$plan['vv_record'] = $statData[$plan['pk_plan']]['vv_record'];
						$plan['vt'] = $statData[$plan['pk_plan']]['vt'];
						$plan['vt_live'] = $statData[$plan['pk_plan']]['vt_live'];
						$plan['vt_record'] = $statData[$plan['pk_plan']]['vt_record'];
						$plan['comment'] = $statData[$plan['pk_plan']]['comment'];
						$plan['discuss'] = $statData[$plan['pk_plan']]['discuss'];
					}else{
						$plan['vv'] = 0;
						$plan['vv_live'] = 0;
						$plan['vv_record'] = 0;
						$plan['vt'] = 0;
						$plan['vt_record'] = 0;
						$plan['vt_live'] = 0;
						$plan['comment'] = 0;
						$plan['discuss'] = 0;
					}
					if(isset($videoData[$plan['pk_plan']])){
						$plan['totaltime'] = $videoData[$plan['pk_plan']]['totaltime'];
					}else{
						$plan['totaltime'] = 0;
					}

					$plan['first_cate_name'] = '';
					$plan['first_cate_name_display'] = '';
					$plan['second_cate_name'] = '';
					$plan['second_cate_name_display'] = '';
					$plan['third_cate_name'] = '';
					$plan['third_cate_name_display'] = '';
					if(!empty($cateData[$plan['first_cate']])){
						$plan['first_cate_name'] = $cateData[$plan['first_cate']]['name'];
						$plan['first_cate_name_display'] = $cateData[$plan['first_cate']]['name_display'];
					}
					if(!empty($cateData[$plan['second_cate']])){
						$plan['second_cate_name'] = $cateData[$plan['second_cate']]['name'];
						$plan['second_cate_name_display'] = $cateData[$plan['second_cate']]['name_display'];
					}
					if(!empty($cateData[$plan['third_cate']])){
						$plan['third_cate_name'] = $cateData[$plan['third_cate']]['name'];
						$plan['third_cate_name_display'] = $cateData[$plan['third_cate']]['name_display'];
					}

					//set grade data
					if(isset($gradeData[$plan['pk_plan']]) && count($gradeData[$plan['pk_plan']]) >0 ){
						if(!empty($gradeData[$plan['pk_plan']]['grade_id'])){
							$plan['grade_id'] = implode(',',$gradeData[$plan['pk_plan']]['grade_id']);
						}else{
							$plan['grade_id'] = '';
						}
						if(!empty($gradeData[$plan['pk_plan']]['grade_name'])){
							$plan['grade_name'] = implode(',',$gradeData[$plan['pk_plan']]['grade_name']);
						}else{
							$plan['grade_name'] = '';
						}
					}else{
						$plan['grade_id'] = '';
						$plan['grade_name'] = '';
					}

					//set subject data
					if(isset($subjectData[$plan['pk_plan']]) && count($subjectData[$plan['pk_plan']]) >0 ){
						if(!empty($subjectData[$plan['pk_plan']]['subject_id'])){
							$plan['subject_id'] = implode(',',$subjectData[$plan['pk_plan']]['subject_id']);
						}else{
							$plan['subject_id'] = '';
						}
						if(!empty($subjectData[$plan['pk_plan']]['subject_name'])){
							$plan['subject_name'] = implode(',',$subjectData[$plan['pk_plan']]['subject_name']);
						}else{
							$plan['subject_name'] = '';
						}
					}else{
						$plan['subject_id'] = '';
						$plan['subject_name'] = '';
					}

					$videoTypeArr = array(0,-2,-1);
					$liveStatusArr = array(1,2);
					$plan['try'] = 0;
					if($plan['status'] == 3 && !in_array($plan['video_public_type'],$videoTypeArr)){
						$plan['try'] = 1;
					}elseif(in_array($plan['status'],$liveStatusArr) && $plan['live_public_type'] != 0){
						$plan['try'] = 1;
					}

					foreach($plan as $k=>$v){
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
		<sphinx:field name="course_name"/>
		<sphinx:field name="section_name"/>
		<sphinx:field name="section_desc"/>
		<sphinx:field name="class_name"/>
		<sphinx:field name="admin_name"/>
		<sphinx:field name="admin_real_name"/>
		<sphinx:field name="teacher_name"/>
		<sphinx:field name="teacher_real_name"/>
		<sphinx:attr name="course_name" type="string"/>
		<sphinx:attr name="section_name" type="string"/>
		<sphinx:attr name="section_desc" type="string"/>
		<sphinx:attr name="class_name" type="string"/>
		<sphinx:attr name="region_level0" type="int"/>
		<sphinx:attr name="region_level1" type="int"/>
		<sphinx:attr name="region_level2" type="int"/>
		<sphinx:attr name="address" type="string"/>
		<sphinx:attr name="teacher_name" type="string"/>
		<sphinx:attr name="subject_name" type="string"/>
		<sphinx:attr name="grade_name" type="string"/>
		<sphinx:attr name="teacher_real_name" type="string"/>
		<sphinx:attr name="teacher_thumb_big" type="string"/>
		<sphinx:attr name="teacher_thumb_med" type="string"/>
		<sphinx:attr name="teacher_thumb_small" type="string"/>
		<sphinx:attr name="admin_name" type="string"/>
		<sphinx:attr name="admin_real_name" type="string"/>
		<sphinx:attr name="admin_thumb_big" type="string"/>
		<sphinx:attr name="admin_thumb_med" type="string"/>
		<sphinx:attr name="admin_thumb_small" type="string"/>

		<sphinx:attr name="pk_plan" type="int"/>
		<sphinx:attr name="video_id" type="int"/>
		<sphinx:attr name="fk_user_plan" type="int"/>
		<sphinx:attr name="fk_user_class" type="int"/>
		<sphinx:attr name="fk_user_owner" type="int"/>
		<sphinx:attr name="first_cate" type="int"/>
		<sphinx:attr name="second_cate" type="int"/>
		<sphinx:attr name="third_cate" type="int"/>
		<sphinx:attr name="first_cate_name" type="string"/>
		<sphinx:attr name="second_cate_name" type="string"/>
		<sphinx:attr name="third_cate_name" type="string"/>
		<sphinx:attr name="first_cate_name_display" type="string"/>
		<sphinx:attr name="second_cate_name_display" type="string"/>
		<sphinx:attr name="third_cate_name_display" type="string"/>
		<sphinx:attr name="subdomain" type="string"/>
		<sphinx:attr name="org_subname" type="string"/>
		<sphinx:attr name="org_id" type="int"/>
		<sphinx:attr name="fk_cate" type="int"/>
		<sphinx:attr name="fk_grade" type="int"/>
		<sphinx:attr name="fk_course" type="int"/>
		<sphinx:attr name="subject_id" type="multi"/>
		<sphinx:attr name="grade_id" type="multi"/>
		<sphinx:attr name="fk_section" type="int"/>
		<sphinx:attr name="fk_class" type="int"/>
		<sphinx:attr name="max_user" type="int"/>
		<sphinx:attr name="user_total" type="int"/>
		<sphinx:attr name="live_public_type" type="bigint"/>
		<sphinx:attr name="video_public_type" type="bigint"/>
		<sphinx:attr name="video_trial_time" type="int"/>
		<sphinx:attr name="try" type="int"/>
		<sphinx:attr name="totaltime" type="int"/>
		<sphinx:attr name="status" type="bigint"/>
		<sphinx:attr name="course_status" type="bigint"/>
		<sphinx:attr name="org_status" type="bigint"/>
		<sphinx:attr name="admin_status" type="bigint"/>
		<sphinx:attr name="course_type" type="int"/>
		<sphinx:attr name="fee_type" type="int"/>
		<sphinx:attr name="vv" type="int"/>
		<sphinx:attr name="vv_live" type="int"/>
		<sphinx:attr name="vv_record" type="int"/>
		<sphinx:attr name="vt" type="int"/>
		<sphinx:attr name="vt_live" type="int"/>
		<sphinx:attr name="vt_record" type="int"/>
		<sphinx:attr name="comment" type="int"/>
		<sphinx:attr name="discuss" type="int"/>
		<sphinx:attr name="start_time" type="bigint"/>
		<sphinx:attr name="end_time" type="bigint"/>
		<sphinx:attr name="create_time" type="bigint"/>
		<sphinx:attr name="last_updated" type="bigint"/>
		<sphinx:attr name="course_thumb_big" type="string"/>
		<sphinx:attr name="course_thumb_med" type="string"/>
		<sphinx:attr name="course_thumb_small" type="string"/>
		<sphinx:attr name="section_order_no" type="int"/>

		</sphinx:schema>';
		
		$dbTag  = new tag_db;
		$dbCourse = new course_db;
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_plan_length;
		$planList = $dbCourse->listPlans( 1, $pageLength,$conf->seek_plan_delta );
		if(isset($planList->totalPage))
			$totalPage = $planList->totalPage;
		else $totalPage = 0;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$planList = $dbCourse->listPlans($page,$pageLength, $conf->seek_plan_delta);
			$userIdArr = $planIdArr = $userOwnerIdArr = $cateIdArr = $vudeoIdArr = array();
			if(isset($planList->items) && count($planList->items)>0){
				foreach($planList->items as $k=>$v){
					if((int)($v['fk_user_plan']))
						$userIdArr[$v['fk_user_plan']] = 0;
					if((int)($v['fk_user_class']))
						$userIdArr[$v['fk_user_class']] = 0;
					if((int)($v['fk_user_owner'])){
						$userIdArr[$v['fk_user_owner']] = 0;
						$userOwnerIdArr[$v['fk_user_owner']] = 0;
					}	
					$planIdArr[$v['pk_plan']] = 0;
					$cateIdArr[$v['first_cate']] = 0;
					$cateIdArr[$v['second_cate']] = 0;
					$cateIdArr[$v['third_cate']] = 0;
					if($v['video_id'] >0){
						$videoIdArr[$v['video_id']] = $v['pk_plan'];
					}
				}
			}
			$userIdStr = implode(',', array_keys($userIdArr));
			$planIdStr = implode(',', array_keys($planIdArr));
			$cateIdStr   = implode(',', array_keys($cateIdArr));
			$userOwnerIdStr = implode(',', array_keys($userOwnerIdArr));
			//get totaltime from db_video
			$videoIdStr= implode(',', array_keys($videoIdArr));
			$dbVideo = new video_db;
			$videoList = $dbVideo->listVideosByVideoIds( $videoIdStr );
			$videoData = array();
			if(!empty($videoList)){
				foreach($videoList->items as $videos){
					$videoData[$videoIdArr[$videos['pk_video']]]['totaltime'] = !empty($videos['segs_totaltime']) ? $videos['segs_totaltime'] : $videos['totaltime'];
				}
			}
			$dbUser = new user_db;
			//get domain info random domain
			$domainList = $dbUser->listDomainsByUserIds( $userOwnerIdStr );
			$domainData = array();
			if(!empty($domainList)){
				foreach($domainList->items as $domains){
					$domainData[$domains['fk_user']]['subdomain'] = $domains['subdomain'];
					$domainData[$domains['fk_user']]['org_subname'] = $domains['org_subname'];
					$domainData[$domains['fk_user']]['org_id'] = $domains['fk_org'];
					$domainData[$domains['fk_user']]['org_status'] = $domains['org_status'];
				}
			}
			//get user info 
			$userList = $dbUser-> listUsersByUserIds( $userIdStr );
			if(isset($userList->items) && count($userList->items)>0){
				foreach($userList->items as $user){
					$userData[$user['pk_user']]['user_id'] = $user['pk_user'];
					$userData[$user['pk_user']]['user_name'] = $user['name'];
					$userData[$user['pk_user']]['real_name'] = $user['real_name'];
					$userData[$user['pk_user']]['user_thumb_big'] = $user['thumb_big'];
					$userData[$user['pk_user']]['user_thumb_med'] = $user['thumb_med'];
					$userData[$user['pk_user']]['user_thumb_small'] = $user['thumb_small'];
					if(isset($domainData[$user['pk_user']])){
						$userData[$user['pk_user']]['subdomain'] = $domainData[$user['pk_user']]['subdomain'];
						$userData[$user['pk_user']]['org_subname'] = $domainData[$user['pk_user']]['org_subname'];
						$userData[$user['pk_user']]['org_id'] = $domainData[$user['pk_user']]['org_id'];
						$userData[$user['pk_user']]['org_status'] = $domainData[$user['pk_user']]['org_status'];
					}else{
						$userData[$user['pk_user']]['subdomain'] = 'www.yunke.com';
						$userData[$user['pk_user']]['org_subname'] = '云课';
						$userData[$user['pk_user']]['org_id'] = 0;
						$userData[$user['pk_user']]['org_status'] = 0;
					}	
				}
			}else{
				$userData = array();
			}
			$dbStat = new stat_db;
			$statList = $dbStat->listPlanStatByIds( $planIdStr );
			if(isset($statList->items) && count($statList->items)>0){
				foreach($statList->items as $stat){
					$statData[$stat['fk_plan']]['vv'] = $stat['vv_live']+$stat['vv_record'];
					$statData[$stat['fk_plan']]['vv_live'] = $stat['vv_live'];
					$statData[$stat['fk_plan']]['vv_record'] = $stat['vv_record'];
					$statData[$stat['fk_plan']]['vt'] = $stat['vt_live'] + $stat['vt_record'];
					$statData[$stat['fk_plan']]['vt_live'] = $stat['vt_live'];
					$statData[$stat['fk_plan']]['vt_record'] = $stat['vt_record'];
					$statData[$stat['fk_plan']]['comment'] = $stat['comment_new'];
					$statData[$stat['fk_plan']]['discuss'] = $stat['comment'];
				}
			}else{
				$statData = array();
			}
			
			$cateList = $dbCourse->getCateByCateIdStr($cateIdStr);
			$cateData = array();
			if(!empty($cateList->items)){
				foreach($cateList->items as $cate){
					$cateData[$cate['pk_cate']]['cate_id'] = $cate['pk_cate'];
					$cateData[$cate['pk_cate']]['name'] = $cate['name'];
					$cateData[$cate['pk_cate']]['name_display'] = $cate['name_display'];
				}
			}
			
			$groupconf =  SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$tagPlanList = $dbTag->getTagPlanByPids($planIdStr);
			$gradeData = array();
			$subjectData = array();
			if(isset($tagPlanList->items) && count($tagPlanList->items)>0){	
				foreach($tagPlanList->items as $tag){
					if($tag['fk_group'] == $groupconf->grade){
						$gradeData[$tag['fk_plan']]['grade_id'][] = $tag['fk_tag'];
						$gradeData[$tag['fk_plan']]['grade_name'][] = $tag['name'];	
					}
					if($tag['fk_group'] == $groupconf->subject){
						$subjectData[$tag['fk_plan']]['subject_id'][] = $tag['fk_tag'];
						$subjectData[$tag['fk_plan']]['subject_name'][] = $tag['name'];	
					}
				}
			}
			if(isset($planList->items) && count($planList->items)>0){	
				foreach($planList->items as $plan ){
					echo '<sphinx:document id="'.$plan['pk_plan'].'">'."\n";
					if(isset($userData[$plan['fk_user_plan']])){
						$plan['teacher_name'] =  $userData[$plan['fk_user_plan']]['user_name'];
						$plan['teacher_real_name'] =  $userData[$plan['fk_user_plan']]['real_name'];
						$plan['teacher_thumb_big'] =  $userData[$plan['fk_user_plan']]['user_thumb_big'];
						$plan['teacher_thumb_med'] =  $userData[$plan['fk_user_plan']]['user_thumb_med'];
						$plan['teacher_thumb_small'] =  $userData[$plan['fk_user_plan']]['user_thumb_small'];
					}else{
						$plan['teacher_name'] = '';
						$plan['teacher_thumb_big'] = '';
						$plan['teacher_thumb_med'] = '';
						$plan['teacher_thumb_small'] = '';
					}
					if(isset($userData[$plan['fk_user_class']])){
						$plan['admin_name'] =  $userData[$plan['fk_user_class']]['user_name'];
						$plan['admin_real_name'] =  $userData[$plan['fk_user_class']]['real_name'];
						$plan['admin_thumb_big'] =  $userData[$plan['fk_user_class']]['user_thumb_big'];
						$plan['admin_thumb_med'] =  $userData[$plan['fk_user_class']]['user_thumb_med'];
						$plan['admin_thumb_small'] =  $userData[$plan['fk_user_class']]['user_thumb_small'];
					}else{
						$plan['admin_name'] = '';
						$plan['admin_thumb_big'] = '';
						$plan['admin_thumb_med'] = '';
						$plan['admin_thumb_small'] = '';
					}
					if(isset($userData[$plan['fk_user_owner']])){
						$plan['subdomain'] =  $userData[$plan['fk_user_owner']]['subdomain'];
						$plan['org_subname'] =  $userData[$plan['fk_user_owner']]['org_subname'];
						$plan['org_id'] =  $userData[$plan['fk_user_owner']]['org_id'];
						$plan['org_status'] =  $userData[$plan['fk_user_owner']]['org_status'];
					}else{
						$plan['subdomain'] =  'www.yunke.com';
						$plan['org_subname'] =  '云课';
						$plan['org_id'] =  0;
						$plan['org_status'] =  0;
					}	
					if(isset($statData[$plan['pk_plan']])){
						$plan['vv'] = $statData[$plan['pk_plan']]['vv'];
						$plan['vv_live'] = $statData[$plan['pk_plan']]['vv_live'];
						$plan['vv_record'] = $statData[$plan['pk_plan']]['vv_record'];
						$plan['vt'] = $statData[$plan['pk_plan']]['vt'];
						$plan['vt_live'] = $statData[$plan['pk_plan']]['vt_live'];
						$plan['vt_record'] = $statData[$plan['pk_plan']]['vt_record'];
						$plan['comment'] = $statData[$plan['pk_plan']]['comment'];
						$plan['discuss'] = $statData[$plan['pk_plan']]['discuss'];
					}else{
						$plan['vv'] = 0;
						$plan['vv_live'] = 0;
						$plan['vv_record'] = 0;
						$plan['vt'] = 0;
						$plan['vt_record'] = 0;
						$plan['vt_live'] = 0;
						$plan['comment'] = 0;
						$plan['discuss'] = 0;
					}
					if(isset($videoData[$plan['pk_plan']])){
						$plan['totaltime'] = $videoData[$plan['pk_plan']]['totaltime'];
					}else{
						$plan['totaltime'] = 0;
					}

					$plan['first_cate_name'] = '';
					$plan['first_cate_name_display'] = '';
					$plan['second_cate_name'] = '';
					$plan['second_cate_name_display'] = '';
					$plan['third_cate_name'] = '';
					$plan['third_cate_name_display'] = '';
					if(!empty($cateData[$plan['first_cate']])){
						$plan['first_cate_name'] = $cateData[$plan['first_cate']]['name'];
						$plan['first_cate_name_display'] = $cateData[$plan['first_cate']]['name_display'];
					}
					if(!empty($cateData[$plan['second_cate']])){
						$plan['second_cate_name'] = $cateData[$plan['second_cate']]['name'];
						$plan['second_cate_name_display'] = $cateData[$plan['second_cate']]['name_display'];
					}
					if(!empty($cateData[$plan['third_cate']])){
						$plan['third_cate_name'] = $cateData[$plan['third_cate']]['name'];
						$plan['third_cate_name_display'] = $cateData[$plan['third_cate']]['name_display'];
					}

					//set grade data
					if(isset($gradeData[$plan['pk_plan']]) && count($gradeData[$plan['pk_plan']]) >0 ){
						if(!empty($gradeData[$plan['pk_plan']]['grade_id'])){
							$plan['grade_id'] = implode(',',$gradeData[$plan['pk_plan']]['grade_id']);
						}else{
							$plan['grade_id'] = '';
						}
						if(!empty($gradeData[$plan['pk_plan']]['grade_name'])){
							$plan['grade_name'] = implode(',',$gradeData[$plan['pk_plan']]['grade_name']);
						}else{
							$plan['grade_name'] = '';
						}
					}else{
						$plan['grade_id'] = '';
						$plan['grade_name'] = '';
					}

					//set subject data
					if(isset($subjectData[$plan['pk_plan']]) && count($subjectData[$plan['pk_plan']]) >0 ){
						if(!empty($subjectData[$plan['pk_plan']]['subject_id'])){
							$plan['subject_id'] = implode(',',$subjectData[$plan['pk_plan']]['subject_id']);
						}else{
							$plan['subject_id'] = '';
						}
						if(!empty($subjectData[$plan['pk_plan']]['subject_name'])){
							$plan['subject_name'] = implode(',',$subjectData[$plan['pk_plan']]['subject_name']);
						}else{
							$plan['subject_name'] = '';
						}
					}else{
						$plan['subject_id'] = '';
						$plan['subject_name'] = '';
					}

					$videoTypeArr = array(0,-2,-1);
					$liveStatusArr = array(1,2);
					$plan['try'] = 0;
					if($plan['status'] == 3 && !in_array($plan['video_public_type'],$videoTypeArr)){
						$plan['try'] = 1;
					}elseif(in_array($plan['status'],$liveStatusArr) && $plan['live_public_type'] != 0){
						$plan['try'] = 1;
					}

					foreach($plan as $k=>$v){
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
