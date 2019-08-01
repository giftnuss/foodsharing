<?php

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\QuizStatus;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class QuizGatewayTest extends \Codeception\Test\Unit
{
	protected $tester;

	private $gateway;

	private $foodsharer;
	private $foodsaver;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Quiz\QuizGateway::class);

		$this->foodsharer = $this->tester->createFoodsharer();
		$this->foodsaver = $this->tester->createFoodsaver();

		foreach (range(1, 3) as $quizId) {
			$this->tester->createQuiz($quizId);
		}
	}

	public function testGetQuizzes()
	{
		$quizzes = $this->gateway->listQuiz();
		$this->assertEquals('1', $quizzes[0]['id']);
		$this->assertEquals('2', $quizzes[1]['id']);
		$this->assertEquals('3', $quizzes[2]['id']);
	}

	public function testAddQuestion()
	{
		$questionId = $this->gateway->addQuestion(1, 'question text', 3, 60);
		$this->tester->seeInDatabase('fs_question', ['text' => 'question text']);
		$this->tester->seeInDatabase('fs_question_has_quiz', ['question_id' => $questionId, 'quiz_id' => 1]);
	}

	public function testDeleteQuestion()
	{
		$this->tester->haveInDatabase('fs_question', ['id' => 1]);
		$this->tester->haveInDatabase('fs_question_has_quiz', ['quiz_id' => 1, 'question_id' => 1]);
		$this->tester->haveInDatabase('fs_answer', ['question_id' => 1]);

		$this->gateway->deleteQuestion(1);

		$this->tester->dontSeeInDatabase('fs_question', ['id' => 1]);
		$this->tester->dontSeeInDatabase('fs_question_has_quiz', ['question_id' => 1]);
		$this->tester->dontSeeInDatabase('fs_answer', ['question_id' => 1]);
	}

	public function testFoodsharerHasNeverTriedQuiz()
	{
		$this->tester->assertEquals(QuizStatus::NEVER_TRIED, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerHasRunningQuizSession()
	{
		$this->foodsharerTriesQuiz(SessionStatus::RUNNING);
		$this->tester->assertEquals(QuizStatus::RUNNING, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerHasPassedQuiz()
	{
		$this->foodsharerTriesQuiz(SessionStatus::PASSED);
		$this->tester->assertEquals(QuizStatus::PASSED, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerFailedQuizOnce()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED);
		$this->tester->assertEquals(QuizStatus::FAILED, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerFailedTwice()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED, 2);
		$this->tester->assertEquals(QuizStatus::FAILED, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerIsPaused()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED, 3);
		$this->tester->assertEquals(QuizStatus::PAUSE, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerIsPausedForOneMoreDay()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED, 3, 29);
		$this->tester->assertEquals(QuizStatus::PAUSE, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerHasAForthTry()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED, 3, 30);
		$this->tester->assertEquals(QuizStatus::PAUSE_ELAPSED, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerHasAFifthTry()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED, 4);
		$this->tester->assertEquals(QuizStatus::PAUSE_ELAPSED, $this->foodsharerQuizStatus());
	}

	public function testFoodsharerGetsDisqualifiedAfterFifthFailure()
	{
		$this->foodsharerTriesQuiz(SessionStatus::FAILED, 5);
		$this->tester->assertEquals(QuizStatus::DISQUALIFIED, $this->foodsharerQuizStatus());
	}

	private function foodsharerTriesQuiz(int $status, int $times = 1, int $daysAgo = 0): void
	{
		foreach (range(1, $times) as $i) {
			$this->tester->createQuizTry($this->foodsharer['id'], Role::FOODSAVER, $status, $daysAgo);
		}
	}

	private function foodsharerQuizStatus(): int
	{
		return $this->gateway->getQuizStatus(Role::FOODSAVER, $this->foodsharer['id']);
	}
}
