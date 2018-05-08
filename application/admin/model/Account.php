<?php
/**
 * Created by PhpStorm.
 * User: zhangwenyuan
 * Date: 2018/5/8
 * Time: 上午11:00
 */
namespace app\admin\model;

use think\Db;
use think\Model;

class Account extends Model{

    /**
     * 手动添加公众号
     * */
    public function insert_account($data = array()){
        if(empty($data))
            $this->error('提交数据不能为空~');
       $insert_res =  Db::name('account_wechats')->insert($data);

       return $insert_res;
    }

    /**
     * 公众号列表
     * */
    public function account_list($id=''){
        $where = array();
        if(!empty($id))
            $where['acid'] = $id;
        $acclist = Db::name('account_wechats')->where('status != 2')->where($where)->select();
        return $acclist;
    }

    /**
     * 公众号停用/启用
     * param $id 用户id
     * return true/fasle
     * */
    public function save_satatus_acc($id){

        $is_open = $_POST['status'];
        $save_res = Db::name('account_wechats')->where('acid',$id)->update(['status' => $is_open]);
        if($save_res)
            return true;
        else
            return false;
    }
}