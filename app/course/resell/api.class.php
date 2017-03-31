<?php
class course_resell_api{     
    
        public static $allow_promote_op_action = array(             
            /* 推广课程 */
            'orgAddDeletedCourse'   => array('promote'=>'status=1,status_code=-5,org_count=0','course'=>'is_promote=1'), // 添加课程(已删除的推广课程)
            'orgDelPromote'         => array('promote'=>'status=-1,status_code=-14,org_count=0,ver=ver+1','resell'=>'status=-14','course'=>'is_promote=0'),                              // 删除推广课程
            'orgStopPromote'        => array('promote'=>'status=-2,status_code=-13,org_count=0,ver=ver+1'),                          // 暂停推广推广课程
            'orgStartPromote'       => array('promote'=>'status=1,status_code=-4,ver=ver+1','course'=>'is_promote=1'),   // 重新推广(含改价)
            'updatePromotePrice'    => array('promote'=>'status_code=-3,org_count=0,ver=ver+1'),            // 修改成本价
            /* 课程&其它 */
            'updateCourseFeetype'   => array('promote'=>'status=0,status_code=-25,org_count=0,ver=ver+1'),                          // 修改课程付费类型
            'updateCoursePrice'     => array('promote'=>'status=0,status_code=-26,org_count=0,ver=ver+1'),                          // 修改课程原价
            //'courseMaxUser'         => array('promote'=>'status=-3,status_code=-31,org_count=0,ver=ver+1,enroll_count=enroll_count+1','resell'=>'enroll_count=enroll_count+1'),                         // 课程报满
            'courseMaxUser'         => array('promote'=>'status=-3,status_code=-31,org_count=0,ver=ver+1'),                         // 课程报满
            'updateEnrollCount'     => array('promote'=>'enroll_count=enroll_count+1','resell'=>'enroll_count=enroll_count+1'),                          // 修改课程原价
            /* 分销课程 */
            'orgDelResell'     => array('resell'=>'status=-1','promote'=>'org_count=if(org_count=0,0,org_count-1)'),                      // 删除引入
            'orgStartResell'   => array('resell'=>'status=1','promote'=>'org_count=org_count+1'), // 重新引入(已删除的分销课程)
            /* 我引入课程页刷新 */ 
            'syncResell' => array(),                         // 我引入课程页刷新
        );

    public static function getPromoteStatusNotOnVarNot($orgResellId){
        $resell_db = new course_db_resellDao();
        $resellList = $resell_db->getPromoteStatusNotOnVarNot($orgResellId);
        return $resellList;
    }

    public static function updateStatus($resellId,$status){
        $resell_db = new course_db_resellDao();
        $resell_db->updateStatus($resellId,$status);
    }

        /*
         * 增加推广课程
         */
        public static function addPromoteCourse($pcourse)
        {          
            $time                       = date("Y-m-d H:i:s");            
            $ipcourse["fk_course"]      = $pcourse["course_id"];
            $ipcourse["price_promote"]  = $pcourse["price_promote"];
            $ipcourse["create_time"]    = $time;            
            $ret  = course_db_promoteDao::addPromoteCourse($ipcourse);

            return $ret;
        }
        /*
         * 修改推广课程
         */
        public static function updatePromoteCourse($course_id , $promote_course=[])
        {       
            $ret  = course_db_promoteDao::updatePromoteCourse($course_id,$promote_course);

            return $ret;
        }
        
        /*
         * 增加分销课程
         */
        public static function addResellCourse($pcourse)
        {
            $ret  = course_db_resellDao::addResellCourse($pcourse);
            
            return $ret;
        }

        /*
         * 修改分销课程
         */
        public static function updateResellCourse($course_id ,$org_id , $resell_course)
        {              
            $ret  = course_db_resellDao::updateResellCourse($course_id ,$org_id ,$resell_course);

            return $ret;
        }

        /*获取分销、推广成交记录*/
        public static function getCourseResellLog($page,$length,$params){
            
            $courseResellLogList = course_db_resellLogDao::getCourseResellLog($page,$length,$params);
            return $courseResellLogList;
        }

        public static function ryncPromoteResellCourse($op,$where,$params=[])
        {
            $ret = new stdclass();
            $ret->result = new stdclass();
            $ret->result->code = 0;
            $ret->result->msg = '操作成功';
            $ret->data = '';
            $code = $code1 = $code2 = $code3 = 0;
          
            $course_id = isset($where->course_id) ? $where->course_id : 0 ;
            
            if (!in_array($op,array_keys(self::$allow_promote_op_action))){
                $ret->result->code = -3;
                $ret->result->msg  = "the opration is not allowed({$op})";
                return $ret;
            } else {
                $allow_info = self::$allow_promote_op_action[$op];
            }
            
            $allow_info['promote'] = isset($allow_info['promote']) ? ( empty($params['promote']) ? $allow_info['promote'] : $allow_info['promote'].','.$params['promote'] ): ( empty($params['promote']) ? '' : $params['promote'] );
            $allow_info['resell'] = isset($allow_info['resell']) ? ( empty($params['resell']) ? $allow_info['resell'] : $allow_info['resell'].','.$params['resell'] ): ( empty($params['resell']) ? '' : $params['resell'] );
            $allow_info['course'] = isset($allow_info['course']) ? ( empty($params['course']) ? $allow_info['course'] : $allow_info['course'].','.$params['course'] ): ( empty($params['course']) ? '' : $params['course'] );
            
            if (!empty($allow_info['promote']) && !empty($course_id)){
                $promote_ret  = course_resell_api::updatePromoteCourse($course_id , $allow_info['promote']);
                if ($promote_ret===false) $code = $code1 = -4;
            }
            if (!empty($allow_info['resell']) && !empty($course_id)){
                $org_id_resell = isset($where->org_id_resell) ? $where->org_id_resell : 0 ;
                $resell_ret  = course_resell_api::updateResellCourse($course_id, $org_id_resell , $allow_info['resell']);
                if ($resell_ret===false) $code = $code2 = -5;
            }
            if (!empty($allow_info['course']) && !empty($course_id)){
                $course_ret  = course_api::update($course_id , $allow_info['course']);
                if ($course_ret===false) $code = $code3 = -6;
            }
            
            $ret->result->code = $code;
            if ($code<0) $ret->result->msg = "操作失败({$code1}|{$code2}|{$code3})";
            
            return $ret;
        }
        /* 获取课程状态详情
         * @params :cid 课程ID
         * @params :uid 用户ID
         * return 
         * -1 参数为空
         * -2 非法 课程ID
         * -4 课程报满
         * 4 课程即将报满
         * -5 课程结束（待定）
         */
        public static function getResellCourseInfo($cid,$params=[])
        {
            $uid = empty($params->uid) ? 0 : (int) $params->uid ; 
            $ret = new stdclass();
            $ret->result = new stdclass();
            $ret->result->code = 0;
            $ret->result->sub_code = 0;
            $ret->result->class_count = 0;
            $ret->result->course_id = $cid;
            $ret->result->user_id = $uid;
            $ret->result->msg = '操作成功';
            $ret->data = '';
            if (empty($cid)) {
                $ret->result->code = -1;
                $ret->result->msg= "课程ID为空!";
                return $ret;
            }  
            $course_db = new course_db;
            $course_info = $course_db->getCourse($cid);
            $ret->data = $course_info;
            if ($course_info===false || empty($course_info['admin_status']) || $course_info['admin_status']<0){
                $ret->result->code = -2;
                $ret->result->msg= "课程ID不存在或下架";
            }
            if($course_info['max_user']<=$course_info['user_total']){
                $ret->result->sub_code = -4;
                $ret->result->msg= "课程报满";       
            }
            if($course_info['max_user']==$course_info['user_total']+1){
                $ret->result->sub_code = 4;
                $ret->result->msg= "课程即将报满";       
            }           
            // 用户如果已经报名，且多班，给出提示
            if (!empty($uid)){
                // 用户是否报名,提示已报名班级名称
                $reu = $course_db->getCourseUserByFkuser($cid,$uid);

                if($reu){
                    $ret_class = course_db_courseClassDao::getClassInfo($reu['fk_class']);               
                    $reg_class_name = $ret_class['name'];
                    $ret->result->reg_class_name = $ret_class['name'];
                    $ret->result->sub_code = -5;
                    $ret->result->msg= "您已报名成功{$reg_class_name}";     
                }
                // 课程是否多班
                $rec = course_db_courseClassDao::getClassByCourseId($cid);        

                if(isset($rec->totalSize)){
                    $ret->result->class_count = $rec->totalSize;
                } elseif(isset($rec->items)) {
                    $ret->result->class_count = count($rec->items);
                }           
            }         
            
            return $ret;
        }
}