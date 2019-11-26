<?php

namespace Foodsharing\Modules\Console;

use Foodsharing\Lib\Db\Mem;

class ConsoleControl
{
	/**
	 * @var Mem
	 */
	protected $mem;

	public function __construct()
	{
	}

	/**
	 * @required
	 */
	public function setMem(Mem $mem)
	{
		$this->mem = $mem;
	}

	public function index()
	{
	}

	public function getSubFunc()
	{
		return false;
	}

	public static function error($msg)
	{
		if (defined('QUIET') && QUIET == true) {
			return false;
		}
		echo "\033[31m" . self::cliTime() . " [ERROR]\t" . $msg . " \033[0m\n";
	}

	public static function info($msg)
	{
		if (defined('QUIET') && QUIET == true) {
			return false;
		}
		//echo "\033[37m[INFO]\t" . $msg." \033[0m\n";
		echo '' . self::cliTime() . " [INFO]\t" . $msg . "\n";
	}

	public static function success($msg)
	{
		if (defined('QUIET') && QUIET == true) {
			return false;
		}
		echo "\033[32m" . self::cliTime() . " [INFO]\t" . $msg . " \033[0m\n";
	}

	private static function cliTime()
	{
		return date('Y-m-d H:i:s');
	}
}
