<?php

/**
 * @doc http://wiki.gn100.com/doku.php?id=docs:api:course:fee
 */
class course_feeorder
{
    const initial = 0;
    const paying = 1;
    const success = 2;
    const deleted = -1;
    const expired = -2;
    const fail = -3;
    const cancel = -4;
    var $status = array("0" => "initial", "1" => "paying", "2" => "success", "-1" => "deleted", "-2" => "expired", -3 => "fail", -4 => "cancel");

    private function status2key($st)
    {
        if (isset($this->status[$st])) return $this->status[$st];

        return false;
    }

    private function status2value($st)
    {
        foreach ($this->status as $k => $status) {
            if ($status == $st) return $k;
        }

        return false;
    }

    private function payTypeStr2Num($str)
    {
        if ("zhifubao" == $str) {
            return 1;
        } else if ("weixin" == $str) {
            return 2;
        } else if ("free" == $str) {
            return 3;
        } else if ("" == $str) {
            return 0;
        } else if ("inapp" == $str) {
            return 4;
        } else {
            return -1;
        }
    }

	public function pageGetDiscountCodeByOrder(){
		$ret = new stdclass;
        $ret->code = 1;
        $ret->msg  = "";
		$params = SJson::decode(utility_net::getPostData(),true);
        if (empty($params['orderId'])) {
			$ret->code = -1;
			$ret->msg  = "params error";
        }
		
		$db  = new course_db;
        $res = $db->getDiscountCodeByOrder($params['orderId']);
        if (empty($res->items)) {
			$ret->code = -1;
			$ret->msg  = "data empty";
        }
		$ret->data = $res->items;
		return $ret;
	}
}
