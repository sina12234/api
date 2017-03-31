<?php
class stat_statphrase{
    public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
    }

    public function pageGetClassPhraseStat($inPath){
        $params = SJson::decode(utility_net::getPostData());
        if(empty($params->pid)){
            return $this->setResult('',-1,'params is error');
        }
        $res = stat_db::getClassPhraseStat($params->pid);
        if(!empty($res->items)){
            return $this->setResult($res->items);
        }else{
            return $this->setResult('',-2,'get data is failed');
        }
    }
}
?>