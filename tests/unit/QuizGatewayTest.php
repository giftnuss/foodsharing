<?php

class QuizGatewayTest extends \Codeception\Test\Unit
{
	protected $tester;

	private $gateway;

	private $foodsaver;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Quiz\QuizGateway::class);

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
}
