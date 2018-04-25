<?php
namespace app\admin\controller;

use think\Build;
use think\Controller;
use think\model;
use think\Db;

class Login extends Controller
{
    /**
     * 主页
     * */
    public function login(){

        $_menu = model('menu');
        $menu_list = $_menu->menulist();

        $this->assign('menu_list',$menu_list);

        return view('index/index');
    }
    /**
     * 管 理 员 列 表
     * */
    public function admin_list(){

        $starttime = isset($_REQUEST['start'])?$_REQUEST['start']:'';
        $endtime = isset($_REQUEST['end'])?$_REQUEST['end']:'';
        $username = isset($_REQUEST['username'])?$_REQUEST['username']:'';
        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;//获取当前页
        $pageSize = 5;
        $p_limit = ($page-1)*$pageSize;//偏移量计算
        $_manage = model('user');
        $_managelist = $_manage->Administration_List($p_limit,$pageSize,$starttime,$endtime,$username);

        $total = $_manage->Administration_List_count($username,$starttime,$endtime);
        $this->assign([
            'manage_list' => $_managelist,
            'pageCount' => ceil($total/$pageSize),
            'pageSize' => $pageSize,
            'page' => $page,
            'total' => $total,
        ]);
        return view('index/admin_list');
    }
    /**
     * 编 辑 管 理 员 信 息
     * */
    public function admin_edit(){

        if(!empty($_POST['saveid'])){
            $usermodel = model('user');
            $_data = array();
            $_data['name'] = isset($_POST['username'])?$_POST['username']:'';
            $_data['email'] = isset($_POST['email'])?$_POST['email']:'';
            $_data['phone'] = isset($_POST['phone'])?$_POST['phone']:'';
            $checkname = $usermodel->checkuser($_data['name']);
            if($checkname){
                return exitMsg(202,'该用户名已存在，请重新提交~');
            }
            if($usermodel->user_save($_data,$_POST['saveid'])){
                return exitMsg(200,'用户修改成功~');
            }else{
                return exitMsg(201,'修改失败，请重新提交~');
            }

        }
        $saveid = $_REQUEST['id'];
        $saveinfo = model('user');
        $userinfo = $saveinfo->getUserInfo($saveid);

        $this->assign('userinfo',$userinfo);
        return view('index/admin_edit');
    }
    /**
     * 删除管理员信息
     * */
    public function admin_del(){
        $id = $_POST['id'];
        if(empty($id))
            return exitMsg(202,'非法操作，无法识别的用户~');
        $del_manage = model('user');
        $del_res = $del_manage->del_manage($id);
        if($del_res)
            return exitMsg(200,'用户删除成功~');
        else
            return exitMsg(201,'用户删除失败，请稍后重试~');
    }
    /**
     * 暂停该管理员
     * */
    public function admin_save_status(){

        $id = $_POST['id'];
        if(empty($id))
            return exitMsg(202,'非法操作，无法识别的用户~');
        $del_manage = model('user');
        $del_res = $del_manage->save_satatus_manage($id);
        if($del_res)
            return exitMsg(200,'该用户已暂停~');
        else
            return exitMsg(201,'操作失败，请稍后重试~');
    }
    /**
     * 管 理 员 添 加
     * */
    public function admin_add(){

        if(!empty($_POST['phone'])){
            $usermodel = model('user');
            $_data = array();
            $_data['name'] = isset($_POST['username'])?$_POST['username']:'';
            $_data['email'] = isset($_POST['email'])?$_POST['email']:'';
            $_data['pwd'] = isset($_POST['repass'])?$_POST['repass']:'';
            $_data['phone'] = isset($_POST['phone'])?$_POST['phone']:'';
            $checkname = $usermodel->checkuser($_data['name']);
            if($checkname){
                return exitMsg(202,'该用户名已存在，请重新提交~');
            }
            if($usermodel->user_add($_data)){
                return exitMsg(200,'用户注册成功~');
            }else{
                return exitMsg(201,'注册失败，请重新提交~');
            }

        }

        return view('index/admin_add');
    }

    /**
     * 角 色 管 理
     * */
    public function admin_role(){

        return view('index/admin_role');
    }

    /**
     * 角 色 添 加
     * */
    public function role_add(){

        return view('index/role_add');
    }


    public function welcome(){

        return view('index/welcome');
    }

    public function question_list(){

        return view('index/question_list');
    }

    public function question_del(){

        return view('index/question_del');
    }

    public function banner_list(){

        return view('index/banner_list');
    }

    public function category(){

        return view('index/category');
    }

    public function comment_list(){

        return view('index/comment_list');
    }

    public function feedback_list(){

        return view('index/feedback_list');
    }

    public function member_list(){

        return view('index/member_list');
    }

    public function member_del(){

        return view('index/member_del');
    }

    public function member_level(){

        return view('index/member_level');
    }

    public function member_kiss(){

        return view('index/member_kiss');
    }

    public function member_view(){

        return view('index/member_view');
    }





    public function admin_cate(){

        return view('index/admin_cate');
    }

    public function admin_rule(){

        return view('index/admin_rule');
    }

    public function sys_set(){

        return view('index/sys_set');
    }

    public function sys_data(){

        return view('index/sys_data');
    }

    public function sys_shield(){

        return view('index/sys_shield');
    }

    public function sys_log(){

        return view('index/sys_log');
    }

    public function sys_link(){

        return view('index/sys_link');
    }

    public function sys_qq(){

        return view('index/sys_qq');
    }
}
