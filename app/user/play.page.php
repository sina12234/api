<?php
class user_play{
    /**
     * 校验提交的服务器权限，仅在配置文件里的才可以提交
     * */
    public function __construct($inPath){
    }

     public function pageGetUserInfo(){
        $userID = SJson::decode(utility_net::getPostData(),true);
        $user = $userID['pk_user'];
        $res = user_db_userDao::row($user);
        return $res;
    }


}
