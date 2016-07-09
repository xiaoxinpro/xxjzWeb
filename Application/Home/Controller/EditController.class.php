<?php
namespace Home\Controller;
use Think\Controller;
class EditController extends BaseController {
    public function _initialize(){
        $uid = session('uid');
        $id = I('get.id');
        if(!CheakIdShell($id, $uid)){
            $this -> error("非法操作!");
        }
    }
    
    public function index(){
        $uid = session('uid');
        $id  = I('get.id');
        $refURL = GetRefURL();
        if(IS_POST){
            $data = array('acid' => $id);
            $data['acmoney']   = I('post.edit_money');
            $data['acclassid'] = I('post.edit_class');
            $data['actime']    = I('post.edit_time');
            $data['acremark']  = I('post.edit_mark');
            $data['zhifu']     = I('post.edit_type');
            $Updata = UpdataAccountData($data);
            if($Updata[0]){
                ClearDataCache(); //清除缓存
                ShowAlert($Updata[1],$refURL);
                $this -> display('Public/common');
                // $this -> success($Updata[1],$refURL);
            }else{
                ShowAlert($Updata[1],$refURL);
                $this -> display('Public/common');
                // $this -> error($Updata[1],$refURL);
            }
        }else{
            $DbData = GetIdData($id);
            $MoneyClass[1] = GetClassData($uid,1);
            $MoneyClass[2] = GetClassData($uid,2);
            $DbClass = $MoneyClass[$DbData['zhifu']];
            $ShowData = ArrDataToShowData($DbData, $DbClass);
            if($DbData) {
                $this -> assign('refURL',$refURL);
                $this -> assign('DbClass',$DbClass);
                $this -> assign('ShowData',$ShowData);
                $this -> assign('MoneyClass',"'".htmlspecialchars(json_encode($MoneyClass))."'");
                $this -> display();
            }else{
                ShowAlert("非法操作~",$refURL);
                $this -> display('Public/common');
                // $this -> error("非法操作~",$refURL);
            }
        }
    }
    
    public function del(){
        $refURL = GetRefURL();
        $Msg = DelIdData(I('get.id'));
        if($Msg[0]) {
            ClearDataCache(); //清除缓存
            ShowAlert($Msg[1],$refURL);
            $this -> display('Public/common');
            // $this -> success($Msg[1],$refURL);
        }else{
            ShowAlert($Msg[1],$refURL);
            $this -> display('Public/common');
            // $this -> error($Msg[1],$refURL);
        }
    }
    
}