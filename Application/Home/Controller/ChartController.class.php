<?php
namespace Home\Controller;
use Think\Controller;
class ChartController extends BaseController {
    public function index(){
        $uid = session('uid');
        $y = I('get.year', date('Y'), 'int');
        $DataJson = getYearData($y,$uid);
        //echo $DataJson;
        if($DataJson['Year'] == "FALSE") echo "<meta http-equiv=refresh content='0; url=index.php'>";
        $DataArray = json_decode($DataJson,TRUE);
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