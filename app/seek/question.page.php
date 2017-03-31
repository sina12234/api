<?php
class seek_question{
	var $attrs = array(
		'question_id' => 'pk_question',
		'answer_id'   => 'answer_id',
		'fav_owner_id' => 'fav_owner_id',
		'fav_user_id' => 'fav_user_id',
		'owner_id' => 'fk_user_org',
		'user_id' => 'fk_user',
		'type' => 'type',
		'source' => 'source',
		'subject_id' => 'subject_id',
		'grade_id' => 'grade_id',
		'keypoint_id' => 'keypoint_id',
		'subject' => 'subject',
		'grade' => 'grade',
		'keypoint' => 'keypoint',
		'answer' => 'answer',
		'descript' => 'descript',
		'desc_img' => 'desc_img',
		'analysis' => 'analysis',
		'result' => 'result',
		'mode'=>'mode',
		'status'=>'status',
		'create_time'=>'create_time',
		'last_updated'=>'last_updated',
		'task_use_num'=>'task_use_num',
		'task_correct_num'=>'task_correct_num',
		'plan_use_num'=>'plan_use_num',
		'plan_correct_num'=>'plan_correct_num',
		'search_field'=>'search_field',
	);
	var $timesArr = array('create_time'=>0,
						  'last_updated'=>0
					     );
	public function pageList($inPath){
		$timesArr = $this->timesArr;
		$tmpAttrs = $this->attrs;
		//获取列表字段
		$params = SJson::decode(utility_net::getPostData());

		if(empty($params->f)){
			$field = array(
				'quetion_id',
				'descript',
				'desc_img',
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
		if(empty($params->q)){
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
		$address = $conf->seek_question_ip;
		$port = (int)($conf->seek_question_port);
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
			if(isset($queryArr['question_id'])){
				$qArr = explode(',', $queryArr['question_id']);
				$cl->setFilter($tmpAttrs['question_id'], $qArr);	
			}
			if(isset($queryArr['owner_id'])){
				$qArr = explode(',', $queryArr['owner_id']);
				$cl->setFilter($tmpAttrs['owner_id'], $qArr);	
			}
			if(isset($queryArr['user_id'])){
				$qArr = explode(',', $queryArr['user_id']);
				$cl->setFilter($tmpAttrs['user_id'], $qArr);	
			}
			if(isset($queryArr['fav_user_id'])){
				$qArr = explode(',', $queryArr['fav_user_id']);
				$cl->setFilter($tmpAttrs['fav_user_id'], $qArr);	
			}
			if(isset($queryArr['fav_owner_id'])){
				$qArr = explode(',', $queryArr['fav_owner_id']);
				$cl->setFilter($tmpAttrs['fav_owner_id'], $qArr);	
			}
			if(isset($queryArr['type'])){
				$qArr = explode(',', $queryArr['type']);
				$cl->setFilter($tmpAttrs['type'], $qArr);	
			}
			if(isset($queryArr['source'])){
				$qArr = explode(',', $queryArr['source']);
				$cl->setFilter($tmpAttrs['source'], $qArr);	
			}
			if(isset($queryArr['mode'])){
				$qArr = explode(',', $queryArr['mode']);
				$cl->setFilter($tmpAttrs['mode'], $qArr);	
			}
			if(isset($queryArr['status'])){
				$qArr = explode(',', $queryArr['status']);
				$cl->setFilter($tmpAttrs['status'], $qArr);	
			}
			if(isset($queryArr['subject_id'])){
				$qArr = explode(',', $queryArr['subject_id']);
				$cl->setFilter($tmpAttrs['subject_id'], $qArr);	
			}
			if(isset($queryArr['grade_id'])){
				$qArr = explode(',', $queryArr['grade_id']);
				$cl->setFilter($tmpAttrs['grade_id'], $qArr);	
			}
			if(isset($queryArr['keypoint_id'])){
				$qArr = explode(',', $queryArr['keypoint_id']);
				$cl->setFilter($tmpAttrs['keypoint_id'], $qArr);	
			}
			if(isset($queryArr['task_use_num'])){
				$qArr = explode(',', $queryArr['task_use_num']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['task_use_num'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'task_use_num syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['task_use_num'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'task_use_num syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['task_correct_num'])){
				$qArr = explode(',', $queryArr['task_correct_num']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['task_correct_num'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'task_correct_num syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['task_correct_num'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'task_correct_num syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['plan_use_num'])){
				$qArr = explode(',', $queryArr['plan_use_num']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['plan_use_num'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'plan_use_num syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['plan_use_num'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'plan_use_num syntax error';
					return $ret;
				}
			}
			if(isset($queryArr['plan_correct_num'])){
				$qArr = explode(',', $queryArr['plan_correct_num']);
				if(count($qArr)==1){
					$cl->setFilter($tmpAttrs['plan_correct_num'], $qArr); 
				}else if(count($qArr) ==2 ){
					$tmpBegin = (int)($qArr[0]);
					$tmpEnd = (int)($qArr[1]);
					if(0 === $tmpBegin || 0 === $tmpEnd){
						$ret['error'] = 1;
						$ret['error_desc'] = 'plan_correct_num syntax error';
						return $ret;
					}
					$cl->setFilterRange($tmpAttrs['plan_correct_num'], $tmpBegin, $tmpEnd);	
				}else{
					$ret['error'] = 1;
					$ret['error_desc'] = 'plan_correct_num syntax error';
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
			if(isset($queryArr['analysis'])){
				$queriesStr .=" @analysis ".$queryArr['analysis'];
			}
			if(isset($queryArr['result'])){
				$queriesStr .=" @result ".$queryArr['result'];
			}
			if(isset($queryArr['descript'])){
				$queriesStr .=" @descript ".$queryArr['descript'];
			}
			if(isset($queryArr['search_field'])){
				$queriesStr .=" @search_field ".$queryArr['search_field'];
			}
		}
		//设置page
		$beginOff = ($page-1)*$pageLength;
		$cl->setLimits((int)($beginOff), (int)($pageLength));
		//设置string query
		$res = $cl->Query($queriesStr,"question");
		//get matches
		$data = array();
		if(isset($res['matches']) && count($res['matches']) > 0){
			//check that if need to select from db 
			$answers = $keypoints = array();
			$grades = $subjects = array();
			$tmpIdArr = array();
			if(in_array('answer', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['answer_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $answerId){
							$tmpIdArr[$answerId] = 0;
						}
					}
				}
				$answerIdStr = implode(',', array_keys($tmpIdArr));
				$dbExam = new exam_db;
				$answerList = $dbExam->getAnswersByAidStr($answerIdStr);
                if (!empty($answerList->items)) {
                    foreach($answerList->items as $val){
                        $tmpCla['answer_id'] = $val['pk_answer'];
                        $tmpCla['desc'] = $val['desc'];
                        $tmpCla['desc_img'] = $val['desc_img'];
                        $tmpCla['correct'] = $val['correct'];
                        $answers[$val['fk_question']][] = $tmpCla;
                    }
                }
			}
			
			
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
				$groupInfo = $dbTag->getGroupInfo('年级');
				$tagList = $dbTag->getQuestionTagListByTids($gradeIdStr,$groupInfo['pk_group']);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpGrade['grade_id'] = $val['pk_tag'];
						$tmpGrade['grade_name'] = $val['name'];
						$grades[$val['fk_question']][] = $tmpGrade;
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
				$groupInfo = $dbTag->getGroupInfo('科目');
				$tagList = $dbTag->getQuestionTagListByTids($subjectIdStr,$groupInfo['pk_group']);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpSubject['subject_id'] = $val['pk_tag'];
						$tmpSubject['subject_name'] = $val['name'];
						$subjects[$val['fk_question']][] = $tmpSubject;
					}
				}
			}
			
			$tmpIdArr = array();
			if(in_array('keypoint', $field)){
				foreach($res['matches'] as $id=>$val){
					$tmpArr = $val['attrs']['keypoint_id'];
					if( count($tmpArr) > 0 ){
						foreach($tmpArr as $keypointId){
							$tmpIdArr[$keypointId] = 0;
						}
					}
				}
				$keypointIdStr = implode(',', array_keys($tmpIdArr));
				$dbTag = new tag_db;
				$groupInfo = $dbTag->getGroupInfo('知识点');
				$tagList = $dbTag->getQuestionTagListByTids($keypointIdStr,$groupInfo['pk_group']);
				if(!empty($tagList->items)){
					foreach($tagList->items as $val){
						$tmpKeypoint['keypoint_id'] = $val['pk_tag'];
						$tmpKeypoint['keypoint_name'] = $val['name'];
						$keypoints[$val['fk_question']][] = $tmpKeypoint;
					}
				}
			}
			
			$i = 0;
			foreach($res['matches'] as $id=>$val){
				foreach($field as $k){
					if(isset($timesArr[$k])){
						$data[$i][$k] = date('Y-m-d H:i:s', $val['attrs'][$tmpAttrs[$k]]);	
					}elseif($k === 'answer'){
						if(isset($answers[$val['attrs']['pk_question']]))
							$data[$i][$k] = $answers[$val['attrs']['pk_question']];
						else 
							$data[$i][$k] = array();
					}elseif($k==='keypoint'){
						if(isset($keypoints[$val['attrs']['pk_question']]))
							$data[$i][$k] = $keypoints[$val['attrs']['pk_question']];
						else
							$data[$i][$k] = array();
					}elseif($k==='grade'){
						if(isset($grades[$val['attrs']['pk_question']]))
							$data[$i][$k] = $grades[$val['attrs']['pk_question']];
						else
							$data[$i][$k] = array();
					}elseif($k==='subject'){
						if(isset($subjects[$val['attrs']['pk_question']]))
							$data[$i][$k] = $subjects[$val['attrs']['pk_question']];
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
		<sphinx:field name="descript"/>
		<sphinx:field name="search_field"/>
		<sphinx:attr name="answer_id" type="multi"/>
		<sphinx:attr name="pk_question" type="int"/>
		<sphinx:attr name="fk_user" type="int"/>
		<sphinx:attr name="fk_user_org" type="int"/>
		<sphinx:attr name="fav_owner_id" type="multi"/>
		<sphinx:attr name="fav_user_id" type="multi"/>
		<sphinx:attr name="type" type="int"/>
		<sphinx:attr name="source" type="int"/>
		<sphinx:attr name="subject_id" type="multi"/>
		<sphinx:attr name="grade_id" type="multi"/>
		<sphinx:attr name="keypoint_id" type="multi"/>
		<sphinx:attr name="subject_name" type="string"/>
		<sphinx:attr name="grade_name" type="string"/>
		<sphinx:attr name="keypoint_name" type="string"/>
		<sphinx:attr name="mode" type="int"/>
		<sphinx:attr name="descript" type="string"/>
		<sphinx:attr name="desc_img" type="string"/>
		<sphinx:attr name="result" type="string"/>
		<sphinx:attr name="analysis" type="string"/>
		<sphinx:attr name="status" type="int"/>
		<sphinx:attr name="task_use_num" type="int"/>
		<sphinx:attr name="task_correct_num" type="int"/>
		<sphinx:attr name="plan_use_num" type="int"/>
		<sphinx:attr name="plan_correct_num" type="int"/>
		<sphinx:attr name="create_time" type="timestamp"/>
		<sphinx:attr name="last_updated" type="timestamp"/>
		<sphinx:attr name="search_field" type="string"/>
		</sphinx:schema>';
		
		$dbTag  = new tag_db;
		$dbExam = new exam_db;
		$conf =  SConfig::getConfig(ROOT_CONFIG."/const.conf","seek");
		$pageLength = $conf->seek_question_length;
		$startCourseId = 1;
		$questionList = $dbExam->listQuestions(1,$pageLength);
		$totalPage = $questionList->totalPage;
		$timesArr = $this->timesArr;
		for($page=1;$page<=$totalPage;$page++){
			$questionList = $dbExam->listQuestions($page,$pageLength);
			$questionIdArr = array();
			foreach($questionList->items as $k=>$v){
				$questionIdArr[] = $v['pk_question'];
			}
			$questionIdStr = implode(',', $questionIdArr);
 
			$answerList = $dbExam->listQuestionAnswerByQids( $questionIdStr );
			$answerData = array();
			if(isset($answerList->items) && count($answerList->items)>0){
				foreach($answerList->items as $answer){
					$answerData[$answer['fk_question']]['answer_id'][] = $answer['pk_answer'];
				}
			}
 
 
			$favList = $dbExam->listFavQuestionByQids( $questionIdStr );
			$favData = array();
			if(isset($favList->items) && count($favList->items)>0){
				foreach($favList->items as $fav){
					$favData[$fav['fk_question']]['fav_user_id'][] = $fav['fk_user'];
					$favData[$fav['fk_question']]['fav_owner_id'][] = $fav['fk_user_owner'];
				}
			}
			
			$groupconf =  SConfig::getConfig(ROOT_CONFIG."/group.conf","group");
			$keypointGroup = $dbTag->getGroupInfo('知识点');
			if(!empty($keypointGroup)){
				$keyGroupId = $keypointGroup['pk_group'];
			}else{
				$keyGroupId = 0;
			}
			$tagQuestionList = $dbTag->getTagQuestionByQids($questionIdStr);
			$gradeData = array();
			$subjectData = array();
			$keypointData = array();
			if(isset($tagQuestionList->items) && count($tagQuestionList->items)>0){	
				foreach($tagQuestionList->items as $tag){
					if( $tag['fk_group'] == $groupconf->grade ){
						$gradeData[$tag['fk_question']]['grade_id'][] = $tag['fk_tag'];
						$gradeData[$tag['fk_question']]['grade_name'][] = $tag['name'];	
					}
					if( $tag['fk_group'] == $groupconf->subject ){
						$subjectData[$tag['fk_question']]['subject_id'][] = $tag['fk_tag'];
						$subjectData[$tag['fk_question']]['subject_name'][] = $tag['name'];	
					}
					if(!empty($keyGroupId)){
						if( $tag['fk_group'] == $keyGroupId ){
							$keypointData[$tag['fk_question']]['keypoint_id'][] = $tag['fk_tag'];
							$keypointData[$tag['fk_question']]['keypoint_name'][] = $tag['name'];	
						}
					}
				}
			}
			
			foreach($questionList->items as $question ){
				$startCousrseId = $question['pk_question'];
				echo '<sphinx:document id="'.$question['pk_question'].'">'."\n";
				if($question['task_use_num'] == NULL){
					$question['task_use_num'] = 0;
				}
				if($question['task_correct_num'] == NULL){
					$question['task_correct_num'] = 0;
				}
				if($question['plan_use_num'] == NULL){
					$question['plan_use_num'] = 0;
				}
				if($question['plan_correct_num'] == NULL){
					$question['plan_correct_num'] = 0;
				}
				
				//get answerid data
				$question['answer_id'] = '';
				if(isset($answerData[$question['pk_question']]) && count($answerData[$question['pk_question']]) >0 ){
					if(!empty($answerData[$question['pk_question']]['answer_id'])){
						$question['answer_id'] =  implode(',',$answerData[$question['pk_question']]['answer_id']);
					}
				}

				//get fav data
				$question['fav_user_id'] = '';
				$question['fav_owner_id'] = '';
				if(isset($favData[$question['pk_question']]) && count($favData[$question['pk_question']]) >0 ){
					if(!empty($favData[$question['pk_question']]['fav_user_id'])){
						$tempFavUserId = $favData[$question['pk_question']]['fav_user_id'];
						if(!empty($question['fk_user']) && !in_array($question['fk_user'],$tempFavUserId)){
							array_push($tempFavUserId,$question['fk_user']);
						}
						$question['fav_user_id'] = implode(',',$tempFavUserId);
					}
					if(!empty($favData[$question['pk_question']]['fav_owner_id'])){
						$tempFavOwnerId = $favData[$question['pk_question']]['fav_owner_id'];
						if(!empty($question['fk_user_org']) && !in_array($question['fk_user_org'],$tempFavOwnerId)){
							array_push($tempFavOwnerId,$question['fk_user_org']);
						}
						$question['fav_owner_id'] = implode(',',$tempFavOwnerId);
					}
				}else{
					if(!empty($question['fk_user'])){
						$question['fav_user_id'] = array($question['fk_user']);
					}
					if(!empty($question['fk_user_org'])){
						$question['fav_owner_id'] = array($question['fk_user_org']);
					}
				}
				
				//set grade data
				if(isset($gradeData[$question['pk_question']]) && count($gradeData[$question['pk_question']]) >0 ){
					if(!empty($gradeData[$question['pk_question']]['grade_id'])){
						$question['grade_id'] = implode(',',$gradeData[$question['pk_question']]['grade_id']);
					}else{
						$question['grade_id'] = '';
					}
					if(!empty($gradeData[$question['pk_question']]['grade_name'])){
						$question['grade_name'] = implode(' ',$gradeData[$question['pk_question']]['grade_name']);
					}else{
						$question['grade_name'] = '';
					}
				}else{
					$question['grade_id'] = '';
					$question['grade_name'] = '';
				}
				
				//set cate data
				if(isset($subjectData[$question['pk_question']]) && count($subjectData[$question['pk_question']]) >0 ){
					if(!empty($subjectData[$question['pk_question']]['subject_id'])){
						$question['subject_id'] = implode(',',$subjectData[$question['pk_question']]['subject_id']);
					}else{
						$question['subject_id'] = '';
					}
					if(!empty($subjectData[$question['pk_question']]['subject_name'])){
						$question['subject_name'] = implode(' ',$subjectData[$question['pk_question']]['subject_name']);
					}else{
						$question['subject_name'] = '';
					}
				}else{
					$question['subject_id'] = '';
					$question['subject_name'] = '';
				}
				
				//get keypoint data
				if(isset($keypointData[$question['pk_question']]) && count($keypointData[$question['pk_question']]) >0 ){
					if(!empty($keypointData[$question['pk_question']]['keypoint_id'])){
						$question['keypoint_id'] = implode(',',$keypointData[$question['pk_question']]['keypoint_id']);
					}else{
						$question['keypoint_id'] = '';
					}
					if(!empty($keypointData[$question['pk_question']]['keypoint_name'])){
						$question['keypoint_name'] = implode(' ',$keypointData[$question['pk_question']]['keypoint_name']);
					}else{
						$question['keypoint_name'] = '';
					}
				}else{
					$question['keypoint_id'] = '';
					$question['keypoint_name'] = '';
				}
				
				
				//set search field
				$question['search_field'] = $question['grade_name'].' '.$question['subject_name'].' '.$question['keypoint_name'].' '.$question['descript'];
				foreach($question as $k=>$v){
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
		echo '</sphinx:docset>';
	}
}
