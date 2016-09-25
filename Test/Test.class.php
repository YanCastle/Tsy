<?php

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/06
 * Time: 10:32
 */
class Test
{
    public $config = [];
    public $url='';
    public $debug=false;
    function __construct($url,$config=[])
    {
        $this->url=$url;
        $this->config=array_merge($this->config,$config);
    }
    function start(){
        foreach ($this->config as $ModuleName=>$Objects){
            foreach ($Objects as $ObjectName=>$Methods){
                foreach ($Methods  as $MethodName=>$POST){
                    $i= implode('/',[$ModuleName,$ObjectName,$MethodName]);
                    $GET=['i'=>$i];
                    if($this->debug){
                        $GET['XDEBUG_SESSION_START']=rand(12000,15000);
                    }
                    echo $i,":\r\nPOST:",$this->json_format(json_encode($POST,JSON_UNESCAPED_UNICODE)),"\r\n响应结果:\r\n",$this->curl($this->url,$GET,$POST,1),"\r\n\r\n";
                }
            }
        }
    }

    function json_format ($json) {
        if(!is_string($json)){
            $json = json_encode($json,JSON_UNESCAPED_UNICODE);
        }
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "\r\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

// Grab the next character in the string.
            $char = substr($json, $i, 1);
// Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
// If this character is the end of an element,
// output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos+1; $j++) {
                    $result .= $indentStr;
                }
            }
// Add the character to the result string.
            $result .= $char;
// If the last character was the beginning of an element,
// output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }
                for ($j = 0; $j < $pos+1; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }

        return $result;

    }

    function debug($open=true){
        $this->debug=$open;
    }
    function curl($url,$get=[],$post=[],$cookie_id=false,$referer=false,$header=false){
//        if($this->UserID&&false===$cookie_id){$cookie_id=$this->UserID;}
        $ch = curl_init($url.'?'.http_build_query($get));
        if($post){
            curl_setopt($ch,CURLOPT_POSTFIELDS,is_string($post)?$post:http_build_query($post));
            curl_setopt($ch,CURLOPT_POST,true);
        }
        if($cookie_id){
            $cookie_jar = md5($cookie_id);
            curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_jar);
            curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_jar);
        }
        if($header){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//	$header = ["content-type: application/x-www-form-urlencoded;
//charset=UTF-8"];
//	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//	curl_setopt($ch,CURLOPT_ENCODING,'gzip');
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
        if($referer)
            curl_setopt($ch,CURLOPT_REFERER,$referer);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36 SE 2.X MetaSr 1.0');
        $rs = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->info = curl_getinfo($ch);
        curl_close($ch);
//    var_dump($rs,$error,$info);
        return $rs;
    }
}