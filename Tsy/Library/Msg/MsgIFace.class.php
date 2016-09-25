<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/28
 * Time: 11:06
 */

namespace Tsy\Library\Msg;


interface MsgIFace
{
    function __construct($config=[]);

    /**
     * 使用内容发送
     * @param $To
     * @param $Content
     * @return mixed
     */
    function send($To,$Content);

    /**
     * 使用服务商模板发送
     * @param $To
     * @param $Params
     * @param $TemplateID
     * @return mixed
     */
    function RemoteTemplateSend($To,$Params,$TemplateID);

    /**
     * 使用本地模板发送
     * @param $To
     * @param $Params
     * @param $Template
     * @return mixed
     */
    function LocalTemplateSend($To,$Params,$Content);

    /**
     * 接受内容
     * @return mixed
     */
    function receive();
}