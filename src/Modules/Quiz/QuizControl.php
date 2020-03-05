<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\QuizPermissions;
use Foodsharing\Services\ImageService;

class QuizControl extends Control
{
	private $quizGateway;
	private $quizSessionGateway;
	private $foodsaverGateway;
	private $imageService;
	private $identificationHelper;
	private $dataHelper;
	private $quizPermissions;

	public function __construct(
		QuizView $view,
		QuizGateway $quizGateway,
		QuizSessionGateway $quizSessionGateway,
		FoodsaverGateway $foodsaverGateway,
		ImageService $imageService,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		QuizPermissions $quizPermissions
	) {
		$this->view = $view;
		$this->quizGateway = $quizGateway;
		$this->quizSessionGateway = $quizSessionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->imageService = $imageService;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->quizPermissions = $quizPermissions;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		} elseif (!$this->quizPermissions->mayEditQuiz()) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		// quiz&a=delete&id=9
		if ($quizSessionId = $this->identificationHelper->getActionId('delete')) {
			$this->quizSessionGateway->deleteSession($quizSessionId);
			$this->goBack();
		}

		$this->pageHelper->addBread('Quiz', '/?page=quiz');
		$this->pageHelper->addTitle('Quiz');

		$topbtn = '';
		$slogan = 'Quiz-Fragen für Foodsaver, Betriebsverantwortliche & Botschafter';
		if (!isset($_GET['sub'])) {
			if (isset($_GET['id'])) {
				$this->listQuiz($_GET['id']);
			} else {
				$this->routeHelper->go('/?page=quiz&id=1');
			}

			$this->pageHelper->addContent($this->view->topbar('Quiz' . $topbtn, $slogan, '<img src="/img/quiz.png" />'), CNT_TOP);
			$this->pageHelper->addContent($this->view->listQuiz($this->quizGateway->listQuiz()), CNT_LEFT);
			$this->pageHelper->addContent($this->view->quizMenu(), CNT_LEFT);
		}
	}

	private function goBack()
	{
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		exit();
	}

	private function listQuiz($quizId): void
	{
		if ($quizId > 0) {
			if ($name = $this->quizGateway->getQuizName($quizId)) {
				$this->pageHelper->addBread($name, '/?page=quiz&id=' . $quizId);
				$topbtn = ' - ' . $name;
				$slogan = 'Quizfragen für ' . $name;
			}
			$this->listQuestions($quizId);
		}
	}

	private function listQuestions($quizId)
	{
		$quizButtons = $this->view->quizbuttons($quizId);
		$this->pageHelper->addContent($quizButtons);

		$questions = $this->quizGateway->listQuestions($quizId);
		$this->pageHelper->addContent($this->view->listQuestions($questions, $quizId));

		$this->pageHelper->addContent('<div style="height:15px;"></div>' . $quizButtons);
	}

	public function wall()
	{
		$questionId = (int)$_GET['id'];
		if ($question = $this->quizGateway->getQuestion($questionId)) {
			if ($quizName = $this->quizGateway->getQuizName($question['quiz_id'])) {
				$this->pageHelper->addBread($quizName, '/?page=quiz&id=' . $questionId);
			}
			$this->pageHelper->addBread('Frage  #' . $questionId, '/?page=quiz&sub=wall&id=' . $questionId);

			$topbarContent = $this->getWallTopbarContent($question);
			$this->pageHelper->addContent($topbarContent, CNT_TOP);
			$this->pageHelper->addContent($this->v_utils->v_field($this->wallposts('question', $questionId), 'Kommentare'), CNT_MAIN);
			$this->pageHelper->addContent($this->view->answerSidebar($this->quizGateway->getAnswers($questionId)), CNT_RIGHT);
		}
	}

	private function getWallTopbarContent($question)
	{
		return $this->view->topbar(
			'Quizfrage  #' . (int)$question['id'],
			'<a style="float:right;color:#FFF;font-size:13px;margin-top:-20px;" href="#" class="button" onclick="ajreq(\'editquest\',{id:' . (int)$question['id'] . ',qid:' . (int)$question['quiz_id'] . '});return false;">Frage bearbeiten</a>' . $question['text'] . '<p><strong>' . $question['fp'] . ' Fehlerpunkte, ' . $question['duration'] . ' Sekunden zum Antworten</strong></p>',
			'<img src="/img/quiz.png" />'
		);
	}

	public function edit()
	{
		$quizId = (int)$_GET['qid'];
		if ($quiz = $this->quizGateway->getQuiz($quizId)) {
			if ($this->isSubmitted()) {
				$name = trim(strip_tags($_POST['name']));
				if (!empty($name)) {
					$desc = trim($_POST['desc']);
					$maxFailurePoints = (int)$_POST['maxfp'];
					$questionCount = (int)$_POST['questcount'];

					if ($updatedQuizId = $this->quizGateway->updateQuiz($quizId, $name, $desc, $maxFailurePoints, $questionCount)) {
						$this->flashMessageHelper->info('Quiz wurde erfolgreich geändert!');
						$this->routeHelper->go('/?page=quiz&id=' . (int)$updatedQuizId);
					}
				}
			}

			$this->dataHelper->setEditData($quiz);
			$this->pageHelper->addContent($this->view->quizForm());
		}
	}

	public function newquiz()
	{
		if ($this->isSubmitted()) {
			$name = trim(strip_tags($_POST['name']));
			if (!empty($name)) {
				$desc = trim($_POST['desc']);
				$maxFailurePoints = (int)$_POST['maxfp'];
				$questionCount = (int)$_POST['questcount'];

				if ($quizId = $this->quizGateway->addQuiz($name, $desc, $maxFailurePoints, $questionCount)) {
					$this->flashMessageHelper->info('Quiz wurde erfolgreich angelegt!');
					$this->routeHelper->go('/?page=quiz&id=' . (int)$quizId);
				}
			}
		}

		$this->pageHelper->addContent($this->view->quizForm());
	}

	public function sessiondetail()
	{
		if ($fs = $this->foodsaverGateway->getFoodsaverBasics($_GET['fsid'])) {
			$this->pageHelper->addBread('Quiz Sessions von ' . $fs['name'] . ' ' . $fs['nachname']);
			$this->pageHelper->addContent($this->getSessionDetailTopbarContent($fs), CNT_TOP);

			if ($sessions = $this->quizSessionGateway->listUserSessions($_GET['fsid'])) {
				$this->pageHelper->addContent($this->view->userSessions($sessions, $fs));
			}
		}
	}

	private function getSessionDetailTopbarContent($fs)
	{
		$title = 'Quiz-Sessions von ' . $fs['name'] . ' ' . $fs['nachname'];
		$subtitle = $this->translationHelper->s('rolle_' . $fs['rolle'] . '_' . $fs['geschlecht']);
		$icon = $this->imageService->avatar($fs);

		return $this->view->topbar($title, $subtitle, $icon);
	}

	public function sessions()
	{
		if ($quiz = $this->quizGateway->getQuiz($_GET['id'])) {
			$this->pageHelper->addContent($this->getSessionListContent($quiz));

			$quizName = $quiz['name'];
			$this->pageHelper->addBread($quizName, '/?page=quiz&id=' . $quiz['id']);

			$this->pageHelper->addBread('Auswertung');
			$topbarContent = $this->view->topbar(
				'Auswertung für ' . $quizName . '-Quiz',
				'Quizfragen für ' . $quizName,
				'<img src="/img/quiz.png" />'
			);
			$this->pageHelper->addContent($topbarContent, CNT_TOP);
		}
	}

	private function getSessionListContent(array $quiz): string
	{
		if ($sessions = $this->quizSessionGateway->listSessions($quiz['id'])) {
			return $this->view->sessionList($sessions, $quiz);
		}

		return $this->view->noSessions($quiz);
	}
}
