<?php 
class customnav_custom{
	
	//添加
	public function pageAddCustomNav(){
			
			$params = SJson::decode(utility_net::getPostData(), true);
			
			$data = [   
				'fk_org'      => $params['org_id'],
				'title'   	  => $params['title'],            
				'url' 		  => $params['url'],
				'sort'        => 0,
				'status'      => 0
			];
			$res = customnav_db::addNav($data);
			if ($res) return json_encode(array('code'=>1,'msg'=>'success'));

			return json_encode(array('code'=>0,'msg'=>'faild'));
	}
	
	//修改
	public function pageModNav(){
			$params = SJson::decode(utility_net::getPostData(), true);
			if(empty($params['status']) || !isset($params['status']))  $params['status'] = 0;

			$data = [
				'title'   	  => $params['title'],            
				'url' 		  => $params['url'],
				'sort'        => 0,
				'status'      => $params['status']
			];
			$condition = [
				'id' =>$params['id'],
				'fk_org'=>$params['org_id'],
				'status'      => 0
			];
			$res = customnav_db::modNav($condition,$data);
			if ($res) return json_encode(array('code'=>1,'msg'=>'success'));

			return json_encode(array('code'=>0,'msg'=>'faild'));
	}
	//查询
	public function pageSelNav(){

			$params = SJson::decode(utility_net::getPostData(), true);
			$data = [
				//'id'		  => $param['id'],
				'fk_org'      => $params['org_id'],
				//'title'   	  => $params['title'],
				//'url' 		  => $params['url'],
				//'sort'        => 0
				'status'      => 0
			];
		//return $data;
			$res = customnav_db::selNav($data);
			if ($res) return $res;

			return json_encode(array('code'=>0,'msg'=>'faild'));
	}

	public function pageDelNav(){
		$params = SJson::decode(utility_net::getPostData(), true);
		$data=[
			'id' =>$params['id'],
			'fk_org'=>$params['org_id']
		];
		$res =  customnav_db::delNav($data);
		return $res;
	}
	
}