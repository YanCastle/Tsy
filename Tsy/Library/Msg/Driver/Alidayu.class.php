<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/9/5
 * Time: 22:07
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\MsgIFace;

class Alidayu implements MsgIFace
{

    protected $handle;
    protected $client;
    protected $config=[
        'APP_KEY'=>'',
        'SECRET_KEY'=>'',
        'SIGN_NAME'=>'身份验证'
    ];
    public function __construct($config=[])
    {
        if($config)
            $this->config = array_merge($this->config,$config);
        foreach ($this->config as $K=>$V){
            if(!$V){
                $this->config[$K]=C('MSG.ALIDAYU.'.$K);
            }
        }

        /**
         * 定义常量开始
         * 在include("TopSdk.php")之前定义这些常量，不要直接修改本文件，以利于升级覆盖
         */
        /**
         * SDK工作目录
         * 存放日志，TOP缓存数据
         */
        if (!defined("TOP_SDK_WORK_DIR"))
        {
            define("TOP_SDK_WORK_DIR", RUNTIME_PATH);
        }

        /**
         * 是否处于开发模式
         * 在你自己电脑上开发程序的时候千万不要设为false，以免缓存造成你的代码修改了不生效
         * 部署到生产环境正式运营后，如果性能压力大，可以把此常量设定为false，能提高运行速度（对应的代价就是你下次升级程序时要清一下缓存）
         */
        if (!defined("TOP_SDK_DEV_MODE"))
        {
            define("TOP_SDK_DEV_MODE", true);
        }

        if (!defined("TOP_AUTOLOADER_PATH"))
        {
            define("TOP_AUTOLOADER_PATH", __DIR__.DIRECTORY_SEPARATOR.'Alidayu/');
        }
        spl_autoload_register('Tsy\Library\Msg\Driver\Alidayu::autoload');
        $this->handle = new \TopClient($this->config['APP_KEY'],$this->config['SECRET_KEY']);
        $this->handle->format='json';
//        $this->handle = new \AlibabaAliqinFcSmsNumSendRequest();
    }

    /**
     * 类库自动加载，写死路径，确保不加载其他文件。
     * @param string $class 对象类名
     * @return void
     */
    public static function autoload($class) {
        $name = $class;
        if(false !== strpos($name,'\\')){
            $name = strstr($class, '\\', true);
        }

        $filename = TOP_AUTOLOADER_PATH."/top/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }

        $filename = TOP_AUTOLOADER_PATH."/top/request/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }

        $filename = TOP_AUTOLOADER_PATH."/top/domain/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }

        $filename = TOP_AUTOLOADER_PATH."/aliyun/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }

        $filename = TOP_AUTOLOADER_PATH."/aliyun/request/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }

        $filename = TOP_AUTOLOADER_PATH."/aliyun/domain/".$name.".php";
        if(is_file($filename)) {
            include $filename;
            return;
        }
    }
    /**
     * 使用内容发送
     * @param $To
     * @param $Content
     * @return mixed
     */
    function send($To, $Content)
    {
        // : Implement send() method.
    }

    /**
     * 使用服务商模板发送
     * @param $To
     * @param $Params
     * @param $TemplateID
     * @return mixed
     */
    function RemoteTemplateSend($To, $Params, $TemplateID)
    {
        // : Implement RemoteTemplateSend() method.
        if(is_string($To)){
            $To = explode(',',$To);
        }
        foreach ($To as $Number){
            if(strlen($Number)!=11||!is_numeric($Number)){
                L('Number:'.$Number.'.Error');
                return false;
            }
        }
        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setSmsParam(json_encode($Params,JSON_UNESCAPED_UNICODE));
        $req->setRecNum(implode(',',$To));
        $req->setSmsTemplateCode($TemplateID);
        $req->setSmsType('normal');
        $req->setSmsFreeSignName($this->config['SIGN_NAME']);
        $rs = $this->handle->execute($req);
        if(isset($rs['result']['success'])&&$rs['result']['success']){
            return true;
        }else{
            L('Msg::SMS_ALIDAYU:'.$rs['sub_msg'],LOG_ERR);
            return false;
        }
    }

    /**
     * 使用本地模板发送
     * @param $To
     * @param $Params
     * @param $Template
     * @return mixed
     */
    function LocalTemplateSend($To, $Params, $Content)
    {
//         : Implement LocalTemplateSend() method.
        return false;
    }

    /**
     * 接受内容
     * @return mixed
     */
    function receive()
    {
        // TODO: Implement receive() method.
    }
}