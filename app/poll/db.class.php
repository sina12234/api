<?php

class poll_db
{
    public static function InitDB($dbName = "db_utility", $dbType = "main")
    {
        redis_api::useConfig($dbName);
        $db = new SDb();
        $db->useConfig($dbName, $dbType);

        return $db;
    }
	
	public static function voteList($page,$length,$data)
    {
        $db = self::InitDB("db_utility", 'query');
        $condition = '';
        $item = ['pk_vote','name','start_time','end_time','user_count','status','fk_user_owner'];
		
		if(!empty($data['sType']) && $data['sType']=='ing'){
			$condition .= " now()>=start_time and now()<end_time and ";
		}
		if(!empty($data['sType']) && $data['sType']=='end'){
			$condition .= " now()>end_time and ";
		}
		if(!empty($data['name'])){
			$condition .= " name like '%".$data['name']."%' and ";
		}
		if(!empty($data['ownerId'])){
			$condition .= " fk_user_owner={$data['ownerId']} and ";
		}
	
		$condition .= " status > 0";
		$orderby['pk_vote']  = $data['orderby'];
		$db->setPage($page);
        $db->setLimit($length);
        $res = $db->select("t_vote", $condition, $item, "", $orderby);
		
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	
	public static function voteRow($voteId)
    {
        $db = self::InitDB("db_utility", 'query');
        $condition = "pk_vote={$voteId}";

        $res = $db->selectOne('t_vote', $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
    }
	
	public static function voteAdd($data)
    {
        $db = self::InitDB("db_utility");

        return $db->insert("t_vote", $data);
    }
	
	public static function voteUpdate($pKey, $data)
    {
        $db = self::InitDB("db_utility");
        $condition = "pk_vote = {$pKey}";
        $res = $db->update("t_vote", $condition, $data);
		return $res;
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
		
        return $res;
    }
	
	public static function optionList($voteId)
	{
		$db = self::InitDB("db_utility", 'query');
		
		$item = ['pk_option','fk_vote','object_type','object_id','name_display','thumb_display','order_no','total_count'];
		
		$condition = '';
		$condition .= "status=1";
		if(!empty($voteId)){
			$condition .= " and fk_vote={$voteId}";
		}
		
        $res = $db->select('t_vote_option', $condition, $item);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
	}
	
	public static function optionRow($optionId){
		$db = self::InitDB("db_utility", 'query');
		
		$item = ['pk_option','object_type','object_id','name_display','thumb_display','order_no','total_count'];
		
		$condition = '';
		$condition .= "status=1";
		if(!empty($optionId)){
			$condition .= " and pk_option={$optionId}";
		}
		
        $res = $db->selectOne('t_vote_option', $condition, $item);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
	}
	
	public static function optionAdd($data)
    {
        $db = self::InitDB("db_utility");

        return $db->insert("t_vote_option", $data);
    }
	
	public static function optionEdit($optionId,$data){
		$db = self::InitDB("db_utility");
        $condition = "pk_option = {$optionId}";

        $res = $db->update("t_vote_option", $condition, $data);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }
		
        return $res;
	}
	
	
	public static function userLogList($data,$page,$length)
	{
		$db = self::InitDB("db_utility", 'query');
        $condition = '';
		$condition .= 'status=1';
		if(!empty($data['voteId'])){
			$condition .= " and fk_vote={$data['voteId']} ";
		}
		if(!empty($data['userId'])){
			$condition .= " and fk_user={$data['userId']} ";
		}
		
		$db->setPage($page);
        $db->setLimit($length);
		$orderby["pk_log"] = "desc";
        $res = $db->select("t_vote_log", $condition, "", "", $orderby);
        if ($res === FALSE) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
	}
	
	public static function userLogAdd($data)
	{
		$db = self::InitDB("db_utility");

        return $db->insert("t_vote_log", $data);
	}
	
	public static function userLogRow($voteId='', $userId='', $optionId=''){
		$db = self::InitDB("db_utility", 'query');
		
		$condition = '';
		if($voteId != ''){
			$condition .= "pk_vote={$voteId}";
		}
        
		if($userId != '' && $optionId != ''){
			$condition .= " fk_user={$userId} and fk_option={$optionId}";
		}
		
        $res = $db->selectOne('t_vote_log', $condition);
        if ($res === false) {
            SLog::fatal('db error[%s]', var_export($db->error(), 1));
        }

        return $res;
	}

	public static function msgTaskAdd($data)
    {
		$db = self::InitDB("db_utility","query");
        
		$key = md5("setAdd.db_utility.t_vote");
		
		return redis_api::sAdd($key,$data);
    }
}
?>
