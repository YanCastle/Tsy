<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/27
 * Time: 21:25
 */

namespace Tsy\Library;

/**
 * 多个SQL异步执行，在所有SQL执行完了后返回给主线程
 * 支持主线程返回，也要支持异步线程操作，
 * 如一个复杂操作，分成多线程后执行，在所有SQL执行完成了后返回到主线程
 * 需要多线程执行函数支持
 * Class MultipleSql
 * @package Tsy\Library
 */
class MultipleSql
{
    protected $SQLs=[];
    function add($SQL,$Key='',$Begin=true){
        $Key=md5($SQL);
        $this->SQLs[$Key]=$SQL;
    }
    function getResult(){}
    protected function async($SQL,$Key){
//        task(\Tsy\Library\MultipleSql::exec());
    }
    public static function exec(){

    }
}