
##小歆记账WebApp项目(Web服务端)

###1、简介
小歆记账WebApp是一个跨平台的记账工具，此项目为Web服务端；使用ThinkPHP+MySQL作为后台，AmazeUI作为前端。

>项目地址为：http://jz.xxgzs.org/xxjzApp/index.php

目前项目还在建设中，暂时无法提供演示，但可以自行搭建。

###2、安装使用

####2.1、填写配置信息
项目配置文件在/Application/Common/Conf/config.php中，在部署源文件前先修改该文件，应正确填写config.php中的数据库信息与服务邮箱。

####2.2、部署源文件
将项目中的文件上传到网站根目录。

####2.3、安装数据库
部署源文件后使用浏览器访问/install.php文件，填写数据库配置信息与管理员账号、密码和邮箱。提交后若配置信息无误则自动跳转到登陆页面。

####2.4、关闭调试模式
该项目目前处于开发阶段，默认开启了调试模式。在实际使用中，为了提高运行效率，建议手动关闭调试模式。
打开根目录下的/index.php文件，将第**18**行改为
`define('APP_DEBUG',false);`

###3、目录结构

    xxjzWeb  WEB部署目录（或者子目录）
      ├─Application         应用目录
      │  ├─Common           配置文件目录
      │  ├─Home             模块目录
      │  ├─index.html       index文件
      ├─Public              静态文件目录
      │  ├─Home             Home静态目录
      │  │  ├─css           CSS目录
      │  │  ├─fonts         字体目录
      │  │  ├─i             图片目录
      │  │  ├─js            JavaScript目录
      ├─ThinkPHP            ThinkPHP目录
      ├─index.php           入口文件
      ├─install.php         安装文件
      ├─README.md           README文件

###4、数据结构