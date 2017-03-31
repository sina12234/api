<?php
class stat_dayuserorgstat{
	
	public $ret;

    public function setResult($data='', $code=1, $msg='success'){
         $this->ret['result'] = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data
         );

   		 return $this->ret;
	}

	public function pageGetUserOrgStatByPkday($inPath){

        $params = SJson::decode(utility_net::getPostData());
	
        if(empty($params->min_date) || empty($params->max_date)){
            return $this->setResult('',-2, 'params error!');
        }
		
		$ret = stat_db::getUserOrgStatByPkday($params);
        if(!$ret){
           return $this->setResult('', -2, 'get data failed');
        }
        return $this->setResult($ret);
	}

	public function pageGetUserOrgStatFkuser($inPath){

		$fkuser = stat_db::GetUserOrgStatFkuser();
	
        if(!$fkuser){
           return $this->setResult('', -2, 'get data failed');
        }
        return $this->setResult($fkuser);
	}
	
	public function pageGetDayUserOrgOrderStatByPkday($inPath){
        $params = SJson::decode(utility_net::getPostData());
		$minDate = isset($params->min_date)?$params->min_date:'';
		$maxDate = isset($params->max_date)?$params->max_date:'';	
        if(empty($minDate) && empty($maxDate)){
            return $this->setResult('',-2, 'params error!');
        }
		$ret = stat_api::getDayUserOrgOrderStatByPkday($minDate,$maxDate);
        if(!empty($ret)){
            return $this->setResult($ret);
        }else{
			return $this->setResult('', -2, 'get data failed');
		}
	}

	public function pageGetDayOrgStatByOwnerid($inPath){
        $params = SJson::decode(utility_net::getPostData());
		$minDate = isset($params->min_date)?$params->min_date:'';
		$maxDate = isset($params->max_date)?$params->max_date:'';	
		$ownerId = isset($params->fk_user)?$params->fk_user:'';
		if(empty($ownerId)){
            return $this->setResult('',-2, 'params error!');
        }
		$ret = stat_db::getDayOrgStatByOwnerid($ownerId,$minDate,$maxDate);
        if(!empty($ret->items)){
            return $this->setResult($ret->items);
        }else{
			return $this->setResult('', -2, 'get data failed');
		}
	}
	
	public function pageGetOrgOrderCountByDay($inPath){
        $params = SJson::decode(utility_net::getPostData());
		$minDate = isset($params->min_date)?$params->min_date:'';
		$maxDate = isset($params->max_date)?$params->max_date:'';	
		$ownerId = isset($params->fk_user)?$params->fk_user:'';
		if(empty($ownerId)){
            return $this->setResult('',-2, 'params error!');
        }
		$ret = stat_db::getOrgOrderCountByDay($ownerId,$minDate,$maxDate);
        if(!empty($ret->items)){
            return $this->setResult($ret->items[0]);
        }else{
			return $this->setResult('', -2, 'get data failed');
		}
	}





}









