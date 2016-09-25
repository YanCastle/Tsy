<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 23:31
 */
function html($id){
    $html = file_get_contents('http://m.23wx.com/html/2/2506/'.$id.'.html');
    $html = iconv('GBK','UTF8',$html);
    $next_id = preg_match('/\/html\/2\/2506\/\d+\.html">下章/',$html,$match);
    $next_id = substr($match[0],13,strlen($match[0])-26);
    $html = strip_tags($html);
    $html = str_replace(['nbsp;',"\n\n",'&','  ','书架','上章','目录','下章'],'',$html);
    $html = str_replace(['nbsp;',"\n\n",'&','  '],'',$html);
    $html = str_replace(['nbsp;',"\n\n",'&','  '],'',$html);
    $html = str_replace(['nbsp;',"\n\n","\n\t",'&','  '],'',$html);
    return [$next_id,$html];
}
//for($id=12309494;$id<123)
$fp = fopen('txt','w+');
$get = html(12309494);
while(is_numeric($get[0])){
    $get = html($get[0]);
    fputs($fp,$get[1]);
    fputs($fp,"\r\n");
}
fclose($fp);
$c=1;