<?php
class user_userThirdPartyLog {
	public function pageGet($inPath){
		//$params = SJson::decode(utility_net::getPostData(),true);
		$page   = (!empty($inPath[3])) ? (int)$inPath[3] : 1;
		$length = (!empty($inPath[4])) ? (int)$inPath[4] : -1;
		/*if(empty($params)){
			return array('code'=>-1,'msg'=>'params error','data'=>array());
		}*/

		$list = user_db_userThirdPartyLogDao::lists('1=1',$page,$length,'*','last_updated DESC');
		$info = $list->items;

		$logIdArr = array();
		$userIdArr = array();
		foreach($info as $v){
			$logIdArr[]=$v['fk_log'];
			$userIdArr[]=$v['fk_user'];
		}

		$logInfo = user_db_userValueLogDao::getLogByidArr($logIdArr);
		$user_db = new user_db();
		$userInfo = $user_db->getUserProfileByUidArr($userIdArr);
		$userMobile = $user_db->getUserMobileByUidArr($userIdArr);
		foreach ($info as &$v) {
			if(!empty($logInfo->items)){
				foreach($logInfo->items as $log){
					if(!empty($v['fk_log']) && $log['pk_log']==$v['fk_log']){
						$v['ios_coin']=$log['ios_coin'];

					}
				}
			}else{
				$v['ios_coin']='';
			}
			if(!empty($userInfo->items)){
				foreach($userInfo->items as $user){
					if(!empty($v['fk_user']) && $user['user_id']==$v['fk_user']){
						$v['user_name']=$user['real_name'];

					}
				}
			}else{
				$v['user_name']='';
			}
			if(!empty($userMobile->items)){
				foreach($userMobile->items as $user){
					if(!empty($v['fk_user']) && $user['fk_user']==$v['fk_user']){
						$v['mobile']=$user['mobile'];

					}
				}
			}else{
				$v['mobile']='';
			}
		}
		return array(
			"page"  => $list->page,
			"size"  => $list->pageSize,
			"total" => $list->totalPage,
			"totalSize" => $list->totalSize,
			"list"      => $info
		);

	}
}

