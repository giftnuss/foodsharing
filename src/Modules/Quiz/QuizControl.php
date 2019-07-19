<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Services\ImageService;

class QuizControl extends Control
{
	private $quizGateway;
	private $imageService;
	private $identificationHelper;
	private $dataHelper;

	public function __construct(
		QuizModel $model,
		QuizView $view,
		QuizGateway $quizGateway,
		ImageService $imageService,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->model = $model;
		$this->view = $view;
		$this->quizGateway = $quizGateway;
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
			$this->quizGateway->deleteSession($id);
			$this->goBack();
		}

		$this->pageHelper->addBread('Quiz', '/?page=quiz');
		$this->pageHelper->addTitle('Quiz');

		$topbtn = '';
		$slogan = 'Quiz-Fragen für Foodsaver, Betriebsverantwortliche & Botschafter';
		if (!isset($_GET['sub']) && isset($_GET['id']) && (int)$_GET['id'] > 0) {
			if ($name = $this->model->getVal('name', 'quiz', $_GET['id'])) {
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
			$this->pageHelper->addContent($this->view->listQuiz($this->quizGateway->getQuizzes()), CNT_LEFT);
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
		if ($q = $this->model->getQuestion($questionId)) {
			if ($name = $this->model->getVal('name', 'quiz', $q['quiz_id'])) {
				$this->pageHelper->addBread($name, '/?page=quiz&id=' . $questionId);
			}
			$this->pageHelper->addBread('Frage  #' . $q['id'], '/?page=quiz&sub=wall&id=' . (int)$q['id']);
			$this->pageHelper->addContent($this->view->topbar('Quizfrage  #' . $q['id'], '<a style="float:right;color:#FFF;font-size:13px;margin-top:-20px;" href="#" class="button" onclick="ajreq(\'editquest\',{id:' . (int)$q['id'] . ',qid:' . (int)$q['quiz_id'] . '});return false;">Frage bearbeiten</a>' . $q['text'] . '<p><strong>' . $q['fp'] . ' Fehlerpunkte, ' . $q['duration'] . ' Sekunden zum Antworten</strong></p>', '<img src="/img/quiz.png" />'), CNT_TOP);
			$this->pageHelper->addContent($this->v_utils->v_field($this->wallposts('question', $questionId), 'Kommentare'), CNT_MAIN);
			$this->pageHelper->addContent($this->view->answerSidebar($this->model->getAnswers($q['id']), $questionId), CNT_RIGHT);
		}
	}

	public function edit()
	{
		if ($quiz = $this->model->getQuiz($_GET['qid'])) {
			if ($this->isSubmitted()) {
				$name = strip_tags($_POST['name']);
				$name = trim($name);

				$desc = $_POST['desc'];
				$desc = trim($desc);

				$maxfp = (int)$_POST['maxfp'];
				$questcount = (int)$_POST['questcount'];

				if (!empty($name)) {
					if ($id = $this->model->updateQuiz($_GET['qid'], $name, $desc, $maxfp, $questcount)) {
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

			$maxfp = (int)$_POST['maxfp'];
			$questcount = (int)$_POST['questcount'];

			if (!empty($name)) {
				if ($id = $this->quizGateway->addQuiz($name, $desc, $maxfp, $questcount)) {
					$this->flashMessageHelper->info('Quiz wurde erfolgreich angelegt!');
					$this->routeHelper->go('/?page=quiz&id=' . (int)$id);
				}
			}
		}

		$this->pageHelper->addContent($this->view->quizForm());
	}

	public function sessiondetail()
	{
		if ($fs = $this->model->getValues(
			array('name', 'nachname', 'photo', 'rolle', 'geschlecht', 'sleep_status'),
			'foodsaver',
			$_GET['fsid']
		)) {
			$this->pageHelper->addBread('Quiz Sessions von ' . $fs['name'] . ' ' . $fs['nachname']);
			$this->pageHelper->addContent(
				$this->view->topbar(
					'Quiz-Sessions von ' . $fs['name'] . ' ' . $fs['nachname'],
					$this->getRolle($fs['geschlecht'], $fs['rolle']),
					$this->imageService->avatar($fs)
				),
				CNT_TOP
			);

			if ($sessions = $this->quizGateway->getUserSessions($_GET['fsid'])) {
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
		if ($quiz = $this->model->getValues(array('id', 'name'), 'quiz', $_GET['id'])) {
			if ($sessions = $this->model->getSessions($_GET['id'])) {
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

		$this->pageHelper->addContent($this->view->listQuestions($this->model->listQuestions($quiz_id), $quiz_id));

		$this->pageHelper->addContent('<div style="height:15px;"></div>' . $this->view->quizbuttons($quiz_id));
	}
}
