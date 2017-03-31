<?PHP

class order_info
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
	
	public function pageList($inPath)
	{
		$page   = !empty($inPath[3]) ? (int)$inPath[3] : 1;
		$length = !empty($inPath[4]) ? (int)$inPath[4] : 10;
		
		$params = SJson::decode(utility_net::getPostData(),true);
		$data   = array();

		if(!empty($params['orgId'])){
			$data['orgId'] = (int)$params['orgId'];
		}
		if(!empty($params['orderSn'])){
			$data['orderSn'] = $params['orderSn'];
		}
		if(!empty($params['price'])){
			$data['price'] = $params['price'];
		}
		if(!empty($params['time'])){
			$data['time'] = $params['time'];
		}
		if(!empty($params['isFavorable'])){
			$data['isFavorable'] = (int)$params['isFavorable'];
		}
		if(!empty($params['status'])){
			$data['status'] = (int)$params['status'];
		}
		if(!empty($params['userId'])){
			$data['userId'] = (int)$params['userId'];
		}
        if(!empty($params['orderType'])){
            $data['objectType'] = (int)$params['orderType'];
        }
		

		if(empty($data)){
			return $this->setAjaxResult('-1','params empty');
		} 

		$orderRes = order_db::getOrderInfo($data,$page,$length);
		
		$order_info_time_items = $orderRes->items;
		if(empty($order_info_time_items)){
			return $this->setAjaxResult('-1','data empty');
		}

		$course_api  = new course_api;
		$course_db   = new course_db;
		$time_now    = strtotime("now");
		$courseIdArr = array();
		$memberIdArr = array();
		$classIdArr  = array();
		$update_order_ids = array();
        foreach ($order_info_time_items as $k => $v) {
			if($v['object_type'] == 1){
				$courseIdArr[$v['object_id']] = $v['object_id'];
				$classIdArr[$v['ext']] = $v['ext'];
			}elseif($v['object_type'] == 11){
				$memberIdArr[$v['object_id']] = $v['object_id'];
			}
			
            $time_exp = strtotime($order_info_time_items[$k]['expiration_time']);
            $status_info = $order_info_time_items[$k]['status'];
            if ($status_info != -2) {
                if ($status_info == -4 || $status_info == 2 || $status_info == -1 || $status_info == -3) continue;
                if ($time_now > $time_exp) {
                    $update_order_id             = $order_info_time_items[$k]['fk_order'];
                    $update_order_info["status"] = -2;
					//释放优惠码
                    $course_api->updateDiscountCodeUsed($update_order_id, 2);
                    $update_order_ids[] = $update_order_id;
                }
            }
        }
		if(!empty($update_order_ids)){
			$update_order_info["status"] = -2;
			$update_order_info['discount_status'] = 0;
			//修改物品订单
			$orderContentDb = order_db_orderContentDao::update($update_order_ids,$update_order_info);
			//修改订单
			$orderDb = order_db_orderDao::update($update_order_ids,$update_order_info);
			
			if ($orderContentDb === false || $orderDb === false) {
				return array("result" => array("msg" => "ERROR！"));
			}
		}
		
		
		//班级信息
		$classInfo = array();
		if(!empty($classIdArr)){
			$classRes = course_class_api::getClassList($classIdArr);
			if(!empty($classRes)){
				foreach($classRes as $val){
					$classInfo[$val['pk_class']] = $val;
				}
			}
		}
		
		//课程信息
		$courseInfo = array();
		if(!empty($courseIdArr)){
			$courseRet = $course_db->getCourseByCids($courseIdArr);
			foreach($courseRet->items as $val){
				$courseInfo[$val['course_id']] = $val;
			}
		}
		
		//会员信息
		$memberInfo = array();
		if(!empty($memberIdArr)){
			$memberIds = implode(',',$memberIdArr);
			$memberListRet = user_db_orgMemberSetDao::getMemberSets($memberIds);
			if(!empty($memberListRet->items)){
				foreach($memberListRet->items as $crk=>$crv){
					$memberInfo[$crv['pk_member_set']] = $crv; 
				}
			}
		}
		
		foreach ($order_info_time_items as $k => $v) {
            $order_info_time_items[$k]['status'] = $this->status[$order_info_time_items[$k]['status']];
			$order_info_time_items[$k]['course'] = !empty($courseInfo[$v['object_id']]) ? $courseInfo[$v['object_id']] : array();
			$order_info_time_items[$k]['member'] = !empty($memberInfo[$v['object_id']]) ? $memberInfo[$v['object_id']] : array();
			$order_info_time_items[$k]['class']  = !empty($classInfo[$v['ext']]) ? $classInfo[$v['ext']] : array();
		}

		return array(
            "page"  => $orderRes->page,
            "size"  => $orderRes->pageSize,
            "total" => $orderRes->totalPage,
            "totalSize" => $orderRes->totalSize,
            "data"  => $order_info_time_items
        );
	}
	

    /**
     * ＠desc get order info by out trade id
     *
     * @param $inPath
     * @return array
     */
    public function pageGetOrderByOutTradeId($inPath)
    {
        if (empty($inPath[3])) {
            return api_func::setMsg(1000);
        }

        $orderInfo = order_db_orderDao::getOrderByOutTradeId($inPath[3]);
        if (empty($orderInfo)) return api_func::setMsg(3002);

        $orderInfo['status']    = $this->status2key($orderInfo['status']);
        $orderInfo['price']     = $orderInfo['price'] / 100;
        $orderInfo['price_old'] = $orderInfo['price_old'] / 100;

        $orderContentList = order_db_orderContentDao::rows($orderInfo['pk_order']);
        if (!empty($orderContentList->items)) {
            $orderInfo['orderContent'] = $orderContentList->items;
        }

        return api_func::setData($orderInfo);
    }

    /**
     * @desc get order and content info
     *
     * @param $inPath
     * @return array
     */
    public function pageGetOrderAndContent($inPath)
    {
        if (empty($inPath[3])) {
            return api_func::setMsg(1000);
        }

        if (is_numeric($inPath[3])) { //orderId
            $orderInfo = order_db_orderDao::row($inPath[3]);
        } else {
            $orderInfo = order_db_orderDao::row(['uniqueOrderId' => $inPath[3]]);
        }

        if (empty($orderInfo)) return api_func::setMsg(3002);

        $orderInfo['status']    = $this->status2key($orderInfo['status']);
        $orderInfo['price']     = $orderInfo['price'] / 100;
        $orderInfo['price_old'] = $orderInfo['price_old'] / 100;

        $orderContentList = order_db_orderContentDao::rows($orderInfo['pk_order']);
        if (!empty($orderContentList->items)) {
            $orderInfo['orderContent'] = $orderContentList->items;
        }

        return api_func::setData($orderInfo);
    }

    public function pageGetOrderByOrderId($inPath)
    {
        if (empty($inPath[3])) {
            return api_func::setMsg(1000);
        }

        $orderInfo = order_db_orderDao::row($inPath[3]);
        if (empty($orderInfo)) return api_func::setMsg(3002);

        $orderInfo['status']    = $this->status2key($orderInfo['status']);
        $orderInfo['price']     = $orderInfo['price'] / 100;
        $orderInfo['price_old'] = $orderInfo['price_old'] / 100;

        return api_func::setData($orderInfo);
    }

	public function setAjaxResult($code, $msg, $data=array())
	{
		return json_encode(
			array(
				'code' => $code,
				'msg'  => $msg,
				'data' => $data
			),
			JSON_UNESCAPED_UNICODE
		);
    }
}
