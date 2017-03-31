<?php
class utility_mobile{

	/**
	 * @return $object
	 * $object->chgmobile ;//13875501578
	 * $object->city;//郴州</city>
	 * $object->province;//湖南</province>
	 * $object->supplier;//移动</supplier>
	 */
	public static function info($mobile){
		$str = SHttp::get("http://life.tenpay.com/cgi-bin/mobile/MobileQueryAttribution.cgi",
				array("chgmobile"=>$mobile),
				array(),
				false,
				2
			);
		return simplexml_load_string($str);
	}
}
