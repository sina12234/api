<?php
class stat_statplan{
    public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
    }

    public function pageGetPlanStatByPidStr($inPath){
        $pidArr = SJson::decode(utility_net::getPostData(),true);
        if(empty($pidArr)&&empty($pidArr["pidStr"])){
            return $this->setResult('',-1,'params is error');
        }
        $pidStr = $pidArr["pidStr"];
        $res = stat_db::listPlanStatById($pidStr);
        if(!empty($res->items)){
            return $this->setResult($res->items);
        }else{
            return $this->setResult('',-2,'get data is failed');
        }
    }
}
?>