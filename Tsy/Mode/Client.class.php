<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:58
 */

namespace Tsy\Mode;


use Tsy\Mode;

/**
 * Client模式
 * @package Tsy\Mode
 */
class Client implements Mode
{
    /**
     * 执行体
     * @return mixed
     */
    function exec(){}

    /**
     * 调度
     * @return mixed
     */
    function dispatch($data=null){}

    /**
     * 启动函数
     * @return mixed
     */
    function start(){
//        读取配置文件、启动服务器
        $ClientConfig = C('CLIENT');
    }
    function stop($Code=0)
    {
        // TODO: Implement stop() method.
    }
    function out($Data=null){

    }
    function in($Data=null){

    }
}