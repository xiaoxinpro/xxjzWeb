<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $uid = session('uid');
        $p = I('get.p', 1, 'intval');
        
        //设置返回页
        SetRefURL(__ACTION__."/p/$p");

        //管理员权限
        if ($uid == C('ADMIN_UID')) {
            $tokenObj = PushApiToken(md5(C('WX_OPENID')));
            $this -> assign('PushAdminUrl', '/Home/Push/admin/token/'.$tokenObj['token'].'/time/'.$tokenObj['time']);
        }
        
        // //获取指定页数据
        // $DbAccount = GetAccountData($uid, $p);

        // //获取资金账户数据
        // $DbFunds = array();
        // $FundsData = GetFundsData($uid);
        // foreach ($FundsData as $key => $data) {
        //     $DbFunds[$data[id]] = $data[name];
        // }
        
        // //获取分类列表
        // $DbClass = GetClassData($uid);
        
        // //整合List表格数组
        // $ListData = OutListData($DbAccount,$DbClass,$DbFunds);
        // $this -> assign('Page', $ListData[0]);
        // $this -> assign('PageMax', $ListData[1]);
        // $this -> assign('ArrPage', $ListData[2]);
        // $this -> assign('ShowData', $ListData[3]);

        $ListData = FindTransferAccountData(array('jiid'=>$uid), $p);
        $this -> assign('Page', $ListData['page']);
        $this -> assign('PageMax', $ListData['pagemax']);
        $this -> assign('ShowData', $ListData['data']);
        $this -> assign('ArrPage', $ListData['ArrPage']);
        
        //输出用户名
        $this -> assign('UserName', session('username'));
        
        //输出数据统计
        $this -> assign('AccountStatistic', AccountStatisticProcess($uid));
        
        //实例化显示
        $this -> display();
    }
    
    public function test(){
        die;
    }

}