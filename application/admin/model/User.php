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
        return $_checkres;
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

//        $save_res = Db::name('user')->where('id',$id)->update($data);
        $save_res = Db::name('user')->where('id',$id)->update($data);
        if($save_res)
            return true;
        else
            return false;
    }

    /**
     * 增加角色
     * param  $data：需要增加的信息
     * return true/false
     * */
    public function role_add($data){

        $insertres = Db::name('role')->insert($data);
        if($insertres)
            return true;
        else
            return false;
    }

    /**
     * 角色列表
     * return array()
     * */
    public function role_list(){

        $rolelist = Db::name('role')->where('status',1)->select();

        return $rolelist;
    }

    /**
     * 删除角色
     * param $id 角色id
     * return true/fasle
     * */
    public function del_role($id){
        $del_res = Db::name('role')->where('id',$id)->delete();
        Db::name('role_access')->where('role_id',$id)->delete();# 清除权限与角色表的关联数据
        if($del_res)
            return true;
        else
            return false;
    }
    /**
     * 根据ID查询角色的信息
     * return array()
     * */
    public function getRoleInfo($id){

        $roleinfo = Db::name('role')->where('id',$id)->find();
        return $roleinfo;
    }

    /**
     * 修改角色信息
     * param  $data：需要修改的信息   $id：用户id
     * return true/false
     * */
    public function role_save($data,$id){

        $save_res = Db::name('role')->where('id',$id)->update($data);
        if($save_res)
            return true;
        else
            return false;
    }

    /**
     * 增加用户角色关系
     * */
    public function add_role_user($uid){
        $_data['role_id'] = isset($_POST['role_id'])?$_POST['role_id']:'';
        $_data['uid'] = $uid;
        $_data['create_time'] = time();
        $insertres = Db::name('user_role')->insert($_data);
        if($insertres)
            return true;
        else
            return false;
    }
    /**
     * 根据用户id查询角色id
     * param id 用户id
     * */
    public function getRoleUserId($id){
        $roleid = Db::name('user_role')->where('uid',$id)->value('role_id');
        return $roleid;

    }
    /**
     * 根据用户id修改角色id
     * param id 用户id
     * */
    public function SaveUserRole($id){
        $_data['role_id'] = isset($_POST['role_id'])?$_POST['role_id']:0;
        $save_res = Db::name('user_role')->where('uid',$id)->update($_data);
        return $save_res;
    }

    /**
     * 增加角色权限
     * param $roleinsertID 角色id
     * return true/fasle
     * */
    public function InsertRoleAccess($roleinsertID){
        $_data['access_str'] = isset($_POST['checkid'])?$_POST['checkid']:'';
        $_data['access_id'] = isset($_POST['fa_checkid'])?$_POST['fa_checkid']:'';
        $_data['role_id'] = $roleinsertID;
        $_data['create_time'] = time();
        $insert_res = Db::name('role_access')->insert($_data);
        if($insert_res)

            return true;
        else
            return false;
    }

}
?>