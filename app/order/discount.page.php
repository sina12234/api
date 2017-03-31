<?PHP

class order_discount
{	
	public function pageCheckCode($inPath)
	{
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['code']) || empty($params['objectId'])){
			return order_api::setMsg('-1','params empty');
		}
		
		$code     = trim($params['code']);
		$courseId = (int)$params['objectId'];
		$userId   = (int)$params['userId'];
        $isRot    = !empty($params['isRot']) ? $params['isRot'] : 0;
		
		$ret = course_discount_api::checkCode($code, $courseId, $userId,$isRot);
		return $ret;
	}
	
}
?>
