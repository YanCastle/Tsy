<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:52
 */
return [
    'HTTP'=>[
        'DISPATCH'=>function(){
            return [
                'i'=>$_GET['i'],
                'd'=>$_POST,
            ];
        },
        'OUT'=>function($data){
            return json_encode($data);
        }
    ]
];