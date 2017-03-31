<?PHP

class order_orderContent
{
    public function pageOrderInfo($inPath)
    {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = '';
        $params = SJson::decode(utility_net::getPostData(),true);
		
        if(empty($params)){
            $ret->result->code = -1;
            $ret->result->msg  = 'params error';
            return $ret;
        }
		
		$page   = (!empty($inPath[3])) ? (int)$inPath[3] : 1;
		$length = (!empty($inPath[4])) ? (int)$inPath[4] : -1;
		
		if(!empty($params['contentOrderArr'])){
			$data['contentOrderIds'] = implode(',',$params['contentOrderArr']);
		}
		if(!empty($params['objectType'])){
			$data['objectType'] = $params['objectType'];
		}
		
        $res = order_db::getOrderInfo($data,$page,$length);
    
		if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        return $res;
    } 

	//获取单个物品订单 
	public function pageGetOrderContentInfo($inPath)
	{		
		if (empty($inPath[3])) {
			return order_api::setMsg('-1','params error');
        }
		$orderContentId = (int)$inPath[3];
		$orderContentInfo = order_db_orderContentDao::row($orderContentId);
		if(empty($orderContentInfo)){
			return order_api::setMsg('-2','data empty');
		}
		
		$orderContentInfo['status']    = self::$payStatus[$orderContentInfo['status']];
		$orderContentInfo['price']     = $orderContentInfo['price']/100;
		$orderContentInfo['price_old'] = $orderContentInfo['price_old']/100;
		return order_api::setMsg("1","success",$orderContentInfo);
	}
}
?>
