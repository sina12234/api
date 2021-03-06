<?php
class redis_api extends SRedis{
	/**
	 * 多个设置
	 */
	public static function mSet($data){
		if(!is_array($data))return false;
		foreach($data as $k=>&$v){
			$v = serialize($v);
		}
		return parent::mSet($data);
	}
	public static function mGet($data){
		if(!is_array($data))return false;
		$ret = parent::mGet($data);
		foreach($ret as &$v){
			$v = unserialize($v);
		}
		return $ret;
	}
	/**
	 * 单个设置
	 */
	public static function set($k,$v,$expire=86400){
		$v = serialize($v);
		return parent::set($k,$v,$expire);
	}
	/*
	 * 获取数据
	 * @param boolean $returnArray
	 */
	public static function get($k){
		$v = parent::get($k);
		return unserialize($v);
	}
	public static function getBinary($k){
		$v = parent::get($k);
		return $v;
	}
	/**
	 * hash set
	 */
	public static function hSet($k,$hashKey,$value){
		$v = serialize($value);
		return parent::hSet($k,$hashKey,$v);
	}
	public static function hGet($k,$hashKey){
		$v = parent::hGet($k,$hashKey);
		return unserialize($v);
	}
	public static function hDel($k,$hashKey){
		return parent::hDel($k,$hashKey);
	}
	public static function hDelAll($k){
		return parent::delete($k);
	}
	public static function lPush($k,$values){
			$values=serialize($values);
			return parent::lPush($k,$values);
	}
	public static function rPush($k,$values){
			$values=serialize($values);
			return parent::rPush($k,$values);
	}
	public static function lPushx($k,$value){
		$value=serialize($value);
		return parent::lPushx($k,$value);
	}
	public static function rPushx($k,$value){
		$value=serialize($value);
		return parent::rPushx($k,$value);
	}
	public static function lRange($k, $start, $end){
		$v = parent::lRange($k, $start, $end);
		$tmp = array();
		if($v){
			foreach($v as $i){
				$tmp[] = unserialize($i);
			}
		}
		return $tmp;
	}
	public static function rRange($k, $start, $end){
		$v = parent::rRange($k, $start, $end);
		$tmp = array();
		if($v){
			foreach($v as $i){
				$tmp[] = unserialize($i);
			}
		}
		return $tmp;
	}
	public static function lPushByJson($k,$values){
			$values=SJson::encode($values);
			return parent::lPush($k,$values);
    }
	public static function rPushByJson($k,$values){
			$values=SJson::encode($values);
			return parent::rPush($k,$values);
    }


   	public static function sAdd($k,$value){
		$value=serialize($value);
		return parent::sAdd($k,$value);
    }

    public static function sMembers($k){
        $v =  parent::SMEMBERS($k);
        $tmp = array();
        if($v){
            foreach($v as $i){
                $tmp[] = unserialize($i);
            }
        }
        return $tmp;
    }
    public static function publish($channel, $content){
        return parent::publish($channel, $content);
    }
    public static function publishAll($channel, $content){
        $hosts = parent::__callStatic("_hosts", array());
        foreach($hosts as $h){
            $r = parent::__callStatic("_instance", array($h));
            $r->publish($channel, $content);
        }
        return True;
    }
    public static function rename($oldKey, $newKey){
        return parent::rename($oldKey, $newKey);
    }
	public static function delete($k){
		return parent::delete($k);
	}
}
