<?php
/**
 * 微信功能
 * User: zhangwenyuan
 * Date: 2018/5/7
 * Time: 下午2:05
 */
namespace app\admin\controller;

use think\Controller;

class Platform extends Controller{

    //自动回复
    public function reply(){
        $platmodel = model('platform');
        $reply_list = $platmodel->sub_relpy_list();
        $this->assign('reply_list',$reply_list);
        return view('reply');
    }
    /**
     * 自动回复保存
     * */
    public function sub_reply(){

        $platmodel = model('platform');
        $_data = array();
        $_data['keyword'] = isset($_POST['keyword'])?$_POST['keyword']:'';
        $_data['desc'] = isset($_POST['desc'])?$_POST['desc']:'';
        $_data['content'] = isset($_POST['content'])?$_POST['content']:'';
        $_data['create_time'] = time();
        if($platmodel->sub_relpy_add($_data)){

            return exitMsg(200,'添加成功~');
        }else{
            return exitMsg(201,'添加失败，请重新提交~');
        }
    }
    /**
     * 自动回复关键词删除
     * */
    public function reply_del(){
        $id = $_POST['id'];
        if(empty($id))
            return exitMsg(202,'非法操作，无法识别的关键词~');
        $del_platform = model('platform');
        $del_res = $del_platform->reply_del($id);
        if($del_res)
            return exitMsg(200,'操作成功~');
        else
            return exitMsg(201,'操作失败，请稍后重试~');
    }

    //自定义菜单
    public function menu(){

    }

    //二维码/转化连接
    public function qr(){

    }

    //素材
    public function material(){

    }

}