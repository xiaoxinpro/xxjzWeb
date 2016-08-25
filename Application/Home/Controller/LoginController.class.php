<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        //dump(get_client_ip());
        if(IS_POST) {
            if($_POST['login_submit']){
                $username = I('post.login_username','','htmlspecialchars');
                $password = I('post.login_password','','htmlspecialchars');
                if(UserLogin($username, $password)){
                    session('submit',$_POST['login_submit']);
                    session('webAppUser',$username); //webApp参数
                    session('webAppPass',$password); //webApp参数
                    ClearAllCache(); //清除缓存
                    echo '登陆成功';
                    $this -> redirect('Home/Index/index');
                }else if(intval(S('login_times_'.$username)) > C('USER_LOGIN_TIMES')){
                    ShowAlert('你的账号已被锁定，请联系管理员解锁！',U('Home/Login/index'));
                    $this -> display('Public/base');
                }else{
                    ShowAlert('用户名或密码错误！',U('Home/Login/index'));
                    $this -> display('Public/base');
                    // $this -> error('用户名或密码错误！');
                }
            }elseif($_POST['forget_submit']){
        	    //验证Email的正确性
        	    $email = I('post.forget_email','','htmlspecialchars');
            	if ( empty($email)|| !preg_match("/^[-a-zA-Z0-9_.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",$email)) {
                    LoginMassage("邮箱格式不正确！","danger");
                    $this -> display();
                    exit;
                } 
                //去除空格
                $email=str_replace(" ","",$email);
                
                $DbUser = M("user") -> where("email='$email'") -> find();
                
                if(is_array($DbUser)){
                    $username = $DbUser['username'];
                    $user_pass = $DbUser['password'];
                    $from = $DbUser['email'];
                    $x = md5($username.'+'.$user_pass);
                    $String = base64_encode($username.".".$x);
                    $StrHtml = U('Home/Login/forget','p='.$String,'',true);
                    //发送邮件
                    $address = $from;
                    $subject = "找回密码 - 小歆记账APP";
                    $body    = "<br>".$username."：<br />请点击下面的链接，按流程进行密码重设。<br><a href=\"".$StrHtml."\">确认密码找回</a><p><pre>".$StrHtml."</pre></br>";   
                    $file    = null;
                    if (!SendMail($address,$subject,$body,$file)) {
                        LoginMassage("服务器出错，请稍后再试！","danger");
                    }else{ 
                        LoginMassage("找回密码的链接已发送至您的邮箱，请查收！");
                    }
                }else{
                    LoginMassage("该邮箱未注册过账号！","danger");
                }
                $this -> display();
            }else{
                LoginMassage("非法操作！","danger");
                $this -> display();
            }
        }elseif(UserShell(session('username'),session('user_shell'))){
            $this -> redirect(Home/Index/index);
        }else{
            //向webApp发送用户名
            if(session('webAppUser')){
                // $str = "'".session('webAppUser')."'";
                // echo '<body href="javascript:void(0);" onload="WebApp_Logout('.$str.')"></body>';
                $str = "'"."web_logout"."','".session('webAppUser')."',''";
                echo '<body href="javascript:void(0);" onload="WebApp_Login('.$str.')"></body>';
                session('webAppUser',null);
            }
            $this -> display();
        }
    }
    
    public function login_api(){
        if(IS_POST){
            $username = I('post.username','','htmlspecialchars');
            $password = I('post.password','','htmlspecialchars');
            $submit   = I('post.submit','','htmlspecialchars');
			$type     = I('post.type','','htmlspecialchars');
            $refurl   = I('post.refurl','','htmlspecialchars');
        }else{
            $username = I('get.username','','htmlspecialchars');
            $password = I('get.password','','htmlspecialchars');
            $submit   = I('get.submit','','htmlspecialchars');
			$type     = I('get.type','','htmlspecialchars');
            $refurl   = I('get.refurl','','htmlspecialchars');
        }
        if($refurl){
            $refurl = str_replace('_','/',$refurl);
		}elseif($type){
			$refurl = 'Home/'.$type.'/index';
        }else{
            $refurl = 'Home/Index/index';
        }
        session(null); //清空session
        if(UserLogin($username,$password)){
            session('submit',$submit);
            ClearAllCache(); //清除缓存
            if ($submit == "xxjzAUI") {
                $arrData = array('uid'=>session('uid'),'uname'=>session('username'));
                die(json_encode($arrData));
            } else {
                $this -> redirect($refurl);
            }
        }else{
            if ($submit == "xxjzAUI") {
                $arrData = array('uid'=>'0','uname'=>'用户名或密码错误！');
                die(json_encode($arrData));
            } else {
                $this -> redirect('Home/Login/index');
            }
        }
    }
    
    public function forget(){
        //用base64_decode解开$_GET['p']的值
        $array = explode('.',base64_decode($_GET['p']));
        // * $array[0] 为用户名
        // * $array[1] 为我们生成的字符串
        $username = trim($array['0']);
        $StrUser = "username='$username'";
        $DbUser = M("user");  //实例化jizhang_user
        $password = $DbUser -> where($StrUser)->getField('password');
        //产生配置码 
        $checkCode = md5($array['0'].'+'.$password);
        //进行配置验证
        if( $array['1'] === $checkCode ){
            if($_POST["forget_submit"]){   
                $username = trim($array['0']);
                $password = trim($_POST["forget_password"]);
                $StrUser = "username='$username'";
                //$row = $DbUser->where($StrUser)->find();
                if($password <> ""){
                    $umima=md5($password);
                    $DbUser-> where($StrUser)->setField('password',$umima);
                    // $this -> success('OK，修改成功！马上为你跳转登录页面...', U('/Home/Login/index'), 2);
                    ShowAlert('OK，修改成功！',U('/Home/Login/index'));
                    $this -> display('Public/base');
                }else{
                    $this -> error('密码格式错误！');
                }
            }else{
                //执行重置程序，一般给出三个输入框。
                $this -> assign('username',$username);
                $this -> display();
            }
        }else{
            $this -> error('非法操作！', U('/Home/Login/index'));
        }
    }
    
    public function regist(){
        if (IS_POST) {
            if ($_POST['regist_submit']) {
                $username = I('post.regist_username');
                $password = I('post.regist_password');
                $email = I('post.regist_email');
                $ret = RegistShell($username, $password, $email);
                if ($ret[0]) {
                    ShowAlert($ret[1], U('/Home/Login/index'));
                } else {
                    ShowAlert($ret[1]);
                }
            } else {
                $this -> redirect('Home/Login/index');
            }
        } else {

        }
        $this -> display();
    }
    
    public function logout(){
        //header('Content-type:text/html;charset=utf-8');
        $UserName = session('username');
        ClearAllCache(); //清除缓存
        session(null);
        if($UserName){
            session('webAppUser',$UserName);
        }
        $this -> redirect('Home/Login/index');
    }
    
}