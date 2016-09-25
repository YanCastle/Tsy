<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:52
 */
return [
    'SWOOLE'=>[
        'AUTO_RELOAD'=>true,
        //监听配置
        'LISTEN'=>[
            [
                'HOST'=>'0.0.0.0',
                'PORT'=>'65400',
                'TYPE'=>'SOCKET',
//                'ALLOW_IP'=>[
//                    '127.0.0.1',
//                    ['10.10.13.1','10.10.13.2']
//                ],
//                'DENY_IP'=>[
//                    '127.0.0.1'
//                ],
                'DISPATCH'=>function($data){
                    list($Controller,$Action,$d) = explode(',,',$data);
                    $d = json_decode($d,true);
                    return[
                        'i'=>"Application/{$Controller}/{$Action}",
                        'd'=>$d
                    ];
                },
                'OUT'=>function($d){
                    return $d;
                }
            ],
//            [
//                'HOST'=>'0.0.0.0',
//                'PORT'=>'65401',
//                'TYPE'=>'SOCKET',
////                'ALLOW_IP'=>[
////                    '127.0.0.1',
////                    ['10.10.13.1','10.10.13.2']
////                ],
////                'DENY_IP'=>[
////                    '127.0.0.1'
////                ],
//                'DISPATCH'=>function($data){
//                    return[
//                        'i'=>'Application/Index/check',
//                        'd'=>''
//                    ];
//                },
//                'OUT'=>function($d){
//                    return $d;
//                }
//            ],
        ],
        'PROCESS'=>[
            [
                'NAME'=>'Router',
                'NUMBER'=>1,//进程数量
                'CALLBACK'=>function(\swoole_process $process,\swoole_server $server){
                    sleep(5);
                    swoole_get_process_type();
                    $client = swoole_get_client('127.0.0.1',9000,function(\swoole_client $client,$msg)use($server,$process){
//                        pipe_message(['t'=>'task'],serialize(new \Tsy\Library\Define\Pipe(\Tsy\Library\Define\Pipe::$CONTROLLER,[
//                            'i'=>'...',
//                            'd'=>[
//
//                            ]
//                        ])));
                    },false,function(\swoole_client $client){
                        $client->send('ss');
                    });
                },
                'REDIRECT_STDIN_STDOUT'=>false,//开启时echo不会输出到屏幕而是进入到可读队列
                'PIPE'=>function(\swoole_process $process,\swoole_server $server,$data){
                    echo $data;
                }
            ],
        ],
        'CALLBACK'=>[
            'PIPE_MESSAGE'=>function(\swoole_server $server,$from_worker_id,$data){
                $Process = static_keep('Client');
            }
        ],
        //SWOOLE 配置
        'CONF'=>[
            'daemonize' => 0, //自动进入守护进程
            'task_worker_num' => 1,//开启task功能，
            'dispatch_mode '=>3,//轮询模式
            'worker_num'=>4,
        ],
        //定时器配置
        'TIMER'=>[

        ],
        
    ]
];