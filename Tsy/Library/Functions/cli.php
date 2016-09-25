<?php
//CLI 模式
//要基于外部文件缓存对链接编号进行分组
/**
 * CLI模式下的链接标识符分组
 * @param bool $GroupName
 * @param bool $fd
 * @param bool $del
 * @return array|bool|string
 */
function cli_fd_group($GroupName=false,$fd=false,$del=false){
//    Group => fd
    $Groups = cache(C('tmp_CLI_FD_GROUP'),false,false,[]);
    if(null===$GroupName&&null===$fd){
//        使用两个null来初始化
        cache(C('tmp_CLI_FD_GROUP'),[]);
    }
    if(false===$GroupName){
//        返回全部已定义的组
        return $Groups;
    }
    if($GroupName&&$fd&&$del){
//        删除这个分组的这个东西
        if(isset($Groups[$GroupName])&&is_array($Groups[$GroupName])){
            if($key = array_search($fd,$Groups[$GroupName])){
                unset($Groups[$GroupName][$key]);
            }
        }else{
            $Groups[$GroupName]=[];
        }
    }
    if(false===$fd&&!$del){
        $return=[];
        if(is_array($GroupName)){
            foreach ($GroupName as $GN){
                $return[$GN]=isset($Groups[$GN])?$Groups[$GN]:[];
            }
        }else{
            $return = isset($Groups[$GroupName])?$Groups[$GroupName]:[];
        }
        return $return;
    }
    if(null===$fd&&!$del){
        //删除这个分组
        if(isset($Groups[$GroupName])){
            unset($Groups[$GroupName]);
        }
    }
    cache(C('tmp_CLI_FD_GROUP'),$Groups);
    return true;
}

/**
 * 给链接赋值
 * @param bool $name
 * @return mixed
 */
function fd_name($name=false){
    $CacheKey = C('CACHE_FD_NAME');
    $fdName = cache($CacheKey);
//    获取当前连接的名称
    if(false===$name){
        return fd();
    }
    if([]===$name){
        $fdName=[];
        cache($CacheKey,$fdName);
        return true;
    }
    if(null===$name){
        unset($fdName[fd()]);
    }else{
        if(!isset($fdName[fd()])){
            $fdName[fd()]=[];
        }
        //检测是否该连接已经被关闭，如果已经被关闭则删除该连接
        if(isset($_GET['_server'])){
            $ClosedFD=[];
            foreach ($fdName[fd()] as $fd){
                if(!$_GET['_server']->exist($fd)){
                    $ClosedFD[]=$fd;
                }
            }
            if($ClosedFD){
                $fdName[fd()]=array_diff($fdName[fd()],$ClosedFD);
            }
        }
        $fdName[fd()]=$name;
//        开始检测是否有该fdName的推送消息，如果有的话则推送，如果没有的话则不推送
        $PushData=cache(C('CACHE_FD_NAME_PUSH').$name);
        if(is_array($PushData)){
            foreach ($PushData as $data){
                push($name,$data,true);
            }
        }
    }
    cache($CacheKey,$fdName);
}

/**
 * Socket推送，
 * @param string $name 推送的目标名称，需要用fd_name设定，如果未设定则是当前连接
 * @param string|array $value 推送内容，可以是数组也可以是字符串，会经过通道code输出
 * @param bool $online 是否必须要当前连接在线才推送，默认为是
 */
function push($name,$value,$online=true){
    $fdName = cache(C('CACHE_FD_NAME'));
    if(!is_array($fdName)){$fdName=[];}
    //获取所有映射关系
    if($fd = array_search($name,$fdName)){
        swoole_out_check($fd,$value);
    }else{
        if(!$online){
            //处理不在线的情况
            cache('[+A]'.C('CACHE_FD_NAME_PUSH').$fdName,$value);
        }
        return false;
    }
}

/**
 * 往某个端口上所有连接广播信息
 * @param $Port
 * @param $value
 */
function broadcast($Port,$value){
    $Group = port_group($Port);
    $Type = swoole_get_port_property($Port,'TYPE');
    $Class = swoole_get_mode_class($Type);
    foreach ($Group as $fd){
        swoole_send($fd,$Class->code($value));
    }
}

/**
 * 按照连接端口对连接进行分组
 * @param $port
 * @param bool $fd
 * @return array|bool
 */
function port_group($port,$fd=false){
    if(false===$fd){
        $g = cache('tmp_port_group'.$port);
        return is_array($g)?$g:[];
    }elseif(null===$fd){
        $g = cache('tmp_port_group'.$port);
        $g = is_array($g)?$g:[];
        if($k = array_search(fd(),$g)){
            unset($g[$k]);
        }
        cache('tmp_port_group'.$port,$fd);
    }else{
        $g = cache('tmp_port_group'.$port);
        $g = is_array($g)?$g:[];
        $g[]=$fd;
        cache('tmp_port_group'.$port,$g);
    }
}

function swoole_in_check($fd,$data){
    $info = swoole_connect_info($fd);
    if(false===$info){return false;}
    $Port = $info['server_port'];
    $Type = swoole_get_port_property($Port,'TYPE');

    $Class = swoole_get_mode_class($Type);
    if(method_exists($Class,'handshake')){
        if($str = call_user_func([$Class,'handshake'],$data)){
            swoole_send($fd,$str);
            $HandShake = swoole_get_port_property($Port,'HANDSHAKE');
            if(is_callable($HandShake)){
                call_user_func($HandShake);
            }
            return true;
        }
    }
    //            解码协议，
    $data = $Class->uncode($data);
    if('Http'==$Type&&isset($_SERVER['REQUEST_METHOD'])&&'OPTIONS'==$_SERVER['REQUEST_METHOD']){
//        if(isset($_SERVER['HTTP_ORIGIN'])) {
//            define('Domain', $_SERVER['HTTP_ORIGIN']);
//            header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
//        }
//        header('Access-Control-Allow-Credentials:true');
//        header('Access-Control-Request-Method:GET,POST');
//        header('Access-Control-Allow-Headers:X-Requested-With,Cookie,ContentType');
        return '';
    }
    $_GET['_str']=$data;
    if(false===$data||null===$data){return;}
    \Tsy\Library\Aop::exec('swoole_in',\Tsy\Library\Aop::$AOP_BEFORE,$data );
    $Data=[
        'i'=>'Empty/_empty',
        'd'=>$data,
        'm'=>'',
        't'=>''
    ];
//            实例化Controller
    $Dispatch = swoole_get_port_property($Port,'DISPATCH');
    if(is_callable($Dispatch)){
        $tmpData = call_user_func($Dispatch,$data);
        if($tmpData===null){
            return null;
        }elseif(is_string($tmpData)&&strlen($tmpData)>0){
            return $tmpData;
        }else{
            $Data = is_array($tmpData)?array_merge($Data,$tmpData):$Data;
        }
    }
    \Tsy\Library\Aop::exec('swoole_in',\Tsy\Library\Aop::$AOP_AFTER,$Data);
    return $Data;
}

/**
 * Swoole通信桥检测
 * @param $fd
 * @param $Data
 * @return bool
 */
function swoole_bridge_check($fd,&$Data){
    //-----------------------------------------
    //开始进行t值检测，做桥链接处理
    //        生成mid
    $_POST['_mid']=isset($Data['m'])&&$Data['m']?$Data['m']:uniqid();
    $Data['m']=$_POST['_mid'];
    //            响应检测
    $_POST['_i']=$Data['i'];
    if($Data['t']){
//            链接桥响应,此处要应用通道编码,通道编码之前要有协议编码
        $SendData = [
            't'=>$Data['t'],
            'm'=>$Data['m']
        ];
        $info = swoole_connect_info($fd);
        if(false===$info){return false;}
        $Port = $info['server_port'];
        $Type = swoole_get_port_property($Port,'TYPE');
        $Bridge = swoole_get_port_property($Port,'BRIDGE');
//            响应桥请求
        $BridgeData=is_callable($Bridge)?call_user_func($Bridge,$SendData):'';
        $Class = swoole_get_mode_class($Type);
        if(is_string($BridgeData)&&strlen($BridgeData)>0){
            swoole_send($fd,$Class->code($BridgeData));
        }
    }
}

/**
 * Swoole输出时的内容检测，
 * @param $fd
 * @param $data
 * @return bool
 */
function swoole_out_check($fd,$data){
    $info = swoole_connect_info($fd);
    if(false===$info){return false;}
    $Port = $info['server_port'];
    $Type = swoole_get_port_property($Port,'TYPE');
    $Out = swoole_get_port_property($Port,'OUT');
    //返回内容检测
    $Class = swoole_get_mode_class($Type);
    $OutData=is_callable($Out)?call_user_func($Out,$data):C('DEFAULT_OUT');
    if(is_string($OutData)&&strlen($OutData)>0){
        \Tsy\Library\Aop::exec('swoole_out',\Tsy\Library\Aop::$AOP_BEFORE,$OutData);
        swoole_send($fd,$Class->code($OutData));
        \Tsy\Library\Aop::exec('swoole_out',\Tsy\Library\Aop::$AOP_AFTER,$OutData);
    }
}

function swoole_connect_check(\swoole_server $server,$info,$fd){
    $Config = swoole_get_port_property($info['server_port']);
    $RemoteIP = ip2long($info['remote_ip']);
    if(isset($Config['DENY_IP'])&&is_array($Config['DENY_IP'])){
        $Close = false;
        foreach ($Config['DENY_IP'] as $Rule){
            if(is_array($Rule)){
                $Close=$Close||($RemoteIP>=$Rule[0]&&$RemoteIP<=$Rule[1]);
            }else{
                $Close=$Close||$Rule==$RemoteIP;
            }
        }
        if($Close){
            // 提示信息
            L('ConnectClosedByConnectCheck:'.json_encode($info,JSON_UNESCAPED_UNICODE),LOG_NOTICE);
            return false;
        }
    }
    if(isset($Config['ALLOW_IP'])&&is_array($Config['ALLOW_IP'])){
        $Close = false;
        foreach ($Config['ALLOW_IP'] as $Rule){
            if(is_array($Rule)){
                $Close=$Close||($RemoteIP>=$Rule[0]&&$RemoteIP<=$Rule[1]);
            }else{
                $Close=$Close||$Rule==$RemoteIP;
            }
        }
        if(!$Close){
            // 提示信息
            L('ConnectClosedByConnectCheck:'.json_encode($info,JSON_UNESCAPED_UNICODE),LOG_NOTICE);
            return false;
        }
    }


    $Connect = isset($Config['CONNECT'])?$Config['CONNECT']:null;
    if(is_callable($Connect)){
        return call_user_func_array($Connect,[$server,$info,$fd]);
    }
    return true;
}
function swoole_close_check(\swoole_server $server,$info,$fd){
    $Close = swoole_get_port_property($info['server_port'],'CLOSE');
    if(is_callable($Close)){
        call_user_func_array($Close,[$server,$info,$fd]);
    }
}

function swoole_get_port_property($Port,$Property=''){
    if($Property)
        return isset($GLOBALS['_PortModeMap'][$Port])&&isset($GLOBALS['_PortModeMap'][$Port][$Property])?$GLOBALS['_PortModeMap'][$Port][$Property]:null;
    else
        return isset($GLOBALS['_PortModeMap'][$Port])?$GLOBALS['_PortModeMap'][$Port]:null;
}
/**
 * @param int $fd 链接标识符
 * @return array
 */
function swoole_connect_info($fd){
    return $GLOBALS['_SWOOLE']->connection_info($fd);
}

/**
 * swoole模式下给链接发送消息
 * @param int $fd 链接标识符
 * @param string $str 发送的消息内容
 * @return bool
 */
function swoole_send($fd,$str){
    $rs=false;
    if($GLOBALS['_SWOOLE']->exist($fd)){
        if(method_exists('Tsy\\Mode\\'.APP_MODE,'send' )){
            call_user_func_array('Tsy\\Mode\\'.APP_MODE.'::send',[$fd,$str] );
        }else{
            $rs = $GLOBALS['_SWOOLE']->send($fd,$str);
            if(isset($GLOBALS['_close'])&&$GLOBALS['_close']===true){
                $GLOBALS['_SWOOLE']->close($fd);
                $GLOBALS['_close']=false;
            }
        }
    }
    return $rs;
}
/**
 * @param bool $fd
 * @return int|string
 */
function swoole_receive($fd=false){
    if(null===$fd){
        //删除该链接的计数缓存
        cache('tmp_swoole_receive_count_'.fd(),null);
    }elseif($fd){
        //返回接受次数
        $count =  cache('tmp_swoole_receive_count_'.fd());
        return is_numeric($count)?$count:0;
    }else{
//        计数+1
        $count =  cache('tmp_swoole_receive_count_'.fd());
        cache('tmp_swoole_receive_count_'.fd(),is_numeric($count)?$count+1:1);
    }
}

function swoole_get_mode_class($mode){
    static $mode_class=[];
    $mode = ucfirst(strtolower($mode));
    if(!isset($mode_class[$mode])&&$mode){
        $class='Tsy\\Library\\Swoole\\'.$mode;
        $mode_class[$mode]=new $class();
    }
    return $mode_class[$mode];
}

function swoole_load_config(){
    $Listen = C('SWOOLE.LISTEN');
    if($Listen) {
        $SwooleTable = C('SWOOLE.TABLE');
        $SwooleProcess = C('SWOOLE.PROCESS');
        $Returns = [
            'LISTEN'=>[],
            'CONF'=>array_merge([
                'daemonize' => 0, //自动进入守护进程
                'task_worker_num' => 1,//开启task功能，
                'dispatch_mode '=>3,//轮询模式
                'worker_num'=>2,
            ],C('SWOOLE.CONF')),
            'PortModeMap'=>[],
            'PROCESS'=>is_array($SwooleProcess)?$SwooleProcess:[],
            'TABLE'=>is_array($SwooleTable)?$SwooleTable:[]
        ];
        foreach ($Listen as $Config) {
            $Config['TYPE'] = isset($Config['TYPE']) ? $Config['TYPE'] : 'Socket';
            if (isset($Config['HOST']) && isset($Config['PORT']) && is_numeric($Config['PORT']) && $Config['PORT'] > 0 && $Config['PORT'] < 65536 && long2ip(ip2long($Config['HOST'])) == $Config['HOST']) {
                $Returns['LISTEN'][]=[$Config['HOST'],$Config['PORT'],SWOOLE_SOCK_TCP];
                //分析ALLOW_IP
                if(isset($Config['ALLOW_IP'])&&is_array($Config['ALLOW_IP'])&&$Config['ALLOW_IP']){
                    foreach ($Config['ALLOW_IP'] as $k=>$Rule){
                        if(is_string($Rule)&&ip2long($Rule)){
                            $Config['ALLOW_IP'][$k]=ip2long($Rule);
                        }elseif(is_array($Rule)&&count($Rule)==2){
                            foreach ($Rule as $key=>$IP){
                                if($LongIP = ip2long($IP)){
                                    $Config['ALLOW_IP'][$k][$key]=$LongIP;
                                }else{
                                    L($IP.':允许IP范围配置错误');
                                    return false;
                                }
                            }
                        }else{
                            return false;
                        }
                    }
                }
                //分析ALLOW_IP
                if(isset($Config['DENY_IP'])&&is_array($Config['DENY_IP'])&&$Config['DENY_IP']){
                    foreach ($Config['DENY_IP'] as $k=>$Rule){
                        if(is_string($Rule)&&ip2long($Rule)){
                            $Config['DENY_IP'][$k]=ip2long($Rule);
                        }elseif(is_array($Rule)&&count($Rule)==2){
                            foreach ($Rule as $key=>$IP){
                                $IPs=[];
                                if($LongIP = ip2long($IP)){
                                    $IPs[]=$LongIP;
                                }else{
                                    L($IP.':禁止IP范围配置错误');
                                    return false;
                                }
                                $Config['DENY_IP'][$k][$key]=[
                                    min($IPs),
                                    max($IPs)
                                ];
                            }
                        }else{
                            return false;
                        }
                    }
                }
                $Returns['PortModeMap'][$Config['PORT']] = $Config;
                //同时允许默认解析方法和输出方法
            } else {
                return false;
            }
        }
        return $Returns['LISTEN']?$Returns:false;
    }else{
        return false;
    }
}

/**
 * socket client 给指定目标发送消息，不管回调
 * @param string $ip 链接地址，hostname
 * @param int $port 链接端口
 * @param string|null $data 发送内容，如果为null则删除该链接
 * @return bool
 */
function client_send($host,$port,$data,$timeout=5,$receive=null){
    static $clients=[];
    $key = $host.$port;
//    检测是否存在Swoole扩展，如果存在swoole扩展且为swoole模式或者client模式则使用swoole_client
    if(extension_loaded('swoole')){
        if(in_array(strtolower(APP_MODE),['client','swoole'])){
            //当data为null时断开连接
            if(null===$data){
                if(isset($clients[$key]))
                    $clients[$key]->close();
                return true;
            }
            //检测连接是否存在，如果不存在则创建连接
            if(!isset($clients[$key])||!$clients[$key]){
                $client = swoole_get_client($host,$port,$receive);
                $clients[$key]=$client;
            }
            //如果连接存在且发送内容为字符串则发送内容
            if(!is_string($data)&&isset($clients[$key])&&$clients[$key]->isConnected()){
                return $clients[$key]->send($data);
            }
        }else{
//            非CLI模式
            //当data为null时断开连接
            if(null===$data){
                if(isset($clients[$key]))
                    $clients[$key]->close();
                return true;
            }
            //检测连接是否存在，如果不存在则创建连接
            if(!isset($clients[$key])||!$clients[$key]){
                $client=new swoole_client(SWOOLE_TCP|SWOOLE_KEEP);
                if(!$client->connect($host,$port,$timeout)){
                    L('SocketClientError:'.$client->errCode,LOG_ERR);
                    return false;
                }
                $clients[$key]=$client;
            }
            //如果连接存在且发送内容为字符串则发送内容
            if(!is_string($data)&&isset($clients[$key])){
                return $clients[$key]->send($data);
            }
        }
    }else{
        //当data为null时断开连接
        if(null===$data){
            if(isset($clients[$key])){
                fclose($clients[$key]);
                unset($clients[$key]);
            }
            return true;
        }
        //检测连接是否存在，如果不存在则创建连接
        if(!isset($clients[$key])||!$clients[$key]){
            $clients[$key]=fsockopen($host,$port);
            if(!$clients[$key]){
                unset($clients[$key]);
                L('SocketClientError:ConnectFailed',LOG_ERR);
                return false;
            }
        }
//    如果连接存在且发送内容为字符串则发送内容
        if(is_string($data)&&isset($clients[$key])){
            return fwrite($clients[$key],$data)>0;
        }
    }    
}

function swoole_get_callback($callback){
    static $conf=[];
    if(is_array($callback)){
        $conf=$callback;
    }else{
        return isset($conf[$callback])?$conf[$callback]:null;
    }
}

function fd($fd=null){
    static $fd_static=0;
    return $fd?$fd_static=$fd:$fd_static;
}