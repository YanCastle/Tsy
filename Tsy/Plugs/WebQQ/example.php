<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/21
 * Time: 16:41
 */
$WebQQ = new \Tsy\Plugs\WebQQ\WebQQ(RUNTIME_PATH.DIRECTORY_SEPARATOR.'qr.png',490523604);
if(!$WebQQ->autoLogin()){
    $WebQQ->downQrcode();
    $NickName = $WebQQ->login();
    while (!$NickName){
        $NickName = $WebQQ->login();
    }
}
$WebQQ->init();
while (true){
    $value=$WebQQ->poll();
//            sleep(2);
    echo json_encode($value,JSON_UNESCAPED_UNICODE);
}