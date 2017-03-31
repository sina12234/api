<?php

/*
 * 平台公告管理
 * author jay
 * date 2016-08-09
 */

class utility_notice {

    /**
     * 公告添加
     * @param type $inPath
     * @return \stdclass
     */
    public function pageAddNotice($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData());
        $data = array();
        if (empty($params->notice_title)) {
            $ret->result->msg = "lack title";
            return $ret;
        }
        $data['notice_title'] = $params->notice_title;
        if (empty($params->notice_content)) {
            $ret->result->msg = "lack content";
            return $ret;
        }
        $data['notice_content'] = $params->notice_content;
        
        $data['admin_user'] = !empty($params->admin_user) ? $params->admin_user : 0;
        $data['weight'] = !empty($params->weight) ? $params->weight : 0;
        $data['status'] = !empty($params->notice_status) ? $params->notice_status : 0;
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $info = utility_db::addNotice($data);
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "failed";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = " add success";
        }
        return $ret;
    }

    /**
     * 公告编辑
     * @param type $inPath
     * @return \stdclass
     */
    public function pageUpdateNotice($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $pid = $inPath[3];
        if (empty($pid)) {
            $ret->result->msg = "lack pid";
            return $ret;
        }
        $params = SJson::decode(utility_net::getPostData());
        $data = array();
        if (empty($params->notice_title)) {
            $ret->result->msg = "lack title";
            return $ret;
        }
        $data['notice_title'] = $params->notice_title;
        if (empty($params->notice_content)) {
            $ret->result->msg = "lack content";
            return $ret;
        }
        $data['notice_content'] = $params->notice_content;
        $data['admin_user'] = !empty($params->admin_user) ? $params->admin_user : 0;
        $data['weight'] = !empty($params->weight) ? $params->weight : 0;
        $data['status'] = !empty($params->notice_status) ? $params->notice_status : 0;
        $data['update_time'] = date("Y-m-d H:i:s", time());
        $info = utility_db::updatNotice($pid, $data);
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "failed";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = "update success";
        }
        return $ret;
    }

    /**
     * 客服删除，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageDelNotice($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $pid = $inPath[3];
        if (empty($pid)) {
            $ret->result->msg = "lack pid";
            return $ret;
        }
        $data['status'] = -1;
        $info= utility_db::updatNotice($pid,$data);//同时删除qq表以及绑定关系表
        
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "failed";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = " del success";
        }
        return $ret;
    }

    /**
     * 客服列表，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageGetNoticeList($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $page=$inPath[3]?$inPath[3]:1;//page
        $pageNum=$inPath[4]?$inPath[4]:10;//pagesize
        $params = SJson::decode(utility_net::getPostData());

        $list = utility_db::noticeList($page,$pageNum,$params->status);
        
        if (!empty($list->items)) {
            $ret->data = $list;
        } else {
            $ret->data = "";
        }
        $ret->result->code = 0;
        return $ret;
    }

     /**
     * 公告详情
     * @param type $inPath
     * @return \stdclass
     */
    public function pageGetNoticeInfo($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $pid=$inPath[3];
        if(empty($pid)){
            $ret->result->msg = "lack pid";
            return $ret;
        }
        $info = utility_db::noticeInfo($pid);
        if ($info == false) {
            $ret->data = "";
        } else {
            $ret->data = $info;
        }
        $ret->result->code = 0;
        return $ret;
    }

}
