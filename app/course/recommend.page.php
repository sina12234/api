<?php
/*
 *App课程推荐
 *@autor lijingjuan 
 */
class course_recommend{
	
	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        );
	}
	
	/*
	 *获取分类列表
	 */
	public function pageList($inPath){
		$params = SJson::decode(utility_net::getPostData());
		
		$page = !empty($params->page)?$params->page:0;
		$length = !empty($params->length)?$params->length:0;
		$name = !empty($params->name)?$params->name:'';
		$courseDb = new course_db;
		$recommendList = $courseDb->getMgrAppRecommendList($page,$length,$name);
		if(!empty($recommendList->items)){
			return $this->setResult($recommendList);
		}else{
			return $this->setResult('',-2,'get failed data');
		}
	}
	
	/*
	 *添加推荐课程
	 */
	public function pageAddRecommend($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->cate_id)){
			return $this->setResult('',-1,'分类id不能为空');
		}
		$cateId    = $params->cate_id;
		$courseIds = !empty($params->course_ids)?$params->course_ids:'';
		$courseDb = new course_db;
		if(!empty($courseIds)){
			$courseIdArr = explode(',',$courseIds);
			$courseIdArr = array_unique($courseIdArr);
			$courseRet = $courseDb->getCourseByCids($courseIdArr);
			if(!empty($courseRet->items)){
				$existCourse = array();
				foreach($courseRet->items as $course){
					$existCourse[] = $course['course_id'];
				}
				$errorCourse = array_diff($courseIdArr,$existCourse);
				$errorCourseIds = implode(',',$errorCourse);
				if(!empty($errorCourse)){
					return $this->setResult('',-20,"推荐的课程id($errorCourseIds)不存在");
				}
			}else{
				return $this->setResult('',-21,'推荐课程id不存在！');
			}
		}else{
			$courseIdArr = array();
		}
		$recommRet = $courseDb->getAppRecommendByCateId($cateId);
		$data = array();
		if(!empty($recommRet)){
			$data['fk_course'] = implode(',',$courseIdArr);
			$addRet = $courseDb->updateRecommend($cateId,$data);
		}else{
			$data['create_time'] = date('Y-m-d H:i:s',time());
			$data['fk_cate'] = $cateId;
			$data['fk_course'] = implode(',',$courseIdArr);
			$addRet = $courseDb->addRecommend($data);
		}		
		if($addRet !== false){
			return $this->setResult($addRet);
		}else{
        	return $this->setResult('',-4,'添加失败');
		}
	}

	public function pageRecommendByCateId(){
		$params = SJson::decode(utility_net::getPostData(),true);

		if(empty($params['cateId'])){
			return $this->setResult('',-1,'分类id不能为空');
		}
		
		$cateId = $params['cateId'];
		
		$courseDb = new course_db;
		$recommendList = $courseDb->getRecommendList($cateId);
		
		if(!empty($recommendList->items)){
			return $this->setResult($recommendList);
		}else{
			return $this->setResult('',-2,'get failed data');
		}
	}
	
	
	
}
