<?php

namespace app\admin\controller;

use think\Build;
use think\Controller;
use think\facade\Log;
use think\Loader;
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

            $this->message = $message;

            if(empty($message)) {

                exit('Request Failed');
            }
            $_SESSION['openid'] = $message['from'];
//            $this->booking($message);
//            if($message['event'] == 'unsubscribe') {
//                #如果是取消关注事件 则不用进行其他的逻辑处理 直接调用
//                $this->receive(array(), array(), array());
//                exit();
//            }

            $respon['FromUserName'] = $message['to'];
            $respon['ToUserName'] = $message['from'];
            $respon['MsgType'] = "text";
            $respon['Content'] = "系统目前正在研发中！";
            $resp = $this->account->response($respon);
            print_r($resp);die;
        }
    }

    private function receive($par, $keyword, $response) {
        global $_W;
        fastcgi_finish_request();
        $subscribe = cache_load('module_receive_enable');
        $modules = uni_modules();
        $obj = WeUtility::createModuleReceiver('core');
        $obj->message = $this->message;
        $obj->params = $par;
        $obj->response = $response;
        $obj->keyword = $keyword;
        $obj->module = 'core';
        $obj->uniacid = $_W['uniacid'];
        $obj->acid = $_W['acid'];
        if(method_exists($obj, 'receive')) {
            @$obj->receive();
        }
        load()->func('communication');
        if (empty($subscribe[$this->message['type']])) {
            $subscribe[$this->message['type']] = $subscribe[$this->message['event']];
        }
        if (!empty($subscribe[$this->message['type']])) {
            foreach ($subscribe[$this->message['type']] as $modulename) {
                $params = array(
                    'i' => $GLOBALS['uniacid'],
                    'modulename' => $modulename,
                    'request' => json_encode($par),
                    'response' => json_encode($response),
                    'message' => json_encode($this->message),
                );
                $response = ihttp_request(wurl('utility/subscribe/receive'), $params, array(), 10);
                if (is_error($response) && $response['errno'] == '7') {
                    $response = ihttp_request($_W['siteroot'] . 'web/' . wurl('utility/subscribe/receive'), $params, array(), 10);
                }
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

    private function booking($message) {
        if ($message['event'] == 'unsubscribe' || $message['event'] == 'subscribe') {
            #  当用户是关注或者取消关注时记录 ，方便记录今日指标（eg: 新关注|取消关注|净增关注| ）
        }
        # 更新 || 补全粉丝信息
        //$fans = mc_fansinfo($message['from']);
        if(!empty($fans)) {
            if ($message['event'] == 'unsubscribe') {
                # 取消关注的情况下执行的流程
                # .......
            } elseif ($message['event'] != 'ShakearoundUserShake' && $message['type'] != 'trace') {
                #
            }
        } else {
            if ($message['event'] == 'subscribe' || $message['type'] == 'text' || $message['type'] == 'image') {
//                load()->model('mc');
//                $force_init_member = false;
//                if (!isset($setting['passport']) || empty($setting['passport']['focusreg'])) {
//                    $force_init_member = true;
//                }
                $model = model('mcmodel');
                $model->mc_init_fans_info($message['from']);
            }
        }
    }

}