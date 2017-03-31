<?php

class tag_tag{
    
    public function pagegetTagList($inPath){
		$page  = isset($inPath[3]) ? $inPath[3] : '';
		$length  = isset($inPath[4]) ? $inPath[4] : '';
        $res = tag_db::getTagList($page,$length);
		$ret = new stdclass;
		if(!empty($res)){
			$tag = array();
			foreach($res->items as $k=>$v){
				if($v['fk_user'] !=0 ){
					$tag[$k]=$v['fk_user'];
				}	
			}
			$strArr= implode(",",array_unique($tag));
			$mq = user_db::listProfilesByUserIds($strArr);
			if(!empty($mq)){
				foreach($mq->items as $key=>$val){
					$real_name[$val['fk_user']]['fk_user']=$val['fk_user'];
					$real_name[$val['fk_user']]['real_name']=$val['real_name'];
				}
			}
			$real_arr= array();
			foreach($res->items as $k=>$v){
				$real_arr[] = [
					'pk_tag'  => $v['pk_tag'],
					'fk_user' => $v['fk_user'],
					'name'    => $v['name'],
					'desc'    => $v['desc'],
					'status'  => $v['status'],
					'lastupdated'=> $v['lastupdated'],
					'real_name' => !empty($real_name[$v['fk_user']]['real_name'])?$real_name[$v['fk_user']]['real_name']:''
				];	
			}
			$res->items = $real_arr;
			$ret->code  = 0;
			$ret->msg   = "success!";
			$ret->result = $res;
			return $ret;
		}else{
			$ret->code  = -1;
			$ret->msg   = "get data faild!";
			return $ret;
		}
    }

	public function pageGetGroupList($inPath){
		$params = SJson::decode(utility_net::getPostData(),true);
		$name  = isset($params['name']) ? $params['name'] : '';
		$page  = isset($params['page']) ? $params['page'] : '';
		$length  = isset($params['length']) ? $params['length'] : '';
        $groupRet = tag_db::getGroupList($page,$length,$name);
        if (empty($groupRet)) {
            return api_func::setMsg(3002);
        }
        return api_func::setData($groupRet);
    }
    
	public static function pageSearchTagShow($inPath){
		$params = SJson::decode(utility_net::getPostData());
		$page   = isset($inPath[3]) ? $inPath[3] : '';
		$length  = isset($inPath[4]) ? $inPath[4] : '';
		$res = tag_db::searchTagShow($page,$length,$params);
		$ret = new stdclass;
		if(!empty($res->items)){
			$tag = array();
			foreach($res->items as $k=>$v){
				if($v['fk_user'] !=0){
					$tag[$k]=$v['fk_user'];
				}	
			}
			$strArr= implode(",",array_unique($tag));
			$mq = user_db::listProfilesByUserIds($strArr);
			if(!empty($mq)){
				foreach($mq->items as $key=>$val){
					$real_name[$val['fk_user']]['fk_user']=$val['fk_user'];
					$real_name[$val['fk_user']]['real_name']=$val['real_name'];
				}
			}
			$real_arr= array();
			foreach($res->items as $k=>$v){
				$real_arr[] = [
					'pk_tag'  => $v['pk_tag'],
					'fk_user' => $v['fk_user'],
					'name'    => $v['name'],
					'desc'    => $v['desc'],
					'status'  => $v['status'],
					'lastupdated'=> $v['lastupdated'],
					'real_name' => !empty($real_name[$v['fk_user']]['real_name'])?$real_name[$v['fk_user']]['real_name']:''
				];	
			}
			$res->items = $real_arr;
			$ret->code  = 0;
			$ret->msg   = "success!";
			$ret->result = $res;
			return $ret;
		}else{
			$ret->code  = -1;
			$ret->msg   = "get data faild!";
			return $ret;
		}
    }
	public static function pageaddTag($inPath){
		$ret=new stdclass;
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			$ret->code = -1;
			$ret->msg  = "名称为空！";
			return $ret;
		}
		$tagInfo = tag_db::getTagInfo($params->name);
		if(!empty($tagInfo['name'])){
			$ret->code = -2;
			$ret->msg  = "标签已经存在,请重新填写";
			return $ret;
		}
		$data['name'] = $params->name;
		$data['desc'] = isset($params->desc)?$params->desc:'';
		$addRet = tag_db::addTag($data);
		if(!empty($addRet)){
			$ret->code = 0;
			$ret->msg  = "添加成功!";
			$ret->result = $addRet;
		}else{
			$ret->code = -3;
			$ret->msg = "添加失败";
		}
		return $ret;
    }
    public static function pagegetsubjectTag($inPath){
		$ret=new stdclass;
        $params=!empty($inPath[3]) ? $inPath[3] : '';
		if($params){
			$suject_arr = tag_db::getsubjectTag($params);
			return $suject_arr;
		}   
    }
	public static function pagegettagByGroupInfo($inPath){
		$ret=new stdclass;
        $params=SJson::decode(utility_net::getPostData());
		if($params){
			$suject_arr = tag_db::getTagGroupInfo($params->pk_group);
			return $suject_arr;
		}   
    }
	/*前端用这个接口 因为pk等信息不能暴露在外*/
	//根据group_id来列取tag 
	// 1科目 2年级
	public static function pagegetTagInfoByGroupId($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "update inpath is empty!";
        $params=SJson::decode(utility_net::getPostData());
		if (empty($params->group_id) || !is_numeric($params->group_id)) {
			$ret->result->msg= "group_id is empty!";
			return $ret;
		}
		$group_id = $params->group_id;
		if($group_id){
			$subject_arr = tag_api::getTagInfoByGroupId($group_id);
			if($subject_arr === false){
				$ret->result->code = -2;
				$ret->result->msg = "the data is not found!";
				return $ret;
			}
			return $subject_arr;
		}
    }
	public static function pagedelGroupId($inPath){
		$ret=new stdclass;
		$groupId = isset($inPath[3])?$inPath[3]:'';
		if(empty($groupId)){
			$ret->code='-1';
			$ret->msg="分组Id为空";
			return $ret;
		}
		$delGroup = tag_db::delGroup($groupId);
		if(!empty($delGroup)){
			tag_db::delBelongTagGroup($groupId);
			$ret->code = 0;
			$ret->msg = "删除成功!";
			$ret->result = $delGroup;
			return $ret;
		}else{
			$ret->code='-1';
			$ret->msg="删除失败";
			return $ret;
		}   
    }	
    public static function pagegetgroupInfo($inPath){
		$ret=new stdclass;
		$params=SJson::decode(utility_net::getPostData());
        
		$uid=$inPath[3];
        $groupInfo=array();
		if(!empty($uid)){
			$groupInfo = tag_db::getTagGroupById($uid);
            $tag =tag_db::getTagGroupInfo($uid);
            $t=array();
            if(!empty($tag->items)){
                foreach($tag->items as $k=>$v){
                    $t[]= !empty($v['tag_name']) ? $v['tag_name'] : '';
                }
            }
            $str= implode("\n",$t);
            $groupInfo['tag_name']=$str;
			if($groupInfo===false){
				$ret->code='-1';
				$ret->msg="查询失败";
				return $ret;
			}
			if($groupInfo){
				$ret->code="100";
				$ret->msg="success";
				$ret->result=$groupInfo;
			}
			return $ret;
		}   
    }
	
	public static function pageaddGroup($inPath){
		$ret=new stdclass;
		$params=SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			$ret->code= -1;
			$ret->msg="名称为空！";
			return $ret;
		}
		$data['name']=$params->name;
		$data['desc']=$params->desc;
		$groupInfo = tag_db::getGroupInfo($params->name);
		if(!empty($groupInfo)){
			$ret->code= -2;
			$ret->msg="该标签分组已经存在";
			return $ret;
		}
		$addRet = tag_db::addGroup($data);
		if($addRet){
			if(!empty($params->tag_name)){
				$tagNameArr = array();
				$tempName = explode("\n",$params->tag_name);
				foreach($tempName as $to){
					if(!empty(trim($to))){
						$tagNameArr[] = trim($to);
					}
				}
				tag_api::addTagBelongGroup($addRet,$tagNameArr);
			}
			$ret->code = 0;
			$ret->msg  = "添加成功!";
			$ret->result = $addRet;
		}else{
			$ret->code = -3;
			$ret->msg = "添加失败";
		}
		return $ret;
    }
	
	public static function pageUpdateGroup($inPath){
		$ret=new stdclass;
		$params=SJson::decode(utility_net::getPostData());		
		$groupId=$inPath[3];
		if(empty($groupId) || empty($params->group_name)){
			$ret->code= -1;
			$ret->msg="参数为空!";
			return $ret;
		}	
		$groupInfo = tag_db::getGroupInfo($params->group_name,$groupId);
		if(!empty($groupInfo)){
			$ret->code= -2;
			$ret->msg="该标签分组已经存在";
			return $ret;
		}
		$data = array();
		$data['name']=$params->group_name;
		$data['desc']=$params->desc;
		$updateRet = tag_db::updateTagGroup($groupId,$data);
		if($updateRet !== false){
			tag_api::updateTagBelongGroup($groupId,$params->tag_name);
			$ret->code = 0;
			$ret->msg  = "修改成功!";
			$ret->result = $updateRet;
		}else{
			$ret->code = -3;
			$ret->msg = "修改失败";
		}
		return $ret;	   
    }
	
	//删除单个标签
	public function pageDelCourseTag(){
		$params = SJson::decode(utility_net::getPostData(), true);
		
		$courseId = !empty($params['courseId']) ? (int)$params['courseId'] : 0;
		$groupId  = !empty($params['groupId']) ? (int)$params['groupId'] : 0;
		$tagId    = !empty($params['tagId']) ? $params['tagId'] : 0;
		if(empty($courseId) || empty($groupId) || empty($tagId)){
			return api_func::setMsg(1000);
		}

		$reg = tag_db::delMappingCourseByCidAndTidArr($courseId, $groupId, $tagId);
		if($reg) return api_func::setMsg(0);
		
		return api_func::setMsg(1);
	}
}

