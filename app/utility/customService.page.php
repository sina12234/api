<?php
/*
 * 机构qq及qq群管理
 * author jay
 * date 2016-08-08
 */
error_reporting(E_ALL & ~E_NOTICE);//上线时注释掉
class utility_customService {

    /**
     * 客服添加，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageAddCs($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $params = SJson::decode(utility_net::getPostData());
        $data = array();
        if (empty($params->type)) {
            $ret->result->msg = "lack type";
            return $ret;
        }
        $data['type'] = $params->type;
        $type_name=trim($params->type_name);
        if (empty($type_name)) {
            $ret->result->msg = "lack type_name";
            return $ret;
        }
        $data['type_name'] = $type_name;
        $type_value=trim($params->type_value);
        if (empty($type_value)) {
            $ret->result->msg = "lack type_value";
            return $ret;
        }
        $data['type_value'] = $type_value;
        if (empty($params->fk_org)) {
            $ret->result->msg = "lack orgid";
            return $ret;
        }
        $data['fk_org'] = $params->fk_org;
        if ($params->type == 2) {//2代表qq群
            $ext=trim($params->ext);
            if (empty($ext)) {
                $ret->result->msg = "lack ext";
                return $ret;
            } else {
                $data['ext'] = $ext;
            }
        } else {
            $data['ext'] = '';
        }
        
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $info = utility_db::addOrgCustomerInfo($data);
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "fail";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = "suc";
        }
        return $ret;
    }

    /**
     * 客服编辑，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageUpdateCs($inPath) {
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
        if (empty($params->type)) {
            $ret->result->msg = "lack type";
            return $ret;
        }
        $type_name=trim($params->type_name);
        if (empty($type_name)) {
            $ret->result->msg = "lack type_name";
            return $ret;
        }
        $data['type_name'] = $type_name;
        $type_value=trim($params->type_value);
        if (empty($type_value)) {
            $ret->result->msg = "lack type_value";
            return $ret;
        }
        $data['type_value'] = $type_value;
        if (empty($params->fk_org)) {
            $ret->result->msg = "lack orgid";
            return $ret;
        }
        //$data['fk_org'] = $params->fk_org;
        if ($params->type == 2) {//2代表qq群
            $ext=trim($params->ext);
            if (empty($ext)) {
                $ret->result->msg = "lack ext";
                return $ret;
            } else {
                $data['ext'] = $ext;
            }
        } else {
            $data['ext'] = '';
        }
        $data['last_updated'] = date("Y-m-d H:i:s", time());
        $info = utility_db::updateOrgCustomerInfo($pid,$params->fk_org, $data);
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "fail";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = "suc";
        }
        return $ret;
    }

    /**
     * 客服删除，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageDelCs($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $pid = $inPath[3];//客服id
        $orgid=$inPath[4];
        if (empty($pid)) {
            $ret->result->msg = "lack pid";
            return $ret;
        }
        if(empty($orgid)){
            $ret->result->msg = "lack orgid";
            return $ret;
        }
        $info= utility_db::delOrgCustomerInfo($pid,$orgid);//同时删除qq表以及绑定关系表
        
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "fail";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = "suc";
        }
        return $ret;
    }

    /**
     * 客服列表，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageGetCsList($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $data = array();
        $params = SJson::decode(utility_net::getPostData());

        if (empty($params->orgid)) {
            $ret->result->msg = "lack orgid";
            return $ret;
        }
        $data['fk_org'] = $params->orgid;
        if (!empty($params->type)) {
            $data['type'] = $params->type;
        }
        if (!empty(trim($params->type_name))) {
            $data['type_name'] = trim($params->type_name);
        }
        $data['page']=$params->page;
        $data['pageSize']=$params->pageSize;
        $cache=$params->cache;

        $list = utility_db::customerServicesList($data,$cache); //按客服名搜索后续需要优化
        //print_r($list);
        $arr = array();
        if (!empty($list->items)) {
            foreach ($list->items as $k => $v) {
                if (!empty($v['type']) && $v['type'] == 1) {
                    $arr['qq'][] = $v;
                } elseif (!empty($v['type']) && $v['type'] == 2) {
                    $arr['qqun'][] = $v;
                }
            }
            $ret->data = $arr;
        } else {
            $ret->data = "";
        }
        $ret->result->code = 0;
        $ret->page=array("totalNum"=>$list->totalSize,"totalPage"=>$list->totalPage,"pageSize"=>$data['pageSize'],"page"=>$data['page']);
        return $ret;
    }

    /**
     * 客服详情，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageGetCsDetail($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";

        $pid = $inPath[3];//客服id
        $orgid=$inPath[4];
        
        if (empty($pid)) {
            $ret->result->msg = "lack pid";
            return $ret;
        }
        if(empty($orgid)){
            $ret->result->msg = "lack orgid";
            return $ret;
        }
        $ret->result->code = 0;
        $data['pk_customer'] = $pid;
        $data['fk_org']=$orgid;

        $info = utility_db::getOrgCustomerInfo($data);
        if ($info === false) {
            $ret->data = "";
        } else {
            $ret->data = $info;
        }
        return $ret;
    }

    /**
     * 机构首页、课程添加客服，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageAddCsRelation($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $data = array();
        $params = SJson::decode(utility_net::getPostData());//print_r($params);
        if(!in_array($params->object_type,array(1,2))){//1代表机构首页客服，2代表课程详情页客服
            $ret->result->msg="illegal object_type";
            return $ret;
        }
        if(!in_array($params->type,array(1,2))){//1代表qq客服，2代表qq群客服
            $ret->result->msg="illegal type";
            return $ret;
        }
        
        if (empty($params->customer)) {//逗号分隔的string
            $ret->result->msg = "lack customers";
            return $ret;
        }
        $check['type']=$params->type;
        if($params->object_type==1){
            $check['fk_org']=$params->fk_org;
        }else if($params->object_type==2){
            $check['fk_course']=$params->fk_course;
        }
        $customerIds=  explode(",", $params->customer);
        $alreadyIds=array();
        $alreadyCustomers=utility_db::csRelationList($check);//print_r($alreadyCustomers);
        
        if(!empty($alreadyCustomers->items)){
            $alreadyIds=  array_column($alreadyCustomers->items, "fk_customer");
        }
        
        $diffIds=array_diff($customerIds,$alreadyIds);
        if(count($alreadyIds)+count($diffIds)>4){
            $ret->result->code = -2;
            $ret->result->msg = "customer must less four";
            return $ret;
        }
        if(!empty($diffIds)){
            $data['fk_customer']=array_values($diffIds);
            $data['fk_org'] = !empty($params->fk_org) ? $params->fk_org : 0;
            $data['fk_course'] = !empty($params->fk_course) ? $params->fk_course : 0;
            $data['type']=$params->type;
            $data['create_time'] = date("Y-m-d H:i:s", time());
            $info = utility_db::addCsRelation($data);
            if ($info === false) {
                $ret->result->code = -1;
                $ret->result->msg = "fail";
            } else {
                $ret->result->code = 0;
                $ret->result->msg = "suc";
            }
        }else{
            $ret->result->code = 0;
            $ret->result->msg = "suc";
        }
        return $ret;
    }

    /**
     * 机构首页、课程删除客服，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageDelCsRelation($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $pid = $inPath[3];
        if (empty($pid)) {
            $ret->result->msg = "lack pid";
            return $ret;
        }

        $info = utility_db::delCsRelation($pid);
        if ($info === false) {
            $ret->result->code = -1;
            $ret->result->msg = "fail";
        } else {
            $ret->result->code = 0;
            $ret->result->msg = "suc";
        }
        return $ret;
    }

    /**
     * 机构首页、课程客服列表，目前有1qq，2qq群
     * @param type $inPath
     * @return \stdclass
     */
    public function pageGetCsRelationList($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        $data = array();
        $params = SJson::decode(utility_net::getPostData());
        
        $data['fk_org']=!empty($params->orgid)?$params->orgid:0;
        $data['fk_course']=!empty($params->courseid)?$params->courseid:0;     
        
        $rs = utility_db::csRelationList($data); //print_r($rs);
        $arr = array();
        if (!empty($rs->items)) {
            $pids_tmp=  array_column($rs->items, "fk_customer","pk_relation");//print_r($pids_tmp);
            $relationArr=  array_flip($pids_tmp);
            $pids=array('pids'=>array_values($pids_tmp));
            $list=  utility_db::customerServicesList($pids);
            //print_r($list);
            foreach ($list->items as $k => $v) {
                $pk_customer=$v['pk_customer'];
                $v['pk_relation']=$relationArr[$pk_customer];
                if (!empty($v['type']) && $v['type'] == 1) {
                    $arr['qq'][] = $v;
                } elseif (!empty($v['type']) && $v['type'] == 2) {
                    $arr['qqun'][] = $v;
                }
            }
            $ret->data = $arr;
        } else {
            $ret->data = "";
        }
        $ret->result->code = 0;
        return $ret;
    }

}
