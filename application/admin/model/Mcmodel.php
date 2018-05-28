<?php
namespace app\admin\model;
use think\model;
use think\Db;
use wx_account\WeAccount;

class Mcmodel extends model{

    /**
     * 用户信息初始化
     * param openid 用户唯一  force_init_member 公众号如果开启会员功能
     * */
    function mc_init_fans_info($openid, $force_init_member = false){

        static $account_api;
        if (empty($account_api)) {
            $account_api = WeAccount::create();
        }
        $fans = $account_api->fansQueryInfo($openid);
        if (empty($fans) || is_error($fans)) {
            return true;
        }
        if (empty($fans['subscribe'])) {
            # 与公众号互动但未关注下如何操作
            return true;
        }
        # 查询当前用户的信息
//        $fans_mapping = mc_fansinfo($openid);

        $fans_update_info = array(
            'openid' => $fans['openid'],
            'acid' => $_SESSION['account']['acid'],
            'uniacid' => 8,
            'updatetime' => TIMESTAMP,
            'followtime' => $fans['subscribe_time'],
            'follow' => $fans['subscribe'],
            'nickname' => strip_emoji(stripcslashes($fans['nickname'])),
            'tag' => base64_encode(iserializer($fans)),
            'unionid' => $fans['unionid'],
            'groupid' => !empty($fans['tagid_list']) ? (','.join(',', $fans['tagid_list']).',') : '',
        );
        if (!empty($fans['headimgurl'])) {
            $fans['headimgurl'] = rtrim($fans['headimgurl'], '0') . 132;
        }
        if ($force_init_member) {
            # 公众号是否开启会员功能
        }

        if (!empty($fans_mapping)) {
            # 公众号信息存在则更新
//            pdo_update('mc_mapping_fans', $fans_update_info, array('fanid' => $fans_mapping['fanid']));
        } else {
            # 未存在则插入用户信息
            $fans_update_info['salt'] = random(8);
            $fans_update_info['unfollowtime'] = 0;
            $fans_update_info['followtime'] = time();

//            pdo_insert('mc_mapping_fans', $fans_update_info);
            $fans_mapping['fanid'] = pdo_insertid();
        }
        #如果用户存在标签
        if (!empty($fans['tagid_list'])) {
            $groupid = $fans['tagid_list'];
            @sort($groupid, SORT_NATURAL);
        }
        return $fans_update_info;
    }
}