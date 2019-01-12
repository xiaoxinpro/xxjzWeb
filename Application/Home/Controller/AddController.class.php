<?php
namespace Home\Controller;
use Think\Controller;
class AddController extends BaseController {
    public function index(){
        $uid = session('uid');
        $type = I('get.type',2);
        $refURL = GetRefURL();
        if(IS_POST){
            //$data = array('acid' => $id);
            $data['acmoney']   = I('post.add_money');
            $data['acclassid'] = I('post.add_class');
            $data['actime']    = I('post.add_time');
            $data['acremark']  = I('post.add_mark');
            $data['zhifu']     = I('post.add_type');
            $data['fid']       = I('post.add_funds');
            $data['jiid']      = $uid;
            $Updata = AddAccountData($data);
            
            ClearDataCache(); //清除缓存
            $type = $data['zhifu'];
            ShowAlert($Updata[1]);
            // if($Updata[0]){
            //     ClearDataCache(); //清除缓存
            //     $this -> success($Updata[1],U('Home/Add/index/type/'.$data['zhifu']));
            // }else{
            //     $this -> error($Updata[1],U('Home/Add/index/type/'.$data['zhifu']));
            // }
        }
        // SetRefURL(__SELF__);
        $FundsData = GetFundsData($uid);
        $MoneyClass[1] = GetClassData($uid,1);
        $MoneyClass[2] = GetClassData($uid,2);
        if (!is_array($MoneyClass[2])) {
            ShowAlert('请先添加记账分类', U('/Home/Class/index'));
        } elseif (!is_array($MoneyClass[1])) {
            ShowAlert('【收入】分类也要添加的！', U('/Home/Class/index/class/in'));
        }
        $this -> assign('type',$type);
        $this -> assign('refURL',$refURL);
        $this -> assign('FundsData',$FundsData);
        $this -> assign('inMoneyClass',$MoneyClass[1]);
        $this -> assign('outMoneyClass',$MoneyClass[2]);
        $this -> assign('MoneyClass',"'".htmlspecialchars(json_encode($MoneyClass))."'");
        
        //整合List表格数组
        $ListData = OutListData(GetAccountData($uid, 1),GetClassData($uid));
        $this -> assign('Page', 1);
        $this -> assign('PageMax', 1);
        $this -> assign('ArrPage', $ListData[2]);
        $this -> assign('ShowData', $ListData[3]);

        $this -> display();

    }
    
}