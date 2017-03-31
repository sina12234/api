<?php
class file_info{
	public function __construct($inPath){
	}
	public function pageSet($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code=-1;
		if(empty($params->fid) || empty($params->size) || empty($params->url) || empty($params->publicUrl)){
			$ret->result->msg='参数错误';
			return $ret;
		}
		$db = new file_db;
		$info=array();
		$info['fid']=$params->fid;
		$info['size']=$params->size;
		$info['fk_user']=$params->uid;
		$type=0;
		if(empty($params->type)){
			$type=0;
		}
		switch($params->type){
		case "image":	$type=1;	break;
		case "flash":	$type=2;	break;
		case "audio":	$type=3;	break;
		case "video":	$type=4;	break;
		case "doc":		$type=5;	break;
		case "pdf":		$type=6;	break;
		default:$type = 0;
		}
		$info['type']=$type;
		$info['name']=$params->name;
		$volume_r=explode(",",$info['fid']);
		if(count($volume_r)>1){
			$info['fk_volume']=$volume_r[0];
		}
		if(isset($params->pk_file)){ $info['pk_file']=$params->pk_file; }
		if(isset($params->status)){ $info['status']=$params->status; }
		$db_ret = $db->addFile($info);
		if($db_ret!==false){
			$ret->result->code=0;
			$ret->result->msg='ok';
		}else{
			$ret->result->code=-2;
			$ret->result->msg='db write error';
		}
		//更新volume
		$Volume=array();
		$Volume['pk_volume']=$info['fk_volume'];
		$Volume['url']=$params->url;
		$Volume['publicUrl']=$params->publicUrl;
		$db->addVolume($Volume);
		return $ret;
	}
	public function pageList($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		if(empty($params->uid) || empty($params->type)){
			$ret->result =  new stdclass;
			$ret->result->code=-1;
			$ret->result->msg='参数错误';
			return $ret;
		}
		$page=!empty($inPath[3])?$inPath[3]:1;
		$size=!empty($inPath[4])?$inPath[4]:20;
		$type=0;
		if(empty($params->type)){
			$type=0;
		}
		switch($params->type){
		case "image":	$type=1;	break;
		case "flash":	$type=2;	break;
		case "audio":	$type=3;	break;
		case "video":	$type=4;	break;
		case "doc":		$type=5;	break;
		case "pdf":		$type=6;	break;
		default:$type = 0;
		}
		$db = new file_db;
		$data = $db->listFile($params->uid,$type,$page,$size);
		$ret->data=array();
		if(!empty($data->items)){
			$ret->size = $data->totalSize;
			$ret->page = $data->totalPage;
			foreach($data->items as $item){
				$ret->data[]=array("fid"=>$item['fid'],"name"=>$item['name'],"type"=>$item['type'],"size"=>$item['size']);
			}
		}
		return $ret;
	}
	
	public function pageGetFileByFidArr($inPath){
		$fidArr = SJson::decode(utility_net::getPostData(),true);
		$ret = new stdclass;
		if(empty($fidArr)){
			$ret->code = -1;
			$ret->msg = 'params is empty';
			return $ret;
		}	
		$fileDb = new file_db;
		$fileInfo = $fileDb->getFileByFidArr($fidArr);
		if(!empty($fileInfo->items)){
			$ret->code = 0;
			$ret->msg = 'success';
			$ret->data = $fileInfo->items;
			return $ret;
		}else{
			$ret->code = -2;
			$ret->msg = 'get failed';
			return $ret;
		}
	}

	public function pageGetFileByFid($inPath)
	{
		if (!isset($inPath[3]))
			return api_func::setMsg(1000);

		$fileDb = new file_db;
		$res = $fileDb->getFileByFid(trim($inPath[3]));

		if (empty($res)) return api_func::setMsg(3002);

		return api_func::setData($res);
	}

	public function pageGetUrlByVolume($inPath)
	{
		if (!isset($inPath[3]) || !(int)($inPath[3]))
			return api_func::setMsg(1000);

		$fileDb = new file_db;
		$res = $fileDb->getUrlByVolume((int)($inPath[3]));

		if (empty($res)) return api_func::setMsg(3002);

		return api_func::setData($res);
	}
	
}
