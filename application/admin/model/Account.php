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

    public function insert_account($data = array()){
        if(empty($data))
            $this->error('提交数据不能为空~');
       $insert_res =  Db::name('account_wechats')->insert($data);

       return $insert_res;
    }
}