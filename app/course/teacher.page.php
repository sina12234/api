<?php
class course_teacher{
    /*
     * 查询机构教师班级数量 
     * @param  $owner,$uid,$status
     * @return int
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageCountTeacherClassByUid($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";
        if (empty($inPath[3])) {
            $ret->result->code = -1;
            $ret->result->msg  = "oid is empty";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData());
        $db = new course_db;
        $res= $db->countTeacherCLassByUid($inPath[3], $params);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->data = $res;
        return $ret;
    }
     /* 查询机构教师章节数量 
     * @param  $owner,uid,$status
     * @return int
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageCountTeacherPlanByUid($inPath){
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";
        if (empty($inPath[3])) {
            $ret->result->code = -1;
            $ret->result->msg  = "oid is empty";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData());
        $db = new course_db;
        $res= $db->countTeacherPlanByUid($inPath[3], $params);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";
            return $ret;
        }
        $ret->data = $res;
        return $ret;
    }
}
