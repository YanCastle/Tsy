<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/25
 * Time: 22:05
 */

namespace Tsy\Mode;


use Tsy\Library\Task;
use Tsy\Library\Traits\Distribute;
use Tsy\Mode;

/**
 * 分布式调度客户端
 * Class DistributedRedisClient
 * @package Tsy\Mode
 */
class DistributedRedisClient extends \Tsy\Library\Fathers\Distribute
{
    /**
     * Redis扩展的订阅回调
     * @param \Redis $redis
     * @param string $channel
     * @param string $msg
     */
    function onRedisSubscribe(\Redis $redis,string $channel,string $msg){
//        $data=[
//            'i'=>'',
//            'd'=>'',
//            't'=>'',
//            'sid'=>''
//        ];
        $data = json_decode($msg,true);
//        task(new Task());
        session('[id]',$data['sid']);
        $result = controller($data['i'],$data['d'],$data['t']);
//        $result=['s'=>1];
        $str = json_encode([
            'i'=>$data['i'],//请求地址
            'd'=>$result,//相应数据
            't'=>$data['t'],//消息编号，做全局存储
            'e'=>is_string($result)?$result:L(LOG_TIP),//错误提示信息
            'm'=>'',//广播？单播
//            'uid'=>,//标识用户编号
            'sid'=>session('[id]')//session编号
        ]);
        self::$RedisPublish->publish(self::$Config['SUBSCRIBE'][self::RETURN_SUBSCRIBE_CHANNEL],$str);

    }

    function subscribeProcess(\swoole_process $process)
    {
        parent::subscribeProcess($process);
        self::$RedisPublish->publish(self::$Config['SUBSCRIBE'][self::NODE_SUBSCRIBE_CHANNEL],json_encode([
            'i'=>'Online',
            'd'=>['Channel'=>self::$Config['SUBSCRIBE'][self::LOGIC_SUBSCRIBE_CHANNEL]],
            't'=>'',
            'e'=>'',
            'sid'=>''
        ]));
        self::$RedisSubscribe->subscribe([self::$Config['SUBSCRIBE'][self::LOGIC_SUBSCRIBE_CHANNEL]],[$this,'onRedisSubscribe']);
    }
//    function
}