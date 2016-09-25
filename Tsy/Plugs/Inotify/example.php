<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/24/16
 * Time: 10:57 PM
 */
include 'Inotify.class.php';
$infotify = new Inotify();
$infotify->watch('.');
//$infotify->watch('/home/castle');

$infotify->start(function($path,$mask){
//    $path 文件路径
//    $mask 触发类型
});