<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/12/16
 * Time: 10:20 PM
 */

namespace Tsy\Library\Cache\Driver;


use Tsy\Library\Cache;
use Tsy\Library\IFace\SwooleCallbackInterface;

class SwooleTable extends Cache implements SwooleCallbackInterface
{
    public static $TYPE_INT='INT';
    public static $TYPE_FLOAT='float';
    public static $TYPE_STRING='string';
    function __construct(array $Config)
    {
        $GLOBALS['_SWOOLE_TABLES']=[];
        foreach ($Config as $key=>$tableConfig){
            $Key = isset($tableConfig['Name'])?$tableConfig['Name']:$key;
            if(isset($tableConfig['Column'])&&is_array($tableConfig['Column'])&&$tableConfig['Column']){
                $table = new \swoole_table(isset($tableConfig['Size'])?$tableConfig['Size']:32768);
                foreach ($tableConfig['Column'] as $column){
                    $table->column($column['Name'],$column['Type'],$column['Size']);
                }
                $GLOBALS['_SWOOLE_TABLES'][$Key]=$table;
            }
        }
    }

    function get($name){}
    function set($name,$value,$expire){}
    function init(){}
    function clear(){

    }
}