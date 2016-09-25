#碳素云基于Swoole的服务端框架
##功能
1. 自动Session管理，通过集成SessionHandler来实现Session管理或通过session函数扩展实现
2. 集成Model模型管理，移植TP的Model实现
3. 自动缓存，基于Redis做缓存

环境要求：
1/Linux
2/php5.5+

扩展要求
swoole
redis
msgpack
json
fileinfo
pdo
pdo-mysql
mysqlnd
mysqli

**注意事项**
需要区分缓存级别，一个是应用CLI应用模式下的临时缓存，这部分缓存需要在每次启动服务时清空
一种是持久缓存，启动应用不需要清空，如Session

需要配置哪个接口接入的数据允许访问哪些模块的哪些类

IP限定功能，只允许哪个IP范围的客户端连接

cache方法如果要缓存临时缓存则用tmp_开头，清楚临时缓存的方法是cache('[cleartmp]')

LOG服务，使用TCP或者UDP协议，在引入框架时通过Config或者define定义日志服务地址。所有使用L方法输出的日志全部发送到这个服务器上。

TODO MySql连接池技术

自动构建分为三部分，
1、构建PDM构建目录信息等

PDM标记:
1:





#使用手册

目录
====

[一、环境依赖 1]
(#__RefHeading__605_584730065)

[Linux]
(#__RefHeading__607_584730065)[
下
](#__RefHeading__607_584730065)

[Windows]
(#__RefHeading__609_584730065)[
下
](#__RefHeading__609_584730065)

[二、项目创建 2]
(#__RefHeading__611_584730065)

[1]
(#__RefHeading__613_584730065)[
、创建启动文件，引入
SwooleFramework]
(#__RefHeading__613_584730065)[
框架
](#__RefHeading__613_584730065)

[2]
(#__RefHeading__615_584730065)[
、项目结构
](#__RefHeading__615_584730065)

[3]
(#__RefHeading__617_584730065)[
、swoole]
(#__RefHeading__617_584730065)[
模式服务器配置
](#__RefHeading__617_584730065)

[LISTEN //]
(#__RefHeading__619_584730065)[
监听配置
](#__RefHeading__619_584730065)

[CONF //]
(#__RefHeading__621_584730065)[
服务器配置
](#__RefHeading__621_584730065)

[三、运行程序 ]
(#__RefHeading__623_584730065)

[四、函数列表 ]
(#__RefHeading__625_584730065)


---

一、环境依赖
============

Linux下
-------

-   PHP5.5.0以上版本

-   Swoole扩展

-   PHPRedis

Swoole安装：
[http://wiki.swoole.com/wiki/pa
ge/6.html]
(http://wiki.swoole.com/wiki/pa
ge/6.html)

Windows下
---------

-   安装cygwin，并安装gcc、make
、autoconf、php 4个包

-   安装swoole、phpredis


---
二、项目创建
============

1、创建启动文件，引入
SocketFramework框架
-------------------------------
---------

Start.php

```php
error_reporting(E_ALL); //设置
PHP 的报错级别
$APP_PATH = 'Example';  //代码
路径
$RUNTIME_PATH = 'Runtime';  //
缓存
define('APP_MODE','SWOOLE');
//模式
define
('DEFAULT_MODULE','Application'
);//这个版本中必须定义默认模块
，其值与APP_PATH的最后一个目录
相同
include '../
SocketFramework/Tsy/Tsy.php';
//引入SocketFramework

```

2、项目结构
-----------

    ServerDemo

        Start.php //入口文件

        |Demo

                |Common //通用
配置

                        |Config


Swoole.php //swoole模式服务器配
置


http.php //http模式服务器配置

                        |
Functions


function.php //共有方法

                |User

                        |Config


config.php //模块配置：数据库连
接信息、自定义常量等

                        |
Controller


UserController.class.php //控制
器

                        |Model


UserModel.class.php //模型

                        |Object


UserObj.class.php //数据对象

3、swoole模式服务器配置
-----------------------


在Swoole.php文件中进行配置，配
置信息为监听配置和服务器运行设置
。



### LISTEN //监听配置

#### HOST

允许访问的ip，设置为0.0.0.0为全
ip允许访问

#### PORT

设置服务器暴露端口

#### TYPE

监听类型

#### DISPATCH

路由设置，设置访问参数的解析方式

#### OUT

输出参数格式设置

### CONF //服务器配置

详见swoole官方文档

#### Daemonize

守护进程化。设置daemonize =\>
1时，程序将转入后台作为守护进程
运行。长时间运行的服务器端程序必
须启用此项。

如果不启用守护进程，当ssh终端退
出后，程序将被终止运行。

-   启用守护进程后，标准输入和
输出会被重定向到 log\_file

-   如果未设置log\_file，将重定
向到
    /dev/null，所有打印屏幕的信
息都会被丢弃




#### task\_worker\_num

配置task进程的数量，配置此参数
后将会启用task功能。所以swoole
\_server务必要注册
onTask/onFinish2个事件回调函数
。如果没有注册，服务器程序将无法
启动。

task进程是同步阻塞的，配置方式
与worker同步模式一致。




#### dispatch\_mode

数据包分发策略。可以选择3种类型
，默认为2

-   1，轮循模式，收到会轮循分配
给每一个worker进程

-   2，固定模式，根据连接的文件
描述符分配worker。这样可以保证
同一个连接发来的数据只会被同一个
worker处理

-   3，抢占模式，主进程会根据
Worker的忙闲状态选择投递，只会
投递给处于闲置状态的Worker

-   4，IP分配，根据客户端IP进行
取模hash，分配给一个固定的
worker进程。可以保证同一个来源
IP的连接数据总会被分配到同一个
worker进程。算法为 ip2long
(ClientIP)
    % worker\_num

-   5，UID分配，需要用户代码中
调用\$serv-\>bind()将一个连接绑
定1个uid。然后swoole根据UID的值
分配到不同的worker进程。算法为
UID
    % worker\_num，如果需要使用
字符串作为UID，可以使用crc32
(UID\_STRING)

dispatch\_mode 4,5两种模式，在
1.7.8以上版本可用\
dispatch\_mode=1/3时，底层会屏
蔽onConnect/onClose事件，原因是
这2种模式下无法保证
onConnect/onClose/onReceive的顺
序\
非请求响应式的服务器程序，请不要
使用模式1或3




#### worker\_num

设置启动的worker进程数。

-   业务代码是全异步非阻塞的，
这里设置为CPU的1-4倍最合理

-   业务代码为同步阻塞，需要根
据请求响应时间和系统负载来调整

比如1个请求耗时100ms，要提供
1000QPS的处理能力，那必须配置
100个进程或更多。但开的进程越多
，占用的内存就会大大增加，而且进
程间切换的开销就会越来越大。所以
这里适当即可。不要配置过大。

-   每个进程占用40M内存，那100
个进程就需要占用4G内存




三、运行程序
============
```cmd

cd /home/ServerDemo

php start.php
```

四、函数列表
============







五、更新记录
===========
[2016-04-25]

^cache函数中的标签操作中的-针对数组的算法使用数组求差集而不是搜索后unset

[2016-04-24]

+cache函数支持[+][-][-A][+A][-S][-A]操作符，+-表示添加或删除，A表示按数组操作，S表示按字符串操作，
示例：
```php
cache('[+A]ke','ss');
```
[2016-04-22]
+若在启动脚本中定义APP_BUILD值为true，则会扫描项目目录下的模块并链接模块配置中的数据库生成Controller和Object以及Model文件和相关缓存文件