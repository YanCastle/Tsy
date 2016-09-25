<?php
/*
 *  Copyright (c) 2014 The CCP project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a Beijing Speedtong Information Technology Co.,Ltd license
 *  that can be found in the LICENSE file in the root of the web site.
 *
 *   http://www.yuntongxun.com
 *
 *  An additional intellectual property rights grant can be found
 *  in the file PATENTS.  All contributing project authors may
 *  be found in the AUTHORS file in the root of the source tree.
 */

include_once("./CCPRestSDK.php");

//子帐号
$subAccountSid= '';

//子帐号Token
$subAccountToken= '';

//VoIP帐号
$voIPAccount= '';

//VoIP密码
$voIPPassword= '';

//应用Id
$appId='';

//请求地址，格式如下，不需要写https://
$serverIP='sandboxapp.cloopen.com';

//请求端口 
$serverPort='8883';

//REST版本号
$softVersion='2013-12-26';

    /**
    * 取消回拨
    * @param callSid          一个由32个字符组成的电话唯一标识符
    * @param type   0： 任意时间都可以挂断电话；1 ：被叫应答前可以挂断电话，其他时段返回错误代码；2： 主叫应答前可以挂断电话，其他时段返回错误代码；默认值为0。
    */
function CallCancel($callSid,$type){
    // 初始化REST SDK
    global $appId,$subAccountSid,$subAccountToken,$voIPAccount,$voIPPassword,$serverIP,$serverPort,$softVersion;
    $rest = new REST($serverIP,$serverPort,$softVersion);
    $rest->setSubAccount($subAccountSid,$subAccountToken,$voIPAccount,$voIPPassword);
    $rest->setAppId($appId);
    
    // 调用取消回拨接口
     $result = $rest->CallCancel($callSid,$type);
     if($result == NULL ) {
         echo "result error!";
         break;
     }
     if($result->statusCode!=0) {
        echo "error code :" . $result->statusCode . "<br>";
        echo "error msg :" . $result->statusMsg . "<br>";
        //TODO 添加错误处理逻辑
     }else{
        echo "CallCancel success!<br/>";
        // 获取返回信息
        echo "statusCode:".$result->statusCode."<br/>";
        //TODO 添加成功处理逻辑
     }     
}

//Demo调用,参数填入正确后，放开注释可以调用
//CallCancel("callSid","type");
?>
