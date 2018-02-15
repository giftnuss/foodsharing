<?php

namespace Foodsharing\Modules\Console;

use Foodsharing\Lib\Func;
use Foodsharing\Lib\Mail\AsyncMail;

class ConsoleControl
{
	protected $model;
	/**
	 * @var Func
	 */
	protected $func;

	/**
	 * @required
	 */
	public function setFunc(Func $func)
	{
		$this->func = $func;
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
			if ($quot = intval($secs / $divisor)) {
				$s .= "$quot $name";
				$s .= (abs($quot) > 1 ? 's' : '') . ', ';
				$secs -= $quot * $divisor;
			}
		}

		return substr($s, 0, -2);
	}

	protected function tplMail($tpl_id, $to, $var = array())
	{
		if ($message = $this->model->getOne_message_tpl($tpl_id)) {
			$search = array();
			$replace = array();

			foreach ($var as $key => $v) {
				$search[] = '{' . strtoupper($key) . '}';
				$replace[] = $v;
			}

			$message['body'] = str_replace($search, $replace, $message['body']);

			$message['subject'] = str_replace($search, $replace, $message['subject']);

			$email = new AsyncMail();
			$email->setFrom(DEFAULT_EMAIL, DEFAULT_EMAIL_NAME);
			$email->addRecipient($to);
			$email->setSubject($message['subject']);
			$email->setHTMLBody($this->func->emailBodyTpl($message['body']));
			$email->setBody($message['body']);

			$email->send();
		}
	}

	public static function error($msg)
	{
		if (QUIET) {
			return false;
		}
		echo "\033[31m" . self::cliTime() . " [ERROR]\t" . $msg . " \033[0m\n";
	}

	public static function info($msg)
	{
		if (QUIET) {
			return false;
		}
		//echo "\033[37m[INFO]\t" . $msg." \033[0m\n";
		echo '' . self::cliTime() . " [INFO]\t" . $msg . "\n";
	}

	public static function success($msg)
	{
		if (QUIET) {
			return false;
		}
		echo "\033[32m" . self::cliTime() . " [INFO]\t" . $msg . " \033[0m\n";
	}

	private static function cliTime()
	{
		return date('Y-m-d H:i:s');
	}
}
