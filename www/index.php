<?php
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	require __DIR__.'/put.php';
	die();
}

require __DIR__.'/../inc/common.inc.php';

$max_size_text = bytes_to_human($GLOBALS['config']['max_size']);

$errors = [];
$uploaded_files = [];

if (!empty($_FILES)) {
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

		$uploaded_files[] = $file;
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
		<title>Just upload stuff.</title>
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
				<input type="submit" value="Up" />
			</form>
			<p id="limitations">Maximum file size and total upload size is <?php echo $max_size_text; ?>.</p>
		</div>
		<div id="footer">
			<p id="usage"><code>curl --upload-file &lt;/home/you/local-file.png&gt; up.ÿ.fr</code></p>
			<p id="credits"><a href="mailto:see@seos.fr">see@seos.fr</a> &mdash; <a href="https://github.com/seeschloss/jus">github.com/seeschloss/jus</a></p>
		</div>
	</body>
</html>
