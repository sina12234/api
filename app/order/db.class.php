<?PHP
class order_db
{
    public static function InitDB($dbname = 'db_order', $dbtype = 'main')
    {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);
		return $db;
    }
	//已废弃
	public static function addOrder($contentValues,$orderValue)
	{
		$db = self::InitDB('db_order');
		//开始事务
		$db->execute("BEGIN");
		$values = trim($contentValues,',');
		$contentSql = "insert into  t_order_content (fk_order,org_id,fk_user,object_type,object_id,price,price_old,ext,status,create_time,last_updated) values ".$values;
		$orderSql   = "insert into  t_order (pk_order,fk_user,price,price_old,status,unique_order_id,out_trade_id,expiration_time,create_time,last_updated) values ".$orderValue;

		$contentRes = $db->execute($contentSql);
		$orderRes   = $db->execute($orderSql);
		if($contentRes && $orderRes){
			//提交成功
			$db->execute("COMMIT");
			return true;
		}
		SLog::fatal('db error[%s]', var_export(
			[
				'contentSql' => $contentSql,
				'orderSql'   => $orderSql
			],
			1
		));
		//事务回滚
		$db->execute("ROLLBACK");
		return false;
	}
	
	public static function getOrderInfo($data,$page,$length)
	{
		$db = self::InitDB("db_order");
        $condition  = ' 1 ';
		if(!empty($data['contentOrderIds'])){
			$condition .= " and t_order_content.pk_order_content IN ({$data['contentOrderIds']})";
		}
        if(!empty($data['resell'])){
            $condition .= " and t_order_content.promote_status = {$data['resell']}";
        }
		if(!empty($data['objectType'])){
			$condition .= " and t_order_content.object_type = {$data['objectType']}";
		}
        if(!empty($data['objectId'])){
            $condition .= " and t_order_content.object_id IN ({$data['objectId']}) ";
        }
		if(!empty($data['userId'])){
			$condition .= " and t_order.fk_user IN ({$data['userId']})";
		}
		if(isset($data['status'])){
			$condition .= " and t_order.status IN ({$data['status']})";
        }else{
            $condition .= " and t_order.status!=-1";
        }
		if(!empty($data['isFavorable'])){
			$condition .= " and t_order.discount_status = {$data['isFavorable']}";
		}
        if(!empty($data['time'][0]) && !empty($data['time'][1])){
			$condition .= " and t_order.last_updated >= '{$data['time'][0]}' and t_order.last_updated <= '{$data['time'][1]}' ";
        }elseif(!empty($data['time']['0'])){
			$condition .= " and t_order.last_updated >= '{$data['time'][0]}' ";
        }elseif(!empty($data['time']['1'])){
			$condition .= " and t_order.last_updated <= '{$data['time'][1]}' ";
        }
        if(!empty($data['price'][0]) && !empty($data['price'][1])){
			$condition .= " and t_order.price >= {$data['price'][0]} and t_order.price <= {$data['price'][1]}";
        }elseif(!empty($data['price'][0])){
			$condition .= " and t_order.price >= {$data['price'][0]}";
        }elseif(!empty($data['price'][1])){
			$condition .= " and t_order.price <= {$data['price'][1]}";
        }
		if(!empty($data['orderSn'])){
			$orderSn = substr($data['orderSn'],8);
			$condition .= " and t_order.pk_order = {$orderSn}";
		}
		if(!empty($data['orgId'])){
			$condition .= " and t_order_content.org_id = {$data['orgId']}";
		}
        if(!empty($data['uniqueOrderId'])){
            $condition .= " and t_order.unique_order_id = \"{$data['uniqueOrderId']}\" ";
        }
		if(isset($data['payType']) && is_numeric($data['payType'])){
			$condition .= " and t_order.pay_type = {$data['payType']}";
		}

		$item = array(
		    "t_order_content.pk_order_content",
			"t_order_content.fk_order",
			"t_order_content.fk_user as content_fk_user",
			"t_order_content.object_type",
			"t_order_content.object_id",
			"t_order_content.price as content_price",
			"t_order_content.price_old as content_price_old",
			"t_order_content.discount_status as content_discount_status",
			"t_order_content.create_time as content_create_time",
			"t_order_content.last_updated as content_last_update",
            "t_order_content.price_promote",
            "t_order_content.resell_org_id",
            "t_order_content.promote_status",
			"t_order_content.ext",
			"t_order_content.org_id",
			"t_order.fk_user",
			"t_order.price",
			"t_order.price_old",
			"t_order.pay_type",
			"t_order.status",
			"t_order.discount_status",
			"t_order.unique_order_id",
			"t_order.out_trade_id",
			"t_order.expiration_time",
			"t_order.create_time",
			"t_order.last_updated"
        );
        $left = new stdclass;
        $left->t_order  = "t_order_content.fk_order=t_order.pk_order";
        $db->setPage($page);
        $db->setLimit($length);
        $db->setCount(true);
       	$res  = $db->select("t_order_content", $condition, $item, "", array("pk_order_content" => "desc"), $left);
		return $res;
	}
		
}

?>
