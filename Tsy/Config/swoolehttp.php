<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 7/6/16
 * Time: 11:16 PM
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
        //SWOOLE 配置
        'CONF'=>[
            'daemonize' => 0, //自动进入守护进程
            'task_worker_num' => 10,//开启task功能，
            'dispatch_mode '=>3,//轮询模式
            'worker_num'=>10,
        ],
    ]
];