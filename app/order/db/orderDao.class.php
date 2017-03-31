<?php

class order_db_orderDao
{
    const dbName = 'db_order';

    const TABLE = 't_order';

    const ExpiredTime = 7200;

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($insertData, $updateData=[])
    {
        $db = self::InitDB(self::dbName);

        if (!empty($updateData)) {
            $res = $db->insert(self::TABLE, $insertData, false, false, $updateData);
        } else {
            $res = $db->insert(self::TABLE, $insertData);
        }

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function update($pKey, $data)
    {
        $db = self::InitDB(self::dbName);
		if(is_array($pKey)){
			$condition = 'pk_order in ('.implode(',', $pKey).')';
		}else{
			$condition = "pk_order={$pKey}";
		}
		
        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($params)
    {
        $db = self::InitDB(self::dbName);

        if (is_array($params)) {
            $condition = '';
            if (!empty($params['orderId'])) {
                $condition = "pk_order = {$params['orderId']}";
            }
            if (!empty($params['uniqueOrderId'])) {
                $condition = " unique_order_id = '{$params['uniqueOrderId']}' ";
            }
        } else {
            $condition = "pk_order = {$params}";
        }
        
        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        return $res;
    }
	
	public static function rows($data, $page=1, $length=-1)
	{
		$db = self::InitDB(self::dbName);
		
		$condition = ' 1 ';
		if(!empty($data['userId'])){
			$condition .= " and t_order.fk_user = {$data['userId']}";
		}
		if(!empty($data['status'])){
			$condition .= " and t_order.status = {$data['status']}";
		}
		if(!empty($data['time'][0])){
			$condition .= " and t_order.create_time >= {$data['time'][0]}";
		}elseif(!empty($data['time'][1])){
			$condition .= " and t_order.create_time <= {$data['time'][1]}";
		}elseif(!empty($data['time'][0]) && !empty($data['time'][1])){
			$condition .= " and t_order.create_time >= {$data['time'][0]} and t_order.create_time <= {$data['time'][1]}";
		}
		if(!empty($data['price'][0])){
			$condition .= " and t_order.price >= {$data['price'][0]}";
		}elseif(!empty($data['price'][1])){
			$condition .= " and t_order.price <= {$data['price'][1]}";
		}elseif(!empty($data['price'][0]) && !empty($data['price'][1])){
			$condition .= " and t_order.price >= {$data['price'][0]} and t_order.price <= {$data['price'][1]}";
		}
		if(!empty($data['orderSn'])){
			$orderSn = substr($data['orderSn'],8);
			$condition .= " and t_order.pk_order = {$orderSn}";
		}elseif(!empty($data['orderIds'])){
			$condition .= " and t_order.pk_order IN ({$data['orderIds']})";
		}
		if(!empty($data['orgId'])){
			$condition .= " and t_order.org_id = {$data['orgId']}";
		}
		
		$db->setPage($page);
        $db->setLimit($length);
        $db->setCount(true);
		$res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        return $res;
	}

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_order={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getOrderByOutTradeId($out_trade_id)
    {
        $db        = self::InitDB(self::dbName);
        $condition["out_trade_id"] = $out_trade_id;

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function setPriceForFeeOrderByOrderId($order_id, $price_old, $price, $out_trade_id)
    {
        $condition = "pk_order=$order_id";
        $item      = array("price_old" => $price_old, "price" => $price, "out_trade_id" => "$out_trade_id");
        $db        = self::InitDB(self::dbName);

        return $db->update(self::TABLE, $condition, $item);
    }

}
