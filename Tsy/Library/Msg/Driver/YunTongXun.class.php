<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/28
 * Time: 11:05
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\Driver\YunTongXun\CCPRestSDK;

class YunTongXun implements \Tsy\Library\Msg\MsgIFace
{
    /**
     * @var CCPRestSDK $handle
     */
    protected $handle;
    protected $config=[
        'SERVER_IP'=>'sandboxapp.cloopen.com',
        'SERVER_PORT'=>'8883',
        'SOFT_VERSION'=>'2013-12-26',
        'ACCOUNT_SID'=>'',
        'ACCOUNT_TOKEN'=>'',
        'APP_ID'=>''
    ];
    function __construct($config=[])
    {
        if($config)
            $this->config = array_merge($this->config,$config);
        foreach ($this->config as $K=>$V){
            if(!$V){
                $this->config[$K]=C('MSG.YUNTONGXUN.'.$K);
            }
        }
        $this->handle=new CCPRestSDK($this->config['SERVER_IP'],$this->config['SERVER_PORT'],$this->config['SOFT_VERSION']);
        $this->handle->setAccount($this->config['ACCOUNT_SID'],$this->config['ACCOUNT_TOKEN']);
        $this->handle->setAppId($this->config['APP_ID']);
    }

    function send($To,$Content){}

    /**
     * @param $To
     * @param $Params
     * @param $TemplateID
     * @return bool
     */
    function RemoteTemplateSend($To,$Params,$TemplateID){
        if(is_string($To)){
            $To = explode(',',$To);
        }
        foreach ($To as $Number){
            if(strlen($Number)!=11||!is_numeric($Number)){
                L('Number:'.$Number.'.Error');
                return false;
            }
        }
        $Rs = $this->handle->sendTemplateSMS(implode(',',$To),$Params,$TemplateID);
        if(null==$Rs){
            return false;
        }elseif(0!=$Rs->statusCode){
            L($Rs->statusCode.";".$Rs->statusMsg);
            return false;
        }else{
            return true;
        }
    }
    function LocalTemplateSend($To,$Params,$Template){
        return false;
    }
    function receive(){}

}