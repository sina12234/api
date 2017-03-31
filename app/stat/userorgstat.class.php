<?php

class stat_userorgstat extends stat_db
{

    const USER_ORG_STAT = 't_user_org_stat';

    public function add($data)
    {
        return $this->commonAdd(self::USER_ORG_STAT, $data);
    }

    public function save($condition, $data)
    {
        return $this->commonSave(self::USER_ORG_STAT, $condition, $data);
    }

    public function del($condition)
    {
        return $this->commonDel(self::USER_ORG_STAT, $condition);
    }

    public function lists($page=1, $limit=10, $condition='', $groupBy='', $orderBy='', $item='', $leftJoin='')
    {
        return $this->commonLists(
            self::USER_ORG_STAT,
            $page,
            $limit,
            $condition,
            $groupBy,
            $orderBy,
            $item,
            $leftJoin
        );
    }

    public function getStatUserOrginfoByUidArr($arr)
    {
        $uidStr = implode(',', $arr);
        $condition = "fk_user IN ($uidStr)";
        $orderBy = 'vv_live DESC';

        return $this->commonLists(self::USER_ORG_STAT, '', '', $condition, '', $orderBy);
    }

}
