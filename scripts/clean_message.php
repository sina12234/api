<?php
define("ROOT_GLOBAL",			dirname(__FILE__)."/../www/");
require_once(ROOT_GLOBAL."global.php");

SlightPHP::setDebug(true);
SlightPHP::setAppDir(ROOT_APP);
SlightPHP::setDefaultZone("index");
SlightPHP::setDefaultPage("main");
SlightPHP::setDefaultEntry("entry");
SlightPHP::setSplitFlag("-_.");

//{{{
SDb::setConfigFile(ROOT_CONFIG. "/db.conf");
SRedis::setConfigFile(ROOT_CONFIG. "/redis.conf");
//}}}


//$message_db = message_db::InitDB();
redis_api::useConfig("db_message");
for($i=0;$i<=500;$i++){
	$key = md5("message_db.t_message_text.$i");
	redis_api::del($key);
	$key = md5("message_db.t_message_good.$i");
	redis_api::del($key);
}

