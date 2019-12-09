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
            // die(json_encode(array('err'=>'10001','msg'=>'访问已超过其时效性'), true));
        }
        //验证管理员权限
        if (PushApiToken(md5(C('WX_OPENID')), $times)['token'] == $token) {
            $this->token_key = md5(C('WX_OPENID'));
            $this->token_auth = 255;
        } else {
            $key = D('UserConfig')->getConfig('push_key', 'Weixin', 0);
            if ($key && PushApiToken($key, $times)['token'] == $token) {
                $this->token_key = $key;
                $this->token_auth = 1;
            } else {
                die(json_encode(array('err'=>'10010','msg'=>'权限验证失败'), true));
            }
        }
        S('push_time', $times);
    }

    // 推送管理，必须使用管理权限登录
    public function admin() {
        if ($this->token_auth < 255) {
            die(json_encode(array('err'=>'10011','msg'=>'权限不足，请使用管理员权限登录。'), true));
        }
        S('push_time', null);

        //POST
        if (IS_POST) {
            switch (I('post.form')) {
                case 'default':
                    D('UserConfig')->setConfig('push_month', I('post.default_push_month', '0'), 'Weixin', 0);
                    D('UserConfig')->setConfig('push_punch', I('post.default_push_punch', '0'), 'Weixin', 0);
                    break;
                case 'add_key':
                    D('UserConfig')->setConfig('push_key', I('post.push_key', '0'), 'Weixin', 0);
                    break;
                default:
                    # code...
                    break;
            }
            $this -> assign('message', '表单提交完成。');
        }
        // 更新Token密钥
        $tokenObj = PushApiToken($this->token_key);
        $this -> assign('tokenObj', $tokenObj);

        //同步显示数据
        $this -> assign('push_month', D('UserConfig')->getConfig('push_month', 'Weixin', 0));
        $this -> assign('push_punch', D('UserConfig')->getConfig('push_punch', 'Weixin', 0));
        $this -> assign('push_key', D('UserConfig')->getConfig('push_key', 'Weixin', 0));
        
        //实例化显示
        $this -> display();
    }

    // 月账单推送
    public function month() {
        // $month = (date('m') == 1) ? 12 : intval(date('m')) - 1;
        // $year = ($month == 12) ? intval(date('Y')) - 1 : intval(date('Y'));
        $month = intval(date('m'));
        $year = intval(date('Y'));
        $template_id = D('UserPush')->getWeixinTemplateId('本月记账成功通知');
        if ($template_id == false) {
            die("Error weixin template id not found.");
        }

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

    // 打卡提醒
    public function punch() {
        $template_id = D('UserPush')->getWeixinTemplateId('打卡提醒');
        if ($template_id == false) {
            die("Error weixin template id not found.");
        }

        //获取uid列表
        $uidList = D('UserPush')->getUidList();
        foreach ($uidList as $key => $uid) {
            //获取用户打卡推送配置
            $punchNum = intval(D('UserConfig')->getConfig('push_punch', 'Weixin', $uid));
            if ($punchNum <= 0) {
                dump('uid='.$uid.'未开启推送，跳过推送。');
                continue;
            }
            $punchDay = date("Y-m-d",strtotime('-'.$punchNum.' day'));
            $startTime = strtotime($punchDay." 00:00:00");
            $endTime = strtotime($punchDay." 23:59:59");

            //获取推送数据
            $arrSql = array(
                "uid" => $uid,
                "push_name" => "Weixin",
                "push_mark" => "Model.account.add",
                "time" => array(array('egt',$startTime),array('elt',$endTime)),
            );
            $dbData = M('UserPush')->where($arrSql)->order('time desc')->find();
            if ($dbData == null) {
                dump('uid='.$uid.'没有可推送的数据，跳过推送。');
                continue;
            }
            $dbData['time'] = date("Y-m-d H:i:s", $dbData['time']);
            // dump($dbData);

            //发送推送
            $arrData = array();
            $arrData[0] = "小歆记账打卡"; //打卡名称
            $arrData[1] = "记账"; //打卡方式
            $arrData[2] = $dbData['time']; //打卡时间
            $arrData[3] = "您已经".$punchNum."天没有记账了，赶紧“进入小程序”开始记账吧。"; //完成情况
            dump('uid='.$uid.'触发推送。');
            dump($arrData);
            $push = D('UserPush')->sendWeixinPush($template_id, $uid, 'add', $arrData);
        }
    }

    // 测试接口
    public function test() {
        dump('test page');
        dump(date('m'));
    }
}