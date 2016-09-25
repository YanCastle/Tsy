<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/27
 * Time: 21:41
 */

namespace Tsy\Library\Cache;


interface CacheInterface
{
    //读取缓存
    public function get($name);
    //写入缓存
    public function set($name, $value, $expire = null) ;
    //删除缓存
    public function rm($name);
    //清除缓存
    public function clear();
    public function queue($name,$value=null);
    function setInc($key,$value=1);
    function setDec($key,$value=1);
}