<?php
class message_replyTaskMessage{

    public function __construct($inPath){
    }


    //批改作业消息发送
    public function pagerepyTaskMessage(){
        $params=SJson::decode(utility_net::getPostData(),true);
        $ret = new stdclass;
        $ret->code=-1;
        $ret->msg="add is faild";

       // $user = isset($inPath[3])?$inPath[3]:0;
//        if(empty($params->msgtype) || empty($user)){
//            $ret->msg = "params is error";
//            return $ret;
//        }
        $dbRet = message_api::add($params);
        if($dbRet){
            $ret->code = 0;
            $ret->data = $dbRet;
        }
        return $ret;
    }

}