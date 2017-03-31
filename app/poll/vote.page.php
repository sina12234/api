<?php
class poll_vote
{

/************************************************ t_vote *************************************************/

	//投票列表
	public function pageVoteList($inPath)
	{
		$params = SJson::decode(utility_net::getPostData(), true);
		
		$page   = isset($inPath[3]) ? (int)($inPath[3]) : 1;
        $length = isset($inPath[4]) ? (int)($inPath[4]) : 20;
		
		if(isset($params['sType']) && $params['sType']=='ing'){
			$data['sType'] = 'ing';
		}
		if(isset($params['sType']) && $params['sType']=='end'){
			$data['sType'] = 'end';
		}
		if(!empty($params['keywords'])){
			$data['name'] = $params['keywords'];
		}
		if(!empty($params['ownerId'])){
			$data['ownerId'] = $params['ownerId'];
		}
		
		$data['orderby'] = !empty($params['orderby']) ? $params['orderby'] : 'desc';
		
		$list = poll_db::voteList($page,$length,$data);
		if (empty($list->items)) return api_func::setMsg(3002);
		
		return $list;
	}
	
	//获取单条投票数据
	public function pageVoteOne($inPath){
		$voteId = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
		if (!$voteId) return api_func::setMsg(1000);

        $res = poll_db::VoteRow($voteId);
        if (empty($res)) return api_func::setMsg(3002);

        return api_func::setData($res);
	}
	
	//添加投票
	public function pageVoteAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        $data = [
			'fk_user_owner' => $params['ownerId'],            
			'fk_user'       => $params['userId'],                
			'name'          => $params['title'],                     
			'descript'      => isset($params['descript'])?$params['descript']:'',                
			'thumb'         => isset($params['thumb'])?$params['thumb']:'',                   
			'thumb1'        => isset($params['thumb1'])?$params['thumb1']:'',                  
			'thumb2'        => isset($params['thumb2'])?$params['thumb2']:'',               
			'object_type'   => isset($params['objectType'])?$params['objectType']:0,              
			'type'          => $params['type'],                  
			'multi_select'  => $params['multiSelect'],             
			'select_count'  => isset($params['selectCount'])?$params['selectCount']:0,           
			'status'        => 1,              
			'create_time'   => date('Y-m-d H:i:s'),             
			'start_time'    => $params['startTime'],            
			'end_time'      => $params['endTime'],             
			'last_updated'  => date('Y-m-d H:i:s')
        ];
		
		$res = poll_db::voteAdd($data);
		if ($res) return api_func::setData(['voteId'=>$res]);
        return api_func::setMsg(1);
    }
	
	//修改投票
	public function pageVoteUpdate(){
		
		$params = SJson::decode(utility_net::getPostData(), true);
		
		$data = array();
		if (empty($params['voteId'])) {
            return api_func::setMsg(1000);
        }
		if(!empty($params['userCount'])){
			$data['user_count'] = $params['userCount'];
		}
		if(!empty($params['type'])){
			$data['type'] = $params['type'];
		}
		if(!empty($params['status'])){
			$data['status'] = $params['status'];
		}
		if(!empty($params['shareCount'])){
			$data['share_count'] = $params['shareCount'];
		}
		
		if(empty($data)){
			return api_func::setMsg(1000);
		}

		if (poll_db::voteUpdate($params['voteId'], $data)) return api_func::setMsg(0);

		return api_func::setMsg(1);
	}
	
/********************************************* t_vote_option ***************************************************/	
	
	//获取投票选项列表
	public function pageOptioByVoteId($inPath){
		$voteId = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
		if (!$voteId) return api_func::setMsg(1000);
		$list = poll_db::OptionList($voteId);
		if (empty($list->items)) return api_func::setMsg(3002);
		
		return $list;
	}
	
	//获取单条投票选项数据
	public function pageOptionRow($inPath){
		$optionId = isset($inPath[3]) && $inPath[3] ? (int)($inPath[3]) : 0;
		if (!$optionId) return api_func::setMsg(1000);
		
		$list = poll_db::optionRow($optionId);
		if (empty($list)) return api_func::setMsg(3002);
		
		return $list;
	}
	
	//添加投票选项
	public function pageOptionAdd(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$data = [              
			'fk_vote'       => $params['voteId'],                 
			'object_type'   => $params['objectType'],            
			'object_id'     => $params['objectId'],                
			'name_display'  => isset($params['nameDisplay'])?$params['nameDisplay']:'',          
			'thumb_display' => isset($params['thumbDisplay'])?$params['thumbDisplay']:'',          
			'order_no'      => $params['orderNo'],              
			'status'        => 1,                 
			'last_updated'  => date('Y-m-d H:i:s')
		];
		$res = poll_db::optionAdd($data);
		if ($res) return api_func::setData(0);
        return api_func::setMsg(1);
	}
	
/************************************************** t_vote_log ***************************************************/

	//获取用户投票数据列表
	public function pageUserLogList($inPath){
	
		$params = SJson::decode(utility_net::getPostData(), true);
		
		$page   = isset($inPath[3]) ? (int)($inPath[3]) : 1;
        $length = isset($inPath[4]) ? (int)($inPath[4]) : 20;
		
		if(!empty($params['voteId'])){
			$data['voteId'] = $params['voteId'];
		}
		if(!empty($params['userId'])){
			$data['userId'] = $params['userId'];
		}
		
		$list = poll_db::userLogList($data,$page,$length);

		if (empty($list->items)) return api_func::setMsg(3002);
		
		return $list;
		
	}

	//添加用户投票记录
	public function pageUserLogAdd(){
		$params = SJson::decode(utility_net::getPostData(), true);
		
		if(empty($params['userId']) || empty($params['voteId']) || empty($params['optionId'])){
			return api_func::setMsg(1000);
		}
		
        $data = [
			'fk_user'      => $params['userId'],
			'fk_vote'      => $params['voteId'],
			'fk_option'    => $params['optionId'],
			'status'       => 1,
			'last_updated' => date("Y-m-d H:i:s")
        ];
		
		$res = poll_db::userLogAdd($data); 
		if($res){
			//修改t_vote_option  (total_count)
			$this->setOptionCount($params['optionId']);
			//修改t_vote  (total_count)
			$this->setVoteCount($params['voteId']);
			
			return api_func::setData(['logId'=>$res]);
		}
     
        return api_func::setMsg(1);
	}

	private function setOptionCount($optionId){
		$list = poll_db::optionRow($optionId);
		if (empty($list)) return api_func::setMsg(3002);
		
		$totalCount = $list['total_count']+1;
		
		return poll_db::optionEdit($optionId,array('total_count'=>$totalCount));
	}
	
	private function setVoteCount($voteId){
		$res = poll_db::VoteRow($voteId);
        if (empty($res))return api_func::setMsg(3002);
		
		$totalCount = $res['total_count']+1;
		return poll_db::voteUpdate($voteId,array('total_count'=>$totalCount));
	}
	
	
	public function pageAddMsgTask(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$data = [   
			'fk_vote'      => $params['voteId'],
			'fk_user_to'   => $params['userTo'],            
			'fk_user_from' => $params['userFrom'],
			'msgType'      => $params['msgType'],
			'content'      => $params['content']
		];
		$res = poll_db::msgTaskAdd($data);
		if ($res) return api_func::setData(0);

        return api_func::setMsg(1);
	}
}
