<?php

require_once(\Tsy\Library\Storage\Driver\UCloud::$UCloudSDKPath."/../ucloud/conf.php");
require_once(\Tsy\Library\Storage\Driver\UCloud::$UCloudSDKPath."/../ucloud/http.php");
require_once(\Tsy\Library\Storage\Driver\UCloud::$UCloudSDKPath."/../ucloud/proxy.php");

$bucket = "your bucket";
$key    = "your key";
$file   = "local file path";

list($data, $err) = UCloud_MultipartForm($bucket, $key, $file);
if ($err) {
	echo "error: " . $err->ErrMsg . "\n";
	echo "code: " . $err->Code . "\n";
	exit;
}
echo "ETag: " . $data['ETag'] . "\n";
