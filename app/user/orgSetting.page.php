<?php
class user_orgSetting{
	public function setResult($data='',$code=0,$msg='success'){
	 	$ret = new stdclass;
	 	$ret->result = new stdclass;
		$ret->result->code =$code;
		$ret->result->data =$data;
		$ret->result->msg= $msg;
		return $ret;    
	}   
	
	public function pageAddXiaowoOrg($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org is not cant empty!";
			return $ret;
		}
       
            $data		 = array(  
							  "fk_org"		=>	isset($params->fk_org) ? $params->fk_org : 0,
							  "types"		=>	isset($params->types) ? $params->types :0 ,
							  "title"		=>	isset($params->title) ? $params->title : '',
							  "thumb_app"	=>	isset($params->thumb_app) ? $params->thumb_app : '',
							  "url"			=>	isset($params->url) ? $params->url : '',
							  "thumb_ipad"	=>	isset($params->thumb_ipad) ? $params->thumb_ipad : '',
							  "create_time"	=>	date("Y-m-d H:i:s"),
							  
						);
		$dbRet	=	user_db_orgsetting::AddXiaowoOrg($data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageupdateXiaowoOrgBanner($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "banner_id is not cant empty!";
			return $ret;
		}
		$bid 		 = !empty($inPath[3]) ? $inPath[3] : 0;
		$data		 = array(  
							  "fk_org"		=>	isset($params->fk_org) ? $params->fk_org : 0,
							  "types"		=>	isset($params->types) ? $params->types :0 ,
							  "title"		=>	isset($params->title) ? $params->title : '',
							  "thumb_app"	=>	isset($params->thumb_app) ? $params->thumb_app : '',
							  "url"			=>	isset($params->url) ? $params->url : '',
							  "thumb_ipad"	=>	isset($params->thumb_ipad) ? $params->thumb_ipad : '',
							  "create_time"	=>	date("Y-m-d H:i:s"),
							  
						);
		$dbRet	=	user_db_orgsetting::updateXiaowoOrgBanner($bid,$data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagexiaowoOrgOneInfo(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$data = array();
		$bannerId 		 = !empty($params->pk_banner) ? $params->pk_banner : 0;
		$orgId 		     = !empty($params->fk_org) ? $params->fk_org : 0;
		if(empty($params->banner_id)){
			$ret->result->code = -100;
			$ret->result->msg  = "banner_id is not cant empty!";
		}
		$info = user_db_orgsetting::xiaowoOrgOneInfo($bannerId,$orgId);
		$ret->data = $info;
		return $ret;
	}
	
	public function pagexiaowoOrgList(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 			= SJson::decode(utility_net::getPostData());
		$data 				= array();
		$oid 			    = !empty($params->fk_org) ? $params->fk_org : 0;
		if(empty($oid)){
			$ret->result->code = -100;
			$ret->result->msg  = "fk_org is not cant empty!";
		}
		$info 			= user_db_orgsetting::xiaowoOrgList($oid);
		$ret->data 		= !empty($info->items) ? $info->items : '';
		return $ret;
	}
	public function pagegetOrgCustomerCateList(){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 			= SJson::decode(utility_net::getPostData());
		$data 				= array();
		$oid 			    = !empty($params->fk_org) ? $params->fk_org : 0;
		if(empty($oid)){
			$ret->result->code = -100;
			$ret->result->msg  = "fk_org is not cant empty!";
		}
		$info 			= user_db_orgsetting::getOrgCustomerCateList($oid);
		$ret->data 		= !empty($info) ? $info : '';
		return $ret;
	}

	public function pageaddCustomerCate($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org is not cant empty!";
			return $ret;
		}
        $data		 = array(  
							  "fk_org"		=>	isset($params->fk_org) ? $params->fk_org : 0,
							  "cate_id"		=>	isset($params->cate_id) ? $params->cate_id : 0,
							  "create_time"	=>	date("Y-m-d H:i:s")
						);
		$catId 		 = array( "cate_id"		=>	isset($params->cate_id) ? $params->cate_id : 0);
		$info 		 = user_db_orgsetting::getOrgCustomerCateList($params->fk_org);
		if(!empty($info)){
			$dbRet =  user_db_orgsetting::updateCustomerCate($params->fk_org,$catId);
		}else{
			$dbRet =  user_db_orgsetting::addCustomerCate($data);
		}
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pagechannelList($inPath){
		$ret 				= new stdclass;
		$ret->result 		= new stdclass;
		$oid 			    = !empty($inPath[3]) ? $inPath[3] : 0;
		if(empty($oid)){
			$ret->result->code = -100;
			$ret->result->msg  = "fk_org is can't empty!";
		}
		$info 			= user_db_orgsetting::channelList($oid);
		$ret->data 		= !empty($info->items) ? $info->items : '';
		return $ret;
	}
	public function pageaddchannel($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org is not can empty!";
			return $ret;
		}
		$data['fk_org'] 		= !empty($params->fk_org) ? (int)$params->fk_org : 0;
		$data['fk_user'] 		= !empty($params->fk_user) ? (int)$params->fk_user : 0;
		$data['create_time'] 	= date('Y-m-d H:i:s');
		$dbRet	=	user_db_orgsetting::addchannel($data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg = $dbRet;
		}
		return $ret;
	}
	public function pageaddChannelBanner($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org is not can empty!";
			return $ret;
		}
		$data['fk_org'] 		= !empty($params->fk_org) ? (int)$params->fk_org : 0;
		$data['fk_user'] 		= !empty($params->fk_user) ? (int)$params->fk_user : 0;
		$data['fk_channel'] 	= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$data['fk_block'] 	    = !empty($params->fk_block) ? (int)$params->fk_block : 0;
		$data['thumb'] 			= !empty($params->thumb) ? $params->thumb : '';
		$data['rgb'] 			= !empty($params->rgb) ? $params->rgb : '';
		$data['url'] 			= !empty($params->url) ? $params->url : '';
		$data['type'] 			= !empty($params->type) ? $params->type : 0;
		$dbRet	=	user_db_orgsetting::addChannelBanner($data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg  = "is failed";
			$ret->result->data = "";
		}else{
			$ret->result->code = 200;
			$ret->result->data =$dbRet;
			$ret->result->msg  = "success";
		}
		return $ret;
	}
	public function pagebannerList($inPath){
		$ret 				= new stdclass;
		$ret->result 		= new stdclass;
		$data = array();
		$params 	 = SJson::decode(utility_net::getPostData());
		if(!empty($params->fk_user_owner)){
            $data['fk_user'] 		= !empty($params->fk_user_owner) ? $params->fk_user_owner : '';
        }
		if(!empty($params->fk_channel)){
            $data['fk_channel'] 		= !empty($params->fk_channel) ? $params->fk_channel : '';
        }
		if(!empty($params->pk_channel)){
            $data['fk_channel'] 		= !empty($params->pk_channel) ? $params->pk_channel : '';
        }
		if(!empty($params->fk_org)){
            $data['fk_org'] 		= !empty($params->fk_org) ? $params->fk_org : '';
        }
		if(!empty($params->fk_block)){
            $data['fk_block'] 		= !empty($params->fk_block) ? $params->fk_block : '';
        }
        if(!empty($params->type)){
            $data['type'] 		= !empty($params->type) ? $params->type : '';
        }
		if(empty($params->fk_org)){
			$ret->result->code = -100;
			$ret->result->msg  = "fk_org is can't empty!";
		}
		$info 			= user_db_orgsetting::bannerList($data);
		$ret->data 		= !empty($info->items) ? $info->items : '';
		return $ret;
	}
	public function pagegetBannerInfo($inPath){
		$ret 				= new stdclass;
		$ret->result 		= new stdclass;
		$data = array();
		$params 	 = SJson::decode(utility_net::getPostData());
		if(!empty($params->pk_banner)){
			$data['pk_banner']    = !empty($params->pk_banner) ? (int)$params->pk_banner : 0;
		}
		if(!empty($params->pk_channel)){
			$data['fk_channel']    = !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		}
		if(!empty($params->fk_user_owner)){
			$data['fk_user']    = !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		}
        if(!empty($params->type)){
            $data['type'] 		= !empty($params->type) ? $params->type : '';
        }
		if(!empty($params->fk_block)){
            $data['fk_block'] 	= !empty($params->fk_block) ? $params->fk_block : '';
        }
		if(!empty($params->fk_channel)){
            $data['fk_channel'] = !empty($params->fk_channel) ? $params->fk_channel : '';
        }
		if(!empty($params->pk_block)){
            $data['fk_block'] 	= !empty($params->pk_block) ? $params->pk_block : '';
        }
		if(!empty($params->banner_id)){
            $data['banner_id'] 	= !empty($params->banner_id) ? $params->banner_id : '';
        }
		$info 			= user_db_orgsetting::getBannerInfo($data);
		$ret->data 		= !empty($info) ? $info : '';
		return $ret;
	}
	public function pageupdateBanner($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org) || empty($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org or banner_id is not can empty!";
			return $ret;
		}
		$condition['pk_banner'] = !empty($inPath[3]) ? $inPath[3] : 0;
		$condition['fk_channel']= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$condition['fk_org'] 	= !empty($params->fk_org) ? (int)$params->fk_org : 0;
		$condition['type'] 		= !empty($params->type) ? $params->type : 0;
		$data['fk_user'] 		= !empty($params->fk_user) ? (int)$params->fk_user : 0;
		if($condition['type']==1){
			$data['thumb'] 			= !empty($params->thumb) ? $params->thumb : '';
			$data['rgb'] 			= !empty($params->rgb) ? $params->rgb : '';
			$data['url'] 			= !empty($params->url) ? $params->url : '';
			
		}elseif($condition['type']==2){
			$data['thumb'] 			= !empty($params->thumb) ? $params->thumb : '';
			$data['url'] 			= !empty($params->url) ? $params->url : '';
		}
		$oneInfo 	= user_db_orgsetting::getBannerInfo($condition);
		if(!empty($oneInfo)){
			$dbRet		=	user_db_orgsetting::updateBanner($condition,$data);
			if($dbRet === false){
				$ret->result->code = -100;
				$ret->result->msg = "";
				$ret->result->data = "";
			}else{
				$ret->result->code = 200;
				$ret->result->msg  = "success";
				$ret->result->data = 0;
			}
		}else{
			$ret->result->code = -100;
			$ret->result->msg = "is fialed";
			$ret->result->data = "";
		}
		return $ret;
	}
	public function pageupdatechannel($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_user_owner  is not can empty!";
			return $ret;
		}
		$condition['pk_channel']= !empty($params->pk_channel) ? (int)$params->pk_channel : 0;
		$condition['fk_user'] 	= !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		$data['name'] 		    = !empty($params->name) ? $params->name : 0;
		$oneInfo 	= user_db_orgsetting::updatechannel($condition,$data);
		if($oneInfo === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
			$ret->result->data = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg  = "success";
			$ret->result->data = 0;
		}
			
			
		
		return $ret;
	}
	public function pagedelBanner($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org) || empty($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org or banner_id is not can empty!";
			return $ret;
		}
		$condition				= array();
		$condition['pk_banner'] = !empty($inPath[3]) ? $inPath[3] : 0;
		$condition['fk_org'] 	= !empty($params->fk_org) ? (int)$params->fk_org : 0;
		$condition['type'] 		= !empty($params->type) ? $params->type : 0;
		$data['fk_user'] 		= !empty($params->fk_user) ? (int)$params->fk_user : 0;
		if(!empty($params->fk_channel)){
			$condition['fk_channel']= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		}
		$oneInfo 				= user_db_orgsetting::getBannerInfo($condition);
		if(!empty($oneInfo)){
			$dbRet		=	user_db_orgsetting::delBanner($condition,$data);
			if($dbRet === false){
				$ret->result->code = -100;
				$ret->result->msg = "";
			}else{
				$ret->result->code = 200;
				$ret->result->msg ="success";
			}
		}else{
			$ret->result->code = -100;
			$ret->result->msg = "is fialed";
		}
		return $ret;
	}
	
	public function pagedelXiaoWoOrgBanner($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_org) || empty($inPath[3])) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_org or banner_id is not can empty!";
			return $ret;
		}
		$condition['pk_banner'] = !empty($inPath[3]) ? $inPath[3] : 0;
		$condition['fk_org'] 	= !empty($params->fk_org) ? (int)$params->fk_org : 0;
		$dbRet		=	user_db_orgsetting::delXiaoWoOrgBanner($condition,$data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "is failed";
		}else{
			$ret->result->code = 200;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	
	public function pagegetblockCheck($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		if(empty($params->fk_user_owner)){
            $ret->result->code = -2;
            $ret->result->msg= "fk_user_owner is not empty";
            return $ret;
		}
		$condition					= array();
		$condition['fk_user_owner'] = !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
        $condition['fk_channel']	= !empty($params->pk_channel) ? (int)$params->pk_channel : 0;
		$dbRet = user_db_orgsetting::getblockCheck($condition);
		if(empty($dbRet->items)){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        foreach($dbRet->items as $k=>$v){
            if(!empty($v['query_str'])){
                $queryArr=array();
                $tmpArr=explode(',',$v['query_str']); 
                foreach($tmpArr as $tv){
                    $arr=explode(':',$tv);
					$queryArr[$arr[0]]= !empty($arr[1]) ? $arr[1] : '0';
                }
                $dbRet->items[$k]['query_arr']=$queryArr;
            }
            if(!empty($v['course_ids'])){
                $courseArr=explode(',',$v['course_ids']);
                $dbRet->items[$k]['course_arr']=$courseArr;
            }
        }
        $ret->result->code = 0;
        $ret->data=$dbRet->items;
        return $ret;
	}
	public function pageaddOrgblock($inPath){
		$ret 				= new stdclass;
		$ret->result 		=  new stdclass;
		$ret->result->code 	= -1;
		$ret->result->msg	= "";
		$params 			= SJson::decode(utility_net::getPostData(),true);
		if (empty($params)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "params is empty!";
			return $ret;
		}
		$dbRet = user_db_orgsetting::addOrgblock($params);
		if($dbRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail insert";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagegetChannelBlockList($inPath){
		$ret 				= new stdclass;
        $params 			= SJson::decode(utility_net::getPostData());
		if(empty($params->fk_channel)){
            $ret->result->code  = -2;
            $ret->result->msg	= "fk_channel is empty";
            return $ret;
		}
        $condition['fk_user_owner'] = !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
        $condition['fk_channel'] 	= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$blockData = user_db_orgsetting::getChannelBlockList($condition);
		if(empty($blockData->items)){
			$ret->code = -2;
			$ret->msg  = "the data is not found!";
			return $ret;
		}
		$courseIds  = array();
        if(!empty($blockData->items)){
            $queryArr	=	array();
            foreach($blockData->items as $k=>$v){
                $arr1	   = explode(',',$v['query_str']);
                $arr2	   = explode(',',$v['course_ids']);
				$type 	   = array();
				foreach($arr1 as $a=>$b){
					$value 		    = explode(":",$b);
					if(!empty($value[1])){
						$type[$value[0]]= $value[1];
					}
				}
				$blockData->items[$k]['query_arr']	= $type;
				$blockData->items[$k]['course_arr']	= $arr2;
            }
        }
        $ret->data		= $blockData->items;
        return $ret;
	}
	public function pagegetchannelOneInfo($inPath){
		$ret 				= new stdclass;
		$data = array();
		$params 	 = SJson::decode(utility_net::getPostData());
		if(!empty($params->fk_user_owner)){
			$data['fk_user'] 	= !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		}
		if(!empty($params->fk_org)){
			$data['fk_org'] 	= !empty($params->fk_org) ? (int)$params->fk_org : 0;
		}
		$data['pk_channel'] = !empty($params->pk_channel) ? (int)$params->pk_channel : 0;
		if(empty($params->fk_org)){
			$ret->code = -100;
			$ret->msg  = "fk_org is can't empty!";
		}
       
		$info 			= user_db_orgsetting::getchannelOneInfo($data);
		$ret->data 		= !empty($info) ? $info : '';
		return $ret;
	}
	public function pagegetBlockOneInfoCheck($inPath){
		$ret 					= new stdclass;
		$data 					= array();
		$params 	 			= SJson::decode(utility_net::getPostData());
		$data['pk_block'] 	= !empty($params->pk_block) ? (int)$params->pk_block : 0;
        if(!empty($params->fk_user_owner)){
            $data['fk_user_owner'] 	= $params->fk_user_owner;
        }
        if(!empty($params->fk_channel)){
            $data['fk_channel'] 	= $params->fk_channel;
        }
		if(empty($params->pk_block)){
			$ret->code = -100;
			$ret->msg  = "pk_block is can't empty!";
		}
		$info = user_db_orgsetting::getBlockOneInfoCheck($data);
		if($info===false){
			$ret->code = -2;
			$ret->msg = "the data is not found!";
			return $ret;
		}
        if(!empty($info['query_str'])){
            $queryArr=array();
            $tmpArr1=explode(',',$info['query_str']); 
            foreach($tmpArr1 as $tv1){
                $arr1=explode(':',$tv1);
                $queryArr[$arr1[0]]= !empty($arr1[1]) ? $arr1[1] : '0';
            }
            $info['query_arr']=$queryArr;
        }
        if(!empty($info['course_ids'])){
            $tmpArr2=explode(',',$info['course_ids']); 
            $info['course_arr']=$tmpArr2;
        }
        $ret->code   = 0;
        $ret->data   = $info;
        return $ret;
	}
	public function pageDeleteBlock($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_channel)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_channel or banner_id is not can empty!";
			return $ret;
		}
		$condition 				= array();
		$condition['fk_channel']= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
        if(!empty($params->pk_block)){
            $condition['pk_block'] 	= $params->pk_block;
        }
		$condition['fk_user_owner'] 	= !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		$dbRet		=	user_db_orgsetting::DeleteBlock($condition);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagedeleteChannel($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_channel)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_channel or banner_id is not can empty!";
			return $ret;
		}
		$condition 				= array();
		$condition['pk_channel']= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$condition['fk_org'] 	= !empty($params->fk_org) ? $params->fk_org : 0;
		$condition['fk_user'] 	= !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		$dbRet		=	user_db_orgsetting::deleteChannel($condition);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	
	public function pagedelChannelBannerMore($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_channel)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_channel is not can empty!";
			return $ret;
		}
		$condition 				= array();
		$condition['fk_block'] 	= !empty($params->pk_block) ? $params->pk_block : 0;
		$condition['fk_channel']= !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$condition['fk_user'] 	= !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		$condition['type'] 		= !empty($params->type) ? (int)$params->type : 0;
		$dbRet		=	user_db_orgsetting::delBanner($condition);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagedeleteOrgBlock($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_channel)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_channel or banner_id is not can empty!";
			return $ret;
		}
		$condition 				        = array();
		$condition['fk_channel']        = !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$condition['fk_user_owner'] 	= !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
		$dbRet		=	user_db_orgsetting::deleteOrgBlock($condition);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagedeleteBannerAndThumb($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_channel)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_channel or banner_id is not can empty!";
			return $ret;
		}
		$condition 				        = array();
		$condition['fk_channel']        = !empty($params->fk_channel) ? (int)$params->fk_channel : 0;
		$condition['fk_user'] 	        = !empty($params->fk_user_owner) ? (int)$params->fk_user_owner : 0;
        $idStr                          = !empty($params->type) ? $params->type : '';
		$dbRet		=	user_db_orgsetting::deleteBannerAndThumb($idStr,$condition);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 200;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	
	public function pageupdateOrgblock($inPath){
		$ret 				= new stdclass;
		$ret->result 		=  new stdclass;
		$ret->result->code 	= -1;
		$ret->result->msg	= "";
		$params 	 		= SJson::decode(utility_net::getPostData());
		if (empty($params->fk_user_owner) || empty($params->pk_block)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_user_owner  or pk_block is empty!";
			return $ret;
		}
		$where 		= array();
		$data		= array();
		$params 	= SJson::decode(utility_net::getPostData());
		$where['pk_block'] 			= !empty($params->pk_block) ? $params->pk_block : 0;
		$where['fk_user_owner']  	= !empty($params->fk_user_owner) ? $params->fk_user_owner : 0;
		$where['fk_channel'] 		= !empty($params->fk_channel) ? $params->fk_channel : 0;
		if(!empty($params->title)){
			$data['title']			= !empty($params->title) ? $params->title : '';
		}
		if(isset($params->type)){
			$data['type']			= isset($params->type) ? $params->type : 0;
		}
		$data['row_count']			= !empty($params->row_count) ? $params->row_count : 0;
		$data['recommend']			= !empty($params->recommend) ? $params->recommend : 2;
		$data['query_str']			= !empty($params->query_str) ? $params->query_str : '';
		$data['order_by']			= !empty($params->order_by) ? $params->order_by : '';
		$data['course_ids']			= !empty($params->course_ids) ? $params->course_ids : '';
		$data['sort']			 	= !empty($params->sort) ? $params->sort : 0;
		$data['last_updated']		= !empty($params->last_updated) ? $params->last_updated : '';
		$data['set_url']			= !empty($params->set_url) ? $params->set_url : '';
		
		$data['thumb_left']			= !empty($params->thumb_left) ? $params->thumb_left : '';
		$data['thumb_right']		= !empty($params->thumb_right) ? $params->thumb_right : '';
		$data['thumb_left_url']		= !empty($params->thumb_left_url) ? $params->thumb_left_url : '';
		$data['thumb_right_url']	= !empty($params->thumb_right_url) ? $params->thumb_right_url : '';
		$info 		= user_db_orgsetting::updateOrgblock($where,$data);
		if($info === false){
			$ret->result->code = -2;
			$ret->result->msg = "fail update";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
	public function pageupdateChannelThumbPic($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->pk_block) || empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "pk_template or fk_user_owner is not cant empty!";
			return $ret;
		}
		$con['pk_block'] 		 = !empty($params->pk_block) ? $params->pk_block : 0;
		$con['fk_channel'] 		 = !empty($params->fk_channel) ? $params->fk_channel : 0;
		$con['fk_user_owner'] 	 = !empty($params->fk_user_owner) ? $params->fk_user_owner : 0;
		$dbRet		 			 = user_db_orgsetting::updateChannelThumbPic($con,$params);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagegetOrgChannelBlockInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
        $params 	 = SJson::decode(utility_net::getPostData());
		if(empty($params->fk_user_owner)){
            $ret->result->code  = -2;
            $ret->result->msg   = "fk_user_owner is empty";
            return $ret;
		}
        $data  = array();
		if(!empty($params->pk_block)){
			$data['pk_block'] = !empty($params->pk_block) ? $params->pk_block : 0;
		}
        $data['fk_user_owner'] = !empty($params->fk_user_owner) ? $params->fk_user_owner : 0;
        $data['fk_channel'] = !empty($params->fk_channel) ? $params->fk_channel : 0;
		$dbRet = user_db_orgsetting::getChannelBlockList($data);
		if(empty($dbRet->items)){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        foreach($dbRet->items as $k=>$v){
            if(!empty($v['query_str'])){
                $queryArr=array();
                $tmpArr=explode(',',$v['query_str']); 
                foreach($tmpArr as $tv){
                    $arr=explode(':',$tv);
					$queryArr[$arr[0]]= !empty($arr[1]) ? $arr[1] : '0';
                }
                $dbRet->items[$k]['query_arr']=$queryArr;
            }
            if(!empty($v['course_ids'])){
                $courseArr=explode(',',$v['course_ids']);
                $dbRet->items[$k]['course_arr']=$courseArr;
            }
        }
        $ret->result->code = 0;
        $ret->data=$dbRet->items;
        return $ret;
	}
    public function pagedeleteOrgblockInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		$bid 	= !empty($params->fk_block) ? $params->fk_block : 0;
        $ownerId= !empty($params->fk_user_owner) ? $params->fk_user_owner : 0;
        $dbRet=user_db_orgsetting::deleteOrgblockInfo($bid,$ownerId);
		if($dbRet === false){
			$ret->result->code = -2;
			$ret->result->msg = "failed";
		}else{
			$ret->result->code = 0;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pagegetblockOneInfo($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		$params = SJson::decode(utility_net::getPostData());
		if(empty($inPath[3])){
            $ret->result->code = -2;
            $ret->result->msg= "tid is empty";
            return $ret;
		}
        $tid=(int)$inPath[3];
		$ownerId = isset($params) ? $params : 0;
		$dbRet = user_db_orgsetting::getblockOneInfo($tid,$ownerId);
		if($dbRet===false){
			$ret->result->code = -2;
			$ret->result->msg = "the data is not found!";
			return $ret;
		}
        if(!empty($dbRet['query_str'])){
            $queryArr=array();
            $tmpArr1=explode(',',$dbRet['query_str']); 
            foreach($tmpArr1 as $tv1){
                $arr1=explode(':',$tv1);
                $queryArr[$arr1[0]]= !empty($arr1[1]) ? $arr1[1] : '0';
            }
            $dbRet['query_arr']=$queryArr;
        }
        if(!empty($dbRet['course_ids'])){
            $tmpArr2=explode(',',$dbRet['course_ids']); 
            $dbRet['course_arr']=$tmpArr2;
        }
        $ret->result->code = 0;
        $ret->data=$dbRet;
        return $ret;
	}
    public function pageaddChannelBlockData($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_block) || empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_block or fk_user_owner is not cant empty!";
			return $ret;
		}
		
		$data		 = array(  
							  "fk_block"		=>	isset($params->fk_block) ? $params->fk_block : 0,
							  "fk_channel"		=>	isset($params->fk_channel) ? $params->fk_channel : 0,
							  "fk_user_owner"	=>	isset($params->fk_user_owner) ? $params->fk_user_owner : 0,
							  "recommend"		=>	isset($params->recommend) ? $params->recommend : 1,
							  "title"			=>	isset($params->title) ? $params->title : '',
							  "row_count"		=>	isset($params->row_count) ? $params->row_count : 0,
							  "query_str"		=>	isset($params->query_str) ? $params->query_str : '',
							  "order_by"		=>	isset($params->order_by) ? $params->order_by : '',
							  "course_ids"		=>	isset($params->course_ids) ? $params->course_ids : '',
							  "sort"			=>	isset($params->sort) ? $params->sort : 1,
							  "create_time"		=>	isset($params->create_time) ? $params->create_time : '',
							  "last_updated"	=>	isset($params->last_updated) ? $params->last_updated : '',
							  "set_url"			=>	isset($params->set_url) ? $params->set_url : '',
							  "type"			=>	isset($params->type) ? $params->type : '',
							  "thumb_left"		=>	isset($params->thumb_left) ? $params->thumb_left : '',
							  "thumb_right"		=>	isset($params->thumb_right) ? $params->thumb_right : '',
							  "thumb_left_url"	=>	isset($params->thumb_left_url) ? $params->thumb_left_url : '',
							  "thumb_right_url"	=>	isset($params->thumb_right_url) ? $params->thumb_right_url : ''
						);
		$dbRet	=	user_db_orgsetting::addChannelBlockData($data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    
    public function pageupdateChannelBlockData($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code  = -1;
		$ret->result->msg	= "";
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->fk_block) || empty($params->fk_user_owner)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "fk_block or fk_user_owner is not cant empty!";
			return $ret;
		}
		$tid 		 = !empty($params->fk_block) ? $params->fk_block : 0;
		$ownerId	 = !empty($params->fk_user_owner) ? $params->fk_user_owner : 0;
		$data		 = array( 
							  "recommend"		=>	isset($params->recommend) ? $params->recommend : 1,
							  "title"			=>	isset($params->title) ? $params->title : '',
							  "row_count"		=>	isset($params->row_count) ? $params->row_count : 0,
							  "query_str"		=>	isset($params->query_str) ? $params->query_str : '',
							  "order_by"		=>	isset($params->order_by) ? $params->order_by : '',
							  "course_ids"		=>	isset($params->course_ids) ? $params->course_ids : '',
							  "sort"			=>	isset($params->sort) ? $params->sort : 1,
							  "create_time"		=>	isset($params->create_time) ? $params->create_time : '',
							  "last_updated"	=>	isset($params->last_updated) ? $params->last_updated : '',
							  "set_url"			=>	isset($params->set_url) ? $params->set_url : '',
							  "type"			=>	isset($params->type) ? $params->type : '',
							  "thumb_left"		=>	isset($params->thumb_left) ? $params->thumb_left : '',
							  "thumb_right"		=>	isset($params->thumb_right) ? $params->thumb_right : '',
							  "thumb_left_url"	=>	isset($params->thumb_left_url) ? $params->thumb_left_url : '',
							  "thumb_right_url"	=>	isset($params->thumb_right_url) ? $params->thumb_right_url : ''
										  );
		$dbRet	=	user_db_orgsetting::updateChannelBlockData($tid,$ownerId,$data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg ="success";
		}
		return $ret;
	}
    public function pageaddTeacherActivity($inPath){
		$ret 		 = new stdclass;
		$ret->result =  new stdclass;
		$params 	 = SJson::decode(utility_net::getPostData());
		$data 		 = array();
		if (empty($params->name) || empty($params->mobile)) {
		    $ret->result->code = -2;
		    $ret->result->msg= "name or mobile is not cant empty!";
			return $ret;
		}
		$data		= array(  
							  "name"			=>	isset($params->name) ? $params->name : 0,
							  "mobile"			=>	isset($params->mobile) ? $params->mobile : 0,
							  "fk_user"			=>	isset($params->fk_user) ? $params->fk_user : 0,
							  "create_time"		=>	date("Y-m-d H:i:s"),
						);
		$dbRet		=	user_db_orgsetting::addTeacherActivity($data);
		if($dbRet === false){
			$ret->result->code = -100;
			$ret->result->msg  = "";
		}else{
			$ret->result->code = 100;
			$ret->result->msg  = "success";
		}
		return $ret;
	}
	public function pagegetteacherActivityOneOfInfo(){
		$ret 				= new stdclass;
		$params 			= SJson::decode(utility_net::getPostData());
		$data 				= array();
		if(!empty($params->fk_user)){
			$data['fk_user']= !empty($params->fk_user) ? $params->fk_user : 0;
		}
		if(!empty($params->mobile)){
			$data['mobile'] = !empty($params->mobile) ? $params->mobile : 0;
		}
		$info 				= user_db_orgsetting::getteacherActivityOneOfInfo($data);
		$ret->data 			= !empty($info) ? $info : '';
		return $ret;
	}
}
