<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/14
 * Time: 11:21
 */
/**
 * 从数组中取键为以下范围内的键值对关系
 * @param array $array
 * @param array $keys
 * @return array
 */
function array_keys_value(array $array,array $keys){
    if(!is_array($array)||!is_array($keys)){return [];}
    $returns=[];
    foreach($array as $key=>$val){
        if(in_array($key,$keys)){
            $returns[$key]=$val;
        }
    }
    return $returns;
}

function array_key_map_change($map, $data)
{
    if (is_array($map) && is_array($data)) {
        $NewArray = [];
        $tmp = [];
        foreach ($data as $d) {
            foreach ($map as $k => $v) {
                $tmp[$v] = $d[$k] ? $d[$k] : '';
            }
            $NewArray[] = $tmp;
            $tmp = [];
        }
        return $NewArray;
    } else {
        return false;
    }
}

/**
 * 数组 键名转换
 * @param $map
 * @param $data
 * @return array
 */
function array_map_format($map, $data,$data_row=1,$default='')
{
//    $map = [
//        '要转换的键'=>'转换键结果'
//    ];
//    $data = [
//        0=>[
//            '要转换的键'
//        ],
//        1=>[
//            1
//        ]
//    ];
//    $result = [
//        '转换键结果'=>1
//    ];
    $d = [];
    $tmp = [];
    if($data_row===1){
        foreach ($data[0] as $k => $v) {
            if (isset($map[$v]))
                $tmp[$k] = $map[$v];
        }
        unset($data[0]);
    }
    else
        $tmp=$map;

    foreach ($data as $k => $v) {
        foreach ($v as $key => $value) {
            $d[$k][$tmp[$key]] = isset($tmp[$key])?$value:$default;
        }
    }
    return $d;
}

/**
 * 二维数组 按sort数组的某个键排序array数组
 * 绵阳市碳素云信息技术有限责任公司 castle@tansuyun.cn
 * @param array $array 要排序的数组
 * @param array $sort 排序依据数组
 * @param string $key 排序键
 * @param string $order 排序规则，ASC升序，DESC降序
 * @return array
 *
 */
function arrays_key_sort(array $array, array $sort, $key, $order = 'ASC')
{
    if (is_array($array) && is_array($sort) && $array && $sort) {
        $function = strtoupper($order) == 'ASC' ? 'array_push' : 'array_unshift';
        $array = array_key_set($array, $key);
        $new = [];
        foreach ($sort as $a) {
            if (isset($a[$key]) && isset($array[$a[$key]]))
                $function($new, $array[$a[$key]]);
        }
        return $new;
    } else {
        return false;
    }
}

/**
 * 多维数组排序
 * @param array $array 要排序的数组
 * @param string $key 排序依据字段
 * @param string $order 排序方式，0为降序，1为升序
 */
function array_sort(array $array, $key, $order = 1)
{
    $sort = [];
//    在此处形成字段值与键名的对应关系
    foreach ($array as $k => $v) {
        $sort[$v[$key]] = isset($sort[$v[$key]]) ? array_merge($sort[$v[$key]], [$k]) : [$k];
    }
    if ($order == 1 && ksort($sort)) {
//        升序排序

    } elseif ($order == 0 && krsort($sort)) {

    } else {
        return false;
    }
    $rs = [];
//    按照排好顺序的关系生成新的数组
    foreach ($sort as $value) {
        foreach ($value as $n) {
            $rs[] = $array[$n];
        }
    }
    unset($sort, $array);
    return $rs;
}

/**
 * 将二维数组中的值转为一维数组的键
 * @param array $array
 * @param string $key
 * @param bool $repeat 是否存在键重复，键重复时将根据键生成数组
 * @return array
 */
function array_key_set($array, $key, $repeat = false)
{
    if (!$array) {
        return [];
    }
    $a = [];
    foreach ($array as $k => $v) {
        if ($repeat) {
            $a[$v[$key]][] = $v;
        } else {
            $a[$v[$key]] = $v;
        }
    }
    return $a;
}

/**
 * 对象化时的结构整合
 * @param array $array
 * @param string $key
 * @param array $properties
 */
function array_object(array &$array,string $key,array $properties){
    foreach ($array as $k=>$v){
        foreach ($properties as $propertyName=>$propertyValues){
            isset($array[$k][$propertyName]) or $array[$k][$propertyName]=[];
//            $array[$k][$propertyName]
//            !isset($propertyValues) or $array[$k][$propertyName][]=$propertyValues[]
        }
    }
}

/**
 * 针对二位数组设置键值关系
 * @param $array
 * @param $key
 * @param $value
 */
function array_set_key_value(&$array,$key,$value){
    foreach ($array as $k=>$item){
        $array[$k][$key]=$value;
    }
}

/**
 * 二维数组的对象组合
 * @param $array
 * @param $properties
 * @param $properties_map
 */
function array_2d_merge(&$array,$properties,$properties_map){
//    $array = [
//        1=>[
//            'a'=>'a',
//            'b'=>1
//        ]
//    ];
//    $properties = [
//        'name'=>[
//            [$key=>1,'ss'=>1]
//        ]
//    ];
//    $result = [
//        1=>[
//            'a'=>'a',
//            'b'=>1,
//            'name'=>[$key=>1,'ss'=>1]
//        ]
//    ];
//    foreach ($properties_map as $propertyName=>$config){
//        if('a'==strtolower(substr($config['Type']))){
//            $properties_map[$propertyName] = array_key_set($properties,$config['Column'],true);
//        }
//    }
    foreach ($array as $k=>$v){
        foreach ($properties_map as $propertyName=>$config){
            switch (strtolower(substr($config['Type'],0,1))){
                case 'a':
                    //数组
                    $array[$k][$propertyName]=isset($properties[$propertyName][$v[$config['Column']]])?$properties[$propertyName][$v[$config['Column']]]:[];
                    break;
                case 'p':
                    //属性合并
                    if(isset($properties[$propertyName][0][$v[$config['Column']]]))
                        $array[$k] = array_merge($v,$properties[$propertyName][0][$v[$config['Column']]]);
                    elseif(isset($properties[$propertyName][1])){
                        $array[$k]=array_merge($v,$properties[$propertyName][1]);
                    }
                    break;
            }
        }
    }
}