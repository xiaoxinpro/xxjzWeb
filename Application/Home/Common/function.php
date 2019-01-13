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

    // function UserLoginSuccess($data) {
    //     if ($data['uid'] > 0) {
    //         session('uid',$data['uid']);
    //         session('username',$username);
    //         session('user_shell',md5($data['username'].$data['password']));
    //         S('user_key_'.$username, null); //清除登录验证缓存
    //     }
    // }
    
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
        if ($data['fid'] !== "") {
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
        
        $DbClass = M('account_class')->cache('account_class_'.$type.'_'.$uid)->where($strSQL)->select();
        
        //$ret = array();
        foreach($DbClass as $key => $Data) {
            $classId = $Data['classid'];
            $className = $Data['classname'];
            $ret["$classId"] = $className;
        }
        return $ret;
    }
    
    //数据库数组数据 转 显示数据
    function ArrDataToShowData($ArrData, $ArrClass, $ArrFunds) {
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
        $ArrPage = array();
        for($i=0; $i<$PageMax; $i++) {
            $ArrPage[$i] = $i + 1;
        }
        $retShowData = array();
        foreach($ArrAccount['data'] as $key => $ArrData) {
            $retShowData[$key] = ArrDataToShowData($ArrData, $ArrClass, $ArrFunds);
        }
        return array($Page,$PageMax,$ArrPage,$retShowData);
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
        
        //昨日收支统计
        $today = date("Y-m-d",strtotime('-1 day'));
        $StartTime = strtotime($today." 0:0:0");
        $EndTime = strtotime($today." 23:59:59");
        $ArrData['LastTodayInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['LastTodayOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
        //上月收支统计
        $EndTime = strtotime(date("Y-m-d",mktime(23,59,59,date("m",time())-1,date("t",time()),date("Y",time()))));
        $StartTime = strtotime(date("Y-m-d",mktime(0,0,0,date("m",time())-1,1,date("Y",time()))));
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
    function DelIdData($id) {
        if(is_numeric($id)){
            $DbData = M('account')->where("acid='$id'")->delete();
            if($DbData > 0){
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
        if(!(is_numeric($data['acmoney'])&&($data['acmoney'] > 0))){
            return array(false,'输入的金额无效!');
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
                return array(true,'数据添加成功!');
            }else{
                return array(false,'写入数据库出错(>_<)');
            }
        }else{
            return $isCheak;
        }
    }

    //校验资金账户名
    function CheakFundsName($FundsName, $uid, $FundsId=0) {
        if(strlen($FundsName) < 1){
            return array(false, '资金账户名不得为空！');
        }

        if ($FundsName == "默认") {
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
    function AddNewFunds($FundsName, $uid) {
        $isCheak = CheakFundsName($FundsName, $uid);
        if($isCheak[0]){
            $data = array('fundsname'=>$isCheak[1],'uid'=>$uid);
            $DbData = M('account_funds')->add($data);
            ClearDataCache();
            if($DbData > 0){
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
        $sql = array('uid' => $uid);
        // $DbData = M('account_funds')->cache('account_funds_'.$uid)->where($sql)->select();
        $retData = array();
        array_push($retData, array('name'=>'默认', 'id'=>0, 'money'=> GetFundsAccountSumData(0,$uid)));
        $DbData = M('account_funds')->where($sql)->select();
        foreach ($DbData as $key => $FundsArr) {
            array_push($retData, array('name'=>$FundsArr['fundsname'], 'id'=>$FundsArr['fundsid'], 'money'=> GetFundsAccountSumData($FundsArr['fundsid'],$uid)));
        }
        return $retData;
    }

    //获取指定资金账户记账汇总
    function GetFundsAccountSumData($FundsId, $uid) {
        $sql = array('fid'=>$FundsId, 'jiid'=>$uid);
        $FundsCount = intval(M('account')->where($sql)->count('acmoney'));
        $sql['zhifu'] = 1; //收入
        $InMoneySum = intval(M('account')->where($sql)->sum('acmoney'));
        $sql['zhifu'] = 2; //支出
        $OutMoneySum = intval(M('account')->where($sql)->sum('acmoney'));
        $OverMoneySum = $InMoneySum - $OutMoneySum;
        return array('over'=>$OverMoneySum, 'in'=>$InMoneySum, 'out'=>$OutMoneySum, 'count'=>$FundsCount);
    }

    //校验分类名
    function CheakClassName($ClassName, $uid, $ClassType=0, $ClassId=0) {
        if(strlen($ClassName) < 1){
            return array(false, '分类名不得为空！');
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

?>