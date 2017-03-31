<?php
class user_organizationTag{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}
	public function pageUpdate($inPath){
		$params=SJson::decode(utility_net::getPostData(),true);
        if (empty($params['fk_org'])){
			return $this->setResult('',-1,'params is empty');
		}
		if(count($params['tag_names']) > 6){
			return $this->setResult('',-2,'tags over 6');
		}
		if(!empty($params['tag_names'])){
			$res = user_api::updateOrganizationTag($params['fk_org'],$params['tag_names']);
		}else{
			$res = user_db_organizationTagDao::del($params['fk_org']);
		}
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-3,'add data is failed');
		}
	}

	public function pageGetOrgTagByOrgId($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_db_organizationTagDao::getOrgTagByOrgId($oid);
		if(!empty($res->items)){
			return $this->setResult($res->items);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetOrgTagSort($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->oid) && !is_numeric($params->oid) ){
			return $this->setResult('',-1,'params is error');	
		}
		$data = [];
		$sort = !empty($params->order)?$params->order:'count';
		$oid = !empty($params->oid)?$params->oid:0;
		$limit = !empty($params->length)?$params->length:10;
		$data['often'] = $this->getUsedTag($oid,$sort,$limit);
		$data['lasted'] = $this->getUsedTag($oid,"sort",$limit);
		if(!empty($data)){
			return $this->setResult($data);
		}else{
			return $this->setResult('',-2,'data is not found ');
		}
	}
	public function getUsedTag($oid,$sort,$limit){
		$arr = [];
		$resOfen = user_db_organizationTagDao::getOrgTagSort($oid,$sort,$limit);
		if(!empty($resOfen->items)){
			foreach($resOfen->items as $k=>$v){
				$tagIdArr[] = isset($v['fk_tag']) ? $v['fk_tag'] : 0;
				$arr[$v['fk_tag']] = $v;
			}
			$tagStr = implode(",",$tagIdArr);
			$tagInfo = tag_db::getTagNameInfo($tagStr);
			$tagArr = [];
			foreach($tagInfo->items as $k=>$v){
				$tagArr[$v['pk_tag']] = $v;
			}
			foreach($arr as $k=>$v){
					$arr[$k]['name'] = isset($tagArr[$v['fk_tag']]['name']) ? $tagArr[$v['fk_tag']]['name'] : '';
			}
		}
		return $arr;
	}
	public function pagegetUserSelectedCourseTag($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$courseId = !empty($params->courseId) ? (int)$params->courseId : 0;
		$groupId = !empty($params->tagConf) ? (int)$params->tagConf : 0;
		$data = ['fk_course'=>$courseId,'t_tag.status'=>0,'fk_group'=>$groupId];
		$tagCourse = tag_db::getUserSelectedCourseTag($data);
		if(!empty($tagCourse)){
			return $this->setResult($tagCourse);
		}else{
			return $this->setResult('',-2,'data is not found ');
		}
	}
	public function pageaddCourseTagBelongGroup($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$courseId = !empty($params->courseId) ? $params->courseId : 0;
		$tagConf = !empty($params->tagConf) ? $params->tagConf : 0;
		$tagNameArr = !empty($params->tagNameArr) ? $params->tagNameArr : '';
		$data = ['fk_course'=>$courseId,'t_tag.status'=>0];
		$tagCourse = tag_api::addCourseTagBelongGroup($courseId,$tagConf,$tagNameArr);
		if(!empty($tagCourse)){
			return $this->setResult($tagCourse);
		}else{
			return $this->setResult('',-2,'data is not found ');
		}
	}
	public function pagedelMappingPlanByGidAndTidArr(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = 100;
		$ret->result->msg= "success";
		$params = SJson::decode(utility_net::getPostData());
		$courseId = !empty($params->courseId) ? $params->courseId : 0;
		$tagConfId = !empty($params->groupId) ? $params->groupId : 0;
		$dbRet = tag_db::delMappingTagCourseData($tagConfId,$courseId);
		if($dbRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "delete is failed!";
			return $ret;
		}
        $ret->data=$dbRet;
        return $ret;
    }
}
