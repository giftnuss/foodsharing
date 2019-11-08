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
}
