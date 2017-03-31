<?php
class stat_statpromote
{
    public function pageGetPromoteStat()
    {
	
        $params = SJson::decode(utility_net::getPostData());       
	    $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code = -1;
        $ret->result->msg = "";
        // if (!$params->start_time || !$params->end_time || !$params->fk_promote || !$params->fk_user_owner) {
        //     $ret->result->code = -2;
        //     $ret->result->msg = 'params error';
        //     return $ret;
        // }
        
        $stat_db = new stat_db();
        $list = $stat_db->getPromoteStat($params);

        if($list->items){
            $ret->result->data = $list;
            $ret->result->code = 1;
            $ret->result->msg = 'success';
        }else{
            $ret->result->code = '-2';
            $ret->result->msg = 'data is empty';
        }
        return $ret;
    }

}
