<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/2/14
 * Time: 9:33
 */

namespace Tsy\Plugs\Auth;


    /**
     * 权限认证类
     * 功能特性：
     * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
     *      $auth=new Auth();  $auth->check('规则名称','用户id')
     * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
     *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
     *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
     * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
     *
     * 4，支持规则表达式。
     *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
     */
//数据库
class Auth{
    //默认配置
    protected $db_prefix='';
    protected $_config = array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'group_access', //用户组明细表
        'AUTH_RULE' => 'auth_rule', //权限规则表
        'AUTH_USER' => 'members'//用户信息表
    );
    public function __construct($DbPrefix='') {
        if(!$DbPrefix){
            $DbPrefix=C('DB_PREFIX');
        }
        $this->db_prefix=$DbPrefix;
        if (C('AUTH_CONFIG')) {
            //可设置配置项 AUTH_CONFIG, 此配置项为数组。
            $this->_config = array_merge($this->_config, C('AUTH_CONFIG'));
        }
        $this->_config['AUTH_GROUP']=$this->db_prefix.$this->_config['AUTH_GROUP'];
        $this->_config['AUTH_GROUP_ACCESS']=$this->db_prefix.$this->_config['AUTH_GROUP_ACCESS'];
        $this->_config['AUTH_RULE']=$this->db_prefix.$this->_config['AUTH_RULE'];
        $this->_config['AUTH_USER']=$this->db_prefix.$this->_config['AUTH_USER'];
    }
    //获得权限$name 可以是字符串或数组或逗号分割， uid为 认证的用户id， $or 是否为or关系，为true是， name为数组，只要数组中有一个条件通过则通过，如果为false需要全部条件通过。
    public function check($name, $uid, $relation='or') {
        if (!$this->_config['AUTH_ON'])
            return true;
        $authList = $this->getAuthList($uid);
        if (is_string($name)) {
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        $list = array(); //有权限的name
        foreach ($authList as $val) {
            if (in_array($val, $name))
                $list[] = $val;
        }
        if ($relation=='or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation=='and' and empty($diff)) {
            return true;
        }
        return false;
    }
    //获得用户组，外部也可以调用
    public function getGroups($uid) {
        static $groups = array();
        if (isset($groups[$uid]))
            return $groups[$uid];
        $user_groups = M()->table($this->_config['AUTH_GROUP_ACCESS'] . ' a')->where("a.UID='$uid' and g.Status='1'")->join($this->_config['AUTH_GROUP']." g on a.GroupID=g.ID")->select();
        $groups[$uid]=$user_groups?$user_groups:array();
        return $groups[$uid];
    }
    //获得权限列表
    protected function getAuthList($uid) {
        static $_authList = array();
        if (isset($_authList[$uid])) {
            return $_authList[$uid];
        }
        if(isset($_SESSION['_AUTH_LIST_'.$uid])){
            return $_SESSION['_AUTH_LIST_'.$uid];
        }
        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = array();
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['Rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid] = array();
            return array();
        }
        //读取用户组所有权限规则
        $map=array(
            'ID'=>array('in',$ids),
            'Status'=>1
        );
        $rules = M()->table($this->_config['AUTH_RULE'])->where($map)->select();
        //循环规则，判断结果。
        $authList = array();
        foreach ($rules as $r) {
            if (!empty($r['Condition'])) {
                //条件验证
                $user = $this->getUserInfo($uid);
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $r['Condition']);
                //dump($command);//debug
                @(eval('$condition=(' . $command . ');'));
                if (isset($condition)&&$condition) {
                    $authList[] = $r['Name'];
                }
            } else {
                //存在就通过
                $authList[] = $r['Name'];
            }
        }
        $_authList[$uid] = $authList;
        if($this->_config['AUTH_TYPE']==2){
            //session结果
            $_SESSION['_AUTH_LIST_'.$uid]=$authList;
        }
        return $authList;
    }
    //获得用户资料,根据自己的情况读取数据库
    protected function getUserInfo($uid) {
        static $userinfo=array();
        if(!isset($userinfo[$uid])){
            $userinfo[$uid]=M()->table($this->_config['AUTH_USER'])->find($uid);
        }
        return $userinfo[$uid];
    }
}