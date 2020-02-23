# 小歆记账WebApp项目(Web服务端)

[V2.x版本](https://github.com/xiaoxinpro/xxjzWeb) | [V1.x版本](https://github.com/xiaoxinpro/xxjzWeb/tree/V1.x) | [基础版本](https://github.com/xiaoxinpro/xxjzWeb/tree/Base) | [微信小程序](https://github.com/xiaoxinpro/xxjzWeChat)

![](https://github.com/xiaoxinpro/xxjzWeb/blob/master/Public/Home/i/logo.png)

## 1、简介

小歆记账WebApp是一个面向移动端的记账工具，此项目为Web服务端；使用ThinkPHP+MySQL作为后台，AmazeUI作为前端。

>GitHub：https://github.com/xiaoxinpro/xxjzWeb

* 项目[基础版本](https://github.com/xiaoxinpro/xxjzWeb/tree/Base)已经完成框架开发，后续版本在此版本框架上升级而来。

* 项目[V1.x版本](https://github.com/xiaoxinpro/xxjzWeb/tree/V1.x)已经完成功能开发，后续若有BUG还会进行升级，功能将不会再增加了。

* 项目[V2.x版本](https://github.com/xiaoxinpro/xxjzWeb)目前正在添加更多功能，数据结构与功能随时变动，稳定性不如以上几个版本。

你可以使用Demo账号登陆体验V2.x版本，也可以自行搭建更加稳定的[V1.x版本](https://github.com/xiaoxinpro/xxjzWeb/releases/tag/V1.0.0)项目。

>Demo  ：http://jz.xiaoxin.pro/xxjzApp/index.php

    Demo账号：demo
    Demo密码：xxgzs.org

## 2、安装使用

>环境要求PHP5.3以上（含PHP7以上版本），MySQl数据库，支持Windows和Linux服务器环境。

### 2.1、安装

部署项目建议选择[Releases](https://github.com/xiaoxinpro/xxjzWeb/releases)版本，master分支属于开发分支，可能存在一些BUG无法正常使用。

如果服务端不是Windows环境，请将根目录下的`Application`文件夹及其子文件设为755权限，否则安装后无法访问页面。

#### 2.1.1、快速安装
部署项目文件后使用浏览器访问/install.php文件，填写数据库配置信息与管理员账号、密码和邮箱。提交后若配置信息无误则自动跳转到登陆页面。

![小歆记账Web安装向导.png](https://github.com/xiaoxinpro/xxjzWeb/blob/master/Public/Home/i/install.png)

点击安装后若配置信息无误则自动跳转到登陆页面，使用刚刚填写的管理员账号和密码就可以登陆使用了。

> 如果你只是日常使用阅读到这里就可以了，如果你想进一步完善或优化请往下看。

#### 2.1.2、手动安装（不推荐）
部署项目文件到网站目录

将根目录下的 `xxjz.sql` 文件导入到数据库中，

修改 `/Application/Common/Conf/config.php` 配置文件，填写数据库和邮箱相关信息。

使用默认账号登陆前台主页即可。

```
默认账号：admin
默认密码：admin888
```

### 2.2、升级

若已经安装过1.x版本，可按照以下说明升级到2.x版本。

> 在升级前请务必备份好数据库与项目文件，以防升级失败造成数据丢失。

下载本项目源码，将 `/Application/Home` 文件夹复制并覆盖到部署目录下的 `/Application` 文件夹中，将 `updata_2.0.php` 复制到部署根目录下，删除部署目录下的 `install.tmp` 文件（没用请忽略）。

使用浏览器访问`/updata_2.0.php`文件，阅读升级说明并填写管理员账号和密码。

![](https://github.com/xiaoxinpro/xxjzWeb/blob/master/Public/Home/i/updata.png)

点击 `升级` 按钮完成升级，升级结束后请务必删除`/updata_2.0.php`文件，防止二次升级破坏数据库文件。

### 2.3、使用优化

#### 2.3.1、关闭调试模式

该项目目前处于开发阶段，默认开启了调试模式。在实际使用中，为了提高运行效率，建议手动关闭调试模式。
打开根目录下的/index.php文件，将第**18**行改为`define('APP_DEBUG',false);`

#### 2.3.2、修改配置文件
配置文件只在/Application/Common/Conf/config.php文件中存放，在该文件中你可以修改数据库、邮箱系统、用户登录限制、列表分页等配置，配置文件注释详细这里不再展开赘述。

#### 2.3.3、重新安装数据库
为了防止重复安装破坏数据库，项目在安装完成后关闭了安装入口。可以手动删除项目根目录下的`_install.tmp`文件，再按照[2.1、安装](#21安装)流程安装即可。

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
      │  │  ├─Conf                           功能配置目录
      │  │  │  ├─config.php                  功能配置文件
      │  │  │  └─index.html                  index文件
      │  │  ├─Controller                     控制器目录
      │  │  │  ├─AddController.class.php     记账控制器文件
      │  │  │  ├─ApiController.class.php     API控制器文件
      │  │  │  ├─BaseController.class.php    公共控制器文件
      │  │  │  ├─ChartController.class.php   图表控制器文件
      │  │  │  ├─ClassController.class.php   分类控制器文件
      │  │  │  ├─EditController.class.php    编辑账目控制器文件
      │  │  │  ├─FindController.class.php    搜索账目控制器文件
      │  │  │  ├─FundsController.class.php   资金账户控制器文件
      │  │  │  ├─IndexController.class.php   主页控制器文件
      │  │  │  ├─ListController.class.php    列表控制器文件
      │  │  │  ├─LoginController.class.php   登录控制器文件
      │  │  │  ├─PushController.class.php    信息推送控制器文件
      │  │  │  ├─UserController.class.php    用户控制器文件
      │  │  │  └─index.html                  index文件
      │  │  ├─Model                          模型目录
      │  │  │  ├─UserPushModel.class.php     信息推送模型文件
      │  │  │  └─index.html                  index文件
      │  │  └─View                           视图目录
      │  │     ├─Add                         记账视图目录
      │  │     ├─Chart                       图表视图目录
      │  │     ├─Class                       分类视图目录
      │  │     ├─Edit                        编辑视图目录
      │  │     ├─Find                        搜索账目视图目录
      │  │     ├─Funds                       资金账户视图目录
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
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|acid|int(11) unsigned |账目ID|
|acmoney|double(9,2) unsigned|金额|
|acclassid|int(11)|分类ID|
|actime|int(11)|时间戳|
|acremark|varchar(64)|备注|
|jiid|int(11)|用户ID|
|zhifu|int(1)|收入1/支出2|
|fid|int(11)[-1]|资金账户ID|

### 4.2、分类表 xxjz_account_class
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|classid|int(11) |分类ID|
|classname|varchar(24)|分类名称|
|classtype|int(1)|收入1/支出2|
|ufid|int(11)|所属用户ID|

### 4.3、分类表 xxjz_account_funds
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|fundsid|int(11)|资金账户ID|
|fundsname|varchar(24)|资金账户名称|
|uid|int(11)|所属用户ID|

### 4.4、图片表 xxjz_account_image
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|id|int(11) 自动增量|图片ID|
|uid|int(11)|用户ID|
|acid|int(11)|记账ID|
|name|varchar(32)|图片名称|
|type|varchar(32)|图片类型|
|size|int(11)|图片大小（字节）|
|ext|varchar(8)|图片扩展名|
|md5|varchar(32)|图片Hash值|
|savepath|varchar(32)|图片文件路径|
|savename|varchar(32)|图片文件名称|
|time|int(11)|上传时间|

### 4.5、用户表 xxjz_user
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|uid|int(11) 自动增量|用户ID|
|username|varchar(24)|账号/用户名|
|password|varchar(32)|密码|
|email|varchar(255)|邮箱|
|utime|int(11)|注册时间戳|

### 4.6、用户表 xxjz_user_config
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|cid|int(11) 自动增量|配置ID|
|uid|int(11)|用户ID|
|config_name|varchar(32)|配置名称|
|config_key|varchar(32)|配置键|
|config_value|varchar(32)|配置值|
|time|int(11)|创建时间戳|

### 4.7、用户登陆表 xxjz_user_login
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|lid|int(11) 自动增量|登陆ID|
|uid|int(11)|用户ID|
|login_name|varchar(32)|登陆平台名称|
|login_id|varchar(32)|平台openid|
|login_key|varchar(32)|平台session_key|
|login_token|varchar(32)|平台unionid|

### 4.8、信息推送表 xxjz_user_push
| 字段 | 类型 | 备注 |
|:--------:|--------|--------|
|pid|int(11) 自动增量|推送ID|
|uid|int(11)|用户ID|
|push_name|varchar(32)|推送平台名称|
|push_id|varchar(64)|推送平台formid|
|push_mark|varchar(32)|推送源备注|
|time|int(11)|推送源时间戳|

## 5、Bug 反馈及需求提交

Bug 反馈及需求提交请使用GitHub中的[Issues](https://github.com/xiaoxinpro/xxjzWeb/issues)


## 6、参考

* [ThinkPHP (Apache2 License)](https://github.com/top-think/thinkphp)
* [Amaze UI (MIT License)](https://github.com/amazeui/amazeui)
* [Font Awesome(SIL OFL 1.1 License)](https://github.com/FortAwesome/Font-Awesome)

可能会有部分项目遗漏，后续会不断整理更新。

## 7、捐赠

如果您觉得小歆记账对你有帮助，欢迎给予我们一定的捐助来维持项目的长期发展。

### 支付宝扫码捐赠

![支付宝扫码捐赠](https://github.com/xiaoxinpro/xxjzWeb/blob/master/Public/Home/i/alipay.png)

### 微信扫描捐赠

![微信扫描捐赠](https://github.com/xiaoxinpro/xxjzWeb/blob/master/Public/Home/i/wechat.png)


