<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 8/28/16
 * Time: 3:37 PM
 */
return [
    'DRS'=>[
        'REDIS'=>[
            'HOST'=>'127.0.0.1',
            'PORT'=>'6379',
            'AUTH'=>''
        ],
        'SUBSCRIBE'=>[
            //All Subscribe Channel Config,This is Server
            \Tsy\Library\Fathers\Distribute::NODE_SUBSCRIBE_CHANNEL=>'Distribute.Manage',
            \Tsy\Library\Fathers\Distribute::RETURN_SUBSCRIBE_CHANNEL=>'Distribute.Receive',
            \Tsy\Library\Fathers\Distribute::LOGIC_SUBSCRIBE_CHANNEL=>'Distribute.Logic1',
        ],
        'PUBLISH'=>[
            // Send The Notice To DistributeRedisClient
        ]
    ],
];