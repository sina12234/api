<?php

class utility_db
{
    public static function InitDB($dbname = "db_utility", $dbtype = "main")
    {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);

        return $db;
    }

    public function getSchoolList($condition, $item = '', $orderBy = '', $page = 1, $length = 100)
    {
        $table = array("t_region_school");
        $db    = self::InitDB('db_utility', 'query');

        if ($page && $length) {
            $db->setPage($page);
            $db->setLimit($length);
            $db->setCount(true);
        }

        return $db->select($table, $condition, $item, '', $orderBy);
    }

    public static function getschool($page = null,$length =null){
        $db = self::InitDB("db_utility","query");
        $table=array("t_region_school");

        if($page){$db->setPage($page);}
        if($length){$db->setLimit($length);}
        $db->setCount(true);
        $condition = '';
        $v = $db->select($table,$condition);
        return $v;
    }

    public static function getSchoolById($sid){
        $table=array("t_region_school");
        $db = self::InitDB("db_utility","query");

        $item = '';
        $condition = 'pk_school='.$sid;
        return $db->selectone($table,$condition,$item,"","");
    }

    public function updateSchoolInfo($sid,$data){
        $table=array("t_region_school");
        $db = self::initdb();
        return $db->update($table,array("pk_school"=>$sid),$data);
    }

    public static function getsearchShow($params){
        $table=array("t_region_school");
        $db = self::InitDB("db_utility","query");
        if(!empty($params['page']) && !empty($params['num'])){
            $db->setPage($params['page']);
            $db->setLimit($params['num']);
        }

        $condition = '1=1 ';
        if(!empty($params['school_type'])){
            $condition.= " and `school_type`='".$params['school_type']."'";
        }
        if(!empty($params['school_name'])){
            $condition.= " and `school_name` LIKE '%".$params['school_name']."%'";
        }
        if(!empty($params['region'])){
            $condition.= " and `fk_region`='".$params['region']."'";
        }
        return $db->select($table,$condition);
    }

    public static function getAreaList($params){
        $table=array("t_region");
        $db = self::InitDB("db_utility","query");
        $condition = '1=1 ';
        if($params['type']==1){
            $condition.= " and `parent_fk_region`=0";
        }
        if($params['type']==2 && isset($params['regionId'])){
            $condition.= " and `parent_fk_region`=".$params['regionId'];
        }
        return $db->select($table,$condition);
    }

    public static function getNormalSchoolNameByInfo($data){
        $table=array("t_region_school");
        $db = self::InitDB("db_utility","query");
        $db->setPage(1);
        $db->setLimit(10);
        $condition = "school_name like '%".$data."%'";
        return $db->select($table,$condition);
    }
    
    //增加客服
    public static function addOrgCustomerInfo($params){
	$db = self::InitDB();
	$table=array("t_custom_service");
	return $db->insert($table,$params);
    }
    
    //编辑客服
    public static function updateOrgCustomerInfo($pid,$orgid,$params){
        $db = self::InitDB();
	$table=array("t_custom_service");
	return $db->update($table, array('pk_customer' => $pid,'fk_org'=>$orgid), $params);
    }
    
    //删除客服
    public static function delOrgCustomerInfo($pid,$orgid){
        $db = self::InitDB();
        //开始事务
        $db->execute("BEGIN");
        
        $relationSql = "DELETE  FROM t_custom_service_relation WHERE fk_customer=".$pid;
        $customSql = "DELETE  FROM t_custom_service WHERE pk_customer=".$pid." AND fk_org=".$orgid;

        $relationRes = $db->execute($relationSql);//print_r($relationRes);
        $customRes = $db->execute($customSql);//print_r($customRes);
        if ($relationRes>=0 && $customRes) {//=0代表未绑定关系时情况
            //提交成功
            $db->execute("COMMIT");
            return true;
        }
        /*SLog::fatal('db error[%s]', var_export(
                        [
            'qqrelationSql' => $relationSql,
            'qqcustomSql' => $customSql
                        ], 1
        ));*/
        //事务回滚
        $db->execute("ROLLBACK");
        return false;
    }

    //客服列表
    public static function customerServicesList($data,$cache=0){
	$table=array("t_custom_service");
        $db = self::InitDB("db_utility","query");
        $condition="1=1 ";
        if(!empty($data['fk_org'])){
            $condition .=" AND fk_org=".$data['fk_org'];
        }
        $condition .=" AND status=1";
        if(!empty($data['type'])){
            $condition .=" AND type=".$data['type'];
        }
        if(!empty($data['pids'])){
            $condition .=" AND pk_customer IN(".implode(',',$data['pids']).")";
        }
        if(!empty($data['type_name'])){
            $condition .=" AND type_name LIKE '%".$data['type_name']."%'";
        }
        if(!empty($data['page'])&&!empty($data['pageSize'])){
                    $db->setPage($data['page']);
                    $db->setLimit($data['pageSize']);
                    $db->setCount(true);
        }//print_r($condition);
	return $db->select($table,$condition,'', '', 'last_updated desc','');
    }
    
    //客服详情
    public static function getOrgCustomerInfo($condition){
	$table=array("t_custom_service");
        $db = self::InitDB("db_utility","query");
	return $db->selectone($table,$condition,'*');
    }
    
    //添加客服绑定关系
    public static function addCsRelation($data){
        $db = self::InitDB();
        $fk_customer=$data['fk_customer'];
        $sql="INSERT INTO t_custom_service_relation(`fk_customer`,`fk_org`,`fk_course`,`create_time`,`type`) VALUES";
        for($i=0;$i<count($fk_customer);$i++){
            $sql.="({$fk_customer[$i]},{$data['fk_org']},{$data['fk_course']},'{$data['create_time']}',{$data['type']}),";
        }
        $sql=  rtrim($sql,",");
        //file_put_contents("/tmp/jay", $sql);
        $rs=$db->execute($sql);
        if($rs){
            return true;
        }else{
            return false;
        }
    }

    //删除客服绑定关系
    public static function delCsRelation($pid){
        $db = self::InitDB();
	$table=array("t_custom_service_relation");
	return $db->delete($table,array("pk_relation"=>$pid));
    }
    
    //客服绑定关系列表
    public static function csRelationList($params){
        $table=array("t_custom_service_relation");
        $db = self::InitDB("db_utility","query");
        //$condition = '1=1 ';
        if(!empty($params['fk_org'])){
            //$condition .=" AND fk_org=".$params['fk_org'];
            $condition['fk_org']=$params['fk_org'];
        }
        if(!empty($params['fk_course'])){
            //$condition .=" AND fk_course=".$params['fk_course'];
            $condition['fk_course']=$params['fk_course'];
        }
        if(!empty($params['type'])){
            $condition['type']=$params['type'];
        }
	return $db->select($table,$condition,'', '', 'last_updated desc','');
    }
    
    //发布平台公告
    public static function addNotice($params){
	$db = self::InitDB();
	$table=array("t_notice");
	return $db->insert($table,$params);
    }
    
    //编辑公告
    public static function updatNotice($pid,$params){
        $db = self::InitDB();
	$table=array("t_notice");
	return $db->update($table, array('pk_notice_id' => $pid), $params);
    }
    
    //公告列表
    public static function noticeList($page,$pageNum,$status){
        $db = self::InitDB("db_utility","query");
        $table=array("t_notice");
        if(!empty($page)&&!empty($pageNum)){
            $db->setPage($page);
            $db->setLimit($pageNum);
            $db->setCount(true);
        }
        $condition = "status IN (".$status.")";
        return $db->select($table,$condition,'', '', 'create_time desc','');
    }
    
    //公告详情
    public static function noticeInfo($pid){
        $db = self::InitDB("db_utility","query");
        $table=array("t_notice");
        $condition = 'pk_notice_id='.$pid;
        return $db->selectone($table,$condition,'*');
    }

    //获取城市省份区域名称
    public static function getShortNameByIdArr($idArr){
        $db = self::InitDB("db_utility","query");
        $table=array("t_region");
        if(is_array($idArr)){
            $condition = "pk_region IN (".implode(',',$idArr).")";
        }else{
            $condition = "pk_region=$idArr";
        }
        return $db->select($table,$condition);
    }

}