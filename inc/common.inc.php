<?php

require_once __DIR__.'/../cfg/config.inc.php';

require_once __DIR__.'/file.inc.php';
require_once __DIR__.'/base60.inc.php';

function bytes_to_human($bytes) {
	static $units = ['B', 'kB', 'MB', 'GB', 'TB'];

	foreach ($units as $unit) {
		if ($bytes < 1024) {
			return round($bytes, 2).' '.$unit;
		}

		$bytes /= 1024;
	}
}

