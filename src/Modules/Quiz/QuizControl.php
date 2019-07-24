<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Services\ImageService;

class QuizControl extends Control
{
	private $quizGateway;
	private $quizSessionGateway;
	private $foodsaverGateway;
	private $imageService;
	private $identificationHelper;
	private $dataHelper;

	public function __construct(
		QuizView $view,
		QuizGateway $quizGateway,
		QuizSessionGateway $quizSessionGateway,
		FoodsaverGateway $foodsaverGateway,
		ImageService $imageService,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->view = $view;
		$this->quizGateway = $quizGateway;
		$this->quizSessionGateway = $quizSessionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->imageService = $imageService;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		} elseif (!$this->session->mayEditQuiz()) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		// quiz&a=delete&id=9
		if ($id = $this->identificationHelper->getActionId('delete')) {
			$this->quizSessionGateway->deleteSession($id);
			$this->goBack();
		}

		$this->pageHelper->addBread('Quiz', '/?page=quiz');
		$this->pageHelper->addTitle('Quiz');

		$topbtn = '';
		$slogan = 'Quiz-Fragen für Foodsaver, Betriebsverantwortliche & Botschafter';
		if (!isset($_GET['sub']) && isset($_GET['id']) && (int)$_GET['id'] > 0) {
			if ($name = $this->quizGateway->getQuizName($_GET['id'])) {
				$this->pageHelper->addBread($name, '/?page=quiz&id=' . (int)$_GET['id']);
				$topbtn = ' - ' . $name;
				$slogan = 'Klausurfragen für ' . $name;
			}
			$this->listQuestions($_GET['id']);
		}

		if (!isset($_GET['sub'])) {
			if (!isset($_GET['id'])) {
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

	public function wall()
	{
		$questionId = (int)$_GET['id'];
		if ($q = $this->quizGateway->getQuestion($questionId)) {
			if ($name = $this->quizGateway->getQuizName($q['quiz_id'])) {
				$this->pageHelper->addBread($name, '/?page=quiz&id=' . $questionId);
			}
			$this->pageHelper->addBread('Frage  #' . $q['id'], '/?page=quiz&sub=wall&id=' . (int)$q['id']);
			$this->pageHelper->addContent($this->view->topbar('Quizfrage  #' . $q['id'], '<a style="float:right;color:#FFF;font-size:13px;margin-top:-20px;" href="#" class="button" onclick="ajreq(\'editquest\',{id:' . (int)$q['id'] . ',qid:' . (int)$q['quiz_id'] . '});return false;">Frage bearbeiten</a>' . $q['text'] . '<p><strong>' . $q['fp'] . ' Fehlerpunkte, ' . $q['duration'] . ' Sekunden zum Antworten</strong></p>', '<img src="/img/quiz.png" />'), CNT_TOP);
			$this->pageHelper->addContent($this->v_utils->v_field($this->wallposts('question', $questionId), 'Kommentare'), CNT_MAIN);
			$this->pageHelper->addContent($this->view->answerSidebar($this->quizGateway->getAnswers($q['id'])), CNT_RIGHT);
		}
	}

	public function edit()
	{
		if ($quiz = $this->quizGateway->getQuiz($_GET['qid'])) {
			if ($this->isSubmitted()) {
				$name = strip_tags($_POST['name']);
				$name = trim($name);

				$desc = $_POST['desc'];
				$desc = trim($desc);

				$maxFailurePoints = (int)$_POST['maxfp'];
				$questionCount = (int)$_POST['questcount'];

				if (!empty($name)) {
					if ($id = $this->quizGateway->updateQuiz($_GET['qid'], $name, $desc, $maxFailurePoints, $questionCount)) {
						$this->flashMessageHelper->info('Quiz wurde erfolgreich geändert!');
						$this->routeHelper->go('/?page=quiz&id=' . (int)$id);
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
			$name = strip_tags($_POST['name']);
			$name = trim($name);

			$desc = $_POST['desc'];
			$desc = trim($desc);

			$maxFailurePoints = (int)$_POST['maxfp'];
			$questionCount = (int)$_POST['questcount'];

			if (!empty($name)) {
				if ($id = $this->quizGateway->addQuiz($name, $desc, $maxFailurePoints, $questionCount)) {
					$this->flashMessageHelper->info('Quiz wurde erfolgreich angelegt!');
					$this->routeHelper->go('/?page=quiz&id=' . (int)$id);
				}
			}
		}

		$this->pageHelper->addContent($this->view->quizForm());
	}

	public function sessiondetail()
	{
		$fs = $this->foodsaverGateway->getFoodsaverBasics($_GET['fsid']);
		if ($fs) {
			$this->pageHelper->addBread('Quiz Sessions von ' . $fs['name'] . ' ' . $fs['nachname']);
			$this->pageHelper->addContent(
				$this->view->topbar(
					'Quiz-Sessions von ' . $fs['name'] . ' ' . $fs['nachname'],
					$this->getRolle($fs['geschlecht'], $fs['rolle']),
					$this->imageService->avatar($fs)
				),
				CNT_TOP
			);

			if ($sessions = $this->quizSessionGateway->getUserSessions($_GET['fsid'])) {
				$this->pageHelper->addContent($this->view->userSessions($sessions, $fs));
			}
		}
	}

	private function getRolle($gender_id, $rolle_id)
	{
		return $this->translationHelper->s('rolle_' . $rolle_id . '_' . $gender_id);
	}

	public function sessions()
	{
		if ($quiz = $this->quizGateway->getQuiz($_GET['id'])) {
			if ($sessions = $this->quizSessionGateway->getSessions($_GET['id'])) {
				$this->pageHelper->addContent($this->view->sessionList($sessions, $quiz));
			} else {
				$this->pageHelper->addContent($this->view->noSessions($quiz));
			}
			$this->pageHelper->addBread($quiz['name'], '/?page=quiz&id=' . (int)$_GET['id']);
			$this->pageHelper->addBread('Auswertung');
			$slogan = 'Klausurfragen für ' . $quiz['name'];

			$this->pageHelper->addContent($this->view->topbar('Auswertung für ' . $quiz['name'] . ' Quiz', $slogan, '<img src="/img/quiz.png" />'), CNT_TOP);
		}
	}

	public function listQuestions($quiz_id)
	{
		$this->pageHelper->addContent($this->view->quizbuttons($quiz_id));

		$this->pageHelper->addContent($this->view->listQuestions($this->quizGateway->listQuestions($quiz_id), $quiz_id));

		$this->pageHelper->addContent('<div style="height:15px;"></div>' . $this->view->quizbuttons($quiz_id));
	}
}
