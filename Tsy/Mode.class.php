<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:56
 */

namespace Tsy;

/**
 * 模式类 基类
 * Class Mode
 * @package Tsy
 */
interface Mode
{
    /**
     * 执行体
     * @return mixed
     */
    function exec();

    /**
     * 调度
     * @return mixed
     */
    function dispatch($data=null);

    /**
     * 启动函数
     * @return mixed
     */
    function start();

    /**
     * 停止继续执行
     * @return mixed
     */
    function stop($Code="0");
    function out($Data=null);
    function in($Data=null);
}