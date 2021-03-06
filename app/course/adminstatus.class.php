<?php
/**
 * 课程管理状态表
 */
class course_adminstatus{
/*
	1 正常normal(同时在列表中显示，上架)
	0 默认状态initial
	-1 未审核通过 notpassed
	-2 下架offline
 */
	const normal	=	1;
	const initial	=	0;
	const notpassed =	-1;
	const offline 	=	-2;
	static $map_k=array(	
		-1	=>	"notpassed",
		-2	=>	"offline",
		0	=>	"initial",
		1	=>	"normal",
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
