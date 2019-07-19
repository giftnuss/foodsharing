<?php

use Foodsharing\Modules\Core\DBConstants\Quiz\RoleType;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class QuizGatewayTest extends \Codeception\Test\Unit
{
	protected $tester;

	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Quiz\QuizGateway::class);

		$this->foodsaver = $this->tester->createFoodsaver();
		$this->basketsIds = [];
		foreach (range(1, 3) as $quizId) {
			$this->createQuiz($quizId);
		}
	}

	private function createQuiz(int $quizId, array $extra_params = []): array
	{
		$params = [
			'id' => $quizId,
			'name' => 'Quiz #' . $quizId,
			'desc' => '',
			'maxfp' => 3,
			'questcount' => 3,
		];
		$params['id'] = $this->tester->haveInDatabase('fs_quiz', $params);

		return $params;
	}

	public function testGetQuizzes()
	{
		$quizzes = $this->gateway->getQuizzes();
		$this->assertEquals('1', $quizzes[0]['id']);
		$this->assertEquals('2', $quizzes[1]['id']);
		$this->assertEquals('3', $quizzes[2]['id']);
	}

	public function testCountRunningQuizSession()
	{
		$fsId = $this->foodsaver['id'];
		$quizId = RoleType::AMBASSADOR;
		$this->tester->createQuizTry($fsId, $quizId, SessionStatus::RUNNING);

		$runningSessionCount = $this->gateway->countQuizSessions($fsId, $quizId, SessionStatus::RUNNING);
		$this->assertEquals(1, $runningSessionCount);

		$runningSession = $this->gateway->getExistingSession($quizId, $fsId);
		$this->assertArrayHasKey('id', $runningSession);
	}

	public function testAbortQuizSession()
	{
		$fsId = $this->foodsaver['id'];
		$quizId = RoleType::AMBASSADOR;
		$this->tester->createQuizTry($fsId, $quizId, 0);
		$runningSessionId = $this->gateway->getExistingSession($quizId, $fsId)['id'];
		$data = ['id' => $runningSessionId, 'quiz_id' => $quizId, 'foodsaver_id' => $fsId];

		$data['status'] = SessionStatus::RUNNING;
		$this->tester->seeInDatabase('fs_quiz_session', $data);

		$this->gateway->abortSession($runningSessionId, $fsId);
		$this->tester->dontSeeInDatabase('fs_quiz_session', $data);

		$data['status'] = SessionStatus::FAILED;
		$this->tester->seeInDatabase('fs_quiz_session', $data);
	}

	public function testAddQuestion()
	{
		$questionId = $this->gateway->addQuestion(1, 'question text', 3, 60);
		$this->tester->seeInDatabase('fs_question', ['text' => 'question text']);
		$this->tester->seeInDatabase('fs_question_has_quiz', ['question_id' => $questionId, 'quiz_id' => 1]);
	}
}
