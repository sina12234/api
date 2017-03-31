<?php
class course_promote{
	/**
	 * @docs https://wiki.gn100.com/doku.php?id=docs:api:course
	 * 验证Promote 推广课程
	 */
       
         /* 添加推广课程 */
	public function pageAddPromoteCourse($inPath){
            $ret = new stdclass();
            $ret->result = new stdClass();
            $ret->result->code = 0;
            $ret->result->msg  = "success";
            $ret->data = '';
            $pcourse = SJson::decode(utility_net::getPostData(),true);
            $course_id = empty($pcourse['course_id']) ? 0 : $pcourse['course_id'];
            $price = empty($pcourse['price']) ? 0 : (float) $pcourse['price']; // 原价
            $price_promote = empty($pcourse['price_promote']) ? 0 : (float) $pcourse['price_promote']; // 成本价

            if (empty($course_id) || (!empty($price) && empty($price_promote))   ) {
                $ret->result->code = -1;
                $ret->result->msg= "course_id or price_promote is not set!";
                return $ret;
            }
            // 已参与推广的课程不能再次参加推广
            
            $pcourse_info = course_db_promoteDao::getPromoteCourseById($course_id);
            if (isset($pcourse_info['fk_course']) && !empty($pcourse_info['fk_course']) && ($pcourse_info['status']<>-1)){
                $ret->result->code = -2;
                $ret->result->msg= "已参与推广的课程不能再次参加推广!";
                return $ret;
            }
            // 已报满的课程不能新增推广
            $course_db = new course_db;
            $course_info = $course_db->getCourse($course_id);
            if($course_info['max_user']==$course_info['user_total']){
                $ret->result->code = -4;
                $ret->result->msg= "课程已报满，不能参与推广!";
                return $ret;
            }
            $result = course_resell_api::addPromoteCourse($pcourse);
            if(!isset($result)){
                $ret->result->code = -5;
                $ret->result->msg= "新增推广课程失败!";
                return $ret;
            } else {
                $op = 'orgAddDeletedCourse';
                $params['promote'] = "price_promote=$price_promote";                
                $where = new stdClass();
                $where->course_id = $course_id;                    
                $ret = course_resell_api::ryncPromoteResellCourse($op,$where,$params);  
            }
            return $ret;
	}
        
         /* 编辑推广课程(修改成本价) */
	public function pageEditPromoteCourse($inPath){
		$ret = new stdclass();
                $ret->result = new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                $ret->data = '';
                
                $pcourse = SJson::decode(utility_net::getPostData());
                $op = empty($inPath[3]) ? (empty($inputData['op']) ? '':$inputData['op']) : $inPath[3];
                $course_id = empty($inPath[4]) ? (empty($pcourse->course_id) ? 0 : $pcourse->course_id): $inPath[4] ;
                $price_old = empty($pcourse->price_old) ? 0 : (float) $pcourse->price_old ;
                $price_promote = empty($pcourse->price_promote) ? 0 : (float) $pcourse->price_promote ;
                
		if (empty($course_id) ||  (!empty($price_old) && empty($price_promote))) {
                    $ret->result->code = -1;
                    $ret->result->msg = "course_id({$course_id}) or price_promote({$price_promote}) is not set!";
                    return $ret;
		} else {
                    $allowOps = array('orgStartPromote','updatePromotePrice');
                    if (!in_array($op,$allowOps)) {
                        $ret->result->code = -2;
                        $ret->result->msg= "the opration is not allowed({$op})!";
                        return $ret;
                    }
                    
                    $params['promote'] = "price_promote=$price_promote";
                    $where = new stdClass();
                    $where->course_id = $course_id;
                    $ret = course_resell_api::ryncPromoteResellCourse($op,$where,$params);  
                    if($ret === false){
                        $ret->result->code = -4;
                        $ret->result->msg = 'change ver&status failed';
                        return $ret;
                    }
                }
		return $ret;            
        }        
        
        /* 获取推广课程列表 */
	public function pageGetPromoteCourseList($inPath){
		$ret = new stdclass();
                $ret->result = new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                $ret->data = '';
                
                $indata = SJson::decode(utility_net::getPostData());
                $page = empty($inPath[3]) ? 1 : $inPath[3];
                $size = empty($inPath[4]) ? 20 : $inPath[4];
                
               
                $pcourse_list = course_db_promoteDao::getPromoteCourseList($page,$size,$indata); 
                
                if (!empty($pcourse_list->items)){
                    $ret->data = $pcourse_list;
                }
                return $ret;
        }
        
    /*获取机构推广课程数量*/
    public function pageCoursePromoteCount($inPath){
        $params = SJson::decode(utility_net::getPostData(),true);
        
        $PromoteCount = course_db_promoteDao::getCoursePromoteCount($params);
        if(empty($PromoteCount)){
            $PromoteCount=0;
        }
        return api_func::setData($PromoteCount);
    }
    
    
         /* 同步课程&推广课程&分销状态信息
          */
	public function pageSyncPRC($inPath){
		$ret = new stdclass();
                $ret->result = new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "操作成功";
                $ret->data = '';
                
                $inputData = SJson::decode(utility_net::getPostData(),true);     
                $course_id   = isset($inputData['cid']) ? $inputData['cid'] : (!empty($inputData['course_id']) ? $inputData['course_id']:0);     
                $org_id_resell = isset($inputData['oid']) ? $inputData['oid'] : (!empty($inputData['org_id']) ? $inputData['org_id']:0);       
                $price_promote = isset($inputData['pricep']) ? (float) $inputData['pricep'] : (!empty($inputData['price_promote']) ? (float) $inputData['price_promote']:0);  // 单位：分           
                $op = empty($inPath[3]) ? (empty($inputData['op']) ? '':$inputData['op']) : $inPath[3];
     
		if (empty($course_id)) {
                    $ret->result->code = -1;
                    $ret->result->msg= "课程ID为空!";
                    return $ret;
		}  
                
                $allowOps = array('orgDelPromote','orgStopPromote','updateCourseFeetype','updateCoursePrice','courseMaxUser');
                if (!in_array($op,$allowOps)) {
                    $ret->result->code = -2;
                    $ret->result->msg= "the opration is not allowed({$op})!";
                    return $ret;
		}
                // 推广状态status=-3 或 status_code=-31 ，不能再更改推广状态
                $pcourse_info = course_db_promoteDao::getPromoteCourseById($course_id);
                if (($pcourse_info['status']<0 || $pcourse_info['status_code']==-31) && ($op<>'orgDelPromote')){
                    $ret->result->code = -4;
                    $ret->result->msg= "推广课程异常!";
                    return $ret;
                }
                $params =[];
                $where = new stdClass();
                $where->course_id = $course_id;
                $where->org_id_resell = $org_id_resell;
                    
                $ret = course_resell_api::ryncPromoteResellCourse($op,$where,$params);  
                                 
		return $ret;                
        }
        
        /* 获取课程状态&详情 */
	public function pageGetResellCourseInfo($inPath){
                $inputData = SJson::decode(utility_net::getPostData());     
                $course_id   = empty($inPath[3]) ? (empty($inputData->cid) ? 0 :$inputData->cid) : $inPath[3];  

		$ret = course_resell_api::getResellCourseInfo($course_id,$inputData);

                return $ret;
        }
}
