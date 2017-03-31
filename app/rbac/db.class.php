<?php
/**
 * @author Zhang Taifeng
 */
class rbac_db{
    var $_db;
    var $_config;
    public function __construct($dbname="db_mgr"){
        $this->_db = new SDb;
        $this->_db->useConfig($dbname,"main");
    }
    public function memberList($page=1,$limit=10){
        $table = array("t_mgr_user");
        $items=array(
                't_mgr_user.*',
                't_mgr_role.role_name',
            );
        $left=array(
                't_mgr_role'=>'t_mgr_role.pk_role_id=t_mgr_user.fk_role_id'
            );
        $this->_db->setLimit($limit);
        $this->_db->setPage($page);
        $this->_db->setCount(true); 
        return $this->_db->select($table,"",$items,"",array('pk_mgr_user'=>'asc'),$left);
    }
    public function getNodeList(){
//define('DEBUG',true);
        $table = array("t_mgr_node");
        return $this->_db->select($table,"","","",array('node_sort'=>'asc'));
    }
    public function getRoleNodeList($admin=''){
//define('DEBUG',true);
        $table = array("t_mgr_node");
        $condition=array(
                't_mgr_user.name'=>$admin,
                't_mgr_node.node_status'=>0
            );
        $leftjoin=array(
                't_mgr_mapping_role_node'=>'t_mgr_mapping_role_node.fk_node_id=t_mgr_node.pk_node_id',
                't_mgr_user'=>'t_mgr_user.fk_role_id=t_mgr_mapping_role_node.fk_role_id'
            );
        return $this->_db->select($table,$condition,"","",array('node_sort'=>'asc'),$leftjoin);
        //return $this->_db->select($table,"","","",array('node_sort'=>'desc'));
    }
    public function nodeList($page=1,$limit=10){
        $table = array("t_mgr_node");
        $this->_db->setLimit($limit);
        $this->_db->setPage($page);
        $this->_db->setCount(true); 
        return $this->_db->select($table,"","","",array('node_sort'=>'asc'),"");
    }
    public function roleList($page=1,$limit=10){
        $table = array("t_mgr_role");
        $this->_db->setLimit($limit);
        $this->_db->setPage($page);
        $this->_db->setCount(true); 
        return $this->_db->select($table,"","","",array('role_sort'=>'asc'),"");
    }
    public function allRole(){
        $table = array("t_mgr_role");
        $items=array(
                'role_id'=>'pk_role_id',
                'role_name'=>'role_name',
            );
        return $this->_db->select($table,"",$items,"",array('role_sort'=>'desc'),"");
    }
    public function getRole($role_id){
        $table=array('t_mgr_role');
        $condition=array(
                'pk_role_id'=>$role_id
            );
        return $this->_db->selectOne($table,$condition,"*");
    }
    public function getMemberByRid($role_id,$page=1,$limit=10){
        $table=array('t_mgr_user');
        $condition=array(
                'fk_role_id'=>$role_id
            );
        $this->_db->setLimit($limit);
        $this->_db->setPage($page);
        $this->_db->setCount(true); 
        return $this->_db->select($table,$condition,"*");
    }
    public function getAccessByRid($role_id){
        $table=array('t_mgr_mapping_role_node');
        $condition=array('fk_role_id'=>$role_id);
        $item=array('node_id'=>'fk_node_id');
        return $this->_db->select($table,$condition,$item,"","","");
    }
	public function getAccessListByUid($user_id){
        $table=array('t_mgr_user');
        $condition=array('pk_mgr_user'=>$user_id,'t_mgr_node.node_status'=>0);
		$leftjoin=array(
                't_mgr_mapping_role_node'=>'t_mgr_mapping_role_node.fk_role_id=t_mgr_user.fk_role_id',
                't_mgr_node'=>'t_mgr_mapping_role_node.fk_node_id=t_mgr_node.pk_node_id',
            );
        $item=array('t_mgr_user.pk_mgr_user','t_mgr_user.fk_role_id','t_mgr_node.pk_node_id',
					't_mgr_node.node_url','t_mgr_node.node_title','t_mgr_node.node_pid','t_mgr_node.node_level');
        return $this->_db->select($table,$condition,$item,"","",$leftjoin);
    }
    public function authorize($data){
        $values=implode(',',$data);
        $sql="insert into t_mgr_mapping_role_node (fk_role_id,fk_node_id) values ".$values;
        return $this->_db->execute($sql);
    }
    public function authorizeDel($role_id){
        $table=array('t_mgr_mapping_role_node');
        $condition=array('fk_role_id'=>$role_id);
        return $this->_db->delete($table,$condition);
    }
    public function addRole($data){
        $table=array("t_mgr_role");
        return $this->_db->insert($table,$data);
    }  
    public function updateRole($rid,$data){
        $table=array("t_mgr_role");
        $condition=array("pk_role_id"=>$rid);
        return $this->_db->update($table,$condition,$data);
    }  
    public function delRole($rid){
        $table=array('t_mgr_role');
        $condition=array('pk_role_id'=>$rid);
        return $this->_db->delete($table,$condition);
    }
    public function getRoleByName($name,$page=1,$limit=10){
//define('DEBUG',true);
        $table = array("t_mgr_role");
        $condition=array('role_name like \'%'.$name.'%\'');
        $this->_db->setLimit($limit);
        $this->_db->setPage($page);
        $this->_db->setCount(true); 
        return $this->_db->select($table,$condition,"","","","");
    }
    public function addMember($data){
        $table=array("t_mgr_user");
        return $this->_db->insert($table,$data);
    }  
    public function getMember($uid){
        $table=array('t_mgr_user');
        $condition=array(
                'pk_mgr_user'=>$uid
            );
        return $this->_db->selectOne($table,$condition,"*");
    }
    public function updateMember($uid,$data){
        $table=array("t_mgr_user");
        $condition=array("pk_mgr_user"=>$uid);
        return $this->_db->update($table,$condition,$data);
    }  
    public function delMember($mid){
        $table=array('t_mgr_user');
        $condition=array('pk_mgr_user'=>$mid);
        return $this->_db->delete($table,$condition);
    }
    public function searchMember($params,$page=1,$limit=10){
        $table = array("t_mgr_user");
        $condition=array(
                'name like \'%'.$params->name.'%\'',
                'status='.$params->status,
            );
        $items=array(
                't_mgr_user.*',
                't_mgr_role.role_name'
            );
        $left=array(
                't_mgr_role'=>'t_mgr_role.pk_role_id=t_mgr_user.fk_role_id'
            );
        $this->_db->setLimit($limit);
        $this->_db->setPage($page);
        $this->_db->setCount(true); 
        return $this->_db->select($table,$condition,$items,"","",$left);
    }
	public function getMemberByName($name){
        $table = array("t_mgr_user");
        $condition=array(
                'name like \'%'.$name.'%\'',
            );
        return $this->_db->selectOne($table,$condition);
    }
    public function addNode($data){
        $table=array("t_mgr_node");
        return $this->_db->insert($table,$data);
    }  
    public function getNode($nid){
        $table=array('t_mgr_node');
        $condition=array(
                'pk_node_id'=>$nid
            );
        return $this->_db->selectOne($table,$condition,"*");
    }
	public function getNodeByUrl($nodeUrl){
        $table=array('t_mgr_node');
        $condition=array(
                'node_url'=>"$nodeUrl"
            );
        return $this->_db->selectOne($table,$condition);
    }
    public function updateNode($nid,$data){
        $table=array("t_mgr_node");
        $condition=array("pk_node_id"=>$nid);
        return $this->_db->update($table,$condition,$data);
    }  
    public function changeNodeStatus($nid,$status){
        $table=array('t_mgr_node');
        $condition=array(
                "pk_node_id=".$nid." or node_pid=".$nid,
            );
        $data=array(
                'node_status'=>$status
            );
        return $this->_db->update($table,$condition,$data);
    }
    
}
?>
