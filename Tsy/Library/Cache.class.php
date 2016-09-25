<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 16:54
 */

namespace Tsy\Library;


abstract class Cache
{
    /**
     * 操作句柄
     * @var string
     * @access protected
     */
    protected $handler;

    /**
     * 缓存连接参数
     * @var integer
     * @access protected
     */
    protected $options = array();
    function __construct($Config=[])
    {
    }

    function read($key){}
    function write($key,$value){}
    function config(array $config=[]){}
}