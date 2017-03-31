<?php
class course_attrValue{
	
	public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        );
	}
	
	public function pagegetAttrValueListByAttrId($inPath){

		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->attr_id)){
			return $this->setResult('',-1,'attr_id is empty');
		}
		$page = !empty($params->page)?$params->page:1;
		$length = !empty($params->length)?$params->length:20;
		
		$courseDb = new course_db;
		$attrValueList = $courseDb->getAttrValueListByAttrId($page,$length,$params->attr_id,$params->name,$params->status);
		if(!empty($attrValueList)){
			return $this->setResult($attrValueList);
		}else{
			return $this->setResult('',-2,'get failed data');
		}
	}
	
	public function pageAddAttrValue($inPath){

		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			return $this->setResult('',-1,'name is empty');
		}
		if(empty($params->fk_attr)){
			return $this->setResult('',-1,'attrId is empty');
		}
		$add_ret = course_db::addAttrValue($params);
		if(!$add_ret){
			return $this->setResult('',-2,'add failed');
		}else{
        	return $this->setResult($add_ret);
		}
	}

	public function pageUpdateAttrValue($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$attrValueId = $inPath[3];
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		if(empty($attrValueId)){
			return $this->setResult('',-1,'attrValueId is empty');
		}
		$update_ret = course_db::updateAttrValue($attrValueId,$params);
		if($update_ret === false){
			return $this->setResult('',-2,'update failed!');
		}else{
			return $this->setResult($update_ret);
		}
	}

	public function pageDelAttrValue($inPath){
		$attrValueId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($attrValueId) ) {
			return $this->setResult('',-1,'attr_value_id is empty');
		}
		$checkRet = course_db::getCourseAttrValueByAttrValueId($attrValueId);
		if(!empty($checkRet->items)){
			return $this->setResult('',-2,'it can not be deleted!');
		}
		$del_ret = course_db::delAttrValueByAvid($attrValueId);
		if( $del_ret === false ){
            return $this->setResult('',-3,'delete failed');
        }else{
        	return $this->setResult($del_ret);
        }
	}

	public function pageGetAttrValueById($inPath){
		$attrValueId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($attrValueId) ) {
			return $this->setResult('',-1,'attrValueId is empty');
		}
		$ret = course_db::getAttrValueById($attrValueId);
		if( $ret ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetAttrValueByAttrId($inPath){
		$attrId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($attrId) ) {
			return $this->setResult('',-1,'attrId is empty');
		}
		$ret = course_db::getAttrValueByAttrId($attrId);
		if( !empty($ret->items) ) {
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetCourseAttrValueByCourseId($inPath){
		$courseId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($courseId) ) {
			return $this->setResult('',-1,'courseId is empty');
		}
		$ret = course_api::getCourseAttrValueByCourseId($courseId);
		if( !empty($ret) ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageCheckMappingCourseByAttrValueIdArr($inPath){
		$attrValueIdArr = SJson::decode(utility_net::getPostData(),true);
		if( empty($attrValueIdArr) ) {
			return $this->setResult('',-1,'attrValueIdArr is empty');
		}
		$ownerId = !empty($inPath[3]) ? (int)($inPath[3]) : 0;
		$courseDb = new course_db;
		$courseRet = $courseDb->checkMappingCourseByAttrValueIdArr($attrValueIdArr,$ownerId);
		if(!empty($courseRet->items)){
			return $this->setResult($courseRet->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetAttrValueByVidArr($inPath){
		$vidArr = SJson::decode(utility_net::getPostData(),true);
		if( empty($vidArr) ) {
			return $this->setResult('',-1,'vidArr is empty');
		}
		$vidStr = implode(',',$vidArr);
		$courseDb = new course_db;
		$ret = $courseDb->getAttrValueByAttrValueIds($vidStr);
		if( $ret->items ) {
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}

	
	
}