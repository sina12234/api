<?php
class course_attr{
	
	public function setResult($data='', $code=0,$msg='success'){
		
        return array(
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        );
	}
	
	public function pageGetAttrList($inPath){

		$params = SJson::decode(utility_net::getPostData());
		$page = !empty($params->page)?$params->page:1;
		$length = !empty($params->length)?$params->length:20;
		$courseDb = new course_db;
		$attrList = $courseDb->getAttrList($page,$length,$params->name,$params->status,$params->cate_id);
		if(!empty($attrList)){
			return $this->setResult($attrList);
		}else{
			return $this->setResult('',-2,'get failed data');
		}
	}
	
	public function pageAddAttr($inPath){

		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			return $this->setResult('',-1,'name is empty!');
		}
		if(empty($params->name_display)){
			return $this->setResult('',-1,'name_display is empty');
		}
		$add_ret = course_db::addAttr($params);
		if(!$add_ret){
			return $this->setResult('',-2,'add failed');
		}else{
        	return $this->setResult($add_ret);
		}
	}

	public function pageUpdateAttr($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$attrId = $inPath[3];
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		if(empty($attrId)){
			return $this->setResult('',-1,'attrId is empty');
		}
		$update_ret = course_db::updateAttr($attrId,$params);
		if($update_ret === false){
			return $this->setResult('',-2,'update failed!');
		}else{
			return $this->setResult($update_ret);
		}
	}

	public function pageDelAttr($inPath){
		$attrId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($attrId) ) {
			return $this->setResult('',-1,'attrid is empty');
		}
		$courseAttrRet = course_db::getCourseAttrValueByAttrId($attrId);
		if(!empty($courseAttrRet->items)){
			return $this->setResult('',-2,'It can not be deleted!');
		}
		$del_ret = course_db::delAttrByAid($attrId);
		if( $del_ret === false ){
            return $this->setResult('',-3,'delete failed!');
        }else{
			course_db::delAttrValueByAttrId($attrId);
        	return $this->setResult($del_ret);
        }
	}

	public function pagegetAttrByAid($inPath){
		$attrId = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($attrId) ) {
			return $this->setResult('',-1,'attrid is empty');
		}
		$ret = course_db::getAttrByAid($attrId);
		if( $ret ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}

	public function pageGetAttrByCateId($inPath){
		$cateId = isset($inPath[3]) ? $inPath[3] : '';
		if( !is_numeric($cateId) ) {
			return $this->setResult('',-1,'cateId is error');
		}
		$courseDb = new course_db;
		$ret = $courseDb->getAttrByCateId($cateId);
		if( !empty($ret->items) ) {
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetAttrByCateIdArr($inPath){
		$cateIdArr = SJson::decode(utility_net::getPostData(),true);
		if( empty($cateIdArr) ) {
			return $this->setResult('',-1,'cateIdArr is empty');
		}
		$courseDb = new course_db;
		$ret = $courseDb->getAttrByCateIdArr($cateIdArr);
		if( !empty($ret->items) ) {
			return $this->setResult($ret->items);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	public function pageGetAttrAndValueByCateId($inPath){
		$cateId = isset($inPath[3]) ? $inPath[3] : '';
		if( !is_numeric($cateId) ) {
			return $this->setResult('',-1,'cateId is error');
		}
		$ret = course_api::getAttrAndValueByCateId($cateId);
		if( !empty($ret) ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
	
	public function pageGetAllAttrValue(){
		$courseDb = new course_db;
		$ret = $courseDb->getAllAttrValue();
		if( !empty($ret) ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'data is not found');
		}
	}
	
}
