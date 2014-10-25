<?php

function __autoload($class_name)
{
	$first = substr($class_name,0,1);

	$folder = 'flourish';

	switch ($first)
	{
		case 'f' : $folder = 'flourish'; break;
		case 'v' : $folder = 'views'; break;
	}

	$file = ROOT_DIR . 'lib/' . $folder . '/' . $class_name . '.php';

	if (file_exists($file))
	{
		include $file;
		return;
	}
	else
	{
		error('file not loadable: '.$file);
	}
}

function loadApp($app)
{
	$app = strtolower($app);

	if(file_exists(ROOT_DIR . 'app/console/'.$app.'/'.$app.'.control.php') && file_exists(ROOT_DIR . 'app/console/'.$app.'/'.$app.'.model.php'))
	{
		require_once ROOT_DIR . 'app/console/'.$app.'/'.$app.'.control.php';
		require_once ROOT_DIR . 'app/console/'.$app.'/'.$app.'.model.php';

		$mod = ucfirst($app).'Control';
		
		return new $mod();
	}

	return false;
}

function error($msg)
{
	if(QUIET)
	{
		return false;
	}
	echo "\033[31m".cliTime()." [ERROR]\t" . $msg." \033[0m\n";
}

function info($msg)
{
	if(QUIET)
	{
		return false;
	}
	//echo "\033[37m[INFO]\t" . $msg." \033[0m\n";
	echo "".cliTime()." [INFO]\t" . $msg . "\n";
}

function success($msg)
{
	if(QUIET)
	{
		return false;
	}
	echo "\033[32m".cliTime()." [INFO]\t" . $msg." \033[0m\n";
}

function cliTime()
{
	return date('Y-m-d H:i:s');
}

class ConsoleControl
{	
	public function __construct()
	{
		
	}
}