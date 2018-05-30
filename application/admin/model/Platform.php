<?php
/**
 * Created by PhpStorm.
 * User: zhangwenyuan
 * Date: 2018/4/24
 * Time: 下午3:20
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class Platform extends Model
{
    /**
     * 存储关键字回复
     * */
    public function sub_relpy_add($_data)
    {

        $reply_res = Db::name('rule_keyword')->insert($_data);

        return $reply_res;
    }

    /**
     * 关键字回复列表
     * */
    public function sub_relpy_list()
    {

        $reply_res = Db::name('rule_keyword')->select();

        return $reply_res;
    }

    /**
     * 关键字删除
     * */
    public function reply_del($id = '')
    {
        if(empty($id))
            return false;

        $reply_res = Db::name('rule_keyword')->where('id',$id)->delete();

        return $reply_res;
    }

}
?>