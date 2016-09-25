<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:37
 */
function S($key,$value=false,$expire=false,$Default=''){
    $value = cache($key,$value,$expire);
    return null!==$value?$value:$Default;
}

/**
 * 数值缓存操作
 * @param $name
 * @param $op
 * @param int $value
 * @param string $type
 */
function number_cache($name,$op,$value=1,$type=''){
    $cache = \Tsy\Library\Cache\Cache::getCacheHandler($type);
    if($name&&in_array($op,[\Tsy\Library\Cache\Cache::$setDec,\Tsy\Library\Cache\Cache::$setInc])){
        if(\Tsy\Library\Cache\Cache::$setInc==$op){
            $cache->setInc($name,$value);
        }else{
            $cache->setDec($name,$value);
        }
    }
}

/**
 * 缓存值加
 * @param $name
 * @param int $value
 * @return mixed
 */
function cache_inc($name,$value=1){
    $cache = \Tsy\Library\Cache\Cache::getCacheHandler();
    return $cache->setInc($name,$value);
}

/**
 * 缓存值减
 * @param $name
 * @param int $value
 * @return mixed
 */
function cache_dec($name,$value=1){
    $cache = \Tsy\Library\Cache\Cache::getCacheHandler();
    return $cache->setDec($name,$value);
}
/**
 * 缓存
 * @param $key
 * @param bool $value
 * @param bool $expire
 */
function cache($key,$value=false,$expire=null,$type=''){
    $cache = \Tsy\Library\Cache\Cache::getCacheHandler($type);
    //开始数据处理
    if($cache){
        if(preg_match('/^\[[a-z]+\]$/',$key)){
            switch (substr($key,1,strlen($key)-2)){
                case 'clear':
                    if(method_exists($cache,'clear'))
                        $cache->clear();
                    break;
                case 'cleartmp':
                    $tmp = $cache->get('_tmp_keys');
                    $tmp = is_array($tmp)?$tmp:[];
                    foreach ($tmp as $k){
                        $cache->rm($k);
                    }
                    $cache->set('_tmp_keys',[]);
                    break;
                default:
                    L('UnknowOperateOfCache',LOG_WARNING);
                    break;
            }
            return null;
        }elseif(preg_match('/^\[[\+\-AS]{1,2}\][A-Za-z_]+$/',$key)){
            //进行复杂操作
//            从 [+][+S][+A][-][-A][-S] 中提取操作符，然后再分离key
            $Operate = substr($key,1,1);
            $Type = substr($key,2,1)===']'?null:substr($key,2,1);
            $key = substr($key,$Type?4:3);
            $v = $cache->get($key);
            $Changed=false;
            if(!$v){
                //初始化
                switch ($Type){
                    case 'A':$v=[];break;
                    case 'S':$v='';break;
                    case null:
                        if(is_array($value)){
                            $v=[];
                        }elseif(is_string($value)){
                            $v='';
                        }
                        break;
                }
                $Changed=true;
            }
            switch ($Operate){
                case '+':
                    switch ($Type){
                        case 'A':$v[]=$value;break;
                        case 'S':$v.=$value;break;
                        case null:
                            if(is_array($value)){
                                $v[]=$value;
                            }elseif(is_string($value)){
                                $v.=$value;
                            }
                            break;
                    }
                    $Changed=true;
                    break;
                case '-':
                    switch ($Type){
                        case 'A':
                            if($index=array_search($value,$v)){
                                $v=array_diff($v,$value);
                                $Changed=true;
                            }
                            break;
                        case 'S':
                            if($pos = strpos($v,$value)){
                                $Changed=true;
                                $v=substr($v,0,$pos).substr($v,$pos+strlen($value));
                            }
                            break;
                        case null:
                            if(is_array($value)){
                                if($index=array_search($value,$v)){
                                    unset($v[$index]);
                                    $Changed=true;
                                }
                            }elseif(is_string($value)){
                                if($pos = strpos($v,$value)){
                                    $Changed=true;
                                    $v=substr($v,0,$pos).substr($v,$pos+strlen($value));
                                }
                            }
                            break;
                    }
                    break;
                case '=':
                    //获取并设置值
                    $cache->set($key,$value);
                    return $v;
                    break;
            }
            //存储 仅当数据发生变更时保存变更值
            !$Changed or $cache->set($key,$v);
        }else{
            if(false===$value){
                return $cache->get($key);
            }elseif (null===$value){
                return $cache->rm($key);
            }else{
                if('tmp_'==substr($key,0,4)){
                    $tmp = $cache->get('_tmp_keys');
                    $tmp = is_array($tmp)?$tmp:[];
                    if(!in_array($key,$tmp)){
                        $tmp[]=$key;
                    }
                    $cache->set('_tmp_keys',$tmp);
                }
                return $cache->set($key,$value,$expire);
            }
        }
    }else{
        L('缓存驱动类不存在',LOG_ERR);
        return false;
    }
}

/**
 * 队列读写
 * @param $key
 * @param bool $value
 * @param int $order 1表示先进先出 0 先进后出
 */
function queue($key,$value=''){
    $cache = \Tsy\Library\Cache\Cache::getCacheHandler();
    return $cache->queue($key,$value);
}