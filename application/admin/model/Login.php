<?php
namespace app\admin\model;
use think\model;
use think\Db;

class Login extends model{
    /**
     * 检测用户名是否存在，存在则返回用户信息
     * param $name 用户名
     * return array()
     * */
    public function checkUserName($name = ''){

        $userinfo = Db::name('user')->where('name',$name)->find();
        return $userinfo;
    }
}