<?php
/**
  * author zhangtaifeng
  **/
class user_task{
    public function pagegetTaskListByOwner($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invaild is empty"; 
            return $ret;
        } 
        $page = empty($inPath[3]) ? 1 : $inPath[4];
        $length = empty($inPath[5]) ? 20 : $inPath[5];
        $task_db = new task_db;
        $db_ret = $task_db->taskListByOwner((int)$inPath[3],$page,$length);
        if(empty($db_ret)){
            $ret->result->code = -3; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $data = array(
            'page' => $db_ret->page,
            'size' => $db_ret->pageSize,
            'total' => $db_ret->totalPage,
            'data' => $db_ret->items,
        );
        $ret->data=$data;
        return $ret;
    }
    public function pagegetTaskListByPlan($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invaild is empty"; 
            return $ret;
        } 
        $page = empty($inPath[3]) ? 1 : $inPath[4];
        $length = empty($inPath[5]) ? 20 : $inPath[5];
        $task_db = new task_db;
        $db_ret = $task_db->taskListByPlan((int)$inPath[3],$page,$length);
        if(empty($db_ret)){
            $ret->result->code = -3; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $data = array(
            'page' => $db_ret->page,
            'size' => $db_ret->pageSize,
            'total' => $db_ret->totalPage,
            'data' => $db_ret->items,
        );
        $ret->data=$data;
        return $ret;
    }
    public function pagecountReply($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1; 
        $ret->result->msg= "";
        $params=SJson::decode(utility_net::getPostData(),true);
        $tids=implode(',',$params);
        $task_db = new task_db;
        $r= $task_db->countReply($tids);
        if (empty($r->items)) {
            $ret->result->code = -2;
            $ret->result->msg= "the data is not found!";
            return $ret;
        }else{
            $ret->data=$r->items; 
            return $ret;
        }   
    }   
    public function pagegetTask($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1; 
        $ret->result->msg= "invalid parameter";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->result->code = -101;
            $ret->result->msg= "invalid parameter";
            return $ret;
        }   
        $task_db = new task_db;
        $r= $task_db->getTask((int)$inPath[3]);
        if (empty($r)) {
            $ret->result->code = -102;
            $ret->result->msg= "the data is not found!";
            return $ret;
        }else{
            return $r; 
        }   
    }   
    //添加作业
    public function pageaddTask($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "the data is empty!";
        $task_db=new task_db;
        $params=SJson::decode(utility_net::getPostData());
        $course_db=new course_db;
        $plan_info=$course_db->getPlan($params->plan);
        //$plan_info=$course_db->getPlanuni($params->course,$params->section,$params->class);
        if(empty($plan_info)){
            $ret->result->code=-2;
            $ret->result->msg="plan id is error!";
        }
        $data=array(
                'title'=>$params->title,
                'fk_user_owner'=>$params->user_id,
                'fk_course'=>$plan_info['course_id'],
                'fk_section'=>$plan_info['section_id'],
                'fk_class'=>$plan_info['class_id'],
                'fk_plan'=>$params->plan,
                'attach'=>$params->attach,
                'desc'=>$params->desc,
            );
        $task_db = new task_db;
        $db_ret=$task_db->addTask($data);
        if($db_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="faild!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }            
    //修改作业
    public function pageupdateTask($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret -> result -> code = -1;
        $ret -> result -> msg = "the data is empty!";
        $task_db=new task_db;
        $params=SJson::decode(utility_net::getPostData(),true);
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret -> result -> code = -1;
            $ret -> result -> msg = "invalid parameter!";
            return $ret;
        }
        $db_ret=$task_db->updateTask($inPath[3],$params);
        if($db_ret===false){
            $ret->result->code=-2;
            $ret->result->msg="the data is no found!";
        }else{
            $ret->result->code=0;
            $ret->result->msg="success!";
        }
        return $ret;
    }            
    //分发作业
    public function pageaddMoreReply($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1; 
        $ret->result->msg= "";
        $params=SJson::decode(utility_net::getPostData(),true);
        if (empty($params)) {
            $ret->result->code = -101;
            $ret->result->msg= "data empty!";
            return $ret;
        }   
        
        $task_db = new task_db;
        $r= $task_db->addMoreTaskReply($params);
        if (empty($r)) {
            $ret->result->code = -103;
            $ret->result->msg= "faild!";
        }else{
            $ret->result->code = 0;
            $ret->result->msg= "Seccess!";
        }   
        return $ret;
    }   
    //删除作业
    public function pagedeleteTask($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1; 
        $ret->result->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->result->code = -101;
            $ret->result->msg= "invalid parameter";
            return $ret;
        }   
        $task_db = new task_db;
        $count=$task_db->countReply((int)$inPath[3]);
        if(!empty($count->items)){
            $ret->result->code = -102;
            $ret->result->msg= "this task is can't delete!";
            return $ret;
        }
        $r= $task_db->deleteTask((int)$inPath[3]);
        if (empty($r)) {
            $ret->result->code = -103;
            $ret->result->msg= "faild!";
        }else{
            $ret->result->code = 0;
            $ret->result->msg= "Seccess!";
        }   
        return $ret;
    }   
    public function pagegetReplyList($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        $params = SJson::decode(utility_net::getPostData());
        $page = empty($inPath[3]) ? 1 : $inPath[3];
        $length = empty($inPath[4]) ? 20 : $inPath[4];
        $task_db = new task_db;
        $db_ret = $task_db->getReplyList($params,$page,$length);
        if(empty($db_ret->items)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $data = array(
            'page' => $db_ret->page,
            'size' => $db_ret->pageSize,
            'total' => $db_ret->totalPage,
            'data' => $db_ret->items,
        );
        $ret->data=$data;
        return $ret;
    }
    public function pagegetReplyListByTid($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "tid is empty"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->getReplyListByTid((int)$inPath[3]);
        if(empty($db_ret->items)){
            $ret->result->code = -3; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
    }
    public function pagegetCourseClassSection($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->getCourseClassSection((int)$inPath[3]);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
         
    }
    public function pagegetReplyClass($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->getReplyClass((int)$inPath[3]);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
         
    }
    public function pagegetReplySection($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->getReplySection((int)$inPath[3]);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
         
    }
    public function pagecountReplyStatus($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->countReplyStatus((int)$inPath[3]);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $ret->data=$db_ret->items;
        return $ret;
         
    }
    public function pagegetReplyInfo($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->getReplyInfo((int)$inPath[3]);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        if(!empty($db_ret['task_attach'])){
            $files=explode('|',$db_ret['task_attach']);
            $db_ret['task_attach']=$files;
        }
        $ret->data=$db_ret;
        return $ret;
         
    }
    public function pagegetAttachList($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter"; 
            return $ret;
        }
        $task_db = new task_db;
        $db_ret = $task_db->getAttachList((int)$inPath[3]);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        foreach($db_ret->items as $k=>$v){
            if($v['attach']){
                $db_ret->items[$k]['attach']=explode('|',$v['attach']);
            }
        }
        
        $ret->data=$db_ret->items;
        return $ret;
         
    }
    public function pageaddAttach($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=-1;
        $ret->result->msg="";
        $params = SJson::decode(utility_net::getPostData());
        if(empty($params->desc)&&empty($params->fid)){
            $ret->result->code = -2; 
            $ret->result->msg="file and desc is empty"; 
            return $ret;
        }
        $user_db = new user_db;
        $teacher= $user_db->getTeacherProfile($params->uid);       
        if(empty($teacher)){
            $fk_user_owner=0;
            $fk_user_reply=$params->uid;
        }else{
            $fk_user_owner=$params->uid;
            $fk_user_reply=0;
        }
        $data = array(
            'fk_task' => $params->tid,
            'fk_task_reply' => $params->rid,
            'fk_user_owner' => $fk_user_owner,
            'fk_user_reply' => $fk_user_reply,
            'attach' => trim($params->fid,'|'),
            'desc' => $params->desc,
        );
        
        $task_db = new task_db;
        $db_ret = $task_db->addTaskAttach($data);
        if(empty($db_ret)){
            $ret->result->code = -2; 
            $ret->result->msg= "data is empty"; 
            return $ret;
        }
        $ret->result->code = 0; 
        return $ret;
         
    }



}
