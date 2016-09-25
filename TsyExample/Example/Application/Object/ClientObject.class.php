<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/5/4
 * Time: 17:03
 */

namespace Application\Object;


use Tsy\Library\Object;

class ClientObject extends Object
{
    protected $property=[
        'Printer'=>[
            self::RELATION_TABLE_NAME=>'Printer',
            self::RELATION_TABLE_COLUMN=>'PrinterClientID',
            self::RELATION_TABLE_PROPERTY=>self::PROPERTY_ARRAY
        ]
    ];
//    protected $pk='PrinterClientID';
    protected $searchFields=[
        'SN'
    ];
}