<?php

require_once(\Tsy\Library\Storage\Driver\UCloud::$UCloudSDKPath."/../ucloud/proxy.php");

$bucket = "js-back";
$key    = "your key";
$file   = "local file path";

list($data, $err) = UCloud_PutFile($bucket, $key, $file);
if ($err) {
	echo "error: " . $err->ErrMsg . "\n";
	echo "code: " . $err->Code . "\n";
	exit;
}
echo "ETag: " . $data['ETag'] . "\n";
