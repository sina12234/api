<?php

class blog_db_commentDao
{
    const dbName = 'db_blog';
    const TABLE = 't_article_comment';

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

    public static function listsByArticleId($id, $page = 1, $length = 20)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_article={$id}";

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

    public static function del($commentId)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "pk_article_comment={$commentId}";

        return $db->delete(self::TABLE, $condition);
    }
}

