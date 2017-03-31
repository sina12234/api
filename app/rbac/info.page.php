<?php
/**
 * 
 * @author Zhang Taifeng
 */
class rbac_info{
    public function pagememberList($inPath){
        $ret=new stdclass;
        if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
        if(empty($inPath[4])||!is_numeric($inPath[4])){$limit = 10;}else{$limit = $inPath[4];}
        $rbac_db=new rbac_db; 
        $node_result=$rbac_db->memberList($page,$limit);
        if($node_result){
            $ret->page = $node_result->page;
            $ret->size = $node_result->pageSize;
            $ret->total = $node_result->totalPage;
            $ret->data=$node_result->items;
        }
        $allRole=$rbac_db->allRole();
        $ret->allRole=$allRole->items;
        return $ret;
    }    
    public function pagegetRoleNodeList($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $rbac_db=new rbac_db; 
        $node_result=$rbac_db->getRoleNodeList($params->name);
        $node_list=array();
        if($node_result){
            foreach($node_result->items as $k=>$v){
                if($v['node_level']==0 && $v['node_pid']==0){
                    $node_list[$v['pk_node_id']]=array(
                            'title'=>$v['node_title'],
                            'url'=>'#',
                            'icon_class'=>$v['node_icon'],
                        );
                }
            }
            foreach($node_result->items as $k=>$v){
                if($v['node_level']==1 && $v['node_pid']>0){
                    $node_list[$v['node_pid']]['submenu'][]=array(
                           'title'=>$v['node_title'], 
                           'url'=>$v['node_url'], 
                        );
                }
                    
            }
        }else{
            $ret->result->msg='data is empty'; 
        }
        $ret->result->data=$node_list; 
        return $ret;
    }
    public function pagenodeList($inPath){
        $ret=new stdclass;
        if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
        if(empty($inPath[4])||!is_numeric($inPath[4])){$limit = 10;}else{$limit = $inPath[4];}
        $rbac_db=new rbac_db; 
        $node_result=$rbac_db->nodeList($page,$limit);
        if($node_result){
            $ret->data=$node_result->items;
            $ret->page = $node_result->page;
            $ret->size = $node_result->pageSize;
            $ret->total = $node_result->totalPage;
        }
        return $ret;
    }
	public function pageGetNodeList($inPath){
        $rbac_db=new rbac_db; 
        $ret = $rbac_db->getNodeList();
		if(!empty($ret->items)){
            return api_func::setData($ret->items);
		}else{
            return api_func::setMsg(3002);
		}
    } 
	
    public function pageroleList($inPath){
        $ret=new stdclass;
        if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
        if(empty($inPath[4])||!is_numeric($inPath[4])){$limit = 10;}else{$limit = $inPath[4];}
        $rbac_db=new rbac_db; 
        $role_result=$rbac_db->roleList($page,$limit);
        if($role_result){
            $ret->page = $role_result->page;
            $ret->size = $role_result->pageSize;
            $ret->total = $role_result->totalPage;
            $ret->data=$role_result->items;
        }
        return $ret;
    }    
    public function pagegetRole($inPath){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->code='101';
            $ret->msg='invalid parameter';
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $role_result=$rbac_db->getRole((int)$inPath[3]);
        if (empty($role_result)) {
            $ret->code='102';
            $ret->msg='data is empty';
        }
        $ret->data=$role_result;    
        return $ret;
    }
    public function pagegetMemberByRid($inPath){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->code='101';
            $ret->msg='invalid parameter';
            return $ret;
        }
        if(empty($inPath[4])||!is_numeric($inPath[4])){$page = 1;}else{$page = $inPath[4];}
        if(empty($inPath[5])||!is_numeric($inPath[5])){$limit = 10;}else{$limit = $inPath[5];}
        $rbac_db=new rbac_db; 
        $member_result=$rbac_db->getMemberByRid((int)$inPath[3],$page,$limit);
        if (empty($member_result->items)) {
            $ret->code='102';
            $ret->msg='data is empty';
        }
        $ret->page=$member_result->page;    
        $ret->size=$member_result->pageSize;    
        $ret->total=$member_result->totalPage;    
        $ret->data=$member_result->items;    
        return $ret;
    }
    public function pagegetAccessByRid($inPath){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->code='101';
            $ret->msg='invalid parameter';
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $access_result=$rbac_db->getAccessByRid((int)$inPath[3]);
        if (empty($access_result->items)) {
            $ret->code='102';
            $ret->msg='data is empty';
        }
        $nodeids=array();
        foreach($access_result->items as $v){
            $nodeids[$v['node_id']]=$v['node_id'];
        }
        $node_result=$rbac_db->getNodeList();
        $html='<ul class="ul_item1">';
        foreach($node_result->items as $k=>$v){
            if($v['node_pid']==0&&$v['node_level']==0){
                $checked=in_array($v['pk_node_id'],$nodeids)?'checked':'';
                $html.='<li class="li_item1"><input type="checkbox" name="nodes[]" value="'.$v['pk_node_id'].'" '.$checked.' onclick="javascript:checknode(this);" level="0">'.$v['node_title'].'</li>';
                $html.='<li class="li_item1"><ul class="ul_item2">';
                foreach($node_result->items as $k2=>$v2){
                    if($v2['node_pid']==$v['pk_node_id']&&$v2['node_level']==1){
                        $checked2=in_array($v2['pk_node_id'],$nodeids)?'checked':'';
                        $html.='<li class="li_item2"><input type="checkbox" name="nodes[]" value="'.$v2['pk_node_id'].'" '.$checked2.' onclick="javascript:checknode(this);" level="1">'.$v2['node_title'].'</li>';
                        $html.='<li class="li_item2"><ul class="ul_item3">';
                        foreach($node_result->items as $k3=>$v3){
                            if($v3['node_pid']==$v2['pk_node_id']&&$v3['node_level']==2){
                                $checked3=in_array($v3['pk_node_id'],$nodeids)?'checked':'';
                                $html.='<li class="li_item3"><input type="checkbox" name="nodes[]" value="'.$v3['pk_node_id'].'" '.$checked3.' onclick="javascript:checknode(this);" level="2">'.$v3['node_title'].'</li>';
								$html.='<li class="li_item3"><ul class="ul_item4">';
								foreach($node_result->items as $k4=>$v4){
									if($v4['node_pid']==$v3['pk_node_id']&&$v4['node_level']==3){
										$checked4=in_array($v4['pk_node_id'],$nodeids)?'checked':'';
										$html.='<li class="li_item4"><input type="checkbox" name="nodes[]" value="'.$v4['pk_node_id'].'" '.$checked4.' onclick="javascript:checknode(this);" level="3">'.$v4['node_title'].'</li>';
									}
								}
								$html.='</ul></li>';
							}
                        }
                        $html.='</ul></li>';
                    }
                }
                $html.='</ul></li>';
            }
        }
        $html.='</ul>';
        $ret->data=$nodeids;    
        $ret->tree=$html;    
        return $ret;
    }
    public function pageauthorize($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($params->rid)){
            $ret->result->code = -101; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $rbac_db->authorizeDel((int)$params->rid); 
        $data=array();
        if(!empty($params->node_arr)){
            foreach($params->node_arr as $v){
               $data[]='(\''.$params->rid.'\',\''.$v.'\')';
            }
            $rbac_db->authorize($data);
            $ret->result->msg= "Success!";
        }
        return $ret;
    }
    public function pageaddRole($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($params->role_name)){
            $ret->result->code = -2; 
            $ret->result->msg= "role_name is empty";
            return $ret;
        }
        $data=array();
        $data['role_name']=$params->role_name;
        if(!empty($params->role_remark)){
            $data['role_remark']=$params->role_remark;
        }
        if(!empty($params->role_sort)){
            $data['role_sort']=$params->role_sort;
        }
        $data['create_time']=date('Y-m-d H:i:s');
        $data['update_time']=date('Y-m-d H:i:s');
        $rbac_db=new rbac_db;
        $role_id=$rbac_db->addRole($data);
        if(!$role_id>0){
            $ret->result->code = -3; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pageupdateRole($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        if(empty($params->role_name)){
            $ret->result->code = -3; 
            $ret->result->msg= "role_name is empty";
            return $ret;
        }
        $data=array();
        $data['role_name']=$params->role_name;
        if(!empty($params->role_remark)){
            $data['role_remark']=$params->role_remark;
        }
        if(!empty($params->role_sort)){
            $data['role_sort']=$params->role_sort;
        }
        $data['update_time']=date('Y-m-d H:i:s');
        $rbac_db=new rbac_db;
        $db_ret=$rbac_db->updateRole((int)$inPath[3],$data);
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagedelRole($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -101; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $db_ret=$rbac_db->delRole((int)$inPath[3]); 
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }

    public function pagesearchRole($inPath){
        $params = SJson::decode(utility_net::getPostData());

        $ret=new stdclass;
        if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
        if(empty($inPath[4])||!is_numeric($inPath[4])){$limit = 10;}else{$limit = $inPath[4];}
        $rbac_db=new rbac_db; 
        if(empty($params->role_name)){
            $role_result=$rbac_db->roleList($page,$limit);
        }else{
            $role_result=$rbac_db->getRoleByName($params->role_name,$page,$limit);
        }
        if($role_result){
            $ret->page = $role_result->page;
            $ret->size = $role_result->pageSize;
            $ret->total = $role_result->totalPage;
            $ret->data=$role_result->items;
        }

       return $ret;

    }
    public function pageaddMember($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($params->name)){
            $ret->result->code = -2; 
            $ret->result->msg= "name is empty";
            return $ret;
        }
        if(empty($params->password)){
            $ret->result->code = -2; 
            $ret->result->msg= "password is empty";
            return $ret;
        }
        if(empty($params->fk_role_id)){
            $ret->result->code = -2; 
            $ret->result->msg= "role_id is empty";
            return $ret;
        }
        $data=array();
        $data['name']=$params->name;
        $data['password']= user_api::encryptPassword($params->password);
        $data['fk_role_id']=$params->fk_role_id;
        $data['status']=$params->status;
        $data['create_time']=date('Y-m-d H:i:s');
        $data['last_updated']=date('Y-m-d H:i:s');
        $rbac_db=new rbac_db;
        $role_id=$rbac_db->addMember($data);
        if(!$role_id>0){
            $ret->result->code = -3; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagegetMember($inPath){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->code='101';
            $ret->msg='invalid parameter';
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $member_result=$rbac_db->getMember((int)$inPath[3]);
        if (empty($member_result)) {
            $ret->code='102';
            $ret->msg='data is empty';
        }
        $ret->data=$member_result;    
        return $ret;
    }
    public function pageupdateMember($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        if(empty($params->name)){
            $ret->result->code = -3; 
            $ret->result->msg= "name is empty";
            return $ret;
        }
        if(empty($params->fk_role_id)){
            $ret->result->code = -2; 
            $ret->result->msg= "role_id is empty";
            return $ret;
        }
        $data=array();
        $data['name']=$params->name;
        if(!empty($params->password)){
            $data['password']=user_api::encryptPassword($params->password);
        }
        $data['fk_role_id']=$params->fk_role_id;
        $data['status']=$params->status;
        $data['last_updated']=date('Y-m-d H:i:s');
        $rbac_db=new rbac_db;
        $db_ret=$rbac_db->updateMember((int)$inPath[3],$data);
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagedelMember($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -101; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $db_ret=$rbac_db->delMember((int)$inPath[3]); 
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagesearchMember($inPath){
        $params = SJson::decode(utility_net::getPostData());
        $ret=new stdclass;
        if(empty($inPath[3])||!is_numeric($inPath[3])){$page = 1;}else{$page = $inPath[3];}
        if(empty($inPath[4])||!is_numeric($inPath[4])){$limit = 10;}else{$limit = $inPath[4];}
        $rbac_db=new rbac_db; 
        $role_result=$rbac_db->searchMember($params,$page,$limit);
        if($role_result){
            $ret->page = $role_result->page;
            $ret->size = $role_result->pageSize;
            $ret->total = $role_result->totalPage;
            $ret->data=$role_result->items;
        }
		$allRole=$rbac_db->allRole();
        $ret->allRole=$allRole->items;
       return $ret;

    }
    public function pageaddNode($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($params->node_title)){
            $ret->result->code = -2; 
            $ret->result->msg= "node_title is empty";
            return $ret;
        }
        $data=array();
        $data['node_pid']=$params->node_pid;
        $data['node_level']=$params->node_level;
        $data['node_desc']=$params->node_desc;
        $data['node_title']=$params->node_title;
        $data['node_url']=$params->node_url;
        $data['node_icon']=$params->node_icon;
        $data['node_sort']=$params->node_sort;
        $rbac_db=new rbac_db;
        $node_id=$rbac_db->addNode($data);
        if(!$node_id>0){
            $ret->result->code = -3; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagegetNode($inPath){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg= "";
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->code='101';
            $ret->msg='invalid parameter';
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $node_result=$rbac_db->getNode((int)$inPath[3]);
        if (empty($node_result)) {
            $ret->code='102';
            $ret->msg='data is empty';
        }
        $ret->data=$node_result;    
        return $ret;
    }
    public function pageupdateNode($inPath){
        $params=SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -2; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        if(empty($params->node_title)){
            $ret->result->code = -3; 
            $ret->result->msg= "node_title is empty";
            return $ret;
        }
        $data=array();
        $data['node_pid']=$params->node_pid;
        $data['node_level']=$params->node_level;
        $data['node_desc']=$params->node_desc;
        $data['node_title']=$params->node_title;
        $data['node_url']=$params->node_url;
        $data['node_icon']=$params->node_icon;
        $data['node_sort']=$params->node_sort;
        $rbac_db=new rbac_db;
        $db_ret=$rbac_db->updateNode((int)$inPath[3],$data);
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagenodeOpen($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -101; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        $rbac_db=new rbac_db;
        $db_ret=$rbac_db->changeNodeStatus((int)$inPath[3],0);
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
    public function pagenodeOff($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        if(empty($inPath[3])){
            $ret->result->code = -101; 
            $ret->result->msg= "invalid parameter";
            return $ret;
        }
        $rbac_db=new rbac_db;
        $db_ret=$rbac_db->changeNodeStatus((int)$inPath[3],1);
        if(!$db_ret){
            $ret->result->code = -4; 
            $ret->result->msg= "failed";
            return $ret;
        }else{
            $ret->result->code = 0; 
            $ret->result->msg= "Success!";
            return $ret;
        }
    }
	
	public function pagegetAccessListByUid($inPath){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg  = "";
		$ret->data = '';
        if (empty($inPath[3]) || !is_numeric($inPath[3])) {
            $ret->code=101;
            $ret->msg='invalid parameter';
            return $ret;
        }
        $rbac_db=new rbac_db; 
        $access_result=$rbac_db->getAccessListByUid((int)$inPath[3]);
        if (empty($access_result->items)) {
            $ret->code=102;
            $ret->msg='data is empty';
        }else{
			$ret->code=0;
            $ret->msg='success';
			$ret->data = $access_result->items;
		}
		return $ret;
	}
	public function pageGetNodeByUrl($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->node_url)){
			return api_func::setMsg(1000);
		}
        $rbacDb = new rbac_db; 
        $nodeRet = $rbacDb->getNodeByUrl($params->node_url);
        if (empty($nodeRet)) {
            return api_func::setMsg(3002);
        }
        return api_func::setData($nodeRet);
    }
	
	public function pageGetMemberByName($inPath){
		$params = SJson::decode(utility_net::getPostData());
		if(empty($params->name)){
			return api_func::setMsg(1000);
		}
        $rbacDb = new rbac_db; 
        $memberRet = $rbacDb->getMemberByName($params->name);
        if (empty($memberRet)) {
            return api_func::setMsg(3002);
        }
        return api_func::setData($memberRet);
    }
}
