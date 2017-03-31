<?php

class stat_daystatoverview extends stat_db
{

    const DAY_STAT_OVERVIEW = 't_day_stat_overview';

    public function add($data)
    {
        return $this->commonAdd(self::DAY_STAT_OVERVIEW, $data);
    }

    public function save($condition, $data)
    {
        return $this->commonSave(self::DAY_STAT_OVERVIEW, $condition, $data);
    }

    public function del($condition)
    {
        return $this->commonDel(self::DAY_STAT_OVERVIEW, $condition);
    }

    public function lists($page, $limit, $condition='', $groupBy='', $orderBy='', $item='', $leftJoin='')
    {
        return $this->commonLists(
            self::DAY_STAT_OVERVIEW,
            $page,
            $limit,
            $condition,
            $groupBy,
            $orderBy,
            $item,
            $leftJoin
        );
    }

}
