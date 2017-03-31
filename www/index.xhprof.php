<?php
/**
 * sample to test
 *
 * http://localhost/samples/www/index.php/zone/default/entry/a/b/c
 * http://localhost/samples/www/index.php/zone-default-entry-a-b-c.html
 *
 */
xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
$start = microtime(true);
require_once("global.php");


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
if(($r=SlightPHP::run())===false){
	echo("404 error");
}elseif(is_object($r) || is_array($r)){
	echo SJson::encode($r);
}else{
	echo($r);
}
$end= microtime(true);
$data = xhprof_disable();   //返回运行数据

// xhprof_lib在下载的包里存在这个目录,记得将目录包含到运行的php代码中
include_once "/data/xhprof_lib/utils/xhprof_lib.php";  
include_once "/data/xhprof_lib/utils/xhprof_runs.php";  

$objXhprofRun = new XHProfRuns_Default(); 

// 第一个参数j是xhprof_disable()函数返回的运行信息
// 第二个参数是自定义的命名空间字符串(任意字符串),
// 返回运行ID,用这个ID查看相关的运行结果
$run_id = $objXhprofRun->save_run($data, "xhprof");
if(empty($XHProfLogFile)){
	$XHProfLogFile = ROOT."/log/xhprof.log";
	if(!is_file($XHProfLogFile)){
		mkdir(dirname($XHProfLogFile));
	}
}
error_log("[API ".date("H:i:s")."] Time:".number_format($end-$start,5)." http://xhprof.gn100.com/index.php?run=$run_id&source=xhprof ".SlightPHP::$zone."/".SlightPHP::$page."::".SlightPHP::$entry."\n",3,$XHProfLogFile);
