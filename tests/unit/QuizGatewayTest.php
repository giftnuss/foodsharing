<?php

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
			$this->tester->createQuiz($quizId);
		}
	}

	protected function _after()
	{
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
		$quizId = 1;
		$quizSessionStatus = 0;
		$this->tester->createQuizTry($fsId, $quizId, $quizSessionStatus);

		$runningSessionCount = $this->gateway->countQuizSessions($fsId, $quizId, $quizSessionStatus);
		$this->assertEquals(1, $runningSessionCount);

		$runningSession = $this->gateway->getExistingSession($quizId, $fsId);
		$this->assertArrayHasKey('id', $runningSession);
	}

	public function testAbortQuizSession()
	{
		$fsId = $this->foodsaver['id'];
		$quizId = 1;
		$this->tester->createQuizTry($fsId, $quizId, 0);
		$runningSessionId = $this->gateway->getExistingSession($quizId, $fsId)['id'];
		$data = ['id' => $runningSessionId, 'quiz_id' => $quizId, 'foodsaver_id' => $fsId];

		$data['status'] = 0;
		$this->tester->seeInDatabase('fs_quiz_session', $data);

		$this->gateway->abortSession($runningSessionId, $fsId);
		$this->tester->dontSeeInDatabase('fs_quiz_session', $data);

		$data['status'] = 2;
		$this->tester->seeInDatabase('fs_quiz_session', $data);
	}
}
