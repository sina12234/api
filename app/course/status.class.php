<?php
/**
 * 课程，章节，上课的状态表
 */
class course_status{
/*
	1 课程 normal正常没有开始
	2 课程 living直播中
	3 课程 finished课程已经完结
	4 课程 teaching 进行中
	-1 禁用 invalid
	0  默认状态 default
 */
	const normal	=	1;
	const living 	=	2;
	const finished 	=	3;
	const teaching 	=	4;
	const invalid 	=	-1;
	const initial	=	0;
	static $map_k=array(	
		0	=>	"initial",
		1	=>	"normal",
		2	=>	"living",
		3	=>	"finished",
		-1	=>	"invalid",
	);
	public	static function name($k){
		if(isset(self::$map_k[$k])){
			return self::$map_k[$k];
		}
		return false;
	}
	public	static function key($v){
		foreach(self::$map_k as $_k =>$_v){
			if($v==$_v)return $_k;
		}
		return false;
	}
}
