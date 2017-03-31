<?php
/*
 * 友盟消息推送-消息添加
 * author jay
 * date 2016-08-02
 */

class ymeng_message {

    /**
     * 消息添加
     * @param type $inPath
     * @return \stdclass
     */
    public function pageAddMessage($inPath) {
        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        
        $params = SJson::decode(utility_net::getPostData());//print_r($params);die;
        if(empty($params->to_uid)){
            $ret->result->msg="lack to_uid";
            return $ret;
        }
        if(empty($params->organization)){
            $ret->result->msg="lack organization";
            return $ret;
        }
        $orgList=  (array)ymeng_db::getYmengConfig();//print_r($orgList);die;
        if(empty($orgList)||!in_array($params->organization,array_keys($orgList))){
            $ret->result->msg="illegal organization";
            return $ret;
        }
        $payload=$params->content;//print_r($payload);die;
        if(empty($payload)){
            $ret->result->msg="lack content";
            return $ret;
        }
        if(!in_array($payload->display_type,array('notification','message'))){
            $ret->result->msg="illegal display_type";
            return $ret;
        }
        if(empty($payload->body->ticker)){
            $ret->result->msg="lack ticker";
            return $ret;
        }
        if(empty($payload->body->title)){
            $ret->result->msg="lack title";
            return $ret;
        }
        if(empty($payload->body->text)){
            $ret->result->msg="lack text";
            return $ret;
        }
        if(!in_array($payload->body->after_open,array('go_app','go_url','go_activity','go_custom'))){
            $ret->result->msg="illegal after_open";
            return $ret;
        }
        if(($payload->body->after_open=='go_activity') && empty($payload->body->activity)){
            $ret->result->msg="lack go_activity";
            return $ret;
        }
        
        $data=new stdClass();
        $data->fk_user_to=$params->to_uid;
        $data->fk_organization=$params->organization;//0云课，其他参照机构id
        $data->type='customizedcast';
        $data->alias_type='xiaovo_push_alias_key';//客户端设定的key
        $data->payload=json_encode($payload);
        $payload_ios=array(
                "aps"=>array(
                    "alert"=>$payload->body->title,
                    //"content-available"=>$payload->body->text
                 )
        );
        $data->payload_ios=json_encode($payload_ios);
        $data->message_type=$params->message_type?$params->message_type:10000;
        
        $ymeng_db=  new ymeng_db();
        //print_r($data);die;
        $rs=$ymeng_db->addMessage($data);
        if($rs){
            $ret->result->code=0;
            $ret->result->msg='success';
        }else{
            $ret->result->msg='insert data fail';
        }
        return $ret;
    }    

}
