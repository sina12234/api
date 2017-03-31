<?php

class course_group_api
{
    public  static $redisTime = 600;          // redis时间： 0 关闭缓存 ，>0 缓存时间 (单位秒)  
    public  static $courseClassGroupParamDb  = [
            'fk_course'         => 'cid',      // 课程ID
            'fk_class'          => 'classid',  // 班级ID
            'group_teacher_id'  => 'tid',      // 老师ID
            'group_name'    => 'gname',     // 分组名称
            'user_count'    => 'ucount',    // 学生数量
            'status'        => 'st',        // 删除 -1 ， 初始 0 ， 正常 1
            'create_time'   => 'ctime',   
            'last_updated'  => 'uptime',          
     ];
    public  static $courseClassGroupUserParamDb  = [
            'pk_id'         => 'pid',      
            'fk_group'      => 'gid',       // 分组ID
            'fk_course'     => 'cid',       // 课程ID
            'fk_class'      => 'classid',   // 班级ID
            'fk_user'       => 'uid',       // 学生ID
            
            'create_time'   => 'ctime',   
            'last_updated'  => 'uptime',          
     ];

    /* 分组列表 cid=3559&classid=1499&rtime=0 */
    public static function getCourseClassGroupList($params){	       
        $fk_course   = empty($params['cid']) ? 0 : (int)$params['cid'] ; // 课程ID
        $fk_class    = empty($params['classid']) ? 0 : (int)$params['classid'] ; // 班级ID
        $redisTime   = isset($params['rtime']) ?  (int)$params['rtime'] : self::$redisTime ;
        $isGetUser   = empty($params['guser']) ? 0 : (int)$params['guser'] ; // 是否获取学生列表
        
        if(empty($fk_course) || empty($fk_class)) { return api_func::setMsg(1000);  }  

        $condition = [];
        $condition['fk_course'] = $fk_course;
        $condition['fk_class']  = $fk_class;
        $condition['status'] = 1;
        if(empty($isGetUser)){
            $data = course_db_courseClassGroupDao::listGroup($redisTime,$condition);
        } else {
            $data = course_db_courseClassGroupDao::listGroupAndUser($redisTime,$condition);
        }
        
        /* 格式化列表数据 */
        $dataParam = api_func::formatListData($data,self::$courseClassGroupParamDb);

        $setConfig = ['count'=>$dataParam['count'],'rtime'=>$redisTime];

        return api_func::setDataConfig($dataParam['data'],$setConfig);
    }	

    /* 分组下学生列表 gid=4&rtime=0 */
    public static function getCourseClassGroupUserList($params){	       
        $fk_group   = empty($params['gid']) ? 0 : (int)$params['gid'] ; // 分组ID
        $redisTime  = isset($params['rtime']) ?  (int)$params['rtime'] : self::$redisTime ;
        
        if(empty($pk_id)) { return api_func::setMsg(1000);  }  

        $condition = [];
        $condition['fk_group'] = $fk_group ;

        $data = course_db_courseUserClassGroupDao::listUserClassGroup($redisTime,$condition);

        /* 格式化列表数据 */
        $dataParam = api_func::formatListData($data,self::$courseClassGroupUserParamDb);

        $setConfig = ['count'=>$dataParam['count'],'rtime'=>$redisTime];

        return api_func::setDataConfig($dataParam['data'],$setConfig);
    }
    
}
