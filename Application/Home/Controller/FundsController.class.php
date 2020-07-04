<?php
namespace Home\Controller;
use Think\Controller;
class FundsController extends BaseController {
    public function index(){
        $uid = session('uid');
        $p = I('get.p', 1, 'intval');

        if (IS_POST) {
            $strFundsName = I('post.funds_name');
            $numFundsMoney = I('post.funds_money', 0, 'float');
            $Updata = AddNewFunds($strFundsName, $numFundsMoney, $uid);
            ClearDataCache(); //清除缓存
            ShowAlert($Updata[1]);
        }
        $FundsData = GetFundsData($uid);
        $TransferData = GetFundsIdTransferData($uid, $p);
        // dump($FundsData);
        // dump($TransferData);
        $this -> assign('FundsData', $FundsData);
        $this -> assign('TransferData', $TransferData['data']);
        $this -> assign('TransferPage', $TransferData['page']);
        $this -> assign('TransferPageMax', $TransferData['pagemax']);
        $this -> display();
    }

    public function edit(){
        $uid = session('uid');
        $fundsid = I('get.id', false, 'int');
        if ($fundsid) {
            if (IS_POST) {
                $fundsSubmit = I('post.funds_submit','');
                if ($fundsSubmit === '编辑') {
                    $fundsName = I('post.funds_name','');
                    $fundsMoney = I('post.funds_money', 0, 'float');
                    $ret = EditFundsName($fundsid, $fundsName, $uid);
                    if ($ret[0]) {
                        $ret = EditFundsDefaultMoney($fundsid, $fundsMoney, $uid);
                        if ($ret[0]) {
                            ShowAlert('账户编辑完成',U('Home/Funds/index'));
                        } else {
                            ShowAlert($ret[1],U('Home/Funds/edit/id/'.$fundsid));
                        }
                    } else {
                        ShowAlert($ret[1],U('Home/Funds/edit/id/'.$fundsid));
                    }
                    $this -> display('Public/base');
                } elseif ($fundsSubmit === '删除') {
                    $fundsChange = I('post.funds_change','',int);
                    $ret = DeleteFunds($fundsid, $uid, $fundsChange);
                    ShowAlert($ret[1],U('Home/Funds/index'));
                    $this -> display('Public/base');
                } else {
                    $this -> error('非法操作...');
                }
            } else {
                $this -> assign('FundsId', $fundsid);
                $this -> assign('FundsData', GetFundsData($uid));
                $this -> assign('FundsMoney', GetFundsAccountSumData($fundsid, $uid));
                if ($fundsid > 0) {
                    $DbFunds = GetFundsIdData($fundsid, $uid);
                    if ($DbFunds[0]) {
                        $this -> assign('FundsName', $DbFunds[1]['fundsname']);
                    } else {
                        $this -> error($DbData[1]);
                    }
                }
                $this -> display();  
            }
        } else {
            $this -> error('非法操作...');
        }
    }

    public function sort() {
        $uid = session('uid');
        if (IS_POST) {
            $fundsIdList = json_decode(I('post.data','[]'), true);
            if (count($fundsIdList) > 0) {
                SortFunds($fundsIdList, $uid);
            }
            ShowAlert("账户排序修改完成！",U('Home/Funds/index'));
            $this -> display('Public/base');
        } else {
            $this -> error('非法操作...');
        }
    }
}