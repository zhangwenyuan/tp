<?php
/**
 * Created by PhpStorm.
 * User: zhangwenyuan
 * Date: 2018/4/24
 * Time: 下午3:20
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class Menu extends Model{
    /**
     * 左侧全部菜单（主管理员登录时调用）
     * */
    public function menulist(){

        $menu_list = Db::name('menu')->where('pid',0)->where('is_show',1)->order('order desc')->select();

        foreach ($menu_list as $key => &$value){
            $value['son'] = Db::name('menu')->where('pid',$value['id'])->select();
        }

        return $menu_list;
    }
    /**
     * 用户左侧权限列表
     * param $access_id 左侧主菜单id $access_str 左侧子菜单id
     * */
    public function powerMenu($access_id,$access_str){

        $menu_list = Db::name('menu')->where('is_show',1)->where('id','in',$access_id)->order('order desc')->select();
        foreach ($menu_list as $key => &$value){

            $son = Db::name('menu')->where('pid',$value['id'])->where('is_show',1)->select();
            foreach ($son as $k => $v){
                if(in_array($v['id'],$access_str)){
                    $value['son'][$k] = $v;
                }
            }
        }
        return $menu_list;
    }
}
?>