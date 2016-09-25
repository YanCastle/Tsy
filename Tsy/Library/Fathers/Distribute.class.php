<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/29
 * Time: 20:14
 */

namespace Tsy\Library\Fathers;


use Tsy\Library\Aop;
use Tsy\Mode;

class Distribute implements Mode
{

    const RETURN_SUBSCRIBE_CHANNEL='RSC';//One Node Want to send some message to Client
    const NODE_SUBSCRIBE_CHANNEL='NSC';//While Node On Line
    const LOGIC_SUBSCRIBE_CHANNEL='LSC';//While Node On Line

    /**
     * @var \Redis
     */
    static $RedisPublish;
    /**
     * @var \Redis
     */
    static $RedisSubscribe;
    static $Clients=[];//All the Redis channels can publish

    static $Config=[];
    static $SwooleMode;


    /**
     * @var \swoole_server
     */
    static $SwooleServer;

    function startSwoole(){
        cache('[cleartmp]');
        if($SwooleConfig = swoole_load_config()){
//            self::$SwooleServer=null;
            $Processes=[];
            $ProcessesConf=[];
            foreach ($SwooleConfig['LISTEN'] as $Listen){
                if(self::$SwooleServer){
                    call_user_func_array([self::$SwooleServer,'addListener'],$Listen);
                }else{
                    self::$SwooleServer=new \swoole_server($Listen[0],$Listen[1]);
                }
            }
            if(null===self::$SwooleServer){
                die('创建Swool对象失败');
            }
//            开始创建共享table
            foreach ($SwooleConfig['TABLE'] as $table){

            }
            swoole_get_callback(C('SWOOLE.CALLBACK'));
            if(self::$SwooleServer instanceof \swoole_server){
                if($SwooleConfig['CONF'])
                    self::$SwooleServer->set($SwooleConfig['CONF']);
                $GLOBALS['_TASK_WORKER_SUM']=self::$SwooleServer->setting['worker_num']+self::$SwooleServer->setting['task_worker_num'];
//                $Swoole = new \Tsy\Library\Server($SwooleConfig['PortModeMap']);
                $GLOBALS['_PortModeMap']=$SwooleConfig['PortModeMap'];
                self::$SwooleServer->on('receive',[$this,'onReceive']);
                self::$SwooleServer->on('connect',[$this,'onConnect']);
                self::$SwooleServer->on('close',[$this,'onClose']);
                self::$SwooleServer->on('start',[$this,'onStart']);
                self::$SwooleServer->on('shutdown',[$this,'onShutdown']);
                self::$SwooleServer->on('WorkerStop',[$this,'onWorkerStop']);
                self::$SwooleServer->on('WorkerStart',[$this,'onWorkerStart']);
                self::$SwooleServer->on('timer',[$this,'onTimer']);
                self::$SwooleServer->on('packet',[$this,'onPacket']);
                self::$SwooleServer->on('task',[$this,'onTask']);
                self::$SwooleServer->on('finish',[$this,'onFinish']);
                self::$SwooleServer->on('PipeMessage',[$this,'onPipeMessage']);
                self::$SwooleServer->on('WorkerError',[$this,'onWorkerError']);
                self::$SwooleServer->on('ManagerStart',[$this,'onManagerStart']);
                self::$SwooleServer->on('ManagerStop',[$this,'onManagerStop']);
                $Processes=[];
                $GLOBALS['_SWOOLE']=&self::$SwooleServer;
//                $SwooleConfig = swoole_load_config();

                //启动Redis订阅线程，用于管理Redis相关信息
                $Processes[] = [new \swoole_process([$this,'subscribeProcess'],true,true)];

                if($SwooleConfig['PROCESS']){
                    foreach ($SwooleConfig['PROCESS'] as $k=>$Process){
                        if(isset($Process['CALLBACK'])&&is_callable($Process['CALLBACK'])){
                            if(!isset($Process['NUMBER'])){
                                $Process['NUMBER']=1;
                            }
                            for($i=0;$i<$Process['NUMBER'];$i++){
                                $ProcessObject = new \swoole_process(function(\swoole_process $process)use($Process){
                                    //框架中套启动函数并启动用户定义函数
//                                    检测是否是需要实例化的类，如果是需要实例化的类则先实例化再传递到回调结构中
                                    if(isset($Process['PIPE'])&&is_callable($Process['PIPE'])){
//                                        加载用户定义的进程pipe回调函数
                                        swoole_event_add($process->pipe,function($pipe)use($process,$Process){
                                            $buffer = $process->read();
                                            if(strlen($buffer)==8192){
                                                static_keep('+receive',$buffer);
                                                return ;
                                            }else{
                                                $buffer .= static_keep('receive');
                                                static_keep('receive','');
                                            }
                                            pipe_message_dispatch(self::$SwooleServer,$buffer,0,$process,$Process);
                                        });
                                    }
                                    call_user_func_array($Process['CALLBACK'],[$process,self::$SwooleServer]);
                                },isset($Process['REDIRECT_STDIN_STDOUT'])?$Process['REDIRECT_STDIN_STDOUT']:true,true);
                                $Processes[$GLOBALS['_TASK_WORKER_SUM']+$i]=[$ProcessObject,$Process];
                            }
                        }else{
                            die('SwooleProcess配置不正确');
                        }
                    }
                }
                $GLOBALS['_PROCESS'] = &$Processes;
                foreach ($Processes as $process){
                    if(self::$SwooleServer->addProcess($process[0])){
                        L('线程创建成功');
                    }else{
                        echo swoole_strerror(swoole_errno());
                    }
                }
                $GLOBALS['_SWOOLE']=&self::$SwooleServer;
                self::$SwooleServer;
                L('启动Swoole');
                fd_name([]);
                self::$SwooleServer->start();
            }else{
                die('SWOOLE创建失败');
            }
        }else{
            die('SWOOLE配置不存在或不正确，请正确配置SWOOLE下面的信息');
        }
    }

    function __construct()
    {
//        self::$SwooleRedis = new \swoole_redis();
//        self::$SwooleRedis->on('message',[$this,'onMessage']);
        self::$Config = C('DRS');
        self::$RedisPublish = new \Redis();
        self::$RedisSubscribe = new \Redis();
        self::$SwooleMode=new Mode\Swoole();
    }

    /**
     * 执行体
     * @return mixed
     */
    function exec()
    {
        // TODO: Implement exec() method.
    }

    /**
     * 调度
     * @return mixed
     */
    function dispatch($data = null)
    {

//        解析从Redis订阅接受到的数据，并执行
    }

    /**
     * 启动函数
     * @return mixed
     */
    function start()
    {
//        1、连接到Redis服务器，
//
//        2、订阅指定频道，将消息接受绑定到dispatch函数中
//        3、检测Swoole配置，启动Swoole服务
        foreach (self::$Config as $Key=>$Value){
            switch ($Key){
                case 'REDIS':
                    if(!(isset($Value['HOST'])&&ip2long($Value['HOST'])&&isset($Value['PORT'])&&is_numeric($Value['PORT']))){
                        exit('错误的Redis配置');
                    }
//                    $Value['Auth'] = isset($Value['Auth'])
                    break;
                case 'SUBSCRIBE':
                    if(!(isset($Value[self::NODE_SUBSCRIBE_CHANNEL])&&isset($Value[self::RETURN_SUBSCRIBE_CHANNEL]))){
                        exit('错误的订阅配置');
                    }
                    break;
                case 'PUBLISH':

                    break;
            }
        }
        $host=self::$Config['REDIS']['HOST'];
        $port=self::$Config['REDIS']['PORT'];
//        self::$SwooleRedis->connect($host,$port,[$this,'onConnect']);
        self::$RedisPublish->pconnect($host,$port);
//        $this->inotify('d');
        $this->startSwoole();
    }


    function subscribeProcess(\swoole_process $process){
        //在其他线程中订阅
        swoole_event_add($process->pipe,function($pipe)use($process){
            $buffer = $process->read();
            if(strlen($buffer)==8192){
                static_keep('+receive',$buffer);
                return ;
            }else{
                $buffer .= static_keep('receive');
                static_keep('receive','');
            }
//            pipe_message_dispatch(self::$SwooleServer,$buffer,0,$process,$Process);
            //已接收管道数据
//            做管理处理
        });

        $host=self::$Config['REDIS']['HOST'];
        $port=self::$Config['REDIS']['PORT'];
        self::$RedisSubscribe->connect($host,$port);
        self::$RedisPublish->connect($host,$port);

    }

    /**
     * 停止继续执行
     * @return mixed
     */
    function stop($Code = "0")
    {
        // TODO: Implement stop() method.
    }

    function out($Data = null)
    {
        // TODO: Implement out() method.
    }

    function in($Data = null)
    {
        // TODO: Implement in() method.
    }

    

    function inotify($file){
        $Inotify = new \Inotify();
        $Inotify->watch($file);
        $Inotify->start(function($path,$msk){
            foreach (self::$Clients as $Channel){
                self::$Redis->publish($Channel,json_encode(['i'=>'Application/Index/check','d'=>['path'=>$path]]));
            }
        });
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
//        if(defined('PACKAGE_EOF')){
//            foreach (explode(PACKAGE_EOF,$data) as $item){
//                if(!$item)continue;
//                $Data = swoole_in_check($fd,$item);
//                if(is_array($Data)&&$Data){
//                    swoole_bridge_check($fd,$Data);
////                    $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
//                    
//                }elseif (is_string($Data)){
//                    swoole_out_check($fd,$Data);
//                }else{
//                    swoole_out_check($fd,'');
//                }
//            }
//        }else{
            $Data = swoole_in_check($fd,$data);
            if(is_array($Data)&&$Data){
                swoole_bridge_check($fd,$Data);
                $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
                
            }elseif (is_string($Data)){
                swoole_out_check($fd,$Data);
            }else{
                swoole_out_check($fd,'');
            }
//        }
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
            call_user_func_array($callback,[$server,$interval]);
        }
        $Timer = C('SWOOLE.TIMER');
        if(isset($Timer[$interval])&&is_callable($Timer[$interval])){
            call_user_func_array($Timer[$interval],[$server,$interval]);
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
//    protected function getModeClass($mode){
//        if(!isset($this->_swoole[$mode])){
//            $class='Tsy\\Library\\Swoole\\'.$mode;
//            $this->_swoole[$mode]=new $class();
//        }
//        return $this->_swoole[$mode];
//    }
}