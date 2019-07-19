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
		$this->tester->createQuizTry($fsId, $quizId, 0);

		$runningSessionCount = $this->gateway->countQuizSessions($fsId, $quizId, $quizSessionStatus);
		$runningSessions = $this->gateway->getExistingSession($quizId, $fsId);

		$this->assertEquals(1, $runningSessionCount);
		$this->assertArrayHasKey('id', $runningSessions);
	}
}
