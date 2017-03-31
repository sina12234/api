<?php

class blog_api
{
    /**
     * @desc get tag name total list by teacher id
     *
     * @param $teacherId
     * @return array
     *
     * @author wen
     */
    public static function getTagNameTotalList($teacherId)
    {
        if (!is_numeric($teacherId) || !(int)($teacherId)) return [];

        $tagLists   = [];
        $tagNumList = tag_db_mappingTagArticleDao::getAllTagArticleCountListByTeacherId($teacherId);

        if (empty($tagNumList->items)) return [];

        $num = 0;
        foreach ($tagNumList->items as $tagNum) {
            $num += $tagNum['total'];
            $tagNum['tag_status']        = $tagNum['status'];
            $tagLists[$tagNum['fk_tag']] = $tagNum;
        }


        $tagNameList = tag_db_tagDao::getTagsByUserId($teacherId);
        if (!empty($tagNameList->items)) {
            foreach ($tagNameList->items as $tagName) {
                if (!empty($tagLists[$tagName['pk_tag']])) {
                    $tagLists[$tagName['pk_tag']] = array_merge($tagLists[$tagName['pk_tag']], $tagName);
                }
            }
        }

        return [
            'list' => array_values($tagLists),
            'num'  => $num
        ];
    }


}
