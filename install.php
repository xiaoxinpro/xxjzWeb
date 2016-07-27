<!DOCTYPE html>
<html lang="en">
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
        } elseif (isset($_POST['submit'])) {
            echo $_POST['submit'];
            $Data = array();
            $Data['db']['host'] = $_POST['db_host'];
            $Data['db']['user'] = $_POST['db_user'];
            $Data['db']['psw'] = $_POST['db_psw'];
            $Data['db']['name'] = $_POST['db_name'];
            $Data['db']['prefix'] = $_POST['db_prefix'];
            $Data['mail']['smtp'] = $_POST['mail_smtp'];
            $Data['mail']['user'] = $_POST['mail_user'];
            $Data['mail']['psw'] = $_POST['mail_psw'];
            $Data['mail']['from'] = $_POST['mail_from'];
            $Data['admin']['user'] = $_POST['admin_user'];
            $Data['admin']['psw'] = $_POST['admin_psw'];
            $Data['admin']['email'] = $_POST['admin_email'];
            CheakData($Data);
            InstallDB($Data['db']);
            $jsonData = base64_encode(json_encode($Data));
            $dbFile = fopen("install.tmp", "w") or die(ShowAlert("安装文件没有写入权限，请修改文件权限！","安装失败"));
            fwrite($dbFile, $jsonData);
            fclose($dbFile);
            ShowAlert($Data['admin']['user']."安装已经完成，请点击下面跳转到登陆页！","安装完成");
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

            <fieldset>
                <legend>管理员信息</legend>
                <div>
                    <label>管理员账号</label>
                    <input type="text" name="admin_user" value="admin">
                </div>
                <div>
                    <label>管理员密码</label>
                    <input type="password" name="admin_psw" value="">
                </div>
                <div>
                    <label>管理员邮箱</label>
                    <input type="email" name="admin_email" value="xxjz@xxgzs.org">
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
        if(strlen($Data['admin']['user']) <= 1) {
            die(ShowAlert('管理员账号无效，请重新输入。','安装失败'));
        }
        if(strlen($Data['admin']['psw']) < 6) {
            die(ShowAlert('管理员密码太短，请重新输入。','安装失败'));
        }
    }

    //数据库是否存在
    function inDataBase($DbName,$Conn){
        mysql_select_db("information_schema",$Conn);
        $sql="select * from SCHEMATA where SCHEMA_NAME='".$DbName."'";
        $query=mysql_query($sql);
        $indb=is_array($row=mysql_fetch_array($query));
        return $indb;
    }
    //表是否存在
    function inTable($DbName,$TableName,$Conn){
        mysql_select_db("information_schema",$Conn);
        $sql="select * from TABLE_CONSTRAINTS where TABLE_SCHEMA='".$DbName."' and TABLE_NAME='".$TableName."'";
        $query=mysql_query($sql);
        $intable=is_array($row=mysql_fetch_array($query));
        mysql_select_db($DbName,$Conn);//重新关联账本数据库
        return $intable;
    }

    function InstallDB($DbData) {
        //连接数据库
        $Conn = mysql_connect($DbData['host'],$DbData['user'],$DbData['psw']);
        if (!$Conn) {
            die(ShowAlert('请检查数据库主机地址、用户名和密码是否正确。','连接数据库失败'));
        } else {
            //创建数据库(已存在则跳过)
            if(!inDataBase($DbData['name'],$Conn)){
                $sql = "create database ".$DbData['name']." default character SET utf8 COLLATE utf8_general_ci";
                $query=mysql_query($sql);
                if(!$query){
                    die(ShowAlert('请检查该用户是否有权限创建数据库。','创建数据库失败'));
                }
            }
            //创建account表(已存在则报错)
            $DbName = $DbData['name'];
            $TableName = $DbData['prefix']."account";
            if(intable($DbData['name'],$TableName,$Conn)){
                die(ShowAlert('请删除数据库中的表，或者修改表前缀！','数据表已存在'));
            }else{
                $sql = "CREATE TABLE `$DbName`.`$TableName` (`acid` int(11) unsigned NOT NULL AUTO_INCREMENT,`acmoney` double(9,2) unsigned NOT NULL,`acclassid` int(8) NOT NULL,`actime` int(11) NOT NULL,`acremark` varchar(50) NOT NULL,`jiid` int(8) NOT NULL,`zhifu` int(8) NOT NULL,PRIMARY KEY (`acid`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
                $query=mysql_query($sql);
                if(!$query){
                    die(ShowAlert('请检查该用户是否有权限创建表。','创建表失败'));
                }
            }
        }
    }
?>