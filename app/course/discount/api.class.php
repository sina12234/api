<?php

class course_discount_api
{
    public static function checkDiscountCode($userId, $orderId, $discountCode, $isRob = true)
    {
        $courseDb = new course_db;

        //获取订单信息
        if (is_numeric($orderId)) {
            $orderInfo = order_api::getOrderAndContent($orderId);
        } else {
            $orderInfo = order_api::getOrderAndContent($orderId);
        }

        if (empty($orderInfo)) return api_func::error(20001, "没有[{$orderId}]订单");

        //得到优惠码记录
        $discountCodeInfo = $courseDb->getDiscountCodeByCode($discountCode);
        if (empty($discountCodeInfo)) return api_func::error(20002, "不存在该【{$discountCode}】优惠码");

        //优惠码禁用
        if (0 != $discountCodeInfo['status']) return api_func::error(20003, "优惠码禁用");

        //检查优惠码是否还有剩余优惠
        if ($discountCodeInfo["total_num"] > 0 && $discountCodeInfo["total_num"] <= $discountCodeInfo["used_num"]) {
            return api_func::error(20004, '优惠已经使用完了');
        }

        //检查优惠码时间限制
        $today = date("Y-m-d H:i:s");

        if (strcmp($today, $discountCodeInfo["endtime"]) > 0) {
            return api_func::error(20005, '优惠过期了');
        }

        //得到优惠码对应的优惠规则
        $discountRules = $courseDb->getDiscountById($discountCodeInfo["discount_id"]);
        if (empty($discountRules)) return api_func::error(20006, "优惠码[{$discountCode}]没有对应的优惠");

        //优惠规则禁用
        if (0 != $discountRules["status"]) return api_func::error(20007, "优惠规则禁用");

        //得到订单的原始价格
        $oldPrice = $orderInfo->price_old;
        if (0 == $oldPrice) {
            $oldPrice = $orderInfo->price;
        }

        //检查优惠的价格限制
        if ($oldPrice < $discountRules["min_fee"]) return api_func::error(20008, '课程不能使用这个优惠');

        //检查课程是否是优惠规则的机构设立的
        if (0 == $discountRules["course_id"]) {
            if (0 != $discountRules["org_id"]) {
                $course = $courseDb->getCourse($orderInfo->object_id);

                if (empty($course) || $course["fk_user"] != $discountRules["org_id"]) {
                    return api_func::error(20009, '该课程不能使用这个优惠码');
                }
            }
        } elseif ($discountRules["course_id"] != $orderInfo->object_id) {
            return api_func::error(20010, '优惠课程和购买课程不符合');
        }

        //检查已经使用的优惠码情况
        $used = $courseDb->getDiscountCodeUsedsByCodeIdUserIdOk($discountCodeInfo["discount_code_id"], $userId, 1, 1);
        if ($discountCodeInfo["user_limit"] > 0 && !empty($used) && $used->totalSize > $discountCodeInfo["user_limit"]) {
            return api_func::error(20011, '本用户使用这个优惠码达到限制了');
        }

        //抢占一个优惠码优惠(默认抢占)
        if ($isRob === true) {
            $courseDb->updateUsedNumForDiscountCodeById($discountCodeInfo["discount_code_id"], 1);
        }

        $discountCodeInfo = $courseDb->getDiscountCodeById($discountCodeInfo["discount_code_id"]);
        if (!empty($discountCodeInfo) && ($discountCodeInfo["total_num"] > 0 && $discountCodeInfo["total_num"] < $discountCodeInfo["used_num"])) {
            $courseDb->updateUsedNumForDiscountCodeById($discountCodeInfo["discount_code_id"], -1);

            return api_func::error(20012, '优惠已经使用完了');
        }

        //得到订单优惠码使用情况
        $used = $courseDb->getDiscountCodeUsedByOrderId($orderInfo->order_id);
        if (!empty($used)) {    //已使用优惠码，修改
            if ($orderInfo->status == 2) {
                return api_func::error(20013, '优惠码已经使用支付订单成功');
            }

            $u = $courseDb->updateDiscountCodeForUsed($orderInfo->order_id, $discountCodeInfo["discount_code_id"]);
            if ($u === false) return api_func::error(20014, '订单有问题');

            $used2 = $courseDb->getDiscountCodeUsedsByCodeId($used["discount_code_id"], 1, 1);
            if (empty($used2->items)) {
                SLog::fatal('unknown error,discount_code_id[%d],used2[%s]', $used["discount_code_id"], var_export($used2, 1));
            }
            $courseDb->setUsedNumForDiscountCodeById($used["discount_code_id"], $used2->totalSize);
        } else {    //没有使用，加记录
            $data = [
                'fk_order'         => $orderInfo->order_id,
                'fk_discount_code' => $discountCodeInfo["discount_code_id"],
                'fk_user'          => $userId
            ];

            $addRes = $courseDb->addDiscountCodeUsed($data);
            if (false === $addRes) {
                $courseDb->updateUsedNumForDiscountCodeById($discountCodeInfo["discount_code_id"], -1);

                return api_func::error(20015, '已经使用优惠码了');
            }
        }

        if ($isRob === true) {
            // 修改订单价格
            if (1 == $discountRules["discount_type"]) {
                $newPrice = $oldPrice - $discountRules["discount_value"];
                $newPrice < 0 && $newPrice = 0;
            } else {
                $newPrice = (int)($oldPrice * $discountRules["discount_value"] / 100 + 0.5);
            }

            $outTradeId = md5($orderInfo->out_trade_id.$discountCode);
            order_db_orderDao::setPriceForFeeOrderByOrderId(
                $orderInfo->order_id,
                $oldPrice,
                $newPrice,
                $outTradeId
            );
            
            //新订单
            $newContentOrderData = ['price' => $newPrice, 'price_old' => $oldPrice, 'discount_status' => 1];
            $newOrderData        = ['price' => $newPrice, 'price_old' => $oldPrice, 'out_trade_id' => $outTradeId];
            order_db_orderContentDao::update($orderInfo->order_id, $newContentOrderData);
            order_db_orderDao::update($orderInfo->order_id, $newOrderData);
        }

        $returnData = [
            'oldPrice'         => $oldPrice,
            'orderInfo'        => $orderInfo,
            'discountRules'    => $discountRules,
            'discountCodeInfo' => $discountCodeInfo
        ];

        return $returnData;
    }

    /**
     * @desc 特定使用场景，外部不要调用
     *
     * @param $userId
     * @param $orderId
     * @param $discountCode
     * @return array
     */
    public static function useDiscountCode($userId, $orderId, $discountCode)
    {
        $res = self::checkDiscountCode($userId, $orderId, $discountCode, false);

        if (!empty($res['code'])) {
            return api_func::error($res['code'], $res['msg']);
        }

        return $res;
    }
	
	/*
	 * @desc 优惠码是否可使用
	 * @isRob 确定下单
	 */
	public static function checkCode($discountCode, $courseId, $userId, $isRob=false)
	{
		$courseDb = new course_db;
		
		$courseInfo       = $courseDb->getCourse($courseId);
		$discountCodeInfo = $courseDb->getDiscountCodeByCode($discountCode);
		if(empty($discountCodeInfo) || empty($courseInfo)){
			return api_func::error(20002, "不存在该【{$discountCode}】优惠码");
		} 
		
		if(0 != $discountCodeInfo['status']) return api_func::error(20003, '优惠码禁用');
		
		//检查优惠码是否还有剩余优惠
		if($discountCodeInfo['total_num'] > 0 && $discountCodeInfo['total_num'] <= $discountCodeInfo['used_num']){
			return api_func::error(20004, '优惠已经使用完了');
		}
		
		//检查优惠码时间限制
		$today = date("Y-m-d H:i:s");
		if(strcmp($today, $discountCodeInfo['endtime']) > 0){
			return api_func::error(20005, '优惠过期了');
		}
		
		//获取优惠码对应得优惠规则
		$discountRules = $courseDb->getDiscountById($discountCodeInfo['discount_id']);
		if(empty($discountRules)) return api_func::error(20006, "优惠码[{$discountCode}]没有对应的优惠");
		
		//优惠规则禁用
		if(0 != $discountRules['status']) return api_func::error(20007, '优惠规则禁用');
		
		//检查优惠的价格限制
		if($courseInfo['price'] < $discountRules['min_fee']) return api_func::error(20008, '课程不能使用这个优惠');
		
		//检查课程是否是优惠规则的机构设立的
		if(0 == $discountRules['course_id']){
			if(0 != $discountRules['org_id']){
				if($courseInfo['fk_user'] != $discountRules['org_id']){
					return api_func::error(20009, '该课程不能使用这个优惠码');
				}
			}
		}elseif($discountRules['course_id'] != $courseId){
			return api_func::error(20010, '优惠课程和购买课程不符合');
		}
		
		//检查已经使用的优惠码情况
		$used = $courseDb->getDiscountCodeUsedsByCodeIdUserIdOk($discountCodeInfo['discount_code_id'],$userId,1,1);
		if($discountCodeInfo['user_limit']>0 && !empty($used) && $used->totalSize > $discountCodeInfo['user_limit']){
			return api_func::error(20011, '本用户使用这个优惠码达到限制了');
		}
		
		//订单使用优惠码
		if($isRob){
			$courseDb->updateUsedNumForDiscountCodeById($discountCodeInfo['discount_code_id'], 1);
			$discountCodeInfo = $courseDb->getDiscountCodeById($discountCodeInfo["discount_code_id"]);
			if (!empty($discountCodeInfo) && ($discountCodeInfo["total_num"] > 0 && $discountCodeInfo["total_num"] < $discountCodeInfo["used_num"])) {
				$courseDb->updateUsedNumForDiscountCodeById($discountCodeInfo["discount_code_id"], -1);
				return api_func::error(20012, '优惠已经使用完了');
			}
		}

        $disPrice = 0;
		if(1 == $discountRules['discount_type']){
			$price = ($courseInfo['price'] - $discountRules['discount_value']);
            $disPrice = $discountRules['discount_value'];
		}else{
			$price = ((int)($courseInfo['price'] * $discountRules['discount_value'] /100 + 0.5));
            if($price <= 0){
                $disPrice = 0;
            }else{
                $disPrice = $courseInfo['price'] - $price;
            }
		}
		
		$data = [
			'priceOld' => $courseInfo['price']/100,
			'disPrice' => $disPrice/100,
			'price'    => $price/100,
			'discountCodeId' => $discountCodeInfo['discount_code_id']
		];
	
		return api_func::setData($data);
	}
		
}
