<?php
/*
 * force only executing on commandline
*/
if(!isset($argv))
{
	header('Location: http://www.' . DEFAULT_HOST);
	exit();
}

require_once 'config.inc.php';
require_once ROOT_DIR . 'app/console/console.control.php';
require_once ROOT_DIR . 'app/console/console.model.php';

$app = 'core';
$method = 'index';

if(isset($argv[3]) && $argv[3] == 'quiet')
{
	define('QUIET', true);
}
else
{
	define('QUIET', false);
}

if(isset($argv) && is_array($argv))
{
	if(count($argv) > 1)
	{
		$app = $argv[1];
	}
	if(count($argv) > 2)
	{
		$method = $argv[2];
	}
}

if($obj = loadApp($app))
{
	if (method_exists($obj, $method))
	{
		$obj->$method();
		
		exit();
	}
}

error('Modul '.$app.' konnte nicht geladen werden');
