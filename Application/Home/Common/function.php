<?php
    
    function UserShell($user,$key){
        if($user){
            //$StrUser = "username='$user'";
            $StrUser = array('username' => $user);
            $data = M("user")->cache('user_key_'.$user)->where($StrUser)->getField('password');
            $Shell = md5($user.$data);
            if($Shell === $key){
                return true;
            }else{
                return false; 
            }
        }else{
            return false;
        }
    }

    function PushApiToken($tokenKey, $tokenTime=null)
    {
        if ($tokenTime == null) {
            $tokenTime = time();
        }
        return array(
            'token' => md5($tokenKey.''.$tokenTime), 
            'time' => $tokenTime,
        );
    }

    function GetVersion() {
        if (defined('APP_VERSION')) {
            return APP_VERSION;
        } else {
            return '2.0.0';
        }
    }
    
    function UserLogin($username,$password){
        // $StrUser = "username='$username'";
        $StrUser = array('username' => $username );
        $TimesLogin = intval(S('login_times_'.$username))+1;
        $data = M("user")->where($StrUser)->find();
        if(($TimesLogin <= C('USER_LOGIN_TIMES'))&&($data['password'] === md5($password))){
            session('uid',$data['uid']);
            session('username',$username);
            session('user_shell',md5($data['username'].$data['password']));
            S('user_key_'.$username, null); //清除登录验证缓存
            return true;
        }else{
            session(null);
            S('login_times_'.$username, $TimesLogin, 3600);
            //echo '已经登录失败'.$TimesLogin.'次！';
            return false;
        }
    }

    function WeixinUserLogin($openid, $session_key = 'null', $unionid = 'null') {
        session('wx_openid',$openid);
        session('wx_session_key',$session_key);
        session('wx_unionid',$unionid);
        $ArrData = array('login_name' => 'Weixin', 'login_id' => $openid);
        $data = M("user_login")->where($ArrData)->find();
        if ($data['uid'] > 0) {
            //存在用户，直接登陆
            $userData = M("user")->where(array('uid' => $data['uid']))->find();
            session('uid',$userData['uid']);
            session('username',$userData['username']);
            session('user_shell',md5($userData['username'].$userData['password']));
            S('user_key_'.$userData['username'], null); //清除登录验证缓存
            return array(true, $userData['uid'], $userData['username']);
        } else {
            //不存在用户，转为注册或绑定
            return array(true, '-1', '新用户，请提交信息注册登陆。');
        }
    }

    function WeixinUserBind($uid, $openid, $session_key = 'null', $unionid = 'null') {
        if (session('uid') == $uid) {
            $data = array();
            $data['login_name'] = 'Weixin';
            $data['login_id'] = $openid;
            $isCheak = intval(M('user_login')->where($data)->getField('uid'));
            if ($isCheak <= 0) {
                $data['uid'] = $uid;
                $data['login_key'] = $session_key ? $session_key : 'null';
                $data['login_token'] = $unionid ? $unionid : 'null';
                $lid = M('user_login')->add($data);
                return array(true, '绑定成功', $uid);
            } else {
                return array(false, '绑定出错，该微信已绑定。', $uid);
            }
        } else {
            return array(false, '绑定失败，账号未登陆。', $uid);
        }

    }

    function WeixinUserRegist($Username, $Password, $Email) {
        $openid = session('wx_openid');
        $session_key = session('wx_session_key');
        $unionid = session('wx_unionid');
        if (!$openid) {
            return array(false, '非法操作openid。');
        }
        if (!$session_key) {
            return array(false, '非法操作session_key。');
        }
        if (intval(M('user_login')->where(array('login_name'=>'Weixin', 'login_id'=>$openid))->getField('uid')) > 0) {
            return array(false, '绑定出错，该微信被已绑定。');
        }
        $ret = RegistShell($Username, $Password, $Email);
        if ($ret[0] === true) {
            //注册成功，写入新注册表
            $userData = M("user")->where(array('uid' => $ret[2]))->find();
            session('uid',$userData['uid']);
            session('username',$userData['username']);
            session('user_shell',md5($userData['username'].$userData['password']));
            S('user_key_'.$userData['username'], null); //清除登录验证缓存
            $ret = WeixinUserBind($userData['uid'], $openid, $session_key, $unionid);
            if($ret[0] === true) {
                //绑定成功
                return array(true, '注册并绑定成功。', $ret[2]);
            } else {
                return $ret;
            }
        } else {
            //注册失败
            return $ret;
        }
    }

    function isEmail($email) {
        if (preg_match("/^[-a-zA-Z0-9_.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",$email)) { 
            return true;
        } else { 
            return false;
        } 
    }

    function GetUserEmail($uid,$isAll=false) {
        $sql = array('uid' => intval($uid));
        $Email = M("user")->where($sql)->getField('email');
        if($isAll){
            return $Email;
        }else{
            $domain = strstr($Email, '@');
            $before = substr($Email, 0, 2);
            return $before.'...'.$domain;
        }
        
    }

    function UpdataUserName($uid, $Username, $Email ,$Password) {
        $user = session('username');
        if($user == C('APP_DEMO_USERNAME')) {
            return array(false, '抱歉Demo账号无法进行用户名修改！');
        }
        $isShell = UserShell($user, md5($user.md5($Password)));
        //dump($user.$Password);
        if(!$isShell) {
            return array(false, '验证密码失败，请重新输入登录密码！');
        }
        if($Email !== GetUserEmail($uid, true)) {
            return array(false, '验证邮箱失败，请输入注册时填写的Email。');
        }
        if(strlen($Username) < 2) {
            return array(false, '用户名不合法！');
        }
        $isCheak = intval(M("user")->where(array('username' => $Username))->getField('uid'));
        if((intval($uid) !== $isCheak) && ($isCheak > 0)) {
            return array(false, '用户名已存在，请更换用户名再试！');
        }
        S('user_key_'.$user,null); //清除登录验证缓存
        M("user")->where(array('uid' => intval($uid)))->setField('username',$Username);
        return array(true, $Username);
    }

    function UpdataPassword($uid, $old, $new) {
        $user = session('username');
        if($user == C('APP_DEMO_USERNAME')) {
            return array(false, '抱歉Demo账号无法进行密码修改！');
        }
        $isShell = UserShell($user, md5($user.md5($old)));
        if(!$isShell) {
            return array(false, '验证密码失败，请重新输入登录密码！');
        }
        if(strlen($new) < 6) {
            return array(false, '密码长度过短，请重新输入新密码！');
        }
        S('user_key_'.$user,null); //清除登录验证缓存
        M("user")->where(array('uid' => intval($uid)))->setField('password',md5($new));
        return array(true, $user);
    }

    function RegistShell($Username, $Password, $Email) {
        if(strlen($Username) < 2) {
            return array(false, '用户名不合法！');
        }
        if(strlen($Password) < 4) {
            return array(false, '密码长度过短，请重新输入新密码！');
        }
        if (!isEmail($Email)) {
            return array(false, '邮箱格式有误，请重新输入邮箱。');
        }
        $isCheak = intval(M("user")->where(array('username' => $Username))->getField('uid'));
        if($isCheak > 0) {
            return array(false, '用户名已存在，请更换用户名再试！');
        }
        $isCheak = intval(M("user")->where(array('email' => $Email))->getField('uid'));
        if($isCheak > 0) {
            return array(false, '该邮箱已注册过，如忘记密码请尝试找回密码。');
        }
        $data = array();
        $data['username'] = $Username;
        $data['password'] = md5($Password);
        $data['email'] = $Email;
        $data['utime'] = time();
        $DbData = M('user')->add($data);
        if($DbData > 0){
            return array(true, '新账号注册成功!', $DbData);
        }else{
            return array(false, '写入数据库出错(>_<)');
        }
    }
    
    function ShowAlert($msg,$url=null)
    {
        if(url){
            $Alert = "'$msg','$url'";
        }else{
            $Alert = "'$msg'";
        }
        echo '<body href="javascript:void(0);" onload="ShowAlert('.$Alert.');"></body>';
    }
    
    //登陆界面专属消息框
    function LoginMassage($msg,$type="success") {
        //msg:消息内容
        //type:消息类型 success=绿色 warning=橙色 danger=红色 secondary=灰色
        echo '<div class="am-alert am-alert-'.$type.'" data-am-alert align="center">';
        echo '<button type="button" class="am-close">&times;</button>';
        echo $msg;
        echo '</div>';
    }
    
    //Mail发送函数
    function SendMail($address,$subject,$body,$file){
        Vendor('PHPMailer.PHPMailerAutoload');    
        $mail = new PHPMailer();                  // 建立邮件发送类
        $mail->CharSet    = "UTF-8";              // 编码格式UTF-8
        $mail->IsSMTP();                          // 使用SMTP方式发送
        $mail->Host       = C('MAIL_HOST');       // 您的企业邮局域名
        $mail->SMTPAuth   = true;                 // 启用SMTP验证功能
        $mail->SMTPSecure = C('MAIL_SECURE');     // 安全协议（none、ssl、tls）
        $mail->Port       = C('MAIL_PORT');       // 邮局SMTP端口
        $mail->Username   = C('MAIL_USERNAME');   // 邮局用户名(请填写完整的email地址)
        $mail->Password   = C('MAIL_PASSWORD');   // 邮局密码
        $mail->From       = C('MAIL_FROM');       // 邮件发送者email地址
        $mail->FromName   = C('MAIL_FROMNAME');   // 邮件发送者姓名
        $mail->AddAddress("$address","");         // 收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        //$mail->AddReplyTo("", "");
        if(file_exists($file)){
            $mail->AddAttachment($file); // 添加附件
        }
        $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
        
        $mail->Subject = $subject; //邮件标题
        $mail->Body = $body; //邮件内容，上面设置HTML，则可以是HTML
        
        if(!$mail->Send())
        {
            echo "邮件发送失败 -- 错误原因: " . $mail->ErrorInfo;
            return false;
        }else{
            return true;
        }
    }
    
    //设置可返回的URL
    function SetRefURL($url) {
        if($url){
            session('url',$url);
        }else{
            session('url',null);
        }
    }
    
    //获取可返回的URL
    function GetRefURL() {
        if(session('?url')){
            return session('url');
        }else{
            return U('Home/Index/index');
        }
    }
    
    //清除全部缓存
    function ClearAllCache() {
        ClearDataCache();
        ClearFindCache();
    }
    
    //清除数据缓存
    function ClearDataCache() {
        $uid = session('uid');
        if($uid){
            S('account_funds_'.$uid,null);
            S('account_funds_data_'.$uid,null);
            S('account_class_data_'.$uid,null);
            S('account_class_0_'.$uid,null);
            S('account_class_1_'.$uid,null);
            S('account_class_2_'.$uid,null);
            S('account_tatistic_'.$uid,null);
            S('account_data_'.$uid,null);
            S('chart_year_'.$uid,null);
            S('account_date_'.$uid,null);
            S('account_time_between_'.$uid,null);
            S('chart_all_year_'.$uid,null);
        }
    }
    
    //清除查询缓存
    function ClearFindCache() {
        $uid = session('uid');
        if($uid){
            S('find_data_'.$uid,null);
            S('find_data_class_'.$uid,null);
        }
    }

    //查询SQL数组生成函数
    function GetFindSqlArr($data)
    {
        $arrSQL = array();
        if ($data['fid']) {
            $arrSQL['fid'] = intval($data['fid']);
        }
        if($data['acid']){
            $arrSQL['acid'] = $data['acid'];
        }
        if($data['jiid']){
            $arrSQL['jiid'] = $data['jiid'];
        }
        if($data['acremark']){
            $arrSQL['acremark'] = array('like', '%'.$data['acremark'].'%');
        }
        if($data['starttime']){
            $strData = strtotime(date($data['starttime']." 0:0:0"));
            $arrSQL['actime'] = array('egt', $strData);
        }
        if($data['endtime']){
            $strData = strtotime(date($data['endtime']." 23:59:59"));
            $arrEnd = array('elt', $strData);
            if(is_array($arrSQL['actime'])){
                $arrSQL['actime'] = array($arrSQL['actime'], $arrEnd);
            }else{
                $arrSQL['actime'] = $arrEnd;
            }
        }
        if($data['acclassid']){
            $arrSQL['acclassid'] = $data['acclassid'];
        }
        if($data['zhifu']){
            $arrSQL['zhifu'] = $data['zhifu'];
        }
        return $arrSQL;
    }
    
    //查询记账数据
    function FindAccountData($data,$p = 0) {
        $strSQL = GetFindSqlArr($data);
        $DbCount = M('account')->where($strSQL)->count();
        //$DbData = M('account')->where($strSQL)->order("actime DESC , acid DESC")->select();
        if($data['zhifu'] == 1){
            $inSumData  = SumDbAccount($strSQL);
            $outSumData = 0.0;
        }elseif($data['zhifu'] == 2){
            $inSumData  = 0.0;
            $outSumData = SumDbAccount($strSQL);
        }else{
            $data['zhifu'] = 1;
            $inSumData  = SumDbAccount(GetFindSqlArr($data));
            $data['zhifu'] = 2;
            $outSumData = SumDbAccount(GetFindSqlArr($data));
            $data['zhifu'] = null;
        }
        if($p > 0) {
            $pagesize = C('PAGE_SIZE');  
            $offset = ($p-1)*$pagesize;
            $DbData = M('account')->where($strSQL)->order("actime DESC , acid DESC")->limit("$offset,$pagesize")->select();
            $ret['pagemax'] = intval(($DbCount-1) / $pagesize) + 1;
            $ret['page'] = $p;
        }else{
            $DbData = M('account')->where($strSQL)->order("actime DESC , acid DESC")->select();
            $ret['pagemax'] = 1;
            $ret['page'] = 1;
        }
        $ret['isTransfer']  = false;
        $ret['SumInMoney']  = $inSumData;
        $ret['SumOutMoney'] = $outSumData;
        $ret['count']       = $DbCount;
        $ret['data']        = $DbData;
        return $ret;
    }
    
    //获取记账数据(用户id,页码)
    function GetAccountData($uid, $p) {
        //dump(S('account_data_'.$p));
        $CacheData = S('account_data_'.$uid);
        if (array_key_exists($p, $CacheData)) {
            return $CacheData[$p];
        } else {
            $DbCount = M('account')->where("jiid='$uid'")->count();
            $DbSQL = M('account')->where("jiid='$uid'")->order("actime DESC , acid DESC");
            if($p > 0) {
                $pagesize = C('PAGE_SIZE');  
                $offset = ($p-1)*$pagesize;
                $DbData = $DbSQL -> limit("$offset,$pagesize") -> select();
                $ret['pagemax'] = intval(($DbCount-1) / $pagesize) + 1;
                $ret['page'] = $p;
            }else{
                $DbData = $DbSQL -> select();
                $ret['pagemax'] = 1;
                $ret['page'] = 1;
            }
            $ret['count'] = $DbCount;
            $ret['data'] = $DbData;
            $CacheData[$p] = $ret;
            S('account_data_'.$uid, $CacheData);
            return $ret;
        }
    }

    //按日期获取记账数据
    function GetDateAccountData($uid, $pageParam) {
        $data = array();
        $data['jiid'] = $uid;
        if ($pageParam['gettype'] == 'year' ) {
            $data['starttime'] = $pageParam['year'].'-01-01';
            $data['endtime']   = $pageParam['year'].'-12-31';
        } elseif ($pageParam['gettype'] == 'month' ) {
            $data['starttime'] = $pageParam['year'].'-'.$pageParam['month'].'-01';
            $data['endtime']   = date('Y-m-d', strtotime($data['starttime']." +1 month -1 day"));
        } elseif ($pageParam['gettype'] == 'day' ) {
            $data['starttime'] = $pageParam['year'].'-'.$pageParam['month'].'-'.$pageParam['day'];
            $data['endtime']   = $data['starttime'];
        } elseif ($pageParam['gettype'] == 'all' ) {
            if ($pageParam['year'] && $pageParam['month'] && $pageParam['day']) {
                $data['starttime'] = '2000-01-01';
                $data['endtime']   = $pageParam['year'].'-'.$pageParam['month'].'-'.$pageParam['day'];
            }
        }
        //缓存处理
        $CacheKey = md5(implode('_', $pageParam));
        $CacheData = S('account_date_'.$uid);
        if (!$CacheData[$CacheKey]) {
            $ret = FindAccountData($data, $pageParam['page']);
            $CacheData[$CacheKey] = NumTimeToStrTime($ret);
            S('account_date_'.$uid, $CacheData);
        }
        return $CacheData[$CacheKey];
    }
    
    //获取分类数据(用户id,1=收入 2=支出)
    function GetClassData($uid,$type=0) {
        $strSQL = array();
        $strSQL['ufid'] = $uid;
        if($type) {
            $strSQL['classtype'] = $type;
        }
        
        $DbClass = M('account_class')->cache('account_class_'.$type.'_'.$uid)->where($strSQL)->order('sort,classid')->select();
        
        //$ret = array();
        foreach($DbClass as $key => $Data) {
            $classId = $Data['classid'];
            $className = $Data['classname'];
            $ret["$classId"] = $className;
        }
        return $ret;
    }
    
    //数据库数组数据 转 显示数据
    function ArrDataToShowData($ArrData, $ArrClass, $ArrFunds = null) {
        $retShowData = array();
        if($ArrData['zhifu'] == 1){
            $classType = '收入';
        }else{
            $classType = '支出';
        }

        if (is_array($ArrFunds)) {
            $fundsId = $ArrData['fid'];
            $fundsName = $ArrFunds[$fundsId];
        }

        $classId = $ArrData['acclassid'];
        $className = $ArrClass[$classId];
        
        $retShowData['id']      = $ArrData['acid'];
        $retShowData['money']   = $ArrData['acmoney'];
        $retShowData['fundsid'] = $fundsId;
        $retShowData['funds']   = $fundsName;
        $retShowData['classid'] = $classId;
        $retShowData['class']   = $className;
        $retShowData['type']    = $classType;
        $retShowData['time']    = $ArrData['actime'];
        $retShowData['mark']    = $ArrData['acremark'];
        $retShowData['uid']     = $ArrData['jiid'];
        
        return $retShowData;
    }
    
    //整合List表格数组
    function OutListData($ArrAccount, $ArrClass, $ArrFunds = null) {
        $Page = $ArrAccount['page'];
        $PageMax = $ArrAccount['pagemax'];
        $retShowData = array();
        foreach($ArrAccount['data'] as $key => $ArrData) {
            $retShowData[$key] = ArrDataToShowData($ArrData, $ArrClass, $ArrFunds);
        }
        return array($Page,$PageMax,$retShowData);
    }
    
    function SumDbAccount($StrSQL) {
        $Ret = M('account')->where($StrSQL)->Sum('acmoney');
        if($Ret == null){
            $Ret = 0.00;
        }
        return floatval($Ret);
    }
    
    //获取指定时间段的记账结果(开始时间戳,结束时间戳,收支,用户id)
    function GetAccountStatistic($StartTime,$EndTime,$Type,$uid) {
        //收支 : 1收入 / 2支出
        $StrSQL = "";
        if ($StartTime) {
            $StrSQL = $StrSQL . "actime >= $StartTime and ";
        }
        if ($EndTime) {
            $StrSQL = $StrSQL . "actime <= $EndTime and ";
        }
        $StrSQL = $StrSQL."jiid = '$uid' and zhifu = '$Type'";
        return SumDbAccount($StrSQL);
    }
    
    function AccountStatisticProcess($uid) {
        $ArrData = S('account_tatistic_'.$uid);
        
        if($ArrData && ($ArrData['TodayDate'] == date("Y-m-d"))){
            return $ArrData;
        }
        $ArrData = array();
        
        //今日收支统计
        $today = date("Y-m-d");
        $ArrData['TodayDate'] = $today;
        $StartTime = strtotime($today." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['TodayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['TodayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //本月收支统计
        $EndTime = strtotime(date("Y-m-d",mktime(23,59,59,date("m",time()),date("t"),date("Y",time()))));
        $StartTime = strtotime(date("Y-m-d",mktime(0,0,0,date("m",time()),1,date("Y",time()))));
        $ArrData['MonthInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['MonthOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //本年收支统计
        $EndTime = strtotime(date("Y-m-d",mktime(23,59,59,12,31,date("Y",time()))));
        $StartTime = strtotime(date("Y-m-d",mktime(0,0,0,1,1,date("Y",time()))));
        $ArrData['YearInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['YearOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //最近7天收支统计
        $StartTime = strtotime(date("Y-m-d",strtotime('-7 day'))." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['Recent7DayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['Recent7DayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);

        //最近30天收支统计
        $StartTime = strtotime(date("Y-m-d",strtotime('-1 month +1 day'))." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['Recent30DayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['Recent30DayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);

        //最近60天收支统计
        $StartTime = strtotime(date("Y-m-d",strtotime('-2 month +1 day'))." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['Recent60DayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['Recent60DayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);

        //最近90天收支统计
        $StartTime = strtotime(date("Y-m-d",strtotime('-3 month +1 day'))." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['Recent90DayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['Recent90DayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);

        //最近180天收支统计
        $StartTime = strtotime(date("Y-m-d",strtotime('-6 month +1 day'))." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['Recent180DayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['Recent180DayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);

        //最近365天收支统计
        $StartTime = strtotime(date("Y-m-d",strtotime('-1 year +1 day'))." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['Recent365DayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['Recent365DayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //昨日收支统计
        $today = date("Y-m-d",strtotime('-1 day'));
        $StartTime = strtotime($today." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['LastTodayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['LastTodayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //上月收支统计
        $EndTime = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').' day')));
        $StartTime = strtotime(date("Y-m-01 00:00:00", strtotime('-1 month')));
        $ArrData['LastMonthInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['LastMonthOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //去年收支统计
        $EndTime = strtotime(date("Y-m-d",mktime(23,59,59,12,31,date("Y",time())-1)));
        $StartTime = strtotime(date("Y-m-d",mktime(0,0,0,1,1,date("Y",time())-1)));
        $ArrData['LastYearInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['LastYearOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //总结统计
        $StartTime = strtotime("2000-1-1 0:0:0");
        $EndTime = strtotime(date("Y-m-d")." 23:59:59");
        $ArrData['SumInMoney']  = GetAccountStatistic(null,null,1,$uid);
        $ArrData['SumOutMoney'] = GetAccountStatistic(null,null,2,$uid);
        
        S('account_tatistic_'.$uid,$ArrData);
        
        return $ArrData;
    }
    
    //验证记账id与登录id是否相同
    function CheakIdShell($id,$uid) {
        if(is_numeric($id)){
            // if($uid == 3) {
            //     return true;
            // }else{
                $jiid = M('account')->where("acid='$id'")->getField('jiid');
                if($jiid == $uid) {
                    return true;
                }else{
                    return false;
                }
            // }
        }else{
            return false;
        }
    }
    
    //获取指定记账id的数据
    function GetIdData($id) {
        if(is_numeric($id)){
            $DbData = M('account')->where("acid='$id'")->find();
            return $DbData;
        }else{
            return null;
        }
    }
    
    //删除指定记账id数据
    function DelIdData($uid, $id) {
        if(is_numeric($id)){
            $DbData = M('account')->where("acid='$id'")->delete();
            if($DbData > 0){
                DelImageData($uid, $id);
                return array(true,'已成功删除'.$DbData.'条数据(^_^)');
            }elseif($DbData === 0){
                return array(false,'未找到你要删除的数据(@_@)');
            }else{
                return array(false,'数据库异常(*_*)');
            }
        }else{
            return array(false,'非法操作(=_=)');
        }
    }
    
    //获取分类id
    function GetClassId($ClassName) {
        if($ClassName){
            $strSQL = array('classname' => $ClassName);
            $DbClass = M('account_class')->where($strSQL)->find();
            return $DbClass['classid'];
        }
    }
    
    //校验记账数据
    function CheakAccountData($data,$isID = true) {
        if(!is_array($data)){
            return array(false,'非法操作~~~');
        }
        if($isID && !(is_numeric($data['acid'])&&($data['acid'] > 0))){
            return array(false,'操作的id无效...');
        }
        if(!(is_numeric($data['acmoney'])&&($data['acmoney'] >= 0.01)&&($data['acmoney'] <= C('MAX_MONEY_VALUE')))){
            return array(false,'输入的金额无效，请输入0.01到' . C('MAX_MONEY_VALUE') . '范围内的有效数字。');
        }
        if(!is_numeric($data['acclassid'])){
            $data['acclassid'] = GetClassId($data['acclassid']);
        }
        if (intval($data['acclassid']) == 0) {
            return array(false,'请先添加分类再进行记账...');
        }
        $strSQL  = 'classid = '.$data['acclassid'];
        $DbClass = M('account_class')->where($strSQL)->find();
        if(!is_array($DbClass)){
            return array(false,'选择的分类无效!');
        }
        if($DbClass['classtype'] != $data['zhifu']){
            return array(false,'选择的分类与收支类别不匹配~');
        }
        if (strlen($data['acremark']) > C('MAX_MARK_VALUE')) {
            return array(false,'备注信息太长，请把长度控制在' . C('MAX_MARK_VALUE') . '个字符以内。');
        }
        if(!is_int($data['actime'])){
            $data['actime'] = strtotime($data['actime']);
        }
        
        return array(true,$data);
    }
    
    //更新记账数据
    function UpdataAccountData($data) {
        $isCheak = CheakAccountData($data);
        if($isCheak[0]){
            $data = $isCheak[1];
            $strSQL  = 'acid = '.$data['acid'];
            $DbData = M('account')->where($strSQL)->find();
            if(!is_array($DbData)){
                return array(false,'该记账信息不存在!');
            }
            $DbSQL = M('account')->where($strSQL)->data($data)->save();
            return array(true,'数据更新成功!');
        }else{
            return $isCheak;
        }
    }
    
    //添加记账数据
    function AddAccountData($data) {
        $isCheak = CheakAccountData($data,false);
        if($isCheak[0]){
            $data = $isCheak[1];
            $DbData = M('account')->add($data);
            if($DbData > 0){
                // 增加图片（uploads中是已保存到数据库中的图片数据）
                if (isset($data['uploads']) && count($data['uploads']) > 0) {
                    for ($i=0; $i < count($data['uploads']); $i++) { 
                        EditImageAcid($data['jiid'], $data['uploads'][$i]['id'], $DbData);
                    }
                }
                return array(true,'数据添加成功!',$DbData);
            }else{
                return array(false,'写入数据库出错(>_<)');
            }
        }else{
            return $isCheak;
        }
    }

    //获取上传文件的对象
    function UploadFile($uid) {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize  = C('IMAGE_SIZE');// 设置附件上传大小
        $upload->exts     = C('IMAGE_EXT');// 设置附件上传类型
        $upload->rootPath = '.'.C('IMAGE_ROOT_PATH'); // 设置附件上传根目录
        $upload->savePath = '';
        $upload->saveName = array('uniqid','');
        $upload->autoSub  = true;
        $upload->subName  = date('Y').'/image'.$uid;
        return $upload->upload();
    }

    //添加图片到数据库
    function AddImageData($uid, $upload, $acid = 0) {
        if (is_array($upload) && count($upload) > 0) {
            $count = GetImageCount($uid, $acid);
            $time = time();
            for ($i=0; $i < count($upload); $i++) { 
                if (i > C('IMAGE_COUNT') + $count) {
                    break; //防止上传文件数量超过限制
                }
                $upload[$i]['uid'] = $uid;
                $upload[$i]['acid'] = $acid;
                $upload[$i]['time'] = $time;
            }
            $ret = M("account_image")->addAll($upload);
            if ($ret) {
                $sql = array('uid'=>$uid, 'acid'=>$acid, 'time'=>$time);
                return M("account_image")->where($sql)->select();
            }
        }
        return false;
    }

    //编辑图片对应的记账ID （用户ID，图片ID，待设定的记账ID）
    function EditImageAcid($uid, $id, $acid) {
        if (GetImageCount($uid, $acid) < C('IMAGE_COUNT')) {
            $sql = array('uid'=>$uid, 'acid'=>$acid);
            $ret = M("account")->where($sql)->find();
            if ($ret) {
                $sql = array('uid'=>$uid, 'id'=>$id);
                return M("account_image")->where($sql)->setField('acid', $acid);
            }
        }
        return false;
    }

    //获取数据库中的图片
    function GetImageData($uid, $acid, $id = false) {
        $sql = array('uid'=>$uid, 'acid'=>$acid);
        if ($id) {
            $sql['id'] = $id;
        }
        $imageData = M("account_image")->where($sql)->select();
        if (is_array($imageData) && count($imageData) > 0) {
            for ($i=0; $i < count($imageData); $i++) { 
                if (stripos($imageData[$i]['savepath'], 'http') === 0) {
                    $imageData[$i]['url'] = $imageData[$i]['savepath'].$imageData[$i]['savename'];
                } elseif (stripos(C('IMAGE_CACHE_URL'), 'http') === 0) {
                    $imageData[$i]['url'] = C('IMAGE_CACHE_URL').'/'.$imageData[$i]['savepath'].$imageData[$i]['savename'];
                } else {
                    $imageData[$i]['url'] = __ROOT__.C('IMAGE_ROOT_PATH').$imageData[$i]['savepath'].$imageData[$i]['savename'];
                }
            }
            return array(true, $imageData);
        } else {
            return array(false, "此记账无对应的图片附件。");
        }
    }

    //获取指定条件下的图片数量
    function GetImageCount($uid=false, $acid=false) {
        $sql = array();
        if ($uid !== false) {
            $sql['uid'] = $uid;
        }
        if ($acid !== false) {
            $sql['acid'] = $acid;
        }
        return M("account_image")->where($sql)->count();
    }

    //删除图片数据库和文件
    function DelImageData($uid, $acid, $id=false) {
        $imageData = GetImageData($uid, $acid, $id);
        if ($imageData[0]) {
            for ($i=0; $i < count($imageData[1]); $i++) { 
                $path = '.'.C('IMAGE_ROOT_PATH').$imageData[1][$i]['savepath'].$imageData[1][$i]['savename'];
                if (file_exists($path)) {
                    $ret = unlink($path);
                }
            }
            $sql = array('uid'=>$uid, 'acid'=>$acid);
            if ($id != false) {
                $sql['id'] = $id;
            }
            return array(true, $ret, M("account_image")->where($sql)->delete());
        }
        return array(false, "删除图片失败，图片数据不存在。");
    }

    //是否要显示默认账户
    function IsShowDefaultFunds($uid) {
        $ret = true;
        $sql = array('uid'=>$uid);
        if (M('account_funds')->where($sql)->count() > 0) {
            $sql = array('jiid'=>$uid, 'fid'=>-1);
            if (M('account')->where($sql)->count() == 0) {
                $ret = false;
            }
        }
        return $ret;
    }

    //校验资金账户名
    function CheakFundsName($FundsName, $uid, $FundsId = -1) {
        if(strlen($FundsName) < 1){
            return array(false, '资金账户名不得为空！');
        }

        if(strlen($FundsName) > C('MAX_FUNDS_NAME')){
            return array(false, '资金账户名太长，请控制在' . C('MAX_FUNDS_NAME') . '个字符以内。');
        }

        if (IsShowDefaultFunds($uid) && ($FundsName == "默认")) {
            return array(false, '资金账户名不可为默认。');
        }

        $sql = array('fundsname' => $FundsName, 'uid' => $uid);
        $FundsData = M("account_funds")->where($sql)->select();
        if (is_array($FundsData) && count($FundsData) > 0) {
            if ($FundsId > 0) {
                foreach ($FundsData as $key => $FundsArr) {
                    if (intval($FundsData[$key]['fundsid']) !== intval($FundsId)) {
                        return array(false, '资金账户名已存在!');
                    }
                }
            } else {
                return array(false, '资金账户名已存在...');
            }
        }
        return array(true, $FundsName);
    }

    //新建资金账户
    function AddNewFunds($FundsName, $FundsMoney, $uid) {
        $isCheak = CheakFundsName($FundsName, $uid);
        if($isCheak[0]){
            $data = array('fundsname'=>$isCheak[1],'uid'=>$uid);
            $fid = M('account_funds')->add($data);
            ClearDataCache();
            if($fid > 0){
                if (floatval($FundsMoney) > 0) {
                    $ret = AddTransferData(array(
                        'uid' => $uid,
                        'money' => floatval($FundsMoney),
                        'source_fid' => 0,
                        'target_fid' => $fid,
                        'time' => strtotime(date('Y-m-d', time())),
                        'mark' => $FundsName.'账户的默认金额',
                    ));
                    if ($ret[0] == false) {
                        return array(false, $ret[1]);
                    }
                }
                return array(true,'新建资金账户成功!');
            }else{
                return array(false,'写入数据库出错(&_&)');
            }
        }else{
            return $isCheak;
        }
    }

    //获取资金账户数据
    function GetFundsData($uid) {
        $CacheData = S('account_funds_'.$uid);
        if ($CacheData) {
            return $CacheData;
        } else {
            $sql = array('uid' => $uid);
            $retData = array();
            if (IsShowDefaultFunds($uid)) {
                array_push($retData, array('name'=>'默认', 'id'=> -1, 'money'=> GetFundsAccountSumData(-1,$uid)));
            }
            $DbData = M('account_funds')->where($sql)->order('sort,fundsid')->select();
            foreach ($DbData as $key => $FundsArr) {
                array_push($retData, array('name'=>$FundsArr['fundsname'], 'id'=>intval($FundsArr['fundsid']), 'money'=> GetFundsAccountSumData($FundsArr['fundsid'],$uid)));
            }
            S('account_funds_'.$uid, $retData);
            return $retData;            
        }
    }

    //获取指定资金账户记账汇总
    function GetFundsAccountSumData($FundsId, $uid) {
        $CacheData = S('account_funds_data_'.$uid);
        if($CacheData[$FundsId]) {
            return $CacheData[$FundsId];
        } else {
            $sql = array('fid'=>$FundsId, 'jiid'=>$uid);
            $FundsCount = intval(M('account')->where($sql)->count('acmoney'));
            $sql['zhifu'] = 1; //收入
            $InMoneySum = floatval(M('account')->where($sql)->sum('acmoney'));
            $sql['zhifu'] = 2; //支出
            $OutMoneySum = floatval(M('account')->where($sql)->sum('acmoney'));
            $OverMoneySum = $InMoneySum - $OutMoneySum;
            $retData = array('init'=>0, 'over'=>$OverMoneySum, 'in'=>$InMoneySum, 'out'=>$OutMoneySum, 'count'=>$FundsCount);
            if ($FundsId > 0) {
                $TransferData = GetFundsTransferMoney($FundsId, $uid);
                if ($TransferData[0]) {
                    $retData['init'] = $TransferData[1]['init'];
                    $retData['in'] += $TransferData[1]['in'];
                    $retData['out'] += $TransferData[1]['out'];
                    $retData['over'] += $TransferData[1]['over'];
                }
            }      
            $CacheData[$FundsId] = $retData;
            S('account_funds_data_'.$uid, $CacheData);
            return $retData;
        }

    }

    //获取资金账户ID数据
    function GetFundsIdData($FundsId,$uid) {
        $sql = array('fundsid' => intval($FundsId), 'uid' => $uid);
        $FundsData = M("account_funds")->where($sql)->find();
        if(is_array($FundsData)){
            return array(true,$FundsData);
        }else{
            return array(false,'资金账户id不存在~');
        }
    }

    //编辑资金账户名称
    function EditFundsName($FundsId, $FundsName, $uid) {
        $isCheak = CheakFundsName($FundsName, $uid, $FundsId);
        if($isCheak[0]){
            $sql = array('fundsid' => intval($FundsId), 'uid' => intval($uid));
            $ret = M("account_funds")->where($sql)->setField('fundsname',$FundsName);
            ClearDataCache();
            return array(true,'资金账户名称修改成功!');
        }else{
            return $isCheak;
        }
    }

    //编辑资金账户默认金额
    function EditFundsDefaultMoney($FundsId, $FundsMoney, $uid) {
        $sql = array('uid'=>$uid, 'source_fid'=>0, 'target_fid'=>$FundsId);
        $DbData = M('account_transfer')->where($sql)->find();
        if ($DbData) {
            $DbData['money'] = floatval($FundsMoney);
            return EditTransferData($DbData['tid'], $DbData);
        } else {
            return AddTransferData(array(
                'uid' => $uid,
                'money' => floatval($FundsMoney),
                'source_fid' => 0,
                'target_fid' => $FundsId,
                'time' => strtotime(date('Y-m-d', time())),
                'mark' => '更新账户初始金额',
            ));
        }
    }

    //调整账户排序
    function SortFunds($FundsIdList, $uid)
    {
        for ($i=0; $i < count($FundsIdList); $i++) { 
            $sql = array('uid'=>$uid, 'fundsid' => intval($FundsIdList[$i]));
            if ($sql['fundsid'] > 0) {
                M("account_funds")->where($sql)->setField('sort', $i + 1);
            }
        }
        ClearDataCache();
    }

    //删除资金账户并转移记账数据
    function DeleteFunds($oldFundsId, $uid, $newFundsId = -1) {
        if ($oldFundsId == $newFundsId) {
            return array(false, '转移资金账户错误，无法删除资金账户!');
        } elseif (($newFundsId === -1)||(M("account_funds")->where(array('fundsid' => $newFundsId, 'uid' => $uid))->find())) {
            $retCount = M('account')->where(array('fid' => $oldFundsId, 'uid' => $uid))->setField('fid', $newFundsId);
            if ($oldFundsId === -1) {
                $retDelete = 1;
            } else {
                $retDelete = M('account_funds')->where(array('fundsid' => $oldFundsId, 'uid' => $uid))->delete();
            }
            ClearDataCache();
            MoveFundsTransferData($oldFundsId, $newFundsId, $uid);
            if ($retCount==0 && $retDelete==1) {
                return array(true, "资金账户删除成功。");
            } elseif ($retCount>0 && $retDelete==1) {
                return array(true, "记账数据转移". $retCount ."条，资金账户删除成功。");
            } else {
                return array(false,'资金账户删除失败，请返回重试。'. $retCount);
            }
        } else {
            return array(false,'待转移的资金账户不存在!');
        }
    }

    //校验转账信息
    function CheakTransferData($data) {
        if (!is_array($data)) {
            return array(false,'非法操作~~~');
        }
        $uid = $data['uid'];
        if (!(is_numeric($uid) && $uid > 0)) {
            return array(false,'未授权的访问！');
        } elseif (!(is_numeric($data['money'])&&($data['money'] >= ($data['source_fid'] == 0 ? 0 : 0.01))&&($data['money'] <= C('MAX_MONEY_VALUE')))) {
            return array(false,'输入的金额无效，请输入'.($data['source_fid'] == 0 ? 0 : 0.01).'到' . C('MAX_MONEY_VALUE') . '范围内的有效数字。');
        } elseif (strlen($data['mark']) > C('MAX_MARK_VALUE')) {
            return array(false,'备注信息太长，请把长度控制在' . C('MAX_MARK_VALUE') . '个字符以内。');
        } elseif ($data['source_fid'] == $data['target_fid']) {
            return array(false,'转出账户与转入账户不能相同，请重新选择。');
        } elseif ($data['source_fid'] > 0 && GetFundsIdData($data['source_fid'], $uid)[0] == false) {
            return array(false,'转出账户不存在，请重新选择。');
        } elseif (GetFundsIdData($data['target_fid'], $uid)[0] == false) {
            return array(false,'转入账户不存在，请重新选择。');
        }
        if(!is_int($data['time'])){
            $data['time'] = strtotime($data['time']);
        }
        return array(true, $data);
    }

    //获取转账ID对应的数据
    function GetTransferIdData($tid, $uid) {
        $sql = array('tid' => intval($tid), 'uid' => $uid);
        $DbData = M("account_transfer")->where($sql)->find();
        if(is_array($DbData)){
            return array(true,$DbData);
        }else{
            return array(false,'转账id不存在~');
        }
    }

    //添加转账记录
    function AddTransferData($data) {
        $ret = CheakTransferData($data);
        if ($ret[0]) {
            $DbData = M('account_transfer')->add($ret[1]);
            ClearDataCache();
            if($DbData > 0){
                return array(true,'资金转账成功!');
            }else{
                return array(false,'写入数据库出错(T_T)');
            }
        } else {
            return $ret;
        }
    }

    //编辑转账记录
    function EditTransferData($tid, $data) {
        $ret = CheakTransferData($data);
        if ($ret[0]) {
            $data = $ret[1];
            $ret = GetTransferIdData($tid, $data['uid']);
            ClearDataCache();
            if ($ret[0]) {
                $DbData = M('account_transfer')->where(array('tid'=>intval($tid), 'uid'=>$data['uid']))->data($data)->save();
                return array(true,'转账记录更新成功!');
            } else {
                return $ret;
            }
        } else {
            return $ret;
        }
    }

    //删除转账记录
    function DelTransferData($tid, $uid) {
        $sql = array('tid' => intval($tid), 'uid' => $uid);
        $DbData = M("account_transfer")->where($sql)->delete();
        ClearDataCache();
        if($DbData > 0){
            ClearDataCache();
            return array(true,'转账记录删除成功！');
        }elseif($DbData === 0){
            return array(false,'未找到你要删除的转账记录(@_@)');
        }else{
            return array(false,'转账数据库异常(*_*)');
        }
    }

    //转移转账记录到指定账户
    function MoveFundsTransferData($source_fid, $target_fid, $uid) {
        // 若目标账户为-1默认账户，则直接删除源账户
        if ($target_fid > 0) {
            // 源账户初始值转移至目标账户
            $sql = array('uid'=>$uid, 'source_fid'=>0, 'target_fid'=>$source_fid);
            $DbSource = M('account_transfer')->where($sql)->find();
            if (is_array($DbSource)) {
                $sql = array('uid'=>$uid, 'source_fid'=>0, 'target_fid'=>$target_fid);
                $DbTarget = M('account_transfer')->where($sql)->find();
                if (is_array($DbTarget)) {
                    $sql = array('uid'=>$uid, 'source_fid'=>0, 'target_fid'=>$target_fid);
                    M('account_transfer')->where($sql)->setField('money', $DbSource['money'] + $DbTarget['money']);
                } else {
                    $sql = array('uid'=>$uid, 'source_fid'=>0, 'target_fid'=>$source_fid);
                    M('account_transfer')->where($sql)->setField('target_fid', $target_fid);
                }
            }
            // 源账户初始记录和删除
            $sql = array('uid'=>$uid, 'source_fid'=>0, 'target_fid'=>$source_fid);
            M('account_transfer')->where($sql)->delete();
            // 将源账户的转入转出id改为目标账户
            $sql = array('uid'=>$uid, 'source_fid'=>$source_fid, 'target_fid'=>array('neq', $target_fid));
            M('account_transfer')->where($sql)->setField('source_fid', $target_fid);
            $sql = array('uid'=>$uid, 'target_fid'=>$source_fid, 'source_fid'=>array('neq', $target_fid));
            M('account_transfer')->where($sql)->setField('target_fid', $target_fid);
        }
        // 源账户初始记录和删除
        $sql = array(
            'uid'=>$uid, 
            '_complex'=>array(
                '_logic'=>'or', 
                'source_fid'=>$source_fid, 
                'target_fid'=>$source_fid)
        );
        $retDelete = M('account_transfer')->where($sql)->delete();
    }

    //获取指定账户转账金额汇总、转入金额、转出金额
    function GetFundsTransferMoney($fid, $uid, $data=false) {
        $FundsData = GetFundsIdData($fid, $uid);
        if ($FundsData[0]) {
            $sql = array('uid'=>$uid);
            if ($data != false) {
                if($data['acremark']){
                    $sql['mark'] = array('like', '%'.$data['acremark'].'%');
                }
                if($data['starttime']){
                    $strData = strtotime(date($data['starttime']." 0:0:0"));
                    $sql['time'] = array('egt', $strData);
                }
                if($data['endtime']){
                    $strData = strtotime(date($data['endtime']." 23:59:59"));
                    $arrEnd = array('elt', $strData);
                    if(is_array($sql['time'])){
                        $sql['time'] = array($sql['time'], $arrEnd);
                    }else{
                        $sql['time'] = $arrEnd;
                    }
                }
            }
            $count = 0;
            $sql['source_fid'] = $fid;
            $outSum = floatval(M("account_transfer")->where($sql)->sum('money'));
            $count += intval(M("account_transfer")->where($sql)->count());
            $sql['source_fid'] = 0;
            $sql['target_fid'] = $fid;
            $initMoney = floatval(M("account_transfer")->where($sql)->sum('money'));
            $count += intval(M("account_transfer")->where($sql)->count());
            $sql['source_fid'] = array('gt', 0);
            $sql['target_fid'] = $fid;
            $inSum = floatval(M("account_transfer")->where($sql)->sum('money'));
            $count += intval(M("account_transfer")->where($sql)->count());
            return array(true, array('in'=>$inSum, 'out'=>$outSum, 'init'=> $initMoney, 'over'=>$initMoney+$inSum-$outSum, 'count'=>$count));
        } else {
            return array(false, '资金账户id不存在~');
        }
    }

    //获取转账数据列表
    function GetFundsIdTransferData($uid, $page=1, $fid=0) {
        $sql = array('transfer.uid'=>$uid);
        if ($fid > 0) {
            $sql['_complex'] = array('_logic'=>'or', 'transfer.source_fid'=>$fid, 'transfer.target_fid'=>$fid);
        }
        $ret['count'] = M('account_transfer')->alias('transfer')->where($sql)->count();
        $DbSQL = M('account_transfer')->alias('transfer')
            ->field('transfer.*, source.fundsname as source_fname, target.fundsname as target_fname')
            ->join('__ACCOUNT_FUNDS__ AS target ON transfer.target_fid = target.fundsid', 'LEFT')
            ->join('__ACCOUNT_FUNDS__ AS source ON transfer.source_fid = source.fundsid', 'LEFT')
            ->fetchSql(false)->where($sql)->order("transfer.time DESC , transfer.tid DESC");
        $ret['page'] = 1;
        $ret['pagemax'] = 1;
        if ($page > 0) {
            $DbSQL = $DbSQL->page($page, C('PAGE_SIZE'));
            $ret['page'] = $page;
            $ret['pagemax'] = intval(($ret['count'] - 1) / C('PAGE_SIZE')) + 1;
        }
        $ret['data'] = $DbSQL->select();
        return $ret;
    }

    //搜索转账数据
    function FindTransferData($data, $page=0) {
        $arrSQL = array();
        if ($data['fid']) {
            if ($data['acclassid'] === 'outTransfer') {
                $arrSQL['transfer.source_fid'] = intval($data['fid']);
            } elseif ($data['acclassid'] === 'inTransfer') {
                $arrSQL['transfer.target_fid'] = intval($data['fid']);
            } else {
                $arrSQL['_complex'] = array(
                    'transfer.source_fid' => intval($data['fid']),
                    'transfer.target_fid' => intval($data['fid']),
                    '_logic' => 'or'
                );
            }
        }
        if($data['jiid']){
            $arrSQL['transfer.uid'] = $data['jiid'];
        }
        if($data['acremark']){
            $arrSQL['transfer.mark'] = array('like', '%'.$data['acremark'].'%');
        }
        if($data['starttime']){
            $strData = strtotime(date($data['starttime']." 0:0:0"));
            $arrSQL['transfer.time'] = array('egt', $strData);
        }
        if($data['endtime']){
            $strData = strtotime(date($data['endtime']." 23:59:59"));
            $arrEnd = array('elt', $strData);
            if(is_array($arrSQL['transfer.time'])){
                $arrSQL['transfer.time'] = array($arrSQL['transfer.time'], $arrEnd);
            }else{
                $arrSQL['transfer.time'] = $arrEnd;
            }
        }
        $ret['count'] = M('account_transfer')->alias('transfer')->where($arrSQL)->count();
        $DbSQL = M('account_transfer')->alias('transfer')
            ->field('transfer.*, source.fundsname as source_fname, target.fundsname as target_fname')
            ->join('__ACCOUNT_FUNDS__ AS target ON transfer.target_fid = target.fundsid', 'LEFT')
            ->join('__ACCOUNT_FUNDS__ AS source ON transfer.source_fid = source.fundsid', 'LEFT')
            ->fetchSql(false)->where($arrSQL)->order("transfer.time DESC , transfer.tid DESC");
        $ret['isTransfer'] = true;
        $ret['SumInMoney'] = 0;
        $ret['SumOutMoney'] = 0;
        $ret['page'] = 1;
        $ret['pagemax'] = 1;
        if ($page > 0) {
            $DbSQL = $DbSQL->page($page, C('PAGE_SIZE'));
            $ret['page'] = $page;
            $ret['pagemax'] = intval(($ret['count'] - 1) / C('PAGE_SIZE')) + 1;
        }
        $ret['data'] = $DbSQL->select();
        return $ret;
    }

    //获取搜索转账数据库对象
    function GetFindTransferDb($data, $typeid) {
        $arrSQL = array();
        if($data['jiid']){
            $arrSQL['transfer.uid'] = $data['jiid'];
        }
        if($data['acremark']){
            $arrSQL['transfer.mark'] = array('like', '%'.$data['acremark'].'%');
        }
        if($data['starttime']){
            $strData = strtotime(date($data['starttime']." 0:0:0"));
            $arrSQL['transfer.time'] = array('egt', $strData);
        }
        if($data['endtime']){
            $strData = strtotime(date($data['endtime']." 23:59:59"));
            $arrEnd = array('elt', $strData);
            if(is_array($arrSQL['transfer.time'])){
                $arrSQL['transfer.time'] = array($arrSQL['transfer.time'], $arrEnd);
            }else{
                $arrSQL['transfer.time'] = $arrEnd;
            }
        }
        $arrSQL['transfer.source_fid'] = array('neq', 0);
        $DbSQL = M('account_transfer')->alias('transfer');
        if ($typeid == 2) {
            if ($data['fid']) {
                $arrSQL['transfer.source_fid'] = intval($data['fid']);
            }
            $DbSQL = $DbSQL->field(" transfer.tid, transfer.money, 0, '转账', transfer.mark, transfer.time, 2, '支出', transfer.source_fid, funds.fundsname as funds, transfer.uid")
            ->join('__ACCOUNT_FUNDS__ AS funds ON funds.fundsid = transfer.source_fid', 'LEFT')
            ->where($arrSQL);
        }
        if ($typeid == 1) {
            if ($data['fid']) {
                $arrSQL['transfer.target_fid'] = intval($data['fid']);
            }
            $DbSQL = $DbSQL->field(" transfer.tid, transfer.money, 0, '转账', transfer.mark, transfer.time, 1, '收入', transfer.target_fid, funds.fundsname as funds, transfer.uid")
            ->join('__ACCOUNT_FUNDS__ AS funds ON funds.fundsid = transfer.target_fid', 'LEFT')
            ->where($arrSQL);
        }
        return $DbSQL;
    }

    function GetFindTransferAccountSql($data, $alias='') {
        if($data['jiid']){
            $arrSQL[$alias.'jiid'] = $data['jiid'];
        }
        if ($data['fid']) {
            $arrSQL[$alias.'fid'] = $data['fid'];
        }
        if($data['acremark']){
            $arrSQL[$alias.'acremark'] = array('like', '%'.$data['acremark'].'%');
        }
        if($data['starttime']){
            $strData = strtotime(date($data['starttime']." 0:0:0"));
            $arrSQL[$alias.'actime'] = array('egt', $strData);
        }
        if($data['endtime']){
            $strData = strtotime(date($data['endtime']." 23:59:59"));
            $arrEnd = array('elt', $strData);
            if(is_array($arrSQL[$alias.'actime'])){
                $arrSQL[$alias.'actime'] = array($arrSQL[$alias.'actime'], $arrEnd);
            }else{
                $arrSQL[$alias.'actime'] = $arrEnd;
            }
        }
        if($data['acclassid']){
            $arrSQL[$alias.'acclassid'] = $data['acclassid'];
        }
        if($data['zhifu']){
            $arrSQL[$alias.'zhifu'] = $data['zhifu'];
        }
        return $arrSQL;
    }

    //获取搜索记账数据库对象
    function GetFindTransferAccountDb($data) {
        $ret = M('account')->alias('account')
            ->field("account.acid as id, account.acmoney as money, account.acclassid as classid, class.classname as class, account.acremark as mark, account.actime as time, account.zhifu as typeid, case account.zhifu when 1 then '收入'  when 2 then '支出' end as type, account.fid as fundsid, funds.fundsname as funds, account.jiid as uid")
            ->join('__ACCOUNT_FUNDS__ AS funds ON funds.fundsid = account.fid', 'LEFT')
            ->join('__ACCOUNT_CLASS__ AS class ON class.classid = account.acclassid', 'LEFT');
        if ((!isset($data['acclassid']) && !isset($data['zhifu'])) || stripos($data['acclassid'], "transfer")!==false) {
            $ret = $ret->union(GetFindTransferDb($data, 2)->fetchSql(true)->select())
                ->union(GetFindTransferDb($data, 1)->fetchSql(true)->select() . " ORDER BY time DESC, id DESC");
        } else {
            $ret = $ret->order("time DESC , id DESC");
        }
        return $ret->where(GetFindTransferAccountSql($data, 'account.'));
    }

    //搜索转账和记账数据
    function FindTransferAccountData($data, $page=0) {
        //初始化返回值
        $ret = array('count'=>0, 'SumInMoney'=>0.0, 'SumOutMoney'=>0.0, 'isTransfer'=>false);

        //计算输出数量
        $ret['count'] = M('account')->where(GetFindTransferAccountSql($data))->count() + GetFindTransferDb($data, 2)->count() + GetFindTransferDb($data, 1)->count();

        //计算统计值
        if($data['zhifu'] == 1){
            $ret['SumInMoney']  += SumDbAccount(GetFindSqlArr($data));
        }elseif($data['zhifu'] == 2){
            $ret['SumOutMoney'] += SumDbAccount(GetFindSqlArr($data));
        }else{
            $data['zhifu'] = 1;
            $ret['SumInMoney']  += SumDbAccount(GetFindSqlArr($data));
            $data['zhifu'] = 2;
            $ret['SumOutMoney'] += SumDbAccount(GetFindSqlArr($data));
            unset($data['zhifu']);
        }
        if (isset($data['fid']) && !isset($data['acclassid']) && !isset($data['zhifu'])) {
            $sumTransfer = GetFundsTransferMoney($data['fid'], $data['jiid'], $data);
            if ($sumTransfer[0]) {
                $ret['SumInMoney']  += $sumTransfer[1]['in'];
                $ret['SumOutMoney'] += $sumTransfer[1]['out'];
                $ret['isTransfer'] = true;
            }
        }

        //输出明细
        $ret['page'] = 1;
        $ret['pagemax'] = 1;
        $DbSQL = GetFindTransferAccountDb($data);
        if ($page > 0) {
            $strSQL = $DbSQL->fetchSql(true)->select()." LIMIT ".(intval($page)-1)*C('PAGE_SIZE').",".C('PAGE_SIZE');
            $ret['data'] = M()->query($strSQL);
            $ret['page'] = $page;
            $ret['pagemax'] = intval(($ret['count'] - 1) / C('PAGE_SIZE')) + 1;
        } else {
            $ret['data'] = $DbSQL->select();
        }
        // dump($strSQL);
        // dump($ret);
        return $ret;
    }

    //校验分类名
    function CheakClassName($ClassName, $uid, $ClassType=0, $ClassId=0) {
        if(strlen($ClassName) < 1){
            return array(false, '分类名不得为空！');
        }

        if(strlen($ClassName) > C('MAX_CLASS_NAME')){
            return array(false, '分类名太长，请控制在' . C('MAX_CLASS_NAME') . '个字符以内。');
        }

        $sql = array('classname' => $ClassName, 'ufid' => $uid);
        if(intval($ClassType) > 0) {
            $sql['classtype'] = intval($ClassType);
        }

        $ClassData = M("account_class")->where($sql)->select();
        if(is_array($ClassData)) {
            if($ClassId > 0) {
                foreach ($ClassData as $key => $ClassArr) {
                    if(intval($ClassData[$key]['classid']) !== intval($ClassId)){
                        return array(false, '分类名已存在!');
                    }
                }
            }else{
                return array(false, '分类名已存在...');
            }
        }
        return array(true, $ClassName);
    }
    
    //校验新分类
    function CheakNewClass($data) {
        if(!is_array($data)){
            return array(false,'非法操作233');
        }
        if(strlen($data['classname']) < 1){
            return array(false,'分类名不得为空！');
        }
        if(strlen($data['classname']) > C('MAX_CLASS_NAME')){
            return array(false, '分类名太长，请控制在' . C('MAX_CLASS_NAME') . '个字符以内。');
        }
        if($data['classtype'] == 0){
            return array(false,'非法操作223');
        }
        if($data['classtype'] > 2){
            return array(false,'无效的分类类别{\/}');
        }
        if($data['ufid'] == 0){
            return array(false,'非法操作333');
        }
        $condition['ufid'] = $data['ufid'];
        $condition['classname'] = $data['classname'];
        if(M("account_class")->where($condition)->select()){
            return array(false,'分类已存在(@_@)');
        }
        return array(true,$data);
    }
    
    //新建分类
    function AddNewClass($data) {
        $isCheak = CheakNewClass($data);
        if($isCheak[0]){
            $data = $isCheak[1];
            $DbData = M('account_class')->add($data);
            ClearDataCache();
            if($DbData > 0){
                return array(true,'新建分类成功!');
            }else{
                return array(false,'写入数据库出错(&_&)');
            }
        }else{
            return $isCheak;
        }
    }

    //快速新建分类（当前uid下无分类时可用，主要用于新用户）
    function FastAddNewClass($data, $uid) {
        //去除重复数据
        $data = array2_unique($data);

        if ((intval($uid) > 0) && (count($data) > 0)) {
            $condition = array();
            //判断用户分类是否为空
            if(M("account_class")->where(array('ufid' => intval($uid)))->find()){
                return array(false, '已经创建过分类，无法使用快速创建。', 0);
            }
            //检查分类名合法性
            foreach ($data as $key => $value) {
                if(!is_array($value)){
                    return array(false, '非法操作233', $key);
                }
                if(strlen($value['classname']) < 1){
                    return array(false, '分类名不得为空！', $key);
                }
                if(strlen($value['classname']) > C('MAX_CLASS_NAME')){
                    return array(false, '分类名太长，请控制在' . C('MAX_CLASS_NAME') . '个字符以内。', $key);
                }
                if($value['classtype'] == 0){
                    return array(false, '非法操作223', $key);
                }
                if($value['classtype'] > 2){
                    return array(false, '无效的分类类别{\/}', $key);
                }
                array_push($condition, array('classname'=>$value['classname'], 'classtype'=>$value['classtype'], 'ufid'=>$uid));
            }
            //判断缓存数组长度
            if (count($condition) == 0) {
                return array(false, '有效写入数据为空', 0);
            }
            //批量写入数据库
            $DbData = M('account_class')->addAll($condition);
            ClearDataCache();
            return array(true, '快速新建分类完成！', intval($DbData));
        } else {
            return array(false, '非法操作234', 0);
        }
    }

    //修改分类名
    function editClassName($ClassName, $ClassId, $ClassType, $uid) {
        $isCheak = CheakClassName($ClassName, $uid, $ClassType, $ClassId);
        if($isCheak[0]) {
            $sql = array('classid' => intval($ClassId), 'ufid' => intval($uid));
            $ret = M("account_class")->where($sql)->setField('classname',$ClassName);
            ClearDataCache();
            return array(true,'分类名修改成功！');
        }else{
            return $isCheak;
        }
    }
    
    //改变分类类别
    function ChangeClassType($ClassId,$uid) {
        $sql = array('classid' => intval($ClassId), 'ufid' => intval($uid));
        $ClassType = intval(M("account_class")->where($sql)->getField('classtype'));
        $ClassName = M("account_class")->where($sql)->getField('classname');
        if($ClassType === 1){
            $Type = 2;
        }elseif($ClassType === 2){
            $Type = 1;
        }else{
            return array(false,'分类id不存在...');
        }
        $isCheak = CheakClassName($ClassName, $uid, $Type, $ClassId);
        if($isCheak[0]) {
            M("account_class")->where($sql)->setField('classtype',$Type);
            $sql = array('acclassid' => intval($ClassId), 'jiid' => intval($uid));
            M("account")->where($sql)->setField('zhifu',$Type);
            ClearDataCache();
            return array(true,$Type);
        }else{
            return $isCheak;
        }
    }

    //调整分类排序
    function SortClass($ClassIdList, $uid)
    {
        for ($i=0; $i < count($ClassIdList); $i++) { 
            $sql = array('ufid'=>$uid, 'classid' => intval($ClassIdList[$i]));
            if ($sql['classid'] > 0) {
                M("account_class")->where($sql)->setField('sort', $i + 1);
            }
        }
        ClearDataCache();
    }

    //获取指定分类记账数量
    function GetClassAccountNum($ClassId, $uid)
    {
        $arrSQL = array('acclassid' => intval($ClassId), 'jiid' => intval($uid));
        return M('account')->where($arrSQL)->count();
    }
    
    //删除分类
    function DelClass($ClassId,$uid) {
        $sql = 'classid = '.intval($ClassId).' and ufid = '.$uid;
        $ClassData = M("account_class")->where($sql)->find();
        if(is_array($ClassData)){
            $DbData = M("account_class")->where($sql)->delete();
            if($DbData > 0){
                ClearDataCache();
                return array(true,'已成功删除【'.$ClassData['classname'].'】分类!',$ClassData['classtype']);
            }elseif($DbData === 0){
                return array(false,'未找到你要删除的分类(@_@)',$ClassData['classtype']);
            }else{
                return array(false,'分类数据库异常(*_*)',$ClassData['classtype']);
            }
        }else{
            return array(false,'你要删除的分类不存在...',0);
        }
    }
    
    //获取指定id分类
    function GetClassIdData($ClassId,$uid) {
        $sql = 'classid = '.intval($ClassId).' and ufid = '.$uid;
        $ClassData = M("account_class")->where($sql)->find();
        if(is_array($ClassData)){
            return array(true,$ClassData);
        }else{
            return array(false,'分类id不存在~');
        }
    }

    //转移分类数据 (将classid1中的数据转移到classid2中)
    function MoveClassAccount($ClassId1,$ClassId2,$uid) {
        $arrSQL = array('acclassid' => intval($ClassId1), 'jiid' => intval($uid));
        $arrUpdata = array('acclassid' => intval($ClassId2));
        $ret = M('account')->where($arrSQL)->setField($arrUpdata);
        ClearDataCache();
        return $ret;
    }

    //获取分类所有数据(用户id,1=收入 2=支出) -- GetClassData函数的加强版
    function GetClassAllData($uid,$type=0) {
        $CacheData = S('account_class_data_'.$uid);
        if($CacheData) {
            return $CacheData;
        }
        $strSQL = array();
        $strSQL['ufid'] = $uid;
        if($type) {
            $strSQL['classtype'] = $type;
        }
        $DbClass = M('account_class')->where($strSQL)->order('sort,classid')->select();
        $CacheData = array();
        foreach($DbClass as $key => $Data) {
            $classId = intval($Data['classid']);
            $className = $Data['classname'];
            $classType = intval($Data['classtype']);
            $sql = array('acclassid'=>$classId, 'jiid'=>$uid);
            $classCount = intval(M('account')->where($sql)->count('acmoney'));
            $classMoney = floatval(M('account')->where($sql)->sum('acmoney'));
            array_push($CacheData, array('id'=>$classId, 'name'=>$className, 'type'=>$classType, 'count'=>$classCount, 'money'=>$classMoney));
        }
        S('account_class_data_'.$uid, $CacheData);
        return $CacheData;
    }

    //获取月份收支数据（月份）
    function getMonthData($y, $m, $uid) {
        if (($y >= 2000)&&($m >= 1)&&(m <= 12)) {
            $DataArray['Year'] = $y;
            $DataArray['Month'] = $m;
            $dInMoney  = array(); //日收入金额
            $dOutMoney = array(); //日支出金额
            $dInSumMoney  = 0; //收入总金额
            $dOutSumMoney = 0; //支出总金额
            $dInSumClassMoney  = array(); //分类收入金额
            $dOutSumClassMoney = array(); //分类支出金额
            $dSurplusMoney  = array(); //日剩余金额
            $dSurplusSumMoney  = array(); //日剩余金额
            $ArrInClass  = GetClassData($uid, 1);
            $ArrOutClass = GetClassData($uid, 2);
            //日数据统计
            $numDay = date('d', strtotime($y.'-'.$m.'-01 +1 month -1 day'));
            for ($d=1; $d <= $numDay; $d++) { 
                $ArrSQL = array();
                $fristDayTime = strtotime($y.'-'.$m.'-'.$d.' 0:0:0');
                $lastDayTime = strtotime($y.'-'.$m.'-'.$d.' 23:59:59');
                $ArrSQL['actime'] = array(array('egt',$fristDayTime),array('elt',$lastDayTime));
                $ArrSQL['jiid'] = $uid;
                $ArrSQL['zhifu'] = 1;
                $dInMoney[$d]  = SumDbAccount($ArrSQL);
                $ArrSQL['zhifu'] = 2;
                $dOutMoney[$d] = SumDbAccount($ArrSQL);
                $dSurplusMoney[$d] = $dInMoney[$d] - $dOutMoney[$d];
                $dSurplusSumMoney[$d] = array_sum($dSurplusMoney);
            }
            $DataArray['InMoney'] = $dInMoney;
            $DataArray['OutMoney']= $dOutMoney;
            $DataArray['SurplusMoney'] = $dSurplusMoney;
            $DataArray['InSumMoney'] = array_sum($dInMoney);
            $DataArray['OutSumMoney']= array_sum($dOutMoney);
            $DataArray['SurplusSumMoney'] = $dSurplusSumMoney;
            //分类数据统计
            $fristDayTime = strtotime($y.'-'.$m.'-01 0:0:0');
            $lastDayTime = strtotime($y.'-'.$m.'-'.$numDay.' 23:59:59');
            $ArrSQL['actime'] = array(array('egt',$fristDayTime),array('elt',$lastDayTime));
            $ArrSQL['zhifu'] = 1;
            foreach ($ArrInClass as $ClassId => $ClassName) {
                $ArrSQL['acclassid'] = $ClassId;
                $dInSumClassMoney[$ClassName] = SumDbAccount($ArrSQL);
            }
            $ArrSQL['zhifu'] = 2;
            foreach ($ArrOutClass as $ClassId => $ClassName) {
                $ArrSQL['acclassid'] = $ClassId;
                $dOutSumClassMoney[$ClassName] = SumDbAccount($ArrSQL);
            }
            $DataArray['InSumClassMoney'] = $dInSumClassMoney;
            $DataArray['OutSumClassMoney']= $dOutSumClassMoney;
        } else {
            $DataArray['Year'] = false;
            $DataArray['Month'] = false;
        }
        $DataJson = json_encode($DataArray);
        return $DataJson;
    }

    //获取年度收支数据（年份）
    function getYearData($y,$uid){
        $CacheData = S('chart_year_'.$uid);
        if ($CacheData[$y]) {
            return $CacheData[$y];
        }
        if($y >= 2000){
            $DataArray['Year'] = $y;
            $mInMoney  = array(); //月收入金额
            $mOutMoney = array(); //月支出金额
            $mInSumMoney  = 0; //收入总金额
            $mOutSumMoney = 0; //支出总金额
            $mInClassMoney  = array(); //分类收入金额
            $mOutClassMoney = array(); //分类支出金额
            $mSurplusMoney  = array(); //年剩余金额
            $ArrInClass  = GetClassData($uid, 1);
            $ArrOutClass = GetClassData($uid, 2);
            for($m=1;$m<=12;$m++){
                $t = mktime(0,0,0,$m,1,$y);
                $tStart = mktime(0,0,0,date("m",$t),1,date("Y",$t));
                $tEnd = mktime(23,59,59,date("m",$t),date("t",$t),date("Y",$t));
                $fristDay = date("Y-m-d",$tStart);
                $lastDay = date("Y-m-d",$tEnd);
                //dump($m."月:第一天:".$fristDay." 最后一天:".$lastDay);// "$m月:第一天:$fristDay 最后一天:$lastDay";
                $ArrSQL = array();
                $ArrSQL['actime'] = array(array('egt',strtotime($fristDay." 0:0:0")),array('elt',strtotime($lastDay." 23:59:59")));
                $ArrSQL['jiid'] = $uid;

                $mInMoney[$m]  = 0;
                $mOutMoney[$m] = 0;
                foreach ($ArrInClass as $ClassId => $ClassName) {
                    $ArrSQL['acclassid'] = $ClassId;
                    $mInClassMoney[$ClassName][$m] += floatval(M('account')->where($ArrSQL)->sum('acmoney'));
                    $mInSumClassMoney[$ClassName]  += $mInClassMoney[$ClassName][$m];
                    $mInMoney[$m]  += $mInClassMoney[$ClassName][$m];
                }
                //dump($m.'月收入:'.$mInMoney[$m]);
                foreach ($ArrOutClass as $ClassId => $ClassName) {
                    $ArrSQL['acclassid'] = $ClassId;
                    $mOutClassMoney[$ClassName][$m] += floatval(M('account')->where($ArrSQL)->sum('acmoney'));
                    $mOutSumClassMoney[$ClassName]  += $mOutClassMoney[$ClassName][$m];
                    $mOutMoney[$m]  += $mOutClassMoney[$ClassName][$m];
                }
                //dump($m.'月支出:'.$mOutMoney[$m]);
                
                $mSurplusMoney[$m] = $mInMoney[$m] - $mOutMoney[$m];
                $mInSumMoney  = $mInSumMoney + $mInMoney[$m];
                $mOutSumMoney = $mOutSumMoney + $mOutMoney[$m];
                $mSurplusSumMoney[$m] = $mInSumMoney - $mOutSumMoney;
            }
            $DataArray['InMoney'] = $mInMoney;
            $DataArray['OutMoney']= $mOutMoney;
            $DataArray['SurplusMoney'] = $mSurplusMoney;
            $DataArray['InClassMoney'] = $mInClassMoney;
            $DataArray['OutClassMoney']= $mOutClassMoney;
            $DataArray['InSumMoney'] = $mInSumMoney;
            $DataArray['OutSumMoney']= $mOutSumMoney;
            $DataArray['InSumClassMoney'] = $mInSumClassMoney;
            $DataArray['OutSumClassMoney']= $mOutSumClassMoney;
            $DataArray['SurplusSumMoney'] = $mSurplusSumMoney;
            //dump($DataArray);
        }else{
            $DataArray['Year'] = "FALSE";
        }
        $DataJson = json_encode($DataArray);
        $CacheData[$y] = $DataJson;
        S('chart_year_'.$uid, $CacheData);
        return $DataJson;
    }

    //获取用户记账区间(时间戳)
    function getAccountTimeBetween($uid) {
        if ($uid > 0) {
            $CacheData = S('account_time_between_'.$uid);
            if ($CacheData) {
                return $CacheData;
            }

            $arrSQL = array();
            $arrSQL['jiid'] = $uid;
            $TimeMax = intval(M('account')->where($arrSQL)->max('actime'));
            $TimeMin = intval(M('account')->where($arrSQL)->min('actime'));
            if (($TimeMax >= $TimeMin)&&($TimeMin > 1)) {
                $ret['TimeMax'] = $TimeMax;
                $ret['TimeMin'] = $TimeMin;
                $ret['YearMax'] = intval(date('Y', $TimeMax));
                $ret['YearMin'] = intval(date('Y', $TimeMin));
                $ret['DateMax'] = date('Y-m-d', $TimeMax);
                $ret['DateMin'] = date('Y-m-d', $TimeMin);
                S('account_time_between_'.$uid, $ret);
                return $ret;
            } else {
                $TimeMax = time();
                $TimeMin = time();
                $ret['TimeMax'] = $TimeMax;
                $ret['TimeMin'] = $TimeMin;
                $ret['YearMax'] = intval(date('Y', $TimeMax));
                $ret['YearMin'] = intval(date('Y', $TimeMin));
                $ret['DateMax'] = date('Y-m-d', $TimeMax);
                $ret['DateMin'] = date('Y-m-d', $TimeMin);
                return $ret;
            }
        }
    }

    //获取历年收支数据(年份)
    function getAllYearData($uid) {
        $CacheData = S('chart_all_year_'.$uid);
        if ($CacheData) {
            return $CacheData;
        }

        $TimeBetween = getAccountTimeBetween($uid);
        if (is_array($TimeBetween)) {
            $DataArray = array();
            $YearMax = $TimeBetween['YearMax'];
            $YearMin = $TimeBetween['YearMin'];
            $arrSQL['jiid'] = $uid;
            for ($y=$YearMin; $y <= $YearMax; $y++) { 
                $TimeMin = strtotime($y.'-01-01 00:00:00');
                $TimeMax = strtotime($y.'-12-31 23:59:59');
                $arrSQL['actime'] = array(array('egt',$TimeMin),array('elt',$TimeMax));
                $arrSQL['zhifu'] = 1;
                $yInMoney[$y] = SumDbAccount($arrSQL);
                $arrSQL['zhifu'] = 2;
                $yOutMoney[$y] = SumDbAccount($arrSQL);
                $ySurplusMoney[$y] = $yInMoney[$y] - $yOutMoney[$y];
            }
            $DataArray['YearMax'] = $YearMax;
            $DataArray['YearMin'] = $YearMin;
            $DataArray['InMoney'] = $yInMoney;
            $DataArray['OutMoney']= $yOutMoney;
            $DataArray['SurplusMoney'] = $ySurplusMoney;
            $DataArray['InSumMoney'] = array_sum($yInMoney);
            $DataArray['OutSumMoney']= array_sum($yOutMoney);
            $DataArray['SurplusSumMoney'] = $DataArray['InSumMoney'] - $DataArray['OutSumMoney'];
            $DataJson = json_encode($DataArray);
            S('chart_all_year_'.$uid, $DataJson);
            return $DataJson;
        }
    }
    
    //数组转表格数据
    function ArrayToNumData($arr){
        $str = "[";
        foreach($arr as $value){
            $str = "$str$value,";
        }
        $str = substr($str,0,-1); // 去除最后一个,
        $str = $str."]";
        return $str;
    }

    //数组键转表格数据
    function ArrayKeyToNumData($arr){
        $str = "[";
        foreach($arr as $key => $value){
            $str = "$str {value:$value,name:'$key'} ,";
        }
        $str = substr($str,0,-1); // 去除最后一个,
        $str = $str."]";
        return $str;
    }

    //时间戳转字符串
    function NumTimeToStrTime($NumTime, $Format = 'Y-m-d') {
        if (is_array($NumTime)) {
            foreach ($NumTime as $key => $value) {
                $NumTime[$key] = NumTimeToStrTime($value, $Format);
            }
        } else {
            if (intval($NumTime) >= strtotime('2000-01-01')) {
                return date($Format, $NumTime);
            }
        }
        return $NumTime;
    }

    //二维数组去掉重复值
    function array2_unique($array2D,$stkeep=false,$ndformat=true){
      $joinstr='+++++';
      // 判断是否保留一级数组键 (一级数组键可以为非数字)
      if($stkeep) $stArr = array_keys($array2D);
      // 判断是否保留二级数组键 (所有二级数组键必须相同)
      if($ndformat) $ndArr = array_keys(end($array2D));
      //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
      foreach ($array2D as $v){
        $v = join($joinstr,$v);
        $temp[] = $v;
      }
      //去掉重复的字符串,也就是重复的一维数组
      $temp = array_unique($temp);
      //再将拆开的数组重新组装
      foreach ($temp as $k => $v){
        if($stkeep) $k = $stArr[$k];
        if($ndformat){
          $tempArr = explode($joinstr,$v);
          foreach($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
        }
        else $output[$k] = explode($joinstr,$v);
      }
      return $output;
    }

    //发送post
    function post($url, $param=array()){
        if(!is_array($param)){
            throw new Exception("参数必须为array");
        }
        $httph =curl_init($url);
        curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($httph,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($httph, CURLOPT_POST, 1);//设置为POST方式 
        curl_setopt($httph, CURLOPT_POSTFIELDS, $param);
        curl_setopt($httph, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($httph, CURLOPT_HEADER,1);
        $rst=curl_exec($httph);
        curl_close($httph);
        return $rst;
    }
    
    //参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：json
    //json $_POST=json_decode(file_get_contents('php://input'), TRUE);
    function request($url, $data='', $type=''){
         if($type=='json'){
             $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
             $data=json_encode($data);
         }
         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
         if (!empty($data)){
             curl_setopt($curl, CURLOPT_POST, 1);
             curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
         }
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers ); 
         $output = curl_exec($curl);
         curl_close($curl);
         return $output;
    }

    /**
     * 修改config的函数
     * @param $arr1 配置前缀
     * @param $arr2 数据变量
     * @return bool 返回状态
     */
    function setconfig($pat, $rep)
    {
        /**
         * 原理就是 打开config配置文件 然后使用正则查找替换 然后在保存文件.
         * 传递的参数为2个数组 前面的为配置 后面的为数值.  正则的匹配为单引号  如果你的是分号 请自行修改为分号
         * $pat[0] = 参数前缀;  例:   default_return_type
           $rep[0] = 要替换的内容;    例:  json
         */
        if (is_array($pat) and is_array($rep)) {
            for ($i = 0; $i < count($pat); $i++) {
                $pats[$i] = '/\'' . $pat[$i] . '\'(.*?),/';
                if (is_array($rep[$i])) {
                    $reps[$i] = "'". $pat[$i]. "'". " => array(" . implode(",", $rep[$i]) ."),";
                } else if (is_string($rep[$i])) {
                    $reps[$i] = "'". $pat[$i]. "'". " => " . "'".$rep[$i] ."',";
                } elseif (is_numeric($rep[$i])) {
                    $reps[$i] = "'". $pat[$i]. "'". " => " . strval($rep[$i]) .",";
                } else {
                    $reps[$i] = "'". $pat[$i]. "'". " => " . (($rep[$i] == true) ? "true" : "false") .",";
                }
            }
            $fileurl = APP_PATH . "/Common/Conf/config.php";
            $string = file_get_contents($fileurl); //加载配置文件
            $string = preg_replace($pats, $reps, $string); // 正则查找然后替换
            file_put_contents($fileurl, $string); // 写入配置文件
            return true;
        } else {
            return flase;
        }
    }

?>