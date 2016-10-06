<?php
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	require __DIR__.'/put.php';
	die();
}

require __DIR__.'/../inc/common.inc.php';

$max_size_text = bytes_to_human($GLOBALS['config']['max_size']);

$uploaded_files = [];

if (!empty($_FILES)) {
	$nfiles = count($_FILES['file']['tmp_name']);
	for ($i = 0; $i < $nfiles; $i++) {
		$file = new File($_FILES['file']['tmp_name'][$i]);

		$destination_directory = $GLOBALS['config']['upload_directory']."/".date("Y-m-d");
		if (!file_exists($destination_directory)) {
			mkdir($destination_directory, 0777, TRUE);
		}

		$file->move_uploaded_file($destination_directory);

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
			body {
				font-family: sans-serif;
				padding: 20px 30%;
				background: #555;
				color: #DDD;
				text-shadow: 1px 1px black;
				text-align: center;
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

			#uploaded ul {
				display: inline-block;
				text-align: left;
				padding: 0;
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
				height: 30px;
				line-height: 30px;
				display: block;
				margin: 10px auto;
				vertical-align: middle;
				padding: 5px 20px;
				box-sizing: content-box;
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

			#usage {
				position: absolute;
				bottom: 3em;
				width: 100%;
				left: 0;
				text-align: center;
			}

			#credits {
				position: absolute;
				bottom: 1em;
				width: 100%;
				left: 0;
				text-align: center;
			}

		</style>
	</head>
	<body>
		<h1>Just upload stuff.</h1>
			<?php echo $uploaded; ?>
		<form id="upload-form" action="" enctype="multipart/form-data" method="POST">
			<label id="file-label">Select your file...
				<input multiple="multiple" required="required" id="file-input" type="file" name="file[]" />
			</label>
			<input type="submit" value="Up" />
		</form>
		<p id="limitations">Maximum file size and total upload size is <?php echo $max_size_text; ?>.</p>
		<p id="usage"><code>curl --upload-file &lt;/home/you/local-file.png&gt; http://up.ÿ.fr</code></p>
		<p id="credits"><a href="mailto:see@seos.fr">see@seos.fr</a> &mdash; <a href="https://github.com/seeschloss/jus">github.com/seeschloss/jus</a></p>
	</body>
</html>
