<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 16-5-7
 * Time: 下午12:39
 */

namespace Tsy\Library\IFace;


interface SwooleProcessInterface
{
    function start(\swoole_process $process,\swoole_server $server);
    function pipe(\swoole_process $process,\swoole_server $server,$data);
}