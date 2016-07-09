<?php
namespace Home\Controller;
use Think\Controller;
class ChartController extends BaseController {
    public function index(){
	    $y = I('get.year', 2016, 'int');
	    $DataJson = getYearData($y);
	    //echo $DataJson;
	    if($DataJson['Year'] == "FALSE") echo "<meta http-equiv=refresh content='0; url=index.php'>";
	    $DataArray = json_decode($DataJson,TRUE);
	    $JsonInMoney = ArrayToNumData($DataArray['InMoney']);
	    $JsonOutMoney = ArrayToNumData($DataArray['OutMoney']);
	    $JsonInClassPer = ArrayKeyToNumData($DataArray['InClassMoney']);
	    $JsonOutClassPer = ArrayKeyToNumData($DataArray['OutClassMoney']);

	    $this -> assign('y',$y);
	    $this -> assign('DataJson',$DataJson);
	    $this -> assign('DataArray',$DataArray);
	    $this -> assign('JsonInMoney',$JsonInMoney);
	    $this -> assign('JsonOutMoney',$JsonOutMoney);
	    $this -> assign('JsonInClassPer',$JsonInClassPer);
	    $this -> assign('JsonOutClassPer',$JsonOutClassPer);

        $this -> display();
    }
}