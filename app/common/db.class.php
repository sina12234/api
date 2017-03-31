<?php
/*
 * @todo ...
 */
class common_db
{

    protected $db;

    public function __construct($dbName)
    {
        $this->db = new SDb;

        $this->db->useConfig($dbName);
    }

    public function commonAdd($table, $data)
    {
        return $this->db->insert($table, $data);
    }

    public function commonSave($table, $condition, $data)
    {
        return $this->db->update($table, $condition, $data);
    }

    public function commonDel($table, $condition)
    {
        return $this->db->delete($table, $condition);
    }

    public function commonLists($table, $page='', $limit='', $condition='', $groupBy='', $orderBy='', $item='', $leftJoin='')
    {
        if ($page && $limit) {
            $this->db->setPage($page);
            $this->db->setLimit($limit);
            $this->db->setCount(true);
        }

        return $this->db->select($table, $condition, $item, $groupBy, $orderBy, $leftJoin);
    }

    public function commonGetOne($table, $condition='', $groupBy='', $orderBy='', $item='', $leftJoin='')
    {
        return $this->db->selectOne($table, $condition, $item, $groupBy, $orderBy, $leftJoin);
    }

    public function getRow($params)
    {
        $data = [];
        foreach($params as $k=>$v) {
            $result = $this->db->selectOne(
                $k,
                !empty($v['condition']) ? $v['condition'] : '',
                !empty($v['item']) ? $v['item'] : ''
            );

            if (!empty($result)) {
                $data[$k] = $result;
            } else {
                SLog::fatal('db error[%s]', var_export($this->db->error(), 1));
                $data[$k] = [];
            }
        }

        return $data;
    }

    public function getIdList($param)
    {
        $page = !empty($param['page']) ? $param['page'] : 1;
        $length = !empty($param['length']) ? $param['length'] : 10000;
        $item = !empty($param['item']) ? $param['item'] : '';
        $table = !empty($param['table']) ? $param['table'] : '';
        $condition = !empty($param['condition']) ? $param['condition'] : '';
        $groupBy = !empty($param['groupBy']) ? $param['groupBy'] : '';
        $orderBy = !empty($param['orderBy']) ? $param['orderBy'] : '';

        if ($page && $length) {
            $this->db->setPage($page);
            $this->db->setLimit($length);
            $this->db->setCount(true);
        }


        $result = $this->db->select(
            $table,
            $condition,
            $item,
            $groupBy,
            $orderBy
        );

        if (!empty($result->items)) {
            if (!empty($param['pk']) && !empty($param['isGetIdStr'])) {
                $idArr = [];
                foreach($result->items as $v) {
                    $idArr[] = $v[$param['pk']];
                }

                return $idArr ? array_unique($idArr) : array();
            }

            return $result;
        }

        return array();
    }

    public function getLists($params)
    {
        $data = [];
        foreach($params as $k=>$v) {
            $result = $this->db->select(
                $k,
                !empty($v['condition']) ? $v['condition'] : '',
                !empty($v['item']) ? $v['item'] : '',
                !empty($v['groupBy']) ? $v['groupBy'] : '',
                !empty($v['orderBy']) ? $v['orderBy'] : ''
            );

            if (!empty($result->items)) {
                 $data[$k] = $result->items;
            } else {
                $data[$k] = [];
            }
        }

        return $data;
    }

}
