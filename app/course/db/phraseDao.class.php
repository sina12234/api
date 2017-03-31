<?php
class course_db_phraseDao{
    public static function InitDB($dbname="db_exam",$dbtype="main") {
        redis_api::useConfig($dbname);
        $db = new SDb();
        $db->useConfig($dbname, $dbtype);
        return $db;
    }
    public function getPhraseByPid($pid){
        $db = self::InitDB("db_course", "query");
        $table = 't_course_plan_phrase';
		$condition = "t_course_plan_phrase.fk_plan = $pid AND t_phrase.type<>2";
        $left = new stdclass;
        $left->t_phrase = "t_phrase.pk_phrase = t_course_plan_phrase.fk_phrase";
        $items =array(
            't_course_plan_phrase.pk_plan_phrase',
            't_course_plan_phrase.fk_plan',
            't_course_plan_phrase.fk_phrase',
            't_course_plan_phrase.answer_right',
            't_course_plan_phrase.create_time',
            't_course_plan_phrase.last_updated',
            't_phrase.pk_phrase',
            't_phrase.type',
            't_phrase.question',
            't_phrase.question_img',
            't_phrase.answer',
            't_phrase.status',
        );
        return $db->select($table,$condition,$items,'','t_course_plan_phrase.create_time DESC',$left);
    }

    public function getPhraseIdArr($params){
        $db = self::InitDB('db_course','query');
        $table = "t_phrase";
        $condition = '';
        if(!empty($params['phraseId'])){
            $phraseIdStr = implode(",",$params['phraseId']);
            $condition .= "t_phrase.pk_phrase IN ({$phraseIdStr}) and ";
        }
        if(!empty($params['type'])){
            $condition .= "t_phrase.type={$params['type']} and ";
        }
        $condition .= "status>-1";
        return $db->select($table,$condition);
    }
}
