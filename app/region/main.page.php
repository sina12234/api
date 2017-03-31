<?php
class region_main{
	public function pageListRegion($inPath){
		$ret = new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		$condition=array();
		if(isset($params->level)){
			$condition['level']=$params->level;
        }
		if(isset($params->parent_region_id)){
			$condition['parent_fk_region']=$params->parent_region_id;
		}
		if(empty($condition)){
			$ret->result = array("code"=>-1,"msg"=>"params error");
			return $ret;
		}
		$db = new region_db;
		$data = $db->listRegion($condition);
		$ret->result=new stdclass;
		$ret->result->code = 0;
		$ret->data = $data->items;
		unset($data->items);
		$ret->pager= $data;
		return $ret;
	}
	public function pageListSchool($inPath){
		$ret = new stdclass;
		$params = SJson::decode(utility_net::getPostData());
		$condition=array();
		if(isset($params->school_type) && !empty($params->school_type)){
			$condition['school_type']=$params->school_type;
		}
		if(isset($params->region_id)){
			$condition['fk_region']=$params->region_id;
		}
		if(empty($condition)){
			$ret->result = array("code"=>-1,"msg"=>"params error");
			return $ret;
		}
		$db = new region_db;
		$data = $db->listSchool($condition);
		$ret->result=new stdclass;
		$ret->result->code = 0;
		$ret->data = $data->items;
		unset($data->items);
		$ret->pager= $data;
		return $ret;
	}
	public function pageScoolByRegionIdArr(){
		$params = SJson::decode(utility_net::getPostData());

		$ret = new stdclass;
		if(empty($params->regionIdArr)){
			$ret->result = array("code"=>-1,"msg"=>"params error");
			return $ret;
		}

        $regIdArr = (array)$params->regionIdArr;
		$regionIdStr = implode(',',$regIdArr);
		$condition = " pk_school IN ({$regionIdStr})";
		$db = new region_db;
		$data = $db->ScoolByRegionIds($condition);
        if(empty($data->items)){
            $ret->result = array("code"=>-1,"msg"=>"params error");
			return $ret;
        }
		$ret->data = $data->items;
		unset($data->items);
		return $ret;
	}
}
