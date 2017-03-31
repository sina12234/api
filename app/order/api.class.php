<?PHP

class order_api
{
    static $status2pay = array(
        "0"  => "initial",
        "1"  => "paying",
        "2"  => "success",
        "-1" => "deleted",
        "-2" => "expired",
        "-3" => "fail",
        "-4" => "cancel"
    );

    static $pay2status = array(
		"initial" => "0",
        "paying"  => "1",
        "success" => "2",
        "deleted" => "-1",
        "expired" => "-2",
        "fail"    => "-3",
        "cancel"  => "-4"
	);
    
    public static function payType($str)
    {
        if("zhifubao" == $str){
            return 1;
        }elseif("weixin" == $str){
            return 2;
        }elseif("free" == $str){
            return 3;
        }elseif(" " == $str){
            return 0;
        }elseif("inapp" == $str){
            return 4;
        }else{
            return -1;
        }
    }

    public static function setMsg($code, $msg, $data = array())
    {
        return json_encode(
            array(
                'code' => $code,
                'msg'  => $msg,
                'data'  => $data 
            ),
            JSON_UNESCAPED_UNICODE
        );
    }

    public static function getOrderAndContent($orderId)
    {
        if (is_numeric($orderId)) { //orderId
            $orderInfo = order_db_orderDao::row($orderId);
        } else {
            $orderInfo = order_db_orderDao::row(['uniqueOrderId' => $orderId]);
        }
        if (empty($orderInfo)) return [];

        $item                  = new stdclass;
        $item->order_id        = $orderInfo["pk_order"];
        $item->user_id         = $orderInfo["fk_user"];
        $item->price           = $orderInfo["price"];
        $item->price_old       = $orderInfo["price_old"];
        $item->price_market    = $orderInfo["price"];
        $item->status          = $orderInfo["status"];
        $item->callback_status = $orderInfo["callback_status"];
        $item->unique_order_id = $orderInfo["unique_order_id"];
        $item->out_trade_id    = $orderInfo["out_trade_id"];
        $item->pay_type        = $orderInfo["pay_type"];
        $item->expiration_time = $orderInfo["expiration_time"];
        $item->third_order_id  = $orderInfo["third_order_id"];
        $item->last_updated    = $orderInfo["last_updated"];
        $item->create_time     = $orderInfo["create_time"];

        $orderContentList = order_db_orderContentDao::rows($orderInfo['pk_order']);
        if (!empty($orderContentList->items)) {
            foreach ($orderContentList->items as $v) {
                // 后期１对多这里应该是个列表
                $item->object_id   = $v['object_id'];
                $item->ext         = $v['ext'];
                $item->org_id      = $v['org_id'];
                $item->object_type = $v['object_type'];
            }

            $orgInfo = user_db::getOrgNameInfo($item->org_id);
            $item->user_owner = $orgInfo['user_owner_id'];
        }

        return $item;
    }
}
?>
