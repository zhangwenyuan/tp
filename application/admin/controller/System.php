<?php
/**
 * 系统设置.
 * User: zhangwenyuan
 * Date: 2018/5/7
 * Time: 下午1:51
 */
namespace app\admin\controller;

use think\Controller;

class System extends Controller{

    public function sys_emulator(){
        global $_W;
//        $_W['']

        $development = 1;
//        $accounts = uni_owned(0, false);

        $this->assign('development',$development);

        return view('sys-emulator');
    }

    //系统设计
    public function sys_index(){

    }

    //数字字典
    public function sys_shuzi(){

    }

}