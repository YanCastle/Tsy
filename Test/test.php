<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/06
 * Time: 10:34
 */
include 'Test.class.php';
$config=[
    'Management'=>[
        'Pack'=>[
            'get'=>[
                'PID'=>15
            ],
            'gets'=>[
                'PIDs'=>[15,25]
            ],
            'save'=>[
                'PID'=>38,
                'Params'=>[
                    'Memo'=>'121323',
                    'OUID'=>3
                ],
            ]
        ]
    ]

];
$Test = new Test('http://www.gzxz.cn/index.php',$config);
$Test->debug();
$Test->start();