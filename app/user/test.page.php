<?php
class user_test{
	public function pageEntry($inPath){
        $api=new user_api;
        $res=$api->getOwnerIdbyAdminId(118,244);
var_dump($res);
		exit;
		$db = new SDb();
		$db->useConfig("db_user", 'main');
		$table="t_user_mobile";
		$db->setLimit(-1);
		$a = $db->select($table);
		foreach($a->items as $item){
			if(empty($item['province'])){
				$m_info = utility_mobile::info($item['mobile']);
				$item['province']=$m_info->province;
				$item['city']=$m_info->city;
				$item['supplier']=$m_info->supplier;
				user_db::updateUserMobile($item['fk_user'],$item);
				print_r($m_info);
				sleep(1);
			}
		}
		print_r($a);
		//print_r($a);
	}
}
