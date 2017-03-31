<?php

class user_db_orgMemberPriorityDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_member_priority';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
    public static function getMemberPriority($setId,$type,$page=1,$length=-1){
		$db = self::InitDB(self::dbName,'query');
		$condition = "fk_member_set = $setId AND type = $type AND status <> -1";
		$db->setPage($page);
		$db->setLimit($length);
        return $db->select(self::TABLE,$condition);	
	}
	
	public static function getMemberPriorityByObjectId($objectId,$type){
		$db = self::InitDB(self::dbName,'query');
		
		$condition = "type = $type AND status <> -1";
		if(is_array($objectId)){
			$objectIds = implode(',', $objectId);
			$condition .= " AND object_id IN({$objectIds}) ";
		}else{
			$condition .= " AND object_id = $objectId ";
		}
		
        return $db->select(self::TABLE,$condition);	
	}
	
	public static function add($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        return $res;
    }

	public static function update($setId,$data,$objectIdArr=''){
        $db = self::InitDB(self::dbName);
		if(!empty($objectIdArr)){
			$objectIdStr = implode(',',$objectIdArr);
			$condition = "fk_member_set = $setId AND object_id IN ($objectIdStr)";	
		}else{
			$condition = "fk_member_set = $setId";	
		}
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	
	public static function del($setId,$type = '',$objectIdArr=''){
        $db = self::InitDB(self::dbName);
		if(!empty($objectIdArr) && !empty($type)){
			$objectIdStr = implode(',',$objectIdArr);
			$condition = "fk_member_set = $setId AND type = $type AND object_id IN ($objectIdStr)";
		}else{
			$condition = "fk_member_set = $setId";
		}
        $res = $db->delete(self::TABLE, $condition);
        return $res;
    }
	
	public static function delByObjectId($objectId,$type,$setIdArr=''){
        $db = self::InitDB(self::dbName);
		if(!empty($setIdArr)){
			$setIdStr = implode(',',$setIdArr);
			$condition = "object_id = $objectId AND type = $type AND fk_member_set IN ($setIdStr)";
		}else{
			$condition = "object_id = $objectId AND type = $type";
		}
        $res = $db->delete(self::TABLE, $condition);
        return $res;
    }
	//用于中间层
	public static function getMemberPriorityByObjectIds($objectIds,$type){
		$db = self::InitDB(self::dbName,'query');
		$left = new stdClass;
		$left->t_organization_member_set = "t_organization_member_priority.fk_member_set = t_organization_member_set.pk_member_set";
		$items = array('t_organization_member_priority.fk_member_set','t_organization_member_priority.status',
					't_organization_member_set.title','t_organization_member_priority.object_id','t_organization_member_priority.type');
		$condition = "t_organization_member_priority.object_id IN ($objectIds) AND 
					  t_organization_member_priority.type = $type  AND 
					  t_organization_member_priority.status = 1";
		$orderBy = array('t_organization_member_priority.create_time'=>'asc');
        return $db->select(self::TABLE,$condition,$items,'',$orderBy,$left);	
	}
	public static function getMemberPriorityBySetIds($setIds,$type){
		$db = self::InitDB(self::dbName,'query');
		$left = new stdClass;
		$left->t_organization_member_set = "t_organization_member_priority.fk_member_set = t_organization_member_set.pk_member_set";
		$items = array('t_organization_member_priority.fk_member_set','t_organization_member_priority.status',
					't_organization_member_set.title','t_organization_member_priority.object_id','t_organization_member_priority.type');
		$condition = "t_organization_member_priority.fk_member_set IN ($setIds) AND 
					  t_organization_member_priority.type = $type  AND 
					  t_organization_member_priority.status <> -1";
		$orderBy = array('t_organization_member_priority.create_time'=>'asc');
        return $db->select(self::TABLE,$condition,$items,'',$orderBy,$left);	
	}
	
	public static function getMemberPriorityCountBySetIds($setIds,$type){
		$db = self::InitDB(self::dbName,'query');
		$left = new stdClass;
		$items = array('fk_member_set','count(object_id) as course_count');
		$condition = "fk_member_set IN ($setIds) AND type = $type AND status <> -1";
		$groupBy = array('fk_member_set');
        return $db->select(self::TABLE,$condition,$items,$groupBy);	
	}
	
}
