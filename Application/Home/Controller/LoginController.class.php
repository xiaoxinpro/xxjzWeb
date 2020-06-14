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
                    ClearAllCache(); //清除缓存
                    $this -> redirect('Home/Index/index');
                }else if(intval(S('login_times_'.$username)) > C('USER_LOGIN_TIMES')){
                    ShowAlert('你的账号已被锁定，请联系管理员解锁！',U('Home/Login/index'));
                    $this -> display('Public/base');
                }else{
                    ShowAlert('用户名或密码错误！',U('Home/Login/index'));
                    $this -> display('Public/base');
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
                    $endtime = time() + 7200;
                    $username = $DbUser['username'];
                    $user_pass = $DbUser['password'];
                    $from = $DbUser['email'];
                    $x = md5($username.'+'.$user_pass.'+'.$endtime);
                    $String = base64_encode($username.".".$x.".".$endtime);
                    $StrHtml = U('Home/Login/forget','p='.$String,'',true);
                    //发送邮件
                    $address = $from;
                    $subject = "找回密码 - 小歆记账APP";
                    $body    = "<br>".$username."：<br />请点击下面的链接，按流程进行密码重设。（两小时内有效）<br><a href=\"".$StrHtml."\">确认密码找回</a></br><pre>".$StrHtml."</pre></br>";   
                    $file    = null;
                    if (!SendMail($address,$subject,$body,$file)) {
                        if (I('post.forget_submit') == 'xxjzAUI') {
                            die(json_encode(array('uid'=>false, 'msg'=>'服务器出错，请稍后再试！')));
                        } else {
                            LoginMassage("服务器出错，请稍后再试！","danger");
                        }
                    }else{ 
                        if (I('post.forget_submit') == 'xxjzAUI') {
                            die(json_encode(array('uid'=>true, 'msg'=>'找回密码的链接已发送至您的邮箱！')));
                        } else {
                            LoginMassage("找回密码的链接已发送至您的邮箱，请查收！");
                        }
                    }
                }else{
                    if (I('post.forget_submit') == 'xxjzAUI') {
                        die(json_encode(array('uid'=>false, 'msg'=>'该邮箱未注册过账号！')));
                    } else {
                        LoginMassage("该邮箱未注册过账号！","danger");
                    }
                }
                $this -> display();
            }else{
                LoginMassage("非法操作！","danger");
                $this -> display();
            }
        }elseif(UserShell(session('username'),session('user_shell'))){
            $this -> redirect(Home/Index/index);
        }else{
            $this -> assign('app_ios_url', C('APP_IOS_URL'));
            $this -> assign('app_android_url', C('APP_ANDROID_URL'));
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
                $arrData = array('uid'=>session('uid'),'uname'=>session('username'),'sessionid'=>session_id());
                die(json_encode($arrData));
            } else {
                $this -> redirect($refurl);
            }
        } else if(intval(S('login_times_'.$username)) > C('USER_LOGIN_TIMES')) {
            if ($submit == "xxjzAUI") {
                $arrData = array('uid'=>'0','uname'=>'你的账号已被锁定，请联系管理员解锁！');
                die(json_encode($arrData));
            }
        } else {
            if ($submit == "xxjzAUI") {
                $arrData = array('uid'=>'0','uname'=>'用户名或密码错误！');
                die(json_encode($arrData));
            } else {
                $this -> redirect('Home/Login/index');
            }
        }
    }

    public function bind_weixin(){
        if (!C('WX_ENABLE')) {
            $arrData = array('uid'=>'0','uname'=>'功能未开启，请联系管理员。');
            die(json_encode($arrData)); 
        }
        if(IS_POST){
            $username = I('post.username','','htmlspecialchars');
            $password = I('post.password','','htmlspecialchars');
            $js_code  = I('post.js_code','','htmlspecialchars');
        }else{
            $username = I('get.username','','htmlspecialchars');
            $password = I('get.password','','htmlspecialchars');
            $js_code  = I('get.js_code','','htmlspecialchars');
        }
        if (session('wx_code') != $js_code) {
            $arrData = array('uid'=>'0','uname'=>'请先使用微信登陆后再绑定。');
            die(json_encode($arrData)); 
        }
        $openid = session('wx_openid');
        $session_key = session('wx_session_key');      
        $unionid = session('wx_unionid');
        session(null); //清空session
        if(UserLogin($username,$password)){
            ClearAllCache(); //清除缓存
            $arrData = array('uid'=>session('uid'),'uname'=>session('username'),'sessionid'=>session_id());
            if ($openid) {
                $ret = WeixinUserBind(session('uid'), $openid, $session_key, $unionid);
            }
            die(json_encode($arrData));
        } else if(intval(S('login_times_'.$username)) > C('USER_LOGIN_TIMES')) {
            session('wx_code', $js_code);
            session('wx_openid',$openid);
            session('wx_session_key',$session_key);
            session('wx_unionid',$unionid);
            $arrData = array('uid'=>'0','uname'=>'你的账号已被锁定，请联系管理员解锁！');
            die(json_encode($arrData));
        } else {
            session('wx_code', $js_code);
            session('wx_openid',$openid);
            session('wx_session_key',$session_key);
            session('wx_unionid',$unionid);
            $arrData = array('uid'=>'0','uname'=>'用户名或密码错误！');
            die(json_encode($arrData));
        }
    }

    public function login_weixin(){
        if (!C('WX_ENABLE')) {
            $arrData = array('uid'=>'0','uname'=>'功能未开启，请联系管理员。');
            die(json_encode($arrData)); 
        }
        if(IS_POST){
            $js_code = I('post.js_code','','htmlspecialchars');
        }else{
            $js_code = I('get.js_code','','htmlspecialchars');
        }
        session(null); //清空session
        if ($js_code == '') {
            $arrData = array('uid'=>'0','uname'=>'非法访问。');
            die(json_encode($arrData));        
        }
        $str_data = request('https://api.weixin.qq.com/sns/jscode2session?appid='.C('WX_OPENID').'&secret='.C('WX_SECRET').'&js_code='.$js_code.'&grant_type=authorization_code');
        $str_data = substr($str_data, strpos($str_data, '{'));
        $js_data = json_decode($str_data, true);
        if ($js_data['openid']) {
            $ret = WeixinUserLogin($js_data['openid'], $js_data['session_key'], $js_data['unionid']);
            if ($ret[0] === true) {
                if ($ret[1] === '-1') {
                    session('wx_code', $js_code);
                    $arrData = array('uid'=>'-1','uname'=>$ret[2],'sessionid'=>session_id());
                    die(json_encode($arrData));
                } else {
                    session('submit', 'xxjzWeixin');
                    ClearAllCache(); //清除缓存
                    $arrData = array('uid'=>session('uid'),'uname'=>session('username'),'sessionid'=>session_id());
                    die(json_encode($arrData));
                }
            } else {
                $arrData = array('uid'=>'0','uname'=>$ret[2]);
                die(json_encode($arrData));
            }
        }
        else if ($js_data['errcode']) {
            $arrData = array('uid'=>'0','uname'=>$str_data);
            die(json_encode($arrData));        
        }
        else{
            $arrData = array('uid'=>'0','uname'=>'无法连接到微信服务器。。。');
            die(json_encode($arrData));   
        }
        $arrData = array('uid'=>'0','uname'=>'服务器出错，请重试！');
        die(json_encode($arrData));   
    }

    public function regist_weixin(){
        if (!C('WX_ENABLE')) {
            $arrData = array('uid'=>'0','msg'=>'功能未开启，请联系管理员。');
            die(json_encode($arrData)); 
        }
        if(IS_POST){
            $js_code = I('post.js_code','','htmlspecialchars');
            $username = I('post.regist_username','','htmlspecialchars');
            $password = I('post.regist_password','','htmlspecialchars');
            $email = I('post.regist_email','','htmlspecialchars');
        }else{
            $js_code = I('get.js_code','','htmlspecialchars');
            $username = I('get.regist_username','','htmlspecialchars');
            $password = I('get.regist_password','','htmlspecialchars');
            $email = I('get.regist_email','','htmlspecialchars');
        }
        if ($js_code != session('wx_code')) {
            $arrData = array('uid'=>'0','msg'=>'微信登陆验证失败，请重新使用微信登陆。');
            die(json_encode($arrData)); 
        } else {
            $ret = WeixinUserRegist($username, $password, $email);
            if ($ret[0] === true) {
                session('wx_code', null);
            }
            $arrData['uid'] = ($ret[0]) ? $ret[2] : 0 ;
            $arrData['msg'] = $ret[1];
            die(json_encode($arrData));
        }
    }

    public function shell_weixin() {
        if (!C('WX_ENABLE')) {
            $arrData = array('uid'=>'0','uname'=>'功能未开启，请联系管理员。');
        } else {
            if (UserShell(session('username'),session('user_shell'))) {
                $arrData = array('uid'=>session('uid'),'uname'=>session('username'));
            } else {
                $arrData = array('uid'=>'0','uname'=>'用户验证失败，请重新登陆。');
            }
        }
        die(json_encode($arrData)); 
    }
    
    public function forget(){
        //用base64_decode解开$_GET['p']的值
        $array = explode('.',base64_decode($_GET['p']));
        // * $array[0] 为用户名
        // * $array[1] 为我们生成的字符串
        // * $array[2] 为终止时间戳
        $username = trim($array['0']);
        $endtime = intval(trim($array['2']));
        $nowtime = time();
        if ($nowtime > $endtime) {
            $this -> error('找回密码链接已过期，请重新获取！', U('/Home/Login/index'));
            return;
        }
        $StrUser = "username='$username'";
        $DbUser = M("user");  //实例化jizhang_user
        $password = $DbUser -> where($StrUser)->getField('password');
        //产生配置码 
        $checkCode = md5($username.'+'.$password.'+'.$endtime);
        //进行配置验证
        if( $array['1'] === $checkCode ){
            if($_POST["forget_submit"]){   
                $username = trim($array['0']);
                $password = trim($_POST["forget_password"]);
                $StrUser = "username='$username'";
                if($password <> ""){
                    $umima=md5($password);
                    $DbUser-> where($StrUser)->setField('password',$umima);
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
            $this -> error('找回密码链接错误，请重新获取链接或联系管理员！', U('/Home/Login/index'));
        }
    }
    
    public function regist(){
        if (IS_POST) {
            if ($_POST['regist_submit']) {
                $username = I('post.regist_username','','htmlspecialchars');
                $password = I('post.regist_password','','htmlspecialchars');
                $email = I('post.regist_email','','htmlspecialchars');
                $ret = RegistShell($username, $password, $email);
                if (I('post.regist_submit') == 'xxjzAUI') {
                    $arrData['uid'] = ($ret[0]) ? $ret[2] : 0 ;
                    $arrData['msg'] = $ret[1];
                    die(json_encode($arrData));
                } else {
                    if ($ret[0]) {
                        ShowAlert($ret[1], U('/Home/Login/index'));
                    } else {
                        ShowAlert($ret[1]);
                    }
                }
            } else {
                $this -> redirect('Home/Login/index');
            }
        } else {

        }
        $this -> display();
    }
    
    public function logout(){
        $UserName = session('username');
        ClearAllCache(); //清除缓存
        session(null);
        $this -> redirect('Home/Login/index');
    }
    
}