<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/14
 * Time: 10:57
 */

namespace Tsy\Library\Cache;


abstract class Cache
{
    public static $QUEUE_ALL="\x00";
    public static $setInc="\x01";
    public static $setDec="\x02";
    public $handler;
    public $options=[];
    //读取缓存
    public function get($name){}
    //写入缓存
    public function set($name, $value, $expire = null) {}
    //删除缓存
    public function rm($name){}
    //清除缓存
    public function clear(){}

    public static function getCacheHandler($type=''){
        static $_map =[];
        if(empty($type))  $type = C('DATA_CACHE_TYPE');
        if(defined('PROCESS_ID')){
            $key = PROCESS_ID.$type;
        }else{
            $key = $type;
        }
        if(!isset($_map[$key])){
            $class  =   strpos($type,'\\')? $type : 'Tsy\\Library\\Cache\\Driver\\'.ucwords(strtolower($type));
            if(class_exists($class)){
                $cache = new $class([]);
                $_map[$key]=$cache;
            }
            else{
                L($type.':缓存驱动类不存在',LOG_ERR);
                return false;
            }
        }
        if(isset($_map[$key])){
            $cache = $_map[$key];
        }
        return $cache;
    }
    /**
     * 队列方法
     * @param $name
     * @param string $value
     * @return array|bool|mixed
     */
    public function queue($name,$value=''){
        $Queue = $this->get($name);
        if(!$Queue){
            $Queue=new \SplQueue();
        }
        switch ($value){
            case Cache::$QUEUE_ALL:
                $Arr=[];
                while (!$Queue->isEmpty()){
                    $Arr[]=$Queue->shift();
                }
                return $Arr;
                break;
            case null:
                $this->rm($name);
                break;
            case '':
                return $Queue->shift();
                break;
            default:
                $Queue->push($value);
                break;
        }
        return $this->set($name,$value);
    }

    /**
     * 递增
     * @param $key
     * @param int $value
     * @return int|mixed
     */
    function setInc($key,$value=1){
        $V = $this->get($key);
        if(!is_numeric($V)){
            $V=0;
        }
        $V+=$value;
        $this->set($key,$V);
        return $V;
    }

    /**
     * 递减
     * @param $key
     * @param int $value
     * @return int|mixed
     */
    function setDec($key,$value=1){
        $V = $this->get($key);
        if(!is_numeric($V)){
            $V=0;
        }
        $V-=$value;
        $this->set($key,$V);
        return $V;
    }
}