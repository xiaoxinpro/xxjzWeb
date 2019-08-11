<!DOCTYPE html>
<html>
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
    $xxjz = include './sql.php';
    $config = include './Application/Common/Conf/config.php';
    
    // $config['DB_PREFIX'] = 'test_';
    
    // 验证配置文件是否存在
    if (!($config && isset($config['DB_HOST']) && isset($config['DB_USER']) && isset($config['DB_PWD']))) {
        ShowAlert('请先运行安装程序，这是用于升级的程序。','尚未安装');
    } elseif (file_exists('install.tmp')) {
        ShowAlert('升级前请删除根目录下的“install.tmp”文件!','无法升级');
    } elseif (version_compare(PHP_VERSION,'5.3.0','<')) {
        ShowAlert('PHP版本过低，请使用5.3及其以上版本！','无法升级');
    } else {
        UpdataDB($xxjz, $config);
    }

    // 主函数（升级数据库）
    function UpdataDB($xxjz, $config) {
        //连接数据库
        $Conn = mysqli_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD']);
        if (mysqli_connect_errno($Conn)) {
            echo("连接 MySQL 失败: " . mysqli_connect_error());
            die(ShowAlert('请检测配置文件中的数据库主机地址、用户名和密码是否正确。','连接数据库失败'));
        }
        
        // 添加表前缀
        $xxjz = AddTablePrefix($xxjz, $config['DB_PREFIX']);
        
        // 获取数据库中的表结构
        $tableDb = GetTableStruct($Conn, $xxjz);
        
        // 比较数据结构并生成升级SQL命令
        $sql = CompTableData($tableDb, $xxjz);
        $sql_md5 = md5($sql);
        
        if ($sql == "") {
            die(ShowAlert('当前数据库为最新版本，无需升级。','无需升级'));
        } elseif (stripos($sql, "ALTER TABLE") !== false) {
            // var_dump("升级数据库。");
            if (isset($_POST['submit']) && $_POST['submit'] == "升级") {
                $username = htmlspecialchars(trim($_POST['admin_user']));
                $password = md5(trim($_POST['admin_psw']));
                CheckUserShell($Conn, $config, $username, $password);
                // var_dump('验证通过，开始升级数据库。');
                if (RunSqlQuery($Conn, $sql, $xxjz, $config)) {
                    ShowAlert("升级已经完成，请点击下面跳转到登陆页！","升级完成");
                } else {
                    ShowAlert("升级因未知原因被中断，建议重新进行数据库安装。","未知错误");
                }
            } else {
                ShowForm();
            }
        } else {
            // var_dump("创建新数据库。");
            if (RunSqlQuery($Conn, $sql, $xxjz, $config)) {
                ShowAlert("安装已经完成，请点击下面跳转到登陆页！","安装完成");
            } else {
                ShowAlert("安装因未知原因被中断，建议重新进行数据库安装。","未知错误");
            }
        }
        
        // var_dump($sql);
    }
    
    // 获取数据库中的表结构
    function GetTableStruct($Conn, $xxjz) {
        $tableDb = array();
        mysqli_select_db($Conn, "information_schema");
        foreach ($xxjz as $item) {
            $tableName =  $item['tableName'];
            $sql = "SELECT * FROM `COLUMNS` WHERE `TABLE_NAME` = '" . $tableName . "';";
            $result = mysqli_query($Conn, $sql);
            if($result) {
                $tableDb[$tableName] = array();
                while($row = $result->fetch_assoc()) {
                    $tableDb[$tableName][$row['COLUMN_NAME']] = $row;
                }
            }
        }
        return $tableDb;
    }
    
    // 添加表前缀
    function AddTablePrefix($xxjz, $prefix) {
        $ret = array();
        foreach ($xxjz as $key => $item) {
            $ret[$prefix.$key] = $item;
            $ret[$prefix.$key]['tableName'] = $prefix.$key;
        }
        return $ret;
    }
    
    // 比较数据表与源数据是否相同
    function CompTableData($table, $data) {
        $sql = "";
        foreach ($data as $dataTableName => $itemDataTable) {
            if(isset($table[$dataTableName]) && count($table[$dataTableName])>0) {
                // 比较字段数据
                $res = CompFieldData($table[$dataTableName], $itemDataTable['tableField'], $itemDataTable['tableKey']);
                if($res != "") {
                    $sql = $sql."ALTER TABLE `".$dataTableName."` ".$res."\r\n";
                }
            } else {
                // 没有表名称，创建表
                $res = "CREATE TABLE `".$dataTableName."` (";
                foreach ($itemDataTable['tableField'] as $fieldKey => $fieldData) {
                    $res = $res.AddFieldSQL($fieldData, "").",";
                }
                $res = $res."PRIMARY KEY (`".$itemDataTable['tableKey']."`)";
                $res = $res.")".$itemDataTable['tableOther'];
                $sql = $sql.$res."\r\n";
            }
        }
        return $sql;
    }
    
    // 比较字段数据与源数据是否相同
    function CompFieldData($field, $data, $dataKey = null) {
        if($dataKey == null) {
            $dataKey = current($data)['COLUMN_NAME'];
        }
        $sql = "";
        $bakFieldName = "";
        foreach ($data as $dataFieldName => $itemDataField) {
            if(isset($field[$dataFieldName])) {
                // 比较字段参数
                $res = CompParamData($field[$dataFieldName], $itemDataField, $dataKey);
                if ($res != "") {
                    $bakFieldName = ($bakFieldName=="") ? " FIRST," : " AFTER `".$bakFieldName."`,";
                    $sql = $sql.$res.$bakFieldName;
                }
                // var_dump($field[$dataFieldName]['COLUMN_NAME'], ' == ', $itemDataField[0]);
            } else {
                // 没有字段名称，创建字段
                $bakFieldName = ($bakFieldName=="") ? " FIRST," : " AFTER `".$bakFieldName."`,";
                $sql = $sql.AddFieldSQL($itemDataField, "ADD").$bakFieldName;
            }
            $bakFieldName = $dataFieldName;
        }
        if($sql != "") {
            $sql = substr($sql,0,-1).";";
        }
        return $sql;
    }
    
    // 比较字段参数与源数据是否相同
    function CompParamData($param, $data, $dataKey) {
        $param['IS_NULLABLE'] = ($param['IS_NULLABLE'] == 'NO') ? "NOT NULL" : "DEFAULT NULL";
        $param['COLUMN_DEFAULT'] = ($param['COLUMN_DEFAULT'] == 'NULL') ? null : "DEFAULT '".$param['COLUMN_DEFAULT']."'";
        $param['EXTRA'] = strtoupper($param['EXTRA']);
        // if($dataKey == $param['COLUMN_NAME'] ^ $param['COLUMN_KEY'] == 'PRI') {
        //     // var_dump('主键不同：',$dataKey , $param['COLUMN_NAME']);
        //     return false;
        // }
        foreach ($data as $dataParamName => $itemDataParam) {
            if($param[$dataParamName] != $itemDataParam) {
                // var_dump('检测不同：',$param[$dataParamName] , $itemDataParam);
                return AddFieldSQL($data, "CHANGE");
            }
        }
        // var_dump(true);
        return "";
    }
    
    function AddFieldSQL($data, $head = "CHANGE") {
        if($head == "CHANGE") {
            $head = $head." `".$data['COLUMN_NAME']."`";
        }
        $sql = $head." `".$data['COLUMN_NAME']."`";
        $sql = $sql." ".$data['COLUMN_TYPE'];
        $sql = $sql." ".$data['IS_NULLABLE'];
        if(isset($data['COLUMN_DEFAULT'])) {
            $sql = $sql." ".$data['COLUMN_DEFAULT'];
        }
        if(isset($data['EXTRA'])) {
            $sql = $sql." ".$data['EXTRA'];
        }
        return $sql;
    }
    
    // 检查管理员权限与密码
    function CheckUserShell($Conn, $config, $username, $password) {
        //检验管理员账号
        $dbName = $config['DB_NAME'];
        $tableName =  $config['DB_PREFIX'] . "user";
        $sql = "select * from `$dbName`.`$tableName` where uid='".$config['ADMIN_UID']."'";
        $UserData = mysqli_fetch_array(mysqli_query($Conn, $sql));
        if ($UserData['username'] != $username || $UserData['password'] != $password) {
            die(ShowAlert('请检查管理员账号或密码是否正确！','权限验证失败'));
        }
    }
    
    // 执行多条数据库命令
    function RunSqlQuery($Conn, $sql, $xxjz, $config) {
        mysqli_select_db($Conn, $config['DB_NAME']);
        if (mysqli_multi_query($Conn, $sql)) {
            $dbFile = fopen("install.tmp", "w");
            fwrite($dbFile, base64_encode(json_encode($config)));
            fclose($dbFile);
            return true;
        } else {
            return false;
        }
    }
    
    // 输出提示信息
    function ShowAlert($Str,$Title="提示") {
        echo '<div id="formwrapper"><fieldset>';
        echo '<legend>'.$Title.'</legend><div style="text-align:center;">';
        echo '<h3>'.$Str.'</h3><br/>';
        switch ($Title) {
            case '升级完成':
            case '安装完成':
            case '无需升级':
                echo '<a href="index.php">跳转到主页</a>';   
                break;
            case '尚未安装':
            case '未知错误':
                echo '<a href="install.php">跳转到安装</a>';  
                break;
            case '无法升级':
                echo '<a href="updata.php">刷新</a>'; 
                break;
            default:
                echo '<a href="updata.php">返回</a>';   
                break;
        }
        echo '</div></fieldset></div>';
    }
    
    // 显示升级表单
    function ShowForm() {
        echo <<<___
    <div id="formwrapper">
        <h3 class="enter"><p>小歆记账Web升级向导</p></h3><br/>
        <form action="updata.php" method="post">   
            <fieldset>
                <legend>升级说明</legend>
                <div>
                    本向导将1.x数据库升级到2.x数据库，升级前请确认一下内容：<br/>
                    &emsp;&emsp;1、在升级前请务必备份好数据库中全部数据，如发生数据丢失将无法恢复。<br/>
                    &emsp;&emsp;2、升级前请先填写管理员账号密码，若没有则无法进行升级。<br/>
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
___;
    }

?>
  </body>
</html>