<?php
class user_orgAccountOrderContent{

	public function setResult($data='',$code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
	}

	public function pagegetDayPercent($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_api::getDayPercent($oid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pagegetWeekPercent($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_api::getWeekPercent($oid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pagegetMonthPercent($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$res = user_api::getMonthPercent($oid);
		if(!empty($res)){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pagegetOrderCountByOrgId($inPath){
		$oid = !empty($inPath[3])?$inPath[3]:'';
		if(empty($oid) && !is_numeric($oid)){
			return $this->setResult('',-1,'params is error');	
		}
		$params = SJson::decode(utility_net::getPostData());
		$startTime = isset($params->start_time)?$params->start_time:'';
		$endTime = isset($params->end_time)?$params->end_time:'';
		$res = user_api::getOrderCountByOrgId($oid,$startTime,$endTime);
		if($res !== false){
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
	public function pageGetOrderContentList($inPath){
		
		if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
		if(empty($inPath[4])||!is_numeric($inPath[4])){$length = -1;}else{$length = $inPath[4];}
		$params = SJson::decode(utility_net::getPostData(),true);

		//if(empty($params) || empty($params['orgId'])){
                if(empty($params)){
			return $this->setResult('',-1,'params is error');
		}
		
                $resellOrgId = empty($params['resellOrgId']) ? 0 : (int) $params['resellOrgId'];
                $promoteOrgId = empty($params['promoteOrgId']) ? 0 : (int) $params['promoteOrgId'];

                if(!empty($params['orgId']) && empty($resellOrgId)) $data['orgId'] = $params['orgId'];
		if(!empty($params['status'])){
			$data['status'] = $params['status'];
		}
		if(!empty($params['startTime']) && !empty($params['endTime'])){
			$data['startTime'] = $params['startTime'];
			$data['endTime'] = $params['endTime'];
		}
                if(!empty($params['objType'])){
                    $data['objType'] = (int)$params['objType'];
                }        
		
		$res = user_db_orgAccountOrderContentDao::rows($data,$page,$length);
                
		if($res !== false){                
                        if(!empty($resellOrgId) || !empty($promoteOrgId)){
                            $lists = $res->items;                            
                            foreach ($lists as $key=>$val){
                                $fk_order_content = $val['fk_order_content'];
                                $condition = "promote_status=1 and pk_order_content =$fk_order_content";
                                if(!empty($promoteOrgId)) $condition .= " and org_id={$promoteOrgId} ";
                                if(!empty($resellOrgId)) $condition .= " and resell_org_id={$resellOrgId} ";
                                $resOrder = order_db_orderContentDao::lists($condition);
                                if(!empty($resOrder->items[0])){
                                    $res->items[$key]['price_promote'] = $resOrder->items[0]['price_promote'];
                                    $res->items[$key]['resell_org_id'] = $resOrder->items[0]['resell_org_id'];
                                    $res->items[$key]['promote_status'] = $resOrder->items[0]['promote_status'];
                                } else {
                                    unset($res->items[$key]);
                                }                                
                            }                             
                        }
			return $this->setResult($res);
		}else{
			return $this->setResult('',-2,'get data is failed');
		}
	}
	
}
