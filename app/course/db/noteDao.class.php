<?php

class course_db_noteDao
{
    const dbName = 'db_course';
    const TABLE = 't_note';

    public static function InitDB($dbName = self::dbName, $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }

    //添加笔记
    public static function noteAdd($condition)
    {
        $db = self::InitDB(self::dbName);
        return $db->insert(self::TABLE, $condition);
    }
    //查询笔记总数
    public static function noteCount($condition){
        $db = self::InitDB(self::dbName);
        return $db->select(self::TABLE, $condition);
    }
    //删除笔记
    public static function DelNote($condition){
        $db = self::InitDB(self::dbName);
        if(!empty($condition['fk_user']) && !empty($condition['id']) ){
            $fk_user = $condition['fk_user'];
            $id = $condition['id'];
            $condition  = "fk_user = $fk_user AND id = $id";
        }
        $item = "status = -1";
        return $db->update(self::TABLE, $condition,$item);
    }

    //编辑笔记
    public static function UpdateNote($condition){
        $db = self::InitDB(self::dbName);
        if(!empty($condition['fk_user']) && !empty($condition['id']) && !empty($condition['content'])){
            $fk_user = $condition['fk_user'];
            $id = $condition['id'];
            $content = $condition['content'];
            $condition  = "fk_user = $fk_user AND id = $id";
        }
        $item = "content = $content";
        return $db->update(self::TABLE, $condition,$item);
    }

    //笔记列表
    public static function noteList($params){
        $db = self::InitDB(self::dbName);
        if(!empty($params['fk_user']) && !empty($params['course_id']) && !empty($params['class_id'])){
            $fk_user = $params['fk_user'];
            $course_id = $params['course_id'];
            $class_id = $params['class_id'];
            $status = $params['status'];
            $condition  = "fk_user = $fk_user AND course_id = $course_id AND class_id = $class_id AND status = 1";
        }
        $items = '';
        if($params['live_type'] == 1){
            //直播
            $desc = 'create_time DESC';
        }elseif($params['live_type'] == 2){
            //录播
            $desc = 'play_time ASC';
        }else{
            $desc = '';
        }


        return $db->select(self::TABLE,$condition,$items,'',$desc);
    }
}

