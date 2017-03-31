<?php

class user_value
{
    public function pageAdd()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $r = api_func::isValidId(['userId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);
        $iosCoin = isset($params['iosCoin']) ? $params['iosCoin'] * 100 : 0;

        $insertData = [
            'fk_user'      => $r['userId'],
            'balance'      => isset($params['balance']) ? $params['balance'] * 100 : 0,
            'ios_coin'     => $iosCoin,
            'virtual_coin' => isset($params['virtualCoin']) ? $params['virtualCoin'] * 100 : 0,
            'status'       => isset($params['status']) ? $params['status'] : 0,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        $updateData = ["ios_coin=ios_coin+{$iosCoin}",];

        $res = user_db_userValueDao::add($insertData, $updateData);
        if ($res === false) return api_func::setMsg(1);

        return api_func::setMsg(0);
    }

    public function pageAddLog()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $r = api_func::isValidId(['userId', 'type'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $data = [
            'fk_user'      => $r['userId'],
            'fk_order'     => isset($params['orderId']) ? $params['orderId'] : 0,
            'balance'      => isset($params['balance']) ? $params['balance'] * 100 : 0,
            'ios_coin'     => isset($params['iosCoin']) ? $params['iosCoin'] * 100 : 0,
            'virtual_coin' => isset($params['virtualCoin']) ? $params['virtualCoin'] * 100 : 0,
            'status'       => isset($params['status']) ? $params['status'] : 0,
            'type'         => $params['type'],
            'source'       => isset($params['source']) ? $params['source'] : 1,
            'create_time'  => date('Y-m-d H:i:s')
        ];

        $res = user_db_userValueLogDao::add($data);
        if ($res) return api_func::setData($res);

        return api_func::setMsg(1);
    }

    public function pageAddThirdPartyLog()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $r = api_func::isValidId(['userId', 'transactionId'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $data = [
            'fk_user'          => $r['userId'],
            'fk_log'           => isset($params['logId']) ? $params['logId'] : 0,
            'transaction_id'   => $r['transactionId'],
            'transaction_info' => isset($params['transactionInfo']) ? json_encode($params['transactionInfo']) : '',
            'source'           => isset($params['source']) ? $params['source'] : 1,
            'create_time'      => date('Y-m-d H:i:s')
        ];

        $res = user_db_userThirdPartyLogDao::add($data);
        if ($res) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }

    public function pageExists($inPath)
    {
        if (empty($inPath[3])) {
            return api_func::setMsg(1000);
        }

        $res = user_db_userThirdPartyLogDao::checkTransactionId(trim($inPath[3]));
        if (!empty($res)) return api_func::setData($res);

        return api_func::setMsg(3002);
    }

    public function pageGetUserBalance($inPath)
    {
        if (empty($inPath[3]) || !(int)($inPath[3])) {
            return api_func::setMsg(1000);
        }

        $res = user_db_userValueDao::row((int)($inPath[3]));
        $iosCoin = 0;

        if (!empty($res['ios_coin'])) {
            $iosCoin = $res['ios_coin'];
        }

        return api_func::setData(['iosCoin' => $iosCoin]);
    }

    public function pageUpdateBalance()
    {
        $params = SJson::decode(utility_net::getPostData(), true);

        $r = api_func::isValidId(['userId', 'balance'], $params);
        if (!empty($r['code'])) return api_func::setMsg($r['code']);

        $res = user_db_userValueDao::updateBalance($r['userId'], $r['balance']);
        if ($res) return api_func::setMsg(0);

        return api_func::setMsg(1);
    }
}
