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
    /**
     * 公众号列表
     * */
    public function account_list(){
        $acc = model('account');
        $acc_list = $acc->account_list();
        $this->assign('acc_list',$acc_list);
        return view('account-list');
    }
    /**
     * 增加公众号主页
     * */
    public function account_add(){

        return view('account-add');
    }
    /**
     * 新增手动公众号
     * */
    public function post_step(){
        if(!empty($_POST)){
            $data['name'] = $_POST['acc_name'];
            $data['desc'] = $_POST['desc'];
            $data['account'] = $_POST['account'];
            $data['original'] = $_POST['title'];
            $data['level'] = $_POST['level'];
            $data['key'] = $_POST['key'];
            $data['token'] = random(32);
            $data['encodingaeskey'] = random(43);
            $data['secret'] = $_POST['secret'];
            $_data['create_time'] = time();
            if($_FILES['h_img']){
                $data['head_img'] = $this->file_upload($_FILES['h_img']);
            }
            if($_FILES['h_qr']){
                $data['qr_img'] = $this->file_upload($_FILES['h_qr']);
            }
            $acc_model = model('account');
            $res = $acc_model->insert_account($data);
            if(!$res)
                $this->error('添加失败，请重新填写公众号信息');
            else
                $this->success('添加成功，开始旅行吧~');
        }
        return view('post-step');
    }
    /**
     * 公众号停用/开启/删除
     * */
    public function acc_save_status(){
        $id = $_POST['id'];
        if(empty($id))
            return exitMsg(202,'非法操作，无法识别的用户~');
        $del_manage = model('account');
        $del_res = $del_manage->save_satatus_acc($id);
        if($del_res)
            return exitMsg(200,'操作成功~');
        else
            return exitMsg(201,'操作失败，请稍后重试~');
    }
    /**
     * 查看公众号详情
     * */
    public function account_show(){

        $acc_model = model('account');
        $acc_detail = $acc_model->account_list($_REQUEST['id']);

        $this->assign('acc_detail',$acc_detail);
        return view('account-show');
    }
    /**
     * 公众号编辑
     * */
    public function account_edit(){
        $saveid = input('id');
        $accmodel = model('account');
        if(!empty($_POST['saveid'])){
            $_data = array();
            $_data['name'] = $_POST['acc_name'];
            $_data['desc'] = $_POST['desc'];
            $_data['account'] = $_POST['account'];
            $_data['original'] = $_POST['title'];
            $_data['level'] = $_POST['level'];
            $_data['key'] = $_POST['key'];
            $_data['secret'] = $_POST['secret'];
            $_data['create_time'] = time();
            $res = $accmodel->saveaccount($_data,$_POST['saveid']);

            if($res || $res==0){#当数据没改动的情况下(update 将返回0)

                return exitMsg(200,'修改成功~');
            }else{
                return exitMsg(201,'修改失败，请重新提交~');
            }
        }
        $accdetail = $accmodel->account_list($saveid);
        $this->assign('accdetail',$accdetail);
        return view('account-edit');
    }
    //新增授权添加公众号
    public function component(){

    }

    //删除公众号
    public function acc_del(){
        $id = $_POST['id'];
        if(empty($id))
            return exitMsg(202,'非法操作，无法识别的公众号~');
        $del_manage = model('account');
        $del_res = $del_manage->del_account($id);
        if($del_res)
            return exitMsg(200,'删除成功~');
        else
            return exitMsg(201,'删除失败，请稍后重试~');
    }
    //公众号回收站
    public function acc_recyclebin(){
        $accmodel = model('account');
        $acc_list = $accmodel->account_huishou();
        $this->assign('acc_list',$acc_list);
        return view('account-huishou');
    }

    //微信开放平台
    public function acc_openwx(){
        echo "微信开放平台";die;
    }

    /**
     * 图片上传
     * param file:图片信息   upload_path 上传路径
     * return url
     * */
    public function file_upload($file,$root_dir = ''){
        //得到文件名称
        $name = $file['name'];
        $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
        $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
        //判断文件类型是否被允许上传
        if(!in_array($type, $allow_type)){
            //如果不被允许，则直接停止程序运行
            $this->error('上传格式不正确~');
        }
        //判断是否是通过HTTP POST上传的
        if(!is_uploaded_file($file['tmp_name'])){
            //如果不是通过HTTP POST上传的
            $this->error('图片上传失败，未捕捉到图片~');
        }
        if(empty($root_dir)){
            $root_dir = __PUBLIC__; //上传文件的存放路径
            $dir = "/static/account/img/".'wx_'.time().substr($file['name'],strrpos($file['name'],'.'));
        }
        $upload_path = $root_dir.$dir;
        //开始移动文件到相应的文件夹
        if(move_uploaded_file($file['tmp_name'],$upload_path)){

            return $dir;
        }else{
            return false;
        }
    }

}