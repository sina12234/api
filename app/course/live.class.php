<?php
/**
 * 新增加班级,章节改变课程状态
 */
class course_live{
/*
	1 课程 normal正常没有开始
	2 课程 living直播中
	3 课程 end课程已经完结
	-1 禁用 invalid
	0  默认状态 default
 */
	const normal	=	1;
	const living 	=	2;
	const finished 	=	3;
	const invalid 	=	-1;
	const initial	=	0;
	static $map_k=array(	
		0	=>	"initial",
		1	=>	"normal",
		2	=>	"living",
		3	=>	"finished",
		-1	=>	"invalid",
	);

	//删除时获取本章节的course_id
	public	static function sec_course_id($section_id){
		$course_db = new course_db;
		$ret_get_section = $course_db->getSection($section_id);
		if(empty($ret_get_section)) return false;
		$course_id = $ret_get_section["course_id"];
		return $course_id;
	}
	//删除时获取本班级course_id
	public	static function class_course_id($class_id){
		$course_db = new course_db;
		$ret_get_class = $course_db->getClass($class_id);
		if(empty($ret_get_class)) return false;
		$course_id = $ret_get_class["fk_course"];
		return $course_id;

	}
	/*
	 *增加时获取该课程排课信息
	 */
	public	static function list_plan($course_id){	
		$course_db = new course_db;
		$ret_list_items = $course_db->planList($course_id);
		$ret_list_plan = $ret_list_items->items;
		if(empty($ret_list_plan)){
			return false;
		}else{
			return $ret_list_plan;	
		}
	}
	/*
	 *改变课程状态
	 */	
	public	static function course_live_update($course_id,$status){
		$course_db = new course_db;
		$course_info = $course_db->getCourse($course_id);
		$class_info  = $course_db->classList($course_id);
		$plan_info   = $course_db->getPlanTeacherByCourseId($course_id);
		$ret_course  = $course_db->updateCourseStatus($course_id,$status);
		$stat_arr = array(1,2);
		if($ret_course===false){
			return false;
		}else{
			//修改分机构教师完成的课程和剩余课程数
			$tid_arr = array();
			foreach($class_info->items as $so){
				$tid_arr[$so['fk_user_class']] = $so['fk_user_class'];
			}
			foreach($plan_info->items as $po){
				$tid_arr[$po['fk_user_plan']] = $po['fk_user_plan'];
			}
			if($course_info['status'] == 3 && in_array($status,$stat_arr)){
				stat_api::reduceTeacherStatCourseCompleteCount($tid_arr);		
				stat_api::reduceTeacherStatOrgCourseCompleteCount($tid_arr,$course_info['fk_user']);		
			}
			if($course_info['status'] !=3 && $status == 3){
				stat_api::addTeacherStatCourseCompleteCount($tid_arr);		
				stat_api::addTeacherStatOrgCourseCompleteCount($tid_arr,$course_info['fk_user']);		
			}

			return $ret_course;
		}
	}
	/*
	 *增加排课时改为未完结
	 */
	public	static function add_plan_change_course($course_id){
		$ret_list_plan = self::list_plan($course_id);
		$status_arr = array();
		//存入数组里
		foreach($ret_list_plan as $k=>$item){
			$status_arr[$k]= $item["status"];
		}
			//如果有1 或2; 未完结
			//如果都不是 完结
		if((in_array("1",$status_arr))||(in_array("2",$status_arr))){
				$status = 1;
				$course_live_update = self::course_live_update($course_id,$status);	
		}else{
				$status = 3;
				$course_live_update = self::course_live_update($course_id,$status);	
		}
		return $course_live_update;
	}
	/*
	 *删除班级
	 */
	public	static function del_class_change_course($class_id){
		$course_id = self::class_course_id($class_id);
		$ret_list_plan = self::list_plan($course_id);
		$status_arr = array();
		//存入数组里
		if(empty($ret_list_plan)){
			$status = 1;
			$course_live_update = self::course_live_update($course_id,$status);	
		}else{
			foreach($ret_list_plan as $k=>$item){
				$status_arr[$k]= $item["status"];
			}
			//如果有1 或2; 未完结
			//如果都不是 完结
			if((in_array("1",$status_arr))||(in_array("2",$status_arr))){
				$status = 1;
				$course_live_update = self::course_live_update($course_id,$status);	
			}else{
				$status = 3;
				$course_live_update = self::course_live_update($course_id,$status);	
			}
		}
		return $course_live_update;
	}
	public	static function del_section_change_course($section_id){
		$course_id = self::sec_course_id($section_id);
		$ret_list_plan = self::list_plan($course_id);
		$status_arr = array();
		//存入数组里
		//存入数组里
		if(empty($ret_list_plan)){
			$status = 1;
			$course_live_update = self::course_live_update($course_id,$status);	
		}else{
			foreach($ret_list_plan  as $k=>$item){
				$status_arr[$k]= $item["status"];
			}
			//如果有1 或2; 未完结
			//如果都不是 完结
			if((in_array("1",$status_arr))||(in_array("2",$status_arr))){
				$status = 1;
				$course_live_update = self::course_live_update($course_id,$status);	
			}else{
				$status = 3;
				$course_live_update = self::course_live_update($course_id,$status);	
			}
		}
		return $course_live_update;
	}
		//获取本班级的course_id
/*	


	//获取本章节的course_id
	public	static function sec_course_id($section_id){
		$ret_get_section = $this->course_db->getSection($section_id);
		if(empty($ret_get_section)) return false;
		$course_id = $ret_get_section["course_id"];
		return $course_id;
	}
	public	static function class_course_id($class_id){
		$ret_get_class = $this->course_db->getClass($section_id);
		if(empty($ret_get_class)) return false;
		$course_id = $ret_get_section["fk_course"];
		return $course_id;

	}
	public	static function is_del_sec($section_id){
		$course_id = self::sec_course_id;
		//获取课程下的所有section状态
		$ret_list_section = $this->course_db->sectionList($course_id);
		$status_arr = array();
		//存入数组里
		foreach($ret_list_section->items  $k as $item){
			$status_arr[$k]= $item["status"];
		}
			//如果有1 或2; 未完结
			//如果都不是 完结
		if((in_array("1",$status_arr))||(in_array("2",$status_arr))){
				$status = 1;
				$course_live_update = self::course_live_update($course_id,$status);	
		}else{
				$status = 3;
				$course_live_update = self::course_live_update($course_id,$status);	
		}
	}
	public	static function is_del_class($class_id){
		$course_id = self::class_course_id;
		//获取课程下的所有class状态
		$ret_list_class = $this->course_db->classList($course_id);
		$status_arr = array();
		foreach($ret_list_class->items  $k as $item){
			$status_arr[$k]= $item["status"];
		}
			//如果有1 或2; 未完结
			//如果都不是 完结
		if((in_array("1",$status_arr))||(in_array("2",$status_arr))){
				$status = 1;
				$course_live_update = self::course_live_update($course_id,$status);	
		}else{
				$status = 3;
				$course_live_update = self::course_live_update($course_id,$status);	
		}
		
	//	$ret_status = self::course_live_update($course_id,$status);
	}
	public	static function is_add_sec($section_id){
		$status = "1";
		$ret_status = self::course_live_update($course_id,$status);
	}
	public	static function is_add_class($class_id){
		$status = "1";
		$ret_status = self::course_live_update($course_id,$status);
	}
*/
}
