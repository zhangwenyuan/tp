<?php
/**
 * Created by PhpStorm.
 * User: zhangwenyuan
 * Date: 2018/5/30
 * Time: 下午5:29
 */

namespace app\admin\model;


class Reply
{
    function reply_keywords_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
        if (!empty($condition)) {
            $where = " WHERE {$condition} ";
        }
        /*
         *
         *    查询数据库对关键字进行匹配
         *
         * */
        $result = array();
        return $result;
    }
}