<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 12:08
 */

namespace Tsy\Library;

/**
 * Server类，用来处理SwooleServer的各种回调
 * @package Tsy\Library
 */
class Server
{
    protected $_swoole=[];
    protected $first=[];
    protected $port_mode_map=[];
    function __construct($modes=[])
    {
        $this->port_mode_map=$modes;
        $this->init();
    }

    /**
     * Server启动时的初始化方法
     */
    function init(){

    }
    /**
     * 收到消息
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    function onReceive(\swoole_server $server,$fd,$from_id,$data){
        //添加一个前切点
//        Aop::exec(__METHOD__,Aop::$AOP_BEFORE,func_get_args());
        $_GET=[];$_POST=[];$_REQUEST=[];
        $callback = swoole_get_callback('RECEIVE');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$fd,$from_id,$data]);
        }
//        标记变量，是否是第一次接受请求
        fd($fd);
        $info = swoole_connect_info($fd);
        $_GET['_Port']=$info['server_port'];
//        接受数据次数统计
//        swoole_receive();
        if(defined('PACKAGE_EOF')){
            foreach (explode(PACKAGE_EOF,$data) as $item){
                if(!$item)continue;
                $Data = swoole_in_check($fd,$item);
                if(is_array($Data)&&$Data){
                    swoole_bridge_check($fd,$Data);
                    $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
                    if(HTTP_COMMENT!==$return){
                        swoole_out_check($fd,$return);
                    }else{
                        //写入HTTP_COMMENT的链接队列中
                        cache('[+]tmp_HTTP_COMMENT',$fd);
                    }
                }elseif (is_string($Data)){
                    swoole_out_check($fd,$Data);
                }else{
                    swoole_out_check($fd,'');
                }
            }
        }else{
            $Data = swoole_in_check($fd,$data);
            if(is_array($Data)&&$Data){
                swoole_bridge_check($fd,$Data);
                $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
                if(HTTP_COMMENT!==$return){
                    swoole_out_check($fd,$return);
                }else{
                    //写入HTTP_COMMENT的链接队列中
                    cache('[+]tmp_HTTP_COMMENT',$fd);
                }
            }elseif (is_string($Data)){
                swoole_out_check($fd,$Data);
            }else{
                swoole_out_check($fd,'');
            }
        }
        session('[id]',null);//删除session_id标识
    }
     
    /**
     * 连接断开
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     */
    function onClose(\swoole_server $server,$fd,$from_id){
        Aop::exec(__METHOD__,Aop::$AOP_BEFORE,$fd);
        $callback = swoole_get_callback('CLOSE');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$fd,$from_id]);
        }
        unset($this->first[$fd]);
        fd($fd);
        L("连接断开：{$fd}");
        fd_name(null);
        $info = swoole_connect_info($fd);
        $_GET['_Port']=$info['server_port'];
//        swoole_receive(null);
        port_group($info['server_port'],null);
        http_header(null);
        cache('[-]tmp_HTTP_COMMENT',$fd);
        Aop::exec(__METHOD__,Aop::$AOP_AFTER,$fd);
    }

    /**
     * 连接建立
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     */
    function onConnect(\swoole_server $server,$fd,$from_id){
        $callback = swoole_get_callback('CONNECT');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$fd,$from_id]);
        }
        fd($fd);
        $info = swoole_connect_info($fd);
        $_GET['_Port']=$info['server_port'];
//        检测该链接是否在允许的IP范围内或者是否在禁止的IP范围内
        L("新连接 服务器:{$info['server_port']} 客户端:{$info['remote_ip']}:{$info['remote_port']} 链接标识符:{$fd}",LOG_DEBUG);
        if(swoole_connect_check($server,$info,$fd)){
            fd_name($fd);
            port_group($info['server_port'],$fd);
        }else{
            $server->close($fd);
        }
    }

    /**
     * 异步任务触发回调
     * @param \swoole_server $server
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    function onTask(\swoole_server $server,$task_id,$from_id,$data){
//        var_dump($data);
//        $data = unserialize($data);
        $AopData = [&$server,&$task_id,&$from_id,&$data];
        Aop::exec(__METHOD__, Aop::$AOP_BEFORE,$AopData);
        $callback = swoole_get_callback('TASK');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$task_id,$from_id,$data]);
        }
        if($data instanceof Task){
            //回调task任务，仅支持静态方法或者函数
            session('[id]',$data->sid);
            if(is_callable($data->cmd)){
                call_user_func($data->cmd,$data->data);
            }
        }
        Aop::exec(__METHOD__, Aop::$AOP_AFTER,$AopData);
    }

    /**
     * 异步任务完成回调
     * @param \swoole_server $server
     * @param $task_id
     * @param $data
     */
    function onFinish(\swoole_server $server,$task_id,$data){
        $callback = swoole_get_callback('FINISH');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$task_id,$data]);
        }
    }

    /**
     * UDP回调
     */
    function onPacket(\swoole_server $server,$data,array $client_info){
        $callback = swoole_get_callback('PACKET');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$data,$client_info]);
        }
    }
    /**
     * 在主线程回调
     * @param \swoole_server $server
     */
    function onStart(\swoole_server $Server){
        $callback = swoole_get_callback('START');
        if(is_callable($callback)){
            call_user_func_array($callback,[$Server]);
        }

    }
    /**
     * Server结束时
     * 已关闭所有线程
    已关闭所有worker进程
    已close所有TCP/UDP监听端口
    已关闭主Rector
     * @param \swoole_server $server
     */
    function onShutdown(\swoole_server $server){
        $callback = swoole_get_callback('SHUTDOWN');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server]);
        }
    }

    /**
     * 此事件在worker进程/task进程启动时发生。这里创建的对象可以在进程生命周期内使用。
     * @param \swoole_server $server
     * @param $worker_id
     */
    function onWorkerStart(\swoole_server $server, $worker_id){
        define('PROCESS_ID',$worker_id);
        $_GET['_server']=$server;
        $callback = swoole_get_callback('WORKER_START');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$worker_id]);
        }
        swoole_timer_tick(5000,[$this,'onTimer']);
    }

    /**
     * 此事件在worker进程终止时发生。在此函数中可以回收worker进程申请的各类资源。
     * @param \swoole_server $server
     * @param $worker_id
     */
    function onWorkerStop(\swoole_server $server,$worker_id){
        $callback = swoole_get_callback('WORKER_STOP');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$worker_id]);
        }
    }

    /**
     * 定时器触发
     * @param \swoole_server $server
     * @param $interval
     */
    function onTimer($interval){
        $callback = swoole_get_callback('TIMER');
        if(is_callable($callback)){
            call_user_func_array($callback,[$this,$interval]);
        }
        $Timer = C('SWOOLE.TIMER');
        if(isset($Timer[$interval])&&is_callable($Timer[$interval])){
            call_user_func_array($Timer[$interval],[$this,$interval]);
        }
//       开始检测系统定时器设定。如检测fdName缓存是否失效，fdGroup是否失效等
        //自动重启检测
        $AutoReload = C('SWOOLE.AUTO_RELOAD');
        if($AutoReload||is_callable($AutoReload)){
            $Time = is_callable($AutoReload)?call_user_func($AutoReload):(is_numeric($AutoReload)?$AutoReload:C('AUTO_RELOAD_TIME'));
            if($Time){
                $GLOBALS['_SWOOLE']->reload();
            }
        }
//        检测Db连接，超过20分钟未动作的链接将被释放掉
        
    }

    /**
     * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件。
     * worker/task进程都可能会触发onPipeMessage事件。
     * @param \swoole_server $server
     * @param $from_worker_id
     * @param $message
     */
    function onPipeMessage(\swoole_server $server,$from_worker_id,$message){
        pipe_message_dispatch($server,$message,$from_worker_id);
    }

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数。
     * @param \swoole_server $server
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     */
    function onWorkerError(\swoole_server $server,$worker_id,$worker_pid,$exit_code){
        $callback = swoole_get_callback('WORKER_ERROR');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server,$worker_id,$worker_pid,$exit_code]);
        }
    }

    /**
     * 当管理进程启动时调用它
     * @param \swoole_server $server
     */
    function onManagerStart(\swoole_server $Server){
        cache('[cleartmp]');
        $callback = swoole_get_callback('MANAGER_START');
        if(is_callable($callback)){
            call_user_func_array($callback,[$Server]);
        }

    }

    /**
     * 当管理进程结束时调用它
     * @param \swoole_server $server
     */
    function onManagerStop(\swoole_server $server){
        cache('[cleartmp]');
        $callback = swoole_get_callback('MANAGER_STOP');
        if(is_callable($callback)){
            call_user_func_array($callback,[$server]);
        }
    }
    protected function getModeClass($mode){
        if(!isset($this->_swoole[$mode])){
            $class='Tsy\\Library\\Swoole\\'.$mode;
            $this->_swoole[$mode]=new $class();
        }
        return $this->_swoole[$mode];
    }    
}
