<?php
/**
 * 教师发布作业
 * @author zhouyu
 * date: 2016/7/5
 */
class task_publishTask{

    public function pageTest($inPath){
        //var_dump($inPath);die;
        //$course = $_GET;
        //测试Select
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $tsel = task_db_taskDao::testSelect();
        $ret->data=$tsel->items;
        return $ret;
        //测试 Insert
        //        $data = array();
        //        $data = array(
        //            'fk_course'=>'333',
        //            'fk_class'=>'333',
        //            'fk_user_teacher'=>'333',
        //            'status'=>'2',
        //            'desc'=>'0000'
        //        );
        //        $inster = task_db_taskDao::testInsert($data);
    }

    //教师发布作业
    public function pageteacherPublishTask($inPath){

        //数组加true 对象不加
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        if(empty($params['fk_course']) || empty($params['fk_class']) ||empty($params['fk_user_teacher'] )  ){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        //添加处理 发布作业
        $inster = task_db_taskDao::teacherPublishTask($params);
        if($inster){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->data = $inster;
            return $ret;
        }
    }


    //查询分页
    public function pagegetOrgSlidelist($inPath){
        $param = $_GET;
        $ret = new stdclass;
        //page 页数
        if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
        //length 每页显示数
        if(empty($inPath[5])||!is_numeric($inPath[5])){$length = 4;}else{$length = $inPath[5];}

        $seldate = task_db_taskDao::selss($page,$length);
        if($seldate === false){
            $ret->result =  new stdclass;
            $ret->result->code = -2;
            $ret->result->msg = "the data is not found!";
            return $ret;
        }
        $ret->data=$seldate->items;
        return $ret;
    }

    //教师发布作业  图片上传
    public function pageteacherUploadImg(){
        //数组加true 对象不加
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        if( empty($params['object_id']) || empty($params['object_type']) || empty($params['thumb_big']) ){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $inster = task_db_taskThumbDao::imgUpload($params);
        if($inster){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            return $ret;
        }
    }

    //附件上传
    public function pageteacherUploadAttach(){
        //数组加true 对象不加
        $params = SJson::decode(utility_net::getPostData(), true);

        $ret = new stdclass;
        $ret->result =  new stdclass;
        //        if(empty($params['fk_task']) || empty($params['thumb_big']) ){
        //            $ret->result->code = -2;
        //            $ret->result->msg = "参数错误";
        //            return $ret;
        //        }

        $inster = task_db_taskAttachDao::attachUpload($params);
        if($inster){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            return $ret;
        }
    }


    //教师发布作业标签查询
    public function pageselTag($inPath){
        $params = SJson::decode(utility_net::getPostData(), true);
        //        if(empty($params['fk_task']) || empty($params['thumb_big']) ){
        //            $ret->result->code = -2;
        //            $ret->result->msg = "参数错误";
        //            return $ret;
        //        }
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $data =  task_db_taskTagDao::tagSelect($params);
        if($data){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->data = $data->items;
            return $ret;
        }

    }

    //教师发布作业添加标签 t_tag 库
    public function pageInsertTag($inPath){
        $params = SJson::decode(utility_net::getPostData(), true);
        //        if(empty($params['fk_task']) || empty($params['thumb_big']) ){
        //            $ret->result->code = -2;
        //            $ret->result->msg = "参数错误";
        //            return $ret;
        //        }

        $ret = new stdclass;
        $ret->result =  new stdclass;
        $data =  task_db_taskTagDao::insertTag($params);
        if($data){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->data = $data;
            return $ret;
        }
    }
    //教师发布作业标签 添加 t_tag库 TagBelong 关联表
    public function pageAddTagBelong(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $result =  task_db_taskTagBelongGroupDao::addTagBelong($params);
        if($result){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            $ret->result->data = $result;
            return $ret;
        }
    }

    //教师发布作业 t_course库
    public function pageAddMappingTag(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $data =  task_db_taskMappingTagDao::AddMappingTag($params);
        if($data){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            return $ret;
        }

    }

    //教师发布作业  添加关联表 t_ourse MappingStudentTag
    public  function pageAddMappingStudentTag(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $data =  task_db_taskMappingTagStudentDao::addMappingStudentTag($params);
        if($data){
            $ret->result->code = 200;
            $ret->result->msg = "success";
            return $ret;
        }

    }

    //通过教师ID获取所属课程
    public function pagegetTeacherCourse(){

        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        if(empty($params['fk_user_teacher'])){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $items = array('fk_course','fk_user_teacher');
        $data =  task_db_taskCourseTeacherDao::getCourse($params,$items);
        if($data){
            $ret->result = $data->items;
            return $ret;
        }
    }

    //根据课程ID获取课程name
    public function pagegetCourseOneName(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        if(empty($params['pk_course'])){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $items = array('pk_course','title','create_time');
        $data =  task_db_courseDao::getClassName($params,$items);

        if($data){
            $ret->result = $data->items;
            return $ret;
        }
    }

    //通过课程ID获取所代班级
    public function pagegetCourseClass(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        if(empty($params['fk_course'])){
            $ret->result->code = -2;
            $ret->result->msg = "参数错误";
            return $ret;
        }
        $fk_course = $params['fk_course'];
        $contion = "fk_course = $fk_course and status > 0";
        $items = array('pk_class','fk_course','name','fk_user_class');
        $data =  task_db_courseClassDao::getClass($contion,$items);
        if($data){
            $ret->result = $data->items;
            return $ret;
        }
    }

    //教师作业列表
    public function pagegetTaskList(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db::taskList($params);
        $ret->result->data = $dataList;
        return $ret;
    }

    //带批改状态 查看作业详情
    public  function pagegetTaskDetail(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskDao::getTaskDetail($params);
        $ret->result->data = $dataList->items;
        return $ret;
    }
    //查看图片；
    public function pagegetTaskDetailThumb(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskThumbDao::getTaskDetailThumb($params);
        $ret->result->data = $dataList->items;
        return $ret;
    }
    //查询附件
    public function pagegetTaskDetailAttach(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskAttachDao::getTaskDetailAttach($params);
        $ret->result->data = $dataList->items;
        return $ret;
    }


    //未发布 修改作业
    public function pageupdatePublishTask(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskDao::updatePublishTask($params);
        if($dataList){
            $ret->result->data = $dataList;
            return $ret;
        }

    }
    //未发布 修改作业  修改图
    public function pageupdatePublishTaskImg(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskThumbDao::updatePublishTaskImg($params);
        if($dataList){
            $ret->result->data = $dataList;
            return $ret;
        }
    }
    //未发布 修改作业 修改附件
    public function pageupdatePublishTaskAttach(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskAttachDao::updatePublishTaskAttach($params);
        if($dataList){
            $ret->result->data = $dataList;
            return $ret;
        }
    }
    //通过taskid 查询 tagid
    public function pagegetTaskDetailTag(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskMappingTagDao::getTaskDetailTag($params);
        if($dataList){
            $ret->result->data = $dataList->items;
            return $ret;
        }
    }

    //查询已经批改作业
    public function pagegetStudentAllTaskAlealy(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;

        $dataList = task_db_taskStudentReplyDao::getStudentAllTaskAlealy($params);
        $ret->result->data = $dataList->items;
        return $ret;

    }

    //通过 t_task_student id 查询已批改标签 t_task_student_reply
    public function pagereplyTaskTag(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskMappingTagStudentDao::replyTaskTag($params);
        if($dataList){
            $ret->result->data = $dataList->items;
            return $ret;
        }
    }


    //删除task
    public function pagegetdelTask(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskDao::getdelTask($params);

        $ret->result->data = $dataList;
        return $ret;

    }

    //删除提交作业表
    public function pagegetdelCommitTask(){
        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_taskStudentDao::getdelCommitTask($params);
        if($dataList){
            $ret->result->data = $dataList;
            return $ret;
        }
    }

    //获取班级信息
    public function pagegetClassInfo(){

        $params = SJson::decode(utility_net::getPostData(), true);
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db_courseClassDao::getClassInfos($params);
        if($dataList){
            $ret->result->data = $dataList;
            return $ret;
        }
    }


    //教师列表是否提示
    public  function pagegetTaskListIsPrompt(){
        $params = SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $dataList = task_db::getTaskListIsPrompt($params);
        if($dataList){
            $ret->result->data = $dataList;
            return $ret;
        }
    }

    //删除图片
    public function pagedelImage(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $ret = new stdclass;
        $dataList = task_db_taskThumbDao::delImage($params);
        if($dataList){
            $ret = 1;
        }else{
            $ret = 2;
        }
        return $ret;
    }
    //删除附件
    public function pagedelAttach(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $ret = new stdclass;
        $dataList = task_db_taskAttachDao::delAttach($params);
        if($dataList){
            $ret = 1;
        }else{
            $ret = 2;
        }
        return $ret;
    }

    //批量删除图片
    public function pageBatchDelImage(){
        $params = SJson::decode(utility_net::getPostData(),true);
        if(!empty($params["imageIdStr"])){
            $params = "pk_thumb IN (".$params["imageIdStr"].")";
        }
        $ret = new stdclass;
        $dataList = task_db_taskThumbDao::delImage($params);
        if($dataList){
            $ret = 1;
        }else{
            $ret = 2;
        }
        return $ret;
    }
    //批量删除附件
    public function pageBatchDelAttach(){
        $params = SJson::decode(utility_net::getPostData(),true);
        if(!empty($params["attachIdStr"])){
            $params = "pk_attach IN (".$params["attachIdStr"].")";
        }
        $ret = new stdclass;
        $dataList = task_db_taskAttachDao::delAttach($params);
        if($dataList){
            $ret = 1;
        }else{
            $ret = 2;
        }
        return $ret;
    }

    //删除标签
    public function pageDelTag(){
        $params = SJson::decode(utility_net::getPostData(),true);
        if(empty($params['pk_tag'])){
            return -1;
        }
        $dataList = task_db_taskTagDao::DelTag($params['pk_tag']);
        if($dataList){
            return 1;
        }else{
            return 2;
        }

    }

    //查询提交作业
    public function pagetaskStudent(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $dataList = task_db_taskStudentDao::taskStudent($params);
        if($dataList){
            return $dataList;
        }

    }


    public function pageselStudentInfo(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $dataList = task_db_taskStudentDao::selStudentInfo($params);
        return $dataList;
    }




}