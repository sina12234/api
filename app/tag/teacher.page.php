<?php

class tag_teacher
{
	public function pageGetBelongTagByGropId($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$param = SJson::decode(utility_net::getPostData());
		if (empty($inPath[3]) || !is_numeric($inPath[3])) 
		{
			$ret->result->code = -1;
			$ret->result->msg= "this group_id error";
			return $ret;
		}
		$data = tag_db::getBelongTagByGropId($inPath[3]);
		$ret->result->code = 0;
		$ret->data = $data->items;
		return $ret;
	}
	
	public function pageGetTagInTagId($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$param = SJson::decode(utility_net::getPostData());
		if (empty($param->tag_ids)) 
		{
			$ret->result->code = -1;
			$ret->result->msg= "this tagid error";
			return $ret;
		}
		
		$data = tag_db_tagDao::getTagInTagIds($param->tag_ids);
		$ret->result->code = 0;
		$ret->data = $data->items;
		return $ret;
	}
	
	public function pageGetTagUserInUids($inPath)
	{
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$param = SJson::decode(utility_net::getPostData());
		if (empty($param->ids) || empty($param->groupId)) 
		{
			$ret->result->code = -1;
			$ret->result->msg= "this ids error";
			return $ret;
		}
		
		$data = tag_db::getTagUserInUids($param->ids,$param->groupId);
		$ret->result->code = 0;
		$ret->data = $data->items;
		return $ret;
	}
}
?>
