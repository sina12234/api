<?php
class video_api{        
        public  static $redisTime = 600;          // redis时间： 0 关闭缓存 ，>0 缓存时间 (单位秒)        
        public  static $perPlanMaxLimit = 20;  // 每个排课最多打点次数限制
        public  static $paramDb  = [
            'fk_video'  => 'vid',       // 视频ID
            'fk_plan'   => 'pid',       // 排课ID
            'fk_user'   => 'uid',       // 老师ID
            'thumbs'    => 'thumbs',    // 截图 thumbs,宽度 width,高度 height,行 rows,列 cols,间隔 intervals , 最后一张编号 last_num
            'content'   => 'content',   // 打点注释内容
            'status'    => 'st',        // 删除 -1 ， 初始 0 ， 正常 1
            'point_seg' => 'pseg',      // 打点视频段
            'point_time'=> 'ptime',     // 原始视频打点时间（秒）
            'seg_time'  => 'stime',     // 剪辑视频打点时间（秒）
            'create_time'=> 'ctime',   
            'last_updated'=> 'uptime',          
        ];
        /* 获取视频打点信息列表 */
        public static function getTeacherPointList($params){	
                $fk_plan    = empty($params['pid']) ?  0 :(int)$params['pid'] ; // 排课ID
                $order    = empty($params['order']) ?  "" :$params['order'] ;
                $redisTime  = isset($params['rtime']) ?  (int)$params['rtime'] : video_api::$redisTime ;
                if ($redisTime>video_api::$redisTime) $redisTime = video_api::$redisTime ;
                
                if(empty($fk_plan)) { return api_func::setMsg(1000);  }  
                
                $condition = [];
                $condition['fk_plan'] = $fk_plan;
                $condition['status'] = 1;

                $data = video_db_pointDao::listPoint($redisTime,$condition,0,0,'',$order);

                /* 格式化列表数据 */
                $dataParam = video_api::formatListData($data);

                $setConfig = ['count'=>$dataParam['count'],'rtime'=>$redisTime];
                
                return api_func::setDataConfig($dataParam['data'],$setConfig);
            
        }
      
        /* 格式化列表数据 */
        public static function formatListData($data){            
                $itemsParam = [];
                $dataCount = $data->pageSize;
                if ($dataCount>0){
                    $items = $data->items;
                    $paramDb = video_api::$paramDb ;
                    for($i=0; $i<$dataCount; $i++){
                        $itemParam = [];
                        foreach($paramDb as $dataName => $dataValue){
                            $itemParam[$dataValue] = $items[$i][$dataName];                            
                        }
                        $itemsParam[] = $itemParam;
                    }           
                }
                
                return ['count'=>$dataCount , 'data'=>$itemsParam];
        }
        
         
        /* 根据 point_time 获取剪辑信息（ point_seg , seg_time ）  */
        public static function getSegInfoByPointtime($point_time , $videoInfo){    
                $pointiData = [];
                $totaltime = empty($videoInfo['totaltime']) ? 0 : (int) $videoInfo['totaltime'];
                $segs_totaltime = empty($videoInfo['segs_totaltime']) ? 0 : (int) $videoInfo['segs_totaltime'];
                $segs      = empty($videoInfo['segs']) ? '' : $videoInfo['segs'];

                if(empty($segs)) {
                    $pointiData['seg_time']  = $point_time;                                
                    $pointiData['point_seg']  = "[0,$totaltime]";
                }  
                
                if(!empty($segs)) {
                    $segs = str_replace('"','',substr($segs, 1,-1));
                    if(is_array($segs)){
                        $segsArr = $segs;
                    } else {
                        $segsArr = explode('],', $segs);   
                    }

                    $segDurationSum = 0;  // seg段总时长
                    $segCount = count($segsArr);       

                    for($j=0; $j<$segCount; $j++){
                        if (empty($segsArr[$j])) return false;                                                   // 非法seg段
                        $segiStr = str_replace(['[',']'], '', $segsArr[$j]);
                        $segiArr   = explode(',', $segiStr);

                        if (empty($segiArr[1]) || $segiArr[0]>=$segiArr[1] ) return false; // 非法seg段
                        $segiA = $segiArr[0];
                        $segiB = $segiArr[1];
                        $segiDuration = $segiB - $segiA;              

                        if($point_time >= $segiA && $point_time <= $segiB){
                            $pointiData['point_seg'] = $point_seg = '['.$segiA.','.$segiB.']';
                            $pointiData['seg_time']  = $segDurationSum + $point_time - $segiA;  
                            break;
                        }
                        $segDurationSum += $segiDuration;
                    }   

                    if(empty($pointiData)) {
                        $pointiData['seg_time']  = 0;                                
                        $pointiData['point_seg']  = "[]";
                    }  
                } else {                    
                    $pointiData['seg_time']  = $point_time;                                
                    $pointiData['point_seg']  = "[0,$segs_totaltime]";
                }
                
                return $pointiData;            
        }
      
        /* 填充剪辑打点时间 */
        public static function computeThumbInfo($params){     
           $thumbsList = [];
           $fk_video   = empty($params['fk_video']) ? 0 : (int) $params['fk_video'];
           $point_time = empty($params['point_time']) ? 0 : (int) $params['point_time'];
           $thumb_num  = empty($params['thumb_num']) ? 1 : (int) $params['thumb_num'];
           $redisTime  = isset($params['rtime']) ?  (int)$params['rtime'] : video_api::$redisTime ;
           
           // 获取视频的截图信息
           $db = new video_db;
           $data = $db->getThumbs($fk_video);
           if(!empty($data)){
               $thumbsList = video_api::getThumbList($data , $params);
           } else {
               return '';
           }
            return $thumbsList;
        }
        /* 填充剪辑打点时间 */
        public static function getThumbList($data , $params)
        {
            $thumbsList = [];
            $point_time = empty($params['point_time']) ? 0 : (int)$params['point_time'];
            $thumb_num = empty($params['thumb_num']) ? 1 : (int)$params['thumb_num'];
            $width = empty($data['width']) ? 0 : (int)$data['width'];
            $height = empty($data['height']) ? 0 : (int)$data['height'];
            $rows = empty($data['rows']) ? 0 : (int)$data['rows'];
            $cols = empty($data['cols']) ? 0 : (int)$data['cols'];
            $thumbs = empty($data['thumbs']) ? '' : $data['thumbs'];
            $last_num = empty($data['last_num']) ? 0 : (int)$data['last_num'];
            $intervals = empty($data['intervals']) ? 0 : (int)$data['intervals'];
            $totalTime = 38500;
            if (empty($width) || empty($height) || empty($rows) || empty($cols) || empty($thumbs) || empty($last_num) || empty($intervals)) return '';              // 无效数据

            $thumbArr = explode(' ', $thumbs);
            $pageSize = $rows * $cols;                        // 每张图截图数量
            $indexPonit = $point_time / $intervals;             // 第N张截图
            $_page = ceil($indexPonit / $pageSize);        // 第几页
            if (!isset($thumbArr[$_page - 1])) return '';           // 超过实际时长
            $_thumb = $thumbArr[$_page - 1];                  // 截图名
            $_currThumb = $indexPonit - ($_page - 1) * $pageSize;   // 当前截图信息
            $_row = ceil($_currThumb / $cols);
            $_col = round($_currThumb - ($_row - 1) * $cols);
            if ($_col == 0) $_col = 1;

            $thumbsInfo = [
                'width' => $width,
                'height' => $height,
                'intervals' => $intervals,
                'last_num' => $last_num,
                'thumbs' => $_thumb,
                'page' => $_page,
                'rows' => $_row,
                'cols' => $_col,
                'left' => $width * ($_col - 1),
                'top' => $height * ($_row - 1),
                'thumb_num' => $thumb_num
            ];
            $thumbsList['thumb1'] = $thumbsInfo;

            if ($thumb_num > 1) {
                $basicPostionArr = ['width' => $width, 'height' => $height, 'rows' => $rows, 'cols' => $cols, '_row' => $_row, '_col' => $_col, 'thumb_num' => $thumb_num, 'point_time' => $point_time, 'intervals' => $intervals, 'last_num' => $last_num, 'thumbPage' => count($thumbArr)];
                $positionInfoArr = video_api::computeMutiThumbPositionByNum($basicPostionArr);
                for ($k = 1; $k <= $thumb_num; $k++) {
                    // 每张图截图数量
                    $indexPonit1 = $positionInfoArr[$k]['point_time'] / $intervals;             // 第N张截图
                    $_page1 = ceil($indexPonit1 / $pageSize);
                    $thumbsInfo = [
                        'width' => $width,
                        'height' => $height,
                        'intervals' => $intervals,
                        'last_num' => $last_num,
                        'thumbs' => !empty($thumbArr[$_page1 - 1]) ? $thumbArr[$_page1 - 1] : '',
                        'page' => $_page,
                        'rows' => $positionInfoArr[$k]['rows'],
                        'cols' => $positionInfoArr[$k]['cols'],
                        'left' => $positionInfoArr[$k]['left'],
                        'top' => $positionInfoArr[$k]['top'],
                        'is_current' => $positionInfoArr[$k]['is_current'],
                        'ptime' => $positionInfoArr[$k]['ptime'],
                        'point_time' => $positionInfoArr[$k]['point_time'],
                        'thumb_num' => $k,
                    ];
                    $thumbsList["thumbInfo"][$k - 1] = $thumbsInfo;
                }
            }
                
               return $thumbsList;
        }
        
        /* 计算左右N张图单张截图的偏移位置  */
        public static function computeOneThumbPositionByNum($basicPostionArr){
             $positionArr = [];
             $intervals  = empty($basicPostionArr['intervals']) ? 0 : (int) $basicPostionArr['intervals'];
             $point_time = empty($basicPostionArr['point_time']) ? 0 : (int) $basicPostionArr['point_time'];
             $rows    = empty($basicPostionArr['rows']) ?  0 : (int)$basicPostionArr['rows'];       // 总行数
             $cols    = empty($basicPostionArr['cols']) ?  0 : (int)$basicPostionArr['cols'];       // 总列数
             $width    = empty($basicPostionArr['width']) ?  0 : (int)$basicPostionArr['width'];    // 宽度
             $height   = empty($basicPostionArr['height']) ?  0 : (int)$basicPostionArr['height'];  // 高度
             $_row  = $_top   = empty($basicPostionArr['_row']) ?  0 : (int)$basicPostionArr['_row'];      // 当前行 , 对应 top=row
             $_col  = $_left   = empty($basicPostionArr['_col']) ?  0 : (int)$basicPostionArr['_col'];      // 当前列 , 对应 left=col
             $thumb_num = empty($basicPostionArr['thumb_num']) ?  1 : (int)$basicPostionArr['thumb_num'];   // 总张数
             
             /*  // [输入行为第1行]     i=1~3 : $i_top=当前行 ,    i=7~9 : $i_top=当前行+2  ,  i=4~6 : $i_top=当前行+1
                 // [输入行为中间行]    i=1~3 : $i_top=当前行-1 ,  i=7~9 : $i_top=当前行+1 ,   i=4~6 : $i_top=当前行  
                 // [输入行为最后1行]   i=1~3 : $i_top=当前行-2 ,  i=7~9 : $i_top=当前行  ,    i=4~6 : $i_top=当前行-1
                *  */
             $thumbNumRow = $thumbNumCol =  sqrt($thumb_num);             
             for($i=1; $i<=$thumb_num; $i++){ 
                     if($_row == 1){            // 第1行
                        if($i<=$thumbNumRow) $i_top = $_top; 
                        elseif($i>=$cols-$thumbNumRow) $i_top = $_top+2; 
                        else  $i_top = $_top+1;                      
                     }elseif($_row >= $rows){  // 最后1行
                        if($i<=$thumbNumRow) $i_top = $_top-1; 
                        elseif($i>=$cols-$thumbNumRow) $i_top = $_top+1; 
                        else  $i_top = $_top;                      
                     }else{                      // 中间行
                        if($i<=$thumbNumRow) $i_top = $_top-1; 
                        elseif($i>=$cols-$thumbNumRow) $i_top = $_top+1; 
                        else  $i_top = $_top;                              
                     }
                    /*
                 // [输入列为第1列]     i=1,4,7 : $i_left=当前列 ,   i=2,5,8 : $i_left=当前列+2  , i=3,6,9 : $i_left=当前列+1
                 // [输入列为中间列]    i=1,4,7 : $i_left=当前列-1 , i=2,5,8 : $i_left=当前列+1  , i=3,6,9 : $i_left=当前列
                 // [输入列为最后1列]   i=1,4,7 : $i_left=当前列-2 , i=2,5,8 : $i_left=当前列 ,    i=3,6,9 : $i_left=当前列-1
                   */
                     $i_indexCol = $i % $thumbNumCol;
                     if($_col == 1){             // 第1列
                        if($i_indexCol == 1) $i_left = $_left; 
                        elseif($i_indexCol == 0) $i_left = $_left+2; 
                        else  $i_left = $_left+1;                      
                     }elseif($_col >= $cols){ //  最后1列
                        if($i_indexCol == 1) $i_left = $_left-2; 
                        elseif($i_indexCol == 0) $i_left = $_left; 
                        else  $i_left = $_left-1;                      
                     }else{                     // 中间列
                        if($i_indexCol == 1) $i_left = $_left-1; 
                        elseif($i_indexCol == 0) $i_left = $_left+1; 
                        else  $i_left = $_left;                              
                     }
                     $i_is_current = 0;
                     if (($i_top == $_row) && ($i_left == $_col)) $i_is_current = 1;
                     
                     $positionArr[$i]['rows']   = $i_top;         // top  = row
                     $positionArr[$i]['cols']   = $i_left;        // left = col
                     $positionArr[$i]['top']    = $height  * ($i_top-1) * -1;
                     $positionArr[$i]['left']   = $width   * ($i_left-1) * -1;
                     // 行==当前行，列==当前列 ： is_current = 1;
                     $positionArr[$i]['is_current']   = $i_is_current;
                     $positionArr[$i]['ptime']        = $point_time;
                     $positionArr[$i]['point_time']   = $point_time + ((($i_top-$_row)*$cols)+($i_left-$_col))*$intervals;  
             }
             
             return $positionArr;
        }  
        
        /* 计算左右N张图的偏移位置
         * $method = center  || row || col
         */
        public static function computeMutiThumbPositionByNum($basicPostionArr , $method='center'){
             $positionArr = [];
             $intervals  = empty($basicPostionArr['intervals']) ? 0 : (int) $basicPostionArr['intervals'];
             $point_time = empty($basicPostionArr['point_time']) ? 0 : (int) $basicPostionArr['point_time'];
             $last_num = empty($basicPostionArr['last_num']) ? 1 : (int) $basicPostionArr['last_num'];
             $thumbPage = empty($basicPostionArr['thumbPage']) ? 1 : (int) $basicPostionArr['thumbPage'];
             $rows    = empty($basicPostionArr['rows']) ?  0 : (int)$basicPostionArr['rows'];//数据库配置好目前12行
             $cols    = empty($basicPostionArr['cols']) ?  0 : (int)$basicPostionArr['cols'];//数据库配置好目前10列
             $width    = empty($basicPostionArr['width']) ?  0 : (int)$basicPostionArr['width'];
             $height   = empty($basicPostionArr['height']) ?  0 : (int)$basicPostionArr['height'];
             $_row     = empty($basicPostionArr['_row']) ?  0 : (int)$basicPostionArr['_row'];//当前行
             $_col     = empty($basicPostionArr['_col']) ?  0 : (int)$basicPostionArr['_col'];//当前列
             $thumb_num = empty($basicPostionArr['thumb_num']) ?  1 : (int)$basicPostionArr['thumb_num'];//取都少张
             $thumbNumRowCol = floor($thumb_num/2);
             for($i=1;$i<=$thumb_num;$i++){
                 $top = $_row - 1;
                 $topC=0;
                 $left=0;
                 if ($_col > ($cols - $thumbNumRowCol*2 + 2) && $_col < ($cols - $thumbNumRowCol + 1)) {
                     $topC=$rows*((ceil($point_time/$intervals/$rows/$cols))-1);
                     if ($i <= $thumbNumRowCol) {
                         $left = $_col - $thumbNumRowCol + $i-1;
                     } elseif ($i >= $thumbNumRowCol) {
                         $left = $_col + ($i-1 - $thumbNumRowCol);
                     }
                 } elseif ($_col <= (($cols - $thumbNumRowCol*2 + 2))) {
                     if (($i - 1) <= (($thumbNumRowCol - $_col))) {
                         if((ceil($point_time/$intervals/($rows*$cols))>1)) {
                             if($_row>1) {
                                 $topC=$rows*(ceil($point_time/$intervals/$rows/$cols)-1);
                                 $top = $_row - 2;
                             }else{

                                 $topC=$rows*(ceil($point_time/$intervals/$rows/$cols)-2);
                                 $top = $rows-1;
                             }
                             $left = $cols - ($i - 1);
                         }else{
                             $top= $_row;
                             $topC=$rows*(ceil($point_time/$intervals/$rows/$cols)-1);
                             if(($_col+$thumbNumRowCol+$i)<=$cols){
                                 $top= $_row-1;
                                 $left=$_col+$thumbNumRowCol+$i;
                             }else{
                                 $left = $_col+$thumbNumRowCol+$i-$cols;
                             }
                         }

                     } else {
                         $top = $_row-1;
                         $topC=$rows*(ceil($point_time/$intervals/$rows/$cols)-1);
                         $left = $_col - $thumbNumRowCol + $i-1;
                     }
                 } elseif ($_col >= ($cols - $thumbNumRowCol + 1)) {
                     if (($_col + ($i - $thumbNumRowCol-1)) > $cols) {
                         $topC=$rows*(ceil($point_time/$intervals/$rows/$cols)-1);
                         if((ceil($point_time/$intervals/$rows/$cols)==$thumbPage) && (ceil($last_num/$cols)==$_row)){
                             if($last_num-$last_num%$cols*$cols-$_col>=1){
                                 $top= $_row-1;
                                 $left=$_col+($i-$thumbNumRowCol-1);

                             }else{
                                 $top = $top= $_row-2;
                                 $left = $i - ($thumbNumRowCol + $cols - $_col)-1;
                             }
                         }else{
                             $top = $_row;
                             $left = $i - ($thumbNumRowCol + $cols - $_col)-1;
                         }
                     } else {
                         $top = $_row-1;
                         $topC=$rows*(ceil($point_time/$intervals/$rows/$cols)-1);
                         $left = $_col + ($i - $thumbNumRowCol-1);
                     }
                 }
                 $positionArr[$i]['rows']   = $top+1;         // top  = row
                 $positionArr[$i]['cols']   = $left;        // left = col
                 $positionArr[$i]['top']    = $height  * $top;
                 $positionArr[$i]['left']   = ($left-1)*$width;
                 // 行==当前行，列==当前列 ： is_current = 1;
                 if($_row==($top+1)&& $_col==$left){
                     $positionArr[$i]['is_current']   = 1;
                 }else{
                     $positionArr[$i]['is_current']   = 0;
                 }
                 $positionArr[$i]['ptime']        = $point_time;
                 $positionArr[$i]['point_time']   = ($top+$topC)*$intervals*$cols + $left*$intervals;
             }
             return $positionArr;
        }        
        
        public static function update($video_id,$params){
		$video_db = new video_db;

		$video_info = $video_db->getVideo($video_id);
		if(empty($video_info))return false;

		$updates = array();
		if(!empty($params->title))	$updates['title']=$params->title;
		if(!empty($params->desc))	$updates['desc']=$params->desc;
		if(!empty($params->filename))	$updates['filename']=$params->filename;
		if(!empty($params->ip))	$updates['ip']=$params->ip;
		if(!empty($params->totaltime))	$updates['totaltime']=$params->totaltime;
		if(!empty($params->filename_org))	$updates['filename_org']=$params->filename_org;
		if(!empty($params->valid)){
			if($params->valid=="valid")$updates['valid'] = 1;
			if($params->valid=="invalid")$updates['valid'] = 0;
		}
		if(!empty($params->progress)){
			if($params->progress=="ok")$updates['progress'] = 1;
			if($params->progress=="encode")$updates['progress'] = 0;
			if($params->progress=="error")$updates['valid'] = -1;
		}
		if(!empty($params->status)){
			if($params->status=="enabled")$updates['status'] = 1;
			if($params->status=="disabled")$updates['status'] = -1;
		}

		if(!empty($params->segs)){//json string
			$updates['segs']=$params->segs;
		}

		if(!empty($params->segs_totaltime)){
			$updates['segs_totaltime']=$params->segs_totaltime;
		}else{
			//如果没有传的话,计算新值
			$segs = array();
			$totaltime= !empty($params->totaltime) ? $params->totaltime : $video_info['totaltime'];
			if(!empty($params->segs)){
				$segs = SJson::decode($params->segs);
			}else{
				$segs = SJson::decode($video_info['segs']);
			};
			if(empty($segs) ){
				$updates['segs_totaltime']=$totaltime;
			}else{
				$t=0;
				foreach($segs as $seg){
					if(isset($seg[1]) && isset($seg[0])){
						$t += $seg[1] - $seg[0];
					}
				}
				$updates['segs_totaltime']=$t;
			}

		}
		if(!empty($params->type)){
			$updates['type']=$params->type;
		}
		$ar = get_object_vars($params);

		$thumbs = array();
		for($i=0;$i<=8;$i++){
			$key = "thumb$i";
			if(array_key_exists($key, $ar)){
				$updates[$key] = $ar[$key];
				array_push($thumbs, $ar[$key]);
			}
		}

		$updates['last_updated']=date("Y-m-d H:i:s");
		$r = $video_db->update($video_id,$updates);
		if($r!==false){
			if(!empty($params->finish_type)){
				$video_db->addType($video_id, null, $params->finish_type);
			}
			if(!empty($params->has_type)){
				$video_db->addType($video_id, $params->has_type, null);
			}
		}

		$db_log = new log_db;
		$data = new stdclass;
		$data->video_id = $video_id;
		$data->thumbs = implode(" ", $thumbs);
		$db_log->addThumbLog($data);

		return $r;
	}
}
