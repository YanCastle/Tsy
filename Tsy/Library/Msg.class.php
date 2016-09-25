<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/7/11
 * Time: 17:12
 */

namespace Tsy\Library;

/**
 * 封装消息通讯系统
 * @package Tsy\Library
 */
class Msg
{
    /**
     * @var \Tsy\Library\Msg\Msg $handler
     */
    static $handler;
    static function send($Method,$To,$Content,$Config=[]){
        self::$handler->load($Method,$Config);
        return self::$handler->send($To,$Content);
    }
    static function remoteTemplateSend($Method,$To,$Params,$TemplateID,$Config=[]){
        self::$handler->load($Method,$Config);
        return self::$handler->RemoteTemplateSend($To,$Params,$TemplateID);
    }
    static function localTemplateSend($Method,$To,$Params,$Template,$Config=[]){
        self::$handler->load($Method,$Config);
        return self::$handler->LocalTemplateSend($To,$Params,$Template);
    }
}