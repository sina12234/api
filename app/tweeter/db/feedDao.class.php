<?php

class tweeter_db_feedDao
{
    const dbName = 'db_tweeter';
    const TABLE = 't_tweeter_feed';
    const ExpiredTime = 7200;

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function add($insertData, $updateData = [])
    {
        $db = self::InitDB(self::dbName);

        if (!empty($updateData)) {
            $res = $db->insert(self::TABLE, $insertData, false, false, $updateData);
        } else {
            $res = $db->insert(self::TABLE, $insertData);
        }

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function update($pKey, $data)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "fk_user={$pKey}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function row($pKey)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "fk_user={$pKey}";

        return $db->selectOne(self::TABLE, $condition);
    }

    public static function del($pKey)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "fk_user={$pKey}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function getMyFeeds($uid, $orgId = 0, $source = 1, $page = 1, $length = -1)
    {
        if (!$uid && !$orgId) return false;
        $db = self::InitDB(self::dbName, 'query');

        if ($uid) {
            $condition = "fk_author_user={$uid} and fk_source={$source}";
        } else {
            $condition = "fk_author_org={$orgId} and fk_source={$source}";
        }

        $key = md5(self::TABLE.'_getMyFeeds_'.$uid.$orgId.$source.$page.$length);
        $v   = redis_api::get($key);
        if (!empty($v)) return $v;

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        redis_api::set($key, $res, self::ExpiredTime);

        return $res;
    }

    public static function getFollowFeeds($uid, $source = 1, $page = 1, $length = -1)
    {
        if (!$uid) return false;
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_source={$source} and fk_user={$uid}";
        $tKey      = '_getFollowFeeds_fk_user_'.$uid;

        $key = md5(self::TABLE.$tKey.$source);
        $v   = redis_api::get($key);
        if (!empty($v)) return $v;

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        redis_api::set($key, $res, self::ExpiredTime);
        return $res;
    }

    public static function getAllFeeds($uid, $orgId = 0, $source = 1, $page = 1, $length = -1)
    {
        if (!$uid && !$orgId) return false;
        $db = self::InitDB(self::dbName, 'query');
        if ($uid) {
            $condition = "(fk_user={$uid} or fk_author_user={$uid}) and fk_source={$source}";
        } else {
            $condition = "(fk_user={$orgId} or fk_author_org={$orgId}) and fk_source={$source}";
        }

        /*$key = md5(self::TABLE.$condition.$source);
        $v   = redis_api::get($key);
        if (!empty($v)) return $v;*/

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $res = $db->select(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
            return false;
        }

        //redis_api::set($key, $res, self::ExpiredTime);
        return $res;
    }
}
