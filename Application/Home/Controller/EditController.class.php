<?php
namespace Home\Controller;
use Think\Controller;
class EditController extends BaseController {
    public function _initialize(){
        $uid = session('uid');
        $id = I('get.id', 0, 'int');
        if ($id == 0) {
            $id = I('post.acid', 0, 'int');
        }
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
            $data['fid']       = I('post.edit_funds');
            $Updata = UpdataAccountData($data);
            if($Updata[0]){
                ClearDataCache(); //清除缓存
                ShowAlert($Updata[1],$refURL);
                $this -> display('Public/common');   
            }else{
                ShowAlert($Updata[1],$refURL);
                $this -> display('Public/common');
            }
        }else{
            $DbData = GetIdData($id);
            $FundsData = GetFundsData($uid);
            $DbFunds = $DbData['fid'];
            $MoneyClass[1] = GetClassData($uid,1);
            $MoneyClass[2] = GetClassData($uid,2);
            $DbClass = $MoneyClass[$DbData['zhifu']];
            $ShowData = ArrDataToShowData($DbData, $DbClass);
            $ImageData = GetImageData($uid, $id);
            if($DbData) {
                $this -> assign('acid',$id);
                $this -> assign('refURL',$refURL);
                $this -> assign('DbFunds',$DbFunds);
                $this -> assign('FundsData',$FundsData);
                $this -> assign('DbClass',$DbClass);
                $this -> assign('ShowData',$ShowData);
                $this -> assign('ImageData',$ImageData[0] ? $ImageData[1] : false);
                $this -> assign('MoneyClass',"'".htmlspecialchars(json_encode($MoneyClass))."'");
                $this -> display();
            }else{
                ShowAlert("非法操作~",$refURL);
                $this -> display('Public/common');
                // $this -> error("非法操作~",$refURL);
            }
        }
    }
    
    //删除记账
    public function del(){
        $refURL = GetRefURL();
        $Msg = DelIdData(I('get.id'));
        if($Msg[0]) {
            ClearDataCache(); //清除缓存
            ShowAlert($Msg[1],$refURL);
            $this -> display('Public/common');
        }else{
            ShowAlert($Msg[1],$refURL);
            $this -> display('Public/common');
        }
    }

    //修改图片对应的记账ID
    public function image() {
        $uid = session('uid');
        $acid  = I('post.acid', 0, 'int');
        $id = I('post.id', 0, 'int');
        if(IS_POST && $acid > 0 && $id > 0) {
            die(json_encode(EditImageAcid($uid, $id, $acid)));
        } else {
            ShowAlert('无效的操作。' ,GetRefURL());
            $this -> display('Public/common');
        }
    }

    //删除图片
    public function deleteImage(){
        $uid = session('uid');
        $acid  = I('post.acid', 0, 'int');
        $id = I('post.id', 0, 'int');
        if(IS_POST && $acid > 0 && $id > 0) {
            die(json_encode(DelImageData($uid, $acid, $id)));
        } else {
            ShowAlert('无效的操作。' ,GetRefURL());
            $this -> display('Public/common');
        }
    }
    
}