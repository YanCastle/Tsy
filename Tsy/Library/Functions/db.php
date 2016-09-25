<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/19
 * Time: 13:30
 */
//function multiple_sql(array $SQLs){
//    if(strtolower(APP_MODE)=='swoole'){
//        foreach ($SQLs as $SQL){
//            //投递任务
//            task();
//        }
//    }else{
//        // HTTP-FPM模式 回到同步模式
//    }
//}
function startTrans($ID=null){
    M()->startTrans();
}
function rollback($ID=null){
    M()->rollback();
}
function commit($ID=null){
    M()->commit();
}

/**
 * sql的前缀替换
 * @param string $sql
 * @param string $prefix
 * @param null|string $replace
 * @return string
 */
function sql_prefix($sql,$prefix,$replace=null){
    $replace = $replace?$replace:C('SQL_PREFIX');
    return str_replace($replace,$prefix,$sql);
}