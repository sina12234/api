<?php
class platform_setting{


	public function setResult($data='',$code=0,$msg='success'){
		$ret = new stdclass;
		$ret->code = $code;
		$ret->data = $data;
		$ret->msg  = $msg;
		return $ret;
	}

	public function pageplatformBlockList($inPath){
		$ret = platform_db::platformBlockList();
		return !empty($ret->items) ? $ret : '';
	}
	public function pagegetBlockByInfo($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$data=!empty($params->pk_block) ? $params->pk_block : '';
		$ret = platform_db::getBlockByInfo($data);
		return !empty($ret) ? $ret : '';
	}
	public function pagegetBlockInfo($inPath){
		$params=SJson::decode(utility_net::getPostData());
        $block = array();
		$blockInfo = platform_db::platformBlockList();
        $id_str = '';
		if(!empty($blockInfo->items)){
			foreach($blockInfo->items as $m=>$n){
				if(isset($n['is_custom'])&&$n['is_custom']==1){
					$result = platform_db::getBlockContent($n['pk_block']);
                    $course = array();
					$block[$n['pk_block']] = $n;
					foreach($result->items as $k=>$v){
						$course[$m]['course_arr'][] = !empty($v['fk_course']) ? $v['fk_course'] : '';
						$block[$n['pk_block']]['sort'][$v['fk_course']]['course_id']= !empty($v['fk_course']) ? $v['fk_course'] : '';
						$block[$n['pk_block']]['sort'][$v['fk_course']]['sort_id']= !empty($v['sort']) ? $v['sort'] : '';
					}
					if(!empty($course[$m]['course_arr'])){
						$id_str = implode(",",$course[$m]['course_arr']);
						$block[$n['pk_block']]['course_arr']=$id_str;
					}    
				}else{
					$block[$n['pk_block']] = $n;
				}
			}
		}
		return $block;
	}
	public function pageupdatesetting($inPath){
		$block_id = !empty($inPath[3]) ? $inPath[3] : '';
		$params=SJson::decode(utility_net::getPostData());
		if(empty($block_id)){
			return $this->setResult('',-1,'block_id is empty');
		}
		$data = array();
		$data['name']=!empty($params->name) ? $params->name : '';
		if(empty($data['name'])){
			return $this->setResult('',-1,'name is empty');
		}		
        $arr = array();
		$data['des']=!empty($params->des) ? $params->des : '';
		$data['is_custom']=!empty($params->is_custom) ? $params->is_custom : 0;
		$data['show_type']=!empty($params->show_type) ? $params->show_type : 0;
		$data['order_str']=!empty($params->order_str) ? $params->order_str : 3;
		$data['total_count']=!empty($params->total_count) ? $params->total_count : 0;
		$arr['fk_course']=!empty($params->pk_course) ? $params->pk_course : 0;
        if($block_id == 13){
            $data['show_type'] = 2;
        }elseif($block_id == 14){
            $data['show_type'] = 3;
        }else{
            $data['show_type'] = 1;
        }
		$data['query_str'] = '';
		if(!empty($params->first_cate)){
			$data['query_str'] .= "first_cate:".$params->first_cate;
		}
		if(!empty($params->second_cate)){
			$data['query_str'] .= ",second_cate:".$params->second_cate;
		}
		if(!empty($params->third_cate)){
			$data['query_str'] .= ",third_cate:".$params->third_cate;
		}
		if(!empty($params->attr_value_id)){
			$data['query_str'] .= ",attr_value_id:".str_replace(',','|',$params->attr_value_id);
		}
		if(!empty($data['order_str'])&&$data['order_str']==1){
			$data['order_str'] = "user_total:desc";
		}elseif(!empty($data['order_str'])&&$data['order_str']==2){
			$data['order_str'] = "remain_user:desc";
		}elseif(!empty($data['order_str'])&&$data['order_str']==3){
			$data['order_str'] = "vv:desc";
		}
		$ret = platform_db::updatesetting($block_id,$data);
		if( $params->is_custom>0 ){
			$info= array();
			$b_content = array();
			$info['fk_block'] = $block_id;
			$num = $data['total_count'] - count($arr['fk_course']);
			$condi['num']= $num;
			$getblock = platform_db::getBlockContent($block_id);
            if($num == 0){
                $course_merge = $params->pk_course;
            }elseif($num > 0){
                if(!empty($getblock->items)){
                    foreach($getblock->items as $k=>$v){
                        $recomend[] = !empty($v['fk_course']) ? $v['fk_course'] : '';
                    }	
                }
                if(!empty($recomend)){
                        $recomend_str = implode(",",$recomend);
                }else{
                        $recomend_str=0;
                }
                $condi['pk_course']= $recomend_str;
                $course_arr = course_db::getBlockCourseOrderByInfo($condi);
                if(!empty($course_arr->items)){
                    foreach($course_arr->items as $k=>$v){
                        $get_course['pk_course'][] = !empty($v['pk_course']) ? $v['pk_course'] : '';
                    }
                }
                //$course_merge = array_merge($params->pk_course,$get_course['pk_course']);  
                $course_merge = $params->pk_course;  
            }
			foreach($course_merge as $k=>$v){
                $b_content[$k]['fk_course']=$v;
                $b_content[$k]['sort']= !empty($params->sort[$k]) ? $params->sort[$k] : ($k+1);
                $b_content[$k]['fk_block']= $block_id;
            }
			if(!empty($getblock->items)){
				$del_res = platform_db::delBlockContent($block_id);
			}
			if(!empty($b_content)){
				foreach($b_content as $k=>$v){
					$recode = platform_db::addBlockContent($v);
				}
			}
			if($recode){
				return $this->setResult($recode);
			}else{
				return $this->setResult('',-2,'update is faild');
			}
		}
		if($ret !== false){
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'update is faild');
		}
	}
	public function pagemgrCoursePreview($inPath){
		$block_id = !empty($inPath[3]) ? $inPath[3] : '';
		$params=SJson::decode(utility_net::getPostData());
		$data = array();
        $arr = array();
		$data['name']=!empty($params->name) ? $params->name : '';
		$data['des']=!empty($params->des) ? $params->des : '';
		$data['is_custom']=!empty($params->is_custom) ? $params->is_custom : 0;
		$data['show_type']=!empty($params->show_type) ? $params->show_type : 0;
		$data['order_str']=!empty($params->order_str) ? $params->order_str : 3;
		$data['total_count']=!empty($params->total_count) ? $params->total_count : 0;
		$arr['fk_course']=!empty($params->fk_course) ? $params->fk_course : 0;
		$total_count=!empty($params->total_count) ? $params->total_count : 0;
		
		if(!empty($params->grade) && empty($params->subject)){
			$data['query_str']= "grade_id:".$params->grade;
		}elseif(!empty($params->grade) &&!empty($params->subject) ){
			$data['query_str']= "grade_id:".$params->grade.",subject_id:".$params->subject;
		}elseif(empty($params->grade) && empty($params->subject)){
			$data['query_str']= "";
		}
		if(!empty($data['order_str'])&&$data['order_str']==1){
			$data['order_str'] = "user_total:desc";
		}elseif(!empty($data['order_str'])&&$data['order_str']==2){
			$data['order_str'] = "remain_user:desc";
		}elseif(!empty($data['order_str'])&&$data['order_str']==3){
			$data['order_str'] = "vv:desc";
		}
        $condi = array();
        $condi['fk_grade'] = !empty($params->grade) ? $params->grade : '';
        $condi['subject']= !empty($params->subject) ? $params->subject : '';
		$condi['num']= $data['total_count'];
		$condi['order_str']= $data['order_str'];
        $c_arr=array();
		if(empty($condi['fk_grade']) && empty($condi['subject'])){
			$c_arr = array();
			if($condi['order_str']=='vv:desc'){
				$ids = array();
				$course_arr = stat_db::getMgrCourseVvByInfo($total_count);
				
				$tmp_course = array();
				foreach($course_arr->items as $k=>$v){
					$ids[]=$v['fk_course'];
					$tmp_course[$v['fk_course']]=$v;
				}
				$course_str = implode(",",$ids);
				$course_info = course_db::getMgrRecommendCourse($course_str);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course'] = $v['pk_course'];
					$c_arr[$k]['title'] = $v['title'];
					$c_arr[$k]['thumb_small'] = $v['thumb_small'];
					$c_arr[$k]['vv'] = !empty($tmp_course[$v['pk_course']]['vv']) ? $tmp_course[$v['pk_course']]['vv'] : '';
				}
				usort(
						$c_arr,
						function ($a, $b) {
							return ($a['vv'] >= $b['vv']) ? -1 : 1;
						}
					);
			}else{
                
				$course_info = course_db::getDefaultCourseRecomend($condi);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course']=$v['pk_course'];
					$c_arr[$k]['title']=$v['title'];
					$c_arr[$k]['thumb_small']=$v['thumb_small'];
				}	
			}
			return $c_arr;
				
		}elseif(!empty($condi['fk_grade']) && empty($condi['subject'])){
			$tag_info = tag_db::getMgrClassTagCourse($condi);
			$course_tag= array();
            $fk_course_str[]=array();
			foreach($tag_info->items as $k=>$v){
                $fk_course_arr[]=$v['fk_course'];
				$course_tag[$v['fk_course']]['pk_course']= $v['fk_course'];
				$course_tag[$v['fk_course']]['fk_tag']= $v['fk_tag'];
			}
            
            $fk_course_str = implode(",",$fk_course_arr);
            if($condi['order_str']=='vv:desc'){
				$ids = array();
				$course_arr = stat_db::getMgrCourseVvByInfo($total_count);
				
				$tmp_course = array();
				foreach($course_arr->items as $k=>$v){
					$ids[]=$v['fk_course'];
					$tmp_course[$v['fk_course']]=$v;
				}
				$course_str = implode(",",$ids);
				$course_info = course_db::getMgrRecommendCourse($course_str);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course'] = $v['pk_course'];
					$c_arr[$k]['title'] = $v['title'];
					$c_arr[$k]['thumb_small'] = $v['thumb_small'];
					$c_arr[$k]['vv'] = !empty($tmp_course[$v['pk_course']]['vv']) ? $tmp_course[$v['pk_course']]['vv'] : '';
				}
				usort(
						$c_arr,
						function ($a, $b) {
							return ($a['vv'] >= $b['vv']) ? -1 : 1;
						}
					);
			}else{
                $condi['pk_course'] = $fk_course_str;
                
				$course_info = course_db::getDefaultCourseRecomend($condi);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course']=$v['pk_course'];
					$c_arr[$k]['title']=$v['title'];
					$c_arr[$k]['thumb_small']=$v['thumb_small'];
				}	
			}
            return $c_arr;
		}elseif(!empty($condi['fk_grade']) && !empty($condi['subject'])){
			$tag_info = tag_db::getMgrClassTagCourse($condi);
			$fk_course_str[]=array();
			foreach($tag_info->items as $k=>$v){
                $fk_course_arr[]=$v['fk_course'];
				$course_tag[$v['fk_course']]['pk_course']= $v['fk_course'];
				$course_tag[$v['fk_course']]['fk_tag']= $v['fk_tag'];
			}
            $fk_course_arr = array_unique($fk_course_arr);
            $fk_course_str = implode(",",$fk_course_arr);
            if($condi['order_str']=='vv:desc'){
				$ids = array();
				$course_arr = stat_db::getMgrCourseVvByInfo($total_count);
				
				$tmp_course = array();
				foreach($course_arr->items as $k=>$v){
					$ids[]=$v['fk_course'];
					$tmp_course[$v['fk_course']]=$v;
				}
				$course_str = implode(",",$ids);
				$course_info = course_db::getMgrRecommendCourse($course_str);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course'] = $v['pk_course'];
					$c_arr[$k]['title'] = $v['title'];
					$c_arr[$k]['thumb_small'] = $v['thumb_small'];
					$c_arr[$k]['vv'] = !empty($tmp_course[$v['pk_course']]['vv']) ? $tmp_course[$v['pk_course']]['vv'] : '';
				}
				usort(
						$c_arr,
						function ($a, $b) {
							return ($a['vv'] >= $b['vv']) ? -1 : 1;
						}
					);
			}else{
                $condi['pk_course'] = $fk_course_str;
				$course_info = course_db::getDefaultCourseRecomend($condi);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course']=$v['pk_course'];
					$c_arr[$k]['title']=$v['title'];
					$c_arr[$k]['thumb_small']=$v['thumb_small'];
				}	
			}
            
			return $c_arr;
		}elseif(empty($condi['fk_grade']) && !empty($condi['subject'])){
			$tag_info = tag_db::getMgrClassTagCourse($condi);
			$fk_course_str[]=array();
			foreach($tag_info->items as $k=>$v){
                $fk_course_arr[]=$v['fk_course'];
				$course_tag[$v['fk_course']]['pk_course']= $v['fk_course'];
				$course_tag[$v['fk_course']]['fk_tag']= $v['fk_tag'];
			}
            $fk_course_arr = array_unique($fk_course_arr);
            $fk_course_str = implode(",",$fk_course_arr);
            if($condi['order_str']=='vv:desc'){
				$ids = array();
				$course_arr = stat_db::getMgrCourseVvByInfo($total_count);
				
				$tmp_course = array();
				foreach($course_arr->items as $k=>$v){
					$ids[]=$v['fk_course'];
					$tmp_course[$v['fk_course']]=$v;
				}
				$course_str = implode(",",$ids);
				$course_info = course_db::getMgrRecommendCourse($course_str);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course'] = $v['pk_course'];
					$c_arr[$k]['title'] = $v['title'];
					$c_arr[$k]['thumb_small'] = $v['thumb_small'];
					$c_arr[$k]['vv'] = !empty($tmp_course[$v['pk_course']]['vv']) ? $tmp_course[$v['pk_course']]['vv'] : '';
				}
				usort(
						$c_arr,
						function ($a, $b) {
							return ($a['vv'] >= $b['vv']) ? -1 : 1;
						}
					);
			}else{
                $condi['pk_course'] = $fk_course_str;//echo "<pre>";print_r($condi);die;
				$course_info = course_db::getDefaultCourseRecomend($condi);
				foreach($course_info->items as $k=>$v){
					$c_arr[$k]['pk_course']=$v['pk_course'];
					$c_arr[$k]['title']=$v['title'];
					$c_arr[$k]['thumb_small']=$v['thumb_small'];
				}	
			}
           
			return $c_arr;
		}
	}
	public function pagegetBlockContent($inPath){
		$block_id = !empty($inPath[3]) ? $inPath[3] : '';
		if(!empty($block_id)){
			$result = platform_db::getBlockContent($block_id);
			return $result;
		}
	}
	public function pagegetMgrRecommendCourse($inPath){
		$course_id = !empty($inPath[3]) ? $inPath[3] : '';
		if(!empty($course_id)){
			$result = course_db::getMgrRecommendCourse($course_id);
			return $result;
		}
	}
}
