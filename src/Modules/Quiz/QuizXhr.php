<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\AnswerRating;
use Foodsharing\Permissions\QuizPermissions;
use Foodsharing\Services\SanitizerService;

class QuizXhr extends Control
{
	private $contentGateway;
	private $quizGateway;
	private $quizSessionGateway;
	private $sanitizerService;
	private $dataHelper;
	private $quizPermissions;

	public function __construct(
		QuizGateway $quizGateway,
		QuizSessionGateway $quizSessionGateway,
		QuizView $view,
		ContentGateway $contentGateway,
		SanitizerService $sanitizerService,
		DataHelper $dataHelper,
		QuizPermissions $quizPermissions
	) {
		$this->view = $view;
		$this->quizGateway = $quizGateway;
		$this->quizSessionGateway = $quizSessionGateway;
		$this->contentGateway = $contentGateway;
		$this->sanitizerService = $sanitizerService;
		$this->dataHelper = $dataHelper;
		$this->quizPermissions = $quizPermissions;

		parent::__construct();
	}

	public function hideinfo()
	{
		$this->session->setOption('quiz-infobox-seen', true);
	}

	public function addquest()
	{
		/*
		 *  [app] => quiz
	[m] => addquest
	[text] => rgds
	[fp] => fdgh
)
		 */
		if ($this->quizPermissions->mayEditQuiz()) {
			if (isset($_GET['text'], $_GET['fp'], $_GET['qid'])) {
				$failurePoints = (int)$_GET['fp'];
				$text = strip_tags($_GET['text']);
				$duration = (int)$_GET['duration'];

				if (!empty($text)) {
					$id = $this->quizGateway->addQuestion($_GET['qid'], $text, $failurePoints, $duration);
					if ($id > 0) {
						$this->flashMessageHelper->info('Frage wurde angelegt');

						return [
							'status' => 1,
							'script' => 'goTo("/?page=quiz&id=' . (int)$_GET['qid'] . '&fid=' . (int)$id . '");'
						];
					}
				} else {
					return [
						'status' => 1,
						'script' => 'pulseError("Du solltest eine Frage angeben ;)");'
					];
				}
			}
		}
	}

	public function delquest()
	{
		if ($this->quizPermissions->mayEditQuiz() && isset($_GET['id'])) {
			$this->quizGateway->deleteQuestion($_GET['id']);

			return [
				'status' => 1,
				'script' => '$(".question-' . (int)$_GET['id'] . '").remove();$("#questions").accordion("refresh");pulseInfo("Frage gelöscht!");'
			];
		}
	}

	public function delanswer()
	{
		if ($this->quizPermissions->mayEditQuiz() && isset($_GET['id'])) {
			$this->quizGateway->deleteAnswer($_GET['id']);

			return [
				'status' => 1,
				'script' => '$("#answer-' . (int)$_GET['id'] . '").remove();pulseInfo("Antwort gelöscht!");'
			];
		}
	}

	public function addansw()
	{
		/*
		 *
		qid		1
		right	1
		text	458
		 */

		if ($this->quizPermissions->mayEditQuiz()) {
			if (isset($_GET['text'], $_GET['right'], $_GET['qid'])) {
				$text = strip_tags($_GET['text']);
				$exp = strip_tags($_GET['explanation']);
				$right = (int)$_GET['right'];

				if (!empty($text) && in_array($right, [AnswerRating::WRONG, AnswerRating::CORRECT, AnswerRating::NEUTRAL])) {
					if ($id = $this->quizGateway->addAnswer($_GET['qid'], $text, $exp, $right)) {
						return [
							'status' => 1,
							'script' => 'pulseInfo("Antwort wurde angelegt");$("#answerlist-' . (int)$_GET['qid'] . '").append(\'<li class="right-' . (int)$right . '">' . $this->sanitizerService->jsSafe(nl2br(strip_tags($text))) . '</li>\');$( "#questions" ).accordion( "refresh" );'
						];
					}
				} else {
					return [
						'status' => 1,
						'script' => 'pulseError("Du solltest einen Text angeben ;)");'
					];
				}
			}
		}
	}

	public function updateansw()
	{
		if ($this->quizPermissions->mayEditQuiz()) {
			if (isset($_GET['text'], $_GET['right'], $_GET['id'])) {
				$text = strip_tags($_GET['text']);
				$exp = strip_tags($_GET['explanation']);
				$right = (int)$_GET['right'];

				if (!empty($text) && in_array($right, [AnswerRating::WRONG, AnswerRating::CORRECT, AnswerRating::NEUTRAL])) {
					$this->quizGateway->updateAnswer($_GET['id'], $text, $exp, $right);

					return [
						'status' => 1,
						'script' => 'pulseInfo("Antwort wurde geändert");$("#answer-' . (int)$_GET['id'] . '").replaceWith(\'<li id="answer-' . (int)$_GET['id'] . '" class="right-' . (int)$right . '">' . $this->sanitizerService->jsSafe(nl2br(strip_tags($text))) . '</li>\');$( "#questions" ).accordion( "refresh" );'
					];
				}

				return [
					'status' => 1,
					'script' => 'pulseError("Du solltest einen Text angeben ;)");'
				];
			}
		}
	}

	public function editanswer()
	{
		if ($this->quizPermissions->mayEditQuiz()) {
			if ($answer = $this->quizGateway->getAnswer($_GET['id'])) {
				$this->dataHelper->setEditData($answer);
				$dia = new XhrDialog();

				$dia->addAbortButton();
				$dia->setTitle('Antwort bearbeiten');
				$dia->addOpt('width', 500);
				$dia->addContent($this->view->answerForm());

				$dia->addButton('Speichern', 'ajreq(\'updateansw\',{id:' . (int)$_GET['id'] . ',explanation:$(\'#explanation\').val(),text:$(\'#text\').val(),right:$(\'#right\').val()});$(\'#' . $dia->getId() . '\').dialog("close");');

				$return = $dia->xhrout();

				$return['script'] .= '
				$("#text, #explanation").css({
				"width":"95%",
				"height":"50px"
				});
				$("#text, #explanation").autosize();';

				return $return;
			}
		}
	}

	public function addanswer()
	{
		$dia = new XhrDialog();

		$dia->addAbortButton();
		$dia->setTitle('Neue Antwort zu Frage #' . (int)$_GET['qid']);
		$dia->addOpt('width', 500);
		$dia->addContent($this->view->answerForm());

		$dia->addButton('Speichern', 'ajreq(\'addansw\',{qid:' . (int)$_GET['qid'] . ',explanation:$(\'#explanation\').val(),text:$(\'#text\').val(),right:$(\'#right\').val()});$(\'#' . $dia->getId() . '\').dialog("close");');

		$return = $dia->xhrout();

		$return['script'] .= '
		$("#text, #explanation").css({
		"width":"95%",
		"height":"50px"
		});
		$("#text, #explanation").autosize();';

		return $return;
	}

	public function addquestion()
	{
		$dia = new XhrDialog();

		$dia->addAbortButton();
		$dia->setTitle('Neue Frage eingeben');
		$dia->addOpt('width', 500);
		$dia->addContent($this->view->questionForm());

		$dia->addButton('Speichern', 'ajreq(\'addquest\',{qid:' . (int)$_GET['qid'] . ',duration:$(\'#duration\').val(),text:$(\'#text\').val(),fp:$(\'#fp\').val()});');

		$return = $dia->xhrout();

		$return['script'] .= '
			$("#text").css({
				"width":"95%",
				"height":"50px"
			});
			$("#text").autosize();
			$("#fp").css({
				"width":"95%"
			});';

		return $return;
	}

	public function editquest()
	{
		if ($this->quizPermissions->mayEditQuiz()) {
			if ($quest = $this->quizGateway->getQuestion($_GET['id'])) {
				$this->dataHelper->setEditData($quest);
				$dia = new XhrDialog();

				$dia->addAbortButton();
				$dia->setTitle('Frage bearbeiten');
				$dia->addOpt('width', 500);
				$dia->addContent($this->view->questionForm());

				$dia->addButton('Speichern', 'ajreq(\'updatequest\',{id:' . (int)$_GET['id'] . ',qid:' . (int)$_GET['qid'] . ',wikilink:$(\'#wikilink\').val(),duration:$(\'#duration\').val(),text:$(\'#text\').val(),fp:$(\'#fp\').val()});');

				$return = $dia->xhrout();

				$return['script'] .= '
				$("#text").css({
					"width":"95%",
					"height":"50px"
				});
				$("#text").autosize();
				$("#fp").css({
					"width":"95%"
				});';

				return $return;
			}
		}
	}

	public function abort()
	{
		if ($this->session->may()) {
			$this->quizSessionGateway->abortSession($_GET['sid'], $this->session->id());

			return [
				'status' => 1,
				'script' => 'pulseInfo("Quiz wurde abgebrochen");reload();'
			];
		}
	}

	/**
	 * Method to initiate a quiz session so get the defined amount of questions, sort it randomly, and store it in a session variable.
	 */
	public function startquiz(): array
	{
		if (!$this->session->may()) {
			return [];
		}

		$quizId = $_GET['qid'];

		$runningQuizSession = $this->quizSessionGateway->getRunningSession($quizId, $this->session->id());
		if ($runningQuizSession) {
			return $this->resumeQuizSession($quizId, $runningQuizSession);
		}

		return $this->startNewQuizSession($quizId);
	}

	private function resumeQuizSession(int $quizId, array $quizSession): array
	{
		$this->session->set('quiz-id', $quizId);
		$this->session->set('quiz-questions', $quizSession['quiz_questions']);
		$this->session->set('quiz-index', $quizSession['quiz_index']);
		$this->session->set('quiz-session', $quizSession['id']);
		$easyMode = $quizSession['easymode'] == 1 && $quizId == Role::FOODSAVER;
		$this->session->set('quiz-easymode', $easyMode);

		/*
		 * Make a little output that the user can continue the quiz
		 */
		$dia = new XhrDialog();
		$dia->setTitle('Quiz fortführen');
		$dia->addContent('<h1>Du hast Dein Quiz nicht beendet</h1><p>Aber keine Sorge, Du kannst einfach jetzt das Quiz zu Ende bringen.</p><p>Also viel Spaß beim Weiterquizzen.</p>');
		$dia->addButton('Quiz fortführen', 'ajreq(\'next\',{app:\'quiz\'});');

		$result = $dia->xhrout();
		$result['script'] .= $this->abortOrOpenDialog($quizSession['id']);

		return $result;
	}

	private function startNewQuizSession(int $quizId): array
	{
		$quiz = $this->quizGateway->getQuiz($quizId);
		if ($quiz) {
			if ($quizId == Role::FOODSAVER && isset($_GET['easymode']) && $_GET['easymode'] == 1) {
				$this->session->set('quiz-easymode', true);
				$quiz['questcount'] = 20;
			} else {
				$this->session->set('quiz-easymode', false);
			}

			$questions = $this->getRandomQuestions($quizId, $quiz['questcount']);
			if ($questions) {
				// for safety check if there are not too many questions
				$questions = array_slice($questions, 0, (int)$quiz['questcount']);

				/*
				 * Store quiz data in the users session
				 */
				$this->session->set('quiz-id', $quizId);
				$this->session->set('quiz-questions', $questions);
				$this->session->set('quiz-index', 0);

				/*
				 * Make a litle output for the user that he/she can just start the quiz now
				 */
				$dia = new XhrDialog();
				$dia->addOpt('width', 600);
				$dia->setTitle($quiz['name'] . '-Quiz');
				$quizHowTo = $this->contentGateway->get(17);
				$dia->addContent($this->view->initQuizPage($quizHowTo));
				$dia->addAbortButton();
				$dia->addButton('Quiz starten', 'ajreq(\'next\',{app:\'quiz\'});$(\'#' . $dia->getId() . '\').dialog(\'close\');');

				return $dia->xhrout();
			}
		}

		return [
			'status' => 1,
			'script' => 'pulseError("Quiz konnte nicht gestartet werden...");'
		];
	}

	private function abortOrOpenDialog(int $quizSessionId): string
	{
		return '
				$("body").append(\'<div id="abortOrPause">' . $this->sanitizerService->jsSafe($this->view->abortOrPause()) . '</div>\');
				$("#abortOrPause").dialog({
					autoOpen: false,
					title: "Quiz wirklich abbrechen?",
					modal: true,
					buttons: [
						{
							text: "Quiz pausieren",
							click: function(){
								$(this).dialog("close");
								ajreq("pause",{app:"quiz",sid:' . $quizSessionId . '});
							}
						}
					]
				});';
	}

	public function endpopup()
	{
		$dia = new XhrDialog();

		$dia->addOpt('width', 720);

		$dia->addAbortButton();

		if ($this->session->get('hastodoquiz-id') == Role::FOODSAVER) {
			$dia->addButton('Jetzt mit dem Quiz meine Rolle als Foodsaver bestätigen', 'goTo(\'/?page=settings&sub=upgrade/up_fs\');');
		} elseif ($this->session->get('hastodoquiz-id') == Role::STORE_MANAGER) {
			$dia->addButton('Jetzt mit dem Quiz meine Rolle als Betriebsverantwortliche*r bestätigen', 'goTo(\'/?page=settings&sub=upgrade/up_bip\');');
		} elseif ($this->session->get('hastodoquiz-id') == Role::AMBASSADOR) {
			$dia->addButton('Jetzt mit dem Quiz meine Rolle als Botschafter*In bestätigen', 'goTo(\'/?page=settings&sub=upgrade/up_bot\');');
		}

		$content = $this->contentGateway->get(36);
		$dia->setTitle($content['title']);
		$dia->addContent($content['body']);

		return $dia->xhrout();
	}

	public function quizpopup()
	{
		if ($this->session->may('fs')) {
			$nextRole = $this->session->get('hastodoquiz-id');
			if (!$this->quizSessionGateway->hasPassedQuiz($this->session->id(), $nextRole)) {
				$dia = new XhrDialog();
				$dia->addOpt('width', 720);
				$dia->addAbortButton();

				if ($nextRole == Role::FOODSAVER) {
					$dia->addButton('Ja, ich möchte jetzt mit dem Quiz meine Rolle als Foodsaver bestätigen.', 'goTo(\'/?page=settings&sub=upgrade/up_fs\');');
				} elseif ($nextRole == Role::STORE_MANAGER) {
					$dia->addButton('Ja, ich möchte jetzt mit dem Quiz meine Rolle als Betriebsverantwortliche/r bestätigen.', 'goTo(\'/?page=settings&sub=upgrade/up_bip\');');
				} elseif ($nextRole == Role::AMBASSADOR) {
					$dia->addButton('Ja, ich möchte jetzt mit dem Quiz meine Rolle als Botschafter*In bestätigen.', 'goTo(\'/?page=settings&sub=upgrade/up_bot\');');
				}

				$content = $this->contentGateway->get(18);
				$dia->setTitle($content['title']);
				$dia->addContent($content['body']);

				return $dia->xhrout();
			}
		}

		return [
			'status' => 0
		];
	}

	public function addcomment()
	{
		if ($this->session->may() && !empty($_GET['comment']) && (int)$_GET['id'] > 0) {
			$this->quizGateway->addUserComment((int)$_GET['id'], $this->session->id(), $_GET['comment']);

			return [
				'status' => 1,
				'script' => 'pulseInfo("Kommentar wurde gespeichert");$("#qcomment-' . (int)$_GET['id'] . '").hide();'
			];
		}
	}

	/**
	 * xhr request to get next question stored in the users session.
	 */
	public function next(): array
	{
		if (!$this->session->may()) {
			return [];
		}

		if ($questions = $this->session->get('quiz-questions')) {
			$dia = new XhrDialog();
			$dia->addClass('quiz-questiondialog');
			// get quiz_index it is the current array index of the questions
			$quizIndex = $this->session->get('quiz-index');

			/*
			 * If the quiz index is 0 we have to start a new quiz session
			 */

			$easyMode = 0;
			if ($this->session->get('quiz-easymode')) {
				$easyMode = 1;
			}

			if ($quizIndex == 0) {
				$quizId = $this->session->get('quiz-id');
				$quiz = $this->quizGateway->getQuiz($quizId);
				// init quiz session in DB
				$sessionId = $this->quizSessionGateway->initQuizSession($this->session->id(), $quizId, $questions, $quiz['maxfp'], $quiz['questcount'], $easyMode);
				if ($sessionId > 0) {
					$this->session->set('quiz-session', $sessionId);
				}
			}

			// this variable we need to output an message that the last question was only a joke
			$was_a_joke = false;

			/*
			 *  check if an answered quiz question is arrived
			 */
			if (isset($_GET['answer'])) {
				/*
				 * parse the anser parameter
				 */
				$answers = urldecode($_GET['answer']);
				$params = [];
				parse_str($_GET['answer'], $params);

				/*
				 * store params in the quiz array to save users answers
				 */
				if (isset($params['qanswers'])) {
					$questions[($quizIndex - 1)]['answers'] = $params['qanswers'];
				}

				/*
				 * check if there are 0 point for the questions its a joke
				 */
				if ($questions[($quizIndex - 1)]['fp'] == 0) {
					$was_a_joke = true;
				}

				/*
				 * store the time how much time has the user need
				 */
				$questions[($quizIndex - 1)]['userduration'] = (time() - (int)$this->session->get('quiz-quest-start'));

				/*
				 * has store noco ;) its the value when the user marked that no answer is correct
				 */
				$questions[($quizIndex - 1)]['noco'] = (int)$_GET['noco'];

				/*
				 * And store it all back to the session
				 */
				$this->session->set('quiz-questions', $questions);
			}

			/*
			 * Have a look has the user entered an comment for this question?
			*/
			if (isset($_GET['comment']) && !empty($_GET['comment'])) {
				$comment = strip_tags($_GET['commentanswers'] . $_GET['comment']);

				// if yes lets store in the db
				$this->quizGateway->addUserComment((int)$_GET['qid'], $this->session->id(), $comment);
			}

			/*
			 * Check the special param if the next question should not be displayed
			 */
			if (isset($_GET['special'])) {
				// make a break
				if ($_GET['special'] == 'pause') {
					$this->quizSessionGateway->updateQuizSession($this->session->get('quiz-session'), $questions, $quizIndex);

					return $this->pause();
				}

				if ($_GET['special'] == 'result') {
					$this->quizSessionGateway->updateQuizSession($this->session->get('quiz-session'), $questions, $quizIndex);

					return $this->resultNew($questions[($quizIndex - 1)], $dia->getId());
				}
			}

			/*
			 * check if there is a next question in quiz array push it to the user
			 * othwise forward to the result of the quiz
			 */
			if (isset($questions[$quizIndex])) {
				// get the question
				if ($question = $this->quizGateway->getQuestion($questions[$quizIndex]['id'])) {
					// get possible answers
					$comment_answers = '';
					if ($answers = $this->quizGateway->getAnswers($question['id'])) {
						// random sorting for the answers
						shuffle($answers);

						$x = 1;
						foreach ($answers as $a) {
							$comment_answers .= $x . '. Frage #' . $a['id'] . ' => ' . preg_replace('/[^a-zA-Z0-9\ \.]/', '', $this->sanitizerService->tt($a['text'], 25)) . "\n";
							++$x;
						}

						/*
						 * increase the question index so we are at the next question ;)
						 */
						++$quizIndex;
						$this->session->set('quiz-index', $quizIndex);

						// update quiz session
						$session_id = $this->session->get('quiz-session');
						$this->quizSessionGateway->updateQuizSession($session_id, $questions, $quizIndex);
						$this->session->set('quiz-quest-start', time());

						/*
						 * let's prepare the output dialog
						 */
						$dia->addOpt('width', 1000);
						$dia->addOpt('height', '($(window).height()-40)', false);
						$dia->addOpt('position', 'center');
						$dia->setTitle('Frage ' . ($quizIndex) . ' / ' . count($questions));

						$dia->addContent($this->view->quizQuestion($question, $answers));

						/*
						 * for later function is not ready yet :)
						 */
						$dia->addButton('Weiter', 'questcheckresult();return false;');
						$dia->addButton('Pause', 'ajreq(\'pause\',{app:\'quiz\',sid:\'' . $session_id . '\'});');

						$dia->addButton('nächste Frage', 'ajreq(\'next\',{app:\'quiz\',qid:' . (int)$question['id'] . ',commentanswers:"' . $this->sanitizerService->jsSafe($comment_answers) . '"});$(".quiz-questiondialog .ui-dialog-buttonset .ui-button").button( "option", "disabled", true );$(".quiz-questiondialog .ui-dialog-buttonset .ui-button span").prepend(\'<i class="fas fa-spinner fa-spin"></i> \')');

						/*
						 * add next() Button
						 */

						$dia->addOpt('open', '
						function(){
							setTimeout(function(){
								$close = $("#' . $dia->getId() . '").prev().children(".ui-dialog-titlebar-close");
								$close.off("click");
								$close.on("click", function(){
									ajreq("pause",{app:"quiz",sid:' . (int)$session_id . '});
									//abortOrPause("' . $dia->getId() . '");
								});
								$("#quizcomment").hide();
								$(".quiz-questiondialog .ui-dialog-buttonset button:last").hide();
								$(".quiz-questiondialog .ui-dialog-buttonset .ui-button:contains(\'Pause\')").hide();
								$(".ui-dialog-titlebar-close").hide();
							},100);
						}', false);

						$return = $dia->xhrout();

						// additional output if it was a joke question
						if ($was_a_joke) {
							$return['script'] .= 'pulseInfo("<h3>Das war eine Scherzfrage</h3>Du kannst beruhigt weitermachen und auch wenn die möglichen Antworten nicht falsch sind, müssen diese Fragen nicht richtig beantwortet werden. Sie dienen lediglich zum Auflockern für Zwischendurch ;)",{sticky:true});';
						}

						/*
						 * strange but it works ;) generate the js code and send is to the client for execute
						 */

						$quizbreath = '
							$(\'#quizwrapper\').show();
							$(\'#quizbreath\').hide();
							var count = ' . (int)$question['duration'] . ';
							var counter = null;
						';

						if ($easyMode == 0) {
							$quizbreath = '
							$(\'#quizwrapper\').hide();
							$(\'#quizbreath\').show();
							$("#' . $dia->getId() . '").next(".ui-dialog-buttonpane").css("visibility","hidden");
							var count = ' . (int)$question['duration'] . ';
							var counter = null;

							function timer()
							{
							  count--;
					          $("#countdown").progressbar("value",count);
							  //$("#countdown").text((count)+"");
							  if (count <= 0)
							  {
							     questcheckresult(true);
							     return;
							  }
							}

							setTimeout(function(){
								$(\'#quizbreath span\').text("Auf die Plätze!");
							},3000);
							setTimeout(function(){
								$(\'#quizbreath span\').text("Fertig...");
							},4000);
							setTimeout(function(){
								$(\'#quizbreath span\').text("Weiter gehts!");
							},5000);

							setTimeout(function(){


								counter = setInterval(timer, 1000);
								$("#countdown").progressbar({
						             value: ' . $question['duration'] . ',
						             max:' . $question['duration'] . '
						        });

								$(\'#quizwrapper\').show();
								$(\'#quizbreath\').hide();
								$(".ui-dialog-buttonpane").css("visibility","visible");
							},6000);';
						}

						$return['script'] .= '

							function abortOrPause()
							{
								$("#abortOrPause").dialog("open");
							}

							function questcomment(el)
							{
								if($(\'#qanswers input:checked\').length > 0)
								{
									clearInterval(counter);
									$(".ui-dialog-buttonpane button:contains(\'Kommentar\')").hide();
									$("#quizwrapper input, #countdown").hide();
									$("#quizwrapper").css({
										"height":"50%",
										"overflow":"auto"
									});
									$("#quizcomment").show();
								}
								else
								{
									pulseError(\'Bitte triff erst eine Auswahl!\')
								}
							}

							function questgonext(special)
							{
								if(special == undefined)
								{
									special = 0;
								}
								clearInterval(counter);
								ajreq(\'next\',{answer:$(\'.qanswers\').serialize(),noco:$(\'.nocheck:checked\').length,app:\'quiz\',commentanswers:"' . $this->sanitizerService->jsSafe($comment_answers) . '",comment:$(\'#quizusercomment\').val(),qid:' . (int)$question['id'] . ',special:special});
							}

							function breaknext()
							{
								if($(\'#qanswers input:checked\').length > 0)
								{
									questgonext("pause");
								}
								else
								{
									pulseError(\'Bitte triff erst eine Auswahl!\')
								}
							}

							function questionnext()
							{
								if($(\'#qanswers input:checked\').length > 0)
								{
									questgonext();
								}
								else
								{
									pulseError(\'Bitte triff eine Auswahl!\')
								}
							}

							function questcheckresult(nowait)
							{
								if(nowait == undefined)
								{
									nowait = false;
								}
								if(nowait || $(\'#qanswers input:checked\').length > 0)
								{
									questgonext("result");
								}
								else
								{
									pulseError(\'Bitte triff erst eine Auswahl!\')
								}
							}

							$("li.noanswer").on("click", function(){
								setTimeout(function(){
									if($("input.nocheck:checked").length > 0)
									{
										$("li.answer input").each(function(){
											this.checked = false;
										});
									}
								},50);
							});

							$("li.answer input").on("click", function(){
								if(this.checked)
								{

								}
							});

							$("li.answer, li.noanswer").on("click", function(ev){

								var nName = ev.target.nodeName.toLowerCase();

								if(nName == "li" || nName == "label")
								{
									if($(this).children("label").children("input:checked").length >= 1)
									{
										$(this).children("label").children("input")[0].checked = false;
									}
									else
									{
										$(this).children("label").children("input")[0].checked = true;
									}
								}
							});

							$("li.answer").on("click", function(){

								if($("li.answer input:checked").length > 0)
								{
									$("input.nocheck")[0].checked = false;
								}
							});

							var width = 1000;
							if($(window).width() < 1000)
							{
								width = ($(window).width()-40);
							}
							$("#' . $dia->getId() . '").dialog("option",{
								width:width,
								height:($(window).height()-40)
							});
							$(window).on("resize", function(){
								var width = 1000;
								if($(window).width() < 1000)
								{
									width = ($(window).width()-40);
								}
								$("#' . $dia->getId() . '").dialog("option",{
									width:width,
									height:($(window).height()-40)
								});
							});



						' . $quizbreath;

						return $return;
					}

					++$quizIndex;
					$this->session->set('quiz-index', $quizIndex);

					return [
						'status' => 1,
						'script' => 'pulseError("Diese Frage hat keine Antworten. Überspringe...");ajreq("next",{app:"quiz"});'
					];
				}
			} else {
				return $this->quizResult();
			}
		}

		++$quizIndex;
		$this->session->set('quiz-index', $quizIndex);

		return [
			'status' => 1,
			'script' => 'pulseError("Es ist ein Fehler aufgetreten. Frage wird übersprungen.");ajreq("next",{app:"quiz"});'
		];
	}

	private function quizResult()
	{
		if (!$this->session->may()) {
			return false;
		}

		if ($quiz = $this->quizGateway->getQuiz($this->session->get('quiz-id'))) {
			if ($questions = $this->session->get('quiz-questions')) {
				if ($rightQuestions = $this->quizGateway->getRightQuestions($this->session->get('quiz-id'))) {
					$explains = [];
					$failurePoints = 0;
					$question_number = 0;
					foreach ($questions as $q_key => $q) {
						++$question_number;
						$valid = $this->validateAnswer($rightQuestions, $q);
						$failurePoints += $valid['fp'];
						foreach ($valid['explain'] as $e) {
							if (!isset($explains[$q['id']])) {
								$explains[$q['id']] = $rightQuestions[$q['id']];
								$explains[$q['id']]['explains'] = [];
							}
							$explains[$q['id']]['explains'][] = $e;
							$explains[$q['id']]['number'] = $question_number;
							$explains[$q['id']]['percent'] = round($valid['percent'], 2);
							$explains[$q['id']]['userfp'] = round($valid['fp'], 2);
						}
					}
				}

				$this->quizSessionGateway->finishQuizSession($this->session->get('quiz-session'), $questions, $explains, $failurePoints, $quiz['maxfp']);

				return [
					'status' => 1,
					'script' => 'goTo("/?page=settings&sub=quizsession&sid=' . (int)$this->session->get('quiz-session') . '");'
				];
			}
		}
	}

	private function resultNew($question, $diaId)
	{
		$uanswers = [];
		$out = [];

		if (isset($question['answers']) && is_array($question['answers'])) {
			foreach ($question['answers'] as $a) {
				$uanswers[$a] = $a;
			}
		}
		// get the question
		if ($quest = $this->quizGateway->getQuestion($question['id'])) {
			// get possible answers
			if ($answers = $this->quizGateway->getAnswers($question['id'])) {
				$joke = false;
				if ($question['fp'] == 0) {
					$joke = true;
				}

				foreach ($answers as $a) {
					// schwerzfrageoder
					if ($joke) {
						$bg = '#F5F5B5';
						$atext = '';
						$color = '#4A3520';
					} //neutraleantwort
					elseif ($a['right'] == AnswerRating::NEUTRAL) {
						$atext = 'Neutrale Antwort wird nicht gewertet';
						$bg = '#F5F5B5';
						$color = '#4A3520';
					} // Antwort richtig angeklickt
					elseif ((isset($uanswers[$a['id']]) && $a['right'] == AnswerRating::CORRECT) || (!isset($uanswers[$a['id']]) && $a['right'] == AnswerRating::WRONG)) {
						if ($a['right'] == AnswerRating::WRONG) {
							$atext = 'Diese Antwort war natürlich falsch. Das hast Du richtig erkannt.';
						} else {
							$atext = 'Richtig! Diese Antwort stimmt.';
						}
						$bg = 'var(--fs-green)';
						$color = '#ffffff';
					} // Antwort richtig, weil nicht angeklickt
					else {
						if ($a['right'] == AnswerRating::WRONG) {
							$atext = 'Falsch! Diese Antwort stimmt nicht.';
						} else {
							$atext = 'Auch diese Antwort wäre richtig gewesen.';
						}
						$bg = '#E74955';
						$color = '#ffffff';
					}

					$out[] = [
						'id' => $a['id'],
						'exp' => nl2br($a['explanation']),
						'bg' => $bg,
						'atext' => $atext,
						'color' => $color
					];
				}
			}
		}

		return [
			'status' => 1,
			'script' => '
				$(".ui-dialog-titlebar-close").show();

				$(".quiz-questiondialog .ui-dialog-buttonset .ui-button").hide();
				$(".quiz-questiondialog .ui-dialog-buttonset .ui-button:last").show();
				$(".quiz-questiondialog .ui-dialog-buttonset .ui-button:contains(\'Pause\')").show();
				$("#quizcomment").show();
				$("#countdown").hide();

				var answers = ' . json_encode($out) . ';
				$(".answer, .answer span").css({
					"cursor":"default"
				});
				$("#qanswers input").attr("disabled",true);
				$(".noanswer").hide();
				for(var i=0;i<answers.length;i++)
				{
					$("#qanswer-" + answers[i].id).css({
						"background-color":answers[i].bg,
						"color":answers[i].color
					}).effect("highlight").attr("onmouseover","return false;").attr("onmouseout","return false;");
					$("#qanswer-" + answers[i].id).append(\'<div style="margin:15px 0 0 43px;">\'+answers[i].atext+\'</div><div id="explanation-\'+answers[i].id+\'" style="font-weight:bold;margin:15px 0 0 43px;"><span class="tail">\'+answers[i].exp.substr(0,60)+\'...</span><span class="complete" style="display:none">\'+answers[i].exp+\'</span> <a style="color:\'+answers[i].color+\';font-weight:bold;" href="#" onclick="$(this).parent().children().toggle();return false;">mehr lesen <i class="far fa-arrow-circle-right"></i></a></div>\');
				}

			'
		];
	}

	public function pause()
	{
		$dia = new XhrDialog();
		$dia->setTitle('Pause');
		$dia->addContent($this->view->pause());
		$dia->addJsBefore('

		');
		$dia->addJs('
			clearInterval(counter);
		');

		$dia->addOpt('open', '
			function(){
				setTimeout(function(){
					$close = $("#' . $dia->getId() . '").prev().children(".ui-dialog-titlebar-close");
					//$close.off("click");
					$close.on("click", function(){
						ajreq(\'next\',{app:\'quiz\'});
					});
				},200);
			}', false);

		$dia->addButton('Später weitermachen', '$(this).dialog("close");');
		$dia->addButton('weiter gehts!', 'ajreq(\'next\',{app:\'quiz\'});');

		return $dia->xhrout();
	}

	private function validateAnswer($rightQuestions, $question)
	{
		$explains = [];

		$wrongAnswers = 0;
		$checkCount = 0;

		$everything_false = true;

		$useranswers = [];
		if (isset($question['answers']) && is_array($question['answers'])) {
			$useranswers = $question['answers'];
		}
		$allNeutral = true;
		if (isset($rightQuestions[$question['id']]['answers'])) {
			foreach ($rightQuestions[$question['id']]['answers'] as $id => $a) {
				switch ($a['right']) {
					case AnswerRating::WRONG:
						$checkCount++;
						$allNeutral = false;
						if (in_array($a['id'], $useranswers)) {
							++$wrongAnswers;
							// Erklärungen anfügen
							$explains[$a['id']] = $rightQuestions[$question['id']]['answers'][$a['id']];
						}
						break;
					case AnswerRating::CORRECT:
						$everything_false = false;
						$allNeutral = false;
						++$checkCount;
						if (!in_array($a['id'], $useranswers)) {
							++$wrongAnswers;
							// Erklärungen anfügen
							$explains[$a['id']] = $rightQuestions[$question['id']]['answers'][$a['id']];
						}
						break;
					default:

						// Bei neutralen Fragen einfach Erklärung anfügen
						$explains[$a['id']] = $rightQuestions[$question['id']]['answers'][$a['id']];
						break;
				}
			}
		} else {
			$wrongAnswers = count($rightQuestions[$question['id']]['answers']);
		}

		// wie viel Prozent sind falsch?
		$percent = $this->percentFrom($checkCount, $wrongAnswers);

		$failurePoints = $this->percentTo($question['fp'], $percent);

		// wenn alles falsch angeklickt wurde, das aber nicht stimmt, gibt's die volle Fehlerpunktezahl
		if (
			(!$everything_false && !isset($question['noco']))
			||
			(!$everything_false && (int)$question['noco'] > 0)
		) {
			$failurePoints = $question['fp'];
			$percent = 100;
		}

		// fix alle Fragen sind neutral
		if ($allNeutral) {
			$failurePoints = 0;
			$percent = '0';
		}

		return [
			'fp' => $failurePoints,
			'explain' => $explains,
			'percent' => $percent
		];
	}

	private function getRandomQuestions($quiz_id, $count = 6)
	{
		$count_questions = $count;

		if ($questionCounts = $this->quizGateway->getQuestionCountByFailurePoints($quiz_id)) {
			// Wie viele Fragen gibt es insgesamt?
			$summe = 0;
			foreach ($questionCounts as $failurePoints => $count) {
				$summe += $failurePoints;
			}

			$out = [];
			// Prozentanteil von jeder Fragenart
			foreach ($questionCounts as $failurePoints => $failurePoints) {
				$percent = round($this->percentFrom($summe, $failurePoints));

				$count = round($this->percentTo($count_questions, $percent));

				if ($rquest = $this->quizGateway->getRandomQuestions($count, $failurePoints, $quiz_id)) {
					foreach ($rquest as $r) {
						$out[] = $r;
					}
				}
			}

			if (!empty($out)) {
				shuffle($out);

				return $out;
			}

			return false;
		}
	}

	private function percentTo($part, $percent)
	{
		return ($part / 100) * $percent;
	}

	private function percentFrom($total, $part)
	{
		if ($total == 0) {
			return 100;
		}

		return $part / ($total / 100);
	}

	public function updatequest()
	{
		if ($this->quizPermissions->mayEditQuiz()) {
			/*
			 *   [id] => 10
				 [text] => test
				 [fp] => 3
			 */
			if (isset($_GET['text'], $_GET['fp'], $_GET['id'])) {
				$failurePoints = (int)$_GET['fp'];
				$text = strip_tags($_GET['text']);
				$duration = (int)$_GET['duration'];
				$wikiLink = strip_tags($_GET['wikilink']);

				if (!empty($text)) {
					$this->quizGateway->updateQuestion($_GET['id'], $_GET['qid'], $text, $failurePoints, $duration, $wikiLink);
					$this->flashMessageHelper->info('Frage wurde geändert');

					return [
						'status' => 1,
						'script' => 'reload();'
					];
				}

				return [
					'status' => 1,
					'script' => 'pulseError("Du solltest einen Text angeben ;)");'
				];
			}
		}
	}
}
