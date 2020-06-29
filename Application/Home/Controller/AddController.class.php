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

            $upload = UploadFile($uid);
            if ($upload && $Updata[0]) {
                AddImageData($uid, $upload, $Updata[2]);
            }
            
            ClearDataCache(); //清除缓存
            $type = $data['zhifu'];
            ShowAlert($Updata[1]);
        }
        //获取资金账户数据
        $DbFunds = array();
        $FundsData = GetFundsData($uid);
        foreach ($FundsData as $key => $data) {
            $DbFunds[$data[id]] = $data[name];
        }

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
        $ListData = OutListData(GetAccountData($uid, 1),GetClassData($uid),$DbFunds);
        $this -> assign('ShowData', $ListData[2]);

        $this -> display();
    }

    public function transfer() {
        $uid = session('uid');
        if (IS_POST) {
            $data['money'] = I('post.transfer_money', 0, 'float');
            $data['source_fid'] = I('post.transfer_funds_source', 0, 'int');
            $data['target_fid'] = I('post.transfer_funds_target', 0, 'int');
            $data['mark'] = I('post.transfer_mark', '');
            $data['time'] = I('post.transfer_time', '');
            $data['uid'] = $uid;
            $Updata = AddTransferData($data);
            ShowAlert($Updata[1]);
        }

        //获取资金账户数据
        $FundsData = GetFundsData($uid);
        $TransferData = GetFundsIdTransferData($uid, 1);
        // dump($FundsData);

        $this -> assign('refURL', GetRefURL());
        $this -> assign('FundsData',$FundsData);
        $this -> assign('TransferData', $TransferData['data']);
        // $this -> assign('TransferPage', $TransferData['page']);
        // $this -> assign('TransferPageMax', $TransferData['pagemax']);

        $this -> display();
    }

    public function upload() {
        $arrData = array();

        //验证登录
        $uid = session('uid');
        if ($uid > 0) {
            $arrData['uid'] = $uid;
        } else {
            $arrData['uid'] = 0;
            $arrData['data'] = "用户未登录，请重新登录！";
            die(json_encode($arrData));
        }

        //获取记账ID
        $acid = I('get.acid', 0, 'int');
        if ($acid == 0) {
            $acid = I('post.acid', 0, 'int');
        }

        if (IS_POST) {
            $upload = UploadFile($uid);
            if ($upload) {
                $ret = AddImageData($uid, $upload, $acid);
                $arrData['upload'] = $ret;
                $arrData['data'] = "上传成功！";
            } else {
                $arrData['upload'] = false;
                $arrData['data'] = "上传失败，文件不符合服务器要求，请检查后再试。";
            }
        } else {
            $arrData['uid'] = 0;
            $arrData['data'] = "无效的请求，请根据协议发送请求！";
        }
        die(json_encode($arrData));
    }
    
}