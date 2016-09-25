<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/30
 * Time: 15:33
 */

namespace Tsy\Library\Traits;

/**
 * 账户管理
 * Class AccountTrait
 * @package Tsy\Library\Traits
 */
Trait AccountTrait
{

    function __construct()
    {
    }

    function login($Account,$PWD,$Code=''){

    }

    /**
     * 退出登录
     */
    function logout(){}

    /**
     * 注册账户
     */
    function register(){}

    /**
     * 支持同时使用OldPWD或者验证码找回密码
     * @param $UID
     * @param string $OldPWD
     * @param $NewPWD
     * @param string $Code
     */
    function resetPWD($UID,$OldPWD='',$NewPWD,$Code=''){}
    function findAccount($Account){}
}