<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=yes" />
    <title>小歆记账Web安装向导</title>
    <style type="text/css">
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size:12px;
        color:#666666;
        background:#fff;
        text-align:center;

    }

    * {
        margin:0;
        padding:0;
    }

    a {
        color:#1E7ACE;
        text-decoration:none;    
    }

    a:hover {
        color:#000;
        text-decoration:underline;
    }
    h3 {
        font-size:14px;
        font-weight:bold;
    }

    pre,p {
        color:#1E7ACE;
        margin:4px;
    }
    input, select,textarea {
        padding:1px;
        margin:2px;
        font-size:11px;
    }
    .buttom{
        padding:1px 10px;
        font-size:12px;
        border:1px #1E7ACE solid;
        background:#D0F0FF;
    }
    #formwrapper {
        width:350px;
        margin:15px auto;
        padding:20px;
        text-align:left;
        border:1px #1E7ACE solid;
    }

    fieldset {
        padding:10px;
        margin-top:5px;
        border:1px solid #1E7ACE;
        background:#fff;
    }

    fieldset legend {
        color:#1E7ACE;
        font-weight:bold;
        padding:3px 20px 3px 20px;
        border:1px solid #1E7ACE;    
        background:#fff;
    }

    fieldset label {
        float:left;
        width:120px;
        text-align:right;
        padding:4px;
        margin:1px;
    }

    fieldset div {
        clear:left;
        margin-bottom:2px;
    }

    .enter{ text-align:center;}
    .clear {
        clear:both;
    }

    </style>

</head>
<body>
    <?php
        if (file_exists('install.tmp')) {
            ShowAlert('你已经安装过小歆记账，如需重装请删除根目录下的“install.tmp”文件!','无法安装');
        } elseif (version_compare(PHP_VERSION,'5.3.0','<')) {
            ShowAlert('PHP版本过低，请使用5.3及其以上版本！','无法安装');
        } elseif (checkPath('./Application/Common/Conf/config.php') == false) {
            ShowAlert('Application文件夹及其子文件权限不足，请设置755权限后再试。','无法安装');
        } elseif (isset($_POST['submit'])) {
            echo $_POST['submit'];
            $Data = array();
            $Data['db']['host'] = $_POST['db_host'];
            $Data['db']['user'] = $_POST['db_user'];
            $Data['db']['psw'] = $_POST['db_psw'];
            $Data['db']['name'] = $_POST['db_name'];
            $Data['db']['prefix'] = $_POST['db_prefix'];
            $Data['mail']['smtp'] = $_POST['mail_smtp'];
            $Data['mail']['secure'] = $_POST['mail_secure'];
            $Data['mail']['port'] = $_POST['mail_port'];
            $Data['mail']['user'] = $_POST['mail_user'];
            $Data['mail']['psw'] = $_POST['mail_psw'];
            $Data['mail']['from'] = $_POST['mail_from'];
            CheakData($Data);
            // InstallDB($Data['db'],$Data['admin']);
            WriteConfig($Data['db'],$Data['mail']);
            // $jsonData = base64_encode(json_encode($Data));
            // $dbFile = fopen("install.tmp", "w") or die(ShowAlert("请手动删除根目录下的install.php文件。","安装完成"));
            // fwrite($dbFile, $jsonData);
            // fclose($dbFile);
            // ShowAlert($Data['admin']['user']."安装已经完成，请点击下面跳转到登陆页！","安装完成");
            header("Location: updata.php");
            return;
        } else {
    ?>
    <div id="formwrapper">
        <h3 class="enter"><p>小歆记账Web安装向导</p></h3><br/>
        <form action="install.php" method="post">   
            <fieldset>
                <legend>数据库配置</legend>
                <div>
                    <label>数据库主机</label>
                    <input type="text" name="db_host" value="localhost">
                </div>
                <div>
                    <label>数据库用户名</label>
                    <input type="text" name="db_user" value="root">
                </div>
                <div>
                    <label>数据库密码</label>
                    <input type="password" name="db_psw" value="">
                </div>
                <div>
                    <label>数据库名称</label>
                    <input type="text" name="db_name" value="">
                </div>
                <div>
                    <label>数据库表前缀</label>
                    <input type="text" name="db_prefix" value="xxjz_">
                </div>
            </fieldset>
                
            <fieldset>
                <legend>邮箱配置(可选)</legend>
                <div>
                    <label>邮箱SMTP主机</label>
                    <input type="text" name="mail_smtp" value="smtp.163.com">
                </div>
                <div>
                    <label>邮箱安全认证</label>
                    <input type="text" name="mail_secure" value="none">
                </div>
                <div>
                    <label>邮箱端口号</label>
                    <input type="text" name="mail_port" value="25">
                </div>
                <div>
                    <label>邮箱用户名</label>
                    <input type="text" name="mail_user" value="">
                </div>
                <div>
                    <label>邮箱密码</label>
                    <input type="password" name="mail_psw" value="">
                </div>
                <div>
                    <label>发件人邮箱</label>
                    <input type="email" name="mail_from" value="xxjz@163.com">
                </div>
            </fieldset>
            <br/>
            <span style="display:block; text-align:center;">
                <input type="submit" class="buttom" name="submit" value="安装" />   
            </span>
        </form> 
    </div>
    <?php
        }
    ?>
</body>
</html>

<?php
    //显示提示框
    function ShowAlert($Str,$Title="提示") {
        echo '<div id="formwrapper"><fieldset>';
        echo '<legend>'.$Title.'</legend><div style="text-align:center;">';
        echo '<h3>'.$Str.'</h3><br/>';
        if ($Title === "安装完成") {
            echo '<a href="index.php">跳转到主页</a>';    
        } else {
            echo '<a href="install.php">返回</a>';            
        }
        echo '</div></fieldset></div>';
    }

    //获取权限
    function checkPath($file_path)
    {
        return is_writable($file_path);
    }

    //验证数据
    function CheakData($Data) {
        if(strlen($Data['db']['host']) == 0) {
            die(ShowAlert('数据库主机不能为空','安装失败'));
        }
        if(strlen($Data['db']['user']) == 0) {
            die(ShowAlert('数据库用户名不能为空','安装失败'));
        }
        if(strlen($Data['db']['psw']) == 0) {
            die(ShowAlert('数据库密码不能为空','安装失败'));
        }
        if(strlen($Data['db']['name']) == 0) {
            die(ShowAlert('数据库表名不能为空','安装失败'));
        }
        if(strlen($Data['db']['prefix']) <= 1) {
            die(ShowAlert('数据库表名前缀不能为空','安装失败'));
        }
    }

    //数据库是否存在
    function inDataBase($DbName,$Conn){
        mysqli_select_db($Conn, "information_schema");
        $sql="select * from SCHEMATA where SCHEMA_NAME='".$DbName."'";
        $query=mysqli_query($Conn, $sql);
        $indb=is_array($row=mysqli_fetch_array($query));
        return $indb;
    }
    //表是否存在
    function inTable($DbName,$TableName,$Conn){
        mysqli_select_db($Conn, "information_schema");
        $sql="select * from TABLE_CONSTRAINTS where TABLE_SCHEMA='".$DbName."' and TABLE_NAME='".$TableName."'";
        $query=mysqli_query($Conn, $sql);
        $intable=is_array($row=mysqli_fetch_array($query));
        mysqli_select_db($Conn, $DbName);//重新关联账本数据库
        return $intable;
    }

    //安装数据库
    function InstallDB($DbData, $DbUser) {
        //连接数据库
        $Conn = mysqli_connect($DbData['host'],$DbData['user'],$DbData['psw']);
        if (!$Conn) {
            die(ShowAlert('请检查数据库主机地址、用户名和密码是否正确。','连接数据库失败'));
        } else {
            //创建数据库(已存在则跳过)
            if(!inDataBase($DbData['name'],$Conn)){
                $sql = "create database ".$DbData['name']." default character SET utf8 COLLATE utf8_general_ci";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert('请检查该用户是否有权限创建数据库。','创建数据库失败'));
                }
            }
            //创建account表(已存在则报错)
            $DbName = $DbData['name'];
            $TableName = $DbData['prefix']."account";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`acid` int(11) unsigned NOT NULL AUTO_INCREMENT,`acmoney` double(9,2) unsigned NOT NULL,`acclassid` int(11) NOT NULL,`actime` int(11) NOT NULL,`acremark` varchar(64) NOT NULL,`jiid` int(11) NOT NULL,`zhifu` int(11) NOT NULL,`fid` int(11) NOT NULL DEFAULT '-1',PRIMARY KEY (`acid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                }
            }
            //创建account_class表(已存在则报错)
            $TableName = $DbData['prefix']."account_class";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`classid` int(11) NOT NULL AUTO_INCREMENT,`classname` varchar(24) NOT NULL,`classtype` int(1) NOT NULL,`ufid` int(11) NOT NULL,PRIMARY KEY (`classid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                }
            }
            //创建account_funds表
            $TableName = $DbData['prefix']."account_funds";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`fundsid` int(11) NOT NULL AUTO_INCREMENT,`fundsname` varchar(24) NOT NULL,`uid` int(11) NOT NULL,PRIMARY KEY (`fundsid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                }
            }
            //创建user_login表
            $TableName = $DbData['prefix']."user_login";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`lid` int(11) NOT NULL AUTO_INCREMENT,`uid` int(11) NOT NULL,`login_name` varchar(32) NOT NULL,`login_id` varchar(32) NOT NULL,`login_key` varchar(32) NOT NULL,`login_token` varchar(32) NOT NULL,PRIMARY KEY (`lid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                }
            }
            //创建user_push表
            $TableName = $DbData['prefix']."user_push";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`pid` int(11) NOT NULL AUTO_INCREMENT, `uid` int(11) NOT NULL, `push_name` varchar(32) NOT NULL DEFAULT 'Weixin', `push_id` varchar(64) NOT NULL, `push_mark` varchar(32) DEFAULT NULL, `time` int(11) NOT NULL, PRIMARY KEY (`pid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                }
            }
            //创建user表
            $TableName = $DbData['prefix']."user";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`uid` int(11) NOT NULL AUTO_INCREMENT,`username` varchar(24) NOT NULL,`password` varchar(32) NOT NULL,`email` varchar(255) NOT NULL,`utime` int(11) NOT NULL,PRIMARY KEY (`uid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                }
            }
            //创建管理员账号
            $username = $DbUser['user'];
            $password = md5($DbUser['psw']);
            $email = $DbUser['email'];
            $utime = strtotime("now");
            $sql="select * from $TableName where username='$username'";
            if(is_array(mysqli_fetch_array(mysqli_query($Conn, $sql)))){
                die(ShowAlert('默认用户已存在！','创建管理员失败'));
            }else{
                $utime=strtotime("now");
                $sql="insert into $TableName (username, password,email,utime) values ('$username', '$password','$email','$utime')";
                $query=mysqli_query($Conn, $sql);
                if(!$query){
                    die(ShowAlert('请检查该用户是否有权限添加数据权限。','创建管理员失败'));
                }
            }
        }
    }

    //写入配置文件 => /Application/Common/Conf/config.php
    function WriteConfig($DbData, $EmailData) {
        $cFile = fopen("./Application/Common/Conf/config.php", "w") or die("Unable to open file!");
        $txt = "<?php return array( //'配置项'=>'配置值' \n";
        $txt = $txt."\n//数据库配置信息\n";
        $txt = $txt."'DB_TYPE'   => 'mysql',  // 数据库类型 \n";
        $txt = $txt."'DB_HOST'   => '". $DbData['host'] ."',       // 服务器地址 \n";
        $txt = $txt."'DB_NAME'   => '". $DbData['name'] ."',       // 数据库名 \n";
        $txt = $txt."'DB_USER'   => '". $DbData['user'] ."',       // 用户名 \n";
        $txt = $txt."'DB_PWD'    => '". $DbData['psw']  ."',       // 密码 \n";
        $txt = $txt."'DB_PORT'   => 3306,     // 端口 \n";
        $txt = $txt."'DB_PARAMS' =>  array(), // 数据库连接参数 \n";
        $txt = $txt."'DB_PREFIX' => '". $DbData['prefix'] ."',  // 数据库表前缀  \n";
        $txt = $txt."'DB_CHARSET'=> 'utf8',   // 字符集 \n";
        $txt = $txt."'DB_DEBUG'  =>  TRUE,    // 数据库调试模式 开启后可以记录SQL日志 \n";

        $txt = $txt."\n//系统配置\n";
        $txt = $txt."'SHOW_PAGE_TRACE'       => false,       // Trace信息\n";
        $txt = $txt."'SHOW_ERROR_MSG'        => false,       // 错误输出\n";
        $txt = $txt."'HTML_CACHE_ON'         => false,       // 关闭静态缓存 \n";
        $txt = $txt."'URL_CASE_INSENSITIVE'  => false,       // 区分大小写(必须) \n";
        $txt = $txt."'URL_MODEL'             => 3,           // URL模式 \n";

        $txt = $txt."\n//应用配置信息\n";
        $txt = $txt."'USER_LOGIN_TIMES'  => 10,              // 用户登录次 \n";
        $txt = $txt."'PAGE_SIZE'         => 15,              // 表格分页数 \n";
        $txt = $txt."'MAIL_HOST'         => '".$EmailData['smtp']."',              // 邮箱SMTP主机 \n";
        $txt = $txt."'MAIL_SECURE'       => '".$EmailData['secure']."',            // 邮箱安全认证（none、ssl、tls） \n";
        $txt = $txt."'MAIL_PORT'         => '".$EmailData['port']."',              // 邮箱SMTP端口号（默认为25，SSL协议为465或994） \n";
        $txt = $txt."'MAIL_USERNAME'     => '".$EmailData['user']."',              // 邮箱用户名 \n";
        $txt = $txt."'MAIL_PASSWORD'     => '".$EmailData['psw'] ."',              // 邮箱密码 \n";
        $txt = $txt."'MAIL_FROM'         => '".$EmailData['from']."',              // 发件人邮箱 \n";
        $txt = $txt."'MAIL_FROMNAME'     => '小歆记账',      // 发件人名字 \n";

        $txt = $txt."\n//微信小程序配置\n";
        $txt = $txt."'WX_ENABLE'         => false,            //使能微信小程序功能 \n";
        $txt = $txt."'WX_OPENID'         => 'openid',        //微信小程序唯一标识 \n";
        $txt = $txt."'WX_SECRET'         => 'secret',        //微信小程序的 app secret \n";

        $txt = $txt."\n//自定义配置\n";
        $txt = $txt."'XXJZ_TITLE'        => '小歆记账',       //网站名称\n";
        $txt = $txt."'XXJZ_KEYWORDS'     => '',              //网站关键字\n";
        $txt = $txt."'XXJZ_DESCRIPTION'  => '',              //网站描述\n";
        $txt = $txt."'XXJZ_WELCOME'      => '欢迎使用小歆记账！',     //网站欢迎语\n";

        $txt = $txt."//管理员配置\n";
        $txt = $txt."'ADMIN_UID'         => 1,               //管理员UID\n";

        $txt = $txt."\n); \n";
        fwrite($cFile, $txt);
        fclose($cFile);
    }
?>