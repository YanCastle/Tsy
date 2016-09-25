<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/02
 * Time: 22:46
 */

namespace Tsy\Extend\User;


use Tsy\Library\Object;

class UserObject extends Object
{
    /**
     * 登陆
     * @param string $Account 账户名
     * @param string $PWD 账户密码
     * @param bool|int $GID 账户分组,账户规则编号 用于管理员登陆等识别
     * @return array|bool|mixed
     */
    function login($Account,$PWD,$GID=false){
//        TODO 逻辑
        $UID = 1;
        return $UID?$this->get($UID):false;
    }

    /**
     * 退出登陆
     * @return bool
     */
    function logout(){
        session(null);
        return true;
    }

    /**
     * 查找账户是否存在
     * @param string $Account 账户名称
     * @return array|bool
     */
    function findAccount(string $Account){
        return $Account?[
            'UID'=>1,
            'UN'=>'a',
        ]:false;
    }

    /**
     * 普通用户通过验证码重置密码
     * @param int $UID 用户编号
     * @param string $PWD 用户密码
     * @param string $Code 验证码
     * @param string $RePWD 重置密码验证
     * @return bool
     */
    function resetPWD(int $UID,string $PWD,string $Code,string $RePWD=''){
        return true;
    }

    /**
     * 用户注册
     * @param string $Account 账户名
     * @param string $PWD 账户密码
     * @param array $Params 注册时的其它参数
     * @return bool
     */
    function regist(string $Account,string $PWD,array $Params=[]){
        return true;
    }

    /**
     * 发送验证码
     * @param int $UID 要发送的用户编号
     * @param string $Way
     * @return mixed
     */
    function sendVerifyCode(int $UID,string $Way){
        return true;
    }
    function reLogin(){}
    function adminResetPWD($UID,$PWD){
        //验证权限
    }
    private function createVerifyCode(){}
    private function checkVerifyCode(){}
}