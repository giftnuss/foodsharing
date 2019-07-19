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

	public function deleteAnswer($id)
	{
		return $this->del('DELETE FROM fs_answer WHERE `id` = ' . (int)$id);
	}

	public function addAnswer($qid, $text, $exp, $right)
	{
		return $this->insert('
			INSERT INTO `fs_answer`(`question_id`, `text`,`explanation` ,`right`) VALUES (' . (int)$qid . ',' . $this->strval($text) . ',' . $this->strval($exp) . ', ' . (int)$right . ')
		');
	}

	public function updateAnswer($id, $data)
	{
		return $this->update('
			UPDATE 	`fs_answer`
			SET 	`text` = ' . $this->strval($data['text']) . ',
					`explanation` = ' . $this->strval($data['explanation']) . ',
					`right` = ' . (int)$data['right'] . '
			WHERE 	`id` = ' . (int)$id . '
		');
	}

	public function getAnswer($aid)
	{
		return $this->qRow('
			SELECT 	`id`, question_id, `text`,`explanation`, `right`
			FROM	fs_answer
			WHERE 	id = ' . (int)$aid . '
		');
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
