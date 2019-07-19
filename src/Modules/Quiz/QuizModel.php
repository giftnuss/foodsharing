<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class QuizModel extends Db
{
	private $bellGateway;
	private $foodsaverGateway;

	public function __construct(BellGateway $bellGateway, FoodsaverGateway $foodsaverGateway)
	{
		parent::__construct();
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function addUserComment($question_id, $comment)
	{
		if ($id = $this->insert('
			INSERT INTO `fs_wallpost`
			(`foodsaver_id`, `body`, `time`)
			VALUES
			(
				' . (int)$this->session->id() . ',
				' . $this->strval($comment) . ',
				NOW()
			)
		')
		) {
			if ($quizAMBs = $this->foodsaverGateway->getBotschafter(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP)) {
				$this->bellGateway->addBell($quizAMBs, 'new_quiz_comment_title', 'new_quiz_comment', 'fas fa-question-circle', array('href' => '/?page=quiz&sub=wall&id=' . (int)$question_id), array(
					'comment' => $comment
				));
			}
			$this->insert('INSERT INTO `fs_question_has_wallpost`(`question_id`, `wallpost_id`, `usercomment`) VALUES (' . (int)$question_id . ',' . (int)$id . ',1)');

			return true;
		}

		return false;
	}
}
