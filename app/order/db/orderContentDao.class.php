<?php

class order_db_orderContentDao
{
    const dbName = 'db_order';

    const TABLE = 't_order_content';

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

    public static function update($params, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = '';
        if (is_array($params)) {
            if(!empty($params['orderContentId'])){
                $condition = " pk_order_content IN ({$params['orderContentId']}) ";
            }elseif(!empty($params['orderId'])){
                $condition = " fk_order IN ({$params['orderId']}) ";
            }
        } else {
            $condition = " fk_order={$params}";
        }

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function rows($orderId, $page = 1, $length = -1)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "fk_order IN ({$orderId})";

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

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);

        $condition = "pk_order_content={$pKey}";
        $res = $db->selectOne(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }
		
        return $res;
    }

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_order_content={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
    
    
    public static function lists($condition, $page = 1, $length = -1)
    {
        $db        = self::InitDB(self::dbName);
  
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
}
