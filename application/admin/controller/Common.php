<?php
/**
 * Created by PhpStorm.
 * User: zhangwenyuan
 * Date: 2018/5/10
 * Time: 下午2:51
 */
class Common extends \think\Controller{

    public function _initialize(){
    //判断用户是否已经登录
        if (!isset($_SESSION['uid'])) {
            $this->error('对不起,您还没有登录!请先登录再进行浏览', U('Login/login'), 1);
        }
    }
}