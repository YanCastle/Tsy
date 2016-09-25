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
 * Date: 7/1/16
 * Time: 10:08 AM
 */

namespace Tsy\Library;


use Tsy\Library\IFace\SwooleProcessInterface;

class DbPool implements SwooleProcessInterface
{
    protected $pools=[];
    protected $reads=[];
    protected $writes=[];
    protected $waits=[];
    protected $size=20;
    function __construct()
    {
    }
    function doQuery(){}

    /**
     * 进程启动时触发函数
     * @param \swoole_process $process
     * @param \swoole_server $server
     */
    function start(\swoole_process $process,\swoole_server $server){
        //进程启动时触发函数
//        链接数据库，创建连接池
        
    }

    /**
     * 进程收到消息时触发
     * @param \swoole_process $process
     * @param \swoole_server $server
     * @param $data
     */
    function pipe(\swoole_process $process,\swoole_server $server,$data){

    }
}