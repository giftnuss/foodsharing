<?php

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class QuizSessionGatewayTest extends \Codeception\Test\Unit
{
	protected $tester;

	private $gateway;

	private $foodsaver;
	/**
	 * @var array
	 */
	private $basketsIds;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Quiz\QuizSessionGateway::class);

		$this->foodsaver = $this->tester->createFoodsaver();
		$this->basketsIds = [];
		foreach (range(1, 3) as $quizId) {
			$this->tester->createQuiz($quizId);
		}
	}

	public function testAbortQuizSession()
	{
		$fsId = $this->foodsaver['id'];
		$quizId = Role::AMBASSADOR;
		$this->tester->createQuizTry($fsId, $quizId, 0);
		$runningSessionId = $this->gateway->getRunningSession($quizId, $fsId)['id'];
		$data = ['id' => $runningSessionId, 'quiz_id' => $quizId, 'foodsaver_id' => $fsId];

		$data['status'] = SessionStatus::RUNNING;
		$this->tester->seeInDatabase('fs_quiz_session', $data);

		$this->gateway->abortSession($runningSessionId, $fsId);
		$this->tester->dontSeeInDatabase('fs_quiz_session', $data);

		$data['status'] = SessionStatus::FAILED;
		$this->tester->seeInDatabase('fs_quiz_session', $data);
	}
}
