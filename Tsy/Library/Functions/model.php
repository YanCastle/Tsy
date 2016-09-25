<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:39
 */
/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}
/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @param boolean $save 是否保存结果
 * @return mixed
 */
function N($key, $step=0,$save=false) {
//    static $_num    = array();
//    if (!isset($_num[$key])) {
//        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
//    }
//    if (empty($step)){
//        return $_num[$key];
//    }else{
//        $_num[$key] = $_num[$key] + (int)$step;
//    }
//    if(false !== $save){ // 保存结果
//        S('N_'.$key,$_num[$key],$save);
//    }
//    return null;
}
/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function G($start,$end='',$dec=4) {
//    static $_info       =   array();
//    static $_mem        =   array();
//    if(is_float($end)) { // 记录时间
//        $_info[$start]  =   $end;
//    }elseif(!empty($end)){ // 统计时间和内存使用
//        if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
//        if(MEMORY_LIMIT_ON && $dec=='m'){
//            if(!isset($_mem[$end])) $_mem[$end]     =  memory_get_usage();
//            return number_format(($_mem[$end]-$_mem[$start])/1024);
//        }else{
//            return number_format(($_info[$end]-$_info[$start]),$dec);
//        }
//
//    }else{ // 记录时间和内存使用
//        $_info[$start]  =  microtime(TRUE);
//        if(MEMORY_LIMIT_ON) $_mem[$start]           =  memory_get_usage();
//    }
//    return null;
}
/**
 * 添加和获取页面Trace记录
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别
 * @param boolean $record 是否记录日志
 * @return void|array
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
//    return Core\Think::trace($value,$label,$level,$record);
}
/**
 * 实例化模型类 格式 [资源://][模块/]模型
 * @param string $name 资源地址
 * @param string $layer 模型层名称
 * @return \Tsy\Library\Model
 */
function D($name='',$layer='') {
    if(empty($name)) return new Tsy\Library\Model;
    static $_model  =   array();
    $layer          =   $layer? : 'Model';
    if(isset($_model[$name.$layer]))
        return $_model[$name.$layer];
    $class          =   parse_res_name($name,$layer);
    if(class_exists($class)) {
        $model      =   new $class(basename($name));
    }elseif(false === strpos($name,'/')){
        // 自动加载公共模块下面的模型
        if(!C('APP_USE_NAMESPACE')){
            import('Common/'.$layer.'/'.$class);
        }else{
            $class      =   '\\Common\\'.$layer.'\\'.$name.$layer;
        }
        $model      =   class_exists($class)? new $class($name) : new Tsy\Library\Model($name);
    }else {
        L('D方法实例化没找到模型类'.$class,LOG_ERR);
        $model      =   new Tsy\Library\Model(basename($name));
    }
    $_model[$name.$layer]  =  $model;
    return $model;
}

/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Tsy\Library\Model
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'Tsy\\Library\\Model';
    }
    $guid           =   (is_array($connection)?implode('',$connection):$connection).$tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    return $_model[$guid];
}

/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($class, $baseUrl = '', $ext=EXT) {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut     = explode('/', $class);
    if (empty($baseUrl)) {
        if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
            //加载当前模块的类库
            $baseUrl = MODULE_PATH;
            $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
        }elseif ('Common' == $class_strut[0]) {
            //加载公共模块的类库
            $baseUrl = COMMON_PATH;
            $class   = substr($class, 7);
        }elseif (in_array($class_strut[0],array('Think','Org','Behavior','Com','Vendor')) || is_dir(LIB_PATH.$class_strut[0])) {
            // 系统类库包和第三方类库包
            $baseUrl = LIB_PATH;
        }else { // 加载其他模块的类库
            $baseUrl = APP_PATH;
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl    .= '/';
    $classfile       = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)&&file_exists($classfile)) {
        // 如果类不存在 则导入类库文件
        return require($classfile);
    }
    return null;
}

function db_connect($linkNum='',$config=[],$force){
    static $_dbs=[];
//    需要监听数据库链接的最后动作时间，如果最后动作时间超时
}

/**
 * 解析资源地址并导入类库文件
 * 例如 module/controller addon://module/behavior
 * @param string $name 资源地址 格式：[扩展://][模块/]资源名
 * @param string $layer 分层名称
 * @param integer $level 控制器层次
 * @return string
 */
function parse_res_name($name,$layer,$level=1){
    if(strpos($name,'://')) {// 指定扩展资源
        list($extend,$name)  =   explode('://',$name);
    }else{
        $extend  =   '';
    }
    if(strpos($name,'/') && substr_count($name, '/')>=$level){ // 指定模块
        list($module,$name) =  explode('/',$name,2);
    }else{
        $Model = process_queue('controller','get');
        $module =   $Model[0] ? $Model[0] : '' ;
    }
    $array  =   explode('/',$name);
    if(!C('APP_USE_NAMESPACE')){
        $class  =   parse_name($name, 1);
        import($module.'/'.$layer.'/'.$class.$layer);
    }else{
        $class  =   $module.'\\'.$layer;
        foreach($array as $name){
            $class  .=   '\\'.parse_name($name, 1);
        }
        // 导入资源类库
        if($extend){ // 扩展资源
            $class      =   $extend.'\\'.$class;
        }
    }
    return $class.$layer;
}

/**
 * 参数分组
 * @param array $Group 分组配置
 * @param array $Params 需要分组的参数
 * @param bool $Kv 传入的是否是KV映射
 * @return array
 */
function param_group(array $Group,array $Params,$Kv=false){
    if(!$Kv){
        $T = $Group;
        $Group=[];
        foreach ($T as $k=>$v){// $BasicFields=['Name','Disable','Virtual','Memo','ProducerDicID','Number'];改为 Name =》‘BasicFields’,'Disable'=》‘BasicFields’,'Virtual'=》‘BasicFields’,'Memo'=》‘BasicFields’,'ProducerDicID'=》‘BasicFields’,'Number'=》‘BasicFields’
            foreach ($v as $p){
                $Group[$p]=$k;
            }
        }
    }
    $Data=[[]];
    foreach ($Params as $K=>$V){
        if(isset($Group[$K])){
            if(!isset($Data[$Group[$K]])){
                $Data[$Group[$K]]=[];
            }
            $Data[$Group[$K]][$K]=$V;
        }else{
            $Data[0][$K]=$V;
        }
    }
    return $Data;
}