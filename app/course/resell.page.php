<?php
class course_resell{
	/**
	 * @docs https://wiki.gn100.com/doku.php?id=docs:api:course
	 * 验证ResellCourse 分销课程
	 */
       
         /* 添加分销课程(引入课程) */
	public function pageAddResellCourse($inPath){
		$ret = new stdclass();
                $ret->result = new stdClass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                $ret->data = '';
                $pcourse = SJson::decode(utility_net::getPostData(),true);
                $course_id = empty($pcourse['course_id']) ? 0 : $pcourse['course_id'];
                $org_id    = empty($pcourse['org_id']) ? 0 : $pcourse['org_id'];
                $price_resell = (float) $pcourse['price_resell'];
                $op = empty($pcourse['op']) ? '' : $pcourse['op'];
		if (empty($course_id) || empty($org_id) ) {
                    $ret->result->code = -1;
                    $ret->result->msg= "course_id or org_id or price_resell is not set!";
                    return $ret;
		}
                
                $allowOps = array('orgResell','orgStartResell','updateResellPrice');
                if (!in_array($op,$allowOps)) {
                    $ret->result->code = -2;
                    $ret->result->msg= "the opration is not allowed({$op})!";
                    return $ret;
		}
                
                // 已参与分销的课程不能再次参加分销                
                $pcourse_info = course_db_resellDao::getResellCourseById($course_id ,$org_id);
                
                $promote_course_info = course_db_promoteDao::getPromoteCourseById($course_id);

                $resell_status = (int) $pcourse_info['status'];
                $resell_course_id = (int) $pcourse_info['fk_course'];
                $promote_course_id = (int) $promote_course_info['fk_course'];
                if (!empty($resell_course_id) && ($resell_status >= 0)){                    
                    if ($op == 'orgStartResell' &&  $resell_status == 1){  // 正常状态课程重新引入                      
                    } else {
                        $ret->result->code = -3;
                        $ret->result->msg= "已参与分销的课程不能再次参加分销{$resell_status}!";
                        return $ret;
                    }
                }
                if (empty($promote_course_id)){                    
                    $ret->result->code = -4;
                    $ret->result->msg= "不能引入已失效的推广课程{$promote_course_id}!";
                    return $ret;
                }
                if($promote_course_info['max_user']==$promote_course_info['user_total']){          
                    $ret->result->code = -5;
                    $ret->result->msg= "抱歉！课程已报满不能引入";
                    return $ret;
                }
                if (!empty($resell_course_id) && ($resell_status < -10)){                    
                    $ret->result->code = -6;
                    $ret->result->msg= "抱歉！该课程已停止推广";
                    return $ret;
                }
                
                /* 1、已删除的分销课程重新引入 (resell_status=-1)
                 * 2、已失效可引入的分销课程重新引入  (-10<resell_status<-1)
                 * 3、修改分销课程出售价格  (resell_status=1 && op=orgStartResell)
                 */                
                if ((!empty($resell_course_id) && (
                        ($resell_status == -1)
                        || ($resell_status < -1 && $resell_status >= -10 )
                        || ($resell_status == 1)
                    ))){ 
                    $resell_ver = empty($promote_course_info['ver']) ? 1 : $promote_course_info['ver'];  
                    $params['resell'] = "ver=$resell_ver,price_resell=$price_resell";
                
                    $where = new stdClass();
                    $where->course_id = $course_id;
                    $where->org_id_resell = $org_id;

                    $ret = course_resell_api::ryncPromoteResellCourse($op,$where,$params);                      
                } else {                 
                    $ipcourse["fk_org_resell"]  = $org_id;
                    $ipcourse["price_resell"]   = $price_resell;
                    $ipcourse["fk_course"]   = $course_id;
                    $ipcourse["create_time"]   = date("Y-m-d H:i:s");                    
                    $ipcourse["ver"]  = empty($promote_course_info['ver']) ? 1 : $promote_course_info['ver'];  
                    
                    $iRet = course_resell_api::addResellCourse($ipcourse);     
                    if($iRet  === false){
                        $ret->result->code = -8;
                        $ret->result->msg= "立即引入失败！";
                        return $ret;
                    } 
                    // 推广课程表org_count+1
                    $updateProArr = ["org_count=org_count+1"];
                    $upProRet = course_resell_api::updatePromoteCourse($course_id ,$updateProArr);
                }

		return $ret;
	}
        
         /* 编辑分销课程,修改成本价 */
	public function pageEditResellCourse($inPath){
		$ret = new stdclass();
                $ret->result=new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                $ret->data = '';
                $pcourse = SJson::decode(utility_net::getPostData());
                $course_id = empty($inPath[3]) ? (empty($pcourse->course_id) ? 0 : $pcourse->course_id): $inPath[3] ;
                $org_id = empty($inPath[4]) ? (empty($pcourse->org_id) ? 0 : $pcourse->org_id): $inPath[4] ;
                $price_resell = empty($pcourse->price_resell) ? 0 :(float) $pcourse->price_resell ;
                
		if (empty($course_id) || empty($org_id) || empty($price_resell)) {
                    $ret->result->code = -1;
                    $ret->result->msg= "course_id({$course_id}) or org_id({$org_id}) or price_resell({$price_resell}) is not set!";
                    return $ret;
		} else {
                    $ucourse = ["price_resell='$price_resell'"];
                    $uret = course_resell_api::updateResellCourse($course_id ,$org_id ,  $ucourse);
                    if($uret === false){
                        $ret->result->code = -3;
                        $ret->result->msg = 'change price faild';
                        return $ret;
                    }
                }
		return $ret;            
        }
        
         /* (删除)分销课程 */
	public function pageChangeResellCourse($inPath){
                $ret = new stdclass();
                $ret->result=new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "操作成功";
                $ret->data = '';
                $indata = SJson::decode(utility_net::getPostData());
                $op = empty($inPath[3]) ? '' : $inPath[3];   // op in ('orgDelResell')
                $courseId = empty($indata->course_id) ? 0 : $indata->course_id;
                $orgId = empty($indata->org_id) ? 0 : $indata->org_id;
 
                if (empty($courseId) || empty($orgId) || empty($op)) {
                    $ret->result->code = -1;
                    $ret->result->msg= "course_id or org_id or operation is not set!";
                    return $ret;
		}
                
                $allowOps = array('orgDelResell');
                if (!in_array($op,$allowOps)) {
                    $ret->result->code = -2;
                    $ret->result->msg= "the opration is not allowed({$op})!";
                    return $ret;
		}
                
                $where = new stdClass();
                $where->course_id = $courseId;
                $where->org_id_resell = $orgId;
                    
                $ret = course_resell_api::ryncPromoteResellCourse($op,$where);  
                
		return $ret;
        }
        
         /* 用户是否引入推广课程 */
	public function pageGetUserCourseRelation($inPath){
                $ret = new stdclass();
                $ret->result = new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                $ret->data = '';

                $indata = SJson::decode(utility_net::getPostData());
                $userId = empty($indata->uid) ? 0 : $indata->uid;
                $courseIds = empty($indata->course_id) ? '' : $indata->course_id;
                
                if (empty($userId) || empty($courseIds) ) {
                    $ret->result->code = -1;
                    $ret->result->msg= "uid or course_id  is not set!";
                    return $ret;
		}
                             
                $condition = "fk_org_resell ={$userId} and fk_course in ({$courseIds}) and status<>-1 and status<>-14";
                $pcourse_info = course_db_resellDao::getResellCourse($courseIds,$userId,$condition);
  
                if (!empty($pcourse_info->items)){
                    $ret->data = $pcourse_info;
                }
                return $ret;
        }
        
        /* 获取分销课程列表 */
	public function pageGetResellCourseList($inPath){
		$ret = new stdclass();
                $ret->result = new stdclass();
                $ret->result->code = 0;
		$ret->result->msg  = "success";
                $ret->data = '';
                
                $indata = SJson::decode(utility_net::getPostData());
                $page = empty($inPath[3]) ? 1 : $inPath[3];
                $size = empty($inPath[4]) ? 20 : $inPath[4];

                $this->UpdateStatus($indata->uid);
                
                $pcourse_list = course_db_resellDao::getResellCourseList($page,$size,$indata); 
                
                if (!empty($pcourse_list->items)){
                    $ret->data = $pcourse_list;
                }
                return $ret;
        }
        /*获取机构引入分销课程数量*/
        public function pageCourseResellCount($inPath){
            $params = SJson::decode(utility_net::getPostData(),true);
            
            $resellCount = course_db_resellDao::getCourseResellCount($params['orgResellId']);
            if(empty($resellCount)){
                $resellCount=0;
            }
            return api_func::setData($resellCount);
        }

    /*获取分销、推广成交记录*/
    public function pageGetCourseResellLog($inPath)
	{    
        $page   = !empty($inPath[3]) ? (int)$inPath[3] : 1;
        $length = !empty($inPath[4]) ? (int)$inPath[4] : -1;
		
		$params = SJson::decode(utility_net::getPostData(),true);
                
		if(empty($params)) return array('code'=>-1,'msg'=>'params error','data'=>array());
		
		$courseResellLogList = course_resell_api::getCourseResellLog($page, $length, $params);
		if(empty($courseResellLogList->items)) return array('code'=>-1,'msg'=>'data error','data'=>array());
		
		$idArr = array();
		foreach($courseResellLogList->items as $val){
			if($params['type'] == 1){
				$idArr['orgIdArr'][] = $val['org_resell_id'];
			}elseif($params['type'] == 2){
				$idArr['orgIdArr'][] = $val['org_promote_id'];
			}
			$idArr['userIdArr'][] = $val['user_id'];
		}
		
		$user_db = new user_db();
		
		//机构信息
        $orgInfo = $user_db->getOrgProfileByOidArr($idArr['orgIdArr']);
		$subname = $user_owner =array();
		if(!empty($orgInfo->items)){
			foreach($orgInfo->items as $val){
				$subname[$val['fk_org']]  = !empty($val['subname']) ? $val['subname'] : '';
                                $user_owner[$val['fk_org']]['fk_user_owner']  = $val['fk_user_owner'];
                                $subdomain = $user_db->getSubDomainByUserId($val['fk_user_owner']);
                                $user_owner[$val['fk_org']]['subdomain'] = $subdomain['subdomain'];
			}
		}

		//用户信息
		$userInfo = $user_db->getUserProfileByUidArr($idArr['userIdArr']);
		$userName = array();
		if(!empty($userInfo)){
			foreach($userInfo->items as $val){
				$userName[$val['user_id']] = !empty($val['real_name']) ? $val['real_name'] : '';
			}
		}
		
		foreach($courseResellLogList->items as &$v){
			if($params['type'] == 1){
				$v['org_subname'] = !empty($subname[$v['org_resell_id']]) ? $subname[$v['org_resell_id']] : '';
                                $subdomain = !empty($user_owner[$v['org_resell_id']]['subdomain']) ? $user_owner[$v['org_resell_id']]['subdomain'] : '';
                                $v['subdomain'] = (stripos($subdomain, ".")!==false) ? $subdomain :$subdomain.'.yunke.com' ;
			}elseif($params['type'] == 2){
				$v['org_subname'] = !empty($subname[$v['org_promote_id']]) ? $subname[$v['org_promote_id']] : '';
                                $subdomain = !empty($user_owner[$v['org_promote_id']]['subdomain']) ? $user_owner[$v['org_promote_id']]['subdomain'] : '';
                                $v['subdomain'] = (stripos($subdomain, ".")!==false) ? $subdomain :$subdomain.'.yunke.com' ;
			}
			
			$v['user_name'] = !empty($userName[$v['user_id']]) ? $userName[$v['user_id']] : '';
			$v['price_resell']  = $v['price_resell']/100;
            $v['price_promote'] = $v['price_promote']/100;
            $v['income']        = $v['price_resell'] - $v['price_promote'];
		}
		
		return array(
            "page"  => $courseResellLogList->page,
            "size"  => $courseResellLogList->pageSize,
            "total" => $courseResellLogList->totalPage,
            "totalSize" => $courseResellLogList->totalSize,
            "data"      => $courseResellLogList->items
        );
    }

    public function pageGetCourseResell()
    {
        $params      = SJson::decode(utility_net::getPostData(), true);
        $courseId    = isset($params['courseId']) && (int)($params['courseId']) ? (int)($params['courseId']) : 0;
        $resellOrgId = isset($params['resellOrgId']) && (int)($params['resellOrgId']) ? (int)($params['resellOrgId']) : 0;

        if (!$courseId || !$resellOrgId) return api_func::setMsg(1000);
        $res = course_db_resellDao::getCourseResell($courseId, $resellOrgId);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }

    /* 更新推广机构订单数&收入 */
    public function pageUpdatePromoteOrderNum()
    {
        $params      = SJson::decode(utility_net::getPostData(), true);
        $courseId    = isset($params['courseId']) && (int)($params['courseId']) ? (int)($params['courseId']) : 0;
        $inCome      = isset($params['inCome']) && (int)($params['inCome']) ? (int)($params['inCome']) : 0;
		
        if (!$courseId) return api_func::setMsg(1000);
        if (course_db_promoteDao::updateOrderCountAndIncome($courseId, $inCome))
            return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageGetCoursePromote($inPath)
    {
        $courseId    = isset($inPath['3']) && (int)($inPath['3']) ? (int)($inPath[3]) : 0;

        if (!$courseId) return api_func::setMsg(1000);
        $res = course_db_promoteDao::getCoursePromote($courseId);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
    }
    /* 更新分销机构订单数&收入 */
    public function pageUpdateResellOrderNum()
    {
        $params      = SJson::decode(utility_net::getPostData(), true);
        $courseId    = isset($params['courseId']) && (int)($params['courseId']) ? (int)($params['courseId']) : 0;
        $resellOrgId = isset($params['resellOrgId']) && (int)($params['resellOrgId']) ? (int)($params['resellOrgId']) : 0;
        $inCome      = isset($params['inCome']) && (float)($params['inCome']) ? (float)($params['inCome']) : 0;
		
        if (!$courseId || !$resellOrgId) return api_func::setMsg(1000);
        if (course_db_resellDao::updateOrderCountAndIncome($courseId, $resellOrgId, $inCome))
            return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageAddResellLog()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $courseResellId = isset($params['courseResellId']) && (int)($params['courseResellId']) ? (int)($params['courseResellId']) : 0;
        $resellOrgId    = isset($params['resellOrgId']) && (int)($params['resellOrgId']) ? (int)($params['resellOrgId']) : 0;
        $promoteOrgId   = isset($params['promoteOrgId']) && (int)($params['promoteOrgId']) ? (int)($params['promoteOrgId']) : 0;
        $userId         = isset($params['userId']) && (int)($params['userId']) ? (int)($params['userId']) : 0;
        $orderContentId = isset($params['orderContentId']) && (int)($params['orderContentId']) ? (int)($params['orderContentId']) : 0;
        $orderId        = isset($params['orderId']) && (int)($params['orderId']) ? (int)($params['orderId']) : 0;
        $priceResell    = isset($params['priceResell']) && (float)($params['priceResell']) ? (float)($params['priceResell']) : 0;
        $pricePromote   = isset($params['pricePromote']) && (float)($params['pricePromote']) ? (float)($params['pricePromote']) : 0;
        
        if (!$userId || !$courseResellId || !$resellOrgId || !$promoteOrgId) return api_func::setMsg(1000);     
        
        $data = [
            'fk_course_resell' => $courseResellId,  // 课程ID
            'fk_org_resell'    => $resellOrgId,     // 分销机构ID
            'fk_org_promote'   => $promoteOrgId,    // 推广机构ID
            'fk_user'          => $userId,          // 报名人
            'fk_order_content' => $orderContentId,
            'fk_order'         => $orderId,
            'price_resell'     => $priceResell,
            'price_promote'    => $pricePromote,
            'status'           => 1,
            'create_time'      => date('Y-m-d H:i:s', time()),
        ];
        
        $rei = course_db_resellLogDao::add($data);  

        if ($rei!==false){            
            // 分销报名记录插入成功，更新分销报名人数
            $where = new stdClass();
            $where->course_id = $courseResellId;
            $where->org_id_resell = $resellOrgId;
            $ret = course_resell_api::ryncPromoteResellCourse('updateEnrollCount',$where);       
            
             return api_func::setMsg(0);
	} 

        return api_func::setMsg(1);
    }
	public function pagegetSalesCourse()
	{	
		$ret = new stdclass;
       
        $ret->data = '';
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		if(!empty($params->con)){
				$data['con'] =  $params->con;
		}
		$data['fk_org_resell'] = !empty($params->fk_org_resell) ? $params->fk_org_resell : '';
		$courseInfo = course_db_resellDao::getSalesCourse($data);
		$ret->data = $courseInfo->items;
		return $ret;
	}

    /*同步resell的status状态码*/
    public function UpdateStatus($orgId){
        if(!isset($orgId)){
            return array('code'=>-1,'msg'=>'orgId error','data'=>array());
        }
        $resell = course_resell_api::getPromoteStatusNotOnVarNot($orgId);
        if(!empty($resell->items)){
            $op='syncResell';
            $where = new stdClass();
            foreach($resell->items as $v){
                if ($v['status']<>-1) {
                    $params['resell'] = "status=".$v['status_code']."";
                    $where->course_id = $v['course_id'];
                    $where->org_id_resell = $v['org_resell_id'];

                    $ret = course_resell_api::ryncPromoteResellCourse($op,$where,$params);
                    //course_resell_api::updateStatus($v['resell_id'],$status1);
                }
            }
        }
    }
}
