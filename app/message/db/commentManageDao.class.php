<?php

class message_db_commentManageDao
{
    const  dbName = 'db_message';
    const TABLE = 't_comment_replay';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }

    /**
     * @param $data
     * @老师插入回复
     */
    public static function InsertCommentReplay($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
    }

    /**
     * 老师删除回复
     */

    public static function DeleteCommentReplay($data){
        $db = self::InitDB(self::dbName);
        $condition = "fk_user = {$data['fk_user']} and pk_replay={$data['pk_replay']} and status=0";
        $item = array('status'=>-1);
        $res = $db->update(self::TABLE,$condition,$item);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
    }

    /**
     * 检测老师是否评论过
     */

    public static function CheckIsReplay($data){
        $db = self::InitDB(self::dbName);
        $condition = "fk_comment = {$data['fk_comment']} and fk_user = {$data['fk_user']} and status = 0 ";
        $res = $db->selectOne(self::TABLE,$condition);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
    }

    /**
     * 获取老师对应的评论
     */

    public static function ShowReplay($data){
        $db = self::InitDB(self::dbName);
        $condition ="fk_comment = {$data['fk_comment']}  and status = 0 ";
        if(isset($data['fk_user'])){
            $condition .= " and fk_user = {$data['fk_user']}";
        }
        $res = $db->selectOne(self::TABLE,$condition);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
        return $res;
    }
}
