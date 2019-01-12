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
        $FundsData = GetFundsData($uid);
        // dump($FundsData);
        $this -> assign('ShowData', $FundsData);
        $this -> display();
    }
}