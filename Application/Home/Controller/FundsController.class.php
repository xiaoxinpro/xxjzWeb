<?php
namespace Home\Controller;
use Think\Controller;
class FundsController extends BaseController {
    public function index(){
        $uid = session('uid');

        if (IS_POST) {
            $strFundsName = I('post.funds_name');
            $Updata = AddNewFunds($strFundsName, $uid);
            ClearDataCache(); //清除缓存
            ShowAlert($Updata[1]);
        }
        GetFundsData($uid);
        $default = array();
        $default['inmoney'] = 100;
        $default['outmoney'] = 200;
        $default['count'] = 95;
        $this -> assign('default',$default);
        $this -> display();
    }
}