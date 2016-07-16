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
    
    function UserLogin($username,$password){
        // $StrUser = "username='$username'";
        $StrUser = array('username' => $username );
        $TimesLogin = intval(S('login_times_'.$username))+1;
        $data = M("user")->where($StrUser)->find();
        if(($TimesLogin <= C('USER_LOGIN_TIMES'))&&($data['password'] === md5($password))){
            session('uid',$data['uid']);
            session('username',$username);
            session('user_shell',md5($data['username'].$data['password']));
            S('user_key_'.$user,null); //清除登录验证缓存
            return true;
        }else{
            session(null);
            S('login_times_'.$username, $TimesLogin, 3600);
            //echo '已经登录失败'.$TimesLogin.'次！';
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
        dump($user.$Password);
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
    
    function ForgetShell(){
        
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
        $mail = new PHPMailer();                // 建立邮件发送类
        $mail->CharSet  = "UTF-8";              // 编码格式UTF-8
        $mail->IsSMTP();                        // 使用SMTP方式发送
        $mail->Host     = C('MAIL_HOST');       // 您的企业邮局域名
        $mail->SMTPAuth = true;                 // 启用SMTP验证功能
        $mail->Username = C('MAIL_USERNAME');   // 邮局用户名(请填写完整的email地址)
        $mail->Password = C('MAIL_PASSWORD');   // 邮局密码
        $mail->Port     = 25;                   // 邮局端口
        $mail->From     = C('MAIL_FROM');       // 邮件发送者email地址
        $mail->FromName = C('MAIL_FROMNAME');   // 邮件发送者姓名
        $mail->AddAddress("$address","");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        //$mail->AddReplyTo("", "");
        if(file_exists($file)){
            $mail->AddAttachment($file); // 添加附件
        }
        $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
        
        $mail->Subject = $subject; //邮件标题
        $mail->Body = $body; //邮件内容，上面设置HTML，则可以是HTML
        
        if(!$mail->Send())
        {
            //echo "邮件发送失败 -- 错误原因: " . $mail->ErrorInfo;
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
    
    //清除缓存
    function ClearDataCache() {
        $uid = session('uid');
        if($uid){
            S('account_class_0_'.$uid,null);
            S('account_class_1_'.$uid,null);
            S('account_class_2_'.$uid,null);
            S('account_tatistic_'.$uid,null);
            $p = S('account_count_'.$uid);
            while($p > 0){
                S('account_data_'.$p.'_'.$uid,null);
                $p = $p - 1;
            }
            S('account_data_0_'.$uid,null);
            S('account_count_'.$uid,null);
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
    function FindAccountData($data) {
        $strSQL = GetFindSqlArr($data);
        $DbCount = M('account')->where($strSQL)->count();
        $DbData = M('account')->where($strSQL)->order("actime DESC , acid DESC")->select();
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
        $ret['SumInMoney']  = $inSumData;
        $ret['SumOutMoney'] = $outSumData;
        $ret['pagemax']     = 1;
        $ret['page']        = 1;
        $ret['count']       = $DbCount;
        $ret['data']        = $DbData;
        return $ret;
    }
    
    //获取记账数据(用户id,页码)
    function GetAccountData($uid, $p) {
        //dump(S('account_data_'.$p));
        $DbCount = M('account')->cache('account_count_'.$uid)->where("jiid='$uid'")->count();
        $DbSQL = M('account')->cache('account_data_'.$p.'_'.$uid)->where("jiid='$uid'")->order("actime DESC , acid DESC");
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
        return $ret;
    }
    
    //获取分类数据(用户id,1=收入 2=支出)
    function GetClassData($uid,$type=0) {
        $strSQL = array();
        $strSQL['ufid'] = $uid;
        if($type) {
            $strSQL['classtype'] = $type;
        }
        
        $DbClass = M('account_class')->cache('account_class_'.$type.'_'.$uid)->where($strSQL)->select();
        
        $ret = array();
        foreach($DbClass as $key => $Data) {
            $classId = $Data['classid'];
            $className = $Data['classname'];
            $ret["$classId"] = $className;
        }
        return $ret;
    }
    
    //数据库数组数据 转 显示数据
    function ArrDataToShowData($ArrData, $ArrClass) {
        $retShowData = array();
        if($ArrData['zhifu'] == 1){
            $classType = '收入';
        }else{
            $classType = '支出';
        }
        $classId = $ArrData['acclassid'];
        $className = $ArrClass[$classId];
        
        $retShowData['id']      = $ArrData['acid'];
        $retShowData['money']   = $ArrData['acmoney'];
        $retShowData['classid'] = $ArrData['acclassid'];
        $retShowData['class']   = $className;
        $retShowData['type']    = $classType;
        $retShowData['time']    = $ArrData['actime'];
        $retShowData['mark']    = $ArrData['acremark'];
        $retShowData['uid']     = $ArrData['jiid'];
        
        return $retShowData;
    }
    
    //整合List表格数组
    function OutListData($ArrAccount, $ArrClass) {
        $Page = $ArrAccount['page'];
        $PageMax = $ArrAccount['pagemax'];
        $ArrPage = array();
        for($i=0; $i<$PageMax; $i++) {
            $ArrPage[$i] = $i + 1;
        }
        $retShowData = array();
        foreach($ArrAccount['data'] as $key => $ArrData) {
            $retShowData[$key] = ArrDataToShowData($ArrData, $ArrClass);
        }
        return array($Page,$PageMax,$ArrPage,$retShowData);
    }
    
    function SumDbAccount($StrSQL) {
        $Ret = M('account')->where($StrSQL)->Sum('acmoney');
        if($Ret == null){
            $Ret = 0.0;
        }
        return $Ret;
    }
    
    //获取指定时间段的记账结果(开始时间戳,结束时间戳,收支,用户id)
    function GetAccountStatistic($StartTime,$EndTime,$Type,$uid) {
        //收支 : 1收入 / 2支出
        $StrSQL = "actime >= $StartTime and actime <= $EndTime and jiid = '$uid' and zhifu = '$Type'";
        return SumDbAccount($StrSQL);
    }
    
    function AccountStatisticProcess($uid) {
        $ArrData = array();
        
        if(S('account_tatistic_'.$uid)){
            return S('account_tatistic_'.$uid);
        }
        
        //今日收支统计
        $today = date("Y-m-d");
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
        $ArrData['SumInMoney']  = GetAccountStatistic($StartTime,$EndTime,1,$uid);
        $ArrData['SumOutMoney'] = GetAccountStatistic($StartTime,$EndTime,2,$uid);
        
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
        return array(true, $ClassName);;
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
        if(!M("account_class")->where($condition)->select()){
            return array(false,'用户不存在!!!');
        }
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
            if($DbData > 0){
                return array(true,'新建分类成功!');
            }else{
                return array(false,'写入数据库出错(&_&)');
            }
        }else{
            return $isCheak;
        }
    }

    //修改分类名
    function editClassName($ClassName, $ClassId, $ClassType, $uid) {
        $isCheak = CheakClassName($ClassName, $uid, $ClassType, $ClassId);
        if($isCheak[0]) {
            $sql = array('classid' => intval($ClassId), 'ufid' => intval($uid));
            $ret = M("account_class")->where($sql)->setField('classname',$ClassName);
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
        return M('account')->where($arrSQL)->setField($arrUpdata);
    }

    //获取年度收支数据（年份）
    function getYearData($y,$uid){
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
        }
        else{
            $DataArray['Year'] = "FALSE";
        }
        $DataJson = json_encode($DataArray);
        return $DataJson;
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

    //数据转表格百分比
    function NumToPerData($arr){
        $ArrayPer = array();
        $sum = 0;
        //求数据和
        foreach($arr as $key => $value){
            $sum = $sum + $value;
        }
        //计算百分比
        foreach($arr as $key => $value){
            $ArrayPer[$key] = $value / $sum;
        }
        return ArrayKeyToNumData($ArrayPer);
    }
?>





















