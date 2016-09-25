<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/6/16
 * Time: 4:07 PM
 */
/**
 * 创建进程的静态内容
 * @param $name
 * @param null $value
 * @return mixed|null
 */
function static_keep($name,$value=null){
    static $values = [];
    if($name&&$value){
        if(substr($name,0,1)=='+'){
            $name=substr($name,1);
            if(is_string($values[$name])){
                $values[$name].=$value;
            }elseif(is_array($values[$name])){
                $values[$name][]=$value;
            }else{
                $values[$name]=$value;
            }
        }else{
            $values[$name]=$value;
        }
    }else{
        return isset($values[$name])?$values[$name]:null;
    }
}