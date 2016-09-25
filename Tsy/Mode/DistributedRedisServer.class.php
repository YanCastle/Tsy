<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/25
 * Time: 22:05
 */

namespace Tsy\Mode;


use Tsy\Library\Cache\Driver\Redis;
use Tsy\Library\Task;
use Tsy\Library\Traits\Distribute;
use Tsy\Mode;

/**
 * 分布式调度程序，接受协议处理
 * Class DistributedRedisServer
 * @package Tsy\Mode
 */
class DistributedRedisServer extends \Tsy\Library\Fathers\Distribute
{

    /**
     * Redis扩展的订阅回调
     * @param \Redis $redis
     * @param string $channel
     * @param string $msg
     */
    function onRedisSubscribe(\Redis $redis,string $channel,string $msg){
        file_put_contents('dd',$msg);
        $data = json_decode($msg,true);
        switch ($channel){
            case self::$Config['SUBSCRIBE'][self::NODE_SUBSCRIBE_CHANNEL]:
//                $data=[
//                    'i'=>'',
//                    'd'=>'',
//                    't'=>'',
//                    'e'=>'',
//                    'sid'=>''
//                ];
                switch ($data['i']){
                    case 'Online':
                        //上线
                        self::$Clients[$data['d']['Chanel']]=array_merge($data['d'],['LastTime'=>time()]);
                        cache('DistributeClients',self::$Clients);
                        break;
                    case 'Offline':
//                        下线
                        unset(self::$Clients[$data['d']['Chanel']]);
                        break;
                    case 'Keep':
//                        心跳计划

                        break;
                }
                break;
            case self::$Config['SUBSCRIBE'][self::RETURN_SUBSCRIBE_CHANNEL]:
//                $data=[
//                    'i'=>'',//请求地址
//                    'd'=>'',//相应数据
//                    't'=>'',//消息编号，做全局存储
//                    'e'=>'',//错误提示信息
//                    'm'=>'',//广播？单播
//                    'uid'=>1,//标识用户编号
//                    'sid'=>''//session编号
//                ];

                //切换session
                session('[id]',$data['sid']);
//                swoole模式下怎么回复，http模式下怎么回复
//                task(new Task());
                swoole_out_check(cache('fd_'.$data['t']),['i'=>$data['i'],'d'=>$data['d'],'t'=>$data['t'],'e'=>$data['e']]);
                break;
        }
    }
    function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        $_GET=$_POST=$_REQUEST=[];
        $Data = swoole_in_check($fd,$data);
        if(is_array($Data)&&$Data){
            swoole_bridge_check($fd,$Data);
//            $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
            $channel = $this->distribute();
            if($channel){
                cache('fd_'.$Data['t'],$fd);
//                $host=self::$Config['REDIS']['HOST'];
//                $port=self::$Config['REDIS']['PORT'];
//                self::$Redis->connect($host,$port);
                self::$RedisPublish->publish($channel,json_encode([
                    'i'=>$Data['i'],
                    'd'=>$Data['d'],
                    't'=>$Data['t'],
                    'sid'=>session('[id]')
                ]));
//                try{
//                    self::$Redis->publish($channel,json_encode([
//                        'i'=>$Data['i'],
//                        'd'=>$Data['d'],
//                        't'=>$Data['t'],
//                        'sid'=>session('[id]')
//                    ]));
//                }catch (\Exception $e){
//                    $host=self::$Config['REDIS']['HOST'];
//                    $port=self::$Config['REDIS']['PORT'];
//                    self::$Redis->pconnect($host,$port);
//                }

            }else{
                swoole_out_check($fd,array_merge($Data,['e'=>'当前无逻辑服务器在线','d'=>[]]));
            }
        }elseif (is_string($Data)){
            swoole_out_check($fd,$Data);
        }else{
            swoole_out_check($fd,'');
        }
    }

    /**
     * 负载算法
     * @return string
     */
    function distribute():string{
        self::$Clients = cache('DistributeClients');
        if(self::$Clients){
            //返回一个频道
            return end(self::$Clients)['Channel'];
        }else{
            return '';
        }
    }

    function subscribeProcess(\swoole_process $process)
    {
//        self::$Redis=new \Redis();
        
        parent::subscribeProcess($process);
        self::$RedisSubscribe->subscribe([self::$Config['SUBSCRIBE'][self::RETURN_SUBSCRIBE_CHANNEL],self::$Config['SUBSCRIBE'][self::NODE_SUBSCRIBE_CHANNEL]],[$this,'onRedisSubscribe']);
    }
}