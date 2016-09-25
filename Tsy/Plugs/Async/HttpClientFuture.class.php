<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan. 
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna. 
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus. 
 * Vestibulum commodo. Ut rhoncus gravida arcu. 
 */

namespace Tsy\Plugs\Async;
/**
 * HttpClientFuture.class.php
 * 暂时不支持HTTPS，受限于swoole_http_client
 * @author Castle
 * @date 2015-11-5
 */
class HttpClientFuture implements FutureIntf {
	protected $url = null;
	protected $post = null;
	protected $timer = null;
	protected $proxy = false;
	protected $timeout = 0.5;
	public $cookie=[];
	public $response_header=[];
	public $request_header=[];
	public $send_str='';
	/**
	 * @var \swoole_client $client
	 */
	private $client;
	private $UserID;
	public $err;
	public function __construct($UID=0,$header = [],$cookie=[], $timeout = 5) {
		$this->timeout = $timeout;
		if($Cookie = cache('cookie_'.$this->UserID)){
			$this->cookie = json_decode($Cookie,true);
		}
	}
	
	function get($url,$data=[],$header=[],$cookie=[]){
		$this->url=$url;
		$this->send_str = $this->http_build($url?$url:$this->url,$data,[],$header,$cookie);
        return $this;
	}
	function post($url,$data,$get=[],$header=[],$cookie=[]){
		$this->url=$url;
		$this->send_str = $this->http_build($url?$url:$this->url,$get,$data,$header,$cookie,'POST');
        return $this;
	}
	public function run(Async &$promise,$content) {
		$parse = parse_url($this->url);
		if(!isset($parse['host'])||isset($parse['user'])||isset($parse['pass'])){
            $this->err=E('_ERROR_HOST_');
			$promise->accept(['http_client'=>$this]);
		}
		if(!isset($parse['scheme'])){
			$parse['scheme']='http';
		}
		switch ($parse['scheme']){
			case 'http':
				$parse['port']=isset($parse['port'])?$parse['port']:80;
				$client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
				break;
			case 'https':
				$parse['port']=isset($parse['port'])?$parse['port']:443;
				$client = new \swoole_client(SWOOLE_SOCK_TCP|SWOOLE_SSL,SWOOLE_SOCK_ASYNC);
				break;
			default:
				return false;
				break;
		}
		$client->on('receive',function (\swoole_client $client,$data)use(&$promise){
			Timer::del($client->sock);
			$client->isDone = true;
			list($header,$body)=explode("\r\n\r\n",$data);
			foreach (explode("\r\n",$header ) as $k=>$item){
				if($k==0){continue;}
				list($key,$value)=explode(': ',$item );
				if('Set-Cookie'==$key){
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
			$this->body=$body;
			$promise->accept(['http_client'=>$this]);
		});
		$client->on('close',[$this,'close']);
		$client->on('error',function($cli)use(&$promise){
			Timer::del($cli->sock);
			$promise->accept(['http_data'=>null, 'http_error'=>'Connect error']);
		});
		$client->on('connect',function (\swoole_client $client){
			if($this->send_str){
				$client->send($this->send_str);
			}else{}
		});
		$client->connect($parse['host'],$parse['port']);
		$client->isConnected = false;

		if(!$client->errCode){
			Timer::add($client->sock, $this->timeout, function()use($client, &$promise){
				@$client->close();
				if($client->isConnected){
					$promise->accept(['http_body'=>null, 'http_error'=>'Http client read timeout']);
				}else{
					$promise->accept(['http_body'=>null, 'http_error'=>'Http client connect timeout']);
				}
			});
		}
	}
    function close(){}
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
//		foreach ($this->cookie() as $Domain=>$Array){
//			if($Domain==$parse['host']||$Domain===0){
				foreach ($this->cookie() as $CookieKey=>$Cookie){
					$Cookies[]=$CookieKey.'='.$Cookie['value'];
				}
//			}
//		}
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
	function __destruct()
	{
		//存储cookie到缓存中
		if($this->cookie){
			cache('cookie_'.$this->UserID,json_encode($this->cookie));
		}
	}
}
