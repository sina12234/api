<?php
/*老师统计数据调用
 * @author Panda <zhangtaifeng@gn100.com> 
 */
class stat_statteacher{
	public function pageGetOrgTeacherStat($inPath){
		$ret = new stdclass;
		$ret->result =  new stdclass;
		$ret->result->code = -1;
		$ret->result->msg= "";
		if (empty($inPath[3]) || empty($inPath[4])) {
            $ret->result->code = -2;
            $ret->result->msg= "The id is not found!";
			return $ret;
		}
		$db = new stat_db;
		$res = $db->getOrgTeacherStat($inPath[3],$inPath[4]);
        if($res===false){
            $ret->result->code = -3;
            $ret->result->msg= "The data is not found!";
			return $ret;
        }
        $ret->data=$res;
        return $ret;
	}
}
