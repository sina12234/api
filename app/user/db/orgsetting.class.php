<?php
class user_db_orgsetting{
	public static function InitDB($dbname="db_user",$dbtype="main") {
		redis_api::useConfig($dbname);
		$db = new SDb();
		$db->useConfig($dbname, $dbtype);
		return $db;
	}
	public static function AddXiaowoOrg($data){
		$db = self::InitDB();
		$table=array("t_organization_banner_app");
		return $db->insert($table,$data);
	}
	public static function updateXiaowoOrgBanner($bid,$data){
		$db = self::InitDB();
		$table=array("t_organization_banner_app");
		return $db->update($table, array('pk_banner' => $bid,"fk_org"=>isset($data['fk_org']) ? $data['fk_org'] : 0), $data);
	}
	public static function xiaowoOrgOneInfo($bannerId,$orgId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_banner_app');
        $condition=array("pk_banner"=>$bannerId,"fk_org"=>$orgId);
        $item=array(
                'pk_banner'=>'pk_banner',
                'fk_org'=>'fk_org',
                'thumb_app'=>'thumb_app',
                'thumb_ipad'=>'thumb_ipad',
				'title'=>'title',
                'url'=>'url',
                'create_time'=>'create_time',
                'types'=>'types',
            );
        return $db->selectOne($table, $condition,$item);
    }
	public static function xiaowoOrgList($ownerId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_banner_app');
        $condition=array('fk_org'=>$ownerId);
        $item=array(
                'pk_banner'=>'pk_banner',
                'fk_org'=>'fk_org',
                'thumb_app'=>'thumb_app',
                'thumb_ipad'=>'thumb_ipad',
				'title'=>'title',
                'url'=>'url',
                'create_time'=>'create_time',
                'types'=>'types',
            );
        return $db->select($table, $condition,$item,'','');
    }
	public static function getOrgCustomerCateList($ownerId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_customer_cate_app');
        $condition=array('fk_org'=>$ownerId);
        $item=array(
                'customer_id'=>'customer_id',
                'fk_org'=>'fk_org',
                'cate_id'=>'cate_id',
                'create_time'=>'create_time',
            );
        return $db->selectone($table, $condition,$item,'','');
    }
	public static function addCustomerCate($data){
		$db 		= self::InitDB();
		$table		=array("t_organization_customer_cate_app");
		return $db->insert($table,$data);
	}
	public static function updateCustomerCate($oid,$data){
		$db 		= self::InitDB();
		$table		=array("t_organization_customer_cate_app");
		return $db->update($table, array("fk_org"=>$oid), $data);
	}
	
	public static function channelList($oid){
        $db 		= self::InitDB('db_user', 'query');
        $table 		= array('t_organization_channel');
        $condition	= array('fk_org'=>$oid);
        $item		= array(
						'pk_channel'=>'pk_channel',
						'name'=>'name',
						'fk_org'=>'fk_org',
						'fk_user'=>'fk_user',
						'create_time'=>'create_time',
						'last_updated'=>'last_updated'
					 );
        return $db->select($table, $condition,$item,'','');
    }
	public static function updatechannel($condition,$data){
		$db 		= self::InitDB();
		$table		=array("t_organization_channel");
		return $db->update($table,$condition, $data);
	}
	public static function getchannelOneInfo($condition){
        $db 		= self::InitDB('db_user', 'query');
        $table 		= array('t_organization_channel');
        $item		= array(
						'pk_channel'=>'pk_channel',
						'name'=>'name',
						'fk_org'=>'fk_org',
						'fk_user'=>'fk_user',
						'create_time'=>'create_time',
						'last_updated'=>'last_updated'
					 );
        return $db->selectOne($table, $condition,$item,'','');
    }
	public static function addchannel($data){
		$db 		= self::InitDB();
		$table		=array("t_organization_channel");
		return $db->insert($table,$data);
	}
	public static function addChannelBanner($data){
		$db 		= self::InitDB();
		$table		=array("t_organization_channel_banner");
		return $db->insert($table,$data);
	}
	public static function bannerList($data){
        $db 		= self::InitDB('db_user', 'query');
        $table 		= array('t_organization_channel_banner');
        $item		= array(
						'pk_banner'=>'pk_banner',
						'fk_user'=>'fk_user',
						'fk_org'=>'fk_org',
						'fk_channel'=>'fk_channel',
						'fk_block'=>'fk_block',
						'title'=>'title',
						'type'=>'type',
						'thumb'=>'thumb',
						'url'=>'url',
						'width'=>'width',
						'height'=>'height',
						'rgb'=>'rgb',
						'last_updated'=>'last_updated'
					 );
        return $db->select($table, $data,$item,'','');
    }
	public static function updateBanner($condition,$data){
		$db 		= self::InitDB();
		$table		=array("t_organization_channel_banner");
		return $db->update($table, $condition, $data);
	}
	public static function getBannerInfo($data){
        $db 		= self::InitDB('db_user', 'query');
        $table 		= array('t_organization_channel_banner');
        $item		= array(
						'pk_banner'=>'pk_banner',
						'fk_user'=>'fk_user',
						'fk_org'=>'fk_org',
						'fk_channel'=>'fk_channel',
						'fk_block'=>'fk_block',
						'title'=>'title',
						'type'=>'type',
						'thumb'=>'thumb',
						'url'=>'url',
						'width'=>'width',
						'height'=>'height',
						'rgb'=>'rgb',
						'last_updated'=>'last_updated'
					 );
        return $db->selectOne($table, $data,$item,'','');
    }
	public static function delBanner($condition){
		$table		=	array("t_organization_channel_banner");
		$db 		= 	self::InitDB();
		return $db->delete($table,$condition);
	}
	public static function delXiaoWoOrgBanner($condition){
		$table		=	array("t_organization_banner_app");
		$db 		= 	self::InitDB();
		return $db->delete($table,$condition);
	}
	public static function getblockCheck($condition){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_block_check');
        $item=array(
                'block_id'=>'pk_block',
                'owner_id'=>'fk_user_owner',
				'fk_channel'=>'fk_channel',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
				'sort'=>'sort',
				'type'=>'type',
				'thumb_left'=>'thumb_left',
				'thumb_right'=>'thumb_right',
				'thumb_left_url'=>'thumb_left_url',
				'thumb_right_url'=>'thumb_right_url',
            );
        //$orderby=array('sort'=>'asc');
        return $db->select($table, $condition,$item,'',$orderby='');
    }
	public static function getBlockOneInfoCheck($condition){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_block_check');
        $item=array(
                'block_id'=>'pk_block',
                'owner_id'=>'fk_user_owner',
				'fk_channel'=>'fk_channel',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
				'sort'=>'sort',
				'type'=>'type',
				'thumb_left'=>'thumb_left',
				'thumb_right'=>'thumb_right',
				'thumb_left_url'=>'thumb_left_url',
				'thumb_right_url'=>'thumb_right_url',
            );
        //$orderby=array('sort'=>'asc');
        return $db->selectOne($table, $condition,$item,'',$orderby='');
    }
	public static function addOrgblock($data){
		$db = self::InitDB();
		$table=array("t_organization_block_check");
		return $db->insert($table,$data);
	}
    public static function getChannelBlockList($condition){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_block');
        $item=array(
                'pk_block'=>'fk_block',
                'fk_channel'=>'fk_channel',
                'owner_id'=>'fk_user_owner',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
				'sort'=>'sort',
				'type'=>'type',
				'thumb_left'=>'thumb_left',
				'thumb_right'=>'thumb_right',
				"thumb_left_url"=>"thumb_left_url",
				"thumb_right_url"=>"thumb_right_url"
            );
        //$orderby=array('sort'=>'asc');
        return $db->select($table, $condition,$item,'',$orderby='');
    }
	public static function DeleteBlock($condition){
		$table		=	array("t_organization_block_check");
		$db 		= 	self::InitDB();
		return $db->delete($table,$condition);
	}
    public static function deleteChannel($condition){
		$table		=	array("t_organization_channel");
		$db 		= 	self::InitDB();
		return $db->delete($table,$condition);
	}
    public static function deleteOrgBlock($condition){
		$table		=	array("t_organization_block");
		$db 		= 	self::InitDB();
		return $db->delete($table,$condition);
	}
    public static function deleteBannerAndThumb($idStr,$condition){
		$table		=	array("t_organization_channel_banner");
		$db 		= 	self::InitDB();
		return $db->delete($table,$condition);
	}
	public static function updateOrgblock($where,$data){
		$db 		= self::InitDB();
		$table		=array("t_organization_block_check");
		return $db->update($table, $where, $data);
	}
	public static function updateChannelThumbPic($con, $data){
		$db = self::InitDB();
		$table=array("t_organization_block_check");
		return $db->update($table, $con, $data);
	}
    public static function deleteOrgblockInfo($bid,$ownerId){
		$table		=	array("t_organization_block");
		$db 		= 	self::InitDB();
		$condition	=	"fk_block IN(".$bid.") AND fk_user_owner=".$ownerId."";
		return $db->delete($table,$condition);
	}
    public static function getblockOneInfo($tid,$ownerId){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_organization_block');
        $condition=array('fk_block'=>$tid,"fk_user_owner"=>$ownerId);
        $item=array(
                'block_id'=>'fk_block',
                'fk_channel'=>'fk_channel',
                'owner_id'=>'fk_user_owner',
                'title'=>'title',
                'row_count'=>'row_count',
                'recommend'=>'recommend',
                'query_str'=>'query_str',
                'order_by'=>'order_by',
                'course_ids'=>'course_ids',
                'create_time'=>'create_time',
                'last_updated'=>'last_updated',
				'set_url'=>'set_url',
            );
        return $db->selectOne($table, $condition,$item);
    }
    public static function addChannelBlockData($data){
		$db = self::InitDB();
		$table=array("t_organization_block");
		return $db->insert($table,$data);
	}
    public static function updateChannelBlockData($tid,$ownerId,$data){
		$db = self::InitDB();
		$table=array("t_organization_block");
		return $db->update($table, array('fk_block' => $tid,"fk_user_owner"=>$ownerId), $data);
	}
	 public static function addTeacherActivity($data){
		$db = self::InitDB();
		$table=array("t_user_teacher_activity_tmp");
		return $db->insert($table,$data);
	}
	public static function getteacherActivityOneOfInfo($condition){
        $db = self::InitDB('db_user', 'query');
        $table = array('t_user_teacher_activity_tmp');
        $item=array(
                'pk_tid'=>'pk_tid',
                'fk_user'=>'fk_user',
                'name'=>'name',
                'mobile'=>'mobile',
                'create_time'=>'create_time',
            );
        return $db->selectOne($table, $condition,$item);
    }
}

