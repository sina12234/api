<?php

class tweeter_db_tweeterDao
{
    const dbName = 'db_tweeter';
    const TABLE = 't_tweeter';
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
        $condition = "pk_tweeter={$pKey}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter={$pKey}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function del($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateCommentNum($pKey, $int = 1)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter={$pKey}";
        if ($int > 0) {
            $data = ["comment_count=comment_count+1"];
        } else {
            $data = ["comment_count=comment_count-1"];
        }

        $res = $db->update(self::TABLE, $condition, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateViewNum($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter={$pKey}";

        $data = [
            'view_count=view_count+1'
        ];
        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateZanNum($pKey, $int = 1)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_tweeter={$pKey}";

        if ($int > 0) {
            $data = ["zan_count=zan_count+1"];
        } else {
            $data = ["zan_count=zan_count-1"];
        }

        $res = $db->update(self::TABLE, $condition, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function addZanUserIntoRedis($tweeterId, $userId)
    {
        $key = 'db_tweeter_get_zan_list'.$tweeterId;
        $res = redis_api::sAdd($key, $userId);
        if ($res === false) {
            SLog::fatal(
                'get tweeter zan list failed,redis key[%s], params[%s]',
                $key,
                var_export(
                    [
                        'tweeterId' => $tweeterId,
                        'userId'    => $userId
                    ], 1
                )
            );
        }

        return $res;
    }

    public static function getTweeterZanList($tweeterId)
    {
        $key = 'db_tweeter_get_zan_list'.$tweeterId;

        return redis_api::sMembers($key);
    }

    public static function getTweeter($uid, $orgId = 0, $page = 1, $length = -1)
    {
        if (!$uid && !$orgId) return false;
        $db = self::InitDB(self::dbName, 'query');

        if ($uid) {
            $condition = "fk_user={$uid}";
        } else {
            $condition = "fk_org={$orgId}";
        }

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }
        $item = ['pk_tweeter']; //这里不取content字段

        $res = $db->select(self::TABLE, $condition, $item, '', 'create_time desc');
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getTwInfoList($tweeterIdArr, $page = 1, $length = -1)
    {
        if (count($tweeterIdArr) < 1) return false;
        $db = self::InitDB(self::dbName, 'query');

        sort($tweeterIdArr);
        $idStr = implode(',', $tweeterIdArr);
        $condition = "pk_tweeter IN ({$idStr})";

        $key = md5(self::TABLE.'_getTwInfoList_'.$idStr);
        $v   = redis_api::get($key);
        if (!empty($v)) return $v;

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition, '', '', 'create_time desc');
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        redis_api::set($key, $res, self::ExpiredTime);
        return $res;
    }
}
