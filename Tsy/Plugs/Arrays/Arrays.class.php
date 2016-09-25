<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/25
 * Time: 12:57
 */

namespace Tsy\Plugs\Arrays;


class Arrays
{
    /**
     * 数组递归获得深度
     * @param $array
     * @param $level
     * @param $lev
     * @return array
     */
    static function al($array,$level,&$lev){

        foreach($array as $a)  {
            if(is_array($a)){
                $level++;
                $lev[]=$level;
                self::al($a,$level,$lev);
            }
        }
        return $lev;
    }
    /**
     * 获得输入数组的维度
     * @param array $array
     * @return int
     */
    static function getArrayLevel(array $array){
        if(!is_array($array))
            return 0;
        $lev=[];
        $arr=array_values($array);
        $Level=array_merge([1],self::al($arr,1,$lev));
        $Max = max($Level);
        return $Max;

    }
    /* 提取二维数组中某个字段的值
     * @param array $array
     * @param $key
     * @return array
     */
    static function getArrayValueByKey(array $array, $key)
    {
        $value = [];
        foreach ($array as $v) {
            if (isset($v[$key])) {
                $value[] = $v[$key];
            }
        }
        return $value;
    }
    /**
     * 多维数组排序
     * @param array $array 要排序的数组
     * @param string $key 排序依据字段
     * @param string $order 排序方式，0为降序，1为升序
     */
    static function sortArrayByKeyValue(array $array, array $sort, $key, $order = 'ASC')
    {
        if (is_array($array) && is_array($sort) && $array && $sort) {
            $function = strtoupper($order) == 'ASC' ? 'array_push' : 'array_unshift';
            $array = self::setArrayKeyByKeyValue($array, $key);
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
     * 将二维数组中的值转为一维的键
     * @param array $array
     * @param string $key
     * @param bool $repeat 是否存在键重复，键重复时将根据键生成数组
     * @return array
     */
    static function setArrayKeyByKeyValue($array, $key, $repeat = false)
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
    static function createArrayByKeysValues(array $keys,array $values){
        if(count($keys)==count($values)){
            $arr=[];
            $vals = array_values($values);
            foreach(array_values($keys) as $k=>$key){
                $arr[$key]=$vals[$k];
            }
            return $arr;
        }else{return [];}
    }

    /**
     * 多维数组排序
     * @param array $array 要排序的数组
     * @param string $key 排序依据字段
     * @param string $order 排序方式，0为降序，1为升序
     */
    static function array_sort(array $array, $key, $order = 1)
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
    static function array_key_set($array, $key, $repeat = false)
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
     * 提取二维数组中某个字段的值
     * @param array $array
     * @param $key
     * @return array
     */
    static function array_key_value(array $array, $key)
    {
        return array_column($array,$key);
//    $value = [];
//    foreach ($array as $v) {
//        if (isset($v[$key])) {
//            $value[] = $v[$key];
//        }
//    }
//    return $value;
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
    static function arrays_key_sort(array $array, array $sort, $key, $order = 'ASC')
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
         * 从数组中取键为以下范围内的键值对关系
         * @param array $array
         * @param array $keys
         * @return array
         */
    static function array_keys_value(array $array,array $keys){
        if(!is_array($array)||!is_array($keys)){return [];}
        $returns=[];
        foreach($array as $key=>$val){
            if(in_array($key,$keys)){
                $returns[$key]=$val;
            }
        }
        return $returns;
    }

    static function array_key_map_change($map, $data)
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
    static function array_map_format($map, $data)
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
        foreach ($data[0] as $k => $v) {
            if (isset($map[$v]))
                $tmp[$k] = $map[$v];
        }
        unset($data[0]);
        foreach ($data as $k => $v) {
            foreach ($v as $key => $value) {
                if (isset($tmp[$key]))
                    $d[$k][$tmp[$key]] = $value;
            }
        }
        return $d;
    }
    static function ArrayValueByKey($array,$key){
        $string=json_encode($array,JSON_UNESCAPED_UNICODE);
        $preg = "/\"$key\":\"{0,1}[a-zA-Z\d]+\"{0,1}/";
        preg_match_all($preg,$string,$match);
        $map='';
        foreach($match[0] as $value){
            $map=$map.','.$value;
        }
        $map=ltrim($map, ",");
        $data='"'.$key.'":';
        $res1=str_replace($data,'',$map);
        $res2=str_replace('"','',$res1);
        $res3=explode(',',$res2);
        return $res3;

    }

    /**
     * 任意数量数组取交集
     * 排除了空数组
     * @param $alls
     * @return mixed
     */
    static function intersection($arrays){
        $filter = function($val){
            return is_array($val)&&count($val);
        };
        $arrays=array_filter($arrays,$filter);
        return count($arrays)>1?call_user_func_array('array_intersect',$arrays):$arrays;
    }

    /**
     * 通过映射关系获取值
     * @param $map
     * @param $val
     * @param bool|false $default
     * @return bool
     */
    static function mapValue($map,$val,$default=false){
        return isset($map[$val])?$map[$val]:$default;
    }
}