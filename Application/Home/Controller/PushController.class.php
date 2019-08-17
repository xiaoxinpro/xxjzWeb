<?php
namespace Home\Controller;
use Think\Controller;
class PushController extends Controller {
    protected $token_auth = 0;

    // 前置函数，验证Push权限
    public function _initialize() {
        //验证Push权限说明
        //普通key = user_config数据库push.key，用于一般推送专用key
        //管理key = md5(C('WX_OPENID'))，用于推送与管理普通key
        //接口time = 为当前时间戳（不可重复使用）
        //接口token = md5(key + time)
        //https://ide.xiaoxin.pro/xxjzApp/index.php/Home/Push/test.html?token=b708f9b4ed6b94f03a2d5d204b5e6648&time=123

        $token = I('get.token', 'null');
        $times = I('get.time', 0, 'int');
        //验证time有效性
        if ($times <= S('push_time')) {
            die(json_encode(array('err'=>'10001','msg'=>'访问已超过其时效性'), true));
        }
        //验证管理员权限
        if (md5(md5(C('WX_OPENID')).''.$times) == $token) {
            $this->token_auth = 255;
        } else {
            $key = D('UserConfig')->getConfig('push_key', 'Weixin', 0);
            if ($key && md5($key.''.$times) == $token) {
                $this->token_auth = 1;
            } else {
                die(json_encode(array('err'=>'10010','msg'=>'权限验证失败'), true));
            }
        }
        S('push_time', $times);
    }

    public function test() {
        dump('test page');
        dump($this->token_auth);
    }

    public function month() {
        $month = (date('m') == 12) ? 1 : intval(date('m')) - 1;
        $year = ($month == 12) ? intval(date('Y')) - 1 : intval(date('Y'));

        //获取uid列表
        $uidList = D('UserPush')->getUidList();
        foreach ($uidList as $key => $uid) {
            //检测推送权限
            if (D('UserConfig')->getConfig('push_month', 'Weixin', $uid) != 1) {
                dump('uid='.$uid.'未开启推送，跳过推送。');
                continue;
            }
            //枚举uid获取数据库数据
            $pageParam = array();
            $pageParam['jiid'] = $uid;
            $pageParam['gettype'] = 'month';
            $pageParam['year'] = $year;
            $pageParam['month'] = $month;
            $data = GetDateAccountData($uid, $pageParam);
            $inMoney = number_format($data['SumInMoney'], 2);
            $outMoney = number_format($data['SumOutMoney'], 2);
            $countData = number_format($data['count'], 0);
            //发送推送
            $template_id = D('UserPush')->getWeixinTemplateId('本月记账成功通知');
            $arrData = array();
            $arrData[0] = $outMoney . "元";
            $arrData[1] = $inMoney . "元";
            $arrData[2] = "本月共记账" . $countData . "笔";
            $arrData[3] = $year . "年" . $month . "月份";
            dump('uid='.$uid.'触发推送。');
            dump($arrData);
            $push = D('UserPush')->sendWeixinPush($template_id, $uid, 'month', $arrData);
            dump($push);
        }
    }
}