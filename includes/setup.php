<?php

require __DIR__ . '/../vendor/autoload.php';

/*
 * Provide autoloading for core library files
 * Note that /lib/flourish has custom files and modifications, so it can't be replaced by stock flourish to be loaded via composer
 */
spl_autoload_register(function ($class_name)
{
	$file = __DIR__ . '/../lib/flourish/' . $class_name . '.php';

	if (file_exists($file)) {
		include $file;

		return;
	} else {
		debug('file not loadable: ' . $file);
	}
});
