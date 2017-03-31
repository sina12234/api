<?php
/*
 *课程分类
 *@autor lijingjuan 
 */
class course_cate{
	
	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        );
	}
	
	/*
	 * 获取分类列表
	 * 分类一般不会改动，缓存1小时
	 */
	public function pageList($inPath){
		utility_cache::pageCache(3600);
		$courseDb = new course_db;
		$cateList = $courseDb->getCateList();
		if(!empty($cateList->items)){
			return $this->setResult($cateList);
		}else{
			return $this->setResult('',-2,'get failed data');
		}
	}
	
	/*
	 *添加分类
	 */
	public function pageAddCate($inPath){
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			return $this->setResult('',-1,'名称不能为空');
		}
		if(empty($params->name_display)){
			return $this->setResult('',-1,'展示名称不能为空');
		}
		$data = array();
		$data['name'] = $params->name;
		$data['name_display'] = $params->name_display;
		$data['descript'] = $params->descript;
		$data['status'] = $params->status;
		$data['create_time'] = date('Y-m-d H:i:s',time());
		$courseDb = new course_db;
		if(!empty($params->before_id)){
			if($params->before_id == $params->parent_id){
				$parentCate = $courseDb->getCateById($params->parent_id);
				$data['level'] = $parentCate['level']+1;
				$data['lft'] = $parentCate['lft'] + 1;
				$data['rgt'] = $parentCate['lft'] + 2;
				$setLftData = array("`lft`=`lft`+2");
				$courseDb->setLftCate($parentCate['lft'],$setLftData);
				$setRgtData = array("`rgt`=`rgt`+2");
				$courseDb->setRgtCate($parentCate['lft'],$setRgtData);
			}else{
				$beforeCate = $courseDb->getCateById($params->before_id);
				$data['level'] = $beforeCate['level'];
				$data['lft'] = $beforeCate['rgt'] + 1;
				$data['rgt'] = $beforeCate['rgt'] + 2;
				$setLftData = array("`lft`=`lft`+2");
				$courseDb->setLftCate($beforeCate['rgt'],$setLftData);
				$setRgtData = array("`rgt`=`rgt`+2");
				$courseDb->setRgtCate($beforeCate['rgt'],$setRgtData);
			}
		}else{
			if(!empty($params->parent_id)){
				$cateInfo = $courseDb->getCateById($params->parent_id);
				$data['level'] = $cateInfo['level']+1;
				$bigLftCate = $courseDb->getBigLftCateByLevel($data['level'],$cateInfo['lft'],$cateInfo['rgt']);
				if(!empty($bigLftCate)){
					$data['lft'] = $bigLftCate['rgt'] + 1;
					$data['rgt'] = $bigLftCate['rgt'] + 2;
					$setLftData = array("`lft`=`lft`+2");
					$courseDb->setLftCate($bigLftCate['rgt'],$setLftData);
					$setRgtData = array("`rgt`=`rgt`+2");
					$courseDb->setRgtCate($bigLftCate['rgt'],$setRgtData);
				}else{
					$data['lft'] = $cateInfo['lft'] + 1;
					$data['rgt'] = $cateInfo['lft'] + 2;
					$setLftData = array("`lft`=`lft`+2");
					$courseDb->setLftCate($cateInfo['lft'],$setLftData);
					$setRgtData = array("`rgt`=`rgt`+2");
					$courseDb->setRgtCate($cateInfo['lft'],$setRgtData);
				}
			}elseif($params->before_id == $params->parent_id){
				$data['level'] = 1;
				$parent['lft'] = 0;
				$data['level'] = $data['level'];
				$data['lft'] = $parent['lft']+1;
				$data['rgt'] = $parent['lft']+2;
				$setLftData = array("`lft`=`lft`+2");
				$courseDb->setLftCate($parent['lft'],$setLftData);
				$setRgtData = array("`rgt`=`rgt`+2");
				$courseDb->setRgtCate($parent['lft'],$setRgtData);	
			}else{
				$data['level'] = 1;
				$bigLftCate = $courseDb->getBigLftCateByLevel($data['level']);
				if(!empty($bigLftCate)){
					$data['lft'] = $bigLftCate['rgt'] + 1;
					$data['rgt'] = $bigLftCate['rgt'] + 2;
				}else{
					$data['lft'] = 1;
					$data['rgt'] = 2;
				}
			}	
		}
		$addRet = $courseDb->addCate($data);
		if(!empty($addRet)){
			return $this->setResult($addRet);
		}else{
        	return $this->setResult('',-2,'添加失败');
		}
	}

	/*
	 *修改分类
	 */
	public function pageUpdateCate($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$cateId = $inPath[3];
		if(empty($params->name)){
			return $this->setResult('',-1,'名称不能为空');
		}
		if(empty($params->name_display)){
			return $this->setResult('',-1,'展示名称不能为空');
		}
		if(empty($cateId)){
			return $this->setResult('',-1,'分类id为空');
		}
		$courseDb = new course_db;
		$oldCate = $courseDb->getCateById($cateId);
		if(empty($oldCate)){
			return $this->setResult('',-1,'分类id错误');
		}
		$data = array();
		$data['name'] = $params->name;
		$data['name_display'] = $params->name_display;
		$data['descript'] = $params->descript;
		$data['status'] = $params->status;
		$update_ret = $courseDb->updateCate($cateId,$data);
		if($update_ret === false){		
			return $this->setResult('',-2,'修改失败!');
		}else{
			return $this->setResult($update_ret);
		}
	}

	/*
	 *删除分类
	 */
	public function pageDelCate($inPath){
		$cateId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($cateId) ) {
			return $this->setResult('',-1,'分类id不能为空');
		}
		$courseDb = new course_db;
		$oldCate = $courseDb->getCateById($cateId);
		if(empty($oldCate)){
			return $this->setResult('',-1,'分类id错误');
		}
		$nodeCate = $courseDb->getNodeCateByLftAndRgt($oldCate['lft'],$oldCate['rgt'],$oldCate['level']);
		if(!empty($nodeCate->items)){
			return $this->setResult('',-20,'该分类下还有子分类不能删除！');
		}
		$cateAttr = $courseDb->getAttrByCateId($oldCate['pk_cate']);
		if(!empty($cateAttr->items)){
			return $this->setResult('',-21,'该分类已有属性不能删除！');
		}
		if($oldCate['level'] == 1){
			$courseInfo = $courseDb->getCourseByFirstCate($oldCate['pk_cate']);
		}elseif($oldCate['level'] == 2){
			$courseInfo = $courseDb->getCourseBySecondCate($oldCate['pk_cate']);
		}elseif($oldCate['level'] == 3){
			$courseInfo = $courseDb->getCourseByThirdCate($oldCate['pk_cate']);
		}
		if(!empty($courseInfo->items)){
			return $this->setResult('',-22,'该分类已绑定课程不能删除！');
		}
		$del_ret = $courseDb->delCateByLftAndRgt($oldCate['lft'],$oldCate['rgt']);
		if( $del_ret === false ){
            return $this->setResult('',-23,'删除失败');
        }else{
			$width = $oldCate['rgt']-$oldCate['lft']+1;
			$setLftData = array("`lft` =`lft` - $width");
			$courseDb->setLftCate($oldCate['rgt'],$setLftData);
			$setRgtData = array("`rgt` = `rgt`-$width");
			$courseDb->setRgtCate($oldCate['rgt'],$setRgtData);
        	return $this->setResult($del_ret);
        }
	}
	
	public function pageAddCateAttr($inPath){	
		$cateId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($cateId) ) {
			return $this->setResult('',-1,'分类id不能为空');
		}
		$attrIdArr=SJson::decode(utility_net::getPostData(),true);
		$courseDb = new course_db;
		if(!empty($attrIdArr)){
			$newAttr = $attrIdArr;
			$delAttr = array();
			$existAttr = array();
			$addAttr = array();
			$cateAttr = $courseDb->getAttrByCateId($cateId);
			if(!empty($cateAttr->items)){
				foreach($cateAttr->items as $attr){
					if(!in_array($attr['pk_attr'],$newAttr)){
						$delAttr[] = $attr['pk_attr'];
					}else{
						$existAttr[] = $attr['pk_attr'];
					}
				}
				if(!empty($existAttr)){
					$addAttr = array_diff($newAttr,$existAttr);
				}else{
					$addAttr = $newAttr;
				}
				if(!empty($delAttr)){
					$delAttrIds = implode(',',$delAttr);
					$delData = array('fk_cate'=>0);
					$retDel = $courseDb->updateAttrByAttrIds($delAttrIds,$delData);
				}
				if(!empty($addAttr)){
					$addAttrIds = implode(',',$addAttr);
					$addData = array('fk_cate'=>$cateId);
					$retAdd = $courseDb->updateAttrByAttrIds($addAttrIds,$addData);
				}
				if( !empty($addAttr) ){
					$ret = $retAdd;
				}elseif( !empty($delAttr) ){
					$ret = $retDel;
				}else{
					$ret = true;
				}
				if($ret === false){
					return $this->setResult('',-2,'修改失败!');
				}else{
					return $this->setResult($ret);
				}
				
			}else{
				$attrIds = implode(',',$attrIdArr);
				$attrData = array('fk_cate'=>$cateId);
				$ret = $courseDb->updateAttrByAttrIds($attrIds,$attrData);
				if($ret === false){
					return $this->setResult('',-2,'修改失败!');
				}else{
					return $this->setResult($ret);
				}
			}
		}else{
			$attrData = array('fk_cate'=>0); 
			$ret = $courseDb->updateAttrByCateId($cateId,$attrData);
			if($ret === false){
				return $this->setResult('',-2,'修改失败!');
			}else{
				return $this->setResult($ret);
			}
		}
	}

	/*
	 *获取分类信息
	 */
	public function pageGetCateByCid($inPath){
		$cateId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($cateId) ) {
			return $this->setResult('',-1,'cateId is empty');
		}
		$courseDb = new course_db; 
		$ret = $courseDb->getCateById($cateId);
		if( !empty($ret) ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetCateByCidStr($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if( empty($params->cateIdStr) ) {
			return $this->setResult('',-1,'cateIdStr is empty');
		}
		$courseDb = new course_db; 
		$ret = $courseDb->getCateByCateIdStr($params->cateIdStr);
		if( !empty($ret->items) ) {
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetNodeCate($inPath){
		$cateId = isset($inPath[3]) ? $inPath[3] : '';
		if( !is_numeric($cateId) ) {
			return $this->setResult('',-1,'cateId is error');
		}
		$courseDb = new course_db;
		$oldCate = $courseDb->getCateById($cateId);
		if(empty($oldCate)){
			return $this->setResult('',-1,'cateId is error');
		}
		$nodeCate = $courseDb->getNodeCateByLftAndRgt($oldCate['lft'],$oldCate['rgt'],$oldCate['level']);
		if(!empty($nodeCate->items)){
			return $this->setResult($nodeCate->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetCateByLevel($inPath){
		$level = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($level) ) {
			return $this->setResult('',-1,'level is error');
		}
		$courseDb = new course_db;
		$cateRet = $courseDb->getCateByLevel($level);
		if(!empty($cateRet->items)){
			return $this->setResult($cateRet->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	/*
	 * 获取一级分类名
	 * 缓存10分钟
	 */
	public function pageCheckCourseByFirstCateArr($inPath){
		utility_cache::pageCache(3600);
		$firstCateArr = SJson::decode(utility_net::getPostData(),true);
		if( empty($firstCateArr) ) {
			return $this->setResult('',-1,'firstCateArr is empty');
		}
		$ownerId = !empty($inPath[3]) ? (int)($inPath[3]) : 0;
		$courseIds = !empty($inPath[4]) ? $inPath[4] : 0;
		$courseDb = new course_db;
		$courseRet = $courseDb->checkCourseByFirstCateArr($firstCateArr,$ownerId,$courseIds);
		if(!empty($courseRet->items)){
			return $this->setResult($courseRet->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	/*
	 * 获取二级分类名
	 * 缓存10分钟
	 */
	public function pageCheckCourseBySecondCateArr($inPath){
		utility_cache::pageCache(3600);
		$secondCateArr = SJson::decode(utility_net::getPostData(),true);
		if( empty($secondCateArr) ) {
			return $this->setResult('',-1,'secondCateArr is empty');
		}
		$ownerId = !empty($inPath[3]) ? (int)($inPath[3]) : 0;
		$courseIds = !empty($inPath[4]) ? $inPath[4] : 0;
		$courseDb = new course_db;
		$courseRet = $courseDb->checkCourseBySecondCateArr($secondCateArr,$ownerId,$courseIds);
		if(!empty($courseRet->items)){
			return $this->setResult($courseRet->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	/*
	 * 获取三级分类名
	 * 缓存10分钟
	 */
	public function pageCheckCourseByThirdCateArr($inPath){
		utility_cache::pageCache(3600);
		$thirdCateArr = SJson::decode(utility_net::getPostData(),true);
		
		if( empty($thirdCateArr) ) {
			return $this->setResult('',-1,'thirdCateArr is empty');
		}
		$ownerId = !empty($inPath[3]) ? (int)($inPath[3]) : 0;
		$courseIds = !empty($inPath[4]) ? $inPath[4] : 0;
		$courseDb = new course_db;
		$courseRet = $courseDb->checkCourseByThirdCateArr($thirdCateArr,$ownerId,$courseIds);
		
		if(!empty($courseRet->items)){
			return $this->setResult($courseRet->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	//获取机构的有效课程分类
	public function pageOrgValidCateIds(){
		utility_cache::pageCache(3600);
		$params = SJson::decode(utility_net::getPostData(),true);
		$uid = empty($params['uid'])?0:intval($params['uid']);
		$type = empty($params['type'])?0:intval($params['type']);
		$extWhere = empty($params['extWhere'])?array():$params['extWhere'];
		if(empty($uid)) return $this->setResult('',-1,'secondCateArr is empty');
		$courseDb = new course_db;
		$result=$courseDb->getOrgCate($uid,$type,$extWhere);
		if(!empty($result)){
			return $this->setResult($result);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	//根据子类id取分类id
	public function pageGetParentCateByIds(){
		utility_cache::pageCache(3600);
		$params = SJson::decode(utility_net::getPostData(),true);
		$cateIds = empty($params['cateIds'])?0:$params['cateIds'];
		$level = empty($params['level'])?0:intval($params['level']);
		if(empty($cateIds) || empty($level)) return $this->setResult('',-1,'secondCateArr is empty');
		$courseDb = new course_db;
		$result=$courseDb->getParentCateByIds($cateIds,$level);
		if(!empty($result)){
			return $this->setResult($result);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
}
