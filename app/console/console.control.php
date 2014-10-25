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

function qs($str)
{
	return $str;
}

class ConsoleControl
{	
	public function __construct()
	{
		
	}
	
	public function progressbar($count)
	{
		return new Console_ProgressBar('[%bar%] %percent% ETA:%estimate%', '=>', '-', 80, $count);
	}
	
	public function calcDuration($start_ts,$current_item,$total_count)
	{
		$duration = (time() - $start_ts);
		$duration_one = ($duration/$current_item);
		$time_left = $duration_one * ($total_count - $current_item);
		
		return 'duration: '.$this->secs_to_h($duration).' time left: ' . $this->secs_to_h($time_left);
	}
	
	public function secs_to_h($secs)
	{
        $units = array(
                "week"   => 7*24*3600,
                "day"    =>   24*3600,
                "hour"   =>      3600,
                "minute" =>        60,
                "second" =>         1,
        );

	// specifically handle zero
        if ( $secs == 0 ) return "0 seconds";

        $s = "";

        foreach ( $units as $name => $divisor ) {
                if ( $quot = intval($secs / $divisor) ) {
                        $s .= "$quot $name";
                        $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                        $secs -= $quot * $divisor;
                }
        }

        return substr($s, 0, -2);
	}
}

function rolleWrapInt($roleInt)
{
	$roles = array(
		0 => 'user',
		1 => 'fs',
		2 => 'bieb',
		3 => 'bot',
		4 => 'orga',
		5 => 'admin'
	);

	return $roles[$roleInt];
}

function rolleWrap($roleStr)
{
	$roles = array(
		'user' => 0,
		'fs' => 1,
		'bieb' => 2,
		'bot' => 3,
		'orga' => 4,
		'admin' => 5
	);

	return $roles[$roleStr];
}