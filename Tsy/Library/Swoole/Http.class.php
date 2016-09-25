<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:36
 */

namespace Tsy\Library\Swoole;


use Tsy\Library\Swoole;

class Http extends Swoole
{
    function code($str){
        http_header(['Content-Length:'=>strlen($str)]);
        $GLOBALS['_close']=true;
        $_REQUEST=[];
        $_GET=[];$_POST=[];$_FILES=[];
        return http_header(null).$str;
    }
    function uncode($str)
    {
        $HTTP = http_parse($str);
        if(is_array($HTTP)){
            if(isset($HTTP['Header'])){
                $_SERVER = array_merge($_SERVER,$HTTP['Header']);
            }
            if(isset($HTTP['Method'])){
                $_SERVER['REQUEST_METHOD']=$HTTP['Method'];
            }
            if(isset($HTTP['POST'])){
                $_POST = array_merge($_POST,$HTTP['POST']);
            }
            if(isset($HTTP['GET'])){
                $_GET = array_merge($_GET,$HTTP['GET']);
            }
            if(isset($HTTP['FILES'])){
                $_FILES = array_merge($_FILES,$HTTP['FILES']);
            }
            if(isset($HTTP['SERVER'])){
                $_SERVER=array_merge($_SERVER,$HTTP['SERVER']);
            }
            $_REQUEST = array_merge($_GET,$_POST);
        }
        return $_REQUEST;
    }
}