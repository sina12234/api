<?php

/**
 * @docs http://wiki.gn100.com/doku.php?id=docs:api:comments
 * @link https://wiki.gn100.com/doku.php?id=web:message
 **/
class message_type
{

    const text = 1;
    const ask = 2;
    const cancel = 3;
    const agree = 4;
    const refuse = 5;
    const call = 6;
    const reply = 7;
    const stop = 8;
    const asking = 9;
    const good = 100;
    const online = 200;
    const offline = 201;
    const start = 300;
    const close = 301;
    const pattern_normal = 400;
    const pattern_reply = 401;
    const pattern_notalk = 402;
    const reply_text = 500;
    const reply_text_display = 501;
    const class_number = 600;
    const microphone_test = 700;
    const microphone_succeed = 701;
    const microphone_fail = 702;
    const fullscreen = 800;
    const ask_cancel = 1002;
    const agree_refuse = 1004;
    const on_off_line = 1006;
    const start_close = 1008;
    const pattern = 1010;
    const microphone_result = 1012;
    const single_notalk = 1014;
    const issue_ask = 1016;        //老师出题
    const issue_publish = 1017;        //老师公布答案
    const issue_answer = 1018;        //学生答题
    const issue_cancel = 1019;//取消答题
    const request_eval = 1020;
    const delete_text = 1022;     //老师删除一个学生的发言
    const score_info = 1024;     //点赞引起的加分信息
    const camera    =   1026;   //学生发言摄像头信息
    const modify_student = 1200;

    /**
     * source web
     */
    const SOURCE_WEB = 20000;

    /**
     * source android
     */
    const SOURCE_ANDROID = 20001;

    /**
     * source ios
     */
    const SOURCE_IOS = 20002;

    /**
     * source pc
     */
    const SOURCE_PC = 20003;

    /**
     * source wei_xin
     */
    const SOURCE_WEI_XIN = 20004;

    /**
     * system message
     */
    const SYSTEM = 10000;

    /**
     * system class remind
     */
    const SYSTEM_CLASS_REMIND = 10001;

    /**
     * system Interactive
     */
    const SYSTEM_INTERACTIVE = 10002;

    /**
     * Contact information
     */
    const SYSTEM_CONTACT_INFORMATION = 10003;
    const ORG_DATA_INFO_VERIFY = 10010;
    const ORG_JOIN_VERIFY = 10011;
    const WITHDRAWALS_VERIFY = 10015;
    const BANK_CARD_VERIFY = 10016;
    const OPEN_VIP = 10017;
    const RESELL_NOTICE = 10018;

    /**
     * @var array
     */
    static $messageType = [
        self::SYSTEM,
        self::SYSTEM_CLASS_REMIND,
        self::SYSTEM_INTERACTIVE,
        self::SYSTEM_CONTACT_INFORMATION,
        self::ORG_DATA_INFO_VERIFY,
        self::ORG_JOIN_VERIFY,
        self::WITHDRAWALS_VERIFY,
        self::BANK_CARD_VERIFY,
        self::OPEN_VIP
    ];

    /**
     * @var array
     */
    static $source = [
        self::SOURCE_WEB,
        self::SOURCE_ANDROID,
        self::SOURCE_IOS,
        self::SOURCE_PC
    ];


    public static function isAutoSetUserTo($type)
    {
        switch ($type) {
            case message_type::ask:
            case message_type::cancel:
            case message_type::reply:
            case message_type::microphone_succeed:
            case message_type::microphone_fail:
            case message_type::fullscreen:
            case message_type::ask_cancel:
            case message_type::microphone_result:
            case message_type::issue_answer:
                return true;
            default:
                return false;
        }
    }

    public static function onlyTeacher($type)
    {
        switch ($type) {
            case message_type::agree:
            case message_type::refuse:
            case message_type::call:
            case message_type::stop:
            case message_type::asking:
            case message_type::good:
            case message_type::start:
            case message_type::close:
            case message_type::pattern_normal:
            case message_type::pattern_reply:
            case message_type::pattern_notalk:
            case message_type::reply_text_display:
            case message_type::microphone_test:
            case message_type::agree_refuse:
            case message_type::start_close:
            case message_type::pattern:
            case message_type::single_notalk:
            case message_type::issue_ask:
            case message_type::issue_publish:
            case message_type::issue_cancel:
            case message_type::delete_text:
            case message_type::score_info:
            case message_type::request_eval:
                return true;
            default:
                return false;
        }
    }

    public static function needUsername($type)
    {
        switch ($type) {
            case message_type::text:
            case message_type::ask:
            case message_type::reply_text:
            case message_type::reply_text_display:
                return true;
            default:
                return false;
        }
    }

    public static function typeCategory($type)
    {
        switch ($type) {
            case message_type::good:
                return "good";
            case message_type::text:
            case message_type::reply_text:
                return "text";
            default:
                return "signal";
        }
    }
}
