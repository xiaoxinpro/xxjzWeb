<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function _initialize() {
        header('Content-type:text/html;charset=utf-8');
        if (!C('DB_HOST')) {
        	$this -> error('请先访问install.php进行安装！','./install.php');
        }
        if(!UserShell(session('username'),session('user_shell'))) {
            $this -> redirect('Home/Login/index');
        }
    }
}