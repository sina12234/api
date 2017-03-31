<?php
class log_log{
	public function __construct($inPath){
		return;
	}
	public function pageAddPlayLog($inPath){
		$params=SJson::decode(utility_net::getPostData());
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		$data = new stdclass;
		$data->data = $params->data;
		$log_db = new log_db;
        if(!empty($params->play)){
            $data->play = $params->play;
            if(!empty($params->reason)){
                $data->reason = $params->reason;
            }
            $db_ret = $log_db->addPlayLogExtra($data);
        }else{
            $db_ret = $log_db->addPlayLog($data);
        }
		if($db_ret){
			$ret->result->code = 0;
		}
		return $ret;
	}
    //报表
    public function pagegetChartsData($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $log_db = new log_db;
        $params = SJson::decode(utility_net::getPostData());
       if(empty($params->areaname)){
           $ret->result->code = -2;
           $ret->result->msg= "areaname is empty!";
        }
        if(empty($params->min_date) || empty($params->max_date)){
           $ret->result->code = -2;
           $ret->result->msg= "date is error!";
           return $ret;
        }
        $intervals=86400;
        $confition=array();
        $condition=array(
                     "areaname"=>$params->areaname,
                     "intervals"=>$intervals,
                     "streamtype"=>$params->streamtype,
                     "opname"=>$params->opname,
                     "cdnid"=>$params->cdnid,
                     "playmode"=>$params->playmode,
                     "min_date"=>$params->min_date,
                     "max_date"=>$params->max_date
                  );
        $result=$log_db->getChartsData($condition);
        if(empty($result->items)){
            $ret->result->code = -3;
            $ret->result->msg= "data is empty!";
           return $ret;
        }
        $charts=array();

        for($i='-'.$params->days;$i<0;$i++){
            $charts[date('m-d',strtotime($i.' days'))]=0;
        }
        $area_result=$log_db->getAreaList($intervals,$params->min_date,$params->max_date);
        $area_list=$area_result->items;
        $opname_list=array();
        foreach($result->items as $k=>$v){
            $rate=number_format((1-($v['duration']>0?($v['b1_mili']/$v['duration']):0))*100,2);
            $result->items[$k]['rate']=$rate;
            $result->items[$k]['starttime']=date('Y-m-d',strtotime($v['starttime']));
            $result->items[$k]['duration']=number_format($v['duration']/1000,1);
            $charts[date("m-d",strtotime($v['starttime']))]=$rate;
        }
        $charts=implode(',',$charts);
        $ret->page = $result->page;
        $ret->size = $result->pageSize;
        $ret->total = $result->totalPage;
        $ret->charts=$charts;
        $ret->area_list=$area_list;
        $ret->opname_list=$opname_list;
        $ret->data=$result->items;

        return $ret;
    }
    //日报表
    public function pagegetDayCharts($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $log_db = new log_db;
        $params = SJson::decode(utility_net::getPostData());
       if(empty($params->areaname)){
           $ret->result->code = -2;
           $ret->result->msg= "areaname is empty!";
        }
        if(empty($params->min_date) || empty($params->max_date)){
           $ret->result->code = -2;
           $ret->result->msg= "date is error!";
           return $ret;
        }
        $intervals=600;
        $confition=array();
        $condition=array(
                     "areaname"=>$params->areaname,
                     "intervals"=>$intervals,
                     "streamtype"=>$params->streamtype,
                     "opname"=>$params->opname,
                     "cdnid"=>$params->cdnid,
                     "playmode"=>$params->playmode,
                     "min_date"=>$params->min_date,
                     "max_date"=>$params->max_date
                  );
        $result=$log_db->getChartsData($condition);
        if(empty($result->items)){
            $ret->result->code = -3;
            $ret->result->msg= "data is empty!";
           return $ret;
        }
        $charts=array();

        for($i=0;$i<24;$i++){
            if($i<10){$i='0'.$i;}
            for($j=0;$j<60;$j++){
                if($j%10==0){
                    if($j<10){$j='0'.$j;}
                    $charts[$i.':'.$j]=0;
                }
            }
        }
        foreach($result->items as $k=>$v){
            $rate=number_format((1-($v['duration']>0?($v['b1_mili']/$v['duration']):0))*100,2);
            $result->items[$k]['rate']=$rate;
            $result->items[$k]['duration']=number_format($v['duration']/1000,1);
            $charts[date("H:i",strtotime($v['starttime']))]=$rate;
        }
        $charts=implode(',',$charts);
        $area_result=$log_db->getAreaList($intervals,$params->min_date,$params->max_date);
        $area_list=$area_result->items;
        $ret->page = $result->page;
        $ret->size = $result->pageSize;
        $ret->total = $result->totalPage;
        $ret->charts=$charts;
        $ret->area_list=$area_list;
        //$ret->opname_list=$opname_list;
        $ret->data=$result->items;

        return $ret;
    }
    public function pagegetReportByArea($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $log_db = new log_db;
        $params = SJson::decode(utility_net::getPostData());
        if(empty($params->min_date) || empty($params->max_date)){
           $ret->result->code = -2;
           $ret->result->msg= "date is error!";
           return $ret;
        }
        $intervals=86400;
        $confition=array();
        $condition=array(
                     "intervals"=>$intervals,
                     "streamtype"=>$params->streamtype,
                     "opname"=>$params->opname,
                     "cdnid"=>$params->cdnid,
                     "playmode"=>$params->playmode,
                     "min_date"=>$params->min_date,
                     "max_date"=>$params->max_date
                  );
        $result=$log_db->getReportByArea($condition);
        if(empty($result->items)){
            $ret->result->code = -3;
            $ret->result->msg= "data is empty!";
            return $ret;
        }
        $charts=array();
        $arealist=array();
        foreach($result->items as $k=>$v){
            $rate=number_format((1-($v['duration']>0?($v['b1_mili']/$v['duration']):0))*100,2);
            $result->items[$k]['rate']=$rate;
            $result->items[$k]['duration']=number_format($v['duration']/1000,1);
            //$charts[$v['areaname']]='0';
            $charts[$v['areaname']]=$rate;
            $arealist[$v['areaname']]='\''.$v['areaname'].'\'';
        }
        $xAxis=implode(',',$arealist);
        $charts=implode(',',$charts);
        $ret->page = $result->page;
        $ret->size = $result->pageSize;
        $ret->total = $result->totalPage;
        $ret->data=$result->items;
        $ret->charts=$charts;
        $ret->xAxis=$xAxis;
        return $ret;
    }
    public function pagegetReportByOp($inPath){
        $ret = new stdclass;
        $ret->result =  new stdclass;
        $ret->result->code = -1;
        $ret->result->msg= "";
        $log_db = new log_db;
        $params = SJson::decode(utility_net::getPostData());
        if(empty($params->min_date) || empty($params->max_date)){
           $ret->result->code = -2;
           $ret->result->msg= "date is error!";
           return $ret;
        }
        $intervals=86400;
        $confition=array();
        $condition=array(
                     "intervals"=>$intervals,
                     "areaname"=>$params->areaname,
                     "streamtype"=>$params->streamtype,
                     "cdnid"=>$params->cdnid,
                     "playmode"=>$params->playmode,
                     "min_date"=>$params->min_date,
                     "max_date"=>$params->max_date
                  );
        $result=$log_db->getReportByOp($condition);
        if(empty($result->items)){
            $ret->result->code = -3;
            $ret->result->msg= "data is empty!";
            return $ret;
        }
        $charts=array();
        $oplist=array();
        foreach($result->items as $k=>$v){
            $rate=number_format((1-($v['duration']>0?($v['b1_mili']/$v['duration']):0))*100,2);
            $result->items[$k]['rate']=$rate;
            $result->items[$k]['duration']=number_format($v['duration']/1000,1);
            $charts[$v['opname']]=$rate;
            if($v['opname']=='all'){
                $oplist[$v['opname']]='\'全国\'';
            }else{
                $oplist[$v['opname']]='\''.$v['opname'].'\'';
            }
        }
        $area_result=$log_db->getAreaList($intervals,$params->min_date,$params->max_date);
        $area_list=$area_result->items;
        $xAxis=implode(',',$oplist);
        $charts=implode(',',$charts);
        $ret->page = $result->page;
        $ret->size = $result->pageSize;
        $ret->total = $result->totalPage;
        $ret->data=$result->items;
        $ret->charts=$charts;
        $ret->area_list=$area_list;
        $ret->xAxis=$xAxis;
        return $ret;
    }

    //统计成功报表
    public function pagegetOverCharts()
    {
        $log_db = new log_db;

        $ret['orgisternum'] = $log_db->getCountData('db_user','t_user_mobile');
        $ret['coursenum']   = $log_db->getCountData('db_course','t_course');
        $ret['orgnum']      = $log_db->getCountData('db_user','t_organization','status>=1');
        $ret['ordernum']    = $log_db->getCountData('db_order','t_order','status=2');
        $orderprice         = $log_db->getCountData('db_order','t_order','status=2',"sum(price) as num");
        $ret['orderprice']  = number_format($orderprice/100,2);
		$ret['teachernum']  = $log_db->getCountData('db_user','t_user','TYPE & 0x02 > 0',"count(*) as num");
        $ret['classnum']    = $log_db->getCountData('db_course','t_course_class');
		$ret['registrationnum'] = $log_db->getCountData('db_course','t_course','user_total>0',"sum(user_total) as num");
		$ret['praisenum'] = $log_db->getCountData('db_message','t_message_plan_good','status!=-1',"sum(num) as num");
		$ret['discussion'] = $log_db->getCountData('db_message','t_message_plan_text','',"count(*) as num");

		$ret['commentnum'] = $log_db->getCountData('db_message','t_comment_course','',"count(*) as num");

		$resFeeUser = $log_db->getPiceUser("STATUS=2 and price>=0",'fk_user');
		if(!empty($resFeeUser)){
			$ret['priceusernum'] = $resFeeUser->pageSize;
		}else{
			$ret['priceusernum'] = 0;
		}
      return $ret;
    }
	public function pageaddPromoteLog(){
		$params=SJson::decode(utility_net::getPostData(),true);
		//print_r($params);
		$ret = new stdclass;
		$ret->result = new stdclass;
		$ret->result->code=-1;
		$ret->result->msg="";
		if(empty($params)){
			$ret->result->code = -101;
			$ret->result->msg = 'data empty';
			return $ret;
		}

		$log_db = new log_db;
		$db_ret = $log_db->addPromoteLog($params);
		if($db_ret){
			$ret->result->code = 0;
			$ret->result->msg = 'success';
		}else{
			$ret->result->code = -102;
			$ret->result->msg = 'insert fail';
		}
		return $ret;


	}
    //获取promote log记录
    public function pagegetPromoteLog($inpath){

        $ret = new stdclass;
        $ret->result = new stdclass;
        $ret->result->code=0;
        $ret->result->msg="";
        $params = SJson::decode(utility_net::getPostData(),true);
        if(empty($params)){
            $ret->result->code = '-101';
            $ret->result->msg = 'params is empty';
            return $ret;
        }
        $log_db = new log_db;
        $res = $log_db->getPromoteLog($params);
        if(empty($res)){
            $ret->result->code=-2;
            $ret->result->msg="data is empty!";
            return $ret;
        }
        $ret->result->data = $res;
        return $ret;
    }

    public function pageAddUserAgentInfo()
    {
        $params = SJson::decode(utility_net::getPostData(), true);
        if (empty($params['orderId'])) return api_func::setMsg(1000);

        $data = [
            'fk_order'        => $params['orderId'],
            'browser'         => isset($params['browser']) ? $params['browser'] : '',
            'browser_version' => isset($params['browserVersion']) ? $params['browserVersion'] : '',
            'system'          => isset($params['system']) ? $params['system'] : '',
            'device'          => isset($params['device']) ? $params['device'] : '',
            'user_agent'      => isset($params['userAgent']) ? $params['userAgent'] : '',
            'pay_type'        => isset($params['payType']) ? $params['payType'] : 0,
            'status'          => isset($params['status']) ? $params['status'] : 0,
            'source'          => isset($params['source']) ? $params['source'] : 0,
        ];

        $res = log_db_feeOrderUserAgentDao::add($data);
        if ($res) {
            return api_func::setMsg(0);
        }

        return api_func::setMsg(1);
    }

    public function pageAddInterfaceLog()
    {
        $params=SJson::decode(utility_net::getPostData(), true);
        if (empty($params['url']) || empty($params['deviceInfo']))
            return api_func::setMsg(1000);

        $data = [
            'url'        => $params['url'],
            'type'       => $params['type'],
            'deviceInfo' => $params['deviceInfo']
        ];

        if (log_db_interfaceDao::add(json_encode($data))) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }
}

