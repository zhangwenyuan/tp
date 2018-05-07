<?php
/**
 * 公众号管理.
 * User: zhangwenyuan
 * Date: 2018/5/7
 * Time: 上午11:43
 */
namespace app\admin\controller;

use think\Build;
use think\Controller;
use think\model;
use think\Db;

class Account extends Controller
{

    //公众号列表
    public function account_list(){
        echo "公众号列表";die;
    }

    public function account_add(){
        echo "添加公众号";die;
    }
    //新增手动公众号
    public function post_step(){

    }

    //新增授权添加公众号
    public function component(){

    }

    //删除公众号
    public function delete(){

    }
    //公众号回收站
    public function acc_recyclebin(){
        echo "公众号回收站";die;
    }

}