<?php

/**
 * common function
 */
class Utility_Tool
{
    public static function dump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    public static function objToArray($obj)
    {
        $res  = [];
        $data = is_object($obj) ? get_object_vars($obj) : $obj;

        foreach ($data as $k => $v) {
            $v = (is_array($v)) || is_object($v) ? self::objToArray($v) : $v;

            $res[$k] = $v;
        }

        return $res;
    }

    public static function array_values_recursive($arr)
    {
        $data = array();

        foreach ($arr as $k => $v) {
            if (is_array($v))
                $data = array_merge($data, self::array_values_recursive($v));
            else
                $data[$k] = $v;
        }

        return $data;
    }

    public static function getAge($time)
    {
        $sTime = strtotime($time);
        if ($sTime >= time() || $sTime <= 0)
            return 0;

        $birth = date('Y-m-d', $sTime);
        list($y, $m, $d) = explode('-', $birth);
        $cm  = date('n');
        $cd  = date('j');
        $age = date('Y') - $y - 1;

        if ($cm > $m || $cm == $m && $cd > $d)
            $age++;

        return $age;
    }

    /**
     * Check if the two bytes are a chinese charactor
     *
     * @param char $lower_chr lower bytes of the charactor
     * @param char $higher_chr higher bytes of the charactor
     *
     * @return bool Returns true if it's a chinese charactor, or false otherwise
     **/
    public static function isValidChinese($lower_chr, $higher_chr)
    {
        if (($lower_chr >= 0xb0 && $lower_chr <= 0xf7 && $higher_chr >= 0xa1 && $higher_chr <= 0xfe) ||
            ($lower_chr >= 0x81 && $lower_chr <= 0xa0 && $higher_chr >= 0x40 && $higher_chr <= 0xfe) ||
            ($lower_chr >= 0xaa && $lower_chr <= 0xfe && $higher_chr >= 0x40 && $higher_chr <= 0xa0)
        ) {
            return true;
        }

        return false;
    }

    /**
     * 检查$str是否由汉字/字母/数字/下划线/.组成，默认$str是gbk编码
     *
     * @param string $str string to be checked
     *
     * @return  bool
     **/
    public static function checkStringValid($str)
    {
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $chr_value = ord($str[$i]);
            if ($chr_value < 0x80) {
                if (!ctype_alnum($str[$i]) && $str[$i] != '_' && $str[$i] != '.') {
                    return false;
                }
            } elseif ($chr_value === 0x80) {
                //欧元字符;
                return false;
            } else {
                if ($i + 1 >= $len) {
                    //半个汉字;
                    return false;
                }
                if (!self::isValidChinese(ord($str[$i]), ord($str[$i + 1]))) {
                    return false;
                }
                $i++;
            }
        }

        return true;
    }

    /**
     * check whether the url is safe
     *
     * @param string $url URL to be checked
     *
     * @return bool
     **/
    public static function isValidUrl($url)
    {
        return preg_match('/^https?:\/\/[^\s&<>#;,"\'\?]+(|#[^\s<>"\']*|\?[^\s<>"\']*)$/i', $url, $match);
    }

    /**
     * Check whether it is a valid phone number
     *
     * @param string $phone Phone number to be checked
     *
     * @return bool
     **/
    public static function isValidPhone($phone)
    {
        return preg_match('/^([0-9]{11}|[0-9]{3,4}-[0-9]{7,8}(-[0-9]{2,5})?)$/i', $phone, $match);
    }

    /**
     * check valid date
     *
     * @param  $date yyyy-mm-dd
     *
     * @return bool
     */
    public static function  checkDate($date)
    {
        return preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date, $result);
    }

    /**
     * Check whether it is a valid ip list, each ip is delemited by ','
     *
     * @param string ip list Ip list string to be checked
     *
     * @return bool
     **/
    public static function isValidIpList($ipList)
    {
        $ipList = trim($ipList);
        if (strlen($ipList) > 0) {
            if (!preg_match(
                '/^(([0-9]{1,3}\.){3}[0-9]{1,3})(,(\s)*([0-9]{1,3}\.){3}[0-9]{1,3})*$/i',
                $ipList, $match
            )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check the int input is valid or not.
     *
     * @param int $value number value
     * @param int $max max value to check
     * @param int $min min value to check
     * @param boolean $compare true to check max,false not to check max
     *
     * @return boolean true | false
     **/
    static function check_int($value, $min = 0, $max = -1, $compare = true)
    {
        if (is_null($value)) {
            return false;
        }

        if (!is_numeric($value)) {
            return false;
        }

        if ((int)($value) != $value) {
            return false;
        }

        if (true === $compare && $value < $min) {
            return false;
        }

        if (true === $compare && 0 <= $max && $max < $value) {
            return false;
        }

        return true;
    }

    /**
     * Check the string input length is valid or not.
     *
     * @param int $value string value
     * @param int $max_length max value length to check
     * @param int $min_length min value length to check
     *
     * @return boolean true | false
     **/
    static function check_string($value, $max_length = null, $min_length = 1)
    {
        if (is_null($value)) {
            return false;
        }

        if (iconv_strlen($value, "utf-8") < $min_length) {
            return false;
        }

        if (!is_null($max_length) && iconv_strlen($value, "utf-8") > $max_length) {
            return false;
        }

        return true;
    }

    /**
     * generate hash string by string
     *
     * @param $str
     *
     * @return int
     */
    public static function hash_string($str)
    {
        if (empty($str)) return 0;
        $h = 0;

        for ($i = 0, $j = strlen($str); $i < $j; $i = $i + 2) {
            $h = 5 * $h + ord($str[$i]);
        }

        return $h;
    }

	/**
 	 * 根据参数拼接生成新的url
 	 * @param array $path  当前访问url（不带参数）
 	 * @param array $pname 参数名称
 	 * @param array $pval  参数值
 	 * @return string
 	 */
	public static function getUrl($path,$pname='',$pval='',$start_time=''){
		$params = $_GET;
		$new_params = array();
		if($pname == 'vid' && !empty($params['vid'])){
			$newVidArr = explode('|',$pval);
			$oldVidArr = explode(',',$params['vid']);
			$mergeVidArr = array();
			foreach($oldVidArr as $vo){
				$temp = explode('|',$vo);
				if($temp[0] != $newVidArr[0] ){
					$mergeVidArr[] = $vo;
				}
			}
			if(!empty($mergeVidArr)){
				if($newVidArr[1] != -1){
					array_push($mergeVidArr,$pval);
				}
				$params['vid'] = implode(',',$mergeVidArr);
			}else{
				if($newVidArr[1] == -1){
					$pval = -1;
				}
				$new_params = array($pname=>$pval,'start_time'=>$start_time);
				$params = array_merge($params, $new_params);
			}
		}else{
			if($pname == 'vid' && empty($params['vid'])){
				$newVidArr = explode('|',$pval);
				if($newVidArr[1] == -1){
					$pval = -1;
				}
			}
			if( $pname ){
				$new_params = array($pname=>$pval,'start_time'=>$start_time);
			}else{
				$new_params = array('start_time'=>$start_time);
			}
			$params = array_merge($params, $new_params);
		}
		
		$arg  = "";
		foreach($params as $k=>$v){
			if($pname != 'page' && $k == 'page'){
				unset($params[$k]);
			}elseif( $k == 'size'){
				unset($params[$k]);
			}elseif($v === '' || $v == -1){
				unset($params[$k]);
			}elseif($pname == 'tc' && $k == 'vid'){
				unset($params[$k]);
			}else{
    			$arg.=$k."=".$v."&";
			}
		}
  		$arg  = substr($arg,0,count($arg)-2);
		if(get_magic_quotes_gpc()){
			$arg = stripslashes($arg);
		}
		if($arg){
			$url  = $path.'?'.$arg;
		}else{
			$url = $path;
		}

		return $url;
	}

    /**
     * @desc How long is the current time to calculate the given number of seconds
     *
     * @param $timestamp
     * @param int $granularity // default year week day hour min sec
     * @return string
     */
    public static function formatInterval($timestamp, $granularity = 5)
    {
        $units = [
            31536000 => '年',
            604800   => '周',
            86400    => '天',
            3600     => '小时',
            60       => '分钟',
            1        => '秒'
        ];

        $output = '';

        foreach ($units as $key => $value) {
            if ($timestamp >= $key) {
                $output .= floor($timestamp / $key).$value;
                $timestamp %= $key;
                $granularity--;
            }

            if ($granularity == 0) break;
        }

        return $output ? $output : '0 秒';
    }

    /**
     * @desc Seconds format for hours, minutes
     *
     * @param $sec
     * @return string
     */
    public static function sec2time($sec)
    {
        if ($sec < 60) {
            return $sec.'秒';
        }

        $sec = round($sec / 60);
        if ($sec >= 60) {
            $hour = floor($sec / 60);
            $min  = $sec % 60;
            $res  = $hour.' 小时 ';
            $min != 0 && $res .= $min.' 分';
        } else {
            $res = $sec.' 分钟';
        }

        return $res;
    }

    public static function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);

            if (count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."...";

            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start  = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';

            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr .= substr($string, $i, 2);
                    } else {
                        $tmpstr .= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129) $i++;
            }
            if (strlen($tmpstr) < $strlen) $tmpstr .= "...";

            return $tmpstr;
        }
    }

    public static function regularImgMatch($str)
    {
        preg_match('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i',$str,$match);
        /*preg_match('/<img src=\"(.+?)\".*?>/',$str,$match);*/

        return !empty($match[2]) ? $match[2] : '';
    }

    public static function t($time)
    {
        $time = strtotime($time);
        $nowYear   = date('Y');

        return ($nowYear == date('Y', $time)) ? date('m-d H:i', $time) : date('Y-m-d H:i', $time);
    }

	/*
	 * @desc 排序
	 * @param $data 
	 * @param $orderBy (SORT_DESC SORT_ASC)
	 * @param $field  排序字段
	 */
	public static function mysort($data,$orderBy,$field)
	{
		$sort = [
			'direction' => $orderBy,
			'field'     => $field
		];

		$arrSort = array();

		foreach($data AS $uniqid => $row)
		{
			foreach($row AS $key=>$value)
			{
				$arrSort[$key][$uniqid] = $value;
			}
		}

		if($sort['direction'])
		{
			array_multisort($arrSort[$sort['field']], constant($sort['direction']), $data);
		}

		return $data;
	}

    public static function replaceStr($str)
    {
        $len = mb_strlen($str);
        if (empty($str) || !$len) return $str;

        $cutStr = mb_substr($str,0,1,'utf-8');

        return $cutStr.'**';
    }
}
