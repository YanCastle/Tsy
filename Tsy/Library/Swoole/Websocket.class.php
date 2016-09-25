<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:14
 */

namespace Tsy\Library\Swoole;


use Tsy\Library\Swoole;

class Websocket extends Swoole
{
    protected static $opcodes = array(
        'continuation' => 0,
        'text'         => 1,
        'binary'       => 2,
        'close'        => 8,
        'ping'         => 9,
        'pong'         => 10,
    );
    /**
     * WebSocket握手
     * @param $k
     * @param $buffer
     * @return bool
     */
    function handshake($buffer){
        if(APP_MODE_LOW=='websocket'){
            return false;
        }
        if($pos = strpos($buffer,'Sec-WebSocket-Key:')){
            $buf  = substr($buffer,$pos+18);
            $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
            $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
            $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
            $new_message .= "Upgrade: websocket\r\n";
            $new_message .= "Sec-WebSocket-Version: 13\r\n";
            $new_message .= "Connection: Upgrade\r\n";
            $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
            return $new_message;
        }

    }

    function uncode($str){
        if(APP_MODE_LOW=='websocket'){
            return $str;
        }
        $opcode = ord(substr($str, 0, 1)) & 0x0F;
        $payloadlen = ord(substr($str, 1, 1)) & 0x7F;
        $ismask = (ord(substr($str, 1, 1)) & 0x80) >> 7;
        $maskkey = null;
        $oridata = null;
        $decodedata = null;

        //关闭连接
        if ($ismask != 1 || $opcode == 0x8)
        {
            return null;
        }

        //获取掩码密钥和原始数据
        if ($payloadlen <= 125 && $payloadlen >= 0)
        {
            $maskkey = substr($str, 2, 4);
            $oridata = substr($str, 6);
        }
        else if ($payloadlen == 126)
        {
            $maskkey = substr($str, 4, 4);
            $oridata = substr($str, 8);
        }
        else if ($payloadlen == 127)
        {
            $maskkey = substr($str, 10, 4);
            $oridata = substr($str, 14);
        }
        $len = strlen($oridata);
        for($i = 0; $i < $len; $i++)
        {
            $decodedata .= $oridata[$i] ^ $maskkey[$i % 4];
        }
        return $decodedata;
    }

    function code($message) {
        if(APP_MODE_LOW=='websocket'){
            return $message;
        }
        $messageType='text';
        switch ($messageType) {
            case 'continuous':
                $b1 = 0;
                break;
            case 'text':
                $b1 = 1;
                break;
            case 'binary':
                $b1 = 2;
                break;
            case 'close':
                $b1 = 8;
                break;
            case 'ping':
                $b1 = 9;
                break;
            case 'pong':
                $b1 = 10;
                break;
        }
        $b1+=128;
        $length = strlen($message);
        $lengthField = "";
        if ($length < 126) {
            $b2 = $length;
        }
        elseif ($length < 65536) {
            $b2 = 126;
            $hexLength = dechex($length);
            //$this->stdout("Hex Length: $hexLength");
            if (strlen($hexLength)%2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;
            for ($i = $n; $i >= 0; $i=$i-2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while (strlen($lengthField) < 2) {
                $lengthField = chr(0) . $lengthField;
            }
        }
        else {
            $b2 = 127;
            $hexLength = dechex($length);
            if (strlen($hexLength)%2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;
            for ($i = $n; $i >= 0; $i=$i-2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while (strlen($lengthField) < 8) {
                $lengthField = chr(0) . $lengthField;
            }
        }
        return chr($b1) . chr($b2) . $lengthField . $message;
    }
}