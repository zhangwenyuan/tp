<?php

namespace app\admin\controller;

use think\Build;
use think\Controller;
use think\facade\Log;
use think\model;
use think\Db;
use think\Request;
use wx_account\WeAccount;


if(!empty($_REQUEST['appid'])) {
    $appid = ltrim($_REQUEST['appid'], '/');
    # 初始化公众号信息
    if ($appid == 'wx570bc396a51b8ff8') {
        $_W['account'] = array(
            'type' => '3',
            'key' => 'wx570bc396a51b8ff8',
            'level' => 4,
            'token' => 'platformtestaccount'
        );
    } else {

        $id = Db::name('account_wechats')->where('key',$appid)->value('acid');
    }
}
if(empty($id)) {
    $id = intval(input('id'));
}
# 获取当前公众号信息
if(!empty($id)) {
    $id = intval($_GET['id']);
    $_SESSION['account']  = Db::name('account_wechats')->where('acid',$id)->find();
}
if(empty($_SESSION['account'])) {
    exit('initial error hash or id');
}

class Api extends Controller{

    private $account = null;

    private $modules = array();

    public $keyword = array();

    public $message = array();

    public function __construct() {
        $this->account = WeAccount::create($_SESSION['account']);
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            # 当接收到post请求时，进行处理
        }
    }

    public function start() {

        if(empty($this->account)) {
            exit('Miss Account.');
        }
        if(!$this->account->checkSign()) {
            exit('Check Sign Fail.');
        }

        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            #  后续需调整  不在session里进行读取
            Db::name('account_wechats')->where('acid',$_SESSION['account']['acid'])->update(['is_connect' => 1]);
            exit(htmlspecialchars($_GET['echostr']));
        }

        if(strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $postStr = file_get_contents('php://input');
            if(!empty($_GET['encrypt_type']) && $_GET['encrypt_type'] == 'aes') {
                $postStr = $this->account->decryptMsg($postStr);
            }
            $message = $this->account->parse($postStr);
            Log::write($message,'trace');
            $this->message = $message;
            if(empty($message)) {
                Log::write('Request Failed','waring');
                exit('Request Failed');
            }
        }
    }

    public function encrypt(){

        if(empty($this->account)) {
            exit('Miss Account.');
        }
        $timestamp = TIMESTAMP;
        $nonce = random(5);
        $token = $_SESSION['account']['token'];
        $signkey = array($token, TIMESTAMP, $nonce);
        sort($signkey, SORT_STRING);
        $signString = implode($signkey);
        $signString = sha1($signString);

        $_GET['timestamp'] = $timestamp;
        $_GET['nonce'] = $nonce;
        $_GET['signature'] = $signString;
        $postStr = file_get_contents('php://input');
        if(!empty($_W['account']['encodingaeskey']) && strlen($_W['account']['encodingaeskey']) == 43 && !empty($_W['account']['key']) && $_W['setting']['development'] != 1) {
            $data = $this->account->encryptMsg($postStr);
            $array = array('encrypt_type' => 'aes', 'timestamp' => $timestamp, 'nonce' => $nonce, 'signature' => $signString, 'msg_signature' => $data[0], 'msg' => $data[1]);
        } else {
            $data = array('', '');
            $array = array('encrypt_type' => '', 'timestamp' => $timestamp, 'nonce' => $nonce, 'signature' => $signString, 'msg_signature' => $data[0], 'msg' => $data[1]);
        }
        exit(json_encode($array));
    }

    public function decrypt(){
        if(empty($this->account)) {
            exit('Miss Account.');
        }
        $postStr = file_get_contents('php://input');
        if(!empty($_W['account']['encodingaeskey']) && strlen($_W['account']['encodingaeskey']) == 43 && !empty($_W['account']['key']) && $_W['setting']['development'] != 1) {
            $resp = $this->account->local_decryptMsg($postStr);
        } else {
            $resp = $postStr;
        }
        exit($resp);
    }

}