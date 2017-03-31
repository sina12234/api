<?php

class stat_userorg
{
    //todo wait optimize
    public $ret;

    public function setResult($data='', $code=1, $msg='success')
    {
        $this->ret['result'] = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data
         );

         return $this->ret;
    }


    public function pageGetUserOrgData(){
        $orgList = user_db::getOrgInfo();
        $uidArr = $list = $result = [];
        if ($orgList) {
            foreach($orgList->items as $k => $v){
                $uidArr[] = (int)$v['fk_user_owner'];
                $list[] = $v;
            }
        }
        $tNew = new stat_userorgstat();
        $userInfo = $tNew->getStatUserOrginfoByUidArr($uidArr);

        if ($userInfo) {
            foreach($userInfo->items as $k => $v){
                // filter does not exist data
                $offset = array_keys($uidArr, $v['fk_user']);
                $result[] = array_merge($list[$offset[0]], $v);
            }
        }
        if (!$result) {
            return $this->setResult('', -4, 'get data failed');
        }
        return $this->setResult($result);
    }

    public function pageSearchOrgData(){
        $uidArr = $list = $result = array();
        $tNew = new stat_userorgstat();
        $params = SJson::decode(utility_net::getPostData());
        if (!$params) {
             return $this->setResult('', -2, 'params error');
        }
        if ($params->oid) {
            $orgInfo = user_db::orgSearchByNameOrId($params->oid);
        }
        if ($params->keyword) {
            $orgInfo = user_db::orgSearchByNameOrId($params->keyword);
        }
        if ($orgInfo) {
            foreach($orgInfo->items as $k => $v){
                $uidArr[] = (int)$v['fk_user_owner'];
                $list[] = $v;
            }
        }
        $userInfo = $tNew->getStatUserOrginfoByUidArr($uidArr);
        if ($userInfo) {
            foreach($userInfo->items as $k => $v){
                // filter does not exist data
                $offset = array_keys($uidArr, $v['fk_user']);
                $result[] = array_merge($list[$offset[0]], $v);
            }
        }
        if (!$result) {
            return $this->setResult('', -4, 'get data failed');
        }
        return $this->setResult($result);
    }
}
