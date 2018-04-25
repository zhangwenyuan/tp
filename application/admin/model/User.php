<?php
namespace app\admin\model;

use think\Model;
use think\Db;
use think\model\concern\TimeStamp;

class User extends Model{

    /**
     * 增加用户信息
     * param $userdata 注册信息
     * return ture/fasle
     * */
    public function user_add($userdata){

        if(empty($userdata))
            return false;
        $userdata['salt'] = randomStr();
        $userdata['pwd'] = md5($userdata['pwd'].$userdata['salt']);
        $userdata['create_time'] = time();

        $insertres = Db::name('user')->insert($userdata);
        if($insertres)
            return true;
        else
            return false;
    }
    /**
     * 检测当前注册用户是否存在
     * param $_name 用户注册的用户名
     * return ture/fasle
     * */
    public function checkuser($_name){

        $_checkres = Db::name('user')->where('name',$_name)->find();
        if($_checkres)
            return true;
        else
            return false;
    }
    /**
     * 管理员列表
     * return array()
     * */
    public function Administration_List($p_limit,$limit,$starttime,$endtime,$username){

        $where = [];
        if(!empty($username)){
            $where['name']= $username;
        }
        $userlist = Db::name('user')->where($where)->limit($p_limit,$limit)->select();
        return $userlist;
    }
    /**
     * 管理员总数量
     * return array()
     * */
    public function Administration_List_Count($username,$starttime,$endtime){
        $where = [];
        if(!empty($username)){
            $where['name']= $username;
        }
        $userlist_count = Db::name('user')->where($where)->count();
        return $userlist_count;
    }
    /**
     * 根据ID查询用户的信息
     * return array()
     * */
    public function getUserInfo($id){

        $userinfo = Db::name('user')->where('id',$id)->find();
        return $userinfo;
    }
    /**
     * 管理员列表
     * param $id 用户id
     * return true/fasle
     * */
    public function del_manage($id){
        $del_res = Db::name('user')->where('id',$id)->delete();
        if($del_res)
            return true;
        else
            return false;
    }

    /**
     * 管理员列表
     * param $id 用户id
     * return true/fasle
     * */
    public function save_satatus_manage($id){
        $is_open = $_POST['status'];
        $save_res = Db::name('user')->where('id',$id)->update(['is_open' => $is_open]);
        if($save_res)
            return true;
        else
            return false;
    }

    /**
     * 修改管理员信息
     * param  $data：需要修改的信息   $id：用户id
     * return true/false
     * */
    public function user_save($data,$id){

        $save_res = Db::name('user')->where('id',$id)->update($data);
        if($save_res)
            return true;
        else
            return false;
    }

}
?>