<?php

/*
 * 大班分小班
 * author jay
 * date 2016-08-17
 */

class course_group {

    /**
     * 分组添加
     * @param type $inPath
     * @return \stdclass
     */
    public function pageAddGroup($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";

        $params = SJson::decode(utility_net::getPostData()); //print_r($params);die;
        if (empty($params->course)) {
            $ret->result->msg = "lack course";
            return $ret;
        }
        if (empty($params->class)) {
            $ret->result->msg = "lack class";
            return $ret;
        }
        if (empty($params->teacher)) {
            $ret->result->msg = "lack teacher";
            return $ret;
        }
        if (empty(trim($params->group_name))) {
            $ret->result->msg = "lack group_name";
            return $ret;
        }
        if(mb_strlen(trim($params->group_name),"UTF-8")>5){
            $ret->result->msg = "group_name too long";
            return $ret;
        }

        $data['fk_course'] = $params->course;
        $data['fk_class'] = $params->class;
        $data['group_teacher_id'] = $params->teacher;
        $data['group_name'] = trim($params->group_name);
        $data['create_time'] = date("Y-m-d H:i:s");

        $couse_db = new course_db();
        //print_r($data);die;
        $rs = $couse_db->addGroup($data);//成功返回组id，失败返回false
        if ($rs) {
            $ret->result->code = 0;
            $ret->result->msg = 'success';
            $ret->data=$rs;
        } else {
            $ret->result->msg = 'insert data fail';
        }
        return $ret;
    }

    //更新分组
    public function pageupGroup($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData()); //print_r($params);die;
        if (empty($params->groupid)) {
            $ret->result->msg = "lack groupid";
            return $ret;
        }

        if (!empty($params->teacher)) {
            $data['group_teacher_id'] = $params->teacher;
        }
        if (!empty(trim($params->group_name))) {
            $data['group_name'] = trim($params->group_name);
        }
        if(mb_strlen(trim($params->group_name),"UTF-8")>5){
            $ret->result->msg = "group_name too long";
            return $ret;
        }

        $couse_db = new course_db();
        //print_r($data);die;
        $rs = $couse_db->upGroup($params->groupid, $data);
        //if ($rs) {
            $ret->result->code = 0;
            $ret->result->msg = 'success';
        //} else {
            //$ret->result->msg = 'up data fail';
        //}
        return $ret;
    }

    //删除分组
    public function pagedelGroup($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $groupid = $inPath[3];
        $classid = $inPath[4];
        if (empty($groupid)) {
            $ret->result->msg = "lack groupid";
            return $ret;
        }
        if (empty($classid)) {
            $ret->result->msg = "lack classid";
            return $ret;
        }
        $couse_db = new course_db();
        //print_r($data);die;
        $rs = $couse_db->delGroup($groupid, $classid);
        if ($rs) {
            $ret->result->code = 0;
            $ret->result->msg = 'success';
        } else {
            $ret->result->msg = 'del fail';
        }
        return $ret;
    }

    //分组列表
    public function pagegroupList($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $classid = $inPath[3];
        if (empty($classid)) {
            $ret->result->msg = "lack classid";
            return $ret;
        }
        $couse_db = new course_db();
        $rs = $couse_db->groupList($classid);//print_r($rs);
        if(!empty($rs->items)){
            for($i=0;$i<count($rs->items);$i++){
                $groupid=$rs->items[$i]['pk_group'];
                $userCount=$couse_db->userList(array('fk_class'=>$classid,'fk_class_group'=>$groupid), 1, 1,0);
                $rs->items[$i]['user_count']=$userCount->totalSize;
            }
        }
        $ret->result->code = 0;
        $ret->data = $rs;
        return $ret;
    }

    //设置分组权限
    public function pagesetGroupPrivilege($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData()); //print_r($params);die;

        if (empty($params->classid)) {
            $ret->result->msg = "lack classid";
            return $ret;
        }

        $privilege = $params->privilege ? $params->privilege : 0; //'0 全部分组可见提示,1 仅见组内聊天'
        $data['group_message'] = $privilege;
        $couse_db = new course_db();
        //print_r($data);die;
        $rs = $couse_db->setGroupPrivilege($params->classid, $data);
        if ($rs) {
            $ret->result->code = 0;
            $ret->result->msg = 'success';
        } else {
            $ret->result->msg = 'del fail';
        }
        return $ret;
    }

    //添加、移除分组学员
    public function pagebatchHandleGroupUser($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData()); //print_r($params);die;

        if (empty($params->userids)) {//逗号分隔
            $ret->result->msg = "lack userids";
            return $ret;
        }
        if (empty($params->classid)) {
            $ret->result->msg = "lack classid";
            return $ret;
        }
        if (empty($params->courseid)) {
            $ret->result->msg = "lack courseid";
            return $ret;
        }
        $data['fk_class_group'] = $params->groupid; //-2未分组，代表移除分组
        $data['fk_user'] = $params->userids;
        $data['fk_class'] = $params->classid;
        $data['fk_course'] = $params->courseid;
        $data['create_time'] = date("Y-m-d H:i:s");
        $couse_db = new course_db();
        $rs = $couse_db->batchHandleGroupUser($data);
        if ($rs) {
            $ret->result->code = 0;
            $ret->result->msg = 'success';
        } else {
            $ret->result->msg = 'fail';
        }
        return $ret;
    }

    //学生列表，按班，组分类,groupid>0分组id，-1，全部学员，-2未分组
    //方法比较乱，涉及东西太多，后续需要优化
    public function pageuserList($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData()); //print_r($params);die;
        if (empty($params->groupid)) {//逗号分隔
            $ret->result->msg = "lack groupid";
            return $ret;
        }
        if (empty($params->classid)) {//逗号分隔
            $ret->result->msg = "lack classid";
            return $ret;
        }
        $cache=$params->cache;//1需要缓存，0不需要缓存
        $type = $params->type; //1代表学生播放页，2代表管理员巡课页
        $loginid = $params->loginid;
        if(!empty($params->search))$search=$params->search;
        $couse_db = new course_db();
        $user_db   = new user_db();
        if ($type == 1) {//验证当前学生是否在组内
            if (!$couse_db->getUser(array('fk_class_group' => $params->groupid, 'fk_user' => $loginid))) {
                $ret->result->msg = "invalid user";
                return $ret;
            }
        } else if ($type == 2) {//验证当前管理员是否是组管理员
            /*if($params->groupid>0){//todo全部学员及未分组学员都能浏览
                if (!$couse_db->getTeacher(array('pk_group' => $params->groupid, 'group_teacher_id' => $loginid))) {
                    $ret->result->msg = "invalid admin";
                    return $ret;
                }
            }*/
        }
        $data['fk_class_group'] = $params->groupid;
        $data['fk_class'] = $params->classid;
        $page = $params->page ? $params->page : 1;
        $pagesize = $params->pagesize ? $params->pagesize : 20;
        $totalNum=$totalPage=0;
        $userinfo = '';
        if ($type == 1) {//学生播放页,按组获取列表
            $rs = $couse_db->userList($data, $page, $pagesize,$cache);
            if (!empty($rs->items)) {
                $userids = array_column($rs->items, 'fk_user');
                $userinfo = $userids;
            }
        } else if ($type == 2) {//巡课页，分为全部学员，未分组，按组列表
            if(!empty($search)){
                if(preg_match('/^1[34578][0-9]{0,9}$/',$search)){
                    $searchRs=$user_db->geteUserIdByLikeMobile($search);
                }else{
                    $searchRs=$user_db->geteUserIdByLikeName($search);
                }
                
                if(!empty($searchRs->items)){
                    if(preg_match('/^1[34578][0-9]{0,9}$/',$search)){
                        $searchIds=  array_column($searchRs->items, "fk_user");
                    }else{
                        $searchIds=  array_column($searchRs->items, "pk_user");
                    }
                    $search_course_infos=$couse_db->getCourseUserByClassAndUids(array('fk_class'=>$params->classid,'fk_user'=>$searchIds));
                    //print_r($search_course_infos);die;
                    if(!empty($search_course_infos->items)){
                        $searchUids=  array_column($search_course_infos->items, "fk_user");
                        $totalNum=$search_course_infos->totalSize;
                        $totalPage=$search_course_infos->totalPage;
                    }else{
                        $ret->result->code = 0;
                        $ret->result->msg = 'success';
                        $ret->data = $userinfo;
                        $ret->page=array("totalNum"=>$totalNum,"totalPage"=>$totalPage,"pageSize"=>$pagesize,"page"=>$page);
                        return $ret;
                    }
                }else{
                    $ret->result->code = 0;
                    $ret->result->msg = 'success';
                    $ret->data = $userinfo;
                    $ret->page=array("totalNum"=>$totalNum,"totalPage"=>$totalPage,"pageSize"=>$pagesize,"page"=>$page);
                    return $ret;
                }
            }else if ($params->groupid == -1) {//全部学员
                $rs = course_db_courseUserDao::listsByClassIdSort($params->classid,$page,$pagesize,'pk_course_user DESC',$cache);//print_r($rs);die;
                $totalNum=$rs->totalSize;
                $totalPage=$rs->totalPage;
            } else if ($params->groupid == -2) {//未分组
                $rs = $couse_db->notInGroup($params->classid,$page,$pagesize,$cache);
                $rsNum=$couse_db->notInGroupTotal($params->classid,$cache);
                $totalNum=$rsNum[0]['totalnum'];
                $totalPage=ceil($totalNum/$pagesize);
            } else if ($params->groupid > 0) {//按组列表
                $rs = $couse_db->userList($data, $page, $pagesize,$cache);
                $totalNum=$rs->totalSize;
                $totalPage=$rs->totalPage;
            }
            if (($params->groupid !=-2 && !empty($rs->items))||($params->groupid ==-2 && !empty($rs))||(!empty($search)&&!empty($searchUids))) {
                if(!empty($search)){
                    $userids=$searchUids;
                }else if($params->groupid == -2){
                    $userids=  array_column($rs,'fk_user');
                }else{
                    $userids = array_column($rs->items, 'fk_user');
                }
                
                $tmp=$user_db->getStudentUsers($userids);//批量获取学生信息
                //print_r($tmp);die;
                $tmp2=array_column($tmp,null,'user_id');
                
                if ($params->groupid == -1) {
                    $check_group=$couse_db->batchCheckUserInGroup($params->classid,$userids);
                    $group_user_list= array_column($check_group->items,'fk_class_group','fk_user');//print_r($group_user_list);die;
                    $group_list=$couse_db->groupList($params->classid);
                    if(!empty($group_list->items)){
                        $group_list=array_column($group_list->items,'group_name','pk_group');
                    }
                }else if($params->groupid == -2) {
                    $groupid = -2;
                    $groupname = '未分组';
                } else if ($params->groupid > 0) {
                    $rs_group = $couse_db->getGroupInfoByGroupid($params->groupid);
                    $groupid = $params->groupid;
                    $groupname = $rs_group['group_name'];
                }
                $t_course_infos=$couse_db->getCourseUserByClassAndUids(array('fk_class'=>$params->classid,'fk_user'=>$userids));
                if(!empty($t_course_infos->items)){
                    $t_course_infos=array_column($t_course_infos->items,'create_time','fk_user');
                }
                foreach ($userids as $uid) {
                    if (!empty($tmp2[$uid])) {//print_r($tmp2[$uid]);
                        //$t_course_info=$couse_db->getCourseUserByClassAndUid(array('fk_class'=>$params->classid,'fk_user'=>$uid));//获取学生报名时间
                        $tmp2[$uid]['register_time']=$t_course_infos[$uid];
                        if($params->groupid ==-1){
                            if(in_array($uid,array_keys($group_user_list))){
                                $tmp2[$uid]['groupid']=$group_user_list[$uid];//groupid
                                //$tmp3=$couse_db->getGroupInfoByGroupid($group_user_list[$uid]);
                                $tmp2[$uid]['groupname']=$group_list[$group_user_list[$uid]];
                            }else{
                                $tmp2[$uid]['groupid'] = -2;
                                $tmp2[$uid]['groupname'] = '未分组';
                            }
                        }else{
                            $tmp2[$uid]['groupid']=$groupid;
                            $tmp2[$uid]['groupname']=$groupname;
                        }
                        $userinfo[] = $tmp2[$uid];
                    }
                }
            }
        }

        $ret->result->code = 0;
        $ret->result->msg = 'success';
        $ret->data = $userinfo;
        $ret->page=array("totalNum"=>$totalNum,"totalPage"=>$totalPage,"pageSize"=>$pagesize,"page"=>$page);

        return $ret;
    }
    
    //管理员列表
    public function pageAdminList($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $orgid=$inPath[3];
        $rett='';
        $rs=user_db::OrgRoleList($orgid);
        if(!empty($rs)){
            $rs=  array_column($rs->items, 'fk_user');
            $j=0;
            for($i=0;$i<count($rs);$i++){
                $userinfo=  user_db::getUser($rs[$i]);
                if(!empty($userinfo)){
                    $rett[$j]['id']=$rs[$i];
                    $rett[$j]['name']=$userinfo['real_name'];
                    $j++;
                }
            }
        }
        $ret->result->code = 0;
        $ret->data = $rett;
        return $ret;
    }
    
    //通过classid与userid获取是否在某组信息
    public function pagecheckIsGroupuser($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData());
        if(empty($params->classid)){
            $ret->result->msg = "lack classid";
            return $ret;
        }
        if(empty($params->userid)){
            $ret->result->msg = "lack userid";
            return $ret;
        }
        $data['fk_class']=$params->classid;
        $data['fk_user']=$params->userid;
        $couse_db = new course_db();
        
        //$rs=$couse_db->checkIsGroupuserByClassAndUid($data);print_r($rs);
        $rs='';
        $rss=$couse_db->batchCheckUserInGroup($params->classid,array($params->userid));//print_r($rss);
        if(!empty($rss->items)){
            $rs['pk_id']=$rss->items[0]['pk_id'];
            $rs['fk_class_group']=$rss->items[0]['fk_class_group'];
            $rs['fk_user']=$rss->items[0]['fk_user'];
            $rs['status']=$rss->items[0]['status'];
            $rs['create_time']=$rss->items[0]['create_time'];
            $rs['last_updated']=$rss->items[0]['last_updated'];
            $rs['fk_class']=$rss->items[0]['fk_class'];
            $rs['fk_course']=$rss->items[0]['fk_course'];
        }
        $ret->result->code = 0;
        $ret->data = $rs?$rs:'';
        return $ret;
        
    }
    
    public function pagegetGroupInfo($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $groupid=$inPath[3];
        if(empty($groupid)){
            $ret->result->msg = "lack groupid";
            return $ret;
        }
        
        $couse_db = new course_db();
        
        $rs=$couse_db->getGroupInfoByGroupid($groupid);
        //print_r($rs);
        $ret->result->code = 0;
        $ret->data = $rs?$rs:'';
        return $ret;
    }
    
    public function pagecheckTeacher($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $classid=$inPath[3];
        $teacherid=$inPath[4];
        if(empty($classid)){
            $ret->result->msg = "lack classid";
            return $ret;
        }
        if(empty($teacherid)){
            $ret->result->msg = "lack teacherid";
            return $ret;
        }
        $couse_db = new course_db();
        $data['fk_class']=$classid;
        $rs=$couse_db->getclassTeachers($data);
        //$group_teacher_list=array_column($rs->items,'group_teacher_id');print_r($group_teacher_list);
        $teacherlist=array();
        if(!empty($rs->items)){
            $teacherlist=  array_column($rs->items, 'group_teacher_id');
        }
        if(in_array($teacherid,$teacherlist)){
            $ret->data = 1;
        }else{
            $ret->data = 0;
        }
        $ret->result->code = 0;
        return $ret;
    }

}
