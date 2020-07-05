<?php
namespace Home\Controller;
use Think\Controller;
class ClassController extends BaseController {
    public function index(){
        $uid = session('uid');
        $ClassType = I('get.class', 2);
        
        if(IS_POST){
            $data = array();
            $data['classname'] = I('post.class_name');
            $data['classtype'] = I('post.class_type/d');
            $data['ufid']      = $uid;
            $Updata = AddNewClass($data);
            ClearDataCache(); //清除缓存
            $ClassType = $data['classtype'];
            ShowAlert($Updata[1]);
        }
        
        $MoneyClass[1] = GetClassData($uid,1);
        $MoneyClass[2] = GetClassData($uid,2);
        $this -> assign('ClassType',$ClassType);
        $this -> assign('inMoneyClass',$MoneyClass[1]);
        $this -> assign('outMoneyClass',$MoneyClass[2]);
        $this -> display();
    }
    
    public function edit(){
        $uid = session('uid');
        $ClassId = I('get.id/d');
        if($ClassId){
            $DbData = GetClassIdData($ClassId,$uid);
            if(IS_POST){
                $ClassName = I('post.class_name');
                $ClassType = I('post.class_type/d');
                ClearDataCache(); //清除缓存
                if(intval($DbData[1]['classtype']) != intval($ClassType)) {
                    $Change = ChangeClassType($ClassId,$uid);
                    if(!$Change[0]){
                        ShowAlert($Change[1],U('Home/Class/index/type/'.$ClassType));
                        $this -> display('Public/base');
                    }
                }
                $ret = editClassName($ClassName, $ClassId, $ClassType, $uid);
                ShowAlert($ret[1],U('Home/Class/index/type/'.$ClassType));
                $this -> display('Public/base');
            }else{
                if($DbData[0]){
                    $this -> assign('ClassId',$ClassId);
                    $this -> assign('ClassName',$DbData[1]['classname']);
                    $this -> assign('ClassType',$DbData[1]['classtype']);
                    $this -> display();
                }else{
                    $this -> error($DbData[1]);
                }
            }
        }else{
            $this -> error('非法操作...');
        }
    }

    public function sort() {
        $uid = session('uid');
        if (IS_POST) {
            $classType = I('post.type','new');
            $classIdList = json_decode(I('post.data','[]'), true);
            if (count($classIdList) > 0) {
                SortClass($classIdList, $uid);
            }
            ShowAlert("分类排序修改完成！",U('Home/Class/index#'.$classType));
            $this -> display('Public/base');
        } else {
            $this -> error('非法操作...');
        }
    }
    
    public function change(){
        $uid = session('uid');
        $ClassId = I('get.id');
        if($ClassId){
            $Change = ChangeClassType($ClassId,$uid);
            if($Change[0]){
                ClearDataCache(); //清除缓存
                ShowAlert("分类变更成功!",U('Home/Class/index/type/'.$Change[1]));
                $this -> display('Public/base');
                // $this -> success("分类变更成功!",U('Home/Class/index/type/'.$Change[1]));
            }else{
                ShowAlert($Change[1],U('Home/Class/index/type/2'));
                $this -> display('Public/base');
                // $this -> error($Change[1],U('Home/Class/index/type/'.$ClassId));
            }
        }else{
            $this -> error('非法操作');
        }
    }
    
    public function del(){
        $uid = session('uid');
        $ClassId = intval(I('get.id'));
        if($ClassId){
            if (intval(GetClassAccountNum($ClassId,$uid)) > 0) {
                $DbData = GetClassIdData($ClassId,$uid);
                ShowAlert('【'.$DbData[1]['classname'].'】分类数据不为空，请先处理记账数据！',U('Home/Class/proc/id/'.$ClassId));
                $this -> display('Public/base');
            }else{
                $retData = DelClass($ClassId,$uid);
                if($retData[0]){
                    ClearDataCache(); //清除缓存
                    ShowAlert($retData[1],U('Home/Class/index/type/'.$retData[2]));
                    $this -> display('Public/base');
                    // $this -> success($retData[1],U('Home/Class/index/type/'.$retData[2]));
                }else{
                    ShowAlert($retData[1],U('Home/Class/index/type/'.$retData[2]));
                    $this -> display('Public/base');
                    // $this -> error($retData[1],U('Home/Class/index/type/'.$retData[2]));
                }
            }
        }else{
            $this -> error('非法操作');
        }
    }

    public function proc(){
        $uid = session('uid');
        $ClassId = intval(I('get.id','','int'));
        if($ClassId){
            if(IS_POST){
                if (I('post.proc_submit') === '转移并删除') {
                    $ClassId2 = I('post.class_id_2','','int');
                    $ClassData2 = GetClassIdData($ClassId2, $uid);
                    if($ClassData2[0]){
                        $ClassName2 = $ClassData2[1]['classname'];
                        $ret = MoveClassAccount($ClassId, $ClassId2, $uid);
                        ShowAlert('已成功转移'.$ret.'条数据到【'.$ClassName2.'】中！',U('Home/Class/del/id/'.$ClassId));
                        $this -> display('Public/base');
                    }else{
                        ShowAlert('没有检测到可转移的分类，请添加分类再试...', U('Home/Class/index'));
                        $this -> display('Public/base');
                    }
                }
            }elseif(!GetClassAccountNum($ClassId,$uid)) {
                ShowAlert('未检测到分类数据！',U('Home/Class/del/id/'.$ClassId));
                $this -> display('Public/base');
            }else{
                $DbData = GetClassIdData($ClassId,$uid);
                $ClassData = GetClassData($uid,$DbData[1]['classtype']);
                if($DbData[0]){
                    $this -> assign('ClassId',$ClassId);
                    $this -> assign('ClassName',$DbData[1]['classname']);
                    $this -> assign('ClassType',$DbData[1]['classtype']);
                    $this -> assign('ClassData',$ClassData);

                    if (count($ClassData) <= 1) {
                        ShowAlert('没有检测到可转移的分类!');
                    }

                    //不显示搜索
                    $ShowFind = 0;
                    //设置返回页
                    SetRefURL(__ACTION__.'/id/'.$ClassId);
                    //获取指定页数据
                    $data = array('acclassid' => $ClassId, 'jiid' => $uid );
                    $DbAccount = FindAccountData($data);
                    $this -> assign('SumInMoney', $DbAccount['SumInMoney']);
                    $this -> assign('SumOutMoney', $DbAccount['SumOutMoney']);
                    //获取分类列表
                    $DbClass = GetClassData($uid);
                    //整合List表格数组
                    $ListData = OutListData($DbAccount,$DbClass);
                    $this -> assign('Page', $ListData[0]);
                    $this -> assign('PageMax', $ListData[1]);
                    $this -> assign('ShowData', $ListData[2]);

                    $this -> display();
                }else{
                    ShowAlert($DbData[1],U('Home/Index/index'));
                    $this -> display('Public/base');
                }
            }
        }else{
            $this -> error('非法操作');
        }
    }

}