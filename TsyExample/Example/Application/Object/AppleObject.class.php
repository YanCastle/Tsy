<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/18
 * Time: 21:24
 */

namespace Application\Object;


use Tsy\Library\Object;

class AppleObject extends Object
{
    protected $main='Apple';
    protected $pk='';
    protected $property=[
        '属性'=>[
            self::RELATION_TABLE_NAME=>'表名',
            self::RELATION_TABLE_COLUMN=>'关联字段',
            self::RELATION_TABLE_PROPERTY=>self::PROPERTY_ONE
        ]
    ];
    protected $link=[
        '多对多属性'=>[
            self::RELATION_TABLE_NAME=>'',
            self::RELATION_TABLE_COLUMN=>'',
            self::RELATION_TABLE_LINK_HAS_PROPERTY=>false,//不含有关联属性
            self::RELATION_TABLE_LINK_TABLES=>[//多个被关联的表
                '表名'=>[
                    self::RELATION_TABLE_COLUMN=>''//关联字段
                ]
            ],
        ]
    ];
}