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
 * Date: 7/6/16
 * Time: 8:07 PM
 */

namespace Tsy\Mode;


use Tsy\Mode;

class Swoolehttp implements Mode
{
    public static $Swoole;
    /**
     * 执行体
     * @return mixed
     */
    function exec(){}

    /**
     * 调度
     * @return mixed
     */
    function dispatch($data=null){}

    /**
     * 启动函数
     * @return mixed
     */
    function start(){
//        读取配置文件、启动服务器
//        清楚缓存
        cache('[cleartmp]');
        if($SwooleConfig = swoole_load_config()){
            $Server=null;
            $Processes=[];
            $ProcessesConf=[];
            foreach ($SwooleConfig['LISTEN'] as $Listen){
                if($Server){
                    call_user_func_array([$Server,'addListener'],$Listen);
                }else{
                    $Server=new \swoole_http_server($Listen[0],$Listen[1]);
                }
            }
            if(null===$Server){
                die('创建Swool对象失败');
            }
//            开始创建共享table
            foreach ($SwooleConfig['TABLE'] as $table){

            }
            swoole_get_callback(C('SWOOLE.CALLBACK'));
            if(isset($Server)&&$Server){
                $Server->set($SwooleConfig['CONF']);
                $GLOBALS['_TASK_WORKER_SUM']=$Server->setting['worker_num']+$Server->setting['task_worker_num'];
                $Swoole = new \Tsy\Library\Server($SwooleConfig['PortModeMap']);
                $GLOBALS['_PortModeMap']=$SwooleConfig['PortModeMap'];
//                static_keep('domain',)
                $Server->on('request',function(\swoole_http_request $request, \swoole_http_response $response)use($Server,$Swoole){
                    $_COOKIE=$request->cookie;
                    server(array_merge($request->server,$request->header));
                    $_POST = $request->post;
                    $_GET=  $request->get;
                    $_REQUEST  = array_merge($_GET,$_POST);
                    $_FILES = $request->files;
//                    TODO 在swoole HTTP模式下加载文件
                    //读取域名配置信息，检查文件是否存在，如果存在则发送文件内容，如果不存在则调用404规则
                    $Status = 404;
                    $End='';
                    $host = $request->header['host'];
                    $Found = false;//是否有找到配置文件
                    if($Domain = static_keep('domain')){
//                        isset($Domain[$_SERVER['']])
                        if(isset($Domain[$host])){
                            $Found=true;
                            $dir = $Domain[$host]['root'];//realpath部分工作在加载配置文件时处理
                            $file =$dir.server('request_uri');
                            if($_SERVER['request_uri']=='/'){
                                //默认文件查找
                                foreach ($Domain['index'] as $index){
                                    if(file_exists($file.$index)){
                                        $file .=$index;
                                        break;
                                    }
                                }
                            }
                            if(file_exists($file)){
                                $Status = 200;
                                $fencoding = finfo_file(finfo_open(FILEINFO_MIME_ENCODING),$file);
                                $ContentType = mime($file).';'.('binary'==$fencoding?'':'charset='.$fencoding);
                                $response->header('Content-Type',$ContentType);
                                $response->sendfile($file);
                            }
                        }
                        $response->status($Status);
                        switch ($Status){
                            case 404:
                                $NotFoundConfig = $Domain[$host][404];
//                            if(file_exists($dir))
                                break;
                        }
                        $response->header('Server','TanSuYun');
                    }
                    if(!$Found){
                        $Data=[
                            'i'=>'Empty/_empty',
                            'd'=>[],
                            'm'=>'',
                            't'=>''
                        ];
                        $Dispatch = swoole_get_port_property($_SERVER['server_port'],'DISPATCH');
                        if(is_callable($Dispatch)){
                            $tmpData = call_user_func_array($Dispatch,[$request,$response]);
                            if($tmpData===null){
//                                return null;
                            }elseif(is_string($tmpData)&&strlen($tmpData)>0){
                                $End = $tmpData;
                            }else{
                                $Data = is_array($tmpData)?array_merge($Data,$tmpData):$Data;
                            }
                        }
                        if(is_array($Data)&&$Data){
                            ob_start();
                            $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
                            $content = ob_get_clean();
                            $Out = swoole_get_port_property($_SERVER['server_port'],'OUT');
                            if(HTTP_COMMENT===$return){
                                return '';
                            }elseif(is_callable($Out)){
                                ob_start();
                                $content = call_user_func_array($Out, [$return,$content]);
                                $cs = ob_get_clean();
                                $response->write($cs);
                                $response->write($content);
                            }else{
                                $End='';
                            }
                        }
                    }
                    $response->end($End);
                });
                $Server->on('open',[$Swoole,'onConnect']);
                $Server->on('close',[$Swoole,'onClose']);
                $Server->on('start',[$Swoole,'onStart']);
                $Server->on('shutdown',[$Swoole,'onShutdown']);
                $Server->on('WorkerStop',[$Swoole,'onWorkerStop']);
                $Server->on('WorkerStart',function(\swoole_server $server, $worker_id)use($Swoole,$Server){
                    if($worker_id<$server->setting['worker_num']&&flock(fopen('.inotify','w'),LOCK_EX )){
                        $Inotify = new \Inotify();
                        $Inotify->watch(CONF_PATH.DIRECTORY_SEPARATOR.'domain.php');
                        $Inotify->start(function(){
                            //之后切换成向所有进程发送函数执行命令
                            for ($i=0;$i<(Swoolehttp::$server->setting['worker_num']+Swoolehttp::$server->setting['task_worker_num']-1);$i++){
                                Swoolehttp::$server->sendMessage('Swoolehttp::loadDomain',$i);
                            }
                        });
                    }
                    Swoolehttp::loadDomain();
                    call_user_func_array([$Swoole,'onWorkerStart'],func_get_args());
                });
                $Server->on('timer',[$Swoole,'onTimer']);
                $Server->on('packet',[$Swoole,'onPacket']);
                $Server->on('task',[$Swoole,'onTask']);
                $Server->on('finish',[$Swoole,'onFinish']);
//                $Server->on('PipeMessage',[$Swoole,'onPipeMessage']);
                $Server->on('PipeMessage',function(\swoole_server $server,$from_worker_id,$message){
                    Swoolehttp::loadDomain();
                });
                $Server->on('WorkerError',[$Swoole,'onWorkerError']);
                $Server->on('ManagerStart',[$Swoole,'onManagerStart']);
                $Server->on('ManagerStop',[$Swoole,'onManagerStop']);
                $Processes=[];
                $GLOBALS['_SWOOLE']=&$Server;
//                $SwooleConfig = swoole_load_config();
                if($SwooleConfig['PROCESS']){
                    foreach ($SwooleConfig['PROCESS'] as $k=>$Process){
                        if(isset($Process['CALLBACK'])&&is_callable($Process['CALLBACK'])){
                            if(!isset($Process['NUMBER'])){
                                $Process['NUMBER']=1;
                            }
                            for($i=0;$i<$Process['NUMBER'];$i++){
                                $ProcessObject = new \swoole_process(function(\swoole_process $process)use($Process,$Server){
                                    //框架中套启动函数并启动用户定义函数
//                                    检测是否是需要实例化的类，如果是需要实例化的类则先实例化再传递到回调结构中
                                    if(isset($Process['PIPE'])&&is_callable($Process['PIPE'])){
//                                        加载用户定义的进程pipe回调函数
                                        swoole_event_add($process->pipe,function($pipe)use($process,$Server,$Process){
                                            $buffer = $process->read();
                                            if(strlen($buffer)==8192){
                                                static_keep('+receive',$buffer);
                                                return ;
                                            }else{
                                                $buffer .= static_keep('receive');
                                                static_keep('receive','');
                                            }
                                            pipe_message_dispatch($Server,$buffer,0,$process,$Process);
                                        });
                                    }
                                    call_user_func_array($Process['CALLBACK'],[$process,$Server]);
                                },isset($Process['REDIRECT_STDIN_STDOUT'])?$Process['REDIRECT_STDIN_STDOUT']:true,2);
                                $Processes[$GLOBALS['_TASK_WORKER_SUM']+$i]=[$ProcessObject,$Process];
                            }
                        }else{
                            die('SwooleProcess配置不正确');
                        }
                    }
                }
                $GLOBALS['_PROCESS'] = &$Processes;
                foreach ($Processes as $process){
                    if($Server->addProcess($process[0])){
                        L('线程创建成功');
                    }else{
                        echo swoole_strerror(swoole_errno());
                    }
                }
                $GLOBALS['_SWOOLE']=&$Server;
                L('启动Swoole');
                fd_name([]);
                self::$Swoole = &$Server;
                $Server->start();
            }else{
                die('SWOOLE创建失败');
            }
        }else{
            die('SWOOLE配置不存在或不正确，请正确配置SWOOLE下面的信息');
        }
    }
    static function send($fd,$data){
        self::$server->push($fd,$data);
    }
    static function sendfile($file){

    }
    static function loadDomain(){
        if(file_exists(CONF_PATH.DIRECTORY_SEPARATOR.'domain.php')){
            $domains = include CONF_PATH.DIRECTORY_SEPARATOR.'domain.php';
            foreach ($domains as $k=>$domain){
                $domains[$k]['root']=realpath($domain['root']);
            }
            static_keep('domain',$domains);
        }
    }
    function stop($Code=0)
    {
        self::$Swoole->stop();
    }
    function out($Data=null){

    }
    function in($Data=null){

    }
}