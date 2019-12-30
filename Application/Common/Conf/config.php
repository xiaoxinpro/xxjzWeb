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
    'DB_CHARSET'=> 'utf8mb4',   // 字符集
    'DB_DEBUG'  =>  TRUE,    // 数据库调试模式 开启后可以记录SQL日志
    
    //系统配置
    'HTML_CACHE_ON'         => false,       // 关闭静态缓存
    'URL_CASE_INSENSITIVE'  => false,       // 区分大小写(必须)
    
    //应用配置信息
    'USER_LOGIN_TIMES'  => 10,              // 用户登录次
    'PAGE_SIZE'         => 15,              // 表格分页数
    'MAIL_HOST'         => '',              // 邮箱SMTP主机
    'MAIL_SECURE'       => 'ssl',           // 邮箱安全认证（none、ssl、tls）
    'MAIL_PORT'         => '465',           // 邮箱SMTP端口号（默认为25，SSL协议为465或994）
    'MAIL_USERNAME'     => '',              // 邮箱用户名
    'MAIL_PASSWORD'     => '',              // 邮箱密码
    'MAIL_FROM'         => '',              // 发件人邮箱
    'MAIL_FROMNAME'     => '小歆记账',      // 发件人名字

    //微信小程序配置
    'WX_ENABLE'         => false,           //使能微信小程序功能
    'WX_OPENID'         => 'openid',        //微信小程序唯一标识
    'WX_SECRET'         => 'secret',        //微信小程序的 app secret

    //自定义配置
    'XXJZ_TITLE'        => '小歆记账App',   //网站名称
    'XXJZ_KEYWORDS'     => '',              //网站关键字
    'XXJZ_DESCRIPTION'  => '',              //网站描述
    'XXJZ_WELCOME'      => '欢迎使用！',    //网站欢迎语

    //管理员配置
    'ADMIN_UID'         => 1,               //管理员UID
);
