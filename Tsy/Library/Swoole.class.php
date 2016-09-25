<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:32
 */

namespace Tsy\Library;


abstract class Swoole
{
    function code($str){
        return $str;
    }
    function uncode($str){
        return $str;
    }

    /**
     * 握手
     * @param $str
     * @return bool
     */
    function handshake($str){
//        如果返回字符串则会被发送给当前接口并忽略这次请求
//        返回false则继续请求
//        握手会在uncode之前
        return false;
    }
}