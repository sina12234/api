<?php

class tag_db_mappingTagArticleDao
{
    const dbName = 'db_tag';
    const TABLE = 't_mapping_tag_article';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    public static function getAllTagArticleCountListByTeacherId($teacherId)
    {
        $db = self::InitDB(self::dbName, 'query');

        $condition = "fk_user={$teacherId} and status=1";
        $item = ['fk_tag','status','count(*) as total'];
        $res = $db->select(self::TABLE, $condition, $item, 'fk_tag', 'last_updated desc');

        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function add($data)
    {
        $db = self::InitDB(self::dbName);

        $res = $db->insert(self::TABLE, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function updateMapTagArticle($articleId, $tagId)
    {
        $db = self::InitDB(self::dbName);
        $condition = "fk_article={$articleId}";
        $item = ['fk_tag'=> $tagId];

        $res = $db->update(self::TABLE, $condition, $item);

        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }

    public static function del($uid, $tagId, $articleId)
    {
        $db        = self::InitDB(self::dbName);
        $condition = "fk_user={$uid} and fk_tag={$tagId} and fk_article={$articleId}";

        $res = $db->delete(self::TABLE, $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
}

