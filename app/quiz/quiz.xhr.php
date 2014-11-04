<?php 
class QuizXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new QuizModel();
		$this->view = new QuizView();

		parent::__construct();
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
		if(isOrgaTeam())
		{
			if(isset($_GET['text']) && isset($_GET['fp']) && isset($_GET['qid']))
			{
				$fp = (int)$_GET['fp'];
				$text = strip_tags($_GET['text']);
				$duration = (int)$_GET['duration'];
				
				if(!empty($text))
				{
					if($id = $this->model->addQuestion($_GET['qid'],$text,$fp,$duration))
					{
						info('Frage wurde angelegt');
						return array(
							'status' => 1,
							'script' => 'goTo("?page=quiz&id='.(int)$_GET['qid'].'&fid='.(int)$id.'");'
						);	
					}
				}
				else
				{
					return array(
						'status' => 1,
						'script' => 'pulseError("Du solltest Eine Frage angeben ;)");'
					);
				}
			}
		}
		
	}
	
	public function delquest()
	{
		if(isOrgaTeam() && isset($_GET['id']))
		{
			$this->model->deleteQuest($_GET['id']);
			return array(
					'status' => 1,
					'script' => '$(".question-'.(int)$_GET['id'].'").remove();$("#questions").accordion("refresh");pulseInfo("Frage gelöscht!");'
			);
		}
	}
	
	public function delanswer()
	{
		if(isOrgaTeam() && isset($_GET['id']))
		{
			$this->model->deleteAnswer($_GET['id']);
			return array(
				'status' => 1,
				'script' => '$("#answer-'.(int)$_GET['id'].'").remove();pulseInfo("Antwort gelöscht!");'		
			);
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
		
		if(isOrgaTeam())
		{
			if(isset($_GET['text']) && isset($_GET['right']) && isset($_GET['qid']))
			{
				$text = strip_tags($_GET['text']);
				$exp = strip_tags($_GET['explanation']);
				$right = (int)$_GET['right'];
		
				if(!empty($text) && ($right == 0 || $right == 1 || $right == 2))
				{
					if($id = $this->model->addAnswer($_GET['qid'],$text,$exp,$right))
					{
						return array(
								'status' => 1,
								'script' => 'pulseInfo("Antwort wurde angelegt");$("#answerlist-'.(int)$_GET['qid'].'").append(\'<li class="right-'.(int)$right.'">'.jsSafe(nl2br(strip_tags($text))).'</li>\');$( "#questions" ).accordion( "refresh" );'
						);
					}
				}
				else
				{
					return array(
							'status' => 1,
							'script' => 'pulseError("Du solltest Einen Text angeben ;)");'
					);
				}
			}
		}
		
	}
	
	public function updateansw()
	{
		if(isOrgaTeam())
		{
			if(isset($_GET['text']) && isset($_GET['right']) && isset($_GET['id']))
			{
				$text = strip_tags($_GET['text']);
				$exp = strip_tags($_GET['explanation']);
				$right = (int)$_GET['right'];
			
				if(!empty($text) && ($right == 0 || $right == 1 || $right == 2))
				{
					$this->model->updateAnswer($_GET['id'],array(
						'text' => $text,
						'explanation' => $exp,
						'right' => $right		
					));
					return array(
							'status' => 1,
							'script' => 'pulseInfo("Antwort wurde geändert");$("#answer-'.(int)$_GET['id'].'").replaceWith(\'<li id="answer-'.(int)$_GET['id'].'" class="right-'.(int)$right.'">'.jsSafe(nl2br(strip_tags($text))).'</li>\');$( "#questions" ).accordion( "refresh" );'
					);
					
				}
				else
				{
					return array(
							'status' => 1,
							'script' => 'pulseError("Du solltest Einen Text angeben ;)");'
					);
				}
			}
		}
	}
	
	public function editanswer()
	{
		if(isOrgaTeam())
		{
			if($answer = $this->model->getAnswer($_GET['id']))
			{
				$answer['isright'] = $answer['right'];
				setEditData($answer);
				$dia = new XhrDialog();
				
				$dia->addAbortButton();
				$dia->setTitle('Antwort bearbeiten');
				$dia->addOpt('width', 500);
				$dia->addContent($this->view->answerForm());
				
				$dia->addButton('Speichern', 'ajreq(\'updateansw\',{id:'.(int)$_GET['id'].',explanation:$(\'#explanation\').val(),text:$(\'#text\').val(),right:$(\'#isright\').val()});$(\'#'.$dia->getId().'\').dialog("close");');
				
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
		$dia->setTitle('Neue Antwort zu Frage #'.(int)$_GET['qid']);
		$dia->addOpt('width', 500);
		$dia->addContent($this->view->answerForm());
		
		$dia->addButton('Speichern', 'ajreq(\'addansw\',{qid:'.(int)$_GET['qid'].',explanation:$(\'#explanation\').val(),text:$(\'#text\').val(),right:$(\'#right\').val()});$(\'#'.$dia->getId().'\').dialog("close");');
		
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
		
		$dia->addButton('Speichern', 'ajreq(\'addquest\',{qid:'.(int)$_GET['qid'].',duration:$(\'#duration\').val(),text:$(\'#text\').val(),fp:$(\'#fp\').val()});');
		
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
		if(isOrgaTeam())
		{
			if($quest = $this->model->getQuestion($_GET['id']))
			{
				setEditData($quest);
				$dia = new XhrDialog();
				
				$dia->addAbortButton();
				$dia->setTitle('Frage bearbeiten');
				$dia->addOpt('width', 500);
				$dia->addContent($this->view->questionForm());
				
				$dia->addButton('Speichern', 'ajreq(\'updatequest\',{id:'.(int)$_GET['id'].',qid:'.(int)$_GET['qid'].',wikilink:$(\'#wikilink\').val(),duration:$(\'#duration\').val(),text:$(\'#text\').val(),fp:$(\'#fp\').val()});');
				
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
		if(S::may())
		{
			$this->model->abortSession($_GET['sid']);
			
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Quiz wurde abgebrochen");reload();'	
			);
		}
	}
	
	private function abortOrOpenDialog($session_id)
	{
		return '
				$("body").append(\'<div id="abortOrPause">'.JsSafe($this->view->abortOrPause()).'</div>\');
				$("#abortOrPause").dialog({
					autoOpen: false,
					title: "Quiz wirklich abbrechen?",
					modal: true,
					buttons: [
						{
							text: "Quiz-Pausieren",
							click: function(){
								$(this).dialog("close");
								ajreq("pause",{app:"quiz",sid:'.(int)$session_id.'});
							}
						},
						{
							text: "Quiz-Abbrechen",
							click: function(){
								if(confirm("Bist Du Dir ganz sicher? Du kannst auch pausieren, Deinen Computer ausschalten und in ein paar Tagen weitermachen ;)"))
								{
									ajreq("abort",{app:"quiz",sid:'.(int)$session_id.'});	
								}
								$(this).dialog("close");
							}
						}
					]
				});';
	}
	
	private function replaceDoubles($questions)
	{
		//print_r($questions);
		return $questions;
	}
	
	/**
	 * Method to initiate a quiz session so get the defined amount of questions sort it randomly and store it in an session variable
	 * 
	 */
	public function startquiz()
	{
		if(!S::may())
		{
			return false;
		}
		/*
		 * First we want to check is there an quiz session what the user have lost?
		 */
		if($session = $this->model->getExsistingSession($_GET['qid']))
		{
			// if yes reinitiate the running quiz session
			S::set('quiz-id', (int)$_GET['qid']);
			S::set('quiz-questions', $session['quiz_questions']);
			S::set('quiz-index', $session['quiz_index']);
			S::set('quiz-session', $session['id']);
			
			/*
			 * Make a little output that the user can continue the quiz
			 */
			$dia = new XhrDialog();
			
			$dia->setTitle('Quiz fortführen');
			
			$dia->addContent(v_input_wrapper('Du hast Dein Quiz nicht beendet', '<p>Aber keine Sorge Du kannst einfach jetzt das Quiz zum Ende bringen.</p><p>Also viel Spaß beim weiterquizzen.</p>'));
			
			$dia->addButton('Quiz Abbrechen', 'if(confirm(\'Möchtest Du das laufende Quiz wirklich beenden? Leider müssten wir das als Fehlversuch bewerten.\')){ajreq(\'abort\',{app:\'quiz\',sid:'.(int)$session['id'].'});}');
			$dia->addButton('Quiz fortführen', 'ajreq(\'next\',{app:\'quiz\'});');
			
			$return = $dia->xhrout();
			
			$return['script'] .= $this->abortOrOpenDialog($session['id']);
			
			return $return;
			
		}
		/*
		 * Otherwiser we start a new quiz session
		 */
		else if($quiz = $this->model->getQuiz($_GET['qid']))
		{
			/*
			 * first get random sorted quiz questions
			 */
			if($questions = $this->getRandomQuestions($_GET['qid'],$quiz['questcount']))
			{
				//Get the description how the quiz works
				$content = $this->model->getContent(17);
				
				// for safety check if there are not to many questions
				$questions = array_slice($questions, 0, (int)$quiz['questcount']);
				
				// check for double question (bugfix)
				$questions = $this->replaceDoubles($questions);
				
				/*
				 * Store quiz data in the users session
				 */
				S::set('quiz-id', (int)$_GET['qid']);
				S::set('quiz-questions', $questions);
				S::set('quiz-index', 0);
				
				/*
				 * Make a litte output for the user that he/she cat just start the quiz now
				 */
				$dia = new XhrDialog();
				$dia->addOpt('width', 600);
				//$dia->addOpt('height', 480);
				$dia->setTitle($quiz['name'].'-Quiz');
				$dia->addContent($this->view->initQuiz($quiz,$content));
				$dia->addAbortButton();
				$dia->addButton('Quiz Starten', 'ajreq(\'next\',{app:\'quiz\'});$(\'#'.$dia->getId().'\').dialog(\'close\');');
				
				$return = $dia->xhrout();
				
				$return['script'] .= $this->abortOrOpenDialog($session['id']);
				
				return $return;
				
			}
		}
		
		/*
		 * If we cant get an quiz from the db send an error
		 */
		return array(
			'status' => 1,
			'script' => 'pulseError("Quiz konnte nicht gestartet werden...");'
		);
	}
	
	public function testquiz()
	{
		$dia = new XhrDialog();
		$content = $this->model->getContent(18);
		$dia->setTitle($content['title']);
		
		$dia->addOpt('width', 450);
		
		$dia->addContent($content['body']);
		
		$dia->addAbortButton();
		$dia->addButton('Ja Ich möchte das Quiz jetzt ausprobieren!', 'goTo(\'?page=settings&sub=upgrade/up_fs\');');
		
		return $dia->xhrout();
		
	}
	
	public function addcomment()
	{
		if(!empty($_GET['comment']) && (int)$_GET['id'] > 0)
		{
			$this->model->addUserComment((int)$_GET['id'], $_GET['comment']);
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Kommentar wurde gespeichert");$("#qcomment-'.(int)$_GET['id'].'").hide();'		
			);
		}
		
	}
	/**
	 * xhr request to get next question stored in the users session
	 * 
	 * @return boolean|string|multitype:number string |Ambigous <boolean, multitype:number string , string>
	 */
	public function next()
	{
		if(!S::may())
		{
			return false;
		}
		/*
		 * Try to find a current quiz session ant retrieve the questions
		 */
		if($quiz = S::get('quiz-questions'))
		{
			$dia = new XhrDialog();
			// get quiz_index it is the current array index of the questions
			$i = S::get('quiz-index');
			
			/*
			 * If the quiz index is 0 we have to start a new quiz session 
			 */
			if($i == 0)
			{
				$quuizz = $this->model->getQuiz(S::get('quiz-id'));
				// init quiz session in DB
				if($id = $this->model->initQuizSession(S::get('quiz-id'), $quiz, $quuizz['maxfp'], $quuizz['questcount']))
				{
					S::set('quiz-session', $id);
				}
			}
			
			// this variable we need to output an message that the last question was only a joke 
			$was_a_joke = false;
			
			/*
			 *  check if an answered quiz question is arrived
			 */
			if(isset($_GET['answer']))
			{
				/*
				 * parse the anser parameter
				 */
				$answers = urldecode($_GET['answer']);
				$params = array();
				parse_str($_GET['answer'], $params);
				
				/*
				 * store params in the quiz array to save users answers
				 */
				if(isset($params['qanswers']))
				{
					$quiz[($i-1)]['answers'] = $params['qanswers'];
				}
				
				/*
				 * check if there are 0 point for the questions its a joke
				 */
				if($quiz[($i-1)]['fp'] == 0)
				{
					$was_a_joke = true;
				}
				
				/*
				 * store the time how much time has the user need
				 */
				$quiz[($i-1)]['userduration'] = (time() - (int)S::get('quiz-quest-start'));
				
				/*
				 * has store noco ;) its the value when the user marked that no answer is correct
				 */
				$quiz[($i-1)]['noco'] = (int)$_GET['noco'];
				
				/*
				 * And store it all back to the session
				 */
				S::set('quiz-questions', $quiz);
			}
			
			/*
			 * Have a look has the user entered an comment for this question?
			*/
			if(isset($_GET['comment']) && !empty($_GET['comment']))
			{
				$comment = strip_tags($_GET['comment']);
				
				$comment = $_GET['commentanswers'] . $comment;
				
				// if yes lets store in the db
				$this->model->addUserComment((int)$_GET['qid'], $comment);
			}
			
			/*
			 * Check the special param if the next question should not be displayed
			 */
			if(isset($_GET['special']))
			{
				// make a break
				if($_GET['special'] == 'pause')
				{
					$this->model->updateQuizSession(S::get('quiz-session'), $quiz, $i);
					return $this->pause();
				}
				
				//
				if($_GET['special'] == 'result')
				{
					$this->model->updateQuizSession(S::get('quiz-session'), $quiz, $i);
					return $this->resultNew($quiz[($i-1)],$dia->getId());
				}
			}
			
			
			/*
			 * check if there is a next question in quiz array push it to the user
			 * othwise forward to the result of the quiz
			 */
			if(isset($quiz[$i]))
			{
				// get the question
				if($question = $this->model->getQuestion($quiz[$i]['id']))
				{		
					// get possible answers		
					$comment_aswers = '';
					if($answers = $this->model->getAnswers($question['id']))
					{
						// random sorting for the answers
						shuffle($answers);

						$x=1;
						foreach ($answers as $a)
						{
							$comment_aswers .= $x.'. Frage #'.$a['id'].' => '.tt($a['text'],35)."\n";
							$x++;
						}
						
						/*
						 * increase the question index so we are at the next question ;)
						 */ 
						$i++;
						S::set('quiz-index',$i);
						
						// update quiz session
						$this->model->updateQuizSession(S::get('quiz-session'), $quiz, $i);
						S::set('quiz-quest-start',time());
						
						/*
						 * let's prepare the output dialog
						 */
						
						//$dia->noClose();
						//$dia->addOpt('beforeClose', 'function(ev){abortOrPause();return false;}',false);
						$dia->addOpt('width', 1000);
						$dia->addOpt('height', '($(window).height()-40)',false);
						$dia->addOpt('position', 'center');
						$dia->setTitle('Frage '.($i).' / '.count($quiz));
						
						
						
						$dia->addContent($this->view->quizQuestion($question,$answers));
						$dia->addContent($this->view->quizComment());
						
						/*
						 * show the pause button only if there are more questions
						 */
						/*
						if($i < count($quiz))
						{
							$dia->addButton('Abschicken & Pause', 'breaknext();');
						}
						*/
						// add comment button
						//$dia->addButton('Kommentar abgeben & Weiter', 'questcomment(this);');
						
						/*
						 * for later function is not ready yet :)
						 */
						$dia->addButton('Weiter', 'questcheckresult();return false;');
						$dia->addButton('nächste Frage','ajreq(\'next\',{app:\'quiz\'});');
						/*
						 * add next() Button
						 */
						//$dia->addButton('Abssenden & Weiter', 'questionnext();');
						
						$dia->addOpt('open','
						function(){
							setTimeout(function(){
								$close = $("#'.$dia->getId().'").prev().children(".ui-dialog-titlebar-close");
								$close.unbind("click");
								$close.click(function(){
									
									abortOrPause("'.$dia->getId().'");
								});
								$("#quizcomment").hide();
								$(".ui-dialog-buttonset button:last").hide();
							},500);
						}',false);
						
						$return = $dia->xhrout();
						
						// additional output if it was a joke question
						if($was_a_joke)
						{
							$return['script'] .= 'pulseInfo("<h3>Das war eine Scherzfrage</h3>Du kannst beruhigt weitermachen und auch wenn die möglichen Antworten nicht falsch sind, müssen diese Fragen nicht richtig beantwortet werden, sie dienen lediglich des auflockerns für Zwischendurch ;)",{sticky:true});';
						}
						
						/*
						 * strange but it works ;) generate the js code and send is to the client for execute
						 */
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
									//$("#quizwrapper").hide();
									$("#quizwrapper input, #countdown").hide();
									$("#quizwrapper").css({
										"height":"50%",
										"overflow":"auto"
									});
									$("#quizcomment").show();
								}
								else
								{
									pulseError(\'Bitte treffe erst eine Auswahl!\')
								}
							}
							
							function questgonext(special)
							{
								if(special == undefined)
								{
									special = 0;
								}
								clearInterval(counter);
								ajreq(\'next\',{answer:$(\'.qanswers\').serialize(),noco:$(\'.nocheck:checked\').length,app:\'quiz\',commentanswers:"'.jsSafe($comment_aswers).'",comment:$(\'#quizusercomment\').val(),qid:'.(int)$question['id'].',special:special});
							}
							
							function breaknext()
							{
								if($(\'#qanswers input:checked\').length > 0)
								{
									//ajreq(\'pause\',{app:\'quiz\'});
									questgonext("pause");
								}
								else
								{
									pulseError(\'Bitte treffe erst eine Auswahl!\')
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
									pulseError(\'Bitte treffe eine Auswahl!\')
								}
							}
							
							function questcheckresult()
							{
								if($(\'#qanswers input:checked\').length > 0)
								{
									//ajreq(\'pause\',{app:\'quiz\'});
									questgonext("result");
								}
								else
								{
									pulseError(\'Bitte treffe erst eine Auswahl!\')
								}	
							}
										
							$("li.noanswer").click(function(){
								setTimeout(function(){
									if($("input.nocheck:checked").length > 0)
									{
										$("li.answer input").each(function(){
											this.checked = false;
										});
									}
								},50);
							});
							
							$("li.answer input").click(function(){
								if(this.checked)
								{
								
								}
							});
							
							$("li.answer, li.noanswer").click(function(ev){
								
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
							
							$("li.answer").click(function(){
								
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
							$("#'.$dia->getId().'").dialog("option",{
								width:width,
								height:($(window).height()-40)
							});
							$(window).resize(function(){
								var width = 1000;
								if($(window).width() < 1000)
								{
									width = ($(window).width()-40);
								}
								$("#'.$dia->getId().'").dialog("option",{
									width:width,
									height:($(window).height()-40)
								});
							});
						
							$(\'#quizwrapper\').hide();
							$(\'#quizbreath\').show();
							$("#'.$dia->getId().'").next(".ui-dialog-buttonpane").css("visibility","hidden");
							var count = '.(int)$question['duration'].';

							var counter = null;
							
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
					                  value: '.$question['duration'].',
					                  max:'.$question['duration'].'
					             });
									
								$(\'#quizwrapper\').show();
								$(\'#quizbreath\').hide();
								$(".ui-dialog-buttonpane").css("visibility","visible");
							},6000);
							
							function timer()
							{
							  count--;
					          $("#countdown").progressbar("value",count);
							  //$("#countdown").text((count)+"");
							  if (count <= 0)
							  {
							     //questgonext();
							     // ajreq(\'pause\',{app:\'quiz\',timefail:\'1\'});
					             ajreq(\'pause\',{app:\'quiz\'});
							     return;
							  }
							}
						';
						
						return $return;
					}
					else
					{
						$i++;
						S::set('quiz-index',$i);
						return array(
							'status' => 1,
							'script' => 'pulseError("Diese Frage hat Keine Antworten, überspringe...");ajreq("next",{app:"quiz"});'		
						);
					}
				}
			}
			else
			{
				return $this->quizResult();
			}
		}
		
		$i++;
		S::set('quiz-index',$i);
		return array(
				'status' => 1,
				'script' => 'pulseError("Es ist ein Fehler aufgetreten, Frage wird übersprungen");ajreq("next",{app:"quiz"});'
		);
		
	}
	
	private function quizResult()
	{
		if(!S::may())
		{
			return false;
		}
		
		if($quiz = $this->model->getQuiz(S::get('quiz-id')))
		{
			if($questions = S::get('quiz-questions'))
			{
				
				if($rightQuestions = $this->model->getRightQuestions(S::get('quiz-id')))
				{
					$explains = array();
					$fp = 0;
					$question_number = 0;
					foreach ($questions as $q_key => $q)
					{
						$question_number++;
						$valid = $this->validateAnswer($rightQuestions, $q);
						$fp += $valid['fp'];
						foreach ($valid['explain'] as $e)
						{
							if(!isset($explains[$q['id']]))
							{
								$explains[$q['id']] = $rightQuestions[$q['id']];
								$explains[$q['id']]['explains'] = array();
							}
							$explains[$q['id']]['explains'][] = $e;
							$explains[$q['id']]['number'] = $question_number;
							$explains[$q['id']]['percent'] = round($valid['percent'],2);
							$explains[$q['id']]['userfp'] = round($valid['fp'],2);
						}
					}
				}
				
				$this->model->finishQuiz(S::get('quiz-session'), $questions, $explains, $fp, $quiz['maxfp']);
				
				return array(
					'status' => 1,
					'script' => 'goTo("?page=settings&sub=quizsession&sid='.(int)S::get('quiz-session').'");'
				);
				
				//$this->model->updateQuizSession(S::get('quiz-id'), $questions, $explains, $fp, $quiz['maxfp']);
				
				$dia = new XhrDialog();
				$dia->setTitle('Ergebnis');
				
				$dia->addOpt('width', 600);
				$dia->addOpt('height', '($(window).height()-60)',false);
				
				$dia->addContent($this->view->result($explains,$fp,$quiz['maxfp']));
				
				$return = $dia->xhrout();
				$return['script'] .= '
				
				$("#explains").accordion({
						heightStyle: "content",
						animate: 200,
						collapsible: true,
						autoHeight: false, 
	    				active: false 
					});
					
					var width = 1000;
					if($(window).width() < 1000)
					{
						width = ($(window).width()-40);
					}
					$("#'.$dia->getId().'").dialog("option",{
						width:width,
						height:($(window).height()-40)
					});
					$(window).resize(function(){
						var width = 1000;
						if($(window).width() < 1000)
						{
							width = ($(window).width()-40);
						}
						$("#'.$dia->getId().'").dialog("option",{
							width:width,
							height:($(window).height()-40)
						});
					});';
				
				return $return;
				
			}
		}
	}
	
	private function result($question)
	{
		$answers = array();
		$joke = false;
		
		//if()
		
		foreach ($question['answers'] as $a)
		{
			$answers[$a] = $a;
		}
		// get the question
		if($quest = $this->model->getQuestion($question['id']))
		{		
					// get possible answers			
			if($answers = $this->model->getAnswers($question['id']))
			{
				/*
				print_r($question);
				print_r($answers);
				die();
				*/
				$joke = false;
				if($question['fp'] == 0)
				{
					$joke = true;
				}
				
				$out = '';
				foreach ($answers as $a)
				{
					$bg = '';
					$atext = '';
					
					if($joke)
					{
						$bg = 'transparent';
						$atext = '';
					}
					// Antwort richtig angeklickt
					else if((isset($answers[$a['id']]) && $a['right'] == 1) || (!isset($answers[$a['id']]) && $a['right'] == 0))
					{
						if($a['right'] == 0)
						{
							$atext = 'Diese Antwort war natürlich falsch, das hast Du richtig erkannt';
						}
						else
						{
							$atext = 'Richtig! Diese Antwort stimmt.';
						}
						$bg = '#599022';
					}
					// Antwort richtig weil nicht angeklickt
					else 
					{
						if($a['right'] == 0)
						{
							$atext = 'Falsch, Diese Antwort stimmt nicht.';
						}
						else
						{
							$atext = 'Auch diese Antwort wäre richtig gewesen.';
						}
						$bg = '#E74955';
					}
					
					if(!empty($atext))
					{
						$atext = '<strong>'.$atext.'</strong><br />';
					}
					
					$out .= '
					<li class="answer" style="color:#fff ;cursor: pointer; border-radius: 10px; display: block; list-style: outside none none; padding: 10px; font-size: 14px; background-color: '.$bg.';">
						'.$atext.'	
						<p>'.nl2br($a['text']).'</p>
						<p>
							<strong>Erklärung</strong><br />
							'.nl2br($a['explanation']).'
						</p>
					</li>';
					
				}
			}
		}
		
		$out = '
			<div id="quizwrapper">
				<div style="border-radius:10px;font-size:14px;color:#000;padding:10px;background:#FFFFFF;margin-bottom:15px;line-height:20px;">'.nl2br($quest['text']).'</div>
				<ul style="display:block;list-style:none;">'.$out.'</ul>
		</div>';
		
		$dia = new XhrDialog();
		$dia->addOpt('height', '($(window).height()-40)',false);
		$dia->addOpt('position', 'center');
		
		$dia->setTitle('Zwischenauswertung Frage '.(S::get('quiz-index')));
		$dia->addContent($out);
		
		$dia->addContent($this->view->quizComment());
		
		//$dia->addButton('nächste Frage','ajreq(\'next\',{app:\'quiz\'});');
		
		$dia->addButton('nächste Frage','ajreq(\'next\',{app:\'quiz\',comment:$(\'#quizusercomment\').val(),qid:'.(int)$question['id'].'});');
		
		$dia->addJsAfter('
			var width = 1000;
			if($(window).width() < 1000)
			{
				width = ($(window).width()-40);
			}
			$("#'.$dia->getId().'").dialog("option",{
				width:width,
				height:($(window).height()-40)
			});
			$(window).resize(function(){
				var width = 1000;
				if($(window).width() < 1000)
				{
					width = ($(window).width()-40);
				}
				$("#'.$dia->getId().'").dialog("option",{
					width:width,
					height:($(window).height()-40)
				});
			});		
				
			$("#'.$dia->getId().'").scrollTop($("#'.$dia->getId().'").height());
		');
		
		
		return $dia->xhrout();
	}
	
	private function resultNew($question,$diaId)
	{
		$uanswers = array();
		$joke = false;
	
		if(isset($question['answers']) && is_array($question['answers']))
		{
			foreach ($question['answers'] as $a)
			{
				$uanswers[$a] = $a;
			}
		}
		// get the question
		if($quest = $this->model->getQuestion($question['id']))
		{
			// get possible answers
			if($answers = $this->model->getAnswers($question['id']))
			{
				/*
					print_r($question);
				print_r($answers);
				die();
				*/
				$joke = false;
				if($question['fp'] == 0)
				{
					$joke = true;
				}
	
				$out = array();
				foreach ($answers as $a)
				{
					$bg = '';
					$atext = '';
						
					if($joke)
					{
						$bg = 'transparent';
						$atext = '';
					}
					// Antwort richtig angeklickt
					else if((isset($uanswers[$a['id']]) && $a['right'] == 1) || (!isset($uanswers[$a['id']]) && $a['right'] == 0))
					{
						if($a['right'] == 0)
						{
							$atext = 'Diese Antwort war natürlich falsch, das hast Du richtig erkannt';
						}
						else
						{
							$atext = 'Richtig! Diese Antwort stimmt.';
						}
						$bg = '#599022';
					}
					// Antwort richtig weil nicht angeklickt
					else
					{
						if($a['right'] == 0)
						{
							$atext = 'Falsch, Diese Antwort stimmt nicht.';
						}
						else
						{
							$atext = 'Auch diese Antwort wäre richtig gewesen.';
						}
						$bg = '#E74955';
					}
						
					$out[] = array(
						'id' => $a['id'],
						'exp' => nl2br($a['explanation']),
						'bg' => $bg,
						'atext' => $atext
					);
					/*
					$out .= '
					<li class="answer" style="color:#fff ;cursor: pointer; border-radius: 10px; display: block; list-style: outside none none; padding: 10px; font-size: 14px; background-color: '.$bg.';">
						'.$atext.'
						<p>'.nl2br($a['text']).'</p>
						<p>
							<strong>Erklärung</strong><br />
							'.nl2br($a['explanation']).'
						</p>
					</li>';
					*/
						
				}
			}
		}
	
		
		return array(
			'status' => 1,
			'script' => '
				$("#'.$diaId.' .ui-dialog-buttonset:first .ui-button").hide();
				$("#'.$diaId.' .ui-dialog-buttonset:first .ui-button:last").show();
				$("#quizcomment").show();
				$("#countdown").hide();
				
				var answers = '.json_encode($out).';
				$(".answer, .answer span").css({
					"cursor":"default"
				});
				$("#qanswers input").attr("disabled",true);
				$(".noanswer").hide();
				for(var i=0;i<answers.length;i++)
				{
					$("#qanswer-" + answers[i].id).css({
						"background-color":answers[i].bg,
						"color":"#fff"
					}).effect("highlight").attr("onmouseover","return false;").attr("onmouseout","return false;");
					$("#qanswer-" + answers[i].id).append(\'<div style="margin:15px 0 0 43px;">\'+answers[i].atext+\' <a style="color:#FFFFFF;font-weight:bold;" href="#" onclick="$(this).parent().next().toggle();return false;">Erklärung <i class="fa fa-arrow-circle-o-right"></i></a></div><div id="explanation-\'+answers[i].id+\'" style="font-weight:bold;margin:15px 0 0 43px;display:none;">\'+answers[i].exp+\'</div>\');
				}
				
			'
		);
		
		/*
		$out = '
			<div id="quizwrapper">
				<div style="border-radius:10px;font-size:14px;color:#000;padding:10px;background:#FFFFFF;margin-bottom:15px;line-height:20px;">'.nl2br($quest['text']).'</div>
				<ul style="display:block;list-style:none;">'.$out.'</ul>
		</div>';

		$dia = new XhrDialog();
		$dia->addOpt('height', '($(window).height()-40)',false);
		$dia->addOpt('position', 'center');
	
		$dia->setTitle('Zwischenauswertung Frage '.(S::get('quiz-index')));
		$dia->addContent($out);
	
		$dia->addContent($this->view->quizComment());
	
		//$dia->addButton('nächste Frage','ajreq(\'next\',{app:\'quiz\'});');
	
		$dia->addButton('nächste Frage','ajreq(\'next\',{app:\'quiz\',comment:$(\'#quizusercomment\').val(),qid:'.(int)$question['id'].'});');
	
		$dia->addJsAfter('
			var width = 1000;
			if($(window).width() < 1000)
			{
				width = ($(window).width()-40);
			}
			$("#'.$dia->getId().'").dialog("option",{
				width:width,
				height:($(window).height()-40)
			});
			$(window).resize(function(){
				var width = 1000;
				if($(window).width() < 1000)
				{
					width = ($(window).width()-40);
				}
				$("#'.$dia->getId().'").dialog("option",{
					width:width,
					height:($(window).height()-40)
				});
			});
	
			$("#'.$dia->getId().'").scrollTop($("#'.$dia->getId().'").height());
		');
	
	
		return $dia->xhrout();
		*/
	}
	
	public function pause()
	{
		$dia = new XhrDialog();
		$dia->setTitle('Pause');
		//$dia->removeTitlebar();
		$dia->addContent($this->view->pause());
		$dia->addJsBefore('
			
		');
		$dia->addJs('
			clearInterval(counter);	
		');
		
		$dia->addOpt('open','
			function(){
				setTimeout(function(){
					$close = $("#'.$dia->getId().'").prev().children(".ui-dialog-titlebar-close");
					//$close.unbind("click");
					$close.click(function(){
					
					ajreq(\'next\',{app:\'quiz\'});
				});
			},200);
		}',false);
		
		$dia->addButton('Später weitermachen','$(this).dialog("close");');
		$dia->addButton('weiter gehts!','ajreq(\'next\',{app:\'quiz\'});');
		
		/*
		 * $("#'.$dia->getId().' .ui-dialog-titlebar-close").click(function(ev){
				ev.preventDefault();
			});
		$dia->addJs('
			var isCloseClicker = false;
			$(".ui-dialog-titlebar-close").click(function(ev){
				ev.preventDefault();
				if(confirm(\'Möchtest Du aufhören? Du kannst auch zu jedem späteren Zeitpunkt weitermachen.\'))
				{
					$("#'.$dia->getId().'").dialog("close");
				}
			});
		');
		*/
		
		return $dia->xhrout();
	
	}
	
	private function validateAnswer($rightQuestions,$question)
	{
		
		$explains = array();
		
		$wrongAnswers = 0;
		$checkCount = 0;
		
		$everything_false = true;
		
		$useranswers = array();
		if(isset($question['answers']) && is_array($question['answers']))
		{
			$useranswers = $question['answers'];
		}
		if(isset($rightQuestions[$question['id']]['answers']))
		{
			foreach ($rightQuestions[$question['id']]['answers'] as $id => $a)
			{			
				switch ($a['right'])
				{
					// Antwort soll falsch sein
					case 0 : 
						$checkCount++;
						if(in_array($a['id'], $useranswers))
						{
							$wrongAnswers++;
							// Erklärungen anfügen
							$explains[$a['id']] = $rightQuestions[$question['id']]['answers'][$a['id']];
						}
						break;
					// Antwort ist richtig wenn nicht im array fehler
					case 1 : 
						$everything_false = false;
						$checkCount++;
						if(!in_array($a['id'], $useranswers))
						{
							$wrongAnswers++;
							// Erklärungen anfügen
							$explains[$a['id']] = $rightQuestions[$question['id']]['answers'][$a['id']];
						}
						break;
					default :
						
						// Bei Neutralen Fragen einfach erklärung anfügen
						$explains[$a['id']] = $rightQuestions[$question['id']]['answers'][$a['id']];
						break;
				}
			}
		}
		else
		{
			$wrongAnswers = count($rightQuestions[$question['id']]['answers']);
		}
		
		//print_r($rightQuestions[$question['id']]['answers']);
		//print_r($useranswers);
		
		
		
		// wie viel prozent sind falsch?
		$percent = $this->percentFrom($checkCount, $wrongAnswers);
		
		$fp = $this->percentTo($question['fp'], $percent);
		
		// wenn alles falsch angeklickt wurde das aber nciht stimmt gibts die volle fehlerpunkt zahl
		if(
			(!$everything_false && !isset($question['noco']))
			||
			(!$everything_false && (int)$question['noco'] > 0)
		)
		{
			$fp = $question['fp'];
			$percent = 100;
		}
		
		return array(
			'fp' => $fp,
			'explain' => $explains,
			'percent' => $percent
		);
	}
	
	private function getRandomQuestions($quiz_id, $count = 6)
	{
		$count_questions = $count;
		$random_questions = array();
		
		if($questions = $this->model->getQuestionMetas($quiz_id))
		{
			// Wie viele Fragen gibt es insgesamt?
			$summe = 0;
			foreach($questions['meta'] as $key => $m)
			{
				$summe += $m;
			}
			
			$out = array();
			// Prozentanzeil von jeder Fragenart
			foreach($questions['meta'] as $key => $m)
			{
				$percent = round($this->percentFrom($summe,$m));
		
				$count = round($this->percentTo($count_questions, $percent));
		
				if($rquest = $this->model->getRandomQuestions($count,$key,$quiz_id))
				{
					foreach ($rquest as $r)
					{
						$out[] = $r;
					}
				}
			}

			if(!empty($out))
			{
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
	
	private function percentFrom($total,$part)
	{
		if($total == 0)
		{
			return 100;
		}
		return ($part / ($total / 100));
	}
	
	public function updatequest()
	{
		if(isOrgaTeam())
		{
			
			/*
			 *   [id] => 10
			     [text] => test
			     [fp] => 3
			 */
			if(isset($_GET['text']) && isset($_GET['fp']) && isset($_GET['id']))
			{
				$fp = (int)$_GET['fp'];
				$text = strip_tags($_GET['text']);
				$duration = (int)$_GET['duration'];
				$wikilink = strip_tags($_GET['wikilink']);
				
				if(!empty($text))
				{
					$this->model->updateQuestion($_GET['id'],$_GET['qid'],$text,$fp,$duration,$wikilink);
					info('Frage wurde geändert');
					return array(
						'status' => 1,
						'script' => 'reload();'
					);	
					
				}
				else
				{
					return array(
						'status' => 1,
						'script' => 'pulseError("Du solltest einen Text angeben ;)");'
					);
				}
			}
		}
	}
}