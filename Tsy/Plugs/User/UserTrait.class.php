<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/9/22
 * Time: 17:41
 */

namespace Tsy\Plugs\User;


use Tsy\Library\Msg;

trait UserTrait
{
//    protected $
    public $allowReg=true;
    public $LoginView='User';
    protected $_map=[
        'Account'=>'Account',
        'PWD'=>'PWD',
    ];
    /**
     * 登录时允许使用哪些字段作为账户名登录
     * @var array
     */
    protected $LoginAccountFields=['Account'];
    /**
     * 用户注册
     * @param string $Account 注册帐号
     * @param string $PWD 注册密码
     * @param array $Properties 其他属性
     * @return UserObject
     */
    function reg(string $Account,string $PWD,array $Properties=[]){
        if(!$this->allowReg){
            return '禁止注册';
        }
//        检测允许被用作登录账户的账户名是否重复
        if($this->checkAccount($Account)){
            return '账号已使用';
        }
        $data=array_merge([
            'Account'=>$Account,
            'PWD'=>$this->password($PWD)
        ],$Properties);
        $data['data']=$data;
        return invokeClass($this,'add',$data);
    }

    /**
     * 用户登录
     * @param string $Account 账户名
     * @param string $PWD 账户密码
     * @return UserObject
     */
    function login(string $Account,string $PWD){
        $User = M($this->LoginView)->where(['Account'=>$Account])->getField('UID,PWD',true);
        if(false!==$User){
            foreach ($User as $UID=>$Hash){
                if($this->password($PWD,$Hash)){
                    return $this->loginSuccess($User);
                }
            }
        }
        return '账户名或密码错误';
    }
    protected function password($PWD,$Hash=''){
        return $Hash?password_verify($PWD,$Hash):password_hash($PWD,PASSWORD_DEFAULT);
    }
    /**
     * 登录成功的处理逻辑
     * @param $User
     * @return mixed
     */
    protected function loginSuccess($User){
        session('UID',$User['UID']);
//        session('GIDs',)
        return $this->get($User['UID']);
    }
    /**
     * 退出登录
     */
    function logout(){
        session(null);
        return true;
    }

    /**
     * 查找我的账户
     * @param string $Account 账户名称
     * @return array {'Email':"","Phone":"",'Account':"","UID":1}
     */
    function findAccount(string $Account){
        return M($this->LoginView)->where(array_fill_keys($this->LoginAccountFields,$Account))->getField('UID');
    }

    /**
     * 重置密码
     * @param int $UID 用户编号
     * @param string $PWD 新密码
     * @param string $Code 验证码或旧密码 当用户权限为管理员时不需要Code参数，如果不是则需要提供Code验证码或者旧密码做验证
     */
    function resetPWD(string $Account,string $PWD,int $UID,string $Code=''){
        if(!$PWD||!$UID||!$Account){return '错误的账号密码';}
        if(session('UID')==$UID){
//            修改自己的密码
        }else
        if(session('UserAdmin')){
//            通过
        }else
        if(!$this->checkVerifyCode($Code,$UID)){
            return '验证码错误';
        }
        if($this->findAccount($Account)==$UID){
            $data[$this->_map['PWD']]=$this->password($PWD);
            return $this->save($UID,$data);
        }
        return '账号信息验证失败';
    }

    /**
     * 检查账户是否存在
     * @param string $Account 账户名称
     * @return bool 存在true,不存在false
     */
    function checkAccount(string $Account){
        return !!M($this->LoginView)->where(array_fill_keys($this->LoginAccountFields,$Account))->find();
    }

    /**
     * 自动登录
     * @param string $SID 自动登录的验证字符
     * @return UserObject|bool 成功返回用户对象，否则返回false
     */
    function reLogin(string $SID=''){
        if($UID = session('UID')){
            return $this->get($UID);
        }
        return false;
    }

    /**
     * 发送验证码
     * @param int $UID 用户名
     * @param int $Type 发送方式，默认为邮件，暂时支持邮件方式
     * @return bool true/false
     */
    function sendVerify(int $UID,string $Address,string $Type='Email'){
        return Msg::send('Email',$Address,$this->createVerifyCode($UID));
    }

    /**
     * @param int $UID UID
     * @param int $Expire 默认半个小时
     * @return string
     */
    private function createVerifyCode(int $UID,int $Expire=1800){
        // 添加过期时间控制
        $Code = '';
        for($i=0;$i<rand(5,10);$i++){
            $Code.=chr(rand(65,90));
        }
        cache('VerifyCode'.$UID,$Code,$Expire);
        return $Code;
    }

    /**
     * 验证验证码
     * @param string $Code
     * @param int $UID
     * @return bool
     */
    private function checkVerifyCode(string $Code,int $UID){
        return $Code==cache('VerifyCode'.$UID);
    }
}