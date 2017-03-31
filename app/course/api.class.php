<?php

class course_api
{

    private static $course_resource = array(
        'course_id'     => '',
        'cate_id'       => '',
		'first_cate'    => '',
		'second_cate'   => '',
		'third_cate'    => '',
        'user_id'       => '',
        'title'         => '',
        'tags'          => '',
        'descript'      => '',
		'scope'         => '',
        'start_time'    => '',
        'create_time'   => '',
        'end_time'      => '',
        'max_user'      => '',
        'min_user'      => '',
        'user_total'    => '',
        //'system_status' => '',
        'check_status' => '',
        'status'        => '',
        'thumb_big'     => "",
        'thumb_med'     => "",
        'thumb_small'   => "",
        'public_type'   => "",
        'fee_type'      => "",
        'admin_status'  => "",
        'last_updated'  => "",
    );
    private static $course_resmp = array(
        'basic' => array(
            'course_id'     => 'pk_course',
            'cate_id'       => 'fk_cate',
			'first_cate'    => 'first_cate',
			'second_cate'   => 'second_cate',
			'third_cate'    => 'third_cate',
            'user_id'       => 'fk_user',
            'grade_id'      => 'fk_grade',
            'type_id'       => 'type',
            'title'         => 'title',
            'tags'          => 'tags',
			'scope'         => 'scope',
            'descript'      => 'descript',
            'start_time'    => 'start_time',
            'create_time'   => 'create_time',
            'end_time'      => 'end_time',
            'max_user'      => 'max_user',
            'min_user'      => 'min_user',
            'user_total'    => 'user_total',
            //'system_status' => array(1 => 'nostart', 2 => 'starting', 3 => 'over'),
            'status'        => array(1 => 'normal', -1 => 'pause', 0 => 'over'),
            'thumb_big'     => 'thumb_big',
            'thumb_med'     => 'thumb_med',
            'thumb_small'   => 'thumb_small',
            'public_type'   => 'public_type',
            'fee_type'      => 'fee_type',
            'admin_status'  => 'admin_status',
            'check_status'  => 'check_status',
            'status'        => 'status',
            'last_updated'  => 'last_updated',
            'is_promote'    => 'is_promote',
        ),
        "fee"   => array(
            "price"        => "price",
            "price_market" => "price_market",
        ),
    );
    public function genId($uid)
    {
        $db    = new course_db;
        $maxId = $db->getMaxCourseIdByUid($uid);
        if ($maxId === false) return false;
        $course = $db->getCourse($maxId);
        if ($maxId === 0 || !empty($course['title'])) {
            //{{{
            $stat_api      = new stat_api;
            $data          = array();
            $data["count"] = "1";
            $retget        = $stat_api->setUserOrgStatCourseCount($uid, $data);
            //}}}
            //新增一个
            $course                = array();
            $course['fk_user']     = $uid;
            $course['status']      = 0;
            $course['type']     = 0;
            $time                  = date("Y-m-d H:i:s");
            $course['create_time'] = $time;
            $courseId              = $db->addCourse($course);
            if ($courseId) return $courseId;
        }

        return $maxId;
    }

    //添加一个空的classid·
    public function genclassId($course_id)
    {
        $db    = new course_db;
        $maxId = $db->getMaxClassIdBycid($course_id);
        if ($maxId === false) return false;
        $class = $db->getClass($maxId);
        if ($maxId === 0 || !empty($class['name'])) {
            //新增一个
            $class              = array();
            $class['fk_course'] = $course_id;
            $class['type']      = "1";


            $time                  = date("Y-m-d H:i:s");
            $class['create_time']  = $time;
            $class['last_updated'] = $time;
            $retclass              = $db->addClass($class);
            //统计创建的班级
            ///{{{
            $stat_api      = new stat_api;
            $data          = array();
            $data["count"] = "1";
            $retget        = $stat_api->setCourseStatClassCount($course_id, $data);
            //}}}
            if ($retclass) return $retclass;
        }

        return $maxId;
    }

    public static function update($course_id, $coursein)
    {
        $course_db = new course_db;
        $course_id = (int)$course_id;
        $course_db->updateCourse($course_id, $coursein);

        return $course_db;
    }
    public function updateCheckstatus($course_id, $coursein){
        $course_db = new course_db;
        $course_id = (int)$course_id;
        $course_db->updateCourse($course_id, $coursein);

        return $course_db;
    }
    public function updateDeletestatus($course_id, $coursein){
        $course_db = new course_db;
        $course_id = (int)$course_id;
        $course_db->updateCourse($course_id, $coursein);
        return $course_db;
    }


    /**
     * 获取课程信息
     *
     * @param int $uid 课程ID
     * @return boolean|array
     */
    public function get($course_id)
    {
        if (empty($course_id)) {
            return false;
        }
        $course_db  = new course_db();
        $user_db    = new user_db();
        $basic_data = $course_db->getCourse($course_id);
        $user_id    = $basic_data["fk_user"];
        //获取用户信息
        $user_get = $user_db->getUser($user_id);
        $user_profile = $user_db->getUserProfile($user_id);
        $username = $user_get["name"];
        if (empty($basic_data)) {
            return array(
                "code" => '-102',
            );
        }
        $course = self::$course_resource;
        foreach (self::$course_resmp['basic'] as $k => $v) {
            if (is_array($v)) {
                //如果是数组的话
                if (isset($v[$basic_data[$k]])) {
                    //如果basic_data的这个key有值把这个值赋值到$user[k]中
                    $course[$k] = $v[$basic_data[$k]];
                }
            } else {
                if (isset($basic_data[$v])) {
                    $course[$k] = $basic_data[$v];
                }
            }
        }
        $course['fee'] = new stdclass;
        if ($course['fee_type']) {
			if (!empty($basic_data)) {
				if($basic_data['price']%100 == 0){
					$basic_data['price']        = $basic_data['price']/100;
				}else{
					$basic_data['price']        = number_format($basic_data['price'] / 100, 2, ".", "");
				}
				if($basic_data['price_market']%100 == 0){
					$basic_data['price_market']        = $basic_data['price_market']/100;
				}else{
					$basic_data['price_market']        = number_format($basic_data['price_market'] / 100, 2, ".", "");
				}
				$course['fee']->price        = $basic_data['price'];
				$course['fee']->price_market = $basic_data['price_market'];
			}
        }
		if(!empty($user_profile)){
			$course['user']['user_real_name'] = $user_profile['real_name'];
		}else{
			$course['user']['user_real_name'] = '';
		}
        $course['user']['username'] = $username;
        $sort                       = $course_db->getSort($course['course_id']);
        $course['sort']             = $sort ? $sort['sort'] : 0;

        return $course;
    }

    /*
     *获取一个班级信息
     */
    public function getclass($class_id)
    {
        if (empty($class_id)) {
            return false;
        }
        $class_key_array = array(
            "pk_class"      => "class_id",
            "fk_user"       => "user_id",
            "fk_user_class" => "user_class_id",
            "fk_course"     => "course_id",
            "name"          => "name",
            "descript"      => "descript",
            "type"          => "type",
            "max_user"      => "max_user",
            "min_user"      => "min_user",
            "user_total"    => "user_total",
            "status"        => "status",
            "region_level0" => "region_level0",
            "region_level1" => "region_level1",
            "region_level2" => "region_level2",
            "address"       => "address",
			"progress_percent"=>"progress_percent",
			"progress_plan"=>"progress_plan",
			"progress_status"=>"progress_status",
            "group_count"=>"group_count",
            "group_message"=>"group_message"
        );
        $course_db       = new course_db();
        $get_class_data  = $course_db->getclass($class_id);
        if (empty($get_class_data)) {
            return array(
                "code" => '-102',
            );
        }
        foreach ($class_key_array as $k => $v) {
            $ret[$v] = $get_class_data[$k];
        }
        $ret['status'] = course_status::name($ret['status']);

        return $ret;
    }


    /*
     *获取所有年级信息
     */
    public function getgradelist()
    {
        $course_db = new course_db();
        $gradelist = $course_db->gradelist()->items;
        if ($gradelist === false) return false;
        $count = count($gradelist);
        for ($i = 0; $i < $count; $i++) {
            $relist[$i]["grade_id"]     = $gradelist[$i]["pk_grade"];
            $relist[$i]["name"]         = $gradelist[$i]["name"];
            $relist[$i]["last_updated"] = $gradelist[$i]["last_updated"];
        }
        if (empty($relist)) {
            $relist = 0;
        }
        $ret       = new stdClass;
        $ret->data = $relist;

        return $ret;
    }

    /*
     *增加章节信息
     */
    public function addsection($cid, $section)
    {
        $array_section = array(
            "name"     => "name",
            "descript" => "descript",
            "order_no" => "order_no",
        );
        $time          = date("Y-m-d H:i:s");
        foreach ($array_section as $key => $value) {
            if (isset($section[$key])) {
                $secdata[$key] = $section[$value];
            }
        }
        $course_db               = new course_db;
        $secdata["fk_course"]    = $cid;
        $secdata["status"]       = empty($section["status"]) ? '1' : $section["status"];
        $secdata["last_updated"] = $time;
        $secdata["create_time"]  = $time;
        $ret                     = $course_db->addsection($secdata);

        return $ret;
    }

    /*
     * 删除章节信息
     */
    public function delSection($sid, $cid)
    {
        $sid = (int)$sid;
        $cid = (int)$cid;
        //				define("DEBUG",true);
        $course_db = new course_db;
        $ret       = $course_db->delSection($sid, $cid);
        $retc      = $course_db->delPlan($cid, $class_id = null, $sid);
        $ret_sec   = course_live::del_section_change_course($sid);

        return $retc;
        //		return $ret_sec;
    }

    /*
     *删除班级信息
     */
    public function delClass($cid, $class_id, $sid = null)
    {
        $class_id  = (int)$class_id;
        $cid       = (int)$cid;
        $course_db = new course_db;
        //	define("DEBUG",true);
        $list_reg1 = $course_db->ListRegistration($cid, $class_id);
        if (empty($list_reg1->items)) {
            //统计创建的班级
            ///{{{
            $stat_api      = new stat_api;
            $data          = array();
            $data["count"] = "-1";
            $retget        = $stat_api->setCourseStatClassCount($cid, $data);
            //}}}
            $rets      = $course_db->delClass($class_id);
            $retc      = $course_db->delPlan($cid, $class_id);
            $ret_class = course_live::del_class_change_course($class_id);

            return $rets;
        } else {
            $rets = "failed";

            return $rets;
        }
    }

    /*
     *获取所有章节信息
     */
    public function getsectionlist($course_id, $sectionIds='')
    {
        $course_id = (int)$course_id;
        //$array_status = array("禁用","未开始","开始中","已经结束",);
        $course_db   = new course_db();
        $sectionlist = $course_db->sectionlist($course_id,$sectionIds)->items;
        if ($sectionlist === false) return false;
        $count = count($sectionlist);
        for ($i = 0; $i < $count; $i++) {
            $relist[$i]["section_id"]   = $sectionlist[$i]["pk_section"];
            $relist[$i]["name"]         = $sectionlist[$i]["name"];
            $relist[$i]["course_id"]    = $sectionlist[$i]["fk_course"];
            $relist[$i]["order_no"]     = $sectionlist[$i]["order_no"];
            $relist[$i]["create_time"]  = $sectionlist[$i]["create_time"];
            $relist[$i]["last_updated"] = $sectionlist[$i]["last_updated"];
            $relist[$i]["descript"]     = $sectionlist[$i]["descript"];//new
            $relist[$i]["status"]       = course_status::name($sectionlist[$i]["status"]);
        }
        if (empty($relist)) {
            $relist = 0;
        }
        $ret       = new stdClass;
        $ret->data = $relist;

        return $ret;
    }

    /*
     *获取group章节洗信息
     */
	public function planGroupSectionByCourseIds($courseIdsArr){
		$course_db   = new course_db();
		$sectionlist= $course_db->planGroupSectionByCourseIds($courseIdsArr);
		if ($sectionlist === false) return false;
		$sectionlist1 = $sectionlist->items;
		if (empty($sectionlist1)) {
			$sectionlist1 = 0;
		}
		$ret       = new stdClass;
		$ret->data = $sectionlist1;

		return $ret;
	}
    /*
     *更新章节信息
     */
    public function updatesection($section_id, $section)
    {
        $course_db  = new course_db;
        $section_id = (int)$section_id;
        $course_db->updateSection($section_id, $section);

        return $course_db;
    }

    /*
     *增加班级
     */
    public function addclass($cid, $dclass)
    {
        $time                   = date("Y-m-d H:i:s");
        $course_db              = new course_db;
        $class["fk_user_class"] = $dclass["user_class_id"];
        $class["fk_user"]       = $dclass["user_id"];
        $class["fk_course"]     = $cid;
        //		$class["fk_class"] = $dclass["class_id"];
        $class["name"] = $dclass["name"];
        //		$class["descript"] = $dclass["descript"];
        $class["type"]     = $dclass["type"];//1大班2小班
        $class["max_user"] = empty($dclass["max_user"]) ? '50' : $dclass["max_user"];
        $class["min_user"] = empty($dclass["min_user"]) ? '50' : $dclass["min_user"];
        //		$class["user_total"] = $class["user_total"];
        $class["status"]       = empty($dclass["status"]) ? '1' : $dclass["status"];
        $class["last_updated"] = $time;
        $class["create_time"]  = $time;
        $ret                   = $course_db->addclass($class);

        return $ret;
    }

    /*
     *列取班级信息
     */
    public function getclasslist($course_id)
    {
        $course_id  = (int)$course_id;
        $array_type = array("1" => "大班", "2" => "小班", "0" => "未设置班级类型");
        $course_db  = new course_db();
        $user_db    = new user_db();
        //change by panda 2015/12/30
        $classRet= $course_db->classlist($course_id);
        if ($classRet === false){
            return false;
        }
        $classlist=$classRet->items;
        $count = count($classlist);
        for ($i = 0; $i < $count; $i++) {
            $relist[$i]["class_id"]     = $classlist[$i]["pk_class"];
            $relist[$i]["name"]         = $classlist[$i]["name"];
            $relist[$i]["course_id"]    = $classlist[$i]["fk_course"];
            $relist[$i]["type"]         = $array_type[$classlist[$i]["type"]];
            $relist[$i]["max_user"]     = $classlist[$i]["max_user"];
            $relist[$i]["min_user"]     = $classlist[$i]["min_user"];
            $relist[$i]["descript"]     = $classlist[$i]["descript"];
            $relist[$i]["user_total"]   = $classlist[$i]["user_total"];
            $relist[$i]["teacher_id"]   = $classlist[$i]["fk_user_class"];
            $relist[$i]["teacher"]      = $user_db->getBasicUser($classlist[$i]["fk_user_class"]);
            $relist[$i]["create_time"]  = $classlist[$i]["create_time"];
            $relist[$i]["last_updated"] = $classlist[$i]["last_updated"];
            $relist[$i]["address"]      = !empty($classlist[$i]["address"])?$classlist[$i]['address']:'';
            $relist[$i]["regionLevel0"] = !empty($classlist[$i]["region_level0"])?$classlist[$i]["region_level0"]:0;
            $relist[$i]["regionLevel1"] = !empty($classlist[$i]["region_level1"])?$classlist[$i]["region_level1"]:0;
            $relist[$i]["regionLevel2"] = !empty($classlist[$i]["region_level2"])?$classlist[$i]["region_level2"]:0;
            $relist[$i]["status"]       = course_status::name($classlist[$i]["status"]);
            $class_id                   = $classlist[$i]["pk_class"];
        }
        if (empty($relist)) {
            $relist = 0;
        }
        $ret       = new stdClass;
        $ret->data = $relist;

        return $ret;
    }
    /*
     *根据条件列取班级信息
     */
    public function classListByCourseIds($user_id,$user_class_id,$cond,$orderby,$page,$length){
		$user_class_id = (int)$user_class_id;
		$user_id = (int)$user_id;
        $course_db  = new course_db();
        $classlist1  = $course_db->classListByCourseIds($user_id,$user_class_id,$cond,$orderby,$page,$length);
        if ($classlist1 === false) return false;
        $classlist  = $classlist1->items;
        if (empty($classlist)) {
            $classlist = 0;
        }
        $ret        = new stdClass;
        $ret->data  = $classlist;
        $ret->page  = $classlist1->page;
        $ret->size  = $classlist1->pageSize;
        $ret->total = $classlist1->totalPage;
        $ret->totalsize = $classlist1->totalSize;
        return $ret;
    }

    /*
     *根据条件列取班级信息
     */
    public function classListByCond($user_id,$user_class_id,$course_id){
        $course_id  = (int)$course_id;
		$user_class_id = (int)$user_class_id;
		$user_id = (int)$user_id;
        $course_db  = new course_db();
        $classlist  = $course_db->classListByCond($user_id,$user_class_id,$course_id)->items;
        if ($classlist === false) return false;
        if (empty($classlist)) {
            $classlist = 0;
        }
        $ret       = new stdClass;
        $ret->data = $classlist;
        return $ret;
    }

    /*
     *更新班级信息
     */
    public function updateclass($class_id, $class)
    {
        $course_db = new course_db;
        $class_id  = (int)$class_id;
        $ret       = $course_db->updateclass($class_id, $class);

        return $ret;
    }

    /*
     *更新排课信息
     */
    public function updateplan($plan_id, $plan)
    {
        $course_db = new course_db;
        $ret = $course_db->updateplan($plan_id, $plan);
        return $ret;
    }

    /*
     *增加排课信息
     */
    public function addplan($plan)
    {
        $time                       = date("Y-m-d H:i:s");
        $course_db                  = new course_db;
        $iplan["fk_user"]           = $plan["user_id"];
        $iplan["fk_user_plan"]      = $plan["user_plan_id"];//讲课老师
        $iplan["fk_course"]         = $plan["course_id"];
        $iplan["fk_section"]        = $plan["section_id"];
        $iplan["fk_class"]          = $plan["class_id"];
        $iplan["live_public_type"]  = $plan["live_public_type"];
        $iplan["video_public_type"] = $plan["video_public_type"];
        $iplan["video_trial_time"]  = $plan["video_trial_time"];
		if(!empty($plan["end_time"])){
			$iplan["end_time"] = $plan["end_time"];
		}
		$iplan["status"]       = 1;
        if(!empty($plan['start_time'])){
            $iplan["start_time"] = $plan["start_time"];
        }
        $iplan["last_updated"] = $time;
        $iplan["create_time"]  = $time;
        $ret                   = $course_db->addplan($iplan);
        $course_status         = course_live::add_plan_change_course($plan["course_id"]);
		$courseTeacherData     = [
			'fk_course'        => $plan["course_id"],
			'fk_user_teacher'  => $plan["user_plan_id"],
			'status'           => 1,
			'create_time'      => $time,
			'last_updated'     => $time
		];
		$course_teacher        = course_db_courseTeacherDao::add($courseTeacherData);
        return $ret;
    }
	/**
	 * 验证用户对plan权限
	 * 如果报名了，上课老师，机构管理员，机构所有者都可以通过
	 * @param int $user_id
	 * @param int $plan_id
	 * @param boolean &$apply 是否报名
	 * @param array &$video_trial=array("type"=>,"time"=>) 试看信息
	 * @return boolean
	 */
	public static function verifyPlan($user_id,$plan_id,&$apply=false,&$video_trial=array()){
		$apply=false;
		$video_trial=array();
		$course_db = new course_db;
		$plan_info = $course_db->getPlan($plan_id);
		if (empty($plan_info)) {
			return false;
		}
		//报名
		$course_user = $course_db->getCourseUserByFkuser($plan_info["course_id"], $user_id);
		if(!empty($course_user) && $plan_info["class_id"] == $course_user["fk_class"]){
			$apply=true;
			return true;
		}
		//上课老师，班主任，管理员可以看
		if($user_id == $plan_info['user_id'] || $user_id == $plan_info['user_plan_id']){
			return true;
		}
		//班主任
		$class_info = $course_db->getClass($plan_info["class_id"]);
		if(!empty($class_info) && $user_id == $class_info["fk_user_class"]){
			return true;
		}
		//判断管理员
		$course = $course_db->getCourse($plan_info['course_id']);
		$user_db=new user_db;
		if(empty($course))return false;

		//查询机构信息
		$org=$user_db->getOrgByUid($course['fk_user']);
		if(!empty($org) && $user_id==$org['user_owner_id']&&$org['status']==1){
			return true;
		}
		$special=$user_db->getTeacherSpecial($org['oid'],$user_id);
		if(!empty($special)&&$special['status']==1&&($special['role']==2 || $special['user_role']&0x04)){
			//管理员
			return true;
		}
		if(!empty($special)&&$special['status']==1&&($special['role']==1 || $special['user_role']&0x01)){
			//老师
			return true;
		}
		if(!empty($special)&&$special['status']==1&&($special['user_role']&0x02)){
			//助教
			return true;
		}
		//禁止观看
		if($plan_info['status'] == course_status::finished && -2 == $plan_info["video_public_type"]){
			$video_trial = array("video_public_type"=>$plan_info["video_public_type"] , "time"=>0);
			return false;
		}
		if($plan_info['status'] == course_status::finished){
			//点播试看
			if(2 == $plan_info["video_public_type"] && $plan_info["video_trial_time"] > 0){
				$video_trial = array("video_public_type"=>$plan_info["video_public_type"] , "time"=>$plan_info["video_trial_time"]);
				return true;
			}
			if(1 == $plan_info["video_public_type"]){
				$video_trial = array("video_public_type"=>$plan_info["video_public_type"] , "time"=>$plan_info["video_trial_time"]);
				return true;
			}
		 }elseif($plan_info['status'] == course_status::living && $plan_info["live_public_type"]>0){
			 $video_trial = array("live_public_type"=>$plan_info["live_public_type"]);
			 //直播公开
			 return true;
		 }else{
			 //没有开课
		 }
		return false;
	}

    /*
     *获取排课信息
     *				$cid   	课程id
     *				$page   页数
     *				$length 每页显示个数
     */
    public function getlistplan($cid, $orgUserId, $class_id, $user_plan_id, $section_id, $plan_id, $week, $allcourse, $order_by, $data, $page, $length)
    {
        $course_db = new course_db();
        $listplan1 = $course_db->planlist($cid, $orgUserId, $class_id, $user_plan_id, $section_id, $plan_id, $week, $allcourse, $order_by, $data, $page, $length);
        if ($listplan1 === false) return false;
        $listplan = $listplan1->items;
        $user_db  = new user_db();
        if ($listplan === false) return false;
        //计算老师信息
        $uids = array();
        $owners = array();
        foreach ($listplan as &$item) {
            $uids[] = $item['fk_user_class'];
            $uids[] = $item['fk_user_plan'];
            $owners[]=$item['fk_user_course'];
        }
        $user_infos_tmp = $user_db->getStudentUsers($uids);
        if ($user_infos_tmp === false) {
            return false;
        }
        foreach ($user_infos_tmp as $tmp) {
            $id             = $tmp['user_id'];
            $user_info[$id] = $tmp;
        }
        //获取subdomain
        $domains=$user_db->getSubdomainByUidArr($owners);
        $domain=array();
        if(!empty($domains->items)){
            foreach($domains->items as $dv){
                $domain[$dv['fk_user']]=$dv['subdomain'];
            }
        }
        //获取机构信息
        $orgList=$user_db->getOrgInfoByUidArr($owners);
        $orgInfo=array();
        if(!empty($orgList->items)){
            foreach($orgList->items as $ov){
                $orgInfo[$ov['user_owner']]['oid']=$ov['oid'];
                $orgInfo[$ov['user_owner']]['user_owner']=$ov['user_owner'];
                $orgInfo[$ov['user_owner']]['name']=$ov['name'];
                $orgInfo[$ov['user_owner']]['subname']=$ov['subname'];
                $orgInfo[$ov['user_owner']]['thumb_big']=$ov['thumb_big'];
                $orgInfo[$ov['user_owner']]['thumb_med']=$ov['thumb_med'];
                $orgInfo[$ov['user_owner']]['thumb_small']=$ov['thumb_small'];
                $orgInfo[$ov['user_owner']]['desc']=$ov['desc'];
                $orgInfo[$ov['user_owner']]['domain']=empty($domain[$ov['user_owner']])?'100':$domain[$ov['user_owner']];
            }
        }
        foreach ($listplan as &$plan) {
            //机构只需要获取一次
            //if (empty($org_user)) {
                //$org_user = $user_db->getOrgByUid($plan['fk_user_course']);
            //}
            $plan['plan_status']    = course_status::name($plan['plan_status']);
            $plan['course_status']  = course_status::name($plan['course_status']);
            $plan['section_status'] = course_status::name($plan['section_status']);
            $plan['user_course']    = !empty($orgInfo[$plan['fk_user_course']])?$orgInfo[$plan['fk_user_course']]:'';
            $plan['price']          = $plan['price'] / 100;
            $plan['price_market']   = $plan['price_market'] / 100;
            $tmp_id                 = $plan['fk_user_class'];
            if (isset($user_info[$tmp_id])) {
                $plan['user_class'] = $user_info[$tmp_id];            //获得班主任信息
            }
            //		$plan['user_class']= $user_info[$tmp_id];			//获得班主任信息
            $tmp_id = $plan['fk_user_plan'];
            if (isset($user_info[$tmp_id])) {
                $plan['user_plan'] = $user_info[$tmp_id];            //获得上课老师信息
            }
            $plan['user_plan_id']  = $plan['fk_user_plan'];
            $plan['user_class_id'] = $plan['fk_user_class'];
        }
        $ret        = new stdClass;
        $ret->data  = $listplan;
        $ret->page  = $listplan1->page;
        $ret->size  = $listplan1->pageSize;
        $ret->total = $listplan1->totalPage;
        $ret->totalSize = $listplan1->totalSize;

        return $ret;
    }

	public function planEndGroupByclassIds($courseIdsArr,$userId,$type,$ut){
		$course_db   = new course_db();
		$planlist= $course_db->planEndGroupByclassIds($courseIdsArr,$userId,$type,$ut);
		if ($planlist === false) return false;
		$planlist1 = $planlist->items;
		if (empty($planlist1)) {
			$planlist1 = 0;
		}
		$ret       = new stdClass;
		$ret->data = $planlist1;

		return $ret;
	}
	
	public function planEndGroupByclassIdsV2($courseIdsArr,$userId)
	{
		$course_db   = new course_db();
		$planlist= $course_db->planEndGroupByclassIdsV2($courseIdsArr,$userId);
		if ($planlist === false) return false;
		$planlist1 = $planlist->items;
		if (empty($planlist1)) {
			$planlist1 = 0;
		}
		$ret       = new stdClass;
		$ret->data = $planlist1;

		return $ret;
	}


    /*
     *获取所有的课程信息
     *
		 */
	public function getcourselist($page = 1, $length = 4, $fee, $oid, $grade_id, $status, $week, $shelf, $data){
        $course_db   = new course_db();
        $courselist1 = $course_db->courselist($page, $length, $fee, $oid, $grade_id, $status, $week, $shelf, false, null, $data);
        if (empty($courselist1->items)) {
            return false;
        }
        $courselist = $courselist1->items;
        $user_db    = new user_db();
        $count      = count($courselist);

        //{{{ for redis cache
        redis_api::useConfig("db_course");
        $course_ids = array();
        foreach ($courselist as $course) {
            $tmpid        = $course['pk_course'];
            $course_ids[] = "course_api::getcourselist.{$course['pk_course']}.v2";
        }
        $relist  = redis_api::mGet($course_ids);
        $allflag = true;
        if ($allflag) {
            foreach ($relist as $list) {
                if ($list === false) {
                    $allflag = false;
                    break;
                }
            }
        }
        //}}}
        if ($allflag === false) {
            $relist = array();
            for ($i = 0; $i < $count; $i++) {
                $user_id = $courselist[$i]["fk_user"];
                //获取用户信息
                $user_get = $user_db->getUser($user_id);
                $username = $user_get["name"];
                //获取创建者信息
                $userinfo = $user_db->getOrgByUid($user_id);
                $org_name = $username;//$org_info["name"];
                $org_id   = $user_id;//org_info["oid"];
                //获取完毕

                //获取课程旗下的所有班级信息
				$classlist = array();
                $course_id = $courselist[$i]["pk_course"];
                $classRet = $course_db->classList($course_id);
				if(!empty($classRet->items)){
					$classlist = $classRet->items;
				}
                $cllist    = array();
                if (!empty($classlist)) {
                    $count_2 = count($classlist);
                    for ($j = 0; $j < $count_2; $j++) {
                        $cllist[$j]["class_id"]   = $classlist[$j]["pk_class"];
                        $cllist[$j]["name"]       = $classlist[$j]["name"];
                        $cllist[$j]["user_total"] = $classlist[$j]["user_total"];
                        $cllist[$j]["max_user"]   = $classlist[$j]["max_user"];
                        $cllist[$j]["status"]     = $classlist[$j]["status"];
                        //获取班主任信息
                        $cllist[$j]["user_id"]   = $classlist[$j]["fk_user_class"];
                        $teacher                 = $user_db->getBasicUser($classlist[$j]['fk_user_class']);
                        $cllist[$j]["teacher"]   = $teacher;
                        $cllist[$j]["user_name"] = $teacher['name'];
                    }
                }

                $price = $price_market = 0;

                $relist[$i]["course_id"]         = $courselist[$i]["pk_course"];
                $relist[$i]["cate_id"]           = $courselist[$i]["fk_cate"];
                $relist[$i]["type_id"]           = $courselist[$i]["type"];
                $relist[$i]["grade_id"]          = $courselist[$i]["fk_grade"];
                $relist[$i]["user_id"]           = $courselist[$i]["fk_user"];
                $relist[$i]["title"]             = $courselist[$i]["title"];
                $relist[$i]["tags"]              = $courselist[$i]["tags"];
                $relist[$i]["descript"]          = $courselist[$i]["descript"];
                $relist[$i]["thumb_big"]         = $courselist[$i]["thumb_big"];
                $relist[$i]["thumb_med"]         = $courselist[$i]["thumb_med"];
                $relist[$i]["thumb_small"]       = $courselist[$i]["thumb_small"];
                $relist[$i]["start_time"]        = $courselist[$i]["start_time"];
                $relist[$i]["end_time"]          = $courselist[$i]["end_time"];
                $relist[$i]["public_type"]       = $courselist[$i]["public_type"];
                $relist[$i]["fee_type"]          = $courselist[$i]["fee_type"];
                $relist[$i]["user_total"]        = $courselist[$i]["user_total"];
                $relist[$i]["max_user"]          = $courselist[$i]["max_user"];
                $relist[$i]["min_user"]          = $courselist[$i]["min_user"];
                $relist[$i]["status"]            = course_status::name($courselist[$i]["status"]);
                $relist[$i]["top"]               = $courselist[$i]["top"];
                $relist[$i]["admin_status"]      = course_adminstatus::name($courselist[$i]["admin_status"]);
                $relist[$i]["create_time"]       = $courselist[$i]["create_time"];
                $relist[$i]["last_updated"]      = $courselist[$i]["last_updated"];
                $relist[$i]["user"]["user_name"] = $username;
                //{{{如果是收费
                if ($courselist[$i]["fee_type"] > 0) {
                    $relist[$i]["fee"]["price"]        = $courselist[$i]["price"] / 100;
                    $relist[$i]["fee"]["price_market"] = $courselist[$i]["price_market"] / 100;
                } else {
                    $relist[$i]["fee"]["price"]        = 0;
                    $relist[$i]["fee"]["price_market"] = 0;
                }
                //}}}
                $relist[$i]["user"]["user_id"] = $user_id;
                $relist[$i]["org"]["org_id"]   = $org_id;
                $relist[$i]["org"]["org_name"] = $org_name;
                $relist[$i]["class"]           = $cllist;
                $relist[$i]['sort']            = $courselist[$i]['sort'];
            }
            if (empty($relist)) {
                $relist = 0;
            } else {
                //{{{
                $cache_data = array();
                foreach ($relist as $i => $course) {
                    $key              = "course_api::getcourselist.{$course['course_id']}.v2";
                    $cache_data[$key] = $course;

                }
                redis_api::mSet($cache_data);
                //}}}
            }
        } else {
        }
		
        $ret        = new stdClass;
        $ret->data  = $relist;
        $ret->page  = $courselist1->page;
        $ret->size  = $courselist1->pageSize;
        $ret->total = $courselist1->totalPage;

        return $ret;

    }


		public function MgrcourseList($cond,$orderBy,$groupBy,$page,$length){
        $course_db   = new course_db();
        $courselistTmp = $course_db->MgrcourseList($cond,$orderBy,$groupBy,$page, $length);
/*
	if (empty($courselistTmp->items)) {
		return false;
	}
 */
		$courselistdata = $courselistTmp->items;
		//$user_db    = new user_db();
		$ret        = new stdClass;
		$ret->data  = $courselistdata;
		$ret->page  = $courselistTmp->page;
		$ret->size  = $courselistTmp->pageSize;
		$ret->total = $courselistTmp->totalPage;

		//return $courselistTmp;
		return $ret;

    }

	public function courseLikeList($user_id,$course_ids,$data,$orderby){
		$course_db       = new course_db();
		$courseList1 = $course_db->courseLikeList($user_id, $course_ids, $data,$orderby);
		if ($courseList1 === false) return false;
		$courseList = $courseList1->items;
		$ret            = new stdClass;
		if (empty($courseList)) {
			$courseList = 0;
		}
		$ret->data  = $courseList;
		//$ret->page  = $courseList->page;
		//$ret->size  = $courseList->pageSize;
		//$ret->total = $courseList->totalPage;
		return $ret;
	}


    /*
     *获取机构信息
     *				$oid    机构id
     *				$page   页数
     *				$length 每页显示个数
     */
    public function getlistorg($oid = null, $page = null, $length = null)
    {
        $user_db   = new user_db();
        $list_org1 = $user_db->listorg($oid, $page, $length);
        $list_org  = $list_org1->items;
        if ($listorg === false) return false;
        $count = count($listorg);
        for ($i = 0; $i < $count; $i++) {
            $relist[$i]["org_id"]       = $listorg[$i]["pk_org"];
            $relist[$i]["user_owner"]   = $listorg[$i]["fk_user_owner"];
            $relist[$i]["namme"]        = $listorg[$i]["name"];
            $relist[$i]["thumb_big"]    = $listorg[$i]["thumb_big"];
            $relist[$i]["thumb_med"]    = $listorg[$i]["thumb_med"];
            $relist[$i]["thumb_small"]  = $listorg[$i]["thumb_small"];
            $relist[$i]["desc"]         = $listorg[$i]["desc"];
            $relist[$i]["status"]       = $listorg[$i]["status"];
            $relist[$i]["create_time"]  = $listorg[$i]["create_time"];
            $relist[$i]["last_updated"] = $listorg[$i]["last_updated"];
            //			$relist[$i]["status"]=$array_status[$listorg[$i]["status"]];
            // "admin_status",这个字段数据库没有 意义：管理员审核状态
        }
        $ret = new stdClass;
        if (empty($relist)) {
            $relist = 0;
        }
        $ret->data  = $relist;
        $ret->page  = $listorg1->page;
        $ret->size  = $listorg1->pageSize;
        $ret->total = $listorg1->totalPage;

        return $ret;
    }

    private static $reg_array = array(
        //	"pk_course_user"=>"course_user_id",
        "fk_course" => "course_id",
        "fk_user"   => "uid",
        "fk_class"  => "class_id",
        "status"    => "status",
        //	"create_time"=>"create_time",
        //	"last_updated"=>"last_updated",
    );

    public function addRegistration($reg_data)
    {
        $course_db = new course_db;

        return $course_db->addRegistration($reg_data);
    }

    /*
     * 调班，需要修正报名用户数
     */
    public function updateRegClass($course_user_id, $upregdata)
    {
        $course_db = new course_db;
        $course_db->updateregclass($course_user_id, $upregdata);

        return $course_db;
    }


    public function listRegistration($course_id, $class_id, $uid, $user_owner, $page = null, $length = null)
    {
        $course_db = new course_db();
        $user_db   = new user_db();
        $list_reg1 = $course_db->ListRegistration($course_id, $class_id, $uid, $user_owner, $page, $length);
        if (empty($list_reg1->items)) return false;
        $uids = array();
        foreach ($list_reg1->items as &$item) {
            $uids[] = $item['uid'];
		}
		$user_infos = $user_db->getStudentUsers($uids);
		if(!empty($user_infos)){
			foreach($user_infos as $k=>$v){
				$user_infos_arr[$user_infos[$k]["user_id"]] = $v;
			}
		}
		foreach ($list_reg1->items as &$item) {
			if(!empty($user_infos_arr[$item["uid"]])){
				$item['user_info'] = $user_infos_arr[$item["uid"]];
			}
		}

		$list_reg = $list_reg1->items;
        $ret      = new stdClass;
        if (empty($list_reg)) {
            $list_reg = 0;
        }
        $ret->data  = $list_reg;
        $ret->page  = $list_reg1->page;
        $ret->size  = $list_reg1->pageSize;
        $ret->total = $list_reg1->totalPage;

        return $ret;
    }
    public function listRegistrationBycond($course_ids, $class_id, $uids, $user_owner, $page, $length){
        $course_db = new course_db();
        $user_db   = new user_db();
        $list_reg1 = $course_db->ListRegistrationbycond($course_ids, $class_id, $uids, $user_owner, $page, $length);
        if (empty($list_reg1->items)) return false;
        $stuids = array();
        foreach ($list_reg1->items as &$item) {
            $stuids[] = $item['uid'];
        }
		$user_infos = $user_db->getStudentUsers($stuids);
		foreach ($list_reg1->items as &$item) {
			if(!empty($user_infos)){
				foreach ($user_infos as $user_info) {
					if ($user_info['user_id'] == $item['uid']){
						$item['user_info'] = $user_info;
					}
				}
			}
		}
        $list_reg = $list_reg1->items;
        $ret      = new stdClass;
        if (empty($list_reg)) {
            $list_reg = 0;
        }
        $ret->data  = $list_reg;
        $ret->page  = $list_reg1->page;
        $ret->size  = $list_reg1->pageSize;
        $ret->total = $list_reg1->totalPage;
        $ret->totalSize = $list_reg1->totalSize;

        return $ret;
    }
    public function listPlanUser($plan_id)
    {
        $course_db = new course_db();

        return $course_db->listPlanUser($plan_id);
    }

    public static function setPlanStatus($plan_id, $new_status)
    {
        $status = course_status::initial;
        if (is_numeric($new_status)) {
            if (!empty(course_status::name($new_status))) {
                $status = $new_status;
            } else {
                $status = course_status::initial;
            }
        } else {
            $status = course_status::key($new_status);
            if ($status === false) {
                $status = course_status::initial;
            }
        }
        $db = new course_db;
        $db->updatePlanStatus($plan_id, $status);
        //修改对应的状态
        $plan_info = $db->getPlanFromMainDb($plan_id);
        if (empty($plan_info)) {
            return false;
        }
		$courseInfo = $db->getCourse($plan_info['course_id']);
        if ($status == course_status::living) {
            //把对应的plan,section,course都设置为进行中
            $db->updateCourseStatus($plan_info['course_id'], $status);
            $db->updateSectionStatus($plan_info['section_id'], $status);
            $db->updateClassStatus($plan_info['class_id'], $status);
			self::updateClassProgress($plan_info['class_id'],$courseInfo['type']);
        } elseif ($status == course_status::finished) {
            //取出章节下的所有plan，如果都完成，就章节完成
            $plans = $db->planList($plan_info['course_id'], 0, 0, 0, $plan_info['section_id'], 0);
            if (!empty($plans->items)) {
                $finished = true;
                foreach ($plans->items as $item) {
                    if ($item['plan_status'] != course_status::finished) {
                        $finished = false;
                        break;
                    }
                }
                if ($finished === false) {
                    //没有上完课把直播状态修改成普通状态
                    $db->updateSectionStatus($plan_info['section_id'], course_status::normal);
                    $db->updateCourseStatus($plan_info['course_id'], course_status::normal, 2);
                    $db->updateClassStatus($plan_info['class_id'], course_status::normal, 2);
                } else {
                    $db->updateSectionStatus($plan_info['section_id'], $status);
                    //取出课程下所有章节，如果都完成，设置课程为完成
                    $sections = $db->sectionList($plan_info['course_id']);
                    if (!empty($sections->items)) {
                        $finished = true;
                        foreach ($sections->items as $item) {
                            if ($item['status'] != course_status::finished) {
                                $finished = false;
                                break;
                            }
                        }
                        if ($finished === false) {
                            $db->updateCourseStatus($plan_info['course_id'], course_status::normal, 2);
                            $db->updateClassStatus($plan_info['class_id'], course_status::normal, 2);
                        } else {
                            $db->updateCourseStatus($plan_info['course_id'], $status, 3);
                            $db->updateClassStatus($plan_info['class_id'], $status, 3);
                        }
                    }
                }
            }
			if(!empty($courseInfo)){
				self::updateClassProgress($plan_info['class_id'],$courseInfo['type']);
			}
        }

        return true;
    }
	
	public static function updateClassProgress($classId,$type){
		$courseDb = new course_db;
		$planList = $courseDb->getPlansByClassId($classId);
		$currSort = 0;
		if(empty($planList)){
			return false;
		}
		$currPlanId = 0;
		$precentProgress = 0;
		$planCount = count($planList);
		$finishSection = 0;
		$temp = array();
		$sort = array();
		$finishStime = 0;
		foreach($planList as $pk=>$po){
			$temp[$po['status']][] = $po;
			$sort[$po['plan_id']]  = $pk+1;
		}
		if(!empty($temp[2])){
			$currPlan = $temp[2][0];
			$currPlanId = $temp[2][0]['plan_id'];
			$finishSection = $sort[$currPlanId];
		}elseif(!empty($temp[3])){
			$finishSection = count($temp[3]);
			$currPlan = $temp[3][$finishSection-1];
			$currPlanId = $temp[3][$finishSection-1]['plan_id'];
			$finishStime = strtotime($currPlan['start_time']);
		}
		if(!empty($temp[1]) && !empty($temp[3]) ){
			foreach($temp[1] as $to){
				if($type == 1){
					$stime = strtotime($to['start_time']);
					if($stime < $finishStime){
						$finishSection += 1;
					}
				}elseif($type == 2){
					if($to['plan_id'] < $currPlanId){
						$finishSection += 1;
					}
				}	
			}
		}
		if(empty($temp[2]) && empty($temp[3]) && !empty($temp[1])){
			$currPlanId = 0;
			$finishSection = 0;
		}
		$precentProgress = floor(($finishSection/$planCount)*100);
		$data['progress_plan'] = $currPlanId;
		$data['progress_percent'] = $precentProgress;
		$ret = $courseDb->updateClass($classId,$data);
		if($ret !== false){
			return true;
		}else{
			return false;
		}
	}
	
    public function updateDiscountCodeUsed($order_id, $status)
    {
        $course_db = new course_db;
        $used      = $course_db->getDiscountCodeUsedByOrderId($order_id);
        if (!$used || $used["status"] == $status) {
            return;
        }
        $course_db->setStatusForDiscountCodeUsedByOrderId($order_id, $status);
        $used2 = $course_db->getDiscountCodeUsedsByCodeId($used["discount_code_id"], 1, 1);
        $course_db->setUsedNumForDiscountCodeById($used["discount_code_id"], $used2->totalSize);
    }

    public function addCourseTop($cid)
    {
        $course_db = new course_db;

        return $course_db->addCourseTop($cid);
    }

    public function delCourseTop($cid)
    {
        $course_db = new course_db;

        return $course_db->delCourseTop($cid);
    }

    /*
     *增加题目和答案
     */
    public function addCoursePlanExam($data)
    {
        $time    = date("Y-m-d H:i:s");
        $datain  = array();
        $arrkeys = array(
            "plan_id"     => "fk_plan",
            "question_id" => "fk_question",
            "type"        => "type",
            "q_desc"      => "q_desc",
            "q_desc_img"  => "q_desc_img",
            "a"           => "a",
            "b"           => "b",
            "c"           => "c",
            "d"           => "d",
            "e"           => "e",
            "answer_a_id" => "fk_answer_a",
            "answer_b_id" => "fk_answer_b",
            "answer_c_id" => "fk_answer_c",
            "answer_d_id" => "fk_answer_d",
            "answer_e_id" => "fk_answer_e",
            "answer"      => "answer",
            "order_no"    => "order_no",
            "status"      => "status",
        );
        foreach ($arrkeys as $arrk => $arrv) {
            if (isset($data->$arrk)) {
                $datain[$arrv] = $data->$arrk;
            }
        }
        $array_type = array(
            "radio"    => "1",    //单选题
            "multiple" => "2",//多选题
            "app"      => "3",        //应用题
        );
        if (isset($data->type)) {
            $datain["type"] = $array_type[$data->type];
        }
        $datain["create_time"] = $time;
        $course_db             = new course_db;
        $ret                   = $course_db->addcourseplanexam($datain);

        return $ret;
    }

    /*
     * 修改出题信息
     */
    public function updateCoursePlanExam($examid, $data)
    {
        $course_db = new course_db;
        $arrkeys   = array(
            "q_desc"      => "q_desc",
            "q_desc_img"  => "q_desc_img",
            "a"           => "a",
            "b"           => "b",
            "c"           => "c",
            "d"           => "d",
            "e"           => "e",
            "answer_a_id" => "fk_answer_a",
            "answer_b_id" => "fk_answer_b",
            "answer_c_id" => "fk_answer_c",
            "answer_d_id" => "fk_answer_d",
            "answer_e_id" => "fk_answer_e",
            "answer"      => "answer",
            "order_no"    => "order_no",
            "status"      => "status",
        );
        foreach ($arrkeys as $arrk => $arrv) {
            if (isset($data->$arrk)) {
                $datain[$arrv] = $data->$arrk;
            }
        }
        $ret = $course_db->updateCoursePlanExam($examid, $datain);

        return $ret;
    }

    /*
     * 删除出题信息
     */
    public function delCoursePlanExam($examids)
    {
        $course_db = new course_db;
        $ret       = $course_db->delCoursePlanExam($examids);

        return $ret;
    }

    /*
     *列取出题信息
     */
    public function coursePlanExamList($data, $page, $length, $item, $orderby)
    {
        $course_db = new course_db();
        $retlist   = $course_db->coursePlanExamList($data, $page, $length, $item, $orderby);
        $retdata   = $retlist->items;
        if ($retlist === false) return false;
        if (empty($retdata)) {
            $retdata = 0;
        }
        $ret        = new stdClass;
        $ret->data  = $retdata;
        $ret->page  = $retlist->page;
        $ret->size  = $retlist->pageSize;
        $ret->total = $retlist->totalPage;

        return $ret;
    }

    /*
     *增加附件信息
	 *planId 改为了class_id
     */
    public function addPlanAttach($planId, $datain)
    {
        if (empty($planId)) {
            return false;
        }
        $data        = array();
        $arrayAttach = array(
            "title"    => "title",
            "atttach"  => "attach",
            "order_no" => "order_no",
            "type"     => "type",
            "thumb"    => "thumb",
            "status"   => "status",
            "fk_user"  => "fk_user"
        );
        $time        = date("Y-m-d H:i:s");
        foreach ($arrayAttach as $key => $value) {
            if (isset($datain[$value])) {
                $data[$key] = $datain[$value];
            }
        }
        $data["fk_plan"]      = 0;
        $data["fk_class"]      = $planId;
        $data["last_updated"] = $time;
        $data["create_time"]  = $time;
        $course_db            = new course_db;
        $ret                  = $course_db->addPlanAttach($data);

        return $ret;
    }

    /*
     * 删除planAttach信息
     */
    public function delPlanAttach($planAttIds){
        $course_db = new course_db;
        $ret       = $course_db->delPlanAttach($planAttIds);

        return $ret;
    }

    public function getPlanAttach($planAttId)
    {
        if (empty($planAttId)) {
            return false;
        }
        $course_db = new course_db();
        $ret       = $course_db->getPlanAttach($planAttId);
        if (empty($ret)) {
            return false;
        }

        return $ret;
    }

    public function listPlanAttach($cond, $page, $length)
    {
        $course_db       = new course_db();
        $listplanAttach1 = $course_db->listPlanAttach($cond, $page, $length);
        if ($listplanAttach1 === false) return false;
        $listplanAttach = $listplanAttach1->items;
        $ret            = new stdClass;
        if (empty($listplanAttach)) {
            $listplanAttach = 0;
        }
        $ret->data  = $listplanAttach;
        $ret->page  = $listplanAttach1->page;
        $ret->size  = $listplanAttach1->pageSize;
        $ret->total = $listplanAttach1->totalPage;

        return $ret;
    }

    public function updatePlanAttach($planAttId, $datain)
    {
        if (empty($planAttId)) {
            return false;
        }
        $data        = array();
        $arrayAttach = array(
            "title"    => "title",
            "atttach"  => "attach",
            "order_no" => "order_no",
            "type"     => "type",
            "thumb"    => "thumb",
            "status"   => "status",
        );
        foreach ($arrayAttach as $key => $value) {
            if (isset($datain[$value])) {
                $data[$key] = $datain[$value];
            }
        }
        $course_db = new course_db;
        $ret       = $course_db->updatePlanAttach($planAttId, $data);

        return $ret;
    }

	public static function getAttrAndValueByCateId($cateId){
		$courseDb = new course_db;
		$attrRet = $courseDb->getAttrAndValueByCateId($cateId);
		$attrList = array();
		if(!empty($attrRet->items)){
			$attrTemp = array();
			foreach($attrRet->items as $attr){
				$attrList[$attr['attr_id']]['attr_id'] = $attr['attr_id'];
				$attrList[$attr['attr_id']]['cate_id'] = $attr['cate_id'];
				$attrList[$attr['attr_id']]['name'] = $attr['name'];
				$attrList[$attr['attr_id']]['name_display'] = $attr['name_display'];
				$attrTemp['value_name'] = $attr['value_name'];
				$attrTemp['attr_value_id'] = $attr['attr_value_id'];
				$attrList[$attr['attr_id']]['attr_value'][] = $attrTemp;
			}
		}
		return $attrList;
	}

	public static function getCourseAttrValueByCourseId($courseId){
		$courseDb = new course_db;
		$attrRet = $courseDb::getCourseAttrValueByCourseId($courseId);
		$attrList = array();
		if(!empty($attrRet->items)){
			$attrTemp = array();
			$attrValueIds = array();
			$attrValueNames = array();
			foreach($attrRet->items as $attr){
				$attrList[$attr['attr_id']]['attr_id'] = $attr['attr_id'];
				$attrList[$attr['attr_id']]['course_id'] = $attr['course_id'];
				$attrTemp['value_name'] = $attr['value_name'];
				$attrTemp['attr_value_id'] = $attr['attr_value_id'];
				$attrTemp['course_id'] = $attr['course_id'];
				$attrList[$attr['attr_id']]['attr_value'][] = $attrTemp;
				$attrValueNames[$attr['attr_id']][] = $attr['value_name'];
				$attrValueIds[$attr['attr_id']][] = $attr['attr_value_id'];
			}
			foreach($attrList as &$ao){
				$ao['value_name'] = implode(',',$attrValueNames[$ao['attr_id']]);
				$ao['attr_value_id']   = implode(',',$attrValueIds[$ao['attr_id']]);
			}
		}
		return $attrList;
	}

	/*
	 *获取分类属性
	 *@params $cate_id int 分类id
	 *@autor binbin
	 */
    public function listAttrValueByCateId($cateId){
		if(empty($cateId)){
			return false;
		}
		$course_db = new course_db;
		$retGetAttrByCateId = $course_db->getAttrByCateId($cateId);
		if( !empty($retGetAttrByCateId->items) ) {
			foreach($retGetAttrByCateId->items as $k=>$v){
				$attrIds[] = $v["pk_attr"];
			}
		}else{
			return false;
		}
		//$attrIds=array("1","2");
        $retAttr = course_db::listAttrValueByAttrIds($attrIds);
		if(empty($retAttr->items) ) {
			return false;
		}
		if( !empty($retGetAttrByCateId->items) ) {
			foreach($retGetAttrByCateId->items as $k=>&$v){
				foreach($retAttr->items as $ak=>$av)
					if($v['pk_attr']==$av['attr_id']){
						$v["attr_value"][] = $av;
					}
			}
		}
        return $retGetAttrByCateId;
    }

	public static function addMappingCourseAttrValue($attrValueIds,$courseId){

		if(empty($attrValueIds) || empty($courseId)){
			return false;
		}
		$attrValueIdArr = explode(',',$attrValueIds);
		$newAttrValue = $attrValueIdArr;
		$delAttrValue = array();
		$existAttrValue = array();
		$addAttrValue = array();
		$courseDb = new course_db;
		$courseAttrValue = $courseDb->getCourseAttrValueByCourseId($courseId);
		if(!empty($courseAttrValue->items)){
			foreach($courseAttrValue->items as $value){
				if(!in_array($value['attr_value_id'],$newAttrValue)){
					$delAttrValue[] = $value['attr_value_id'];
				}else{
					$existAttrValue[] = $value['attr_value_id'];
				}
			}
			if(!empty($existAttrValue)){
				$addAttrValue = array_diff($newAttrValue,$existAttrValue);
			}else{
				$addAttrValue = $newAttrValue;
			}
			if(!empty($delAttrValue)){
				$delAttrValueIds = implode(',',$delAttrValue);
				$retDel = $courseDb->delMappingCourseAttrValueByCidAndAvids($delAttrValueIds,$courseId);
			}
			if(!empty($addAttrValue)){
				foreach($addAttrValue as $attrValueId){
					$addData = array(
						'fk_attr_value' => $attrValueId,
						'fk_course'     => $courseId,
						'create_time'   => date('Y-m-d H:i:s', time()),
					);
					$retAdd = $courseDb->addMappingCourseAttrValue($addData);
				}
			}
			if( !empty($addAttr) ){
				$ret = $retAdd;
			}elseif( !empty($delAttr) ){
				$ret = $retDel;
			}else{
				$ret = true;
			}
			if($ret === false){
				return false;
			}else{
				return $ret;
			}
		}else{
			foreach($attrValueIdArr as $attrValueId){
				$addData = array(
					'fk_attr_value' => $attrValueId,
					'fk_course'     => $courseId,
					'create_time'   => date('Y-m-d H:i:s', time()),
				);
				$ret = $courseDb->addMappingCourseAttrValue($addData);
			}
			return $ret;
		}
	}
	/*
	 *获取用户今天以后的直播课程
	 */
	public static function getUserLivingCourseList($userId,$type,$startTime,$ownerId){
		if(empty($userId) || empty($type) || empty($startTime)){
			return false;
		}
		$registRet = course_db_courseUserDao::getUserLivingCourse($userId,$type,$startTime,$ownerId);
		if(!empty($registRet->items)){
			$regCourse = $registRet->items;
			foreach($registRet->items as $reg){
				$courseIdArr[] = $reg['fk_course'];
			}
			$stat_db = new stat_db;
			$courseIdStr = implode(',',$courseIdArr);
			$courseStat = $stat_db->listCourseStatByIds($courseIdStr);
			$section = array();
			if(!empty($courseStat->items)){
				foreach($courseStat->items as $stat){
					$section[$stat['fk_course']] = $stat['section_count'];
				}

			}
			foreach($registRet->items as $course){
				if(!empty($section[$course['fk_course']])){
					$course['section_count'] = $section[$course['fk_course']];
				}else{
					$course['section_count'] = 0;
				}
				$ret[] = $course;
			}
		}else{
			$ret = false;
		}
		return $ret;
	}

	/*
	 *获取用户报名课程列表
	 */
	public static function getUserRegisterCourseList($userId,$page,$length,$ownerId,$title,$type=0){
		if(empty($userId)){
			return false;
		}
		$registRet = course_db_courseUserDao::getUserRegisterCourseList($userId,$page,$length,$ownerId,$title,$type);
		if(!empty($registRet->items)){
			$regCourse = $registRet->items;
			foreach($registRet->items as $reg){
				$courseIdArr[$reg['fk_course']]  = $reg['fk_course'];
				$teacherIdArr[$reg['fk_user_class']] = $reg['fk_user_class'];
				$ownerIdArr[$reg['fk_user_owner']]   = $reg['fk_user_owner'];
			}

			//获取机构信息
			$orgInfo = user_db::getOrgInfoByUidArr($ownerIdArr);
			$orgniztion = array();
			if(!empty($orgInfo->items)){
				foreach($orgInfo->items as $org){
					$orgniztion[$org['user_owner']] = $org;
				}
			}
			//获取章节总数
			$stat_db = new stat_db;
			$courseIdStr = implode(',',$courseIdArr);
			$courseStat = $stat_db->listCourseStatByIds($courseIdStr);
			$section = array();
			if(!empty($courseStat->items)){
				foreach($courseStat->items as $stat){
					$section[$stat['fk_course']] = $stat['section_count'];
				}
			}
			//获取班主任信息
			$teacherIdStr = implode(',',$teacherIdArr);
			$teacherInfo  = user_db::listUsersByUserIds($teacherIdStr);
			$teacher = array();
			if(!empty($teacherInfo->items)){
				foreach($teacherInfo->items as $to){
					$teacher[$to['pk_user']] = $to;
				}
			}
			foreach($registRet->items as $course){
				if(!empty($section[$course['fk_course']])){
					$course['section_count'] = $section[$course['fk_course']];
				}else{
					$course['section_count'] = 0;
				}
				if(!empty($teacher[$course['fk_user_class']])){
					$course['teacher_info'] = $teacher[$course['fk_user_class']];
				}else{
					$course['teacher_info'] = '';
				}
				if(!empty($orgniztion[$course['fk_user_owner']])){
					$course['org_info'] = $orgniztion[$course['fk_user_owner']];
				}else{
					$course['org_info'] = '';
				}
				$ret['data'][] = $course;
			}
			$ret['page'] = $registRet->page;
			$ret['pageSize']  = $registRet->pageSize;
			$ret['totalPage'] = $registRet->totalPage;
			$ret['totalSize'] = $registRet->totalSize;
		}else{
			$ret = false;
		}
		return $ret;
	}
	
	public static function getPlanListByOwner($org_owner,$params){
		if(empty($org_owner)){
			return false;
		}
		$course_db = new course_db;
		$planList = $course_db->getPlanListByOwner($org_owner,$params);
		if(!empty($planList->items)){
			foreach($planList->items as $po){
				$courseIdArr[$po['fk_course']] = $po['fk_course']; 
			}
			//获取章节总数
			$stat_db = new stat_db;
			$courseIdStr = implode(',',$courseIdArr);
			$courseStat = $stat_db->listCourseStatByIds($courseIdStr);
			$section = array();
			if(!empty($courseStat->items)){
				foreach($courseStat->items as $stat){
					$section[$stat['fk_course']] = $stat['section_count'];
				}
			}
			foreach($planList->items as &$vo){
				if(!empty($section[$vo['fk_course']])){
					$vo['section_count'] = $section[$vo['fk_course']];
				}else{
					$vo['section_count'] = 0;
				}
			}
			return $planList->items;
		}else{
			return false;
		}
	}
}
