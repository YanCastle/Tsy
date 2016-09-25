<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/27
 * Time: 20:59
 */
/**
 * 异步任务投递功能完成
 * @param $data
 * @param bool $wait
 * @return mixed
 */
function task(\Tsy\Library\Task $data,$wait=false){
    //检测当前进程是否是worker进程，如果是则投递，如果不是则sendMessage到worker线程，然后由worker线程发起投递
    if(!$GLOBALS['_SWOOLE']->taskworker){
        //这是worker线程，可以投递
        if($wait){
            return $GLOBALS['_SWOOLE']->taskwait($data,is_numeric($wait)?$wait:60);
        }else{
            $GLOBALS['_SWOOLE']->task($data);
        }
        return true;
    }else{
//        不可投递，通过sendMessage发送到Worker进程再继续投递
//        需要定义多线程之间通信协议
        pipe_message([
            't'=>'worker',
        ],new \Tsy\Library\Define\Pipe(\Tsy\Library\Define\Pipe::$TASK,$data));
    }

}
function task_controller(){}

/**
 * 异步任务
 * @param callable $callback
 * @param array $params
 */
function async($config,array $params=[]){}

/**
 * 进程间通信的给指定进程发送消息
 * @param $to
 * @param $message
 */
function pipe_message($to,$message){
    if(!is_string($message)){
        $message=serialize($message);
    }
    if(is_numeric($to)){
        //按目标worker id 发送，在此处检测是否是用户自定义进程，如果是则调用process的write方法，否则调用swoole_server的sendmessage方法
        if(\Tsy\Library\Define\SwooleProcess::$PROCESS==swoole_get_process_type($to)){
            //调用process的write方法
            $GLOBALS['_PROCESS'][$to][0]->write($message);
        }else{
            $GLOBALS['_SWOOLE']->sendMessage($message,$to);
        }
    }elseif (is_array($to)&&isset($to['t'])){
//        解析指令
        switch (strtolower($to['t'])){
            case 'worker':
                //worker进程是0-worker_num,所以随机一个呗
                $GLOBALS['_SWOOLE']->sendMessage($message,rand(0,$GLOBALS['_SWOOLE']->setting['worker_num']-1));
                break;
            case 'task':
                $GLOBALS['_SWOOLE']->sendMessage($message,rand($GLOBALS['_SWOOLE']->setting['worker_num'],$GLOBALS['_SWOOLE']->setting['worker_num']+$GLOBALS['_SWOOLE']->setting['task_worker_num']));
                break;
            case 'process':
                $GLOBALS['_PROCESS'][$GLOBALS['_TASK_WORKER_SUM']][0]->write($message);
                break;
        }
    }else{
        return false;
    }
}

function pipe_message_dispatch(\swoole_server $server,string $pipe,$from_worker_id=0,\swoole_process $process=null,$config=[]){
//    解析pipe的信息并执行相关逻辑
//    要区分是自定义进程还是系统进程
    $Match = true;
    try{
//        优先按照Pipe类来解析，如果解析失败再进行其他解析
        $Pipe = unserialize($pipe);
        if(is_object($Pipe)){
            switch ($Pipe->c){
                case \Tsy\Library\Define\Pipe::$TASK:
                    task($Pipe->d);
                    break;
                case \Tsy\Library\Define\Pipe::$CALLBACK:
                    if(is_callable($Pipe->d['callback'])){
                        call_user_func_array($Pipe->d['callback'],$Pipe->d['d']);
                    }
                    break;
                case \Tsy\Library\Define\Pipe::$EXEC:
                    @eval($Pipe->d);
                    break;
                case \Tsy\Library\Define\Pipe::$CONTROLLER:
                    if(isset($Pipe->d['i'])&&isset($Pipe->d['d'])){
                        controller($Pipe->d['i'],$Pipe->d['d']);
                    }
                    break;
                default:
                    $Match=false;
                    break;
            }
        }else{
            //解析失败，执行逻辑同catch部分
            $Match=false;
        }
    }catch (Exception $e){
        $Match=false;
    }
    if($Match){}else{
        if(is_object($process)){
            //在自定义进程中
            if(is_callable($config['PIPE'])){
                call_user_func_array($config['PIPE'],[$process,$server,$pipe]);
            }
        }else{
            //不再自定义进程中
            $callback = swoole_get_callback('PIPE_MESSAGE');
            if(is_callable($callback)){
//            做返回值检测
                call_user_func_array($callback,[$server,$from_worker_id,$pipe]);
            }
        }
    }
}

/**
 * 获取或设置当前进程的进程编号
 * @param null $id
 * @return null
 */
function swoole_get_process_id($id=null){
    static $worker_id=null;
    if($id){
        $worker_id=$id;
    }else{
        return $GLOBALS['_SWOOLE']->worker_id;
    }
    return $worker_id;
}

/**
 * 获取指定的进程编号的进程类型
 * @param null $id
 * @return string
 */
function swoole_get_process_type($id=null){
    if($id==null){
        $id=$GLOBALS['_SWOOLE']->worker_id;
    }
    //返回id是什么类型的进程
    if($id>=0&&$id<$GLOBALS['_SWOOLE']->setting['worker_num']){
        return \Tsy\Library\Define\SwooleProcess::$WORK;
    }elseif($id>=$GLOBALS['_SWOOLE']->setting['worker_num']&&$id<$GLOBALS['_SWOOLE']->setting['task_worker_num']){
        return \Tsy\Library\Define\SwooleProcess::$TASK;
    }elseif((null===$id)||($id>=$GLOBALS['_SWOOLE']->setting['worker_num']+$GLOBALS['_SWOOLE']->setting['task_worker_num'])){
        return \Tsy\Library\Define\SwooleProcess::$PROCESS;
    }else{
        return \Tsy\Library\Define\SwooleProcess::$UNKNOW;
    }
}

/**
 * 创建或获取SwooleClient
 * @param string $ip
 * @param int $port
 * @param callable $receive
 * @param callable $new
 * @param callable $connect
 * @param callable $close
 * @param callable $error
 * @return mixed
 */
function swoole_get_client($ip,$port,$receive,$new=false,$connect=null,$close=null,$error=null){
    static $clients=[];
    $host = $ip.$port;
    if(isset($clients[$host])&&count($clients[$host])&&false==$new){
        return $clients[$host][0];
    }
    $receiveCallback = function(\swoole_client $client,$data)use($clients,$receive){
        if(is_callable($receive)){
            call_user_func_array($receive,[$client,$data]);
        }
    };
    $closeCallback=function(\swoole_client $client)use($close){
        if(is_callable($close)){
            call_user_func_array($close,[$client]);
        }
    };
    $errorCallback=function(\swoole_client $client)use($error){
        if(is_callable($error)){
            call_user_func_array($error,[$client]);
        }
    };
    $connectCallback = function(\swoole_client $client)use($connect){
        if(is_callable($connect)){
            call_user_func_array($connect,[$client]);
        }
    };
    $client = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
    $client->on('receive',$receiveCallback);
    $client->on('error',$errorCallback);
    $client->on('connect',$connectCallback);
    $client->on('close',$closeCallback);
    $client->connect($ip,$port);
    if(!isset($clients[$host])){
        $clients[$host]=[];
    }
    $clients[$host][]=$client;
    return $client;
}