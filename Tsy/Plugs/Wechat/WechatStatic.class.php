<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/2/9
 * Time: 18:44
 */

namespace Wechat;

/**
 * 微信静态回掉，用户success匹配时的常用回掉方法
 * Class WechatStatic
 * @package Tsy\Wechat
 */
class WechatStatic
{
    static function getDkf($status=false){
        return [
            'MsgTypeID'=>1,
            'Content'=>'fwefwe'
        ];
    }
    static function toKf($Account){

    }
}