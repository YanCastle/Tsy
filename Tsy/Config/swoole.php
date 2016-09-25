<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:46
 */
return [
    'SWOOLE'=>[
        'AUTO_RELOAD'=>function($int){
            $Time = cache('LastRestartTime');
            if((time()-$Time)>7200){
                cache('LastRestartTime',time());
                return true;
            }
            return false;
        },
        'AUTO_RELOAD_TIME'=>3,
        'CONF'=>[
            'daemonize' => !APP_DEBUG, //自动进入守护进程
            'task_worker_num' => 5,//开启task功能，
            'dispatch_mode '=>1,//轮询模式
            'worker_num'=>5,
            'task_ipc_mode'=>3
//            'open_eof_check'=>true,
//            'package_eof'=>"\r\n",
//            'open_eof_split'=>true
        ],
        'TABLE'=>[]
    ],
    'CACHE_FD_NAME'=>'tmp_fd_name',//对来自Swoole的链接标识符fd进行命名的缓存键名称
    'CACHE_FD_NAME_PUSH'=>'fd_name_push',//缓存不在线的push推送信息，禁止带上tmp_前缀
];