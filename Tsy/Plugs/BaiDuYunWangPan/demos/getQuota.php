<?php

require_once '../libs/BaiduPCS.class.php';
//请根据实际情况更新$access_token
$access_token = '32ddf6c937762d5d251ca4e41040368d';

$pcs = new BaiduPCS($access_token);
$b = $pcs->getQuota();
$a=1;
?>