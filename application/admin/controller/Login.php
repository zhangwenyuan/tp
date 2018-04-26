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
        foreach ($_managelist as $key => &$val){
            $role_id = $_manage->getRoleUserId($val['id']);
            $rolename = $_manage->getRoleInfo($role_id);
            $val['role_name'] = $rolename['name'];
        }
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
        $usermodel = model('user');
        $rolelist = $usermodel->role_list();
        if(!empty($_POST['saveid'])){
            $usermodel = model('user');
            $_data = array();
            $_data['name'] = isset($_POST['username'])?$_POST['username']:'';
            $_data['email'] = isset($_POST['email'])?$_POST['email']:'';
            $_data['phone'] = isset($_POST['phone'])?$_POST['phone']:'';
            $checkname = $usermodel->checkuser($_data['name']);
            if($checkname && $checkname['name'] != $_data['name']){
                return exitMsg(202,'该用户名已存在，请重新提交~');
            }
            $res = $usermodel->user_save($_data,$_POST['saveid']);
            if($res || $res==0){#当数据没改动的情况下(update 将返回0)
                $usermodel->SaveUserRole($_POST['saveid']);
                return exitMsg(200,'用户修改成功~');
            }else{
                return exitMsg(201,'修改失败，请重新提交~');
            }
        }
        $saveid = $_REQUEST['id'];
        $saveinfo = model('user');
        $userinfo = $saveinfo->getUserInfo($saveid);
        $userinfo['role_id'] = $usermodel->getRoleUserId($userinfo['id']);
        $this->assign(['rolelist'=>$rolelist,'userinfo'=>$userinfo]);
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
            if($_POST['role_id'] == 2){
                $_data['is_admin'] = 1;
            }
            $checkname = $usermodel->checkuser($_data['name']);
            if($checkname){
                return exitMsg(202,'该用户名已存在，请重新提交~');
            }
            if($usermodel->user_add($_data)){
                $userId = Db::name('user')->getLastInsID();
                $usermodel->add_role_user($userId);
                return exitMsg(200,'用户注册成功~');
            }else{
                return exitMsg(201,'注册失败，请重新提交~');
            }

        }
        $usermodel = model('user');
        $rolelist = $usermodel->role_list();
        $this->assign('rolelist',$rolelist);
        return view('index/admin_add');
    }

    /**
     * 角 色 管 理
     * */
    public function admin_role(){
        $usermodel = model('user');
        $rolelist = $usermodel->role_list();

        $this->assign('rolelist',$rolelist);
        return view('index/admin_role');
    }

    /**
     * 角 色 添 加
     * */
    public function role_add(){
        $menumodel = model('menu');
        $usermodel = model('user');
        if(!empty($_POST['rolename'])){
            $_data = array();
            $_data['name'] = isset($_POST['rolename'])?$_POST['rolename']:'';
            $_data['desc'] = isset($_POST['desc'])?$_POST['desc']:'';
            $_data['create_time'] = time();
            if($usermodel->role_add($_data)){
                $roleinsertID = $usermodel->getLastInsID();
                $usermodel->InsertRoleAccess($roleinsertID);
                return exitMsg(200,'角色添加成功~');
            }else{
                return exitMsg(201,'角色添加失败，请重新提交~');
            }
        }
        $menu_list = $menumodel->menulist();
        $this->assign('menu_list',$menu_list);
        return view('index/role_add');
    }

    /**
     * 角 色 del
     * */
    public function role_del(){
        $id = $_POST['id'];
        if(empty($id))
            return exitMsg(202,'非法操作，无法识别的角色~');
        $del_manage = model('user');
        $del_res = $del_manage->del_role($id);
        if($del_res)
            return exitMsg(200,'用户角色成功~');
        else
            return exitMsg(201,'用户角色失败，请稍后重试~');
    }

    /**
     * 角 色 修 改
     * */
    public function role_edit(){
        $menumodel = model('menu');
        if(!empty($_REQUEST['saveid'])){
            $usermodel = model('user');
            $_data = array();
            $_data['name'] = isset($_POST['rolename'])?$_POST['rolename']:'';
            $_data['desc'] = isset($_POST['desc'])?$_POST['desc']:'';
            $_data['update_time'] = time();
            if($usermodel->role_save($_data,$_POST['saveid'])){
                return exitMsg(200,'角色修改成功~');
            }else{
                return exitMsg(201,'角色失败，请重新提交~');
            }
        }
        $saveid = $_REQUEST['editid'];
        $saveinfo = model('user');
        $roleinfo = $saveinfo->getRoleInfo($saveid);
        $menu_list = $menumodel->menulist();
        $this->assign('menu_list',$menu_list);
        $this->assign('roleinfo',$roleinfo);
        return view('index/role_edit');
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
