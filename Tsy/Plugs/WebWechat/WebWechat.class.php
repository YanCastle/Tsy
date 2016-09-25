<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/3/16
 * Time: 5:51 PM
 */

namespace Tsy\Plugs\WebWechat;


class WebWechat
{
    protected $UrlMaps=[];
    protected $QRCodePath='v.png';
    protected $Curl;
    protected $UUID;
    protected $WXUIN;
    protected $WXSID;
    protected $SKey;
    protected $PassTicket;
    protected $UserName;
    protected $MemberList;
    protected $SyncKey;
    function __construct()
    {
        $this->Curl=new Curl();
    }

    /**
     * 下载二维码
     * @param $path 二维码存储路径及名字，如不填，则取保护变量QRCodePath
     */
    function downQRCode($path=''){
        if(!$path){
            $path=$this->QRCodePath;
        }
        $this->Curl->referer='http://wx.qq.tansuyun.cn/';
        $VFCode=$this->Curl->get('http://login.weixin.qq.tansuyun.cn/jslogin?appid=wx782c26e4c19acffb&redirect_uri=http%3A%2F%2Fwx.qq.tansuyun.cn%2Fcgi-bin%2Fmmwebwx-bin%2Fwebwxnewloginpage&fun=new&lang=zh_CN&_='.time());
        $VFCode=$this->requestParse($VFCode);
        if (isset($VFCode['window.QRLogin.code'])&&isset($VFCode['window.QRLogin.uuid'])&&$VFCode['window.QRLogin.code']==200&&$VFCode['window.QRLogin.uuid']){
            $this->UUID=$VFCode['window.QRLogin.uuid'];
            $res=$this->Curl->get('http://login.weixin.qq.tansuyun.cn/qrcode/'.$this->UUID);
            if($res){
                file_put_contents($path,$res);
            }
        }
    }

    /**
     * 登陆 需要手机扫面当前目录下的二维码
     * @return bool
     */
    function login(){
        while (true){
            $html=$this->Curl->get('http://login.weixin.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/login?loginicon=true&uuid='.$this->UUID.'&tip=1&_='.time());
            $html=$this->requestParse($html);
            if (isset($html['window.code'])&&$html['window.code']==201){
                //登陆成功
                $html=$this->Curl->get('http://login.weixin.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/login?loginicon=true&uuid='.$this->UUID.'&tip=1&_='.time());
                $html=$this->requestParse($html);
                if ($html['window.redirect_uri']){
                    file_put_contents('url',$html['window.redirect_uri']);
                    $html=$this->Curl->get($html['window.redirect_uri'].'&fun=new&version=v2&lang=zh_CN');
                    if($html){
                        $Html=(array)simplexml_load_string($html);
                        if($Html){
                            $this->WXSID=$Html['wxsid']?$Html['wxsid']:null;
                            $this->WXUIN=$Html['wxuin']?$Html['wxuin']:null;
                            $this->SKey=$Html['skey']?$Html['skey']:null;
                            $this->PassTicket=$Html['pass_ticket']?$Html['pass_ticket']:null;
                        }
                        return true;
                    }
                    return false;
                }
                else{
                    continue;
                }
            }else{
                $this->downQRCode();
            }
            sleep(2);
        }
    }

    /**
     * 微信初始化
     * @return string ClientMsgId
     */
    function init(){
        $this->Curl->referer='http://wx2.qq.tansuyun.cn/?&lang=zh_CN';
        $res=$this->Curl->post('http://wx2.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/webwxinit?r=-1800193184&lang=zh_CN&pass_ticket='.$this->PassTicket,json_encode(['BaseRequest'=>[
            'DeviceID'=>"e313172144421856",
            'Sid'=>$this->WXSID,
            'Skey'=>$this->SKey,
            'Uin'=>$this->WXUIN
        ]],true));
        $res=json_decode($res,true);
        if(isset($res['BaseRequest'])&&isset($res['BaseRequest']['Ret'])&&$res['BaseRequest']['Ret']==0){
            $this->UserName=$res['User']['UserName'];
            $this->SyncKey=$res['SyncKey'];
            return true;
        }
        else{
            return '初始化失败';
        }
    }


    /**
     * 获取好友列表
     * @return bool|string
     */
    function getMemberList(){
        $res=$this->Curl->get('http://wx2.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/webwxgetcontact?lang=zh_CN&pass_ticket='.$this->PassTicket.'&r='.(time()*1000).'&seq=0&skey='.$this->SKey);
        $res=json_decode($res,true);
        if($res['BaseRequest']['Ret']==0){
            $this->MemberList=$this->array_key_set($res['MemberList'],'NickName');
            return true;
        }
        else{
            return '获取失败';
        }
    }

    /**
     * 发送消息
     * @param $Name 接收方昵称
     * @param $Content 发送内容
     * @return bool|string
     */
    function sendMSG($Name,$Content){
        $res=$this->Curl->post('http://wx2.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/webwxsendmsg?lang=zh_CN&pass_ticket='.$this->PassTicket,json_encode(['BaseRequest'=>[
            'DeviceID'=>'e928858077528946',
            'Sid'=>$this->WXSID,
            'SKey'=>$this->SKey,
            'Uin'=>$this->WXUIN
        ],'Msg'=>[
            'ClientMsgId'=>time()*10000000,
            'Content'=>$Content,
            'FromUserName'=>$this->UserName,
            'LocalID'=>time()*1000000,
            'ToUserName'=>$this->MemberList[$Name]['UserName'],
            'Type'=>1
        ]]));
        $res=json_decode($res,true);
        if($res['BaseRequest']['Ret']==0){
            return true;
        }else{
            return '发送失败';
        }
    }

    /**
     * 与服务器保持心跳
     */
    function syncCheck(){
        $g='';
        foreach ($this->SyncKey['List'] as $list){
            $g=$g.'|'.implode($list,'_');
        }
        $g=ltrim($g,'|');
        $res=$this->Curl->get('http://webpush2.weixin.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/synccheck?r='.(time()*1000).'&skey='.$this->SKey.'&sid='.$this->WXSID.'&uin='.$this->WXUIN.'&deviceid=e953485558200912&synckey='.$g);
        if($res){
            return $res;
        }
        else{
            return true;
        }
    }

    /**
     * 接受消息
     * @param $callback 自定义回掉函数
     * @return mixed
     */
    function getMSG($callback){
        $res=$this->Curl->post('http://wx2.qq.tansuyun.cn/cgi-bin/mmwebwx-bin/webwxsync?sid='.$this->WXSID.'&skey='.$this->SKey.'&lang=zh_CN&pass_ticket='.$this->PassTicket,json_encode(['BaseRequest'=>[
            'DeviceID'=>"e452844144799900",
            'Sid'=>$this->WXSID,
            'SKey'=>$this->SKey,
            'Uin'=>$this->WXUIN
        ],'rr'=>time(),'SyncKey'=>$this->SyncKey],true));
        $res=json_decode($res,true);
        $this->SyncKey=$res['SyncKey'];
        if(is_callable($callback)){
            call_user_func($callback,$res);
        }
        return $res;
    }
    protected function requestParse($data){
        $arrays=[];
        foreach (explode(';',trim($data,';')) as $row){
            list($k,$v)=explode('=',$row);
            $arrays[trim($k)]=trim(trim($v),'"');
        }
        return $arrays;
    }
    protected function array_key_set($array, $key, $repeat = false)
    {
        if (!$array) {
            return [];
        }
        $a = [];
        foreach ($array as $k => $v) {
            if ($repeat) {
                $a[$v[$key]][] = $v;
            } else {
                $a[$v[$key]] = $v;
            }
        }
        return $a;
    }
}