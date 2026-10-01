<?php
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	require __DIR__.'/put.php';
	die();
}

require __DIR__.'/../inc/common.inc.php';

$max_size_text = bytes_to_human($GLOBALS['config']['max_size']);

$errors = [];
$uploaded_files = [];

$is_password_protected = array_key_exists('password', $GLOBALS['config']) && !is_null($GLOBALS['config']['password']) && strlen($GLOBALS['config']['password']) > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_password_protected) {
	if (!array_key_exists("password", $_POST)) {
		$errors["password"] = "No password.";
	}
	if ($_POST["password"] != $GLOBALS['config']['password']) {
		$errors["password"] = "Wrong password.";
	}
}

if (count($errors) == 0 && !empty($_FILES)) {
	$nfiles = count($_FILES['file']['tmp_name']);
	for ($i = 0; $i < $nfiles; $i++) {
		$file = new File($_FILES['file']['tmp_name'][$i]);

		$destination_directory = $GLOBALS['config']['upload_directory']."/".date("Y-m-d");
		if (!file_exists($destination_directory)) {
			mkdir($destination_directory, 0777, TRUE);
		}

		if ($file->move_uploaded_file($destination_directory)) {
			$uploaded_files[] = $file;
		} else {
			$errors['file'] = "Could not upload file.";
		}
	}
} else if (count($errors) == 0 && !empty($_POST['url']) and preg_match("/https?:/", $_POST['url'])) {
	$file = new File();

	$directory = $GLOBALS['config']['upload_directory']."/".date("Y-m-d");
	$file->path = $file->create_name($directory, null);
	$putdata = fopen($_POST['url'], "r");

	$fp = fopen($file->path, "w");
	$headers = http_get_last_response_headers();
	foreach ($headers as $header) {
		if (strpos($header, ':') !== FALSE) {
			list($key, $value) = explode(':', $header, 2);

			if (strtolower(trim($key)) === 'content-length') {
				if ($value > $GLOBALS['config']['max_size']) {
					$file_size_human = bytes_to_human($value);
					$errors['url'] = "File larger than {$max_size_text} ({$file_size_human}).";
				}
			}
		}
	}

	if (!count($errors)) {
		$readsize = 1024;
		$size = 0;
		while ($data = fread($putdata, $readsize)) {
			$size += $readsize;
			fwrite($fp, $data);

			if ($size > $GLOBALS['config']['max_size']) {
				fclose($fp);
				fclose($putdata);
				unlink($file->path);
				$errors['url'] = "File larger than {$max_size_text}.";
				break;
			}
		}
	}

	if (!count($errors)) {
		fclose($fp);
		fclose($putdata);

		$extension = $file->extension();
		$final_path = $file->create_name($directory, $extension);

		rename($file->path, $final_path);
		$file->path = $final_path;

		$uploaded_files[] = $file;
	}
}

if (!empty($_REQUEST['clean-metadata'])) {
	foreach ($uploaded_files as $file) {
		$file->clean_metadata();
	}
}

if ($_SERVER['HTTP_ACCEPT'] == 'text/plain' or 
		(count($uploaded_files) && strpos($_SERVER['HTTP_USER_AGENT'], 'curl/') === 0)) {
	foreach ($uploaded_files as $file) {
		echo $file->url()."\n";
	}

	die();
}

$errors_text = "";

if (count($errors)) {
	$list_html = implode('</li><li>', $errors);
	$errors_text = <<<HTML
		<div id="errors">
			<p>There have been some errors:</p>
			<ul>
				<li>{$list_html}</li>
			</ul>
		</div>
HTML;
}

$uploaded = "";

if (count($uploaded_files)) {
	$list = [];

	foreach ($uploaded_files as $file) {
		$list[] = "<a href='{$file->url()}'>{$file->url()}</a>";
	}

	$list_html = implode('</li><li>', $list);
	$uploaded = <<<HTML
		<div id="uploaded">
			<p>Your stuff has been uploaded:</p>
			<ul>
				<li>{$list_html}</li>
			</ul>
		</div>
HTML;
		}

?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="icon" type="image/png" href="ij.png">
		<title>Just upload stuff.</title>
		<script type="module" src="index.js"></script>
		<style>
			html, body {
				height: 100%;
			}

			body {
				font-family: sans-serif;
				margin: auto;
				width: 30%;
				min-width: 600px;
				background: #555;
				color: #DDD;
				text-shadow: 1px 1px black;
				text-align: center;
				display: flex;
				flex-direction: column;
			}

			a {
				color: #ABCDEF;
			}

			a:hover {
				color: #ABCDEF;
				text-decoration: none;
			}

			li {
				list-style-type: none;
			}

			#errors {
				color: #FEDCBA;
			}

			#uploaded ul, #errors ul {
				display: inline-block;
				text-align: left;
				padding: 0;
				margin-top: 0;
			}

			#file-input {
				cursor: inherit;
				display: block;
				position: absolute;
				right: 0;
				top: 0;
				left: -1px;
				height: 100%;
				width: 100%;

				filter: alpha(opacity=0);
				opacity: 0;
			}

			#file-input:valid {
				filter: alpha(opacity=100);
				opacity: 1;
				background: #DDD;

				margin-top: -1px;
				margin-right: -1px;
				border: 1px outset;
				border-radius: 2px;

				font-size: 20px;
			}

			#file-input:valid:hover {
				border: 1px inset;

				padding-top: 1px;
				padding-left: 1px;
			}

			#url-input, #password-input-1, #password-input-2 {
				height: 40px;
				line-height: 40px;
				display: block;
				margin: 10px auto;
				vertical-align: middle;
				padding: 5px 20px;
				font-size: 100%;
				width: 100%;

				box-sizing: border-box;
				border: 1px inset;
				border-radius: 2px;
				text-align: center;
			}
			
			#file-label, input[type="submit"] {
				overflow: hidden;
				position: relative;

				background: #DDD;
				border: 1px outset;
				border-radius: 2px;
				color: black;
				text-shadow: none;
				height: 40px;
				line-height: 30px;
				display: block;
				margin: 10px auto;
				vertical-align: middle;
				padding: 5px 20px;
				box-sizing: border-box;
				font-size: 100%;
			}
			
			#file-label:hover, input[type="submit"]:hover {
				padding: 6px 19px 4px 21px;
				background: #FFF;
				border: 1px inset;
			}

			#file-label:active, input[type="submit"]:active {
				background: #FFFFEE;
			}

			#content {
				flex-grow: 1;
			}

			#footer {
				width: 100%;
				left: 0;
				text-align: center;
			}

			#url-form {
				margin-top: 3em;
			}

			form {
				position: relative;
			}

			label.checkbox {
				position: absolute;
				left: 0;
			}

			label.checkbox input {
				vertical-align: middle;
			}

		</style>
	</head>
	<body>
		<div id="content">
			<h1>Just upload stuff.</h1>
				<?php echo $errors_text; ?>
				<?php echo $uploaded; ?>
			<form id="upload-form" action="" enctype="multipart/form-data" method="POST">
				<label id="file-label">Select your file...
					<input multiple="multiple" required="required" id="file-input" type="file" name="file[]" />
				</label>
				<?php if($is_password_protected): ?>
					<input id="password-input-1" name="password" type="text" placeholder="Password" required="required" />
				<?php endif; ?>
				<label class="checkbox" for="clean-metadata-1">Strip image metadata <input id="clean-metadata-1" name="clean-metadata" value="1" type="checkbox" /></label>
				<input type="submit" value="Up" />
			</form>
			<form id="url-form" action="" method="POST">
				<input id="url-input" name="url" type="url" placeholder="... or an URL" required="required" />
				<?php if($is_password_protected): ?>
					<input id="password-input-2" name="password" type="text" placeholder="Password" required="required" />
				<?php endif; ?>
				<label class="checkbox" for="clean-metadata-2">Strip image metadata <input id="clean-metadata-2" name="clean-metadata" value="1" type="checkbox" /></label>
				<input type="submit" value="Up" />
			</form>
			<p id="limitations">Maximum file size and total upload size is <?php echo $max_size_text; ?>.</p>
		</div>
		<div id="footer">
			<p id="usage"><code>curl <?php echo $GLOBALS["config"]["base_server"]; ?> <?php if ($is_password_protected) { echo "-u :&lt;password&gt; "; } ?>--upload-file &lt;/home/you/local-file.png&gt;</code></p>
			<p id="credits"><a href="mailto:<?php echo $GLOBALS["config"]["contact_email"]; ?>"><?php echo $GLOBALS["config"]["contact_email"]; ?></a> &mdash; <a href="https://github.com/seeschloss/jus">github.com/seeschloss/jus</a></p>
		</div>
	</body>
</html>
