<?php	
/*
 * 视频打点
 */
class video_point
{
        /* 保存视频打点信息 */
        public function pageAddTeacherPoint(){
                $params = SJson::decode(utility_net::getPostData(),true);	

                $fk_video   = empty($params['vid']) ? 0 : (int)$params['vid'] ; // 视频ID
                $fk_plan    = empty($params['pid']) ?  0 :(int)$params['pid'] ; // 排课ID
                $fk_user    = empty($params['uid']) ?  0 :(int)$params['uid'] ; // 老师ID 
                $status     = empty($params['st']) ? 1 : (int)$params['st'] ;   // 删除 -1 ， 初始 0 ， 正常 1
                $content    = isset($params['content']) ?  $params['content'] : '';
                $point_time = empty($params['ptime']) ?  0 : (int) $params['ptime'] ; // 原始视频打点时间（秒）
                
                $params = ['fk_video'=>$fk_video , 'point_time'=>$point_time , 'thumb_num'=>1 ];
                $thumbsList = video_api::computeThumbInfo($params);
                
                /* 截图 thumbs,宽度 width,高度 height,行 rows,列 cols,间隔 intervals , 最后一张编号 last_num */
                $thumbs  = empty($thumbsList['thumb1']) ? '' : json_encode($thumbsList['thumb1']);
                
                $pointData = [];
                $msg = '';
                foreach(video_api::$paramDb as $dataName => $dataValue){
                    if(isset($$dataName)) {
                        $pointData[$dataName] = $$dataName;
                        if(empty($$dataName)) $msg .= $dataValue.'|';
                    }
                }
                
                if(empty($fk_video) || empty($fk_plan) || empty($fk_user) || empty($content) || empty($point_time)) {
                    return api_func::setMsg(1000,$msg);
                }
                
                // 非法视频ID
                $planInfo = course_db_coursePlanDao::getPlanById($fk_plan);  
                if ($planInfo === false) {  return api_func::setMsg(5001,"无效planID");  }      
                if ($planInfo['fk_video'] <> $fk_video)  {  return api_func::setMsg(5002,"无效videoID");  }   
                
                // 请不要重复打点
                $conditionRow = [];
                $conditionRow['fk_plan'] = $fk_plan;
                $conditionRow['point_time'] = $point_time;
                $conditionRow['status'] = 1;
                $rowData = video_db_pointDao::rowPoint($conditionRow); 
                if (!empty($rowData)) return api_func::setMsg(5003,"请不要重复打点");
                
                // 每个排课最多20个打点次数限制
                $conditionList = [];
                $conditionList['fk_plan'] = $fk_plan;
                $conditionList['status'] = 1;
                $listData = video_db_pointDao::listPoint(0 , $conditionList);                
                if(!empty($listData)){
                    $listDataCount = $listData->pageSize;
                    $perPlanMaxLimit = video_api::$perPlanMaxLimit;
                    if ($listDataCount >= $perPlanMaxLimit){ return api_func::setMsg(5004,"打点次数超过上限（{$perPlanMaxLimit}次）"); }
                }
                
                $time  = date("Y-m-d H:i:s");
                $pointData['create_time'] = $time;
                $pointData['last_updated'] = $time;
            $update["status"]=1;
            $update["content"]=$content;
            $update['last_updated'] = $time;
            $data = video_db_pointDao::addPoint($pointData,$update);
                
                return api_func::setData($data);
        }       		
        
        /* 更改视频打点信息(删除; 修改原视频打点时间) */
        public function pageUpdateTeacherPoint($inPath){
                $params = SJson::decode(utility_net::getPostData(),true);		

                $fk_plan    = empty($params['pid']) ?  0 :(int) $params['pid'] ; // 排课ID
                $fk_video   = empty($params['vid']) ?  0 :(int) $params['vid'] ; // 视频ID
                $point_time = empty($params['ptime']) ?  0 : (int) $params['ptime'] ; // 原始视频打点时间（秒）
                $status     = empty($params['st']) ? 0 : (int) $params['st'] ;  // 删除 -1 ， 初始 0 ， 正常 1
                $point_time_new = empty($params['point_time']) ?  0 : (int) $params['point_time'] ; // 原始视频打点时间（秒）(新)

                $paramCount = count($params);
                if(empty($point_time) || $paramCount<=2) { return api_func::setMsg(1000); }     
                if(empty($fk_plan) && empty($fk_video)) {  return api_func::setMsg(1001); }
                
                $condition = [];
                $pointData = [];                
                
                if(isset($params['st'])) {                    
                    $condition['fk_plan'] = $fk_plan;
                    $condition['point_time'] = $point_time;
                    
                    $pointData['status'] = $status;
                    
                    $data = video_db_pointDao::updatePoint($condition,$pointData);
                }
                if(isset($params['point_time'])) {
                    $params = ['fk_video'=>$fk_video , 'point_time'=>$point_time_new , 'thumb_num'=>1 ];
                    $thumbsList = video_api::computeThumbInfo($params);
                    /* 截图 thumbs,宽度 width,高度 height,行 rows,列 cols,间隔 intervals , 最后一张编号 last_num */
                    $thumbs  = empty($thumbsList['thumb1']) ? '' : json_encode($thumbsList['thumb1']);
                    
                    $pointData['thumbs'] = $thumbs;
                    $pointData['point_time'] = $point_time;
                    
                    $condition['fk_video']   = $fk_video;
                    $condition['point_time'] = $point_time;         
                    
                    $data = video_db_pointDao::updatePoint($condition,$pointData);
                }
                
                return api_func::setData($data);
        }    
        
        /* 获取视频打点信息列表 */
        public function pageGetTeacherPointList(){
                $params = SJson::decode(utility_net::getPostData(),true);	 

                $ret = video_api::getTeacherPointList($params);
                
                return $ret;
        }
        
        
        /* 更新视频(重新上传视频 reupload ，删除视频 del ，转码视频 uploadtask ) */
        public function pageChangeVideoTeacherPoint(){
                 $params = SJson::decode(utility_net::getPostData(),true);	
                 $fk_plan  = empty($params['pid']) ?  0 :(int) $params['pid'] ; // 排课ID
                 $act      = isset($params['act']) ? $params['act'] : '' ;                  
                 
                 if(empty($fk_plan) || empty($act)) { return api_func::setMsg(1000); }     
                 
                 $allowActArr = ['reupload','del','uploadtask'];
                 if(!in_array($act, $allowActArr)) { return api_func::setMsg(5001,'非法操作'.$act); }    
                 
                 $condition = 'fk_plan='.$fk_plan;
                 $ret = video_db_pointDao::delPoint($condition);
                 
                 if ($ret===0) return api_func::setMsg(5002,'操作失败！');
                 
                 if ($ret===1) return api_func::setData($ret);
        }
        
        /* 获取视频指定时间点截图 */
        public function pageGetVideoThumbsPointTime(){
            $params = SJson::decode(utility_net::getPostData(),true);	

            $fk_video   = empty($params['vid']) ? 0 : (int)$params['vid'] ;         // 视频ID
            $point_time = empty($params['ptime']) ?  0 : (int) $params['ptime'] ;   // 原始视频打点时间（秒） 
            $thumb_num = empty($params['num']) ?  1 : (int) $params['num'] ;        // 截图张数           
            
            if(empty($fk_video) || empty($point_time)) { return api_func::setMsg(1000);  }  
            
            $params = ['fk_video'=>$fk_video , 'point_time'=>$point_time , 'thumb_num'=>$thumb_num ];
            
            $thumbsList = video_api::computeThumbInfo($params);
            
            return api_func::setData($thumbsList);
        }

        /* 获取打点信息9张小图 */
        public function pageGetPointThumbInfo($inpath){
            $params = SJson::decode(utility_net::getPostData(),true);
            $fk_plan    = empty($params['pid']) ?  0 :(int) $params['pid'] ; // 排课ID
            $fk_video   = empty($params['vid']) ?  0 :(int) $params['vid'] ; // 视频ID
            $point_time = empty($params['ptime']) ?  0 : (int) $params['ptime'] ; // 原始视频打点时间（秒）
            $thumb_num = empty($params['thumb_num'])? 1:(int)$params['thumb_num'];
            $paramCount = count($params);
            if(empty($point_time) || $paramCount<=2) { return api_func::setMsg(1000); }
            if(empty($fk_plan) && empty($fk_video)) {  return api_func::setMsg(1001); }
            if(isset($params['ptime'])) {
                $params = ['fk_video'=>$fk_video , 'point_time'=>$point_time , 'thumb_num'=>$thumb_num ];
                $thumbsList = video_api::computeThumbInfo($params);
                return api_func::setData($thumbsList);
            }
        }
}
        
        
        
        