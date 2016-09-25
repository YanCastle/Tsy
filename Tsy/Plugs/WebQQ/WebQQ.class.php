<?php
namespace Tsy\Plugs\WebQQ;
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/02/21
 * Time: 18:44
 */
class WebQQ
{
    protected static $OFFLINE='100001';
    protected $Curl;
    protected $StartTime;
    protected $NickName="";
    protected $RedirectUrl = "";
    protected $ptwebqq='';
    public $QRCodePath = '';
    protected $vfwebqq='';
    protected $psessionid='';
    protected $Uin2QQNumberMap=[];
    protected $Categories=[];//用户分组
    protected $Friends=[];//用户好友
    protected $SelfUin='';
    protected $Self=[];
    protected $GroupList=[];//群信息
    protected $GroupMap=[];//群code与群ID对应关系
    protected $Dnamelist=[];//讨论组信息
    protected $UrlMaps = [
        'getFriendUin'=>'http://s.web2.qq.com/api/get_friend_uin2?tuin={$Uin}&type=1&vfwebqq={$vfwebqq}&t={$t}',
        'getUserFriend'=>'http://s.web2.qq.com/api/get_user_friends2',
        'getGroupNameList'=>'http://s.web2.qq.com/api/get_group_name_list_mask2',
        'getDiscussList'=>'http://s.web2.qq.com/api/get_discus_list?clientid={$clientid}&psessionid={$psessionid}&vfwebqq={$vfwebqq}&t={$t}',
        'getSelfInfo'=>'http://s.web2.qq.com/api/get_self_info2?t={$t}',
        'getOnlineBuddies'=>'http://d1.web2.qq.com/channel/get_online_buddies2?vfwebqq={$vfwebqq}&clientid={$clientid}&psessionid={$psessionid}&t={$t}',
        'getRecentList'=>'http://d1.web2.qq.com/channel/get_recent_list2',
        'getFriendInfo'=>'http://s.web2.qq.com/api/get_friend_info2?tuin={$tuin}&vfwebqq={$vfwebqq}&clientid={$clientid}&psessionid={$psessionid}&t={$t}',
        'getSingleLongNick'=>'http://s.web2.qq.com/api/get_single_long_nick2?tuin={$tuin}&vfwebqq={$vfwebqq}&t={$t}',
        'getGroupInfo'=>'http://s.web2.qq.com/api/get_group_info_ext2?gcode={$gcode}&vfwebqq={$vfwebqq}&t={$t}',
        'getDiscuInfo'=>'http://d1.web2.qq.com/channel/get_discu_info?did={$did}&vfwebqq={$vfwebqq}&clientid={$clientid}&psessionid={$psessionid}&t={$t}',
        'changeStatus'=>'http://d1.web2.qq.com/channel/change_status2?newstatus={$newstatus}&clientid={$clientid}&psessionid={$psessionid}&t={$t}',
        'poll'=>'http://d1.web2.qq.com/channel/poll2',
        'send'=>'http://d1.web2.qq.com/channel/send_buddy_msg2',
        'groupSend'=>'http://d1.web2.qq.com/channel/send_qun_msg2',
        'discSend'=>'http://d1.web2.qq.com/channel/send_discu_msg2'
    ];
    protected $q;
    protected $z=0;

    function __construct($QRCodePath,$QQNumber){
        if('png'==pathinfo($QRCodePath,PATHINFO_EXTENSION)&&is_writable(dirname($QRCodePath))){
            $this->QRCodePath=$QRCodePath;
        }
        $this->Curl = new Curl($QQNumber);
        $this->Curl->referer='http://w.qq.com/';
        $initUrl = 'https://ui.ptlogin2.qq.com/cgi-bin/login?daid=164&target=self&style=16&mibao_css=m_webqq&appid=501004106&enable_qlogin=0&no_verifyimg=1&s_url=http%3A%2F%2Fw.qq.com%2Fproxy.html&f_url=loginerroralert&strong_login=1&login_state=10&t=20131024001';
        $this->Curl->get($initUrl);
        $this->StartTime = time()*1000;
        if(preg_match('/ptwebqq\s[a-z\d]{64}/',$this->Curl->cookie(),$match)){
            $this->ptwebqq = substr($match[0],8);
        }
        $this->q=time();
        $this->q=($this->q - $this->q % 1000) / 1000;
        $this->q=$this->q % 10000 * 10000;
        $this->psessionid=cache('psessionid');
        if(!$this->psessionid){
            $this->autoLogin();
        }
    }
    function downQrcode($path=''){
        L('请扫码');
        if(!$path&&$this->QRCodePath){
            $path=$this->QRCodePath;
        }
        $this->QRCodePath=$path;
        $Content = $this->Curl->get('https://ssl.ptlogin2.qq.com/ptqrshow?appid=501004106&e=0&l=M&s=5&d=72&v=4&t=0.5462884965818375');
        if($Content){
            @unlink($path);
            file_put_contents($path,$Content);
        }
    }
    function login(){
        while(true){
            $action = rand(1000,99999);
            $html = $this->Curl->get("https://ssl.ptlogin2.qq.com/ptqrlogin?webqq_type=10&remember_uin=1&login2qq=1&aid=501004106&u1=http%3A%2F%2Fw.qq.com%2Fproxy.html%3Flogin2qq%3D1%26webqq_type%3D10&ptredirect=0&ptlang=2052&daid=164&from_ui=1&pttype=1&dumy=&fp=loginerroralert&action=0-0-{$action}&mibao_css=m_webqq&t=1&g=1&js_type=0&js_ver=10149&login_sig=&pt_randsalt=0");
            $html = explode(',',str_replace(['ptuiCB(',')','\''],'',$html));
            if($html[0]==0){
                //登陆成功
                $this->RedirectUrl = $html[2];
                $this->NickName = $html[5];
                $html = $this->Curl->get($html[2]);
                if(preg_match('/ptwebqq\s[a-z\d]{64}/',$this->Curl->cookie(),$match)){
                    $this->ptwebqq = substr($match[0],8);
                }
                return $this->NickName;
                break;
            }elseif($html[0]==65){
                //重新下载图片
                $this->downQrcode();
                return false;
            }
            sleep(1);
        }
    }
    function autoLogin(){
        $Rs = $this->Curl->get('http://s.web2.qq.com/api/getvfwebqq?ptwebqq='.$this->ptwebqq.'&clientid=53999199&psessionid=&t='.$this->StartTime);
        if($Rs&&$JSON = json_decode($Rs,true)){
            $this->vfwebqq = $JSON['result']['vfwebqq'];
        }
        $POST = ['r'=>'{"ptwebqq":"'.$this->ptwebqq.'","clientid":53999199,"psessionid":"","status":"online"}'];
        $this->Curl->referer='http://d1.web2.qq.com/proxy.html?v=20151105001&callback=1&id=2';
        $Rs = $this->Curl->post('http://d1.web2.qq.com/channel/login2',$POST);
        if($Rs&&($JSON = json_decode($Rs,true))&&isset($JSON['result'])){
            $this->psessionid=$JSON['result']['psessionid'];
            cache('psessionid',$this->psessionid);
            $this->SelfUin=$JSON['result']['uin'];
            $this->Curl->referer='http://s.web2.qq.com/proxy.html?v=20130916001&callback=1&id=1';
            return true;
        }
        return false;
    }
    function init(){
        $this->getUserFriend();
    }
    /**
     * 获取好友及分组情况
     */
    protected function getUserFriend(){
        $this->api(__METHOD__,function($result){
            $this->Friends=$result['friends'];
        },'','post',['r'=>json_encode([
            'vfwebqq'=>$this->vfwebqq,
            'hash'=>$this->hash($this->splite($this->ptwebqq))
        ])]);
    }
    function poll(){
        $this->Curl->referer='http://d1.web2.qq.com/proxy.html?v=20151105001&callback=1&id=2';
        return $this->api('poll',function($result,$retcode){
           foreach($result as $r){
               switch($r['poll_type']){
                   case 'message':
//                       消息，普通文本
                       $value = json_decode(iconv('GBK','UTF-8',json_encode($r['value'])),true);
                       return $value;
                       break;
                   case 'group_message':

                       break;
               }
           }
        },[],'post',[
            'r'=>json_encode([
                'ptwebqq'=>$this->ptwebqq,
                'clientid'=>53999199,
                'psessionid'=>$this->psessionid,
                'key'=>''
            ])
        ]);
    }
    /**
     * 获取群列表
     */
    protected function getGroupNameList(){
        $this->api(__METHOD__,function($result){
            foreach($result['gnamelist'] as $r){
                $this->GroupList[$r['name']]=$r['gid'];
            }
//            foreach($this->GroupList as $gid){
//                $this->getGroupInfo($gid);
//            }
        },'','post',['r'=>json_encode([
            'vfwebqq'=>$this->vfwebqq,
            'hash'=>$this->hash($this->splite($this->ptwebqq))
        ])]);
    }

    /**
     * 获取讨论组列表
     */
    protected function getDiscussList(){
        $this->api(__METHOD__,function($result){
            foreach($result['dnamelist'] as $r){
                $this->Dnamelist[$r['name']]=$r['did'];
            }
        }, [
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            'vfwebqq'=>$this->vfwebqq,
            't'=>time().rand(100,999)
        ],[
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            'vfwebqq'=>$this->vfwebqq,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 获取自己的信息
     */
    protected function getSelfInfo(){
        $this->api('getSelfInfo',function($result){
//            $this->SelfUin=$result['uin'];
            $this->Self=$result;
        },[],'post');
    }

    /**
     * 获取在线好友
     */
    protected function getOnlineBuddies(){
        $this->api(__METHOD__,function($result){

        },[
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            'vfwebqq'=>$this->vfwebqq,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 获取会话列表
     */
    protected function getRecentList(){
        $this->api(__METHOD__,function($result){

        },'','post',['r'=>json_encode([
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            'vfwebqq'=>$this->vfwebqq
        ])]);
    }

    /**
     * 获取好友信息
     * @param numeric $Uin
     */
    protected function getFriendInfo($Uin){
        $this->api(__METHOD__,function($result){

        },[
            'tuin'=>$Uin,
            'vfwebqq'=>$this->vfwebqq,
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 获取用户的账号
     * @param numeric $Uin
     */
    protected function getFriendUin($Uin){
        $this->api(__METHOD__,function($result){
            $this->Uin2QQNumberMap[$result['account']]=$result['uin'];
        },[
            'tuin'=>$Uin,
            'type'=>1,
            'vfwebqq'=>$this->vfwebqq,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 获取个性签名
     * @param numeric $UIN 就是UIN
     */
    protected function getSingleLongNick($Uin){
        $this->api(__METHOD__,function($result){

        },[
           'tuin'=>$Uin,
            'vfwebqq'=>$this->vfwebqq,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 获取群及群成员信息
     * @param numeric $GroupID 群ID
     */
    protected function getGroupInfo($GroupID){
        $this->api(__METHOD__,function($result){
            $this->GroupMap[$result['ginfo']['name']]=$result['ginfo']['gid'];
        },[
           'gcode'=>$GroupID,
            'vfwebqq'=>$this->vfwebqq,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 获取讨论组及讨论组成员信息
     * @param numeric $DiscName 讨论组名
     */
    protected function getDiscuInfo($DiscName){
        if(!$this->Dnamelist){
            $this->getDiscussList();
        }
        $this->api(__METHOD__,function($result){

        },[
           'did'=>$this->Dnamelist[$DiscName],
            'vfwebqq'=>$this->vfwebqq,
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 更改状态
     * @param string $NewStatus 新状态 ['Q我吧'=>'callme','离开'=>'away','忙碌'=>'busy','静音'=>'silent','隐身'=>'hidden','离线'=>'offline']
     */
    protected function changeStatus($NewStatus){
        $this->api(__METHOD__,function($result){

        },[
            'newstatus'=>$NewStatus,
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            't'=>time().rand(100,999)
        ]);
        $this->api('http://d1.web2.qq.com/channel/get_online_buddies2?vfwebqq={$vfwebqq}&clientid={$clientid}&psessionid={$psessionid}&t={$t}',function($result){

        },[
            'newstatus'=>$NewStatus,
            'clientid'=>53999199,
            'psessionid'=>$this->psessionid,
            't'=>time().rand(100,999)
        ]);
    }

    /**
     * 单人发送信息
     * @param numeric $QQNumber QQ号
     * @param string $Content 发送内容
     */
    function send($QQNumber,$Content){
        if(!$this->Uin2QQNumberMap){
            foreach($this->Friends as $Friend){
                $this->getFriendUin($Friend['uin']);
            }
        }
        $Content='["'.$Content.'",["font",{"name":"宋体","size":10,"style":[0,0,0],"color":"000000"}]]';
        $this->z++;
        $msg_id=$this->q+$this->z;
        $this->api(__METHOD__,function($result){

        },'','post',[
            'r'=>json_encode([
                'to'=>$this->Uin2QQNumberMap[$QQNumber],
                'content'=>$Content,
                'face'=>702,
                'clientid'=>53999199,
                'msg_id'=>$msg_id,
                'psessionid'=>$this->psessionid
            ])
        ]);
//        $Content=[
//            $Content,
//            'font'=>[
//                'name'=>'宋体',
//                'size'=>10,
//                'style'=>[0,0,0],
//                'color'=>'000000'
//            ]
//        ];

//        $res=$this->Curl->post('http://d1.web2.qq.com/channel/send_buddy_msg2',"r=%7B%22to%22%3A{$UIN}%2C%22content%22%3A%22%5B%5C%22{$Content}%5C%22%2C%5B%5C%22font%5C%22%2C%7B%5C%22name%5C%22%3A%5C%22%E5%AE%8B%E4%BD%93%5C%22%2C%5C%22size%5C%22%3A10%2C%5C%22style%5C%22%3A%5B0%2C0%2C0%5D%2C%5C%22color%5C%22%3A%5C%22000000%5C%22%7D%5D%5D%22%2C%22face%22%3A702%2C%22clientid%22%3A53999199%2C%22msg_id%22%3A{$msg_id}%2C%22psessionid%22%3A%22{$this->psessionid}%22%7D");
    }

    /**
     *  群发消息
     * @param string $GroupName 群名
     * @param string $Content 发送内容
     */
    function groupSend($GroupName,$Content){
        if(!$this->GroupMap){
            if(!$this->GroupList){
                $this->getGroupNameList();
            }
        }
        $Content='["'.$Content.'",["font",{"name":"宋体","size":10,"style":[0,0,0],"color":"000000"}]]';
        $this->z++;
        $msg_id=$this->q+$this->z;
//        $GName=array_search($GroupName,$this->GroupMap);
//        $GUIN=$this->GroupList[$GName];
        $this->api(__METHOD__,function($result){

        },'','post',[
            'r'=>json_encode([
                'group_uin'=>$this->GroupList[$GroupName],
                'content'=>$Content,
                'face'=>702,
                'clientid'=>53999199,
                'msg_id'=>$msg_id,
                'psessionid'=>$this->psessionid
            ])
        ]);
    }

    /**
     * 讨论组发送
     * @param string $DiscName 讨论组名
     * @param string $Content 发送内容
     */
    function discSend($DiscName,$Content){
        if(!$this->Dnamelist){
            $this->getDiscussList();
        }
        $Content='["'.$Content.'",["font",{"name":"宋体","size":10,"style":[0,0,0],"color":"000000"}]]';
        $this->z++;
        $msg_id=$this->q+$this->z;
        $this->api(__METHOD__,function($result){

        },'','post',[
            'r'=>json_encode([
                'did'=>$this->Dnamelist[$DiscName],
                'content'=>$Content,
                'face'=>702,
                'clientid'=>53999199,
                'msg_id'=>$msg_id,
                'psessionid'=>$this->psessionid
            ])
        ]);
    }
    protected function api($name,$callback=null,$vars=[],$method='get',$post=[]){
        preg_match('/::[A-Za-z]+/',$name,$match);
        if($match){
            $name=substr($match[0],2);
        }
        if(!isset($this->UrlMaps[$name])){return false;}
        $Data=array_merge([
            't'=>time().rand(100,999),
            'vfwebqq'=>$this->vfwebqq,
            'ptwebqq'=>$this->ptwebqq,
            'psessionid'=>$this->psessionid
        ],$vars);
        $Replace=[];
        foreach(array_keys($Data) as $key){
            $Replace[]='{$'.$key.'}';
        }
        $Url = str_replace($Replace,array_values($Data),$this->UrlMaps[$name]);
        if($method=='get'){
            $Rs = $this->Curl->get($Url);
        }else{
            $Rs = $this->Curl->post($Url,$post);
        }
        $Json = $Rs?json_decode($Rs,true):false;
        if($Json['retcode']==self::$OFFLINE){
            return $Json['retcode'];
        }
        if(is_callable($callback)&&$Json){
            return call_user_func_array($callback,['result'=>isset($Json['result'])?$Json['result']:[],'retcode'=>$Json['retcode']]);
        }else{
//            TODO 返回错误
        }
        return $Json;
    }
    protected function hash($I){
        $x = $this->SelfUin;
        $N=[];
        for($T=0;$T<count($I);$T++)$N[$T%4]^=$this->get_bianma($I[$T]);
        $U=['EC','OK'];
        $V=[];
        $V[0] = $x >> 24 & 255 ^ $this->get_bianma(substr($U[0],0,1));
        $V[1] = $x >> 16 & 255 ^ $this->get_bianma(substr($U[0],1,1));
        $V[2] = $x >> 8 & 255 ^ $this->get_bianma(substr($U[1],0,1));
        $V[3] = $x & 255 ^ $this->get_bianma(substr($U[1],1,1));
        $U = [];
        for ($T = 0; $T < 8; $T++) $U[$T] = $T % 2 == 0 ? $N[$T >> 1] : $V[$T >> 1];
        $N = [
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F'
        ];
        $V = '';
        for ($T = 0; $T < count($U); $T++) {
            $V .= $N[$U[$T] >> 4 & 15];
            $V .= $N[$U[$T] & 15];
        }
        return $V;
    }
    function get_bianma($str)//等同于js的charCodeAt()
    {
        $result = array();
        for($i = 0, $l = mb_strlen($str, 'utf-8');$i < $l;++$i)
        {
            $result[] = $this->uniord(mb_substr($str, $i, 1, 'utf-8'));
        }
        return join(",", $result);
    }
    function uniord($str, $from_encoding = false)
    {
        $from_encoding = $from_encoding ? $from_encoding : 'UTF-8';
        if (strlen($str) == 1)
            return ord($str);
        $str = mb_convert_encoding($str, 'UCS-4BE', $from_encoding);
        $tmp = unpack('N', $str);
        return $tmp[1];
    }
    protected function splite($str){
        $arr=[];
        for($i=0;$i<strlen($str);$i++){
            $arr[]=substr($str,$i,1);
        }
        return $arr;
    }

    function test(){
//        $this->Dnamelist=[];
        $this->getGroupNameList();
//        $this->getDiscussList();
//        $this->getSelfInfo();
//        $this->getOnlineBuddies();
//        $this->getRecentList();
    }
    function testSend(){
//        $this->send(326956620,'...');
        $this->groupSend('test讨论','woshwizy');
//        $this->discSend('TI6血虐vp干爆eg的神秘组织','...');
    }
}

