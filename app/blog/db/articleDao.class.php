<?php

class blog_db_articleDao
{
    const dbName = 'db_blog';
    const TABLE = 't_article';

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

    public static function update($pKey, $data)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_article={$pKey}";

        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateCommentNum($pKey)
    {
        $db = self::InitDB(self::dbName);
        $condition = "pk_article={$pKey}";

        $data = [
            'comment_num=comment_num+1'
        ];
        $res = $db->update(self::TABLE, $condition, $data);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function lists($params)
    {
        $db = self::InitDB(self::dbName, 'query');

        $page = isset($params['page']) && $params['page'] ? $params['page'] : 1;
        $length = isset($params['length']) && $params['length'] ? $params['length'] : 20;

        $condition = [];
        if (isset($params['teacherId']) && $params['teacherId']) {
            $condition['fk_user'] = $params['teacherId'];
        }

        if (isset($params['draft']) && $params['draft']) {
            $condition['status'] = 0;
        } else {
            $condition['status'] = 1;
        }

        if (isset($params['tagId']) && $params['tagId']) {
            $condition['fk_tag'] = $params['tagId'];
        }

        isset($params['type']) && $condition['type'] = $params['type'];
        isset($params['top']) && $condition['top'] = $params['top'];

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        $item = ['pk_article','title','summary','thumb','comment_num','share_num','create_time'];
        $res = $db->select(self::TABLE, $condition, $item, '', 'top desc, create_time desc');

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function listsByUserId($teacherId, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user={$teacherId}";

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

    public static function row($id)
    {
        $db = self::InitDB("db_blog", 'query');
        $condition = "pk_article={$id}";

        $res = $db->selectOne(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function del($id)
    {
        $db = self::InitDB("db_blog");
        $condition = "pk_article={$id}";

        $res = $db->delete(self::TABLE, $condition);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }



}

