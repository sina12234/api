<?php

class message_db_dialogDao
{
    const dbName = 'db_message';
    const TABLE = 't_message_user_text';
    const ExpiredTime = 7200;

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        return $db->insert(self::TABLE, $data);
    }

    public static function update($msgId, $userToId = '', $action='readed')
    {
        $condition = '';
        if (is_array($msgId) && count($msgId) > 0) {
            $msgIdStr = implode(',', $msgId);
            $msgIdStr && $condition = "pk_msg_id IN ({$msgIdStr})";
        } else {
            $msgId     = (int)($msgId);
            $condition = "pk_msg_id={$msgId}";
        }

        if ((int)($userToId)) {
            $condition .= " AND fk_user_to={$userToId}";
        }
        $item = ['status' => $action];

        $db = self::InitDB(self::dbName);

        return $db->update(self::TABLE, $condition, $item);
    }
	public static function updateByUserToId($userToId,$messageType="", $action='readed')
    {
		$condition = "1=1";
        if ((int)($userToId)) {
            $condition .= " AND fk_user_to={$userToId}";
        }
		if($messageType){
			$condition .= " AND message_type in ({$messageType})";
		}
        $item = ['status' => $action];
        $db = self::InitDB(self::dbName);
        return $db->update(self::TABLE, $condition, $item);
    }

    public static function msgDel($userFrom, $userTo, $msgType)
    {
        $db = self::InitDB(self::dbName);
        $condition = "message_type={$msgType} and ((fk_user_to={$userTo} and fk_user_from={$userFrom}) or (fk_user_to={$userFrom} and fk_user_from={$userTo}))";
        $data = ['status' => 'delete'];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateByUserFromId($userFromId, $userToId, $action='readed')
    {
        if (is_array($userFromId) && count($userFromId) > 0) {
            $userFromIdStr = implode(',', $userFromId);
            $userFromIdStr && $condition = "fk_user_from IN ({$userFromIdStr})";
        } else {
            $userFromId     = (int)($userFromId);
            $condition = "fk_user_from={$userFromId}";
        }

        if ((int)($userToId)) {
            $condition .= " AND fk_user_to={$userToId}";
        }

        $item = ['status' => $action];

        $db = self::InitDB(self::dbName);

        $res = $db->update(self::TABLE, $condition, $item);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
    }

    public static function msgUpdate($userFrom, $userTo, $msgType)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type={$msgType}";
        $data = ['status' => 'delete'];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function msgUpdateRead($userFrom, $userTo, $msgType)
    {
        $db = self::InitDB(self::dbName);
        //$condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type={$msgType} and status='unread'";
        $condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type !=10003 and status='unread'";
        $data = ['status' => 'readed'];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function chatMsgUpdateRead($userFrom, $userTo)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and status='unread'";
        $data = ['status' => 'readed'];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function lists($userToId, $maxId=0, $page=1, $length=500,$messageType=array())
    {
        $db = self::InitDB(self::dbName, 'query');

/*        $key = md5(self::TABLE.'message_lists'.serialize(func_get_args()));
        $value = redis_api::get($key);*/

        if (!empty($value)) {
            return $value;
        }

        $condition = "status <> 'delete' ";

        $userToId && $condition .= " AND (fk_user_to={$userToId} or fk_user_from={$userToId})";
        ($maxId > 0) && $condition .= " AND pk_msg_id>{$maxId}";
		if(!empty($messageType)){
			if(is_array($messageType)){
				$messageType = implode(',',$messageType);
			}else{
				$messageType = intval($messageType);
			}
			$condition .= " AND message_type in ($messageType)";
		}
        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, '', '', 'pk_msg_id asc');

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        /*redis_api::set($key, $res, self::ExpiredTime);*/

        return $res;

    }

    public static function getDialogLastTotal($uid,$messageType=array())
    {
        $db    = self::InitDB(self::dbName, 'query');
        $table = self::TABLE;
        $sql   = "select count(*) as totalNum from (select  count(*) as totalNum from {$table} where `status`='unread' AND `fk_user_to`={$uid}";
		if(!empty($messageType)){
			if(is_array($messageType)) $messageType = implode(',',$messageType);
			 $sql   .= " AND message_type in($messageType) ";
		}
		$sql   .= " group by `fk_user_from`) as b";
        $res = $db->execute($sql);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));

            return 0;
        }

        return $res[0]['totalNum'];
    }

    public static function getDialogLastTotalList($uid, $page = 1, $length = 20)
    {
        $db       = self::InitDB(self::dbName, 'query');
/*        $key = md5(self::TABLE.'message_getDialogLastTotalList'.serialize(func_get_args()));
        $value = redis_api::get($key);*/

        if (!empty($value)) {
            return $value;
        }
        
        $offset   = ($page - 1) * $length;
        $limitSql = "LIMIT {$offset}, {$length}";
        $table    = self::TABLE;

        $sql = "select *, count(*) as totalNum from {$table} where `status`='unread' AND `fk_user_to`={$uid} group by `fk_user_from`  order by `pk_msg_id` desc {$limitSql}";

        $res = $db->execute($sql);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));

            return false;
        }

        $total               = self::getDialogLastTotal($uid);
        $result['items']     = $res;
        $result['totalSize'] = $total;
        $result['totalPage'] = ceil($total / $length);
        /*redis_api::set($key, $result, self::ExpiredTime);*/

        return $result;
    }

    public static function getUnreadMsg($from, $to, $type, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');
		//$condition = "fk_user_to={$to} and fk_user_from={$from} and message_type={$type} and status <> 'delete'";
        $condition = "fk_user_to={$to} and fk_user_from={$from} and message_type !=10003 and status <> 'delete'";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        //$res = $db->select(self::TABLE, $condition, '', '', 'pk_msg_id asc');
		$res = $db->select(self::TABLE, $condition, '', '', 'insert_time desc');
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function ChatSingle($from, $to, $maxId=0, $page, $length)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "message_type=10003 and status <> 'delete' and ((fk_user_to={$to} and fk_user_from={$from}) or (fk_user_to={$from} and fk_user_from={$to}))";
        ($maxId > 0) && $condition .= " AND pk_msg_id>{$maxId}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, '', '', 'pk_msg_id asc');

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}

