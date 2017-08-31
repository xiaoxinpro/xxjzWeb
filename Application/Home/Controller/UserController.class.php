<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends BaseController {
    public function index(){
        $uid = session('uid');
        $type = I('get.type',0);
        if(IS_POST) {
            if(session('username') == C('APP_DEMO_USERNAME')){
                ShowAlert('抱歉Demo账号无法进行账号信息修改！',U('Home/User/index/type/1'));
                $this -> display('Public/base');
                exit;
            }
            $Submit = I('post.user_submit');
            if($Submit === '修改账号') {
                $Username = I('post.user_name');
                $Email = I('post.user_email');
                $Password = I('post.user_password');
                $Updata = UpdataUserName($uid, $Username, $Email ,$Password);
                if($Updata[0]) {
                    ShowAlert('登录账号已改为【'.$Updata[1].'】请重新登录!',U('Home/Login/logout'));
                    $this -> display('Public/base');
                }else{
                    ShowAlert($Updata[1],U('Home/User/index'));
                    $this -> display('Public/base');
                }
            }elseif($Submit === '修改密码') {
                $OldPassword = I('post.user_password');
                $NewPassword = I('post.user_password_new');
                $Updata = UpdataPassword($uid, $OldPassword, $NewPassword);
                if($Updata[0]) {
                    ShowAlert('【'.$Updata[1].'】登录密码修改成功，请重新登录!',U('Home/Login/logout'));
                    $this -> display('Public/base');
                }else{
                    ShowAlert($Updata[1],U('Home/User/index/type/1'));
                    $this -> display('Public/base');
                }
            }elseif($Submit === '提交') {

            }else{
                $this -> error('非法操作!');
            }
        }else{
            $Email = GetUserEmail($uid);

            $this -> assign('type',$type);
            $this -> assign('Email',$Email);
            $this -> display();
        }
        

    }
}