<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/8/16
 * Time: 12:51 PM
 */

namespace Tsy\Library\Define;

/**
 * PIPE 通信内容定义
 * Class Pipe
 * @package Tsy\Library\Define
 */
class Pipe
{
    static public $TASK='TASK';
    static public $EXEC='EXEC';
    static public $CALLBACK='CALLBACK';
    static public $CONTROLLER='CONTROLLER';
//    static public $TASK='TASK';
    public $c='';
    public $d=[];
    public $id=null;
    function __construct(string $c,$d)
    {
        $this->id=$GLOBALS['_SWOOLE']->worker_id;
        $this->c=$c;
        $this->d=$d;
    }
}