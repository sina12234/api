<?php
class stat_dayoverview
{

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

    public function pageGetDayOverViewData()
    {
        $params = SJson::decode(utility_net::getPostData());

        if (!$params->min_date || !$params->max_date) {
            return $this->setResult('', -2, 'params error');
        }

        $condition = "pk_day BETWEEN '{$params->min_date}' AND '{$params->max_date}' ";

        $tNew = new stat_daystatoverview();
        $res = $tNew->lists('', '', $condition, '', 'pk_day desc');

        if (!$res) {
            return $this->setResult('', -2, 'get data failed');
        }

        return $this->setResult($res);
    }

}
