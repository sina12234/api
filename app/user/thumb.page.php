<?php
class user_thumb{


	public function setResult($data='',$code=0,$msg='success'){

		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code = $code;
		$ret->result->data = $data;
		$ret->result->msg  = $msg;
		return $ret;

	}

	public function pageAddUserThumb($inPath){

		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->fk_user) || empty($params->thumb_small)){
			return $this->setResult('',-1,'params is empty');
		}
		$exist = user_db::checkUserThumb($params);
		if( !$exist ){
			$add_ret = user_db::addUserThumb($params);
			if(!$add_ret){
				return $this->setResult('',-2,'add is failed');
			}else{
        		return $this->setResult($add_ret);
			}
		}else{
			$curr = date('Y-m-d H:i:s',time());
			$data = array('create_time'=> $curr);
			$ret = user_db::updateUserThumb($exist['pk_thumb'], $data);
			return $this->setResult($exist['pk_thumb']);
		}	
	}

	public function pageGetUserThumbByUid($inPath){

		$uid   = isset($inPath[3]) ? $inPath[3] : '';
		$limit = isset($inPath[4]) ? $inPath[4] : 4;
		if( empty($uid) ) {
			return $this->setResult('',-1,'uid is empty');
		}

		$ret = user_db::getUserThumbByUid(1,$limit,$uid);
		if( $ret->items ) {
			return $this->setResult($ret);
		}else{
			return $this->setResult('',-2,'get data faild');
		}

	}

}
