<?php
class course_discount{
	public function __construct($inPath){
		return;
	}
	public function pageCreate($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$course_db = new course_db();
		$params = SJson::decode(utility_net::getPostData());
		$data = new stdclass;
		$data->name = $params->name;
		$data->introduction = $params->introduction;
		$data->owner = $params->user_id;
		$data->fk_org = $params->user_id;
		$data->fk_course = $params->course_id;
		if(0 != $data->fk_course){
			$course = $course_db->getCourse($data->fk_course);
			if(!$course || $course["fk_user"]!=$params->user_id){
				$ret->result->msg = "课程[$data->fk_course]不属于机构[$params->user_id]！";
				return $ret;
			}
		}
		$data->discount_type = $params->discount_type;
		if(1!=$data->discount_type && 2!=$data->discount_type){
			$ret->result->msg = "优惠类型=[$data->discount_type],错误！";
			return $ret;
		}
		if(1==$data->discount_type){
			$v = (int)($params->discount_value*100);
			if($v <= 0){
				$ret->result->msg = "优惠金额必须大于0";
				return $ret;
			}
			$data->discount_value = $v;
		}else{
			$v = (int)$params->discount_value;
			if($v < 0 || $v > 99){
				$ret->result->msg = "打折额度必须在0-99";
				return $ret;
			}
			$data->discount_value = $v;
		}
		$v = (int)($params->min_fee*100);
		if($v <= 0){
			$ret->result->msg = "最小费用必须大于0";
			return $ret;
		}
		$data->min_fee = $v;
		$data->starttime = $params->starttime;
		$data->endtime = $params->endtime;
		if(strcmp($data->starttime, $data->endtime) >= 0){
			$ret->result->msg = "开始时间大于等于结束时间";
			return $ret;
		}
		$db_ret = $course_db->addDiscount($data);
		if($db_ret){
			$ret->result->code = 0;
		}
		return $ret;
	}
	public function pageListByOrg($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$limit = empty($params->limit)?0:$params->limit;
		$page = empty($params->page)?1:$params->page;
		$data = $course_db->getDiscountsByOrg($params->user_id, $limit, $page);
		if(empty($data->items)){
			$ret->total = 0;
			return $ret;
		}
		foreach($data->items as $i=>&$item){
			if(0 == $item["course_id"]){
				$item["course_name"] = "所有课程";
			}else{
				$course = $course_db->getCourse($item["course_id"]);
				if($course){
					$item["course_name"] = $course["title"];
				}else{
					$item["course_name"] = "数据库无记录";
				}
			}
		}
		$ret->data = $data->items;
		$ret->total = $data->totalSize;
		$ret->result->code = 0;
		return $ret;
	}
	public function pageForbid($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->forbidDiscount($params->user_id, $params->discount_id);
		if(false !== $data){
			$ret->result->code = 0;
		}
		return $ret;
	}
	public function pageRecover($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->recoverDiscount($params->user_id, $params->discount_id);
		if(false !== $data){
			$ret->result->code = 0;
		}
		return $ret;
	}
	public function pageForbidDiscountCode($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->forbidDiscountCode($params->user_id, $params->discount_code_id);
		if(false !== $data){
			$ret->result->code = 0;
		}
		return $ret;
	}
	public function pageRecoverDiscountCode($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->recoverDiscountCode($params->user_id, $params->discount_code_id);
		if(false !== $data){
			$ret->result->code = 0;
		}
		return $ret;
	}
	public function pageListCodeByDiscountId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$discount = $course_db->getDiscountById($params->discount_id);
		if(!$discount){
			$ret->result->msg = "没有这个优惠规则";
			return $ret;
		}
		if($discount["org_id"] != $params->user_id){
			$ret->result->msg = "优惠规则[$params->discount_id]不属于机构[$params->user_id]";
			return $ret;
		}
		if(0 == $discount["course_id"]){
			$discount["course_name"] = "所有课程";
		}else{
			$course = $course_db->getCourse($discount["course_id"]);
			if($course){
				$discount["course_name"] = $course["title"];
			}else{
				$discount["course_name"] = "数据库无记录";
			}
		}
		$ret->result->code = 0;
		$ret->discount = $discount;
		$limit = empty($params->limit)?0:$params->limit;
		$page = empty($params->page)?1:$params->page;
		$data = $course_db->getDiscountCodesByDiscountId($params->discount_id, $limit, $page);
		if(empty($data->items)){
			$ret->total = 0;
			return $ret;
		}else{
			$ret->data = $data->items;
			$ret->total = $data->totalSize;
		}
		return $ret;
	}
	public function pageCreateCode($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$discount = $course_db->getDiscountById($params->discount_id);
		if(!$discount){
			$ret->result->msg = "没有这个优惠规则";
			return $ret;
		}
		if($discount["org_id"] != $params->user_id){
			$ret->result->msg = "优惠规则[$params->discount_id]不属于机构[$params->user_id]";
			return $ret;
		}
		$data = new stdclass;
		//$data->introduction = $params->introduction;
		$data->owner = $params->user_id;
		$data->fk_discount = $params->discount_id;
		$data->total_num = $params->total_num;
		$data->user_limit = $params->user_limit;
		$data->starttime = $params->starttime;
		$data->endtime = $params->endtime;
		if(strcmp($data->starttime, $data->endtime) >= 0){
			$ret->result->msg = "开始时间大于等于结束时间";
			return $ret;
		}
		if(strcmp($data->starttime, $discount["starttime"]) < 0){
			$a = $data->starttime;
			$b = $discount["starttime"];
			$ret->result->msg = "优惠码开始时间[$a]小于优惠规则开始时间[$b]";
			return $ret;
		}
		if(strcmp($data->endtime, $discount["endtime"]) > 0){
			$ret->result->msg = "优惠码结束时间大于优惠规则结束时间";
			return $ret;
		}
		$intros = split("\|", $params->introduction);
		$num = 0;
		foreach($intros as $k=>$v){
			$data->introduction = $v;
			$tmpCode = base_convert(rand(1679616, 15548445), 10, 36);
			$tmpCode = str_replace('o','@',$tmpCode);
			$tmpCode = str_replace('l','!',$tmpCode);
			$data->discount_code = $tmpCode;
			$db_ret = $course_db->addDiscountCode($data);
			if($db_ret){
				$num += 1;
			}else{
				$tmpCode = base_convert(rand(1679616, 15548445), 10, 36);
				$tmpCode = str_replace('o','@',$tmpCode);
				$tmpCode = str_replace('l','!',$tmpCode);
				$data->discount_code = $tmpCode;
				$db_ret = $course_db->addDiscountCode($data);
				if($db_ret){
					$num += 1;
				}
			}
		}
		if($num){
			$ret->result->code = 0;
		}else{
			$ret->result->msg = "不能生成新的优惠码";
		}
		return $ret;
	}
	public function pageListCodeUsedByCodeId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$discount = $course_db->getDiscountCodeByCode($params->discount_code);
		if(!$discount){
			$ret->result->msg = "没有这个优惠码";
			return $ret;
		}
		if($discount["owner"] != $params->user_id){
			$ret->result->msg = "优惠码[$params->discount_code]不属于机构[$params->user_id]";
			return $ret;
		}
		$ret->result->code = 0;
		$ret->discount = $discount;
		$limit = empty($params->limit)?0:$params->limit;
		$page = empty($params->page)?1:$params->page;
		$data = $course_db->getDiscountCodeUsedsAllByCodeId($discount["discount_code_id"], $limit, $page);
		if(empty($data->items)){
			$ret->total = 0;
			return $ret;
		}else{
			$user_db = new user_db;
			foreach($data->items as $k=>&$v){
				$user = $user_db->getUser($v["user_id"]);
				if($user){
					$v["name"] = $user["name"];
				}else{
					$v["name"] = "no name";
				}
				$mobile = $user_db->getUserMobileByID($v["user_id"]);
				if($mobile){
					$v["mobile"] = $mobile["mobile"];
				}else{
					$v["mobile"] = "no mobile";
				}
				$order = order_api::getOrderAndContent($v["order_id"]);
				if($order){
					$v["price"] = $order["price"];
					$v["price_old"] = $order["price_old"];
					$v["pay_type"] = $order["pay_type"];
					$course = $course_db->getCourse($order["course_id"]);
					if($course){
						$v["title"] = $course["title"];
					}else{
						$v["title"] = "no title";
					}
				}else{
					$v["pay_type"] = 0;
					$v["price"] = 0;
					$v["price_old"] = 0;
					$v["title"] = "no order";
				}
			}
			$ret->data = $data->items;
			$ret->total = $data->totalSize;
		}
		return $ret;
	}
	public function pageGetDiscountCodeUsed($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->getDiscountCodeUsedByOrderIdOk($params->order_id);// var_dump($data);
		if(!$data){
			return $ret;
		}
		$data2 = $course_db->getDiscountCodeById($data["discount_code_id"]);// var_dump($data2);
		if(!$data2){
			return $ret;
		}
		$data["discount_code"] = $data2["discount_code"];
		$ret->data = $data;
		return $ret;
	}
	public function pageUseDiscountCode($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		//得到订单
		$order_info = order_api::getOrderAndContent($params->unique_order_id);
		if(!$order_info){
			$ret->result->msg = "没有[$params->unique_order_id]订单";
			return $ret;
		}
		/*if(0!=$order_info["price_old"] && $order_info["price_old"]!=$order_info["price"]){
			$ret->result->msg = "[$params->unique_order_id]订单已经有优惠了";
			return $ret;
		}*/
		//得到优惠码记录
		$discount_code = $course_db->getDiscountCodeByCode($params->discount_code);
		if(!$discount_code){
			//$ret->result->msg = "没有[$params->discount_code]优惠码";
			$ret->result->msg = "优惠码输入错误";
			$ret->result->code = -2;
			return $ret;
		}
		//优惠码禁用
		if(0 != $discount_code["status"]){
			$ret->result->msg = "优惠码禁用";
			$ret->result->code = -3;
			return $ret;
		}
		//检查优惠码是否还有剩余优惠
		if($discount_code["total_num"] > 0 && $discount_code["total_num"] <= $discount_code["used_num"]){
			$ret->result->msg = "优惠已经使用完了";
			$ret->result->code = -3;
			return $ret;
		}
		//检查优惠码时间限制
		$today = date("Y-m-d H:i:s");
		/*if(strcmp($today, $discount_code["starttime"])<0){
			$ret->result->msg = "优惠还没有开始呢";
			$ret->result->code = -3;
			return $ret;
		}*/
		if(strcmp($today, $discount_code["endtime"])>0){
			$ret->result->msg = "优惠过期了";
			$ret->result->code = -3;
			return $ret;
		}
		//得到优惠码对应的优惠规则
		$discount = $course_db->getDiscountById($discount_code["discount_id"]);
		if(!$discount){
			$ret->result->msg = "优惠码[$params->discount_code]没有对应的优惠";
			return $ret;
		}
		//优惠规则禁用
		if(0 != $discount["status"]){
			$ret->result->msg = "优惠规则禁用";
			$ret->result->code = -3;
			return $ret;
		}
		/*if(strcmp($today, $discount["starttime"])<0){
			$ret->result->msg = "优惠还没有开始呢";
			$ret->result->code = -3;
			return $ret;
		}*/
		/*if(strcmp($today, $discount["endtime"])>0){
			$ret->result->msg = "优惠过期了";
			$ret->result->code = -3;
			return $ret;
		}*/
		//得到订单的原始价格
		$old_price = $order_info["price_old"];
		if(0 == $old_price){
			$old_price = $order_info["price"];
		}
		//检查优惠的价格限制
		if($old_price < $discount["min_fee"]){
			$b = $discount['min_fee'];
			$ret->result->msg = "课程不能使用这个优惠";
			$ret->result->code = -3;
			return $ret;
		}
		//检查课程是否是优惠规则的机构设立的
		if(0 == $discount["course_id"]){
			if(0 != $discount["org_id"]){
				$course = $course_db->getCourse($order_info["course_id"]);
				if(!$course || $course["fk_user"]!=$discount["org_id"]){
					$ret->result->msg = "课程不能使用这个优惠";
					$ret->result->code = -3;
					return $ret;
				}
			}
		}else if($discount["course_id"] != $order_info["course_id"]){
			$ret->result->msg = "课程不能使用这个优惠";
			$ret->result->code = -3;
			return $ret;
		}
		//检查已经使用的优惠码情况
		$used = $course_db->getDiscountCodeUsedsByCodeIdUserIdOk($discount_code["discount_code_id"], $params->user_id, 1, 1);
		if($discount_code["user_limit"] > 0 && $used->totalSize >= $discount_code["user_limit"]){
			$ret->result->msg = "本用户使用这个优惠码达到限制了";
			$ret->result->code = -3;
			return $ret;
		}
		//抢占一个优惠码优惠
		$course_db->updateUsedNumForDiscountCodeById($discount_code["discount_code_id"], 1);
		$discount_code = $course_db->getDiscountCodeById($discount_code["discount_code_id"]);
		if(!$discount_code || ($discount_code["total_num"] > 0 && $discount_code["total_num"] < $discount_code["used_num"])){
			$course_db->updateUsedNumForDiscountCodeById($discount_code["discount_code_id"], -1);
			$ret->result->msg = "优惠已经使用完了";
			$ret->result->code = -3;
			return $ret;
		}
		//得到订单优惠码使用情况
		$used = $course_db->getDiscountCodeUsedByOrderId($order_info["order_id"]);
		if($used){	//已使用优惠码，修改
			if($used["discount_code_id"] == $discount_code["discount_code_id"]){
				$ret->result->msg = "优惠码重复";
				$ret->result->code = -3;
				return $ret;
			}
			$u = $course_db->updateDiscountCodeForUsed($order_info["order_id"], $discount_code["discount_code_id"]);
			if(!$u){
				$ret->result->msg = "订单有问题";
				return $ret;
			}
			$used2 = $course_db->getDiscountCodeUsedsByCodeId($used["discount_code_id"], 1, 1);
			$course_db->setUsedNumForDiscountCodeById($used["discount_code_id"], $used2->totalSize);
		}else{	//没有使用，加记录
			$data = new stdclass;
			$data->fk_order = $order_info["order_id"];
			$data->fk_discount_code = $discount_code["discount_code_id"];
			$data->fk_user = $params->user_id;
			$a = $course_db->addDiscountCodeUsed($data);//print "add result:";var_dump($a);print "\nadd finish\n";
			if(false === $a){
				$course_db->updateUsedNumForDiscountCodeById($discount_code["discount_code_id"], -1);
				//$ret->result->msg = "有人同时提交订单了";
				$ret->result->msg = "已经使用优惠码了";
				return $ret;
			}
		}
		//再次检查优惠码使用次数
		/*$used = $course_db->getDiscountCodeUsedsByCodeIdUserIdOk($discount_code["discount_code_id"], $params->user_id, 1, 1);
		if($used->totalSize > $discount_code["user_limit"]){
			$course_db->updateUsedNumForDiscountCodeById($discount_code["discount_code_id"], -1);
			$ret->result->msg = "用户使用优惠码次数过多";
			$course_db->setStatusForDiscountCodeUsedByOrderId($order_info["order_id"], 2);
			return $ret;
		}*/
		//修改订单价格
		if(1 == $discount["discount_type"]){
			$new_price = $old_price - $discount["discount_value"];
			if($new_price < 0){
				$new_price = 0;
			}
		}else{
			$new_price = (int)($old_price * $discount["discount_value"] / 100 + 0.5);
		}
		order_db_orderDao::setPriceForFeeOrderByOrderId($order_info["order_id"], $old_price, $new_price, md5($order_info["out_trade_id"] . $params->discount_code));
		//新订单
		$newOrderContentData = ['price'=>$new_price,'price_old'=>$old_price,'discount_status'=>1];
		$newOrderData        = ['price'=>$new_price,'price_old'=>$old_price,'out_trade_id'=>md5($order_info["out_trade_id"].$params->discount_code)];
		order_db_orderContentDao::update($order_info["order_id"],$newOrderContentData);
		order_db_orderDao::update($order_info["order_id"],$newOrderData);;
		
		$ret->result->code = 0;
		return $ret;
	}
	public function pageGetCoursesByOrg($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->getCoursesByOrg($params->user_id);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}
	public function pageGetFeeCoursesByOrg($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->getFeeCoursesByOrg($params->user_id);
		if(empty($data->items)) return $ret;
		
		foreach($data->items as &$v){
			$v['price'] = ($v['price'] / 100);
		}
		$ret->data = $data->items;
		return $ret;
	}
	public function pageConfirmDiscountCode($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$data = $course_db->setStatusForDiscountCodeUsedByOrderId($params->order_id, 3);
		if(empty($data->items)){
			return $ret;
		}
		$ret->data = $data->items;
		return $ret;
	}
	public function pageCancelDiscountCode($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$order_info = order_api::getOrderAndContent($params->order_id);
		$used = $course_db->getDiscountCodeUsedByOrderId($params->order_id);
		if(!$order_info || !$used){
			return $ret;
		}
		if(0 != $used["status"]){
			$ret->result->msg = "不能取消";
			return $ret;
		}
		$course_db->deleteUsedByOrderId($params->order_id);
		$old_price = $order_info["price_old"];
		if($old_price){
			$new_price = $old_price;
		}else{
			$new_price = $order_info["price"];
		}
		order_db_orderDao::setPriceForFeeOrderByOrderId($params->order_id, $old_price, $new_price, $order_info["unique_order_id"]);
		//新订单
		$newContentOrderData = ['price'=>$new_price,'price_old'=>$old_price,'discount_status'=>0];
		$newOrderData        = ['price'=>$new_price,'price_old'=>$old_price,'out_trade_id'=>$order_info["unique_order_id"]];
		order_db_orderContentDao::update($params->order_id,$newContentOrderData);
		order_db_orderDao::update($params->order_id,$newOrderData);
		
		$used2 = $course_db->getDiscountCodeUsedsByCodeId($used["discount_code_id"], 1, 1);
		$course_db->setUsedNumForDiscountCodeById($used["discount_code_id"], $used2->totalSize);
		$ret->result->code = 0;
		return $ret;
	}
	//------------------------ v2 ---------------------------
	public function pageCreateV2($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$course_db = new course_db();
		$params = SJson::decode(utility_net::getPostData());
		$data = new stdclass;
		if(empty($params->name)){
			$ret->result->msg = "没有规则名";
			return $ret;
		}
		$data->name = $params->name;
		$data->owner = $params->user_id;
		$data->fk_org = $params->user_id;
		$data->fk_course = $params->course_id;
		if(0 != $data->fk_course){
			$course = $course_db->getCourse($data->fk_course);
			if(!$course || $course["fk_user"]!=$params->user_id){
				$ret->result->msg = "课程[$data->fk_course]不属于机构[$params->user_id]！";
				return $ret;
			}
		}
		$data->discount_type = $params->discount_type;
		if(1!=$data->discount_type && 2!=$data->discount_type){
			$ret->result->msg = "优惠类型=[$data->discount_type],错误！";
			return $ret;
		}
		if(1==$data->discount_type){
			$v = (int)($params->discount_value*100);
			if($v <= 0){
				$ret->result->msg = "优惠金额必须大于0";
				return $ret;
			}
			$data->discount_value = $v;
		}else{
			$v = (int)($params->discount_value*10);
			if($v < 0 || $v > 99){
				$ret->result->msg = "打折额度必须在0-9.9";
				return $ret;
			}
			$data->discount_value = $v;
		}
		$v = (int)($params->min_fee*100);
		if($v <= 0){
			$ret->result->msg = "最小费用必须大于0";
			return $ret;
		}
		$data->min_fee = $v;
		$data->total_num = $params->total_num;
		$data->user_limit = $params->user_limit;
		$data->duration = $params->duration*86400;
		$db_ret = $course_db->addDiscountV2($data);
		if($db_ret){
			$ret->result->code = 0;
			if(!empty($params->create_code) && $params->create_code){
				$this->createCodeV2($db_ret, $params->user_id, 50);
			}
		}
		return $ret;
	}
	public function pageListByOrgV2($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$limit = empty($params->limit)?0:$params->limit;
		$page = empty($params->page)?1:$params->page;
		$data = $course_db->getDiscountsByOrgV2($params->user_id, $limit, $page);
		if(empty($data->items)){
			$ret->total = 0;
			return $ret;
		}
		foreach($data->items as $i=>&$item){
			if(0 == $item["course_id"]){
				$item["course_name"] = "所有课程";
			}else{
				$course = $course_db->getCourse($item["course_id"]);
				if($course){
					$item["course_name"] = $course["title"];
				}else{
					$item["course_name"] = "数据库无记录";
				}
			}
		}
		$ret->data = $data->items;
		$ret->total = $data->totalSize;
		$ret->result->code = 0;
		return $ret;
	}
	public function createCodeV2($discount_id, $user_id, $num){
		$course_db = new course_db;
		$discount = $course_db->getDiscountByIdV2($discount_id);
		if(!$discount){
			return -1;
		}
		if($discount["org_id"] != $user_id){
			return -2;
		}
		if(0 != $discount["status"]){
			return -3;
		}
		$data = new stdclass;
		$data->owner = $user_id;
		$data->fk_discount = $discount_id;
		$data->total_num = $discount["total_num"];
		$data->user_limit = $discount["user_limit"];
		$data->starttime = date("Y-m-d");
		if(0 == $discount["duration"]){
			$data->endtime = "2035-01-01";
		}else{
			$t = time() + $discount["duration"];
			$data->endtime = date("Y-m-d", $t);
		}
		for($i=0;$i<$num;$i++){
			$tmpCode = base_convert(rand(1679616, 15548445), 10, 36);
			$tmpCode = str_replace('o','@',$tmpCode);
			$tmpCode = str_replace('l','!',$tmpCode);
			$data->discount_code = $tmpCode;
			$db_ret = $course_db->addDiscountCode($data);
			if(!$db_ret){
				$tmpCode = base_convert(rand(1679616, 15548445), 10, 36);
				$tmpCode = str_replace('o','@',$tmpCode);
				$tmpCode = str_replace('l','!',$tmpCode);
				$data->discount_code = $tmpCode;
				$db_ret = $course_db->addDiscountCode($data);
			}
		}
		return 0;
	}
	public function pageListCodeByDiscountIdV2($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		$discount = $course_db->getDiscountById($params->discount_id);
		if(!$discount){
			$ret->result->msg = "没有这个优惠规则";
			return $ret;
		}
		if($discount["org_id"] != $params->user_id){
			$ret->result->msg = "优惠规则[$params->discount_id]不属于机构[$params->user_id]";
			return $ret;
		}
		if(0 == $discount["course_id"]){
			$discount["course_name"] = "所有课程";
		}else{
			$course = $course_db->getCourse($discount["course_id"]);
			if($course){
				$discount["course_name"] = $course["title"];
			}else{
				$discount["course_name"] = "数据库无记录";
			}
		}
		$ret->result->code = 0;
		$ret->discount = $discount;
		$data = $course_db->getDiscountCodesByDiscountId($params->discount_id, 0, 1);
		if(empty($data->items)){
			$ret->total = 0;
			return $ret;
		}else{
			$limit = empty($params->limit)?0:$params->limit;
			$page = empty($params->page)?0:$params->page-1;
			$used = 0;
			$total = 0;
			foreach($data->items as $i){
				if($total>=0){
					if(0==$i["total_num"]){
						$total = -1;
					}else{
						$total += $i["total_num"];
					}
				}
				$used += $i["used_num"];
			}
			$ret->total_num = $total;
			$ret->used = $used;
			//$ret->data = $data->items;
			$ret->total = $data->totalSize;
			if(0 == $limit){
				$ret->data = $data->items;
			}else{
				if($page*$limit >= $ret->total){
					if($limit >= $ret->total){
						$ret->data = $data->items;
					}else{
						$ret->data = array_slice($data->items, -$limit);
					}
				}else{
					$ret->data = array_slice($data->items, $page*$limit, $limit);
				}
			}
		}
		return $ret;
	}
	public function pageListCodeUsedV2($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		if(empty($params->page)){
			$page = 1;
		}else{
			$page = $params->page;
		}
		if(empty($params->limit)){
			$limit = 10;
		}else{
			$limit = $params->limit;
		}
		$code = $course_db->getDiscountCodeByCode($params->code);
		if(empty($code)){
			$ret->result->msg = "no code";
			return $ret;
		}
		$ret->code = $code;
		if($params->user_id != $code["owner"]){
			$ret->result->msg = "userid is error";
			return $ret;
		}
		$data = $course_db->getDiscountCodeUsedsByCodeId($code["discount_code_id"], $limit, $page);
		if(empty($data->items)){
			return $ret;
		}
		$user_ids = array();
		foreach($data->items as $i){
			$user_ids[$i["user_id"]] = "不知道";
		}
		$users = user_db::listUsersByUserIds(implode(",", array_keys($user_ids)));
		foreach($users->items as $i){
			$user_ids[$i["pk_user"]] = $i["name"];
		}
		foreach($data->items as &$i){
			$i["name"] = $user_ids[$i["user_id"]];
		}
		$ret->total = $data->totalSize;
		$ret->result->code = 0;
		$ret->used = $data->items;
		return $ret;
	}

    /*
     * 通过用户id查找用户优惠券
     */
	public function pageListUserByCode($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		if(empty($params->page)){
			$page = 1;
		}else{
			$page = $params->page;
		}
		if(empty($params->limit)){
			$limit = 10;
		}else{
			$limit = $params->limit;
		}
		
		$data = $course_db->getDiscountCodeByUserId($params->user_id,$limit,$page);
		if(empty($data))
		{
			$ret->result->msg = "no code";
			return $ret;
		}
		
		$ret->total = $data->totalSize;
		$ret->result->code = 0;
		$ret->data = $data->items;
		return $ret;
	}
	
	public function pageListDiscountByIds($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		if(empty($params->page)){
			$page = 1;
		}else{
			$page = $params->page;
		}
		if(empty($params->limit)){
			$limit = 10;
		}else{
			$limit = $params->limit;
		}
		if(!empty($params->code))
		{
			$code = $params->code;
		}else
		{
			$code = '';
		}
		if(!empty($params->owner))
		{
			$owner = $params->owner;
		}else
		{
			$owner = '';
		}
		$data = $course_db->getListDiscountByIds($params->dis_ids,$limit,$page,$code,$owner);
		
		if(empty($data))
		{
			$ret->result->msg = "no code";
			return $ret;
		}
		$ret->totalPage  = $data->totalPage;
		$ret->page  = $data->page;
		$ret->total = $data->totalSize;
		$ret->result->code = 0;
		$ret->data = $data->items;
		return $ret;
	}
	
	public function pagegetDiscountByIds($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$course_db = new course_db;
		if(empty($params->page)){
			$page = 1;
		}else{
			$page = $params->page;
		}
		if(empty($params->limit)){
			$limit = 10;
		}else{
			$limit = $params->limit;
		}
		
		$data = $course_db->getDiscountByIds($params->discountid,$limit,$page);
		if(empty($data))
		{
			$ret->result->msg = "no code";
			return $ret;
		}
		$ret->total = $data->totalSize;
		$ret->result->code = 0;
		$ret->data = $data->items;
		return $ret;
	}
	
	
	public function pageCreateCodeV2($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$result = $this->createCodeV2($params->discount_id, $params->user_id, $params->num);
		if(0 == $result){
			$ret->result->code = 0;
		}else{
			$ret->result->code = $result;
			$ret->result->msg = "错误";
		}
		return $ret;
	}

    public function pageGetCodeInfo($inPath)
    {
        if (!isset($inPath[3]) || !(int)($inPath[3])) return api_func::setMsg(1000);

        $discountCodeUsedInfo = course_db_discountCodeUsedDao::getDiscountCodeUsedInfo((int)($inPath[3]));
        if (empty($discountCodeUsedInfo)) return api_func::error(20016, '该使用的优惠码信息不存在');

        $discountCodeInfo = course_db_discountCodeDao::getDiscountCodeInfo($discountCodeUsedInfo['fk_discount_code']);
        if (empty($discountCodeInfo)) return api_func::error(20017, '该优惠码信息不存在');

        $discountInfo = course_db_discountDao::getDiscountInfo($discountCodeInfo['fk_discount']);
        if (empty($discountInfo)) return api_func::error(20018, '该优惠码的优惠信息不存在');

        return api_func::setData(
            [
                'discountCodeUsedInfo' => $discountCodeUsedInfo,
                'discountCodeInfo'     => $discountCodeInfo,
                'discountInfo'         => $discountInfo
            ]
        );
    }

    public function pageCheckDiscountCode($inPath)
	{
		if (!isset($inPath[3])) return api_func::setMsg(1000);

		$res = course_db_discountCodeDao::checkDiscountCode(trim($inPath[3]));

		if (empty($res->items)) return api_func::setMsg(3002);

		return api_func::setData($res->items);
	}

    /**
     * @link https://wiki.gn100.com/doku.php?id=docs:api:promocode
     *
     * @return array
     */
    public function pageCheckDiscountCodeValidV2()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $orderId      = empty($params['orderId']) ? 0 : $params['orderId'];
        $userId       = empty($params['userId']) ? 0 : $params['userId'];
        $discountCode = empty($params['discountCode']) ? 0 : $params['discountCode'];

        if (!$orderId || !$userId || !$discountCode) {
            return api_func::setMsg(1000);
        }

        $res = course_discount_api::checkDiscountCode($userId, $orderId, $discountCode);

        if (!empty($res['code'])) {
            return api_func::error($res['code'], $res['msg']);
        }

        return api_func::setData($res);
    }

    /**
     * @link https://wiki.gn100.com/doku.php?id=docs:api:promocode
     *
     * @return array
     */
    public function pageUseDiscountCodeV2()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $orderId      = empty($params['orderId']) ? 0 : $params['orderId'];
        $userId       = empty($params['userId']) ? 0 : $params['userId'];
        $discountCode = empty($params['discountCode']) ? 0 : $params['discountCode'];

        if (!$orderId || !$userId || !$discountCode) {
            return api_func::setMsg(1000);
        }

        $res = course_discount_api::useDiscountCode($userId, $orderId, $discountCode);

        if (!empty($res['code'])) {
            return api_func::error($res['code'], $res['msg']);
        }

        return api_func::setData($res);
    }

}
