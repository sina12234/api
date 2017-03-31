<?php
class utility_region{
    public function pagegetAreaList(){
        $params = SJson::decode(utility_net::getPostData(),true);
        $utility_api = new utility_api();
        $ret =$utility_api->getAreaList($params);
        return $ret;
    }

    public function pageGetShortNameByIdArr(){
        $params = SJson::decode(utility_net::getPostData(),true);
        if(empty($params["idArr"])){
            return false;
        }
        $ret =utility_db::getShortNameByIdArr($params["idArr"]);
        return $ret;
    }
}
