<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=yes" />
    <title>小歆记账Web升级向导</title>
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
        width:500px;
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
        $config = include './Application/Common/Conf/config.php';
        if (file_exists('install.tmp')) {
            ShowAlert('升级前请删除根目录下的“install.tmp”文件!','无法升级');
        } elseif (version_compare(PHP_VERSION,'5.3.0','<')) {
            ShowAlert('PHP版本过低，请使用5.3及其以上版本！','无法升级');
        } elseif (!is_array($config)) {
            ShowAlert('获取配置文件有误，请检查项目文件读写权限，或重新安装。','无法升级');
        } elseif (isset($_POST['submit'])) {
            $username = htmlspecialchars($_POST['admin_user']);
            $password = htmlspecialchars($_POST['admin_psw']);
            UpdataDB($config,$username,$password);
            $jsonData = base64_encode(json_encode($config));
            $dbFile = fopen("install.tmp", "w") or die(ShowAlert("请手动删除根目录下的updata_2.0.php文件。","升级完成"));
            fwrite($dbFile, $jsonData);
            fclose($dbFile);
            ShowAlert("升级已经完成，请点击下面跳转到登陆页！","升级完成");
        } else {
    ?>
    <div id="formwrapper">
        <h3 class="enter"><p>小歆记账Web升级向导</p></h3><br/>
        <form action="updata_2.0.php" method="post">   
            <fieldset>
                <legend>升级说明</legend>
                <div>
                    本向导将1.x数据库升级到2.x数据库，升级前请确认一下内容：<br/>
                    &emsp;&emsp;1、在升级前请务必备份好数据库中全部数据，如发生数据丢失将无法恢复。<br/>
                    &emsp;&emsp;2、若已经升级过请勿二次使用本向导进行升级操作。<br/>
                    确认无误后请删除根目录下的“install.tmp”文件，并点击“升级”按钮。<br/>
                </div>
            </fieldset>
            <br/>
            <fieldset>
                <legend>权限验证</legend>
                <div>
                    请输入安装时注册的管理员账号和密码：<br/>
                </div>
                <div>
                    <label>管理员账号</label>
                    <input type="text" name="admin_user" value="">
                </div>
                <div>
                    <label>管理员密码</label>
                    <input type="password" name="admin_psw" value="">
                </div>
            </fieldset>
            <br/>
            <span style="display:block; text-align:center;">
                <input type="submit" class="buttom" name="submit" value="升级" />   
            </span>
        </form> 
    </div>
    <?php
        }
    ?>
</body>
</html>

<?php
    function ShowAlert($Str,$Title="提示") {
        echo '<div id="formwrapper"><fieldset>';
        echo '<legend>'.$Title.'</legend><div style="text-align:center;">';
        echo '<h3>'.$Str.'</h3><br/>';
        if ($Title === "升级完成") {
            echo '<a href="index.php">跳转到主页</a>';    
        } else {
            echo '<a href="updata_2.0.php">返回</a>';            
        }
        echo '</div></fieldset></div>';
    }

    //验证数据
    function CheakData($Data) {
        if(strlen($Data['db']['host']) == 0) {
            die(ShowAlert('数据库主机不能为空','升级失败'));
        }
        if(strlen($Data['db']['user']) == 0) {
            die(ShowAlert('数据库用户名不能为空','升级失败'));
        }
        if(strlen($Data['db']['psw']) == 0) {
            die(ShowAlert('数据库密码不能为空','升级失败'));
        }
        if(strlen($Data['db']['name']) == 0) {
            die(ShowAlert('数据库表名不能为空','升级失败'));
        }
        if(strlen($Data['db']['prefix']) <= 1) {
            die(ShowAlert('数据库表名前缀不能为空','升级失败'));
        }
        if(strlen($Data['admin']['user']) <= 1) {
            die(ShowAlert('管理员账号无效，请重新输入。','升级失败'));
        }
        if(strlen($Data['admin']['psw']) < 6) {
            die(ShowAlert('管理员密码太短，请重新输入。','升级失败'));
        }
    }

    //获取管理员账号信息
    function GetUserData($uid,$DbName,$TableName,$Conn) {
        $sql="select * from `$DbName`.`$TableName` where uid='".$uid."'";
        $query=mysqli_query($Conn, $sql);
        return (mysqli_fetch_array($query));
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

    //升级数据库
    function UpdataDB($config,$username,$password) {
        //连接数据库
        $Conn = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD']);
        if (!$Conn) {
            die(ShowAlert('请检测配置文件中的数据库主机地址、用户名和密码是否正确。','连接数据库失败'));
        } else {
            if(!inDataBase($config['DB_NAME'],$Conn)){
                die(ShowAlert('数据库不存在，请重新安装。','连接数据库失败'));
            } else {
                //检验管理员账号
                $UserData = GetUserData($config['ADMIN_UID'], $config['DB_NAME'], $config['DB_PREFIX'] . "user", $Conn);
                if ($UserData['username'] != $username || $UserData['password'] != md5($password)) {
                    die(ShowAlert('请检查管理员账号或密码是否正确！','权限验证失败'));
                }

                //升级account表(不存在则报错)
                $DbName = $config['DB_NAME'];
                $TableName = $config['DB_PREFIX']."account";
                if(!intable($DbName,$TableName,$Conn)){
                    die(ShowAlert($TableName.'数据表不存在，请重新安装！','数据表不存在'));
                }else{
                    $sql = "ALTER TABLE `$DbName`.`$TableName` CHANGE `acclassid` `acclassid` int(11) NOT NULL AFTER `acmoney`, CHANGE `acremark` `acremark` varchar(64) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `actime`, CHANGE `jiid` `jiid` int(11) NOT NULL AFTER `acremark`, CHANGE `zhifu` `zhifu` int(11) NOT NULL AFTER `jiid`, ADD `fid` int(11) NOT NULL DEFAULT '-1';";
                    $query=mysqli_query($Conn, $sql);
                    if(!$query){
                        echo "警告： $TableName 未修改。";
                    }
                    $sql = "ALTER TABLE `$DbName`.`$TableName` CHANGE `fid` `fid` int(11) NOT NULL DEFAULT '-1' AFTER `zhifu`;";
                    $query=mysqli_query($Conn, $sql);
                    $sql = "UPDATE `$DbName`.`$TableName` SET `fid` = '-1' WHERE `fid` = '0';";
                    $query=mysqli_query($Conn, $sql);
                }

                //升级account_class表(不存在则报错)
                $TableName = $config['DB_PREFIX']."account_class";
                if(!intable($DbName,$TableName,$Conn)){
                    die(ShowAlert($TableName.'数据表不存在，请重新安装！','数据表不存在'));
                }else{
                    $sql = "ALTER TABLE `$DbName`.`$TableName` CHANGE `classid` `classid` int(11) NOT NULL AUTO_INCREMENT FIRST, CHANGE `classname` `classname` varchar(24) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `classid`, CHANGE `ufid` `ufid` int(11) NOT NULL AFTER `classtype`;";
                    $query=mysqli_query($Conn, $sql);
                    if(!$query){
                        echo "警告： $TableName 未修改。";
                    }
                }

                //升级user表(不存在则报错)
                $TableName = $config['DB_PREFIX']."user";
                if(!intable($DbName,$TableName,$Conn)){
                    die(ShowAlert($TableName.'数据表不存在，请重新安装！','数据表不存在'));
                }else{
                    $sql = "ALTER TABLE `$DbName`.`$TableName` CHANGE `uid` `uid` int(11) NOT NULL AUTO_INCREMENT FIRST, CHANGE `username` `username` varchar(24) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `uid`, CHANGE `email` `email` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `password`;";
                    $query=mysqli_query($Conn, $sql);
                    if(!$query){
                        echo "警告： $TableName 未修改。";
                    }
                }

                //创建account_funds表(已存在则报错)
                $TableName = $config['DB_PREFIX']."account_funds";
                if(intable($DbName,$TableName,$Conn)){
                    // die(ShowAlert('请删除数据库中的'.$TableName.'表，或者修改表前缀！','数据表已存在'));
                }else{
                    $sql = "CREATE TABLE `$DbName`.`$TableName` ( `fundsid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `fundsname` varchar(24) COLLATE 'utf8mb4_general_ci' NOT NULL, `uid` int(11) NOT NULL ) ENGINE='MyISAM' COLLATE 'utf8mb4_general_ci';";
                    $query=mysqli_query($Conn, $sql);
                    if(!$query){
                        die(ShowAlert("请检查该用户是否有权限创建表 $TableName 。",'创建表失败'));
                    }
                }
            }
        }
    }
?>