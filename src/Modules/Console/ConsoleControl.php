<?php

namespace Foodsharing\Modules\Console;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Func;

class ConsoleControl
{
	/**
	 * @var Db
	 */
	protected $model;
	/**
	 * @var Func
	 */
	protected $func;

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
	public function setFunc(Func $func)
	{
		$this->func = $func;
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

	protected function progressbar($count)
	{
		return new \ProgressBar\Manager(0, $count);
	}

	protected function calcDuration($start_ts, $current_item, $total_count)
	{
		$duration = (time() - $start_ts);
		$duration_one = ($duration / $current_item);
		$time_left = $duration_one * ($total_count - $current_item);

		return 'duration: ' . $this->secs_to_h($duration) . ' time left: ' . $this->secs_to_h($time_left);
	}

	private function secs_to_h($secs)
	{
		$units = array(
			'week' => 7 * 24 * 3600,
			'day' => 24 * 3600,
			'hour' => 3600,
			'minute' => 60,
			'second' => 1,
		);

		// specifically handle zero
		if ($secs == 0) {
			return '0 seconds';
		}

		$s = '';

		foreach ($units as $name => $divisor) {
			if ($quot = (int)($secs / $divisor)) {
				$s .= "$quot $name";
				$s .= (abs($quot) > 1 ? 's' : '') . ', ';
				$secs -= $quot * $divisor;
			}
		}

		return substr($s, 0, -2);
	}

	public static function error($msg)
	{
		if (defined('QUIET')) {
			return false;
		}
		echo "\033[31m" . self::cliTime() . " [ERROR]\t" . $msg . " \033[0m\n";
	}

	public static function info($msg)
	{
		if (defined('QUIET')) {
			return false;
		}
		//echo "\033[37m[INFO]\t" . $msg." \033[0m\n";
		echo '' . self::cliTime() . " [INFO]\t" . $msg . "\n";
	}

	public static function success($msg)
	{
		if (defined('QUIET')) {
			return false;
		}
		echo "\033[32m" . self::cliTime() . " [INFO]\t" . $msg . " \033[0m\n";
	}

	private static function cliTime()
	{
		return date('Y-m-d H:i:s');
	}
}
