<?php
/**
 * Created by PhpStorm.
 * User: zhangwenyuan
 * Date: 2018/5/10
 * Time: 下午4:15
 */

if(!empty($_REQUEST['appid'])) {
    $appid = ltrim($_REQUEST['appid'], '/');
    if ($appid == 'wx570bc396a51b8ff8') {
        $_W['account'] = array(
            'type' => '3',
            'key' => 'wx570bc396a51b8ff8',
            'level' => 4,
            'token' => 'platformtestaccount'
        );
    } else {
//        $id = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE `key` = :appid", array(':appid' => $appid));
    }
}

if(empty($id)) {
    $id = intval($_GET['id']);
    $_SESSION['account'] = Db::name('account_wechats')->where('acid',$id)->select();
}

$engine = new WXEngine();
$engine->start();
class WXEngine {

    public function start() {

        echo 1;die;
    }
}