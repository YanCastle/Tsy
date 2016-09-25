<?php
namespace Tsy\Plugs\BaiDuYunWangPan;
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/12
 * Time: 20:17
 * @link https://github.com/houtianze/bypy
 */
class BaiDuYunWangPan
{
    ### Auth servers
    const GaeUrl = 'https://bypyoauth.appspot.com';
    const OpenShiftUrl = 'https://bypy-tianze.rhcloud.com';
    const HerokuUrl = 'https://bypyoauth.herokuapp.com';

    const GaeRedirectUrl = self::GaeUrl.'/auth';
    const GaeRefreshUrl = self::GaeUrl.'/refresh';

    const OpenShiftRedirectUrl = self::OpenShiftUrl.'/auth';
    const OpenShiftRefreshUrl = self::OpenShiftUrl.'/refresh';
    const HerokuRedirectUrl = self::HerokuUrl.'/auto';
    const HerokuRefreshUrl = self::HerokuUrl.'/refresh';

    static $AuthServerList=[
        [self::OpenShiftRedirectUrl,false,'正在刷新认证信息...'],
        [self::HerokuRedirectUrl,true,'正在刷新认证信息...'],
        [self::GaeRedirectUrl,false,'正在刷新认证信息...'],
    ];

    function __construct()
    {
        //引入PHP文件
        include __DIR__.DIRECTORY_SEPARATOR.'libs/BaiduPCS.class.php';
    }
    function getAccessToken($AuthorizationCode){
        foreach (self::$AuthServerList as $row){

        }
    }
    private function _get(){}
}