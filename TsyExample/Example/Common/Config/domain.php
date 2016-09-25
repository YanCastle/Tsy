<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 7/6/16
 * Time: 10:21 PM
 */
return [
    'localhost:8000'=>[
        'root'=>'/home/castle/www/Chest',
        'index'=>['index.html'],
        404=>'',
        'expire'=>'',
        'location'=>[
            '/'=>[//目录
                '\.php$'=>[//匹配规则
                    //定义
                ],

            ]
        ],
    ],
    'api.tansuyun.cn'=>[]
];