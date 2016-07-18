<?php
namespace Home\Controller;
use Think\Controller;
class ChartController extends BaseController {
    public function index(){
        $uid = session('uid');
        $y = I('get.year', date('Y'), 'int');
        $DataJson = getYearData($y,$uid);
        // dump($DataJson) ;
        $DataArray = json_decode($DataJson,TRUE);
        if($DataArray['Year'] == "FALSE") {
            ShowAlert('这里已脱离服务范围，请穿越回'.date('Y').'年...',U('Home/Chart/index'));
            $this -> display('Public/base');
        }elseif ($DataArray['InSumMoney'] + $DataArray['OutSumMoney'] === 0) {
            //无数据
        }
        $JsonInMoney = ArrayToNumData($DataArray['InMoney']);
        $JsonOutMoney = ArrayToNumData($DataArray['OutMoney']);
        $JsonInClassPer = ArrayKeyToNumData($DataArray['InSumClassMoney']);
        $JsonOutClassPer = ArrayKeyToNumData($DataArray['OutSumClassMoney']);
        $JsonSurplusMoney= ArrayToNumData($DataArray['SurplusMoney']);
        $JsonSurplusSumMoney= ArrayToNumData($DataArray['SurplusSumMoney']);

        $this -> assign('y',$y);
        $this -> assign('DataJson',$DataJson);
        $this -> assign('DataArray',$DataArray);
        $this -> assign('JsonInMoney',$JsonInMoney);
        $this -> assign('JsonOutMoney',$JsonOutMoney);
        $this -> assign('JsonInClassPer',$JsonInClassPer);
        $this -> assign('JsonOutClassPer',$JsonOutClassPer);
        $this -> assign('JsonSurplusMoney',$JsonSurplusMoney);
        $this -> assign('JsonSurplusSumMoney',$JsonSurplusSumMoney);

        $this -> display();
    }
}