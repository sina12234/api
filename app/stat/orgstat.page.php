<?php
class stat_orgstat{
    public function setResult($data='', $code=0,$msg='success'){
        return array(
            'code'    => $code,
            'message' => $msg,
            'data'    => $data
        );
    }

    public function pageGetOrgStat($inPath){
        $params = SJson::decode(utility_net::getPostData(),true);
        if(empty($params)||empty($params["condition"])){
            return $this->setResult('',-1,'params is error');
        }
        $data = array(
            "item"=>!empty($params["item"])?$params["item"]:'*',
            "condition"=>$params["condition"],
        );
        if(!empty($params["order"])){
            $data["order"] = $params["order"];
        }
        if(!empty($params["group"])){
            $data["group"] = $params["group"];
        }
        if(!empty($params["page"])){
            $data["page"] = $params["page"];
        }
        if(!empty($params["length"])){
            $data["length"] = $params["length"];
        }
        $res = stat_db_dayOrgDao::getOrgStat($data);
        if(!empty($res->items)){
            if(isset($data["page"])&&isset($data["length"])){
                return $this->setResult($res);
            }
            return $this->setResult($res->items);
        }else{
            return $this->setResult('',-2,'get data is failed');
        }
    }
}
?>