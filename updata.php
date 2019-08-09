<?php
    $xxjz = include './sql.php';
    $config = include './Application/Common/Conf/config.php';
    
    $config['DB_PREFIX'] = 'test2_';
    
    UpdataDB($xxjz, $config);
    
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
        
        var_dump($sql);
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
    
    // 输出提示信息
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