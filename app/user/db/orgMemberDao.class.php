<?php

class user_db_orgMemberDao{
    const dbName = 'db_user';
    const TABLE = 't_organization_member';
    public static function InitDB($dbName = self::dbName, $dbType = "main"){
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);
        return $db;
    }
	
    public static function getUserCountByMemberSetArr($msetIdArr,$endTime=''){
		$db = self::InitDB(self::dbName,'query');
		$msetIdStr = implode(',',$msetIdArr);
		$condition = "fk_member_set IN ($msetIdStr) AND member_status <> -1";
		if(!empty($endTime)){
			$condition .= " AND end_time > '$endTime'";
		}
		$items = array('count(fk_user) as user_count','fk_member_set');
		$groupBy = array('fk_member_set');
        return $db->select(self::TABLE,$condition,$items,$groupBy);	
	}
	public static function checkMemberByUidAndSetId($userId,$setId){
		$db = self::InitDB(self::dbName,'query');
		$condition = "fk_member_set = $setId AND fk_user = $userId AND member_status <> -1";
        return $db->selectOne(self::TABLE,$condition);	
	}
	public static function update($memberId, $data){
        $db = self::InitDB(self::dbName);
        $condition = "pk_member = $memberId";
        $res = $db->update(self::TABLE, $condition, $data);
        if ($res === false) {
            SLog::fatal(
                'db error[%s],params[%s]',
                var_export($db->error(), 1),
                var_export(
                    ['memberId' => $memberId, $data],
                    1
                )
            );
        }
        return $res;
    }
	public static function updateBySetId($setId, $data){
        $db = self::InitDB(self::dbName);
        $condition = "fk_member_set = $setId";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	public static function delByUidArr($setId,$uidArr,$data){
        $db = self::InitDB(self::dbName);
		$uidStr = implode(',',$uidArr);
        $condition = "fk_member_set = $setId AND fk_user IN ($uidStr)";
        $res = $db->update(self::TABLE, $condition, $data);
        return $res;
    }
	public static function add($data){
        $db = self::InitDB(self::dbName);
        $res = $db->insert(self::TABLE,$data);
        if ($res === false) {
            SLog::fatal(
                'db error[%s],params[%s]',
                var_export($db->error(), 1),
                var_export($data, 1)
            );
        }
        return $res;
    }
	public static function getMemberListByMemberSetId($page,$length,$setId,$memberStatus = '',$orgId='',$mobile='',$userId=''){
		$db = self::InitDB(self::dbName,'query');
		$left = new stdClass;
		$left->t_user = "t_user.pk_user = t_organization_member.fk_user";
		$left->t_organization_member_set = "t_organization_member.fk_member_set = t_organization_member_set.pk_member_set";
		$items = array('t_user.real_name','t_user.name','t_user.mobile','t_organization_member.end_time','t_organization_member_set.title',
					't_organization_member.member_status','t_organization_member.status','t_organization_member.fk_member_set','t_organization_member.last_type','t_organization_member_set.fk_org');
		$condition = 't_organization_member.member_status <> -1';
		if(!empty($userId)){
			$condition .= " AND t_organization_member.fk_user = $userId";
		}
		if(!empty($setId)){
			$condition .= " AND t_organization_member.fk_member_set = $setId";
		}
		if(!empty($orgId)){
			$condition .= " AND t_organization_member_set.fk_org = $orgId";
		}
		if(!empty($mobile)){
            if(preg_match("/^1[34578]\d{9}$/",$mobile)){
                $condition.= " AND t_user.`mobile`='".$mobile."'";
            }else{
                $condition.= " AND (t_user.`name` Like '%".$mobile."%' OR t_user.`real_name` Like '%".$mobile."%')";
			}
        } 
		$now = date('Y-m-d H:i:s',time());
		if(is_numeric($memberStatus) && $memberStatus == 0 ){
			$condition .= " AND t_organization_member.end_time <= '$now'";
		}elseif(is_numeric($memberStatus) && $memberStatus == 1){
			$condition .= " AND t_organization_member.end_time > '$now' AND t_organization_member_set.status > 0";
		}
		if($page && $length){
			$db->setPage($page);
			$db->setLimit($length);
		}
		$orderBy = array('t_organization_member.begin_time'=>'desc');
        return $db->select(self::TABLE,$condition,$items,'',$orderBy,$left);		
	}
	public static function getValidMemberListByUid($uid){
        $db = self::InitDB(self::dbName, 'query');
		$currTime = date('Y-m-d H:i:s',time());
        $condition = "fk_user={$uid} AND member_status <> -1 AND end_time > '$currTime' AND status = 1";
        return $db->select(self::TABLE, $condition);
    }
	
    public static function getMemberByUidAndSetIdArr($uid, $setIdArr)
    {
        if (count($setIdArr) < 1) return false;
        $setIdStr = implode(',', $setIdArr);

        $db        = self::InitDB(self::dbName, 'query');
        $condition = "fk_user={$uid} and fk_member_set IN ($setIdStr) AND member_status <> -1";
		$orderBy = array('end_time' => 'desc');
        return $db->select(self::TABLE, $condition,'','',$orderBy);
    }

    public static function getMemberBySetIdArr($setIdArr)
    {
        if (count($setIdArr) < 1) return false;
        $setIdStr = implode(',', $setIdArr);

        $db        = self::InitDB(self::dbName, 'query');
        $condition = "fk_member_set IN ($setIdStr) AND member_status <> -1";

        return $db->select(self::TABLE, $condition);
    }
	public static function checkIsMemberByUidArrAndSetId($uidArr,$setId){
		$db = self::InitDB(self::dbName,'query');
		$uidStr = implode(',', $uidArr);
		$condition = "fk_member_set = $setId AND fk_user IN ($uidStr) AND member_status <> -1";
        return $db->select(self::TABLE,$condition);	
	}
	
	public static function checkUserMemberCourse($userId,$courseId,$type){
        $db = self::InitDB(self::dbName, 'query');
		$currTime = date('Y-m-d H:i:s',time());
		$left = new stdClass;
		$left->t_organization_member_priority = "t_organization_member_priority.fk_member_set = t_organization_member.fk_member_set";
        $condition = "t_organization_member.fk_user={$userId} 
					AND t_organization_member.member_status <> -1  
					AND t_organization_member.end_time > '$currTime'  
					AND t_organization_member.status = 1 
					AND t_organization_member_priority.object_id = {$courseId} 
					AND t_organization_member_priority.type = $type";
		$items = array('t_organization_member.fk_user','t_organization_member.fk_member_set','t_organization_member.begin_time',
					't_organization_member.end_time','t_organization_member_priority.object_id');
		$orderBy = array('end_time'=>'desc');
        return $db->selectOne(self::TABLE, $condition,$items,'',$orderBy,$left);
    }

}
