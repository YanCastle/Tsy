<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/29
 * Time: 20:52
 */
return [
    'SWOOLE'=>[
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
                    $data= json_decode($data,true);
                    return [
                        'i'=>$data['i'],
                        'd'=>$data['d'],
                        't'=>uniqid('distribute')
                    ];
                },
                'OUT'=>function($d){
                    return json_encode($d,JSON_UNESCAPED_UNICODE);
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
    ]
];