<?PHP

class order_member
{
    //添加临时会员购买有效期
    public function pageAddVipTime()
    {
        $parsms   = SJson::decode(utility_net::getPostData(), true);
        if(empty($parsms['setId']) || empty($parsms['day']) || empty($parsms['uid'])){
            return order_api::setMsg('-1', 'params error');
        }

        redis_api::useConfig('db_order');

        $setId  = (int)$parsms['setId']; 
        $day    = (int)$parsms['day']; 
        $userId = (int)$parsms['uid'];
        $key = md5('member_'.$setId.'_'.$userId);

        $ret = redis_api::set($key, $day);
        if($ret){
            return redis_api::get($key);
        }
        return false;
    }
}
?>
