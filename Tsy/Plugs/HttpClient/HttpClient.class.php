<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/6/16
 * Time: 11:01 AM
 */

namespace Tsy\Plugs\HttpClient;


class HttpClient
{
    public $url;
    public $cookie=[];
    public $response_header=[];
    public $request_header=[];
    /**
     * @var \swoole_client $client
     */
    private $client;
    private $UserID;
    private $clients=[];
    function __construct($url='',$UserID='',$async=false)
    {
        if(!extension_loaded('swoole')){
            L('未找到Swoole扩展');
        }
        $this->url=$url;
        $this->UserID = $UserID?uniqid('user_'):$UserID;
        if($Cookie = cache('cookie_'.$this->UserID)){
            $this->cookie = json_decode($Cookie);
        }
    }
    function post(callable $func,$data,$url='',$header=[],$cookie=[]){
        $this->getClient($url?$url:$this->url);
        $sendStr = $this->http_build($url?$url:$this->url,[],$data,$header,$cookie);
        $this->queue($func, $this->client, $sendStr, $data);
//        $this->clients[$this->client->sock]=[$sendStr,$func];
        return $this->client->sock;
    }
    function get(callable $func,$data=[],$url='',$header=[],$cookie=[]){
        $this->getClient($url?$url:$this->url);
        $sendStr = $this->http_build($url?$url:$this->url,$data,[],$header,$cookie);
//        $this->clients[$this->client->sock]=[$sendStr,$func];
        $this->queue($func, $this->client, $sendStr, $data);
        return $this->client->sock;
    }
    function queue(callable $func,\swoole_client $client,$send,$data){
        $this->clients[$client->sock]=[$send,$func,$data];
    }
    function callback($sock,$body,$header,$cookie){
        if(is_callable($this->clients[$sock][1])){
            call_user_func_array($this->clients[$sock][1],[$body,$this->clients[$sock][2],$header,$cookie]);
        }
    }
    function put(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function delete(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function options(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function cookie($name=false,$value='',$path='',$domain=0,$expires=0){
        if(!isset($this->cookie[$domain])){
            $this->cookie[$domain]=[];
        }
        if($name&&$value)
            $this->cookie[$domain][$name]=['value'=>$value,'path'=>$path,'expires'=>is_numeric($expires)?$expires:strtotime($expires)];
        elseif($name&&false===$value){
            foreach ($this->cookie as $item){
                if(isset($item[$name])){
                    return $item[$name];
                }
            }
            return null;
        }elseif($name===null)
            $this->cookie[$domain]=[];
        elseif($name===false){
            return isset($this->cookie[0])?array_merge($this->cookie[0],$this->cookie[$domain]):$this->cookie[$domain];
        }elseif($name===-1){
            return call_user_func_array('array_merge',$this->cookie);
        }
    }
    function header($name,$value=''){
        if($name&&$value)
            $this->request_header[$name]=$value;
        elseif($name===null)
            $this->request_header=[];
        elseif($name===false){
            return $this->request_header;
        }
    }
    private function getClient($url){
        $parse = parse_url($url);
        if(!isset($parse['host'])||isset($parse['user'])||isset($parse['pass'])){
            return false;
        }
        if(!isset($parse['scheme'])){
            $parse['scheme']='http';
        }
        switch ($parse['scheme']){
            case 'http':
                $parse['port']=isset($parse['port'])?$parse['port']:80;
                $this->client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
                break;
            case 'https':
                $parse['port']=isset($parse['port'])?$parse['port']:443;
                $this->client = new \swoole_client(SWOOLE_SOCK_TCP|SWOOLE_SSL,SWOOLE_SOCK_ASYNC);
                break;
            default:
                return false;
                break;
        }
        $this->client->on('receive',[$this,'receive']);
        $this->client->on('close',[$this,'close']);
        $this->client->on('error',[$this,'error']);
        $this->client->on('connect',[$this,'connect']);
        $this->client->connect($parse['host'],$parse['port']);
        return true;
    }
    function close(\swoole_client $client){}
    function connect(\swoole_client $client){
        if(isset($this->clients[$client->sock])){
            $client->send($this->clients[$client->sock][0]);
        }
    }
    function receive(\swoole_client $client,$data){
        //解析http代码
        $sock = $client->sock;
        $client->close();
        list($header,$body)=explode("\r\n\r\n",$data);
        foreach (explode("\r\n",$header ) as $k=>$item){
            if($k==0){continue;}
            list($key,$value)=explode(': ',$item );
            if('Set-Cookie'==$key){
//                ptui_identifier=000DC4D2AAD419E91EB16581D475774A1E54E6859F9D673B6DD2F420; PATH=/; DOMAIN=ui.ptlogin2.qq.com;
                $cookieStr = trim($value,';');
                $Cookie=[];

                foreach (explode('; ',$cookieStr ) as $CookieParam){
                    list($CookieKey,$CookieValue)=explode('=',$CookieParam);
                    if(in_array($CookieKey,['PATH','DOMAIN','EXPIRES'])){
                        $Cookie[$CookieKey]=$CookieValue;
                    }else{
                        $Key = $CookieKey;$Value=$CookieValue;
                    }
                }
                $this->cookie($Key,$Value,$Cookie['PATH'],$Cookie['DOMAIN'],isset($Cookie['EXPIRES'])?$Cookie['EXPIRES']:0);
            }else{
                $this->response_header[$key]=$value;
            }
        }
        $this->callback($sock, $body, $this->response_header, $this->cookie(false));
    }
    function error(\swoole_client $client){}
    function http_build($URL,$GET=[],$POST=[],$Header=[],$Cookie=[],$Method='GET'){
        $parse = parse_url($URL);
        $UrlGet=[];
        if(isset($parse['query']))
            parse_str($parse['query'],$UrlGet);
        if(!isset($parse['path'])){
            $parse['path']='/';
        }
        $RequestUrl = http_build_query(array_merge($UrlGet,$GET));
        $RequestUrl = $RequestUrl?$parse['path'].'?'.$RequestUrl:$parse['path'];
        $header = [
            strtoupper($Method)." {$RequestUrl} HTTP/1.0",
        ];
        $str = '';
        $Cookies = [];
        foreach ($this->cookie as $Domain=>$Array){
            if($Domain==$parse['host']||$Domain===0){
                foreach ($Array as $CookieKey=>$Cookie){
                    $Cookies[]=$CookieKey.'='.$Cookie['value'];
                }
            }
        }
        $header_array = [
            "HOST"=>$parse['host'],
            'Accept'=>'*/*',
            'Accept-Language'=>'zh-cn',
            'Content-Type'=>'text/html; charset=utf-8',
            "User-Agent"=>"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36",
            'Cookie'=>implode('; ',$Cookies ),
        ];
        $header_array = array_merge($header_array,$this->request_header,$Header);
        $body='';
        switch (strtoupper($Method)){
            case 'GET':break;
            case 'POST':
                $body = is_string($POST)?$POST:http_build_query($POST);
                break;
            case 'DELETE':break;
            case 'OPTIONS':break;
            case 'PUT':break;
        }
        foreach ($header_array as $k=>$v){
            $header[]=$k.': '.$v;
        }
        $str = implode("\r\n",$header)."\r\n\r\n".$body;
        return $str;
    }
    function __set($name, $value)
    {
        if(in_array($name,['referer'])){
            
        }elseif(in_array($name, [])){

        }else{

        }
    }
    function __destruct()
    {
        //存储cookie到缓存中
        if($this->cookie){
            cache('cookie_'.$this->UserID,json_encode($this->cookie));
        }
    }
}