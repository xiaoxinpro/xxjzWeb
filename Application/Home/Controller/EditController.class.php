<?php
namespace Home\Controller;
use Think\Controller;
class EditController extends BaseController {
    private $uid;
    private $id;
    private $transfer;

    //检测acid与uid是否匹配
    private function cheak_acid() {
        if(!CheakIdShell($this->id, $this->uid)){
            $this -> error("非法操作!");
        }
    }

    //检测tid与uid是否匹配
    private function cheak_tid() {
        $transferData = GetTransferIdData($this->id, $this->uid);
        if(!$transferData[0]){
            $this->error($transferData[1]);
        } else {
            $this->transfer = $transferData[1];
        }
    }

    public function _initialize(){
        $uid = intval(session('uid'));
        $id = I('get.id', 0, 'int');
        if ($id == 0) {
            $id = I('post.acid', 0, 'int');
        }
        $this->uid = $uid;
        $this->id = $id; 
    }
    
    public function index() {
        $this->cheak_acid();
        $uid = $this->uid;
        $id  = $this->id;
        if (!stripos($_SERVER['HTTP_REFERER'], __SELF__)) {
            SetRefURL($_SERVER['HTTP_REFERER']);
        }
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
        $this->cheak_acid();
        $uid = $this->uid;
        $id  = $this->id;
        $refURL = GetRefURL();
        $Msg = DelIdData($uid, $id);
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
        $this->cheak_acid();
        $uid = $this->uid;
        $acid  = $this->id;
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
        $this->cheak_acid();
        $uid = $this->uid;
        $acid  = $this->id;
        $id = I('post.id', 0, 'int');
        if(IS_POST && $acid > 0 && $id > 0) {
            die(json_encode(DelImageData($uid, $acid, $id)));
        } else {
            ShowAlert('无效的操作。' ,GetRefURL());
            $this -> display('Public/common');
        }
    }

    //编辑转账
    public function transfer() {
        $this->cheak_tid();
        $uid = $this->uid;
        $tid  = $this->id;
        $transfer = $this->transfer;
        if (!stripos($_SERVER['HTTP_REFERER'], __SELF__)) {
            SetRefURL($_SERVER['HTTP_REFERER']);
        }
        $refURL = GetRefURL();
        if (IS_POST) {
            $data = array();
            $data['money'] = I('post.transfer_money', 0, 'float');
            $data['source_fid'] = I('post.transfer_funds_source', 0, 'int');
            $data['target_fid'] = I('post.transfer_funds_target', 0, 'int');
            $data['mark'] = I('post.transfer_mark', '');
            $data['time'] = I('post.transfer_time', '');
            $data['uid'] = $uid;
            $Updata = EditTransferData($tid, $data);
            if($Updata[0]){
                ClearDataCache(); //清除缓存
                ShowAlert($Updata[1],$refURL);
                $this -> display('Public/common');   
            }else{
                ShowAlert($Updata[1],$refURL);
                $this -> display('Public/common');
            }
        } else {
            SetRefURL($_SERVER['HTTP_REFERER']);
            $FundsData = GetFundsData($uid);

            $this -> assign('refURL',$_SERVER['HTTP_REFERER']);
            $this -> assign('TransferData',$transfer);
            $this -> assign('FundsData',$FundsData);

            $this -> display();            
        }
    }

    //删除转账
    public function deleteTransfer() {
        $this->cheak_tid();
        $uid = $this->uid;
        $tid  = $this->id;
        $refURL = GetRefURL();
        $Msg = DelTransferData($tid, $uid);
        if($Msg[0]) {
            ClearDataCache(); //清除缓存
            ShowAlert($Msg[1],$refURL);
            $this -> display('Public/common');
        }else{
            ShowAlert($Msg[1],$refURL);
            $this -> display('Public/common');
        }
    }
    
}