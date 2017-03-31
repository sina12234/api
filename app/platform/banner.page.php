<?php
class platform_banner{


	public function setResult($data='',$code=0,$msg='success'){

		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = $code;
		$ret->result->data = $data;
		$ret->result->msg  = $msg;
		return $ret;

	}

	public function pageGetBannerList($inPath){
		$page  = isset($inPath[3]) ? $inPath[3] : 1;
		$limit = isset($inPath[4]) ? $inPath[4] : '';
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->status)){
			return $this->setResult('',-1,'status is empty');
		}
		$condition = "status IN ($params->status)";
		$orderby = array("order_no"=>"asc");
		$banner_ret = platform_api::getBannerList($page,$limit,$condition,$orderby);
		
		if( !$banner_ret ){
			return $this->setResult('',-1,'data is not found');
		}else{
			return $this->setResult($banner_ret);
		}
	}
	
	public function pageGetShowBannerByType($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$type = !empty($params->type)?$params->type:0;
		if(empty($type)){
			return $this->setResult('',-1,'params is empty');
		}
		$banner_ret = platform_db::getShowBannerByType($type);
		if( empty($banner_ret->items) ){
			return $this->setResult('',-1,'data is not found');
		}else{
			return $this->setResult($banner_ret);
		}
	}

	public function pageAddBanner($inPath){

		$params=SJson::decode(utility_net::getPostData(),true);
		if(empty($params['url'])){
			return $this->setResult('',-1,'图片为空');
		}
		if(empty($params['type'])){
			return $this->setResult('',-2,'请选择图片展示类型');
		}
		$add_flag = 1;
		$banner_ret = platform_db::getBannerByType($params['type']);
		if(!empty($banner_ret->items)){
			$count= count($banner_ret->items);
			if($count >=6 ){
				$add_flag = 0;
			}
		}
		if($add_flag == 1){
			$add_ret = platform_db::addBanner($params);
			if(!$add_ret){
				return $this->setResult('',-3,'添加失败');
			}else{
        		return $this->setResult($add_ret);
			}	
		}else{
			return $this->setResult('',-4,'一种展示类型轮播图最多添加6个');
		}
	}

	public function pageUpdateBanner($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$banner_id = $inPath[3];
		if(empty($params)){
			return $this->setResult('',-1,'params is empty');
		}
		if(empty($banner_id)){
			return $this->setResult('',-1,'banner_id is empty');
		}
		$update_ret = platform_db::updateBanner($banner_id,$params);
		if($update_ret === false){
			return $this->setResult('',-3,'update failed!');
		}else{
			return $this->setResult($update_ret);
		}
	}

	public function pageDelBanner($inPath){

		$banner_id = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($banner_id) ) {
			return $this->setResult('',-1,'params is error');
		}
		$data = array('status'=>-1);
		$del_ret = platform_db::updateBanner($banner_id, $data);
		if( $del_ret === false ){
            return $this->setResult('',-2,'delete failed');
        }else{
        	return $this->setResult($del_ret);
        }
	}

	public function pageGetBannerByid($inPath){

		$banner_id = isset($inPath[3]) ? $inPath[3] : '';
		if( empty($banner_id) ) {
			return $this->setResult('',-1,'params is error');
		}
		$ret = platform_db::getBannerByid($banner_id);
		if( $ret ) {
			return $this->setResult($ret);
		}else{
			return $this->seResult('',-2,'data is not found');
		}

	}

}
