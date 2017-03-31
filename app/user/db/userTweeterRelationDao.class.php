<?php

class user_db_userTweeterRelationDao
{
    const dbName = 'db_user';
    const TABLE = 't_user_tweeter_relation';
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

    public static function del($fid, $uid, $orgId = 0)
    {
        $db = self::InitDB(self::dbName);
        $condition = "follower_id={$fid}";
        if ($uid && $orgId) return false;

        if ($uid) {
            $condition .= " AND fk_user={$uid}";
        } else {
            $condition .= " AND fk_org={$orgId}";
        }

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateBatch($fid, $uidArr, $gid = -4)
    {
        if (count($uidArr) < 1) return false;
        $uidStr = implode(',', $uidArr);

        $db = self::InitDB(self::dbName);
        $condition = "follower_id={$fid} AND fk_user IN ({$uidStr})";

        $data = ['fk_group' => $gid];
        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function listsByGroupId($groupId, $fid, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName, 'query');
        $k = md5(self::TABLE.'listsByGroupId'.$groupId.$fid);
        $v= redis_api::get($k);
        if (!empty($v)) return $v;

        $condition = "fk_group={$groupId} and follower_id={$fid}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error,params[%s]', var_export(func_get_args(), 1));
            return false;
        }
        redis_api::set($k, $res, self::ExpiredTime);

        return $res;
    }

    public static function getGroupId($loginId, $uid)
    {
        $db = self::InitDB(self::dbName);
        $condition = "follower_id={$loginId} AND fk_user=$uid";

        $res = $db->selectOne(self::TABLE, $condition);

        $groupId = 0;
        if (empty($res['fk_group'])) {
            $groupId = $res['fk_group'];
        }

        return $groupId;
    }

    public static function getGroupMember($loginId, $groupId, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName);
        $condition = "follower_id={$loginId} AND fk_group=$groupId";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getMyFollow($uid, $page = 1, $length = -1)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_user={$uid}";

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    /**
     * @param $uid->current login user id
     * @param $followId
     * @param int $orgId
     * @param int $source
     * @param int $page
     * @param int $length
     * @return bool
     */
    public static function addFollowTwIntoRedis($uid, $followId, $orgId = 0, $source = 1, $page = 1, $length = -1)
    {
        $key = md5('t_tweeter_feed_getMyFeeds_'.$followId.$orgId.$source.$page.$length);
        $v   = redis_api::get($key);

        $data = [];
        if (!empty($v)) {
            $myFeeds = $v;
        } else {
            $myFeeds = tweeter_db_feedDao::getMyFeeds($followId, $orgId, $source, $page, $length);
            if ($myFeeds === false) return false;
        }
        if (empty($myFeeds->items)) return false;

        $time = date('Y-m-d H:i:s', time());
        foreach ($myFeeds->items as $item) {
            $data[] = [
                'fk_user'        => $uid,
                'fk_tweeter'     => $item['pk_tweeter'],
                'fk_source'      => $source,
                'fk_author_user' => $item['fk_user'],
                'fk_author_org'  => $item['fk_org'],
                'create_time'    => $time
            ];
        }

        $res = redis_api::sAdd('db_tweeter_get_feeds', $data);
        if ($res === false) {
            SLog::fatal(
                'add addFollowTwIntoRedis failed,redis key[%s], params[%s]',
                $key,
                var_export($data, 1)
            );
        }

        return $res;
    }

}
