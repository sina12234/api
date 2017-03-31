<?php

class api_func
{

    public static function setMsg($code = 3000,$errStr='')
    {
        $ret = [
            'code'   => $code,
            'zh_msg' => !empty(api_code::$errCode['zh'][$code])
                ? api_code::$errCode['zh'][$code]
                : api_code::$errCode['zh'][3000],
            'en_msg' => !empty(api_code::$errCode['en'][$code])
                ? api_code::$errCode['en'][$code]
                : api_code::$errCode['en'][3000]
        ];
        if (!empty($errStr)){ $ret['zh_msg'] = $errStr; }

        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    public static function setData($data)
    {
        return [
            'code'    => 0,
            'message' => 'success',
            'result'  => $data
        ];
    }
    
    public static function setDataConfig($data,$setConfig)
    {
        return [
            'code'    => 0,
            'message' => 'success',
            'count'  => empty($setConfig['count']) ? 0 : (int) $setConfig['count'],
            'rtime'  => empty($setConfig['rtime']) ? 0 : (int) $setConfig['rtime'],
            'result'  => $data
        ];
    }
    public static function listsParamCheck($param)
    {
        $param['page'] = isset($param['page']) && (int)$param['page'] ? (int)$param['page'] : 1;
        $param['length'] = isset($param['length']) && (int)$param['length'] ? (int)$param['length'] : 20;
        $param['item'] = isset($param['item']) && $param['item'] ? $param['item'] : '*';
        $param['condition'] = isset($param['condition']) && $param['condition'] ? $param['condition'] : '';
        $param['orderBy'] = isset($param['orderBy']) && $param['orderBy'] ? $param['orderBy'] : '';
        $param['groupBy'] = isset($param['groupBy']) && $param['groupBy'] ? $param['groupBy'] : '';

        return $param;
    }

    public static function setDefaultNull($param)
    {
        return array_map(
            function ($v) {
                return isset($v) && $v ? $v : '';
            },
            $param
        );
    }

    public static function isValidId($idNameArr, $params)
    {
        foreach ($idNameArr as $v) {
            if (!isset($params[$v]) || !(int)($params[$v])) {
                return self::setMsg(1000);
            }
            $params[$v] = (int)($params[$v]);
        }

        return $params;
    }

    public static function error($code, $msg)
    {
        return [
            'code' => $code,
            'msg'  => $msg,
        ];
    }

    public static function success($code = 0, $msg ='success')
    {
        return [
            'code' => $code,
            'msg'  => $msg,
        ];
    }

    public static function checkParams($idArr, $params)
    {
        if (count($idArr) < 1) return $params;
        foreach ($idArr as $v) {
            if (isset($params['page'], $params['length'])) {
                $params['page']   = 1;
                $params['length'] = -1;
            }

            if (isset($params[$v]) && utility_tool::check_int($params[$v])) {
                $params[$v] = (int)($params[$v]);
            } elseif (utility_tool::check_string($params[$v])) {
                $params[$v] = trim($params[$v]);
            } else {
                return self::error(1000, "'{$v}'' is not a valid Parameter");
            }
        }

        return $params;
    }    

    /* 格式化列表数据 */
    public static function formatListData($data , $paramDb=[]){            
            $itemsParam = [];
            $dataCount = empty($data->pageSize) ? 0 : (int) $data->pageSize;
            if ($dataCount>0){
                $items = $data->items;
                
                for($i=0; $i<$dataCount; $i++){
                    $itemParam = [];
                    foreach($paramDb as $dataName => $dataValue){
                        $itemParam[$dataValue] = $items[$i][$dataName];                            
                    }
                    $itemsParam[] = $itemParam;
                }           
            }

            return ['count'=>$dataCount , 'data'=>$itemsParam];
    }
        
}
