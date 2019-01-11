<?php
namespace Home\Controller;
use Think\Controller;
class FundsController extends BaseController {
    public function index(){
        $uid = session('uid');
        $default = array();
        $default['inmoney'] = 100;
        $default['outmoney'] = 200;
        $default['count'] = 95;
        $this -> assign('default',$default);
        $this -> display();
    }
}