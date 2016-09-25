<?php

require_once(\Tsy\Library\Storage\Driver\UCloud::$UCloudSDKPath."/../ucloud/proxy.php");

$bucket = "your bucket";
$key    = "your key";

list($data, $err) = UCloud_Delete($bucket, $key);
if ($err) {
	echo "error: " . $err->ErrMsg . "\n";
	echo "code: " . $err->Code . "\n";
	exit;
}

echo "delete $bucket/$key success\n";
