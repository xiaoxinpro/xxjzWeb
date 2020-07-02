<?php
return array(
    //'配置项'=>'配置值'
    'APP_DEMO_USERNAME' => 'demo',
    'APP_IOS_URL' => 'http://downloadpkg.apicloud.com/app/download?path=http://7xwwr4.com1.z0.glb.clouddn.com/3148046adbdb7b3ae9736fb35e5b26ae.ipa',
    'APP_ANDROID_URL' => 'http://downloadpkg.apicloud.com/app/download?path=http://7xwwr4.com1.z0.glb.clouddn.com/dcace9ca9ecf952a679c88e04e7cf3bf_d',

    //金额格式设置
    'MONEY_FORMAT_DECIMALS' => 2,    //金额保留小数位数（0-2）
    'MONEY_FORMAT_POINT' => '.',     //金额小数点符号（.）
    'MONEY_FORMAT_THOUSANDS' => ',', //金额千分符（,）

    //记账数据限制
    'MAX_MONEY_VALUE' => 999999999.99,  //单笔记录金额最大值（不超过999999999.99）
    'MAX_CLASS_NAME' => 24,            //分类名最大长度（不超过255）
    'MAX_FUNDS_NAME' => 24,            //资金账户名最大长度（不超过255）
    'MAX_MARK_VALUE' => 64,            //备注最大长度（不超过255）

    //图片上传限制
    'IMAGE_SIZE'    => 3*1024*1024,     //图片大小限制
    'IMAGE_COUNT'   => 9,               //图片数量限制
    'IMAGE_EXT'     => array('jpg', 'gif', 'png', 'jpeg'), //允许图片格式
    'IMAGE_ROOT_PATH' => '/Uploads/',  //图片根目录路径
    'IMAGE_CACHE_URL' => '',            //图片缓存URL,必须以`http(s)://`开头,否则无效。
);