<?PHP

class order_feeorder
{	
	//获取单个订单信息 
	public function pageGetOrderInfo($inPath)
	{
		$data = array();
		$params = SJson::decode(utility_net::getPostData(),true);
		if(!empty($params['orderId'])){
			$data['orderId'] = (int)$params['orderId'];
		}
		if(!empty($params['uniqueOrderId'])){
			$data['uniqueOrderId'] = $params['uniqueOrderId'];
		}
		if(empty($data)){
			return order_api::setMsg('-1','params error');
		}
		
		$orderInfo = order_db_orderDao::row($data);
		if(empty($orderInfo)){
			return order_api::setMsg('-2','data empty');
		}
		
		$orderInfo['status']    = order_api::$status2pay[$orderInfo['status']];
		$orderInfo['price']     = $orderInfo['price']/100;
		$orderInfo['price_old'] = $orderInfo['price_old']/100;
		return order_api::setMsg("1","success",$orderInfo);
	}
	
	//获取订单列表
	public function pageOrderList($inPath)
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
		if(isset($params['isFavorable'])){
			$data['isFavorable'] = (int)$params['isFavorable'];
		}
		if(isset($params['status'])){
			$data['status'] = $params['status'];
		}
		if(!empty($params['userId'])){
			$data['userId'] = $params['userId'];
		}
        if(!empty($params['orderType'])){
            $data['objectType'] = (int)$params['orderType'];
        }
        if(!empty($params['objectId'])){
            $data['objectId'] = $params['objectId'];
        }
        if(!empty($params['resell'])){
            $data['resell'] = $params['resell'];
        }

		if(empty($data)){
			return array('code'=>-1,'msg'=>'params error','data'=>array());
		} 
		$orderRes = order_db::getOrderInfo($data,$page,$length);

		if(empty($orderRes->items)){
			return array('code'=>-1,'msg'=>'data error','data'=>array());
		}

		$orderInfo = $orderRes->items;
		//释放优惠码
		$course_api = new course_api;
		$course_db  = new course_db;
		$timeNow    = strtotime("now");
		$updateOrderIdArr = array();
		foreach ($orderInfo as $k=>$v) {
			$timeExp = strtotime($v['expiration_time']);
			if($v['status'] != '-2'){
				if($v['status'] == '-4' || $v['status'] == '2' || $v['status'] == '-1' || $v['status'] == '-3') continue;
				
				if($timeNow > $timeExp){
					$course_api->updateDiscountCodeUsed($v['fk_order'], 2);
					$updateOrderIdArr[] = $v['fk_order'];
				}
			}
        }
		
		//修改订单 (支付状态/优惠码状态)
		if(!empty($updateOrderIdArr)){
            $updateOrderInfo = ['status'=>'-2','discount_status'=>0];
            $orderIds = implode(',',$updateOrderIdArr);
            $orderContentDb  = order_db_orderContentDao::update(array('orderId'=>$orderIds),$updateOrderInfo);
            $orderDb         = order_db_orderDao::update($updateOrderIdArr,$updateOrderInfo);
            if ($orderContentDb === false || $orderDb === false) {
                return array("result" => array("msg" => "ERROR！"));
            }
        }

		foreach($orderInfo as &$val){
			$val['status'] = order_api::$status2pay[$val['status']];
		}

		return array(
            "page"  => $orderRes->page,
            "size"  => $orderRes->pageSize,
            "total" => $orderRes->totalPage,
            "totalSize" => $orderRes->totalSize,
            "data"      => $orderInfo
        ); 
	}
	
	//生成订单 
	public function pageAdd()
	{
		$params = SJson::decode(utility_net::getPostData(),true);
		if(empty($params['userId']) || empty($params['data']) || empty($params['orderPriceOld'])){
			return array('result'=>array('code'=>-1,'msg'=>'params error'));
		}
		
		//插入t_order
		$orderData = [
			'fk_user'         => (int)$params['userId'],
			'price'           => ($params['orderPrice'] > 0) ? $params['orderPrice'] * 100 : 0,
			'price_old'       => ($params['orderPriceOld'] > 0) ? $params['orderPriceOld'] * 100 : 0,
			'discount_status' => !empty($params['disStatus']) ? 1 : 0,
			'status'          => 0,
			'unique_order_id' => md5($params['userId']."+".time()."+".rand(0,19999999)),
			'expiration_time' => date('Y-m-d H:i:s', time() + 3600),
			'create_time'     => date('Y-m-d H:i:s', time())
		];

		$orderData['out_trade_id'] = $orderData['unique_order_id'];
		$orderId = order_db_orderDao::add($orderData);
		if($orderId){
			//插入t_order_content
			foreach($params['data'] as $val){
				$orderContentData = [
					'fk_order'    => $orderId,
					'org_id'      => $val['orgId'],
					'fk_user'     => $val['userId'],
					'object_type' => $val['objectType'],
					'object_id'   => $val['objectId'],
					'price'       => ($val['price'] > 0) ? $val['price'] * 100 : 0,
					'price_old'   => ($val['priceOld'] > 0) ? $val['priceOld'] *100 : 0,
					'ext'         => $val['ext'],
					'create_time' => date('Y-m-d H:i:s', time()),
					'price_promote'   => !empty($val['pricePromote']) ? $val['pricePromote'] * 100 : 0,
					'resell_org_id'   => !empty($val['resellOrgId']) ? $val['resellOrgId'] : 0,
					'promote_status'  => !empty($val['promoteStatus']) ? $val['promoteStatus'] : 0
				];
				$orderContentRes = order_db_orderContentDao::add($orderContentData);
			}
			//添加优惠码使用记录
			if(!empty($params['disStatus'])){
				$courseDb = new course_db;
				$usedData = [
					'fk_order' => $orderId,
					'fk_user'  => (int)$params['userId'],
					'fk_discount_code' => $params['disStatus']
				];
				$addUsedRes = $courseDb->addDiscountCodeUsed($usedData);
				if(false === $addUsedRes){
					$courseDb->updateUsedNumForDiscountCodeById($params['disStatus'], -1);
				}
			}
			
			if($orderContentRes){
				return array(
					'result' => array('code'=>0),
					'data'   => array(
						'order_id'        => $orderId,
						'unique_order_id' => $orderData['unique_order_id'],
						'expiration_time' => $orderData['expiration_time']
					)
				);
			}
		}
		
		return array('result'=>array('code'=>-2,'msg'=>'add order error'));
	}
	
	//修改订单
	public function pageUpdate($inPath)
	{
		if(empty($inPath[3])){
			return order_api::setMsg('-1','params error');
		}
		$orderId = (int)$inPath[3];
		
		$params = SJson::decode(utility_net::getPostData(),true);

		$data = array();
		if(!empty($params['price'])){
			$data['price'] = $params['price'] * 100;
			$orderContent['price'] = $params['price'] * 100;
		}
		if(!empty($params['pay_type'])){
			$data['pay_type'] = order_api::payType($params['pay_type']);
		}
		if(!empty($params['status'])){
			$data['status'] = order_api::$pay2status["{$params['status']}"];
            $orderContent['status'] = order_api::$pay2status["{$params['status']}"];
        }
		if(!empty($params['callback_status'])){
			$data['callback_status'] = $params['callback_status'];
		}
		if(!empty($params['third_return_params'])){
			$data['third_return_params'] = $params['third_return_params'];
		}
		if(!empty($params['third_order_id'])){
			$data['third_order_id'] = $params['third_order_id'];
		}
		if(empty($data) || empty($orderContent)){
			return order_api::setMsg('-1','params error');
		}
        
		$courseRes = new course_api;
		if(2 == $data['status']){
			//优惠码状态
			$courseRes->updateDiscountCodeUsed($orderId, 1);
			//结算
			$orderContentRes = order_db_orderContentDao::rows($orderId);
			if(!empty($orderContentRes->items)){
				$courseIdArr = array();
				$courseInfo  = array();
				
				foreach($orderContentRes->items as $val){
					if($val['object_type'] == 1){
						$courseIdArr[] = $val['object_id'];
					}
				}
				//获取已完结的课程
				if(!empty($courseIdArr)){
					$courseIds = implode(',', $courseIdArr);
					$courseRes = course_db::getstudentCount($courseIds);
					foreach($courseRes->items as $val){
						if($val['status'] == 3){
							$courseInfo[$val['pk_course']] = $val['type'];
						}
					}
				}
				//结算记录 t_organization_account_order_content
				$incomeAllPrice = 0;
                $orgAccountOrderRsell = [];    // 分销帐户信息 
				$time = date('Y-m-d H:i:s');
				foreach($orderContentRes->items as $val){
                    $orgAccountOrderContentExt = '';
                    $resell_org_id = (int) $val['resell_org_id'];  // 分销帐户信息
                    if(!empty($val['resell_org_id'])){
                        $resellData = [
                            'resell_org_id'=>$resell_org_id,
                            'price_promote'=>$val['price_promote'],
                            'price_resell'=>$val['price'] - $val['price_promote']                   
                        ];
                        $orgAccountOrderContentExt = json_encode($resellData); 
                    }
                    
					$accountLog = [
						'fk_order_content' => $val['pk_order_content'],
						'order_id'         => $orderId,
						'fk_org'           => $val['org_id'],
                        'resell_org_id'    => $resell_org_id,
						'balance'          => $val['price'],
						'income_all'       => $val['price'],
                        'ext'              => $orgAccountOrderContentExt,
						'status'           => 1,
						'type'             => $val['object_type'],
						'create_time'      => $time
					];
					//课程
					if($val['object_type'] == 1){
						if(!empty($courseInfo[$val['object_id']])){
							$accountLog['withdraw'] = $val['price'];
							$accountLog['status']   = 2;
							$accountLog['withdraw_time'] = date('Y-m-d H:i:s');
						}else{
							$accountLog['withdraw'] = 0;
						}
					}elseif($val['object_type'] == 11){
						//会员
						$accountLog['withdraw'] = $val['price'];
						$accountLog['status']   = 2;
						$accountLog['withdraw_time'] = date('Y-m-d H:i:s');
					}
					
					// 分销帐户信息 
					if(empty($val['resell_org_id'])){
                        $incomeAllPrice += $val['price'];
                    } else {
                        $incomeAllPrice += $val['price_promote'];                                   // 推广商收入
                        if(isset($orgAccountOrderRsell[$resell_org_id]['income_all'])){             // 分销商收入
                            $orgAccountOrderRsell[$resell_org_id]['income_all'] += $resellData['price_resell'];
                        } else {
                            $orgAccountOrderRsell[$resell_org_id]['income_all'] = $resellData['price_resell'];
                        }
                        $orgAccountOrderRsell[$resell_org_id]['resell_org_id'] = $resell_org_id;
                    }

					$organizationAccountOrder = user_db_orgAccountOrderContentDao::add($accountLog);
					$orgId = $val['org_id'];
				}
				//机构结算
				if($organizationAccountOrder){
					$addData = [
						'fk_org'      => $orgId,
						'create_time' => $time,
						"order_count=order_count+1",
                        "balance=balance+{$incomeAllPrice}",
						"income_all=income_all+{$incomeAllPrice}",
					];
					$updateData = [
						"order_count=order_count+1",
                        "balance=balance+{$incomeAllPrice}",
						"income_all=income_all+{$incomeAllPrice}",
						"last_updated" => $time
					];
					$organizationAccount = user_db_organizationAccountDao::add($addData, $updateData);
					
                    // 分销帐户信息 20160729
                    foreach($orgAccountOrderRsell as $resell_org_id =>$resellData){
                        $addDataResell = [
                            'fk_org'      => $resellData['resell_org_id'],
                            'create_time' => $time,
                            "order_count=order_count+1",
                            "balance=balance+{$resellData['income_all']}",
                            "income_all=income_all+{$resellData['income_all']}",
                        ];
                        $updateDataResell = [
                            "order_count=order_count+1",
                            "balance=balance+{$resellData['income_all']}",
                            "income_all=income_all+{$resellData['income_all']}",
                            "last_updated" => $time
                        ];

                        $organizationAccountResell = user_db_organizationAccountDao::add($addDataResell, $updateDataResell);
                    }
                    
					if($organizationAccount === false){
						return order_api::setMsg("-1","fail");
					}
				}
			}
		}elseif($data['status'] < 0){
			$courseRes->updateDiscountCodeUsed($orderId, 2);
		}
		
		$orderConRes = order_db_orderContentDao::update(array('orderId'=>$orderId), $orderContent);
		$orderRes    = order_db_orderDao::update($orderId, $data);

		
		if($orderConRes === false || $orderRes === false){
			return order_api::setMsg("-1","fail");
		}
		
		return order_api::setMsg("1","success");
	}
	
	//订单信息
	public function pageGetOrder()
	{
		$params = SJson::decode(utility_net::getPostData(),true);
		$data   = array();
		if(!empty($params['uniqueOrderId'])){
			$data['uniqueOrderId'] = $params['uniqueOrderId'];
		}
		if(empty($data)){
			return array('code'=>-1,'msg'=>'params error','data'=>array());
		} 
		
		$ret = order_db::getOrderInfo($data,1,-1);
		if(empty($ret->items)){
			return array('code'=>-1,'msg'=>'data error','data'=>array());
		}
		foreach($ret->items as $key=>$val){
			$ret->items[$key]['status'] = order_api::$status2pay[$val['status']];
		}
		
		return $ret;
	}
}	
?>
