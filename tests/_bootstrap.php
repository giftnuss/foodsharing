<?php

if (!getenv('FS_ENV')) {
	// This is global bootstrap for autoloading
	putenv('FS_ENV=test');
}

include __DIR__ . '/../config.inc.php';
