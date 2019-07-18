<?php

class QuizModelTest extends \Codeception\Test\Unit
{
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Quiz\QuizModel
	 */
	private $model;	// TODO: delete after complete refactoring (model -> gateway)

	protected function _before()
	{
		$this->model = $this->tester->get(\Foodsharing\Modules\Quiz\QuizModel::class);

		$this->foodsaver = $this->tester->createFoodsaver();
		$this->basketsIds = [];
		foreach (range(1, 3) as $quizId) {
			$this->tester->createQuiz($quizId);
		}
	}

	protected function _after()
	{
	}

	public function testListQuiz()
	{
		$quizList = $this->model->listQuiz();
		$this->assertEquals('1', $quizList[0]['id']);
		$this->assertEquals('2', $quizList[1]['id']);
		$this->assertEquals('3', $quizList[2]['id']);
	}
}
