<?php
namespace Home\Controller;
use Think\Controller;
class FindController extends BaseController {
    private $isTransfer = false;
    public function index(){
        $uid = session('uid');
        $p = I('get.p', 1, 'intval');
        $ShowFind = 1;
        if(IS_POST){
            $ClassValue = I('post.find_class');
            $TypeValue = I('post.find_type');
            if($TypeValue === '2'){
                $data['zhifu']     = '2';
            }else if($TypeValue === '1'){
                $data['zhifu']     = '1';
            }
            if ($ClassValue !== '') {
                $data['acclassid'] = $ClassValue;
                // $data['acclassid'] = array('75', '1031');
            }
            $data['starttime'] = I('post.find_start_time');
            $data['endtime']   = I('post.find_end_time');
            $data['acremark']  = I('post.find_mark');
            $data['fid']       = I('post.find_funds');
            $data['jiid']      = $uid;
            
            //更新缓存
            ClearFindCache(); //清除查询缓存
            S('find_data_'.$uid,$data);
            S('find_data_class_'.$uid,$ClassValue);
            S('find_data_type_'.$uid,$TypeValue);
        }else{
            //读取查询缓存
            $data = S('find_data_'.$uid);
            $ClassValue = S('find_data_class_'.$uid);
            $TypeValue = S('find_data_type_'.$uid);
        }

        if ($TypeValue == "3" && $ClassValue === '') {
            $this->isTransfer = true;
            $data['acclassid'] = "transfer";
        }

        //表单信息
        $this -> assign('inClassData',GetClassData($uid,1));
        $this -> assign('outClassData',GetClassData($uid,2));
        $this -> assign('FundsData',GetFundsData($uid));
        
        if($data) {
            //不显示搜索
            $ShowFind = 0;
            
            //设置返回页
            SetRefURL(__ACTION__);
            
            //输出查询信息
            $this -> assign('FindData',$data);
            $this -> assign('FindDataClass',$ClassValue);
            $this -> assign('FindDataType',$TypeValue);
            if ($this->isTransfer) {
                $DbTransfer = FindTransferData($data, $p);
                $this -> assign('Page', $DbTransfer['Page']);
                $this -> assign('PageMax', $DbTransfer['PageMax']);
                $this -> assign('TransferData', $DbTransfer['data']);
                $this -> display('transfer');
            } else {
                $DbAccount = FindTransferAccountData($data, $p);
                $this -> assign('SumInMoney', $DbAccount['SumInMoney']);
                $this -> assign('SumOutMoney', $DbAccount['SumOutMoney']);
                $this -> assign('Page', $DbAccount['page']);
                $this -> assign('PageMax', $DbAccount['pagemax']);
                $this -> assign('ShowData', $DbAccount['data']);
                $this -> assign('isTransfer', $DbAccount['isTransfer']);
                $this -> display();
            }
        } else {
            $this -> assign('ShowFind', 1);
            $this -> display();
        }
    }
    
    public function reboot() {
        ClearFindCache(); //清除查询缓存
        $this -> redirect('Home/Find/index');
    }
}