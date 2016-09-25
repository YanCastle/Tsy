<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/5/14
 * Time: 12:19
 */

namespace Tsy\Plugs\Auth;

//权限管控控制器类，用于实现权限管控表的各种接口
class AuthController extends Auth
{
    public $Prefix; 
    function __construct($Prefix)
    {
        $this->Prefix=C('AUTO_PREFIX');
    }

    //用户组添加
    function addUserGroup($Title,$Rules,$Status=1){
        $GroupID=M($this->Prefix.'AuthGroup')->add(['Title'=>$Title,'Rules'=>$Rules,'Status'=>$Status]);
        return $GroupID?$GroupID:'用户添加失败';
    }
    //用户组删除
    function delUserGroup($ID){
        $Rs=M($this->Prefix.'AuthGroup')->where(['ID'=>$ID])->delete();
        return $Rs?$ID:'用户删除失败';
    }
    //用户组信息修改
    function saveUserGroup($ID,$Params){
        $Rs=M($this->Prefix.'AuthGroup')->where(['ID'=>$ID])->save($Params);
        return $Rs?$ID:'修改失败';
    }
    //规则添加
    function addRule($Name,$Title,$Condition,$Status=1){
        $RuleID=M($this->Prefix.'AuthRule')->add(['Name'=>$Name,'Title'=>$Title,'Status'=>$Status,'Condition'=>$Condition]);
        return $RuleID?$RuleID:'规则添加失败';
    }
    //规则修改
    function saveRule($RuleID,$Params){
        $RuleID=M($this->Prefix.'AuthRule')->where(['ID'=>$RuleID])->save($Params);
        return $RuleID?$RuleID:'规则修改失败';
    }
    //规则删除
    function delRule($RuleID){
        $Rs=M($this->Prefix.'AuthRule')->where(['ID'=>$RuleID])->delete();
        return $Rs?$RuleID:'规则删除失败';
    }
    //用户绑定到用户组
    function bindGroup($UID,$GroupID){
        $UID=M($this->Prefix.'AuthGroupAccess')->add(['UID'=>$UID,'GroupID'=>$GroupID]);
        return $UID?$UID:'用户组绑定失败';
    }
//    用户从用户组中解绑
    function unbindGroup($UID,$GroupID){
        $Rs=M($this->Prefix.'AuthGroupAccess')->where(['UID'=>$UID,'GroupID'=>$GroupID])->delete();
        return $Rs?$UID:'用户组解绑失败';
    }
    //权限验证
    function check($CName){

    }
    //用户登录
    function login($UN,$PWD){
//        用户名解析
        $MailCheck=strpos ($UN,'@');
        if($MailCheck){
            $User=M($this->Prefix.'User')->where(['Mail'=>$UN])->find();
            if($User['PWD']&&password_verify($PWD,$User['PWD'])){
                 session('UID',$User['UID']);
                return true;
            }else{
                return '登录名或密码错误';
            }
        }else{
            $User=M($this->Prefix.'User')->where(['Phone'=>['like',$UN],'_logic'=>'OR','UN'=>['like',$UN]])->find();
            if($User['PWD']&&password_verify($PWD,$User['PWD'])){
                session('UID',$User['UID']);
                return true;
            }else{
                return '登录名或密码错误';
            }
        }
    }

}