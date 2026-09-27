<?php
require __DIR__.'/../inc/common.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	$putdata = fopen("php://input", "r");

	$is_password_protected = array_key_exists('password', $GLOBALS['config']) && !is_null($GLOBALS['config']['password']) && strlen($GLOBALS['config']['password']) > 0;

	if ($is_password_protected) {
		if (!array_key_exists("PHP_AUTH_PW", $_SERVER)) {
			header("HTTP/1.0 401 Unauthorized");
			echo "No password.\n";
			die();
		} else if ($_SERVER["PHP_AUTH_PW"] != $GLOBALS['config']['password']) {
			header("HTTP/1.0 401 Unauthorized");
			echo "Wrong password.\n";
			die();
		}
	}

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

	if (!empty($_REQUEST['clean-metadata'])) {
		$file->clean_metadata();
	}

	echo $file->url()."\n";
}

