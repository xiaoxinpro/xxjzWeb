# 小歆记账WebApp项目(Web服务端)

![](http://upload-images.jianshu.io/upload_images/1568014-caeefa6ab53be35b.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

## 1、简介


小歆记账WebApp是一个面向移动端的记账工具，此项目为Web服务端；使用ThinkPHP+MySQL作为后台，AmazeUI作为前端。

>GitHub：https://github.com/xiaoxinpro/xxjzWeb

>Demo  ：http://jz.xiaoxin.pro/xxjzApp/index.php

目前项目已基本完成，你可以使用Demo账号登陆体验，也可以自行搭建项目。

    Demo账号：demo
    Demo密码：xxgzs.org

## 2、安装使用


>环境要求PHP5.3以上，MySQl数据库，支持Windows和Linux服务器环境。

### 2.1、快速安装
部署项目文件后使用浏览器访问/install.php文件，填写数据库配置信息与管理员账号、密码和邮箱。提交后若配置信息无误则自动跳转到登陆页面。

![小歆记账Web安装向导.png](http://upload-images.jianshu.io/upload_images/1568014-b9284b83fef9b783.png?imageMogr2/auto-orient/strip%7CimageView2/2/h/576)

点击安装后若配置信息无误则自动跳转到登陆页面，使用刚刚填写的管理员账号和密码就可以登陆使用了。

> 如果你只是日常使用阅读到这里就可以了，如果你想进一步完善或优化请往下看。

### 2.2、使用优化

#### 2.2.1、关闭调试模式

该项目目前处于开发阶段，默认开启了调试模式。在实际使用中，为了提高运行效率，建议手动关闭调试模式。
打开根目录下的/index.php文件，将第**18**行改为`define('APP_DEBUG',false);`

#### 2.2.2、修改配置文件
配置文件只在/Application/Common/Conf/config.php文件中存放，在该文件中你可以修改数据库、邮箱系统、用户登录限制、列表分页等配置，配置文件注释详细这里不再展开赘述。

#### 2.2.3、重新安装数据库
为了防止重复安装破坏数据库，项目在安装完成后关闭了安装入口。可以手动删除项目根目录下的`_install.tmp`文件，再按照[2.1、快速安装](#21快速安装)流程安装即可。

* 重复安装前请注意原数据库的备份。
* 安装向导不会进行覆盖安装。


## 3、目录结构


    xxjzWeb  WEB部署目录（或者子目录）
      ├─Application                          应用目录
      │  ├─Common                            配置文件目录
      │  │  ├─Common                         未使用
      │  │  ├─Conf                           配置文件目录
      │  │  │  ├─config.php                  配置文件
      │  │  │  └─index.html                  index文件
      │  │  └─index.html                     index文件
      │  ├─Home                              模块目录
      │  │  ├─Common                         公共函数目录
      │  │  │  ├─function.php                全局函数文件
      │  │  │  └─index.html                  index文件
      │  │  ├─Conf                           未使用
      │  │  ├─Controller                     控制器目录
      │  │  │  ├─AddController.class.php     记账控制器文件
      │  │  │  ├─ApiController.class.php     API控制器文件
      │  │  │  ├─BaseController.class.php    公共控制器文件
      │  │  │  ├─ChartController.class.php   图表控制器文件
      │  │  │  ├─ClassController.class.php   分类控制器文件
      │  │  │  ├─EditController.class.php    编辑账目控制器文件
      │  │  │  ├─FindController.class.php    搜索账目控制器文件
      │  │  │  ├─IndexController.class.php   主页控制器文件
      │  │  │  ├─ListController.class.php    列表控制器文件
      │  │  │  ├─LoginController.class.php   登录控制器文件
      │  │  │  ├─UserController.class.php    用户控制器文件
      │  │  │  └─index.html                  index文件
      │  │  ├─Model                          未使用
      │  │  └─View                           视图目录
      │  │     ├─Add                         记账视图目录
      │  │     ├─Chart                       图表视图目录
      │  │     ├─Class                       分类视图目录
      │  │     ├─Edit                        编辑视图目录
      │  │     ├─Find                        搜索账目视图目录
      │  │     ├─Index                       主页视图目录
      │  │     ├─List                        列表视图目录
      │  │     ├─Login                       登录视图目录
      │  │     ├─Public                      公共视图目录
      │  │     ├─User                        用户视图目录
      │  │     └─index.html                  index文件
      │  └─index.html                        index文件
      ├─Public                               资源文件目录
      │  └─Home                              Home资源目录
      │     ├─css                            CSS目录
      │     ├─fonts                          字体目录
      │     ├─i                              图片目录
      │     └─js                             JavaScript目录
      ├─ThinkPHP                             ThinkPHP目录
      ├─index.php                            入口文件
      ├─install.php                          安装文件
      └─README.md                            README文件


## 4、数据结构

### 4.1、账目表 xxjz_account
| 字段 | 类型 | 注释 |
|:--------:|--------|--------|
|acid|int(11) unsigned |账目ID|
|acmoney|double(9,2) unsigned|金额|
|acclassid|int(8)|分类ID|
|actime|int(11)|时间戳|
|acremark|varchar(50)|备注|
|jiid|int(11)|用户ID|
|zhifu|int(1)|收入1/支出2|

### 4.2、分类表 xxjz_account_class
| 字段 | 类型 | 注释 |
|:--------:|--------|--------|
|classid|int(8) |分类ID|
|classname|varchar(24)|分类名称|
|classtype|int(1)|收入1/支出2|
|ufid|int(11)|所属用户ID|

### 4.3、用户表 xxjz_user
| 字段 | 类型 | 注释 |
|:--------:|--------|--------|
|uid|int(11) 自动增量|用户ID|
|username|varchar(24)|账号/用户名|
|password|varchar(32)|密码|
|email|varchar(255)|邮箱|
|utime|int(11)|注册时间戳|

## 5、Bug 反馈及需求提交

Bug 反馈及需求提交请使用GitHub中的[Issues](https://github.com/xiaoxinpro/xxjzWeb/issues)


## 6、参考

* [ThinkPHP (Apache2 License)](https://github.com/top-think/thinkphp)
* [Amaze UI (MIT License)](https://github.com/amazeui/amazeui)
* [Font Awesome(SIL OFL 1.1 License)](https://github.com/FortAwesome/Font-Awesome)

可能会有部分项目遗漏，后续会不断整理更新。
