<?php
class utility_cache{
    static public function pageCache($time){
        $ts = gmdate("D, d M Y H:i:s", time() + $time) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$time");
    }
}
