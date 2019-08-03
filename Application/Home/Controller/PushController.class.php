<?php
namespace Home\Controller;
use Think\Controller;
class PushController extends Controller {
    public function month() {
        $month = (date('m') == 12) ? 1 : intval(date('m')) - 1;
        $year = ($month == 12) ? intval(date('Y')) - 1 : intval(date('Y'));

        //获取uid列表
        $uidList = D('UserPush')->getUidList();
        foreach ($uidList as $key => $uid) {
            //枚举uid获取数据库数据
            $pageParam = array();
            $pageParam['jiid'] = $uid;
            $pageParam['gettype'] = 'month';
            $pageParam['year'] = $year;
            $pageParam['month'] = $month;
            $data = GetDateAccountData($uid, $pageParam);
            $inMoney = $data['SumInMoney'];
            $outMoney = $data['SumOutMoney'];
            $countData = $data['count'];
            //发送推送
            $template_id = D('UserPush')->getWeixinTemplateId('本月记账成功通知');
            $arrData = array();
            $arrData[0] = $outMoney . "元";
            $arrData[1] = $inMoney . "元";
            $arrData[2] = "本月共记账" . $countData . "笔";
            $arrData[3] = $year . "年" . $month . "月份";
            $push = D('UserPush')->sendWeixinPush($template_id, $uid, 'month', $arrData);
            dump($push);
        }
    }
}