<?php
return array(
	//'配置项'=>'配置值'
	
    //数据库配置信息
    'DB_TYPE'   => 'mysql',  // 数据库类型
    'DB_HOST'   => '',       // 服务器地址
    'DB_NAME'   => '',       // 数据库名
    'DB_USER'   => '',       // 用户名
    'DB_PWD'    => '',       // 密码
    'DB_PORT'   => 3306,     // 端口
    'DB_PARAMS' =>  array(), // 数据库连接参数
    'DB_PREFIX' => 'xxjz_',  // 数据库表前缀 
    'DB_CHARSET'=> 'utf8',   // 字符集
    'DB_DEBUG'  =>  TRUE,    // 数据库调试模式 开启后可以记录SQL日志
    
    //系统配置
    'HTML_CACHE_ON'         => false,       // 关闭静态缓存
    'URL_CASE_INSENSITIVE'  => false,       // 区分大小写(必须)
    
    //应用配置信息
    'USER_LOGIN_TIMES'  => 10,              // 用户登录次
    'PAGE_SIZE'         => 15,              // 表格分页数
    'MAIL_HOST'         => '',              // 邮箱SMTP主机
    'MAIL_USERNAME'     => '',              // 邮箱用户名
    'MAIL_PASSWORD'     => '',              // 邮箱密码
    'MAIL_FROM'         => '',              // 发件人邮箱
    'MAIL_FROMNAME'     => '小歆记账',      // 发件人名字
    
);
