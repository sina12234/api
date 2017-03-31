<?php

class message_db_messageUserTextGatherDao
{
    const dbName = 'db_message';
    const TABLE = 't_message_user_text_gather';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($insertData, $updateData)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $insertData, false, false, $updateData);

        if($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_message_user_text_gather={$pKey}";

        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_message_user_text_gather={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function msgDel($userFrom, $userTo, $msgType)
    {
        $db = self::InitDB(self::dbName);
        $condition = "(fk_user_to={$userTo} and fk_user_from={$userFrom}) or (fk_user_to={$userTo} and fk_user_from={$userFrom}) and message_type={$msgType}";
        $data = ['status' => 'delete'];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function msgTop($userFrom, $userTo, $msgType, $type)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type={$msgType}";
        $data = ['is_top' => $type];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function msgRemind($userFrom, $userTo, $msgType, $type)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type={$msgType}";
        $data = ['is_remind' => $type];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getMyMessages($userTo, $page=1, $length=20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user_to={$userTo} and status<>'delete'";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, '', '', 'is_top desc, is_remind desc, create_time desc');

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function msgUpdateRead($userFrom, $userTo, $msgType)
    {
        $db = self::InitDB(self::dbName);
        //$condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type={$msgType} and status='unread'";
        $condition = "fk_user_to={$userTo} and fk_user_from={$userFrom} and message_type !='10003' and status='unread'";
        $data = [
            'status'      => 'readed',
            'message_num' => 0
        ];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	public static function updateAll($userTo, $msgType,$status="readed")
    {
        $db = self::InitDB(self::dbName);
		$msgType = is_array($msgType)?implode(',',$msgType):$msgType;
        $condition = "fk_user_to=$userTo and message_type in({$msgType})";
        $data = [
            'message_num' => 0,
			'status'=>$status,
        ];
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
        $data = [
            'status'      => 'readed',
            'message_num' => 0
        ];

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getLatestUser($to, $page, $length)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user_to={$to} and fk_user_from > 0";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, '', '', 'create_time desc');

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}
