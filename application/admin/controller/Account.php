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

        return view('account-list');
    }

    public function account_add(){

        return view('account-add');
    }
    //新增手动公众号
    public function post_step(){
        if(!empty($_POST)){
            $data['name'] = $_POST['acc_name'];
            $data['desc'] = $_POST['desc'];
            $data['account'] = $_POST['account'];
            $data['original'] = $_POST['title'];
            $data['level'] = $_POST['level'];
            $data['key'] = $_POST['key'];
            $data['secret'] = $_POST['secret'];
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

    //微信开放平台
    public function acc_openwx(){
        echo "微信开放平台";die;
    }

    /**
     * 图片上传
     * param file:图片信息   upload_path 上传路径
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