<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:36
 */
function load_module_config($module){
    static $CurrentModel = '';
//    \Tsy\Library\Aop::exec(__FUNCTION__,\Tsy\Library\Aop::$AOP_BEFORE,func_get_args());
    if($CurrentModel==$module){
        return ;
    }else{
        //清空配置缓存
        C(false,false);
        //加载公共配置
        C($GLOBALS['Config']);
        $ModuleConfigPath = APP_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Config/';
//        加载项目配置文件,http模式则加载http.php,swoole模式则加载swoole.php
        C(load_config($ModuleConfigPath.'config.php'));
        E(load_config($ModuleConfigPath.'error.php'));
        !APP_DEBUG or C(load_config($ModuleConfigPath.'debug.php'));
        C(load_config($ModuleConfigPath.strtolower(APP_MODE).'.php'));
        !APP_DEBUG or C(load_config($ModuleConfigPath.strtolower(APP_MODE).'_debug.php'));
        $CurrentModel=$module;
//        为SaaS模式的数据库切换做准备
        if(in_array($module,C('SAAS_MODULE'))){
            C('DB_PREFIX',session('DB_PREFIX').C('DB_PREFIX'));
        }
    }
//    \Tsy\Library\Aop::exec(__FUNCTION__,\Tsy\Library\Aop::$AOP_AFTER,func_get_args());
}

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
    static $_config=[];
    // 无参数时获取所有
//    if(!isset($_config)){$_config=[];}
    if(false===$name&&$value===false){
        $_config=[];
    }

    if (empty($name)) {
        return $_config;
    }

    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value)){
            $value=isset($_config[$name[0]])?$_config[$name[0]]:$default;
            if(is_array($value)){
                for($i=1;$i<count($name);$i++){
                    $value=isset($value[$name[$i]])?$value[$name[$i]]:$default;
                }
            }
            return $value;
//            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        }
        $_config[$name[0]][$name[1]] = $value;
        return null;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return null;
    }
    return null; // 避免非法参数
}

/**
 * 加载配置文件 支持格式转换 仅支持一级配置
 * @param string $file 配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 * @return array
 */
function load_config($file,$parse='php'){
    if(!file_exists($file)){
        //从模块内的配置文件开始查找，如果文件存在则替换，否则return[]
        $info = pathinfo($file);
        if($info['dirname']==='.'&&$file!=($info['dirname'].DIRECTORY_SEPARATOR.$info['basename'])){
            foreach ([APP_PATH.DIRECTORY_SEPARATOR.'Common/Config'] as $dir){
                if(file_exists($dir.DIRECTORY_SEPARATOR.$info['basename'])){
                    $file=$dir.DIRECTORY_SEPARATOR.$info['basename'];
                }
            }
        }
        if(!file_exists($file)){
            return [];
        }
    }
    $ext  = pathinfo($file,PATHINFO_EXTENSION);
    switch($ext){
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml':
            return (array)simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
        default:
            if(function_exists($parse)){
                return $parse($file);
            }else{
                L(E('_NOT_SUPPORT_').':'.$ext);
            }
    }
}

function controller($i,$data,$mid='',$layer="Controller"){
//    \Tsy\Library\Aop::exec(__FUNCTION__,\Tsy\Library\Aop::$AOP_BEFORE,func_get_args());
    static $LoginRequire=null;
    /**
     * SplQueue
     */
    static $controllers=[];
//    if($layer=='Object'){
//        $a=1;
//    }
    if(null===$LoginRequire){
        $LoginRequire=C('LOGIN_REQUIRE');
    }
    if(is_array($data)){
        $_POST=array_merge($_POST,$data);
        $_REQUEST = array_merge($_REQUEST,$_GET,$_POST);
    }
//    切换mid,如果当前环境下存在mid则
    $mid or $mid=isset($_POST['_mid'])?$_POST['_mid']:'';
//    if(!$mid){$mid=$_POST['_mid'];}
    $ModuleClassAction=explode('/',$i);
    $MCACount = count($ModuleClassAction);
    if($MCACount==2){
        list($C,$A)=$ModuleClassAction;
        $M=DEFAULT_MODULE;
    }elseif($MCACount==3){
        list($M,$C,$A)=$ModuleClassAction;
    }else{
        L($i.'错误',LOG_ERR);
        return null;
    }
    //TODO 检查是否需要登录
    if(is_callable('is_login')){
        $LoginCheck = false;
        if(in_array($M,$LoginRequire )){
            //整个模块都需要登录
            $LoginCheck=true;
        }elseif (isset($LoginRequire[$M])&&is_array($LoginRequire[$M])){
            if(in_array($C,$LoginRequire[$M])){
                //整个类下面的都需要
                $LoginCheck=true;
            }elseif(isset($LoginRequire[$M][$C])&&is_array($LoginRequire[$M][$C])){
                if(in_array($A,$LoginRequire[$M][$A] )){
                    $LoginCheck=true;
                }
            }
        }else{
//            这个模块不需要登录验证
        }
        if($LoginCheck&&call_user_func('is_login')){

        }else{
            return E('NOT_LOGIN');
        }
    }
    //存储调用之前的配置参数
//    $LastController = process_queue('controller','get');
//    $Config = C();
//    process_queue('controller','push',[$M,$C,$A]);
//    $_GET['_m']=$M;$_GET['_a']=$A;$_GET['_c']=$C;
    $controllers[]=$M;
    try{
        current_MCA($M,$C,$A,$layer);
        load_module_config($M);
//    如果要切换配置需要先还原Common配置再加载需要加载的模块配置文件
        $ClassName = implode('\\',[$M,$layer,$C.$layer]);
        if(!class_exists($ClassName)){
            $ClassName=str_replace($C,'Empty',$ClassName);
            if(!class_exists($ClassName)){
                L($C.'类不存在',LOG_ERR);
                return null;
            }
        }
        $result = '';//返回结果
        $Class = new $ClassName();
        if(!method_exists($Class,$A)){
            //方法不存在
            if(method_exists($Class,'_empty')){
                $result = call_user_func_array([$Class,'_empty'],[$i,$data]);
            }elseif(method_exists($Class,'__call')){
                $result = call_user_func_array([$Class,$A],$data);
            }else{
                L($A.'方法不存在',LOG_ERR);
                return null;
            }
            return $result;
        }
        //前置检测，其返回结果将跟Data部分合并。如果返回false则不会再调用，此部分的返回内容为数组时会合并
        if(method_exists($Class,'_before_'.$A)){
            $before = invokeClass($Class,'_before_'.$A,$data);
        }
        if(isset($before)&&is_array($before)){
            $data = array_merge($data,$before);
        }
        $result = invokeClass($Class,$A,$data);
        //后置检测
        if(method_exists($Class,'_after_'.$A)){
            $after = invokeClass($Class,'_after_'.$A,$data);
        }
        if(isset($after)&&is_array($after)&&is_array($result)){
            $result = array_merge($result,$after);
        }
        $Class=null;
    }catch (Exception $e){
        var_dump($e);
    }
//    判断配置文件是否是当前模块配置文件，如果不是则加载当前模块配置文件

//    process_queue('controller','pop');
    array_pop($controllers);
    if($lastModule = end($controllers)){
        load_module_config($lastModule);

    }
    return $result;
}

/**
 * 获取当前正在执行的模块/控制器/方法名称
 * @param string $M
 * @param string $C
 * @param string $A
 * @return mixed|string
 */
function current_MCA($M='',$C='',$A='',$L=''){
    static $MCA=[];
    if($M&&$C&&$A){$MCA=['M'=>$M,'C'=>$C,'A'=>$A,'L'=>$L];}
    return isset($MCA[$M])?$MCA[$M]:'';
}
function invokeClass($Class,$A,$data){
    \Tsy\Library\Aop::exec(__FUNCTION__,\Tsy\Library\Aop::$AOP_BEFORE,$data);
    $result = '';
    //方法存在时
    $ReflectMethod = new ReflectionMethod($Class,$A);
//     获取函数的注释，查找是否有标注需要调用前置函数、后置函数，如果有且函数存在则调用
    //获取方法参数
    if($ReflectMethod->isPublic()){
//        是否需要参数绑定
        $args = [];
        if($ReflectMethod->getNumberOfParameters()>0){
//            $Parameters = $ReflectMethod->getParameters();
            foreach ($ReflectMethod->getParameters() as $Param){
                $ParamName=$Param->getName();
                //必填参数未传入完整
                if($A=='gets'&&substr($ParamName,-3)=='IDs'&&!isset($data[$ParamName])){
                    $ParamName='IDs';
                }
                if(isset($data[$ParamName])){
//                    if(!(is_string($data[$ParamName])&&strlen($data[$ParamName])>0)){
//                        L($ParamName.':参数为空',LOG_ERR);
//                        return null;
//                    }else{
                        $args[$ParamName]=$data[$ParamName];
//                    }
                }elseif($Param->isDefaultValueAvailable()){
                    $args[$ParamName]=$Param->getDefaultValue();
                }else{
                    L($ParamName.':必填参数未传入完整',LOG_TIP);
                    return false;
                }
            }
            \Tsy\Library\Aop::exec('dispatch',\Tsy\Library\Aop::$AOP_BEFORE,$args);
            \Tsy\Library\Aop::exec('dispatch_'.get_class($Class).'::'.$A,\Tsy\Library\Aop::$AOP_BEFORE,$args);
            $result = $ReflectMethod->invokeArgs($Class,$args);
            \Tsy\Library\Aop::exec('dispatch_'.get_class($Class).'::'.$A,\Tsy\Library\Aop::$AOP_AFTER,$result);
        }else{
            \Tsy\Library\Aop::exec('dispatch',\Tsy\Library\Aop::$AOP_BEFORE,$args);
            \Tsy\Library\Aop::exec('dispatch_'.get_class($Class).'::'.$A,\Tsy\Library\Aop::$AOP_BEFORE,$args);
            $result = $ReflectMethod->invoke($Class);
            \Tsy\Library\Aop::exec('dispatch_'.get_class($Class).'::'.$A,\Tsy\Library\Aop::$AOP_AFTER,$result);
        }
//        判断result内容
    }else{
        L('方法不是公共方法',LOG_ERR);
    }
    return $result;
}

/**
 * @param $Code
 * @return bool|float|int|mixed|string
 */
function E($Code){
    static $_arrays=[];
    if(is_array($Code)){
        $_arrays=array_merge($_arrays,$Code);
    }elseif(is_scalar($Code)){
        return isset($_arrays[$Code])?$_arrays[$Code]:$Code;
    }
    return false;
}

function L($msg = false,$Type=6,$trace=''){
    static $_log=[];
    if($msg){
        if(isset($_log[$Type])){
            $_log[$Type]=$msg;
        }else{
            $_log[$Type]=$msg;
        }
        //TODO 完善log函数
        if('swoole'==APP_MODE_LOW&&!ob_get_level()){
            echo is_array($msg)?json_encode($msg,JSON_UNESCAPED_UNICODE):$msg,"\r\n";
        }elseif(APP_DEBUG){
            echo is_array($msg)?json_encode($msg,JSON_UNESCAPED_UNICODE):$msg,"\r\n";
        }
        return $msg;
//        echo is_string($msg)?$msg:json_encode($msg,JSON_UNESCAPED_UNICODE),"\r\n";
    }elseif(false===$msg){
        return $Type===0?$_log:$_log[$Type];
    }elseif(null===$msg&&$Type===null){
        $_log=[];
    }
    return $msg;
}

/**
 * 创建初始化环境和缓存
 * 创建缓存支持指定模块来创建
 * @param array $Models
 */
function build_cache($Models=[]){
    $Builder=new \Tsy\Plugs\Build\Build();
    if(!$Models){
        foreach (scandir(APP_PATH) as $dir){
            if(in_array($dir,['.','..','Common'])||!is_dir(APP_PATH.DIRECTORY_SEPARATOR.$dir)||APP_PATH.DIRECTORY_SEPARATOR.$dir==realpath(RUNTIME_PATH)){
                continue;
            }
            $Models[]=$dir;
        }
    }
    foreach ($Models as $dir){
        $path = APP_PATH.DIRECTORY_SEPARATOR.$dir;
        //Switch the Module Config
        load_module_config($dir);
        $Builder->ModulePath=$path.DIRECTORY_SEPARATOR;
        $Builder->ModuleName= $dir;
        foreach (['db','controller','model','object'] as $conf){
            if(!file_exists($path.DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.$conf.'.php')){
                call_user_func([$Builder,'build'.ucfirst($conf).'Config']);
            }
        }
    }
}

/**
 * 目录遍历
 * @param string $dir
 * @param callable|null $dir_callback
 * @param callable|null $file_callback
 */
function each_dir(string $dir,callable $dir_callback=null,callable $file_callback=null){
    if(is_dir($dir)){
        foreach (scandir($dir) as $path){
            if(!in_array($path, ['.','..'])){
                $path = $dir.DIRECTORY_SEPARATOR.$path;
                if(is_dir($path)){
                    if(is_callable($dir_callback)&&false===call_user_func($dir_callback,$path)){
//                        call_user_func($dir_callback,$path);
                        return false;
                    }
                    each_dir($path, $dir_callback, $file_callback);
                }else{
                    if(is_callable($file_callback)&&false===call_user_func($file_callback,$path)){
//                        call_user_func($file_callback,$path);
                        return false;
                    }
                }
            }
        }
    }
}

/**
 * 一个线程内有效的队列
 * @param $key
 * @param string $op
 * @param string $value
 * @return bool|mixed
 */
function process_queue($key,$op='push',$value=''){
    static $queue=[];
    isset($queue[$key]) or $queue[$key]=[];
    switch (strtolower($op)){
        case 'pop':
            return array_pop($queue[$key]);
            break;
        case 'push':
            $queue[$key][]=$value;
            break;
        case 'get':
            return end($queue[$key]);
            break;
        case 'getAll':
            return $queue[$key];
            break;
    }
    return true;
}