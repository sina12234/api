<?php

class course_class_api
{
    public static function getClassList($classIdArr)
    {
        if (count($classIdArr) < 1) return [];
        $params = ['classId' => $classIdArr];

        $list = course_db_courseClassDao::getClassList($params);
		if(empty($list->items)) return [];
		
		//老师信息
		$userDb = new user_db();
		$userClassIdArr = array_column($list->items,'fk_user_class');
		$userClassIds   = implode(',',$userClassIdArr);
		$userRes   = $userDb->listUsersByUserIds($userClassIds);
		$userInfo = array();
		if(!empty($userRes->items)){
			foreach($userRes->items as $val){
				$userInfo[$val['pk_user']] = $val;
			}
		}

		foreach($list->items as &$v){
			$v['teacher_name'] = !empty($userInfo[$v['fk_user_class']]['name']) ? $userInfo[$v['fk_user_class']]['name'] : '';
			$v['teacher_real_name'] = !empty($userInfo[$v['fk_user_class']]['real_name']) ? $userInfo[$v['fk_user_class']]['real_name'] : '';
		}
		
		 return $list->items;
    }
   
}
