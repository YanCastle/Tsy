<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 16-5-4
 * Time: 下午6:52
 */
/**
 * 实例化对象类
 * @param string $Name 对象名
 * @param array $Config 初始化对象时的参数
 * @return \TSy\Library\Object
 */
function O($Name,$Config=[]){
//    $Config=[
//        'main'=>'printer',
//        'pk'=>'PrinterClientID',
//        'property'=>[
//            '属性名称'=>[
//                \Tsy\Library\Object::RELATION_TABLE_NAME=>'',
//            ]
//        ]
//    ];
    static $Objects=[];
    $CacheKey = md5($Name.serialize($Config));
    if (isset($Objects[$CacheKey])){
        return $Objects[$CacheKey];
    }
    if(class_exists($Obj=implode('\\',[process_queue('controller','get')[0],'Object',$Name.'Object']))){
        $OBJ=new $Obj($Name);
        foreach ($Config as $K=>$config){
            $OBJ->$K=$config;
        }
        $Objects[$CacheKey]=$OBJ;
    }else{
        $OBJ=new \TSy\Library\Object($Name);
    }
    return $OBJ;
}

function object_generate($Objects,$Properties){
    foreach ($Objects as $ObjectID=>$Object){
//        $Objects[$ObjectID]
    }
}