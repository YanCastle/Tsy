<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:19
 */

namespace Tsy;


use Tsy\Library\Aop;
use Tsy\Library\Msg;
use Tsy\Library\Storage;
use Tsy\Mode\Swoole;

class Tsy
{
    protected static $class_map = [];
    /**
     * @var Mode $Mode
     */
    public static $Mode;
    function __construct()
    {
//        加载框架function函数库
        include_once TSY_PATH.DIRECTORY_SEPARATOR.'function.php';
        spl_autoload_register('Tsy\Tsy::autoload');
        register_shutdown_function('Tsy\Tsy::fatalError');
        set_error_handler('Tsy\Tsy::appError');
        set_exception_handler('Tsy\Tsy::appException');

    }
    function start(){
//        加载配置文件
//        Aop::exec(__METHOD__,Aop::$AOP_BEFORE);
        Storage::connect();
        $this->loadFunctions();//加载框架function和项目function
        
        $this->loadConfig();

        $GLOBALS['Config']=C();

//        分析配置，决定是http模式还是swoole模式
////        如果是http模式则实例化http类，如果是swoole模式则实例化swoole类
        if(file_exists(TSY_PATH.DIRECTORY_SEPARATOR.'Mode'.DIRECTORY_SEPARATOR.APP_MODE.'.class.php')){
            include_once TSY_PATH.DIRECTORY_SEPARATOR.'Mode'.DIRECTORY_SEPARATOR.APP_MODE.'.class.php';
        }else{
            die('MODE:'.APP_MODE.'不存在');
        }
        //扫描有哪些模块
        if(!defined('MODULES')){
            $Modules=[];
            foreach (scandir(APP_PATH) as $dir){
                if(!in_array($dir, ['.','..','Common','Runtime'])&&substr($dir, 0,1)!='.'&&is_dir($dir)){
                    $Modules[]=$dir;
                }
            }
            define('MODULES',implode(',',$Modules ));
        }
        if(defined('APP_BUILD')&&APP_BUILD)
            build_cache();
        if(APP_DEBUG){
            $this->build();
        }
//        $Session = new Session();
//        session_set_save_handler($Session,true);
//        开始实例化Mode类，进行初始化操作
        $ModeClassName = 'Tsy\\Mode\\'.parse_name(APP_MODE,1);
        if(class_exists($ModeClassName)){
            self::$Mode = new $ModeClassName();
        }else{
            die(APP_MODE.':模式不存在');
        }
//        加载模式处理类，开始模式处理
//        Aop::exec(__METHOD__,Aop::$AOP_AFTER);
        Msg::$handler =  new Msg\Msg();
        self::$Mode->start();
    }
    function loadConfig(){
        //因为涉及到多线程竞争同步的问题，所以C函数的内容必须是共享式的，
//        加载框架配置文件
        C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/config.php'));

        !CONFIG_SUFFIX  or  C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/config'.CONFIG_SUFFIX.'.php'));
        C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/'.strtolower(APP_MODE).'.php'));
        !CONFIG_SUFFIX  or  C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/'.strtolower(APP_MODE).CONFIG_SUFFIX.'.php'));
        E(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/error.php'));
//        加载调试配置
        !APP_DEBUG or C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/debug.php'));
//        加载项目配置文件,http模式则加载http.php,swoole模式则加载swoole.php
        C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.'config.php'));
        !CONFIG_SUFFIX  or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.'config'.CONFIG_SUFFIX.'.php'));
        !APP_DEBUG or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.'debug.php'));
        !APP_DEBUG && !CONFIG_SUFFIX or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.'debug'.CONFIG_SUFFIX.'.php'));
        !APP_MODE or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).'.php'));
        !CONFIG_SUFFIX or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).CONFIG_SUFFIX.'.php'));
        !APP_DEBUG or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).'_debug.php'));
        !APP_DEBUG&&!CONFIG_SUFFIX or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).CONFIG_SUFFIX.'_debug.php'));
        !defined('CONFIG_MODE') or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(CONFIG_MODE).'.php'));
        //开始加载aop配置文件
        $AopConfig = load_config(CONF_PATH.DIRECTORY_SEPARATOR.'aop.php');
        if(is_array($AopConfig)){
            foreach ($AopConfig as $point=>$config){
                if($config instanceof \Tsy\Library\IFace\Aop){
                    if(!is_callable($config->cmd)){
                        L($config->cmd.':不是可回调函数或方法');
                        break;
                    }
                    Aop::add($config->name,$config->cmd ,$config->when,$config->Async,$config->order );
                }
            }
        }
    }
    function loadFunctions(){
        $FunctionPath = TSY_PATH.'/Library/Functions';
        if(is_dir($FunctionPath))
            foreach (scandir($FunctionPath) as $path){
                if(!in_array($path,['.','..'])&&'php'==substr($path,-3)){
                    include ($FunctionPath.'/'.$path);
                }
            }
        $FunctionPath = APP_PATH.'/Common/Functions';
        if(is_dir($FunctionPath))
            foreach (scandir($FunctionPath) as $path){
                if(!in_array($path,['.','..'])&&'php'==substr($path,-3)){
                    include ($FunctionPath.'/'.$path);
                }
            }
    }
    static function autoload($class){
//        if(isset(self::$class_map[$class])){
//            include_once self::$class_map[$class];
        if(false !== strpos($class,'\\')) {
            //带命名空间的类
            if ('Tsy' == substr($class, 0, 3)){
                $file_path = dirname(TSY_PATH) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php';
            }else{
                $file_path = APP_PATH.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php';
                if(!file_exists($file_path)){
//                    TODO 需要检测文件是否存在，如果不存在的情况下要遍历Vendor目录检查是否有这个类的名称存在
//                    foreach ([TSY_PATH.DIR])
                    $ClassPath = explode("\\",$class);
                    $ClassName = array_pop($ClassPath);
                    $PlugsPath = implode(DIRECTORY_SEPARATOR,array_merge([TSY_PATH,'Plugs'],$ClassPath));
                    if(is_dir($PlugsPath)){
                        foreach ([
                                     $PlugsPath.'/'.$ClassName.'.class.php',
                                     $PlugsPath.'/'.$ClassName.'.php',
                                     $PlugsPath.'/'.$ClassName.'/Autoload.php',
                                 ] as $file_path){
                            if(file_exists($file_path)){
                                break;
                            }
                        }
                    }
                }
            }
            if(file_exists($file_path)){
                include($file_path);
            }
        }else{
            $ClassName = trim($class,'\\');
            $PlugsPath = TSY_PATH.'/Plugs/'.$ClassName;
            if(is_dir($PlugsPath)){
                foreach ([
                             $PlugsPath.'/'.$ClassName.'.class.php',
                             $PlugsPath.'/'.$ClassName.'.php',
                             $PlugsPath.'/'.$ClassName.'Autoload.php',
                         ] as $file_path){
                    if(file_exists($file_path)){
                        break;
                    }
                }
            }
            if(file_exists($file_path))
                include($file_path);
        }
    }
    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $trace              =   $e->getTrace();
        if('E'==$trace[0]['function']) {
            $error['file']  =   $trace[0]['file'];
            $error['line']  =   $trace[0]['line'];
        }else{
            $error['file']  =   $e->getFile();
            $error['line']  =   $e->getLine();
        }
        $error['trace']     =   $e->getTraceAsString();
        if (is_callable(\C('APP_EXCEPTION'))){
            call_user_func(\C('APP_EXCEPTION'),$e);
        }
        L($error,LOG_ERR);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
//                ob_end_clean();
                $errorStr = "$errstr ".$errfile." 第 $errline 行.";
                break;
            default:
                $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
                break;
        }
        if (is_callable(C('APP_ERROR'))){
            call_user_func_array(C('APP_ERROR'),[$errno,$errstr,$errfile,$errline]);
        }
        L($errorStr,LOG_ERR);
    }

    // 致命错误捕获
    static public function fatalError() {
        if ($e = error_get_last()) {
            switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    break;
            }
            if (is_callable(C('FATAL_ERROR'))){
                call_user_func(C('FATAL_ERROR'),$e);
            }
            L($e,LOG_ERR);
        }
    }
    function build(){
        foreach ([APP_PATH,CONF_PATH,RUNTIME_PATH,TEMP_PATH,] as $dir){
            if(!is_dir($dir)&&is_string($dir)){
                @mkdir($dir,0777,true);
                file_put_contents($dir.DIRECTORY_SEPARATOR.'README.md', '#');
            }
        }
        $ConfigFiles=[
            [
                CONF_PATH.DIRECTORY_SEPARATOR.'config.php',
                "[
                    'DB_TYPE'               =>  'mysql',     // 数据库类型
                    'DB_HOST'               =>  '', // 服务器地址
                    'DB_NAME'               =>  '',          // 数据库名
                    'DB_USER'               =>  '',      // 用户名
                    'DB_PWD'                =>  '',          // 密码
                    'DB_PORT'               =>  '3306',        // 端口
                    'DATA_CACHE_TYPE'=>'File',
                    'DATA_CACHE_TEMP_TYPE'=>'File',
                ]"
            ],[
                CONF_PATH.DIRECTORY_SEPARATOR.'swoole.php',
                "[
                    'SWOOLE'=>[
                        'AUTO_RELOAD_TIME'=>3,
                        'CONF'=>[
                            'daemonize' => !APP_DEBUG, //自动进入守护进程
                            'task_worker_num' => 5,//开启task功能，
                            'dispatch_mode '=>3,//轮询模式
                            'worker_num'=>5,
                        ],
                        'TABLE'=>[],
                        'LISTEN'=>[]
                    ],
                ]"
            ],[
                CONF_PATH.DIRECTORY_SEPARATOR.'http.php',
                "[
                    'HTTP'=>[
                        'DISPATCH'=>'',
                        'OUT'=>''
                    ]
                ]"
            ],
        ];
        //创建配置文件
        foreach ($ConfigFiles as $conf_file){
            if(isset($conf_file[0])&&!file_exists($conf_file[0])){
                if(isset($conf_file[1]))
                    file_put_contents($conf_file[0],"<?php\r\n return ".$conf_file[1].';');
            }
        }
        //创建模块目录，创建
        if(defined('MODULES')&&MODULES)
            foreach (explode(',',MODULES) as $Module){
            foreach (['Config','Model','Controller','Object'] as $dir){
                $dir_path = APP_PATH.DIRECTORY_SEPARATOR.$Module.DIRECTORY_SEPARATOR.$dir;
                if(!is_dir($dir_path)){
                    @mkdir($dir_path,0777,true);
                    file_put_contents($dir_path.DIRECTORY_SEPARATOR.'README.md', '#');
                }
            }
        }
        each_dir(APP_PATH,function($path){
            if(is_dir($path)&&strpos($path,DIRECTORY_SEPARATOR.'.')===false&&!file_exists($path).DIRECTORY_SEPARATOR.'README.md'){
                file_put_contents($path.DIRECTORY_SEPARATOR.'README.md','#');
            }
        });
    }
    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    static public function instance($class,$method='') {
        if(class_exists($class)){
            $o = new $class();
            if(!empty($method) && method_exists($o,$method))
                $o = call_user_func(array(&$o, $method));
            return $o;
        }
        return false;
    }
}
