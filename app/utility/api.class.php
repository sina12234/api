<?php
/**
 *@author longhouan
 */
class utility_api{

    public function getschoolList($page,$length = 8){
        $utility_db = new utility_db();
        $list_school1 = $utility_db->getschool($page,$length);
        $relist = array();
        if(!empty($list_school1->items)){
            $list_school = $list_school1->items;
            $count = count($list_school);
            for($i = 0;$i<$count;$i++){
                $relist[$i]["pk_school"]= $list_school[$i]["pk_school"];
                $relist[$i]["school_type"]= $list_school[$i]["school_type"];
                $relist[$i]["school_name"]= $list_school[$i]["school_name"];
                $relist[$i]["addr"]= $list_school[$i]["addr"];
                $relist[$i]["position"]= $list_school[$i]["position"];
                $relist[$i]["phone"]=	$list_school[$i]["phone"];
            }
        }
        $ret = new stdClass;
        if(empty($relist)){$relist = 0;}
        $ret->data = $relist;
        $ret->page = $list_school1->page;
        $ret->size = $list_school1->limit;
        $ret->total = $list_school1->totalPage;
        $relist = SJson::encode($ret);
        return $relist;
    }

    public function getsearchShow($params){
        $ret = utility_db::getsearchShow($params);
        if (empty($ret->items)) {
            return false;
        }

        $data = array(
            'page' => $ret->page,
            'size' => $ret->pageSize,
            'total' => $ret->totalPage,
            'data' => $ret->items,
        );
        return $data;
    }
    public function getAreaList($params){
        $ret = utility_db::getAreaList($params);
        if (empty($ret->items)) {
            return false;
        }
        $data = array(
            'data' => $ret->items,
        );
        return $data;
    }
}