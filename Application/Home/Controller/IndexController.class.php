<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $uid = session('uid');
        $p = I('get.p', 1, 'intval');
        
        //设置返回页
        SetRefURL(__ACTION__."/p/$p");
        
        //向webApp发送用户名和密码
        if(session('webAppPass')){
            $str = "'"."web_login"."','".session('webAppUser')."','".session('webAppPass')."'";
            echo '<body href="javascript:void(0);" onload="WebApp_Login('.$str.')"></body>';
            session('webAppUser',null);
            session('webAppPass',null);
        }
        
        //获取指定页数据
        $DbAccount = GetAccountData($uid, $p);
        
        //获取分类列表
        $DbClass = GetClassData($uid);
        
        //整合List表格数组
        $ListData = OutListData($DbAccount,$DbClass);
        $this -> assign('Page', $ListData[0]);
        $this -> assign('PageMax', $ListData[1]);
        $this -> assign('ArrPage', $ListData[2]);
        $this -> assign('ShowData', $ListData[3]);
        
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