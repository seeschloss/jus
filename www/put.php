<?php
require __DIR__.'/../inc/common.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	$putdata = fopen("php://input", "r");

	if ($_SERVER['CONTENT_LENGTH'] > $GLOBALS['config']['max_size']) {
		 header("HTTP/1.0 413 Request Entity Too Large");
		 echo "Upload size is limited to ".bytes_to_human($GLOBALS['config']['max_size'])."\n";
		 die();
	}

	$file = new File();

	$directory = $GLOBALS['config']['upload_directory']."/".date("Y-m-d");
	$file->path = $file->create_name($directory, null);

	$fp = fopen($file->path, "w");

	while ($data = fread($putdata, 1024)) {
		 fwrite($fp, $data);

		 if (filesize($file->path) > $GLOBALS['config']['max_size']) {
			 header("HTTP/1.0 413 Request Entity Too Large");
			 echo "Upload size is limited to ".bytes_to_human($GLOBALS['config']['max_size'])."\n";
			 fclose($fp);
			 fclose($putdata);
			 unlink($file->path);
			 die();
		 }
	}

	fclose($fp);
	fclose($putdata);

	$extension = $file->extension();
	$final_path = $file->create_name($directory, $extension);

	rename($file->path, $final_path);
	$file->path = $final_path;

	echo $file->url()."\n";
}

