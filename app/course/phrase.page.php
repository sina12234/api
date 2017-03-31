<?php
class course_phrase{
    public function pageGetPlanPhraseByPid($inPath){

        $pid = SJson::decode(utility_net::getPostData());
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg = "";
        $ret->data = '';
        if(empty($pid) && !is_numeric($pid)){
            $ret->msg = 'params is error';
            return $ret;
        }
        $phrase_db = new course_db_phraseDao();
        $question_ret = $phrase_db->getPhraseByPid($pid);
        if(!empty($question_ret) && !empty($question_ret->items)){
            $ret->code = 0;
            $ret->msg = 'success';
            $ret->data = $question_ret->items;
        }else{
            $ret->code = -2;
            $ret->msg = 'get data failed';
        }
        return $ret;
    }

    public function pageGetPhraseIdArr(){
        $ret = new stdclass;
        $ret->code = -1;
        $ret->msg = "";
        $ret->data = '';
        $params = SJson::decode(utility_net::getPostData(),true);

        $phrase_db = new course_db_phraseDao();
        $res = $phrase_db->getPhraseIdArr($params);

        if(!empty($res)){
            $ret->code = 0;
            $ret->msg = 'success';
            $ret->data = $res;
        }else{
            $ret->code = -2;
            $ret->msg = 'get data failed';
        }
        return $ret;
    }
}
