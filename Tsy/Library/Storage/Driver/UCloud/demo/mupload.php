<?php

require_once('../ucloud/proxy.php');

$bucket = 'your bucket';
$key    = 'your key';
$file   = 'local file path';

list($data, $err) = UCloud_MInit($bucket, $key);
if ($err)
{
	echo "error: " . $err->ErrMsg . "\n";
	echo "code: " . $err->Code . "\n";
	exit;
}

$uploadId = $data['UploadId'];
$blkSize  = $data['BlkSize'];
echo "UploadId: " . $uploadId . "\n";
echo "BlkSize:  " . $blkSize . "\n";

list($etagList, $err) = UCloud_MUpload($bucket, $key, $file, $uploadId, $blkSize);
if ($err) {
	echo "error: " . $err->ErrMsg . "\n";
	echo "code: " . $err->Code . "\n";
	exit;
}

list($data, $err) = UCloud_MFinish($bucket, $key, $uploadId, $etagList);
if ($err) {
	echo "error: " . $err->ErrMsg . "\n";
	echo "code: " . $err->Code . "\n";
	exit;
}
echo "Etag:     " . $data['ETag'] . "\n";
echo "FileSize: " . $data['FileSize'] . "\n";
