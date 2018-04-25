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

    # 左侧列表展示
    public function menulist(){

        $menu_list = Db::name('menu')->where('pid',0)->where('is_show',1)->order('order desc')->select();

        foreach ($menu_list as $key => &$value){
            $value['son'] = Db::name('menu')->where('pid',$value['id'])->select();
        }

        return $menu_list;
    }
}
?>