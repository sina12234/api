<?php
class platform_api{


	public static function getBannerList($page,$limit,$condition,$orderby){
		
		$ret = platform_db::getBannerList($page,$limit,$condition,$orderby);
		return $ret;

	}












}
