<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/util/Log.php");
require_once(__DIR__ . "/util/Cache.php");
require_once(__DIR__ . "/api/ISVService.php");
require_once(__DIR__ . "/api/Activate.php");
require_once(__DIR__ . "/crypto/DingtalkCrypt.php");
file_put_contents('get',json_encode($_GET));
$signature = $_GET["signature"];
$timeStamp = $_GET["timestamp"];
$nonce = $_GET["nonce"];
//echo 1;
$postdata = file_get_contents("php://input");
$postList = json_decode($postdata,true);
$encrypt = $postList['encrypt'];
$crypt = new DingtalkCrypt(C('DD_TOKEN'), C('DD_ENCODING_AES_KEY'), C('DD_SUITE_KEY'));

$msg = "";
$errCode = $crypt->DecryptMsg($signature, $timeStamp, $nonce, $encrypt, $msg);

if ($errCode != 0)
{
    L(json_encode($_GET) . "  ERR:" . $errCode);
    
    /**
     * 创建套件时检测回调地址有效性，使用C('DD_CREATE_SUITE_KEY')作为SuiteKey
     */
    $crypt = new DingtalkCrypt(C('DD_TOKEN'), C('DD_ENCODING_AES_KEY'), C('DD_CREATE_SUITE_KEY)'));
    $errCode = $crypt->DecryptMsg($signature, $timeStamp, $nonce, $encrypt, $msg);
    if ($errCode == 0)
    {
        L("DECRYPT CREATE SUITE MSG SUCCESS " . json_encode($_GET) . "  " . $msg);
        $eventMsg = json_decode($msg);
        $eventType = $eventMsg->EventType;
        if ("check_create_suite_url" === $eventType)
        {
            $random = $eventMsg->Random;
            $testSuiteKey = $eventMsg->TestSuiteKey;
            
            $encryptMsg = "";
            $errCode = $crypt->EncryptMsg($random, $timeStamp, $nonce, $encryptMsg);
            if ($errCode == 0) 
            {
                L("CREATE SUITE URL RESPONSE: " . $encryptMsg);
                echo $encryptMsg;
            } 
            else 
            {
                L("CREATE SUITE URL RESPONSE ERR: " . $errCode);
            }
        }
        else
        {
            //should never happened
        }
    }
    else 
    {
        L(json_encode($_GET) . "CREATE SUITE ERR:" . $errCode);
    }
    return;
}
else
{
    /**
     * 套件创建成功后的回调推送
     */
    L("DECRYPT MSG SUCCESS " . json_encode($_GET) . "  " . $msg);
    $eventMsg = json_decode($msg);
    $eventType = $eventMsg->EventType;
    /**
     * 套件ticket
     */
    if ("suite_ticket" === $eventType)
    {
        Cache::setSuiteTicket($eventMsg->SuiteTicket);
    }
    /**
     * 临时授权码
     */
    else if ("tmp_auth_code" === $eventType)
    {
        $tmpAuthCode = $eventMsg->AuthCode;
        error_log("tmpAuthCode:".$tmpAuthCode);
        Activate::autoActivateSuite($tmpAuthCode);
    }
    /**
     * 授权变更事件
     */

    /*user_add_org : 通讯录用户增加
    user_modify_org : 通讯录用户更改
    user_leave_org : 通讯录用户离职
    org_admin_add ：通讯录用户被设为管理员
    org_admin_remove ：通讯录用户被取消设置管理员
    org_dept_create ： 通讯录企业部门创建
    org_dept_modify ： 通讯录企业部门修改
    org_dept_remove ： 通讯录企业部门删除
    org_remove ： 企业被解散
    */

    else if ("user_add_org" === $eventType)
    {
        L(json_encode($_GET) . "  ERR:user_add_org");
        //handle auth change event
    }

    else if ("user_modify_org" === $eventType)
    {
        L(json_encode($_GET) . "  ERR:user_modify_org");
        //handle auth change event
    }

    else if ("user_leave_org" === $eventType)
    {
        L(json_encode($_GET) . "  ERR:user_leave_org");
        //handle auth change event
    }

    else if ("change_auth" === $eventType)
    {
        //handle auth change event
    }

    /**
     * 回调地址更新
     */
    else if ("check_update_suite_url" === $eventType)
    {
        $random = $eventMsg->Random;
        $testSuiteKey = $eventMsg->TestSuiteKey;
        
        $encryptMsg = "";
        $errCode = $crypt->EncryptMsg($random, $timeStamp, $nonce, $encryptMsg);
        if ($errCode == 0) 
        {
            L("UPDATE SUITE URL RESPONSE: " . $encryptMsg);
            echo $encryptMsg;
            return;
        } 
        else 
        {
            L("UPDATE SUITE URL RESPONSE ERR: " . $errCode);
        }
    }
    else
    {
        //should never happen
    }
    
    $res = "success";
    $encryptMsg = "";
    $errCode = $crypt->EncryptMsg($res, $timeStamp, $nonce, $encryptMsg);
    if ($errCode == 0) 
    {
        echo $encryptMsg;
        L("RESPONSE: " . $encryptMsg);
    } 
    else 
    {
        L("RESPONSE ERR: " . $errCode);
    }
}
