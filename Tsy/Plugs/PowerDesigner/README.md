Comment,表关系语法

property|link
|分割property和link
,分割多个对应关系，
&分离属性
示例：
商品库的商品obj
Goods/GoodsObject
中的comment
property,Basic&Basic&BasicID&ONE,OldVersion&OldVersion&VersionID&ARRAY|link,Class&ClassLink&GoodsID&true#ClassDic&CID

对应
````php
protected $main='Goods';
    protected $pk='GoodsID';
    protected $property=[
        'Basic'=>[
            self::RELATION_TABLE_NAME=>'Basic',
            self::RELATION_TABLE_COLUMN=>'BasicID',
            self::RELATION_TABLE_PROPERTY=>self::PROPERTY_ONE
        ],,
        'OldVersion'=>[
            self::RELATION_TABLE_NAME=>'OldVersion',
            self::RELATION_TABLE_COLUMN=>'VersionID',
            self::RELATION_TABLE_PROPERTY=>self::PROPERTY_ARRAY
        ]
    ];
    protected $link=[
        'Class'=>[
            self::RELATION_TABLE_NAME=>'ClassLink',
            self::RELATION_TABLE_COLUMN=>'GoodsID',
            self::RELATION_TABLE_LINK_HAS_PROPERTY=>true,
            self::RELATION_TABLE_LINK_TABLES=>[
                'ClassDic'=>[
                    self::RELATION_TABLE_COLUMN=>'CID'
                ]
            ]
        ]
    ];
```

