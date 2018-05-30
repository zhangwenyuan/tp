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
            if($message['event'] == 'unsubscribe') {
                #如果是取消关注事件 则不用进行其他的逻辑处理 直接调用
                $this->receive(array(), array(), array());
                exit();
            }
            $pars = $this->analyze($message);
            foreach($pars as $par) {
                $par['message'] = $message;
                # 未完善
                $response = $this->process($par);
                if($this->isValidResponse($response)) {
                    break;
                }
            }
            # 有问题待解决
//            $resp = $this->account->response($response);
//
//            if(!empty($_GET['encrypt_type']) && $_GET['encrypt_type'] == 'aes') {
//                $resp = $this->account->encryptMsg($resp);
//                $resp = $this->account->xmlDetract($resp);
//            }
//            $mapping = array(
//                '[from]' => $this->message['from'],
//                '[to]' => $this->message['to'],
//                '[rule]' => $this->params['rule']
//            );
//            $resp = str_replace(array_keys($mapping), array_values($mapping), $resp);
//            $this->receive('', '', $response);
//            exit();
            $respon['FromUserName'] = $message['to'];
            $respon['ToUserName'] = $message['from'];
            $respon['MsgType'] = "text";
            $respon['Content'] = "系统目前正在研发中！";
            $resp = $this->account->response($respon);
            print_r($resp);die;
        }
        exit('Request Failed');
    }
    /**
     * 验证回复类型的正确性
     * */
    private function isValidResponse($response) {
        if(is_array($response)) {
            if($response['type'] == 'text' && !empty($response['content'])) {
                return true;
            }
            if($response['type'] == 'news' && !empty($response['items'])) {
                return true;
            }
            if(!in_array($response['type'], array('text', 'news', 'image'))) {
                return true;
            }
        }
        return false;
    }

    private function process($param) {
        global $_W;
        if(empty($param['module']) || !in_array($param['module'], $this->modules)) {
            return false;
        }
        $processor = WeUtility::createModuleProcessor('core');

        $processor->message = $param['message'];
        $processor->rule = $param['rule'];
        $processor->reply_type = $param['reply_type'];
        $processor->priority = intval($param['priority']);
        $processor->inContext = $param['context'] === true;
        $response = $processor->respond();
        if(empty($response)) {
            return false;
        }

        return $response;
    }

    /**
     * 消息类型匹配方法（普通文本/事件类型）
     * */
    private function analyze(&$message) {
        $params = array();
        if(in_array($message['type'], array('event', 'qr'))) {
            // 消息类型为事件消息或者参数二维码时
            $params = call_user_func_array(array($this, 'analyze' . $message['type']), array(&$message));
            if(!empty($params)) {
                return (array)$params;
            }
        }

        if(method_exists($this, 'analyze' . $message['type'])) {
            $temp = call_user_func_array(array($this, 'analyze' . $message['type']), array(&$message));
            if(!empty($temp) && is_array($temp)){
                $params += $temp;
            }
        } else {
            // 默认回复default_message
            $params += $this->handler($message['type']);
        }
        return $params;
    }
    /**
     * 默认回复（$setting 需查数据库当前公众号是否存在默认回复）
     * */
    private function handler($type) {
        if(empty($type)) {
            return array();
        }
        $params = array();
        $setting['default_message'] = '';
        $default_message = $setting['default_message'];
        if(is_array($default_message) && !empty($default_message[$type]['type'])) {
            if ($default_message[$type]['type'] == 'keyword') {
                $message = $this->message;
                $message['type'] = 'text';
                $message['redirection'] = true;
                $message['source'] = $type;
                $message['content'] = $default_message[$type]['keyword'];
                return $this->analyzeText($message);
            } else {
                $params[] = array(
                    'message' => $this->message,
                    'module' => is_array($default_message[$type]) ? $default_message[$type]['module'] : $default_message[$type],
                    'rule' => '-1',
                );
                return $params;
            }
        }
        return array();
    }
    /**
     * 普通消息回复包括（文本、语音、图片、视频、小视频、地理位置、链接）、
     * $_uniacid 需传入或从session拿
     * */
    public function analyzeText(&$message, $order = 0) {
        $replymodel = model('reply');
        $pars = array();
        $_uniacid = 1;
        $order = intval($order);
        if(!isset($message['content'])) {
            return $pars;
        }

        $condition = <<<EOF
`uniacid` IN ( 0, {$_uniacid} )
AND 
(
	( `type` = 1 AND `content` = :c1 )
	or
	( `type` = 2 AND instr(:c2, `content`) )
	or
	( `type` = 3 AND :c3 REGEXP `content` )
	or
	( `type` = 4 )
)
AND `status`=1
EOF;

        $params = array();
        $params[':c1'] = $message['content'];
        $params[':c2'] = $message['content'];
        $params[':c3'] = $message['content'];

        if (intval($order) > 0) {
            $condition .= " AND `displayorder` > :order";
            $params[':order'] = $order;
        }

        $keywords = $replymodel->reply_keywords_search($condition, $params);
        if(empty($keywords)) {
            return $pars;
        }
        foreach($keywords as $keyword) {
            $params = array(
                'message' => $message,
                'module' => $keyword['module'],
                'rule' => $keyword['rid'],
                'priority' => $keyword['displayorder'],
                'keyword' => $keyword,
                'reply_type' => $keyword['reply_type']
            );
            $pars[] = $params;
        }
        return $pars;
    }
    /**
     * 当消息类型qr类型时，进行相应的处理
     * */
    private function analyzeQR(&$message) {

        $params = array();
//        $message['type'] = 'text';
//        $message['redirection'] = true;
//        if(!empty($message['scene'])) {
//            $message['source'] = 'qr';
//            $sceneid = trim($message['scene']);
//            $scene_condition = '';
//            if (is_numeric($sceneid)) {
//                $scene_condition = " `qrcid` = '{$sceneid}'";
//            }else{
//                $scene_condition = " `scene_str` = '{$sceneid}'";
//            }
//            $qr = pdo_fetch("SELECT `id`, `keyword` FROM " . tablename('qrcode') . " WHERE {$scene_condition} AND `uniacid` = '{$_W['uniacid']}'");
//
//        }
//        if (empty($qr) && !empty($message['ticket'])) {
//            $message['source'] = 'qr';
//            $ticket = trim($message['ticket']);
//            if(!empty($ticket)) {
//                $qr = pdo_fetchall("SELECT `id`, `keyword` FROM " . tablename('qrcode') . " WHERE `uniacid` = '{$_W['uniacid']}' AND ticket = '{$ticket}'");
//                if(!empty($qr)) {
//                    if(count($qr) != 1) {
//                        $qr = array();
//                    } else {
//                        $qr = $qr[0];
//                    }
//                }
//            }
//        }
//        if(!empty($qr)) {
//            $message['content'] = $qr['keyword'];
//            if (!empty($qr['type']) && $qr['type'] == 'scene') {
//                $message['msgtype'] = 'text';
//            }
//            $params += $this->analyzeText($message);
//        }
        return $params;
    }
    /**
     * 消息类型为事件类型时
     * */
    private function analyzeEvent(&$message) {
        if (strtolower($message['event']) == 'subscribe') {
            return $this->analyzeSubscribe($message);
        }
        if (strtolower($message['event']) == 'click') {
            $message['content'] = strval($message['eventkey']);
            return $this->analyzeClick($message);
        }
        # 当事件类型为（弹出拍照或者相册发图/弹出微信相册发图器/弹出系统拍照发图）时进行的处理
        if (in_array($message['event'], array('pic_photo_or_album', 'pic_weixin', 'pic_sysphoto'))) {
//            pdo_query("DELETE FROM ".tablename('menu_event')." WHERE createtime < '".($GLOBALS['_W']['timestamp'] - 100)."' OR openid = '{$message['from']}'");
//            if (!empty($message['sendpicsinfo']['count'])) {
//                foreach ($message['sendpicsinfo']['piclist'] as $item) {
//                    pdo_insert('menu_event', array(
//                        'uniacid' => $GLOBALS['_W']['uniacid'],
//                        'keyword' => $message['eventkey'],
//                        'type' => $message['event'],
//                        'picmd5' => $item,
//                        'openid' => $message['from'],
//                        'createtime' => TIMESTAMP,
//                    ));
//                }
//            } else {
//                pdo_insert('menu_event', array(
//                    'uniacid' => $GLOBALS['_W']['uniacid'],
//                    'keyword' => $message['eventkey'],
//                    'type' => $message['event'],
//                    'picmd5' => $item,
//                    'openid' => $message['from'],
//                    'createtime' => TIMESTAMP,
//                ));
//            }
            $message['content'] = strval($message['eventkey']);
            $message['source'] = $message['event'];
            return $this->analyzeText($message);
        }
        if (!empty($message['eventkey'])) {
            $message['content'] = strval($message['eventkey']);
            $message['type'] = 'text';
            $message['redirection'] = true;
            $message['source'] = $message['event'];
            return $this->analyzeText($message);
        }
        return $this->handler($message['event']);
    }
    /**
     * 事件类型为关注事件时进行的操作
     * */
    private function analyzeSubscribe(&$message) {
        $params = array();
//        $message['type'] = 'text';
//        $message['redirection'] = true;
//        if(!empty($message['scene'])) {
//            $message['source'] = 'qr';
//            $sceneid = trim($message['scene']);
//            $scene_condition = '';
//            if (is_numeric($sceneid)) {
//                $scene_condition = " `qrcid` = '{$sceneid}'";
//            }else{
//                $scene_condition = " `scene_str` = '{$sceneid}'";
//            }
//            $qr = pdo_fetch("SELECT `id`, `keyword` FROM " . tablename('qrcode') . " WHERE {$scene_condition} AND `uniacid` = '{$_W['uniacid']}'");
//            if(!empty($qr)) {
//                $message['content'] = $qr['keyword'];
//                if (!empty($qr['type']) && $qr['type'] == 'scene') {
//                    $message['msgtype'] = 'text';
//                }
//                $params += $this->analyzeText($message);
//                return $params;
//            }
//        }
//        $message['source'] = 'subscribe';
//        $setting = uni_setting($_W['uniacid'], array('welcome'));
//        if(!empty($setting['welcome'])) {
//            $message['content'] = $setting['welcome'];
//            $params += $this->analyzeText($message);
//        }

        return $params;
    }
    /**
     * 事件类型为点击事件时进行的处理
     * */
    private function analyzeClick(&$message) {
        if(!empty($message['content']) || $message['content'] !== '') {
            $message['type'] = 'text';
            $message['redirection'] = true;
            $message['source'] = 'click';
            return $this->analyzeText($message);
        }

        return array();
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