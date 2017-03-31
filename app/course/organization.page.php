<?php
/*
 * 机构相关课程
 * @author Panda <zhangtaifeng@gn100.com>
 */
class course_organization{
    /*
     * 查询机构课程数量
     * @param  $owner,$status,$startTime,$endTime
     * @return int
     * @author Panda <zhangtaifeng@gn100.com>
     */
    public function pageCountCourseByOwner($inPath){
        $ret               = new stdclass;
        $ret->result       = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg  = "";
        if (empty($inPath[3])) {
            $ret->result->code = -1;
            $ret->result->msg  = "owner is empty";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData());
        $db = new course_db;
        $res= $db->countCourseByOwner($inPath[3], $params);
        if (empty($res)) {
            $ret->result->code = -2;
            $ret->result->msg  = "data is empty!";

            return $ret;

        }
        $ret->data = $res;

        return $ret;
    }
}
