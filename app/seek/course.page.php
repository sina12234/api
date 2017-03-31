<?php
require("grade.php");
require("subject.php");
class seek_course{
	var $attrs = array(
		'course_id'=>'pk_course',
		'title'=>'title',
		'tags'=>'tags',
		'desc'=>'descript',
		'thumb_big'=>'thumb_big',
		'thumb_med'=>'thumb_med',
		'thumb_sma'=>'thumb_small',
		'first_cate'=>'first_cate',
		'second_cate'=>'second_cate',
		'third_cate'=>'third_cate',
		'first_cate_name'=>'first_cate_name',
		'second_cate_name'=>'second_cate_name',
		'third_cate_name'=>'third_cate_name',
		'first_cate_name_display'=>'first_cate_name_display',
		'second_cate_name_display'=>'second_cate_name_display',
		'third_cate_name_display'=>'third_cate_name_display',
		'attr_value_id'=>'attr_value_id',
		'user_thumb_big'=>'user_thumb_big',
		'user_thumb_med'=>'user_thumb_med',
		'user_thumb_sma'=>'user_thumb_small',
		'user_name'=>'user_name',
		'user_real_name'=>'user_real_name',
		'subject_id'=>'subject_id',
		'recomm_weight'=>'sort',
		'course_type'=>'type',
		'grade_id'=>'grade_id',
		'user_id'=>'fk_user',
		'public_type'=>'public_type',
		'fee_type'=>'fee_type',
		'max_user'=>'max_user',
		'min_user'=>'min_user',
		'remain_user' =>'remain_user',
		'try' => 'try',
		'status'=>'status',
		'admin_status'=>'admin_status',
		'system_status'=>'system_status',
		'start_time'=>'start_time',
		'end_time'=>'end_time',
		'create_time'=>'create_time',
		'last_updated'=>'last_updated',
		'class_id'=>'class_id',
		//'section_id'=>'section_id',
		'price'=>'price',
		'market_price'=>'price_market',
		'top'=>'top',
		'vv'=>'vv',
		'user_total'=>'user_total',
		'vv_live'=>'vv_live',
		'vv_record'=>'vv_record',
		'vt'=>'vt',
		'vt_live'=>'vt_live',
		'vt_record'=>'vt_record',
		//'section_count'=>'section_count',
		'class_count'=>'class_count',
		'comment'=>'comment',
		'discuss'=>'discuss',
		'avg_score' => 'avg_score',
		'search_field'=>'search_field',
		'have_plan_date'=>'have_plan_date',
		'subdomain'=>'subdomain',
		'org_subname'=>'org_subname',
		'org_status'=>'org_status',
		'scope' => 'scope',
		'org_id' => 'org_id',
		'member_set_id' => 'member_set_id',
		'course_tag_id' => 'course_tag_id',
		'is_promote' => 'is_promote',
		'price_promote' => 'price_promote',
		'promote_status' => 'promote_status',
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

		if(empty((array)$params->f)){
			$field = array(
				'course_id',
				'title',
				'create_time',
			);
		}else{
			if(isset($params->f)){
				foreach($params->f as $k=>$v){
					$field[] = $v;
				}
			}
		}
		//new获取查询条件
		if(empty((array)$params->q)){
			$queryArr = array();
		}else{
			foreach($tmpAttrs as $k=>$v){
				if(isset($params->q->$k)){
					$queryArr[$k] = $params->q->$k;
				}
			}
		}
		//new获取order by 信息
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$address = $conf->seek_course_ip;
		$port = (int)($conf->seek_course_port);
		$cl = new SSphinx();
		$cl->SetServer ( $address, $port);
		$cl->ResetFilters();
		$cl->ResetGroupBy();
		$cl->SetArrayResult( true );
		$cl->SetMatchMode ( $mode=SPH_MATCH_EXTENDED );
		$order = array();
		if(empty($params->ob)){
			$order = array();
		}else{
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
			if(isset($queryArr['course_id'])){
				$qArr = explode(',', $queryArr['course_id']);
				$cl->setFilter($tmpAttrs['course_id'], $qArr);	
			}
			if(isset($queryArr['org_id'])){
				$qArr = explode(',', $queryArr['org_id']);
				$cl->setFilter($tmpAttrs['org_id'], $qArr);	
			}
			if(isset($queryArr['recomm_weight'])){
				$qArr = explode(',', $queryArr['recomm_weight']);
				$cl->setFilter($tmpAttrs['recomm_weight'], $qArr);	
			}
			if(isset($queryArr['course_type'])){
				$qArr = explode(',', $queryArr['course_type']);
				$cl->setFilter($tmpAttrs['course_type'], $qArr);	
			}
			if(isset($queryArr['user_id'])){
				$qArr = explode(',', $queryArr['user_id']);
				$cl->setFilter($tmpAttrs['user_id'], $qArr);	
			}
			if(isset($queryArr['attr_value_id'])){
				$qArr = explode(',', $queryArr['attr_value_id']);
				$cl->setFilter($tmpAttrs['attr_value_id'], $qArr);	
			}
			if(isset($queryArr['public_type'])){
				$qArr = explode(',', $queryArr['public_type']);
				$cl->setFilter($tmpAttrs['public_type'], $qArr);	
			}
			if(isset($queryArr['fee_type'])){
				$qArr = explode(',', $queryArr['fee_type']);
				$cl->setFilter($tmpAttrs['fee_type'], $qArr);	
			}
			if(isset($queryArr['max_user'])){
				$qArr = explode(',', $queryArr['max_user']);
				$cl->setFilter($tmpAttrs['max_user'], $qArr);	
			}
			if(isset($queryArr['min_user'])){
				$qArr = explode(',', $queryArr['min_user']);
				$cl->setFilter($tmpAttrs['min_user'], $qArr);	
			}
			if(isset($queryArr['user_total'])){
				$qArr = explode(',', $queryArr['user_total']);
				$cl->setFilter($tmpAttrs['user_total'], $qArr);	
			}
			if(isset($queryArr['remain_user'])){
				$qArr = explode(',', $queryArr['remain_user']);
				$cl->setFilter($tmpAttrs['remain_user'], $qArr);	
			}
			if(isset($queryArr['try'])){
				$qArr = explode(',', $queryArr['try']);
				$cl->setFilter($tmpAttrs['try'], $qArr);	
			}
			if(isset($queryArr['status'])){
				$qArr = explode(',', $queryArr['status']);
				$cl->setFilter($tmpAttrs['status'], $qArr);	
			}
			if(isset($queryArr['org_status'])){
				$qArr = explode(',', $queryArr['org_status']);
				$cl->setFilter($tmpAttrs['org_status'], $qArr);	
			}
			if(isset($queryArr['promote_status'])){
				$qArr = explode(',', $queryArr['promote_status']);
				$cl->setFilter($tmpAttrs['promote_status'], $qArr);	
			}
			if(isset($queryArr['admin_status'])){
				$qArr = explode(',', $queryArr['admin_status']);
				$cl->setFilter($tmpAttrs['admin_status'], $qArr);	
			}
			if(isset($queryArr['system_status'])){
				$qArr = explode(',', $queryArr['system_status']);
				$cl->setFilter($tmpAttrs['system_status'], $qArr);	
			}
			if(isset($queryArr['is_promote'])){
				$qArr = explode(',', $queryArr['is_promote']);
				$cl->setFilter($tmpAttrs['is_promote'], $qArr);	
			}
			if(isset($queryArr['class_id'])){
				$qArr = explode(',', $queryArr['class_id']);
				$cl->setFilter($tmpAttrs['class_id'], $qArr);	
			}
			/*if(isset($queryArr['section_id'])){
				$qArr = explode(',', $queryArr['section_id']);
				$cl->setFilter($tmpAttrs['section_id'], $qArr);	
			}*/
			if(isset($queryArr['grade_id'])){
				$qArr = explode(',', $queryArr['grade_id']);
				$cl->setFilter($tmpAttrs['grade_id'], $qArr);	
			}
			if(isset($queryArr['subject_id'])){
				$qArr = explode(',', $queryArr['subject_id']);
				$cl->setFilter($tmpAttrs['subject_id'], $qArr);	
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
			if(isset($queryArr['top'])){
				$qArr = explode(',', $queryArr['top']);
				$cl->setFilter($tmpAttrs['top'], $qArr);	
			}
			/*if(isset($queryArr['section_count'])){
				$qArr = explode(',', $queryArr['section_count']);
				$cl->setFilter($tmpAttrs['section_count'], $qArr);	
			}*/
			if(isset($queryArr['class_count'])){
				$qArr = explode(',', $queryArr['class_count']);
				$cl->setFilter($tmpAttrs['class_count'], $qArr);	
			}
			if(isset($queryArr['member_set_id'])){
				$qArr = explode(',', $queryArr['member_set_id']);
				$cl->setFilter($tmpAttrs['member_set_id'], $qArr);	
			}
			if(isset($queryArr['course_tag_id'])){
				$qArr = explode(',', $queryArr['course_tag_id']);
				$cl->setFilter($tmpAttrs['course_tag_id'], $qArr);	
			}
			if(isset($queryArr['have_plan_date'])){
				$qArr = explode(',', $queryArr['have_plan_date']);
				foreach($qArr as $k=>$date){
					$tmpDate = date('Ymd',strtotime($date));
					if((int)($tmpDate))
						$qArr[$k] = (int)($tmpDate);
					else
						unset($qArr[$k]);
				}
				$cl->setFilter($tmpAttrs['have_plan_date'], $qArr);	
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
			if(isset($queryArr['price'])){
				$qArr = explode(',', $queryArr['price']);
				if(count($qArr)<>2){
					$cl->setFilter($tmpAttrs['price'], $qArr); 
				}else{
					$cl->setFilterRange($tmpAttrs['price'], $qArr[0], $qArr[1]);	
				}
			}
			if(isset($queryArr['price_promote'])){
				$qArr = explode(',', $queryArr['price_promote']);
				if(count($qArr)<>2){
					$cl->setFilter($tmpAttrs['price_promote'], $qArr); 
				}else{
					$cl->setFilterRange($tmpAttrs['price_promote'], $qArr[0], $qArr[1]);	
				}
			}
			if(isset($queryArr['market_price'])){
				$qArr = explode(',', $queryArr['market_price']);
				if(count($qArr)<>2){
					$cl->setFilter($tmpAttrs['market_price'], $qArr); 
				}else{
					$cl->setFilterRange($tmpAttrs['market_price'], $qArr[0], $qArr[1]);	
				}
			}
			if(isset($queryArr['title'])){
				$queriesStr .=" @title ".$queryArr['title'];
			}
			if(isset($queryArr['user_name'])){
				$queriesStr .=" @user_name ".$queryArr['user_name'];
			}
			if(isset($queryArr['desc'])){
				$queriesStr .=" @descript ".$queryArr['desc'];
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
			if(isset($queryArr['scope'])){
				$queriesStr .=" @scope ".$queryArr['scope'];
			}
			if(isset($queryArr['search_field'])){
				$queriesStr .=" @search_field ".$queryArr['search_field'];
			}
		}
		//设置page
		$beginOff = ($page-1)*$pageLength;
		$cl->setLimits((int)($beginOff), (int)($pageLength));
		//设置string query
		$res = $cl->Query($queriesStr,"course");
		//get matches
		$data = array();
		if(isset($res['matches']) && count($res['matches']) > 0){
			//check that if need to select from db 
			$classes = $sections = array();
			$grades = $subjects = array();
			$membersets = array();
			$tmpIdArr = array();
			if(in_array('class', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['class_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $classId){
							$tmpIdArr[$classId] = 0;
						}
					}
				}
				$classIdStr = implode(',', array_keys($tmpIdArr));
				$dbCourse = new course_db;
				$classList = $dbCourse->listClasses($classIdStr);
                if (!empty($classList->items)) {
					$regionIdArr = array();
					$regionList = array();
					foreach($classList->items as $co){
						if(!empty($co['region_level0'])){
							$regionIdArr[$co['region_level0']] = $co['region_level0'];
						}
						if(!empty($co['region_level1'])){
							$regionIdArr[$co['region_level1']] = $co['region_level1'];
						}
						if(!empty($co['region_level2'])){
							$regionIdArr[$co['region_level2']] = $co['region_level2'];
						}
					}
					$regionDb = new region_db;
					$regionRet = $regionDb->getRegionByRegionIdArr($regionIdArr);
					if(!empty($regionRet)){
						foreach($regionRet->items as $region){
							$regionList[$region['pk_region']] = $region['name'];
						}
					}
                    foreach($classList->items as $val){
                        $tmpCla['class_id'] = $val['pk_class'];
                        $tmpCla['class_admin_id'] = $val['fk_user_class'];
                        $tmpCla['owner_id'] = $val['fk_user'];
                        $tmpCla['name'] = $val['name'];
                        $tmpCla['desc'] = $val['descript'];
                        $tmpCla['type'] = $val['type'];
                        $tmpCla['max_user'] = $val['max_user'];
                        $tmpCla['min_user'] = $val['min_user'];
                        $tmpCla['user_total'] = $val['user_total'];
                        $tmpCla['status'] = $val['status'];
						if(!empty($regionList[$val['region_level0']])){
							$tmpCla['region_level0'] = $regionList[$val['region_level0']];
						}else{
							$tmpCla['region_level0'] = '';
						}
						if(!empty($regionList[$val['region_level1']])){
							$tmpCla['region_level1'] = $regionList[$val['region_level1']];
						}else{
							$tmpCla['region_level1'] = '';
						}
						if(!empty($regionList[$val['region_level2']])){
							$tmpCla['region_level2'] = $regionList[$val['region_level2']];
						}else{
							$tmpCla['region_level2'] = '';
						}
						$tmpCla['address'] = $val['address'];
                        $classes[$val['fk_course']][] = $tmpCla;
                    }
                }
			}
			
			$tmpIdArr = array();
			/*if(in_array('section', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['section_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $sectionId){
							$tmpIdArr[$sectionId] = 0;
						}
					}
				}
				$sectionIdStr = implode(',', array_keys($tmpIdArr));
				$dbCourse = new course_db;
				$sectionList = $dbCourse->listSections($sectionIdStr);
				if(!empty($sectionList->items)){
					foreach($sectionList->items as $val){
						$tmpSec['section_id'] = $val['pk_section'];
						$tmpSec['name'] = $val['name'];
						$tmpSec['desc'] = $val['descript'];
						$tmpSec['order'] = $val['order_no'];
						$tmpSec['status'] = $val['status'];
						$sections[$val['fk_course']][] = $tmpSec;
					}
				}	
			}*/

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
				$tagList = $dbTag->getCourseTagListByTids($gradeIdStr,$groupconf->grade);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpGrade['grade_id'] = $val['pk_tag'];
						$tmpGrade['grade_name'] = $val['name'];
						$grades[$val['fk_course']][] = $tmpGrade;
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
				$tagList = $dbTag->getCourseTagListByTids($subjectIdStr,$groupconf->subject);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpSubject['subject_id'] = $val['pk_tag'];
						$tmpSubject['subject_name'] = $val['name'];
						$subjects[$val['fk_course']][] = $tmpSubject;
					}
				}
			}
			
			$tmpIdArr = array();
			if(in_array('memberset', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['member_set_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $memberId){
							$tmpIdArr[$memberId] = 0;
						}
					}
				}
				$memberIdStr = implode(',', array_keys($tmpIdArr));
				$membersetList = user_db_orgMemberPriorityDao::getMemberPriorityBySetIds($memberIdStr,1);
				if(!empty($membersetList->items)){
					foreach($membersetList->items as $mo){
						$tmpMemberSet['member_set_id'] = $mo['fk_member_set'];
						$tmpMemberSet['member_set_name'] = $mo['title'];
						$tmpMemberSet['status'] = $mo['status'];
						$membersets[$mo['object_id']][] = $tmpMemberSet;
					}
				}
			}
			
			$tmpIdArr = array();
			$courseAttrs = array();
			if(in_array('course_attr',$field)){
				foreach($res['matches'] as $id=>$val){
					$tmpIdArr[$val['attrs']['pk_course']] = 0;
				}
				$courseIdStr = implode(',', array_keys($tmpIdArr));
				$dbCourse = new course_db;
				$attrValueList = $dbCourse->getCourseAttrValueByCourseIds($courseIdStr);
				if(!empty($attrValueList->items)){
					foreach($attrValueList->items as $value){
						$key = $value['course_id'].$value['attr_id'];
						$tmpAttrValue[$key]['attr_id'] = $value['attr_id'];
						$tmpAttrValue[$key]['attr_name'] = $value['attr_name'];
						$tmpAttrValue[$key]['attr_name_display'] = $value['attr_name_display'];
						$tmpAttrValue[$key]['attr_value'][$value['attr_value_id']]['attr_value_id'] = $value['attr_value_id'];
						$tmpAttrValue[$key]['attr_value'][$value['attr_value_id']]['attr_value_name'] = $value['value_name'];
						$courseAttrs[$value['course_id']][$value['attr_id']] = $tmpAttrValue[$key];
					}
					
				}
			}
			
			$i = 0;
			foreach($res['matches'] as $id=>$val){
				foreach($field as $k){
					if(isset($timesArr[$k])){
						$data[$i][$k] = date('Y-m-d H:i:s', $val['attrs'][$tmpAttrs[$k]]);	
					}elseif($k === 'class'){
						if(isset($classes[$val['attrs']['pk_course']]))
							$data[$i][$k] = $classes[$val['attrs']['pk_course']];
						else 
							$data[$i][$k] = array();
					/*}elseif($k==='section'){
						if(isset($sections[$val['attrs']['pk_course']]))
							$data[$i][$k] = $sections[$val['attrs']['pk_course']];
						else
							$data[$i][$k] = array();*/
					}elseif($k==='grade'){
						if(isset($grades[$val['attrs']['pk_course']]))
							$data[$i][$k] = $grades[$val['attrs']['pk_course']];
						else
							$data[$i][$k] = array();
					}elseif($k==='subject'){
						if(isset($subjects[$val['attrs']['pk_course']]))
							$data[$i][$k] = $subjects[$val['attrs']['pk_course']];
						else
							$data[$i][$k] = array();
					}elseif($k==='memberset'){
						if(isset($membersets[$val['attrs']['pk_course']]))
							$data[$i][$k] = $membersets[$val['attrs']['pk_course']];
						else
							$data[$i][$k] = array();
					}elseif($k==='course_attr'){
						if(isset($courseAttrs[$val['attrs']['pk_course']]))
							$data[$i][$k] = $courseAttrs[$val['attrs']['pk_course']];
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
		<sphinx:field name="title"/>
		<sphinx:field name="user_name"/>
		<sphinx:field name="descript"/>
		<sphinx:field name="search_field"/>
		<sphinx:attr name="thumb_big" type="string"/>
		<sphinx:attr name="thumb_med" type="string"/>
		<sphinx:attr name="thumb_small" type="string"/>
		<sphinx:attr name="descript" type="string"/>
		<sphinx:attr name="scope" type="string"/>
		<sphinx:attr name="search_field" type="string"/>
		<sphinx:attr name="title" type="string"/>
		<sphinx:attr name="user_name" type="string"/>
		<sphinx:attr name="user_real_name" type="string"/>
		<sphinx:attr name="user_thumb_big" type="string"/>
		<sphinx:attr name="user_thumb_med" type="string"/>
		<sphinx:attr name="user_thumb_small" type="string"/>
		<sphinx:attr name="subdomain" type="string"/>
		<sphinx:attr name="org_subname" type="string"/>
		<sphinx:attr name="pk_course" type="int"/>
		<sphinx:attr name="org_id" type="int"/>
		<sphinx:attr name="org_status" type="bigint"/>
		<sphinx:attr name="first_cate" type="int"/>
		<sphinx:attr name="second_cate" type="int"/>
		<sphinx:attr name="third_cate" type="int"/>
		<sphinx:attr name="first_cate_name" type="string"/>
		<sphinx:attr name="second_cate_name" type="string"/>
		<sphinx:attr name="third_cate_name" type="string"/>
		<sphinx:attr name="first_cate_name_display" type="string"/>
		<sphinx:attr name="second_cate_name_display" type="string"/>
		<sphinx:attr name="third_cate_name_display" type="string"/>
		<sphinx:attr name="attr_value_id" type="multi"/>
		<sphinx:attr name="attr_value_name" type="string"/>
		<sphinx:attr name="subject_id" type="multi"/>
		<sphinx:attr name="grade_name" type="string"/>
		<sphinx:attr name="subject_name" type="string"/>
		<sphinx:attr name="fk_cate" type="int"/>
		<sphinx:attr name="fk_grade" type="int"/>
		<sphinx:attr name="check_status" type="int"/>
		<sphinx:attr name="tags" type="string"/>
		<sphinx:attr name="sort" type="int"/>
		<sphinx:attr name="top" type="int"/>
		<sphinx:attr name="type" type="int"/>
		<sphinx:attr name="grade_id" type="multi"/>
		<sphinx:attr name="fk_user" type="int"/>
		<sphinx:attr name="public_type" type="int"/>
		<sphinx:attr name="fee_type" type="int"/>
		<sphinx:attr name="max_user" type="int"/>
		<sphinx:attr name="min_user" type="int"/>
		<sphinx:attr name="user_total" type="int"/>
		<sphinx:attr name="remain_user" type="int"/>
		<sphinx:attr name="try" type="int"/>
		<sphinx:attr name="status" type="bigint"/>
		<sphinx:attr name="admin_status" type="bigint"/>
		<sphinx:attr name="system_status" type="bigint"/>
		<sphinx:attr name="price" type="int"/>
		<sphinx:attr name="price_market" type="int"/>
		<sphinx:attr name="start_time" type="bigint"/>
		<sphinx:attr name="end_time" type="bigint"/>
		<sphinx:attr name="create_time" type="bigint"/>
		<sphinx:attr name="last_updated" type="bigint"/>
		<sphinx:attr name="class_id" type="multi"/>
		<sphinx:attr name="vv" type="int"/>
		<sphinx:attr name="vv_live" type="int"/>
		<sphinx:attr name="vv_record" type="int"/>
		<sphinx:attr name="vt" type="int"/>
		<sphinx:attr name="vt_live" type="int"/>
		<sphinx:attr name="vt_record" type="int"/>
		<sphinx:attr name="class_count" type="int"/>
		<sphinx:attr name="comment" type="int"/>
		<sphinx:attr name="discuss" type="int"/>
		<sphinx:attr name="avg_score" type="int"/>
		<sphinx:attr name="have_plan_date" type="multi"/>
		<sphinx:attr name="member_set_id" type="multi"/>
		<sphinx:attr name="course_tag_id" type="multi"/>
		<sphinx:attr name="is_promote" type="bigint"/>
		<sphinx:attr name="price_promote" type="int"/>
		<sphinx:attr name="promote_status" type="bigint"/>
		</sphinx:schema>';
		
		$dbTag  = new tag_db;
		$dbCourse = new course_db;
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_course_length;
		$startCourseId = 1;
		$courseList = $dbCourse->courselist(1,$pageLength,null,null,null,null,false,false,true,$startCourseId);
		$totalPage = $courseList->totalPage;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$courseList = $dbCourse->courselist($page,$pageLength,null,null,null,null,false,false,true,$startCourseId);
			$userIdArr = $courseIdArr = array();
			$cateIdArr = array();
			foreach($courseList->items as $k=>$v){
				$userIdArr[$v['fk_user']] = 0;
				$courseIdArr[] = $v['pk_course'];
				$cateIdArr[$v['first_cate']] = 0;
				$cateIdArr[$v['second_cate']] = 0;
				$cateIdArr[$v['third_cate']] = 0;
			}
			$userIdStr   = implode(',', array_keys($userIdArr));
			$courseIdStr = implode(',', $courseIdArr);
			$cateIdStr   = implode(',', array_keys($cateIdArr));
			$dbUser = new user_db;
			//get domain info random domain
			$domainList = $dbUser->listDomainsByUserIds( $userIdStr );
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
			$userData = array();
			if(!empty($userList)){
				foreach($userList->items as $user){
					$userData[$user['pk_user']]['user_id'] = $user['pk_user'];
					$userData[$user['pk_user']]['user_name'] = $user['name'];
					$userData[$user['pk_user']]['user_real_name'] = $user['real_name'];
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
			
			//get stat data
			$dbStat = new stat_db;
			$statList = $dbStat->listCourseStatByIds( $courseIdStr );
			$statData = array();
			foreach($statList->items as $stat){
				$statData[$stat['fk_course']]['vv'] = $stat['vv_live']+$stat['vv_record'];
				$statData[$stat['fk_course']]['vv_live'] = $stat['vv_live'];
				$statData[$stat['fk_course']]['vv_record'] = $stat['vv_record'];
				$statData[$stat['fk_course']]['vt'] = $stat['vt_live']+$stat['vv_record'];
				$statData[$stat['fk_course']]['vt_record'] = $stat['vt_record'];
				$statData[$stat['fk_course']]['vt_live'] = $stat['vt_live'];
				//$statData[$stat['fk_course']]['section_count'] = $stat['section_count'];
				$statData[$stat['fk_course']]['class_count'] = $stat['class_count'];
				$statData[$stat['fk_course']]['discuss'] = $stat['comment'];
				$statData[$stat['fk_course']]['comment'] = $stat['comment_new'];
			}
			//get class info
			$classList = $dbCourse->listClassesByCourseIds( $courseIdStr );
			$classData = array();
			if(isset($classList->items) && count($classList->items)>0){
				foreach($classList->items as $class){
					$classData[$class['fk_course']][] = $class['pk_class'];
				}
			}			
			/*/get section info
			$sectionList = $dbCourse->listSectionsByCourseIds( $courseIdStr );
			$sectionData = $sectionDescData = array();
			foreach($courseIdArr as $courseId){
				$sectionDescData[$courseId] = '';
			}
			if(isset($sectionList->items) && count($sectionList->items)>0){
				foreach($sectionList->items as $section){
					$sectionData[$section['fk_course']][] = $section['pk_section'];
					//if(isset($section['descript']))
						//$sectionDescData[$section['fk_course']] .= " ".$section['descript'];
				}
			}*/			
			//get plan start_time
			$planDateList = $dbCourse->listPlanDateByCourseIds( $courseIdStr );
			$planDateData = array();
			foreach($planDateList->items as $planDate){
				if($planDate['start_time'] == '0000-00-00 00:00:00')
					continue;
				$dateNumber = (int)(date("Ymd",strtotime( $planDate['start_time'] )));
				$planDateData[$planDate['fk_course']][] = $dateNumber;
			}

			$planList = $dbCourse->listPlanByCourseIds( $courseIdStr );
			$planData = array();
			if(!empty($planList->items)){
				foreach($planList->items as $plan){
					$planData[$plan['fk_course']][] = $plan;
				}
			}
			
			//get avg_sacore info
			$dbMessage = new message_db;
			$scoreList = $dbMessage->listCourseScoreByCourseIds( $courseIdStr );
			$scoreData = array();
			if(isset($scoreList->items) && count($scoreList->items)>0){
				foreach($scoreList->items as $score){
					$score_count = $score['score'];
					if(!empty($score['total_user'])){
						$avg_score = sprintf('%.1f',$score_count/$score['total_user']);
					}else{
						$avg_score = 0;
					}
					$scoreData[$score['fk_course']]['avg_score'] = $avg_score * 10;
				}
			}
			
			$groupconf =  SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$tagCourseList = $dbTag->getTagCourseByCids($courseIdStr);
			$gradeData = array();
			$subjectData = array();
			$courseTagData = array();
			$groupInfo = tag_db::getGroupInfo('课程标签');
			if(isset($tagCourseList->items) && count($tagCourseList->items)>0){	
				foreach($tagCourseList->items as $tag){
					if($tag['fk_group'] == $groupconf->grade){
						$gradeData[$tag['fk_course']]['grade_id'][] = $tag['fk_tag'];
						$gradeData[$tag['fk_course']]['grade_name'][] = $tag['name'];	
					}
					if($tag['fk_group'] == $groupconf->subject){
						$subjectData[$tag['fk_course']]['subject_id'][] = $tag['fk_tag'];
						$subjectData[$tag['fk_course']]['subject_name'][] = $tag['name'];	
					}
					if(!empty($groupInfo)){
						if($tag['fk_group'] == $groupInfo['pk_group']){
							$courseTagData[$tag['fk_course']]['course_tag_id'][] = $tag['fk_tag'];
						}
					}
				}
			}

			//get attr_value
			$attrValueData = array();
			$attrValueList = $dbCourse->getCourseAttrValueByCourseIds($courseIdStr);
			if(!empty($attrValueList->items)){
				foreach($attrValueList->items as $value){
					$attrValueData[$value['course_id']]['attr_value_id'][] = $value['attr_value_id'];
					$attrValueData[$value['course_id']]['attr_value_name'][] = $value['value_name'];
				}
			}
			//get memberset
			$memberSetData = array();
			$memberRet = user_db_orgMemberPriorityDao::getMemberPriorityByObjectIds($courseIdStr,1);
			if(!empty($memberRet->items)){
				foreach($memberRet->items as $mo){
					$memberSetData[$mo['object_id']]['member_set_id'][] = $mo['fk_member_set'];
				}
			}
			if(isset($courseList->items) && count($courseList->items)>0){	
				foreach($courseList->items as $course ){
					$startCousrseId = $course['pk_course'];
					echo '<sphinx:document id="'.$course['pk_course'].'">'."\n";
					$course['user_name'] =  $userData[$course['fk_user']]['user_name'];
					$course['user_real_name'] =  $userData[$course['fk_user']]['user_real_name'];
					$course['user_thumb_big'] =  $userData[$course['fk_user']]['user_thumb_big'];
					$course['user_thumb_med'] =  $userData[$course['fk_user']]['user_thumb_med'];
					$course['user_thumb_small'] =  $userData[$course['fk_user']]['user_thumb_small'];
					$course['subdomain'] =  $userData[$course['fk_user']]['subdomain'];
					$course['org_subname'] =  $userData[$course['fk_user']]['org_subname'];
					$course['org_id'] =  $userData[$course['fk_user']]['org_id'];
					$course['org_status'] =  $userData[$course['fk_user']]['org_status'];
					if(isset($statData[$course['pk_course']])){
						$course['vv']= $statData[$course['pk_course']]['vv'];
						$course['vv_live']= $statData[$course['pk_course']]['vv_live'];
						$course['vv_record']= $statData[$course['pk_course']]['vv_record'];
						$course['vt']= $statData[$course['pk_course']]['vt'];
						$course['vt_live']= $statData[$course['pk_course']]['vt_live'];
						$course['vt_record']= $statData[$course['pk_course']]['vt_record'];
						//$course['section_count']= $statData[$course['pk_course']]['section_count'];
						$course['class_count']= $statData[$course['pk_course']]['class_count'];
						$course['comment']= $statData[$course['pk_course']]['comment'];
						$course['discuss']= $statData[$course['pk_course']]['discuss'];
					}else{
						$course['vv']= 0;
						$course['vv_live']= 0;
						$course['vv_record']= 0;
						$course['vt']= 0;
						$course['vt_live']= 0;
						$course['vt_record']= 0;
						//$course['section_count']= 0;
						$course['class_count']= 0;
						$course['comment']= 0;
						$course['discuss']= 0;
					}
					if(isset($classData[$course['pk_course']]))
						$course['class_id'] = $classData[$course['pk_course']];
					else
						$course['class_id'] = array();
					
					/*if(isset($sectionData[$course['pk_course']]))
						$course['section_id'] = $sectionData[$course['pk_course']];
					else
						$course['section_id'] = array();
					 */
					if(isset($planDateData[$course['pk_course']]))
						$course['have_plan_date'] = $planDateData[$course['pk_course']];
					else
						$course['have_plan_date'] = array();
					
					$course['try'] = 0;
					if(!empty($planData[$course['pk_course']])){
						$videoTypeArr = array(0,-2,-1);
						$liveStatusArr = array(1,2);
						foreach($planData[$course['pk_course']] as $plan ){
							if($plan['status'] == 3 && !in_array($plan['video_public_type'],$videoTypeArr)){
								$course['try'] = 1;
								break;
							}
							if(in_array($plan['status'],$liveStatusArr) && $plan['live_public_type'] != 0){
								$course['try'] = 1;
								break;
							}
						}
					}
					
					$course['first_cate_name'] = '';
					$course['first_cate_name_display'] = '';
					$course['second_cate_name'] = '';
					$course['second_cate_name_display'] = '';
					$course['third_cate_name'] = '';
					$course['third_cate_name_display'] = '';
					if(!empty($cateData[$course['first_cate']])){
						$course['first_cate_name'] = $cateData[$course['first_cate']]['name'];
						$course['first_cate_name_display'] = $cateData[$course['first_cate']]['name_display'];
					}
					if(!empty($cateData[$course['second_cate']])){
						$course['second_cate_name'] = $cateData[$course['second_cate']]['name'];
						$course['second_cate_name_display'] = $cateData[$course['second_cate']]['name_display'];
					}
					if(!empty($cateData[$course['third_cate']])){
						$course['third_cate_name'] = $cateData[$course['third_cate']]['name'];
						$course['third_cate_name_display'] = $cateData[$course['third_cate']]['name_display'];
					}
				
					if(isset($scoreData[$course['pk_course']])){
						$course['avg_score'] = $scoreData[$course['pk_course']]['avg_score'];
					}else{
						$course['avg_score'] = 0;
					}
					
					//set grade data
					if(isset($gradeData[$course['pk_course']]) && count($gradeData[$course['pk_course']]) >0 ){
						if(!empty($gradeData[$course['pk_course']]['grade_id'])){
							$course['grade_id'] = implode(',',$gradeData[$course['pk_course']]['grade_id']);
						}else{
							$course['grade_id'] = '';
						}
						if(!empty($gradeData[$course['pk_course']]['grade_name'])){
							$course['grade_name'] = implode(' ',$gradeData[$course['pk_course']]['grade_name']);
						}else{
							$course['grade_name'] = '';
						}
					}else{
						$course['grade_id'] = '';
						$course['grade_name'] = '';
					}
					
					//set cate data
					if(isset($subjectData[$course['pk_course']]) && count($subjectData[$course['pk_course']]) >0 ){
						if(!empty($subjectData[$course['pk_course']]['subject_id'])){
							$course['subject_id'] = implode(',',$subjectData[$course['pk_course']]['subject_id']);
						}else{
							$course['subject_id'] = '';
						}
						if(!empty($subjectData[$course['pk_course']]['subject_name'])){
							$course['subject_name'] = implode(' ',$subjectData[$course['pk_course']]['subject_name']);
						}else{
							$course['subject_name'] = '';
						}
					}else{
						$course['subject_id'] = '';
						$course['subject_name'] = '';
					}
					if(!empty($course['max_user'])){
						$course['remain_user'] = $course['max_user'] - $course['user_total'];
					}else{
						$course['remain_user'] = 0;
					}
					
					//set coursetag
					if(isset($courseTagData[$course['pk_course']]) && count($courseTagData[$course['pk_course']]) >0){
						if(!empty($courseTagData[$course['pk_course']]['course_tag_id'])){
							$course['course_tag_id'] = implode(',',$courseTagData[$course['pk_course']]['course_tag_id']);
						}else{
							$course['course_tag_id'] = '';
						}	
					}else{
						$course['course_tag_id'] = '';
					}
					
					//set attr_value 
					if(isset($attrValueData[$course['pk_course']]) && count($attrValueData[$course['pk_course']]) >0 ){
						if(!empty($attrValueData[$course['pk_course']]['attr_value_id'])){
							$course['attr_value_id'] = implode(',',$attrValueData[$course['pk_course']]['attr_value_id']);
						}else{
							$course['attr_value_id'] = '';
						}
						if(!empty($attrValueData[$course['pk_course']]['attr_value_name'])){
							$course['attr_value_name'] = implode(' ',$attrValueData[$course['pk_course']]['attr_value_name']);
						}else{
							$course['attr_value_name'] = '';
						}
					}else{
						$course['attr_value_id'] = '';
						$course['attr_value_name'] = '';
					}
					//set memberset
					if(isset($memberSetData[$course['pk_course']]) && count($memberSetData[$course['pk_course']]) >0){
						if(!empty($memberSetData[$course['pk_course']]['member_set_id'])){
							$course['member_set_id'] = implode(',',$memberSetData[$course['pk_course']]['member_set_id']);
						}else{
							$course['member_set_id'] = '';
						}	
					}else{
						$course['member_set_id'] = '';
					}
					//set search field
					$course['search_field'] = $course['attr_value_name'].' '.$course['grade_name'].' '.$course['subject_name'].' '.$course['title'];//.' '.$sectionDescData[$course['pk_course']];
					//set price_promote
					if(NULL === $course['price_promote'])
						$course['price_promote'] = 0;
					if(NULL === $course['promote_status'])
						$course['promote_status'] = 0;
					foreach($course as $k=>$v){
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
