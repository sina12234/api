<?php
class stat_dayorguserstat{	
    public $ret;

    public function setResult($data='', $code=1, $msg='success'){
         $this->ret['result'] = array(
            'code' => $code,
            'message' => $msg,
            'data' => $data
         );
        return $this->ret;
    }

    /* 机构用户日报表 api */
    public function pageGetDayOrgUserStat($inPath){
        $params = SJson::decode(utility_net::getPostData());

        $searchDate = !empty($params->search_date)?$params->search_date:(!empty($inPath[3]) ? $inPath[3] : '');
        if(empty($searchDate)){
            return $this->setResult('',-3, 'params error!'.$inPath[3]);
        }	
        $ret = stat_api::getDayOrgUserStat($searchDate);
        if(!empty($ret)){
            return $this->setResult($ret);
        }else{
            return $this->setResult('', -2, 'get data failed'.$inPath[3]);
        }
    }

}









