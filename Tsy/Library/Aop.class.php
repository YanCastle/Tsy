<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/7/4
 * Time: 22:28
 */

namespace Tsy\Library;


use Tsy\Tsy;

class Aop
{
    /**
     * 停止继续执行的信号量，该信号量不支持异步Aop
     */
    const STOP_EXEC='_AOP_STOP_EXEC_';

    protected static $config=[];
    public static $AOP_BEFORE=0;
    public static $AOP_AFTER=1;

    /**
     * 添加aop拦截
     * @param string $name
     * @param callable $callback
     * @param $when
     * @param int $order
     * @return bool
     */
    public static function add(string $name,callable $callback,$when,$async=false,$order=0){
        if(!isset(self::$config[$name])){
            self::$config[$name]=[[],[]];
        }
        if(!isset(self::$config[$name][$when][$order])){
            self::$config[$name][$when][$order]=[];
        }
        self::$config[$name][$when][$order][]=[$callback,$async];
        return true;
    }
    public static function remove(string $name,$when=-1){}

    /**
     * aop执行
     * @param string $name
     * @param $when
     * @param array $data
     */
    public static function exec(string $name,$when,&$data=[],$async=null){
        if(isset(self::$config[$name][$when])){
            foreach (self::$config[$name][$when] as $callbacks){
                foreach ($callbacks as $callback){
                    if(is_callable($callback[0])){
                        if(is_string($callback[0])&&$callback[1])
                            task(new Task($callback[0], $data));
                        else{
                            $rs = call_user_func_array($callback[0],[&$data]);
                            if($rs===self::STOP_EXEC){
                                //调用对应mode的stop方法
                                Tsy::$Mode->out();
                                Tsy::$Mode->stop();
                            }
                        }
                    }
                }
            }
        }
    }
    public static function regist(string $name,callable $callback,$when){}
}