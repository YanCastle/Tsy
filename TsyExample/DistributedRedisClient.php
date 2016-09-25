<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 8/28/16
 * Time: 12:01 PM
 */
error_reporting(E_ALL);
$APP_PATH = 'Example';
$RUNTIME_PATH = 'Runtime';
define('APP_DEBUG',true);
define('APP_MODE','DistributedRedisClient');
define('DEFAULT_MODULE','Application');//这个版本中必须定义默认模块，其值与APP_PATH的最后一个目录相同
include '../Tsy/Tsy.php';