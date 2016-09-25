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
 * Date: 6/10/16
 * Time: 11:38 AM
 */

namespace Tsy\Plugs\Async;


use Tsy\Plugs\Async\MySql\MySqlResult;

class MySqlFuture implements FutureIntf
{
    static private $instances=[];
    static private $InstanceID='';
    static private $inUse=[];
    private $config=[];
    private $SQL='';
    private $md5='';
    function __construct($SQL,$config=[])
    {
        $this->config=$config;
        $this->SQL=$SQL;
        $this->md5=md5(serialize($config));
    }

    /**
     * 执行
     * @param Async $promise
     * @param $content
     */
    function run(Async &$promise, $content)
    {
        if($this->SQL&&$this->config){
            if($MySqli = self::getInstance($this->config)){
                self::$inUse[$this->md5]=true;
                swoole_mysql_query($MySqli,$this->SQL,function (\mysqli $link,mixed $result)use(&$promise,&$content,&$this){
                    unset(MySqlFuture::$inUse[$this->md5]);
                    $promise->accept([
                        'mysql_query'=>new MySqlResult($this->SQL,$result,$link->_insert_id,$link->_affected_rows)
                    ]);
                });
            }else{

            }
        }
    }

    /**
     * 获取一个实例
     * @param $config
     * @return mixed
     */
    static private function getInstance($config){
        $md5    =   md5(serialize($config));
        if(!isset(self::$instances[$md5])){
            self::$instances[$md5]=[];
        }
        $new_client = function ()use($config,$md5){
            $mysqli=new \mysqli();
            $mysqli->connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME'],$config['DB_PORT']);
            self::$instances[$md5][]=$mysqli;
            $IDs = array_keys(self::$instances[$md5]);
            $InstanceID = $md5.','.$IDs[count($IDs)-1];
            self::$InstanceID=$InstanceID;
            return $mysqli;
        };
        if(count(self::$instances[$md5])>self::$inUse[$md5]){
//            有空余
            if($IDs = array_diff(self::$instances[$md5],self::$inUse[$md5])){
                $ID = array_shift($IDs);
                if(isset(self::$instances[$ID])){
                    self::$InstanceID = $md5.','.$ID;
                    return self::$instances[$ID];
                }else{

                }
            }else{

            }
        }else{

        }
        return $new_client();
    }

    /**
     * 开始标记
     * @param bool $InstanceID
     */
    static private function start($InstanceID=false){
        if(!$InstanceID){$InstanceID=self::$InstanceID;}
        list($md5,$ID)=explode(',',$InstanceID);
        self::$inUse[$md5][]=$ID;
    }

    /**
     * 完成标记
     * @param bool $InstanceID
     */
    static private function finish($InstanceID=false){
        if(!$InstanceID){$InstanceID=self::$InstanceID;}
        list($md5,$ID)=explode(',',$InstanceID);
        if($Key = array_search($ID, self::$inUse[$md5])){
            unset(self::$inUse[$md5][$Key]);
        }
    }
}