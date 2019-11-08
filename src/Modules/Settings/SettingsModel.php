<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Quiz\QuizGateway;

class SettingsModel extends Db
{
	/**
	 * @var QuizGateway
	 */
	private $quizGateway;

	/**
	 * SettingsModel constructor.
	 *
	 * @param QuizGateway $quizGateway
	 */
	public function __construct(QuizGateway $quizGateway)
	{
		$this->quizGateway = $quizGateway;

		parent::__construct();
	}

	public function updateSleepMode($status, $from, $to, $msg)
	{
		return $this->update('
 			UPDATE
 				fs_foodsaver

 			SET
 				`sleep_status` = ' . (int)$status . ',
 				`sleep_from` = ' . $this->dateval($from) . ',
 				`sleep_until` = ' . $this->dateval($to) . ',
 				`sleep_msg` = ' . $this->strval($msg) . '

 			WHERE
 				id = ' . (int)$this->session->id() . '
 		');
	}
}
