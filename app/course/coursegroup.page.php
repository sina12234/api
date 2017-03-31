<?php	
/*
 * 班级分组
 */
class course_coursegroup
{    
        /* 分组列表 cid=3559&classid=1499&rtime=0 */
        public function pageGetCourseClassGroupList(){
                $params = SJson::decode(utility_net::getPostData(),true);	

                $ret = course_group_api::getCourseClassGroupList($params);
                
                return $ret;
        }
        /* 分组下学生列表 gid=4&rtime=0 */
        public function pageGetCourseClassGroupUserList(){
                $params = SJson::decode(utility_net::getPostData(),true);	

                $ret = course_group_api::getCourseClassGroupUserList($params);
                
                return $ret;
        }
        
        public function pageSaveCourseClassGroup(){
            $params = SJson::decode(utility_net::getPostData(),true);	

            $data  = isset($params['data']) ? $params['data'] : '' ;
            $dataArr = json_decode($data);
            $ret  = false;
            $pids = '';
            $insertValSql = '';
            $updateGnameValSql = ' group_name = CASE pk_group ';
            $updateTidValSql   = ' END , group_teacher_id = CASE pk_group ';

            foreach($dataArr->data as $key => $val){
                if(empty($val->pid)){
                    $insertValSql .= '('.$val->cid.','.$val->classid.',"'.$val->gname.'",'.$val->tid.'),';
                } else {
                    $updateTidValSql   .= " WHEN {$val->pid} THEN  {$val->tid}";
                    $updateGnameValSql .= " WHEN {$val->pid} THEN  '{$val->gname}'";
                    $pids .= $val->pid.',';
                }
            }
            if (!empty($insertValSql)){
                $insertValSql = substr($insertValSql,0,-1);
                $insertSql = 'insert t_course_class_group(fk_course , fk_class , group_name , group_teacher_id) values '.$insertValSql;
                $insertRet = course_db_courseClassGroupDao::queryGroup($insertSql);
                if ($insertRet===false) $code = 1;
            }
            if (!empty($pids)){
                $pids = substr($pids,0,-1);
                $updateSql = 'update t_course_class_group set '
                        . $updateGnameValSql
                        . $updateTidValSql
                        . ' END '
                        .' where pk_group in('.$pids .')';

                $updateRet = course_db_courseClassGroupDao::queryGroup($updateSql);
                if ($updateRet===false) $code = 1;
            }
            
            return api_func::setData($code);
        }
        /* 添加班级分组 cid=3559&classid=1499&gname=A组&tid=2932 */
        public function pageAddCourseClassGroup(){
                $params = SJson::decode(utility_net::getPostData(),true);	

                $fk_course  = empty($params['cid']) ? 0 : (int)$params['cid'] ;         // 课程ID
                $fk_class   = empty($params['classid']) ?  0 :(int)$params['classid'] ; // 班级ID
                $group_teacher_id = empty($params['tid']) ?  0 :(int)$params['tid'] ; // 老师ID 
                $group_name = isset($params['gname']) ?  $params['gname'] : '';
                
                $GroupData = [];
                $msg = '';
                foreach(course_group_api::$courseClassGroupParamDb as $dataName => $dataValue){
                    if(isset($$dataName)) {
                        $GroupData[$dataName] = $$dataName;
                        if(empty($$dataName)) $msg .= $dataValue.'|';
                    }
                }
                
                if(empty($fk_course) || empty($fk_class) || empty($group_teacher_id) || empty($group_name)) {
                    return api_func::setMsg(1000,$msg);
                }
                
                $condition = ['fk_course'=>$fk_course , 'fk_class'=>$fk_class];
                $planInfo = course_db_coursePlanDao::getPlanList($condition,$orderBy=''); 
                if ($planInfo === false) {  return api_func::setMsg(5001,"无效课程ID&&班级ID");  }                     
                
                 // 请不要重复分组
                $conditionRow = [];
                $conditionRow['fk_course'] = $fk_course;
                $conditionRow['fk_class']  = $fk_class;
                $conditionRow['group_name'] = $group_name;
                $rowData = course_db_courseClassGroupDao::rowGroup($conditionRow); 
                if (!empty($rowData)) return api_func::setMsg(5002,"请不要重复分组");
                
                $time  = date("Y-m-d H:i:s");
                $GroupData['create_time'] = $time;
                $GroupData['last_updated'] = $time;
                
                $data = course_db_courseClassGroupDao::addGroup($GroupData);
                
                return api_func::setData($data);
        }

        /* 更改班级分组信息(删除; 修改分组信息) cid=3559&classid=1499&gname=A组&tid=2932 */
        public function pageUpdateCourseClassGroup($inPath){
                $params = SJson::decode(utility_net::getPostData(),true);		
                
                $condition = [];
                $pointData = [];                
                
                if(isset($params['st'])) {        
                    $pk_group   = empty($params['gid']) ?  0 :(int) $params['gid'] ;            // 分组ID
                    $status     = empty($params['st']) ? 0 : (int) $params['st'] ;          

                    if(empty($pk_group) || empty($status)) {  return api_func::setMsg(1000); }
                    
                    $condition['pk_group'] = $pk_group;
                    
                    $groupData['status'] = $status;
                    
                    $data = course_db_courseClassGroupDao::updateGroup($condition,$groupData);
                }
                if(isset($params['gname'])) {     
                    $fk_course  = empty($params['cid']) ?  0 :(int) $params['cid'] ;            
                    $fk_class   = empty($params['classid']) ?  0 :(int) $params['classid'] ;    
                    $group_teacher_id = empty($params['tid']) ?  0 : (int) $params['tid'] ;  
                    $group_name = isset($params['gname']) ?  $params['gname'] : '';
                    
                    if(empty($fk_course) || empty($fk_class) || empty($group_teacher_id) || empty($group_name)) {  return api_func::setMsg(1000); }
                                  
                    // 请不要重复分组
                   $conditionRow = [];
                   $conditionRow['fk_course']  = $fk_course;
                   $conditionRow['fk_class']   = $fk_class;
                   $conditionRow['group_name'] = $group_name;
                   $rowData = course_db_courseClassGroupDao::rowGroup($conditionRow); 
                   if (!empty($rowData)) return api_func::setMsg(5002,"请不要重复分组");

                    $condition['pk_group'] = $pk_group;
                    
                    $groupData['group_teacher_id'] = $group_teacher_id;
                    $groupData['group_name']       = $group_name;         
                    
                    $data = course_db_courseClassGroupDao::updateGroup($condition,$groupData);           
                }
                
                return api_func::setData($data);
        }    
}
        
        
        
        