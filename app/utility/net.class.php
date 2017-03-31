<?php
/**
 *
 */
class utility_net{
	/**
	 * 获取原始的post body
	 */
	public static function getPostData(){
		if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
			return $GLOBALS['HTTP_RAW_POST_DATA'];
		}
		return file_get_contents("php://input");
	}
}
