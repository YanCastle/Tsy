<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 16-7-20
 * Time: 上午9:48
 */

namespace Tsy\Library\Aop;


use Tsy\Library\IFace\Aop;

/**
 * 同步Aop
 * Class SyncAop
 * @package Tsy\Library\Aop
 */
class SyncAop implements Aop
{
    /**
     * @var bool $Async 是否异步，异步模式下禁止使用数组配置
     */
    public $Async=false;
    /**
     * @var string|array $cmd 回调函数，数组或字符串，异步模式下禁止使用数组配置
     */
    public $cmd;
    /**
     * @var string $exec 可执行php代码，用于进行自定义处理。允许从此处获取环境变量信息
     */
    public $exec;
    public $when;
    public $order=0;
    function __construct($name,$cmd,$when,$order=0)
    {
        $this->name=$name;
        $this->cmd=$cmd;
        $this->when=$when;
        $this->order=$order;
    }
}