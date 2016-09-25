<?php
define('SWOOLE_BASE',4);
define('SWOOLE_THREAD',2);
define('SWOOLE_PROCESS',3);
define('SWOOLE_PACKET',16);
define('SWOOLE_IPC_UNSOCK',1);
define('SWOOLE_IPC_MSGQUEUE',2);
define('SWOOLE_IPC_CHANNEL',3);
define('SWOOLE_SOCK_TCP',1);
define('SWOOLE_SOCK_TCP6',3);
define('SWOOLE_SOCK_UDP',2);
define('SWOOLE_SOCK_UDP6',4);
define('SWOOLE_SOCK_UNIX_DGRAM',5);
define('SWOOLE_SOCK_UNIX_STREAM',6);
define('SWOOLE_TCP',1);
define('SWOOLE_TCP6',3);
define('SWOOLE_UDP',2);
define('SWOOLE_UDP6',4);
define('SWOOLE_UNIX_DGRAM',5);
define('SWOOLE_UNIX_STREAM',6);
define('SWOOLE_SOCK_SYNC',0);
define('SWOOLE_SOCK_ASYNC',1);
define('SWOOLE_SYNC',2048);
define('SWOOLE_ASYNC',1024);
define('SWOOLE_KEEP',4096);
define('SWOOLE_EVENT_READ',512);
define('SWOOLE_EVENT_WRITE',1024);
define('SWOOLE_VERSION','1.8.2-beta');
define('SWOOLE_AIO_BASE',0);
define('SWOOLE_AIO_GCC',1);
define('SWOOLE_AIO_LINUX',2);
define('SIGHUP',1);
define('SIGINT',2);
define('SIGQUIT',3);
define('SIGILL',4);
define('SIGTRAP',5);
define('SIGABRT',6);
define('SIGBUS',10);
define('SIGFPE',8);
define('SIGKILL',9);
define('SIGUSR1',30);
define('SIGSEGV',11);
define('SIGUSR2',31);
define('SIGPIPE',13);
define('SIGALRM',14);
define('SIGTERM',15);
define('SIGCHLD',20);
define('SIGCONT',19);
define('SIGSTOP',17);
define('SIGTSTP',18);
define('SIGTTIN',21);
define('SIGTTOU',22);
define('SIGURG',16);
define('SIGXCPU',24);
define('SIGXFSZ',25);
define('SIGVTALRM',26);
define('SIGPROF',27);
define('SIGWINCH',28);
define('SIGIO',23);
define('SIGPWR',29);
define('SIGSYS',12);
define('SWOOLE_FILELOCK',2);
define('SWOOLE_MUTEX',3);
define('SWOOLE_SEM',4);
define('SWOOLE_RWLOCK',1);
define('SWOOLE_SPINLOCK',5);
define('HTTP_GLOBAL_GET',2);
define('HTTP_GLOBAL_POST',4);
define('HTTP_GLOBAL_COOKIE',8);
define('HTTP_GLOBAL_ALL',126);
define('WEBSOCKET_OPCODE_TEXT',1);
define('WEBSOCKET_OPCODE_BINARY',2);
define('WEBSOCKET_STATUS_CONNECTION',1);
define('WEBSOCKET_STATUS_HANDSHAKE',2);
define('WEBSOCKET_STATUS_FRAME',3);
define('WEBSOCKET_STATUS_ACTIVE',3);
define('SWOOLE_SSL',1 );
function swoole_version(){}

function swoole_cpu_num(){}

/**
 * @param $fd[required]
 * @param $cb[required]
 */
function swoole_event_add($fd,$cb){}

function swoole_event_set(){}

/**
 * @param $fd[required]
 */
function swoole_event_del($fd){}

function swoole_event_exit(){}

function swoole_event_wait(){}

/**
 * @param $fd[required]
 * @param $data[required]
 */
function swoole_event_write($fd,$data){}

/**
 * @param $callback[required]
 */
function swoole_event_defer($callback){}

/**
 * @param $ms[required]
 * @param $callback[required]
 * @param $param[optional]
 */
function swoole_timer_after($ms,$callback,$param=null){}

/**
 * @param $ms[required]
 * @param $callback[required]
 */
function swoole_timer_tick($ms,$callback){}

/**
 * @param $timer_id[required]
 */
function swoole_timer_clear($timer_id){}

/**
 * @param $settings[required]
 */
function swoole_async_set($settings){}

/**
 * @param $filename[required]
 * @param $callback[required]
 * @param $chunk_size[optional]
 * @param $offset[optional]
 */
function swoole_async_read($filename,$callback,$chunk_size=null,$offset=null){}

/**
 * @param $filename[required]
 * @param $content[required]
 * @param $offset[optional]
 * @param $callback[optional]
 */
function swoole_async_write($filename,$content,$offset=null,$callback=null){}

/**
 * @param $filename[required]
 * @param $callback[required]
 */
function swoole_async_readfile($filename,$callback){}

/**
 * @param $filename[required]
 * @param $content[required]
 * @param $callback[optional]
 */
function swoole_async_writefile($filename,$content,$callback=null){}

/**
 * @param $domain_name[required]
 * @param $content[required]
 */
function swoole_async_dns_lookup($domain_name,$content){}

/**
 * @param $read_array[required]
 * @param $write_array[required]
 * @param $error_array[required]
 * @param $timeout[optional]
 */
function swoole_client_select($read_array,$write_array,$error_array,$timeout=null){}

/**
 * @param $process_name[required]
 */
function swoole_set_process_name($process_name){}

function swoole_get_local_ip(){}

/**
 * @param $errno[required]
 */
function swoole_strerror($errno){}

function swoole_errno(){}

/**
 *@since 1.8.2-beta
 */
class swoole_server{
    static $taskworker=true;
    public $setting=[
        'worker_num'=>1,
        'task_worker_num'=>1,
    ];
    /**
     * @param $serv_host[required]
     * @param $serv_port[required]
     * @param $serv_mode[optional]
     * @param $sock_type[optional]
     */
    public function __construct($serv_host,$serv_port,$serv_mode=null,$sock_type=null){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     */
    public function listen($host,$port,$sock_type){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     */
    public function addlistener($host,$port,$sock_type){}

    /**
     * @param $name[required]
     * @param $cb[required]
     */
    public function on($name,$cb){}

    /**
     * @param $zset[required]
     */
    public function set($zset){}

    public function start(){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     * @param $from_id[optional]
     */
    public function send($conn_fd,$send_data,$from_id=null){}

    /**
     * @param $ip[required]
     * @param $port[required]
     * @param $send_data[optional]
     */
    public function sendto($ip,$port,$send_data=null){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     */
    public function sendwait($conn_fd,$send_data){}

    /**
     * @param $conn_fd[required]
     */
    public function exist($conn_fd){}

    /**
     * @param $conn_fd[required]
     * @param $is_protected[optional]
     */
    public function protect($conn_fd,$is_protected=null){}

    /**
     * @param $conn_fd[required]
     * @param $filename[required]
     */
    public function sendfile($conn_fd,$filename){}

    /**
     * @param $fd[required]
     */
    public function close($fd){}

    /**
     * @param $data[required]
     * @param $worker_id[required]
     */
    public function task($data,$worker_id){}

    /**
     * @param $data[required]
     * @param $timeout[optional]
     * @param $worker_id[optional]
     */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
     * @param $data[required]
     */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    /**
     * @param $from_id[required]
     */
    public function heartbeat($from_id){}

    /**
     * @param $fd[required]
     * @param $from_id[required]
     */
    public function connection_info($fd,$from_id){}

    /**
     * @param $start_fd[required]
     * @param $find_count[required]
     */
    public function connection_list($start_fd,$find_count){}

    /**
     * @param $fd[required]
     * @param $from_id[required]
     */
    public function getClientInfo($fd,$from_id){}

    /**
     * @param $start_fd[required]
     * @param $find_count[required]
     */
    public function getClientList($start_fd,$find_count){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     * @param $param[optional]
     */
    public function after($ms,$callback,$param=null){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     */
    public function tick($ms,$callback){}

    /**
     * @param $timer_id[required]
     */
    public function clearTimer($timer_id){}

    /**
     * @param $callback[required]
     */
    public function defer($callback){}

    public function sendmessage(){}

    /**
     * @return bool
     */
    public function addProcess(swoole_process $process){
        return true;
    }

    public function stats(){}

    /**
     * @param $fd[required]
     * @param $uid[required]
     */
    public function bind($fd,$uid){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_timer{
    /**
     * @param $ms[required]
     * @param $callback[required]
     * @param $param[optional]
     */
    public static function tick($ms,$callback,$param=null){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     */
    public static function after($ms,$callback){}

    /**
     * @param $timer_id[required]
     */
    public static function clear($timer_id){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_connection_iterator{
    public function rewind(){}

    public function next(){}

    public function current(){}

    public function key(){}

    public function valid(){}

    public function count(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_server_port{
    private function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function on(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_client{
    /**
     * 函数执行错误会设置该变量
     *
     * @var
     */
    public $errCode;
    /**
     * socket的文件描述符
     *
     * PHP代码中可以使用:
     * $sock = fopen("php://fd/".$swoole_client->sock);
     *
     * 将swoole_client的socket转换成一个stream socket。可以调用fread/fwrite/fclose等函数进程操作。
     * swoole_server中的$fd不能用此方法转换，因为$fd只是一个数字，$fd文件描述符属于主进程
     * $swoole_client->sock可以转换成int作为数组的key.
     *
     * @var int
     */
    public $sock;
    /**
     * swoole_client构造函数
     *
     * @param int $sock_type 指定socket的类型，支持TCP/UDP、TCP6/UDP64种
     * @param int $sync_type SWOOLE_SOCK_SYNC/SWOOLE_SOCK_ASYNC  同步/异步
     */
    public function __construct($sock_type, $sync_type = SWOOLE_SOCK_SYNC) {
    }
    /**
     * 连接到远程服务器
     *
     * @param string $host 是远程服务器的地址 v1.6.10+ 支持填写域名 Swoole会自动进行DNS查询
     * @param int $port 是远程服务器端口
     * @param float $timeout 是网络IO的超时，单位是s，支持浮点数。默认为0.1s，即100ms
     * @param int $flag 参数在UDP类型时表示是否启用udp_connect。设定此选项后将绑定$host与$port，此UDP将会丢弃非指定host/port的数据包。
     * 在send/recv前必须使用swoole_client_select来检测是否完成了连接
     * @return bool
     */
    public function connect($host, $port, $timeout = 0.1, $flag = 0) {
    }
    /**
     * 向远程服务器发送数据
     *
     * 参数为字符串，支持二进制数据。
     * 成功发送返回的已发数据长度
     * 失败返回false，并设置$swoole_client->errCode
     *
     * @param string $data
     * @return bool
     */
    public function send($data) {
    }
    /**
     * 向任意IP:PORT的服务器发送数据包，仅支持UDP/UDP6的client
     * @param $ip
     * @param $port
     * @param $data
     */
    function sendto($ip, $port, $data) {
    }
    /**
     * 从服务器端接收数据
     *
     * 如果设定了$waitall就必须设定准确的$size，否则会一直等待，直到接收的数据长度达到$size
     * 如果设置了错误的$size，会导致recv超时，返回 false
     * 调用成功返回结果字符串，失败返回 false，并设置$swoole_client->errCode属性
     *
     * @param int $size 接收数据的最大长度
     * @param bool $waitall 是否等待所有数据到达后返回
     * @return string
     */
    public function recv($size = 65535, $waitall = false) {
    }
    /**
     * 关闭远程连接
     *
     * swoole_client对象在析构时会自动close
     *
     * @return bool
     */
    public function close() {
    }
    /**
     * 注册异步事件回调函数
     *
     * @param $event_name
     * @param $callback_function
     * @return bool
     */
    public function on($event_name, $callback_function) {
    }
    /**
     * 判断是否连接到服务器
     * @return bool
     */
    public function isConnected() {}
    /**
     * 获取客户端socket的host:port信息
     * @return bool | array
     */
    public function getsockname(){}
    /**
     * 获取远端socket的host:port信息，仅用于UDP/UDP6协议
     * UDP发送数据到服务器后，可能会由其他的Server进行回复
     * @return bool | array
     */
    public function getpeername(){}

}
/**
 *@since 1.8.2-beta
 */
class swoole_process{
    public $pipe;
    public function __construct(){}

    public function __destruct(){}

    public static function wait(){}

    public static function signal(){}

    public static function kill(){}

    public static function daemon(){}

    public function useQueue(){}

    public function freeQueue(){}

    public function start(){}

    public function write(){}

    public function close(){}

    /**
     * @return string
     */
    public function read(){
        return '';
    }

    public function push(){}

    public function pop(){}

    public function exit(){}

    public function exec(){}

    public function name(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_table{
    /**
     * @param $table_size[required]
     */
    public function __construct($table_size){}

    /**
     * @param $name[required]
     * @param $type[optional]
     * @param $size[optional]
     */
    public function column($name,$type=null,$size=null){}

    public function create(){}

    public function destroy(){}

    /**
     * @param $key[required]
     * @param $value[required]
     */
    public function set($key,$value){}

    /**
     * @param $key[required]
     */
    public function get($key){}

    public function count(){}

    /**
     * @param $key[required]
     */
    public function del($key){}

    /**
     * @param $key[required]
     */
    public function exist($key){}

    /**
     * @param $key[required]
     * @param $column[required]
     * @param $incrby[optional]
     */
    public function incr($key,$column,$incrby=null){}

    /**
     * @param $key[required]
     * @param $column[required]
     * @param $decrby[optional]
     */
    public function decr($key,$column,$decrby=null){}

    public function lock(){}

    public function unlock(){}

    public function rewind(){}

    public function next(){}

    public function current(){}

    public function key(){}

    public function valid(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_lock{
    public function __construct(){}

    public function __destruct(){}

    public function lock(){}

    public function trylock(){}

    public function lock_read(){}

    public function trylock_read(){}

    public function unlock(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_atomic{
    public function __construct(){}

    public function add(){}

    public function sub(){}

    public function get(){}

    public function set(){}

    public function cmpset(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_http_server extends swoole_server{
    /**
     * @param $ha_name[required]
     * @param $cb[required]
     */
    public function on($ha_name,$cb){}

    public function setglobal(){}

    public function start(){}

    /**
     * @param $serv_host[required]
     * @param $serv_port[required]
     * @param $serv_mode[optional]
     * @param $sock_type[optional]
     */
    public function __construct($serv_host,$serv_port,$serv_mode=null,$sock_type=null){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     */
    public function listen($host,$port,$sock_type){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     */
    public function addlistener($host,$port,$sock_type){}

    /**
     * @param $zset[required]
     */
    public function set($zset){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     * @param $from_id[optional]
     */
    public function send($conn_fd,$send_data,$from_id=null){}

    /**
     * @param $ip[required]
     * @param $port[required]
     * @param $send_data[optional]
     */
    public function sendto($ip,$port,$send_data=null){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     */
    public function sendwait($conn_fd,$send_data){}

    /**
     * @param $conn_fd[required]
     */
    public function exist($conn_fd){}

    /**
     * @param $conn_fd[required]
     * @param $is_protected[optional]
     */
    public function protect($conn_fd,$is_protected=null){}

    /**
     * @param $conn_fd[required]
     * @param $filename[required]
     */
    public function sendfile($conn_fd,$filename){}

    /**
     * @param $fd[required]
     */
    public function close($fd){}

    /**
     * @param $data[required]
     * @param $worker_id[required]
     */
    public function task($data,$worker_id){}

    /**
     * @param $data[required]
     * @param $timeout[optional]
     * @param $worker_id[optional]
     */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
     * @param $data[required]
     */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    /**
     * @param $from_id[required]
     */
    public function heartbeat($from_id){}

    /**
     * @param $fd[required]
     * @param $from_id[required]
     */
    public function connection_info($fd,$from_id){}

    /**
     * @param $start_fd[required]
     * @param $find_count[required]
     */
    public function connection_list($start_fd,$find_count){}

    /**
     * @param $fd[required]
     * @param $from_id[required]
     */
    public function getClientInfo($fd,$from_id){}

    /**
     * @param $start_fd[required]
     * @param $find_count[required]
     */
    public function getClientList($start_fd,$find_count){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     * @param $param[optional]
     */
    public function after($ms,$callback,$param=null){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     */
    public function tick($ms,$callback){}

    /**
     * @param $timer_id[required]
     */
    public function clearTimer($timer_id){}

    /**
     * @param $callback[required]
     */
    public function defer($callback){}

    public function sendmessage(){}

    public function addprocess(){}

    public function stats(){}

    /**
     * @param $fd[required]
     * @param $uid[required]
     */
    public function bind($fd,$uid){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_http_response{
    public function cookie(){}

    public function rawcookie(){}

    public function status($code){}

    public function gzip($level){}

    public function header($name,$value){}

    public function write($content){}

    public function end($content =''){}

    public function sendfile($path){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_http_request{
    public $rawContent='';
    public $files=[];
    public $cookie=[];
    public $post=[];
    public $get=[];
    /**
     * @var array
     */
    public $server=[];
    public $header=[];
    function __construct()
    {
        $this->server=$_SERVER;
    }

}
/**
 *@since 1.8.2-beta
 */
class swoole_buffer{
    public function __construct(){}

    public function __destruct(){}

    public function __toString(){return '';}

    public function substr(){}

    public function write(){}

    public function read(){}

    public function append(){}

    public function expand(){}

    public function clear(){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_websocket_server extends swoole_http_server{
    /**
     * @param $event_name[required]
     * @param $callback[required]
     */
    public function on($event_name,$callback){}

    /**
     * @param $fd[required]
     * @param $data[required]
     * @param $opcode[optional]
     * @param $finish[optional]
     */
    public function push($fd,$data,$opcode=null,$finish=null){}

    /**
     * @param $fd[required]
     */
    public function exist($fd){}

    /**
     * @param $data[required]
     * @param $opcode[optional]
     * @param $finish[optional]
     * @param $mask[optional]
     */
    public static function pack($data,$opcode=null,$finish=null,$mask=null){}

    /**
     * @param $data[required]
     */
    public static function unpack($data){}

    public function setglobal(){}

    public function start(){}

    /**
     * @param $serv_host[required]
     * @param $serv_port[required]
     * @param $serv_mode[optional]
     * @param $sock_type[optional]
     */
    public function __construct($serv_host,$serv_port,$serv_mode=null,$sock_type=null){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     */
    public function listen($host,$port,$sock_type){}

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $sock_type[required]
     */
    public function addlistener($host,$port,$sock_type){}

    /**
     * @param $zset[required]
     */
    public function set($zset){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     * @param $from_id[optional]
     */
    public function send($conn_fd,$send_data,$from_id=null){}

    /**
     * @param $ip[required]
     * @param $port[required]
     * @param $send_data[optional]
     */
    public function sendto($ip,$port,$send_data=null){}

    /**
     * @param $conn_fd[required]
     * @param $send_data[required]
     */
    public function sendwait($conn_fd,$send_data){}

    /**
     * @param $conn_fd[required]
     * @param $is_protected[optional]
     */
    public function protect($conn_fd,$is_protected=null){}

    /**
     * @param $conn_fd[required]
     * @param $filename[required]
     */
    public function sendfile($conn_fd,$filename){}

    /**
     * @param $fd[required]
     */
    public function close($fd){}

    /**
     * @param $data[required]
     * @param $worker_id[required]
     */
    public function task($data,$worker_id){}

    /**
     * @param $data[required]
     * @param $timeout[optional]
     * @param $worker_id[optional]
     */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
     * @param $data[required]
     */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    /**
     * @param $from_id[required]
     */
    public function heartbeat($from_id){}

    /**
     * @param $fd[required]
     * @param $from_id[required]
     */
    public function connection_info($fd,$from_id){}

    /**
     * @param $start_fd[required]
     * @param $find_count[required]
     */
    public function connection_list($start_fd,$find_count){}

    /**
     * @param $fd[required]
     * @param $from_id[required]
     */
    public function getClientInfo($fd,$from_id){}

    /**
     * @param $start_fd[required]
     * @param $find_count[required]
     */
    public function getClientList($start_fd,$find_count){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     * @param $param[optional]
     */
    public function after($ms,$callback,$param=null){}

    /**
     * @param $ms[required]
     * @param $callback[required]
     */
    public function tick($ms,$callback){}

    /**
     * @param $timer_id[required]
     */
    public function clearTimer($timer_id){}

    /**
     * @param $callback[required]
     */
    public function defer($callback){}

    public function sendmessage(){}

    public function addprocess(){}

    public function stats(){}

    /**
     * @param $fd[required]
     * @param $uid[required]
     */
    public function bind($fd,$uid){}


}
/**
 *@since 1.8.2-beta
 */
class swoole_websocket_frame{
    public $fd;
    public $data;
    public $opcode; //WEBSOCKET_OPCODE_TEXT = 0x1 ，文本数据.WEBSOCKET_OPCODE_BINARY = 0x2 ，二进制数据
    public $finish;
}
class swoole_http_client{
    public $body;
    function __construct(string $ip,int $port,bool $ssl=false)
    {
    }
    function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
    function set($array){}
    function setMethod(){}
    function setHeaders(array $header){}
    function setCookies(array $cookie){}
    function setData($data){}
    function execute($url,callable $callbale){}
    function push($data){}
    function get($url,callable $function){}

    /**
     * @param string $url 请求地址
     * @param string|array $data 请求内容
     * @param callable $function
     */
    function post($url,$data,callable $function){}
    function upgrade($url,callable $function){}
    function isConnected(){}
    function close(){}
    function on(string $callbackName,callable $func){}
}

function swoole_mysql_query(mysqli $mysqli,string $sql,callable $callback){}

class swoole_redis{
    function on(string $event_name,callable $callback){}
    function connect(string $host, int $port, callable $callback){}
    function close(){}
    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}