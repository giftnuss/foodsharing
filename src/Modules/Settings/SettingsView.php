<?php

namespace Foodsharing\Modules\Settings;

use DateTime;
use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\PageHelper;
use Foodsharing\Helpers\RouteHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;
use Foodsharing\Modules\Core\DBConstants\FoodSharePoint\FollowerType;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\DBConstants\Quiz\AnswerRating;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsView extends View
{
	private $regionGateway;

	public function __construct(
		\Twig\Environment $twig,
		Utils $viewUtils,
		Session $session,
		SanitizerService $sanitizerService,
		PageHelper $pageHelper,
		TimeHelper $timeHelper,
		ImageService $imageService,
		RouteHelper $routeHelper,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		TranslationHelper $translationHelper,
		RegionGateway $regionGateway,
		TranslatorInterface $translator
	) {
		$this->regionGateway = $regionGateway;

		parent::__construct(
			$twig,
			$viewUtils,
			$session,
			$sanitizerService,
			$pageHelper,
			$timeHelper,
			$imageService,
			$routeHelper,
			$identificationHelper,
			$dataHelper,
			$translationHelper,
			$translator
		);
	}

	public function sleepMode($sleep)
	{
		$this->dataHelper->setEditData($sleep);

		if ($sleep['sleep_status'] != SleepStatus::TEMP) {
			$this->pageHelper->addJs('$("#daterange-wrapper").hide();');
		}

		if ($sleep['sleep_status'] == SleepStatus::NONE) {
			$this->pageHelper->addJs('$("#sleep_msg-wrapper").hide();');
		}

		if ($sleep['sleep_status'] == SleepStatus::TEMP) {
			$date = DateTime::createFromFormat('Y-m-d', $sleep['sleep_from']);
			if ($date === false) {
				$date = new DateTime();
			}
			$from = $date->format('d.m.Y');

			$date = DateTime::createFromFormat('Y-m-d', $sleep['sleep_until']);
			if ($date === false) {
				$date = new DateTime();
			}
			$to = $date->format('d.m.Y');

			$this->pageHelper->addJs("
				$('#daterange_from').val('$from');
				$('#daterange_to').val('$to');
			");
		}

		$this->pageHelper->addJs('
			$("#sleep_status").on("change", function(){
				var $this = $(this);
				if($this.val() == 1)
				{
					$("#daterange-wrapper").show();
				}
				else
				{
					$("#daterange-wrapper").hide();
				}

				if($this.val() > 0)
				{
					$("#sleep_msg-wrapper").show();
				}
				else
				{
					$("#sleep_msg-wrapper").hide();
				}
			});
			$("#sleep_msg").css("height","50px").autosize();

			$("#schlafmtzenfunktion-form").on("submit", function(ev){
				ev.preventDefault();
				if ($("#sleep_status").val() == 1 ){
					if ($("#daterange_from").val() == "" || $("#daterange_to").val() == "" ){
						pulseError("' . $this->translationHelper->s('sleep_mode_date_missing') . '");
						return;
					}
				}
				ajax.req("settings","sleepmode",{
					method:"post",
					data: {
						status: $("#sleep_status").val(),
						from: $("#daterange_from").val(),
						until: $("#daterange_to").val(),
						msg: $("#sleep_msg").val()
					},
					success: function(){
						pulseSuccess("' . $this->translationHelper->s('sleep_mode_saved') . '");
					}
				});
			});
			$("#formwrapper").show();
		');

		$out = $this->v_utils->v_quickform($this->translationHelper->s('sleepmode'), [
			$this->v_utils->v_info($this->translationHelper->s('sleepmode_info')),
			$this->v_utils->v_info($this->translationHelper->s('sleepmode_show')),
			$this->v_utils->v_form_select('sleep_status', [
				'values' => [
					['id' => SleepStatus::NONE, 'name' => $this->translationHelper->s('no_sleepmode')],
					['id' => SleepStatus::TEMP, 'name' => $this->translationHelper->s('temp_sleepmode')],
					['id' => SleepStatus::FULL, 'name' => $this->translationHelper->s('full_sleepmode')]
				]
			]),
			$this->v_utils->v_form_daterange(),
			$this->v_utils->v_form_textarea('sleep_msg', [
				'maxlength' => 150
			])
		], ['submit' => $this->translationHelper->s('save')]);

		return '<div id="formwrapper" style="display:none;">' . $out . '</div>';
	}

	public function settingsInfo($foodSharePoints, $threads)
	{
		global $g_data;
		$out = '';

		if ($foodSharePoints) {
			foreach ($foodSharePoints as $fsp) {
				$this->pageHelper->addJs('
					$("input[disabled=\'disabled\']").parent().on("click", function(){
						pulseInfo("Du bist verantwortlich für diesen Fair-Teiler und somit verpflichtet, die Updates entgegenzunehmen!");
					});
				');

				$g_data['fairteiler_' . $fsp['id']] = $fsp['infotype'];
				$out .= $this->v_utils->v_form_radio('fairteiler_' . $fsp['id'], [
					'label' => $this->translationHelper->sv('follow_food_share_point', $fsp['name']),
					'desc' => $this->translationHelper->sv('follow_food_share_point_desc', $fsp['name']),
					'values' => [
						['id' => InfoType::EMAIL, 'name' => $this->translationHelper->s('follow_food_share_point_mail')],
						['id' => InfoType::BELL, 'name' => $this->translationHelper->s('follow_food_share_point_bell')],
						['id' => InfoType::NONE, 'name' => $this->translationHelper->s('follow_food_share_point_none')]
					],
					'disabled' => $fsp['type'] == FollowerType::FOOD_SHARE_POINT_MANAGER
				]);
			}
		}

		if ($threads) {
			foreach ($threads as $thread) {
				$g_data['thread_' . $thread['id']] = $thread['infotype'];
				$out .= $this->v_utils->v_form_radio('thread_' . $thread['id'], [
					'label' => $this->translationHelper->sv('follow_thread', $thread['name']),
					'desc' => $this->translationHelper->sv('follow_thread_desc', $thread['name']),
					'values' => [
						['id' => InfoType::EMAIL, 'name' => $this->translationHelper->s('follow_thread_mail')],
						['id' => InfoType::NONE, 'name' => $this->translationHelper->s('follow_thread_none')]
					]
				]);
			}
		}

		return $this->v_utils->v_field($this->v_utils->v_form('settingsinfo', [
			$this->v_utils->v_input_wrapper(
				$this->translationHelper->s('push_notifications'),
				'<div id="push-notification-label"><!-- Content to be set via JavaScript --></div>
						<a href="#" class="button" id="push-notification-button"><!-- Content to be set via JavaScript --></a>'
			),
			$this->v_utils->v_form_radio('newsletter', [
				'desc' => $this->translationHelper->s('newsletter_desc'),
				'values' => [
					['id' => 0, 'name' => $this->translationHelper->s('no')],
					['id' => 1, 'name' => $this->translationHelper->s('yes')]
				]
			]),
			$this->v_utils->v_form_radio('infomail_message', [
				'desc' => $this->translationHelper->s('infomail_message_desc'),
				'values' => [
					['id' => 0, 'name' => $this->translationHelper->s('no')],
					['id' => 1, 'name' => $this->translationHelper->s('yes')]
				]
			]),
			$out
		], ['submit' => $this->translationHelper->s('save')]), $this->translationHelper->s('settings_info'), ['class' => 'ui-padding']);
	}

	public function quizSession($session, $try_count, ContentGateway $contentGateway)
	{
		if ($session['fp'] <= $session['maxfp']) {
			$infotext = $this->v_utils->v_success('Herzlichen Glückwunsch! mit ' . $session['fp'] . ' von maximal ' . $session['maxfp'] . ' Fehlerpunkten bestanden!');
		} else {
			$infotext = $this->v_utils->v_error('mit ' . $session['fp'] . ' von maximal ' . $session['maxfp'] . ' Fehlerpunkten leider nicht bestanden.</p>');
		}
		$this->pageHelper->addContent('<div class="quizsession">' . $this->topbar($session['name'] . '-Quiz', '', '<img src="/img/quiz.png" />') . '</div>');
		$out = '';

		$out .= $infotext;

		if ($session['fp'] <= $session['maxfp']) {
			$btn = '';
			switch ($session['quiz_id']) {
				case 1:
					$btn = '<a href="/?page=settings&sub=upgrade/up_fs" class="button">Jetzt die Foodsaver-Anmeldung abschließen!</a>';
					break;

				case 2:
					$btn = '<a href="/?page=settings&sub=upgrade/up_bip" class="button">Jetzt die Betriebsverantwortlichenanmeldung abschließen!</a>';
					break;

				case 3:
					$btn = '<a href="/?page=settings&sub=upgrade/up_bot" class="button">Jetzt die Botschafteranmeldung abschließen!</a>';
					break;

				default:
					break;
			}
			$out .= $this->v_utils->v_field('<p>Herzlichen Glückwunsch, Du hast es geschafft!</p><p>Die Auswertung findest Du unten.</p><p style="padding:15px;text-align:center;">' . $btn . '</p>', 'Geschafft!', ['class' => 'ui-padding']);
		} else {
			/*
			 * get the specific text from content table
			 */
			$content_id = false;

			if ($try_count > 4) {
				$content_id = 13;
			} elseif ($try_count > 2) {
				$content_id = 21;
			} elseif ($try_count == 2) {
				$content_id = 20;
			} elseif ($try_count == 1) {
				$content_id = 19;
			}

			if ($content_id) {
				$cnt = $contentGateway->get($content_id);
				$out .= $this->v_utils->v_field($cnt['body'], $cnt['title'], ['class' => 'ui-padding']);
			}
		}

		$i = 0;
		foreach ($session['quiz_result'] as $r) {
			/*
			 * If the question has no error points its a joke question lets store in clear in a variable
			 */
			$was_a_joke = false;
			if ($r['fp'] == 0) {
				$was_a_joke = true;
			}

			/*
			 * If the question has more than 10 error point its a k.o. question
			*/
			$was_a_ko_question = false;
			if ($r['fp'] > 10) {
				$was_a_ko_question = true;
			}

			$ftext = 'hast Du komplett richtig beantwortet. Prima!';
			++$i;
			$cnt = '<div class="question">' . $r['text'] . '</div>';

			$cnt .= $this->v_utils->v_input_wrapper('Passender Wiki-Artikel zu diesem Thema', '<a target="_blank" href="' . $r['wikilink'] . '">' . $r['wikilink'] . '</a>');

			$right_answers = '';
			$wrong_answers = '';
			$neutral_answers = '';
			$ai = 0;

			$sort_right = 'right';

			$noclicked = true;
			foreach ($r['answers'] as $a) {
				++$ai;
				$right = 'red';

				if ($a['user_say']) {
					$noclicked = false;
				}

				if (!$r['noco'] && $r['percent'] == 100) {
					$atext = '';
					$right = 'red';
				} elseif ($a['user_say'] == true && $a['right'] == AnswerRating::CORRECT && !$r['noco']) {
					$right = 'green';
					if ($a['right']) {
						$atext = ' ist richtig!';
						$sort_right = 'right';
					} else {
						$atext = ' ist falsch. Das hast Du richtig erkannt!';
						$sort_right = 'right';
					}
				} elseif ($a['right'] == AnswerRating::NEUTRAL) {
					$atext = ' ist neutral und daher ohne Wertung.';
					$right = 'neutral';
					$sort_right = 'neutral';
				} else {
					if ($a['right']) {
						$atext = ' wäre auch richtig gewesen.';
						$sort_right = 'false';
					} else {
						$atext = ' stimmt so nicht!';
						$sort_right = 'false';
					}
				}

				if ($sort_right == 'right') {
					$right_answers .= '
					<div class="answer q-' . $right . '">
						' . $this->v_utils->v_input_wrapper('Antwort ' . $ai . $atext, $a['text']) . '
						' . $this->v_utils->v_input_wrapper('Erklärung', $a['explanation']) . '

					</div>';
				} elseif ($sort_right == 'neutral') {
					$neutral_answers .= '
					<div class="answer q-' . $right . '">
						' . $this->v_utils->v_input_wrapper('Antwort ' . $ai . $atext, $a['text']) . '
						' . $this->v_utils->v_input_wrapper('Erklärung', $a['explanation']) . '

					</div>';
				} elseif ($sort_right == 'false') {
					$wrong_answers .= '
					<div class="answer q-' . $right . '">
						' . $this->v_utils->v_input_wrapper('Antwort ' . $ai . $atext, $a['text']) . '
						' . $this->v_utils->v_input_wrapper('Erklärung', $a['explanation']) . '

					</div>';
				}
			}

			$no_wrong_right_sort = false;

			if ($r['userfp'] > 0) {
				$cnt .= $this->v_utils->v_input_wrapper('gesammelte Fehlerpunkte', $r['userfp']);
				if ($r['percent'] == 100) {
					$ftext = ' wurde leider falsch beantwortet.';
					if (!$r['noco'] && $noclicked) {
						$no_wrong_right_sort = true;
						$ftext = ' wurde leider als falsch gewertet. Da Du nichts ausgewählt hast oder die Zeit abgelaufen ist.';
					}
				} else {
					$ftext = ' hast Du leider nur zu ' . (100 - $r['percent']) . ' % richtig beantwortet.';
				}
			}

			if ($no_wrong_right_sort) {
				$cnt .= $this->v_utils->v_input_wrapper('Antworten', $wrong_answers . $right_answers, false, ['collapse' => true]);
			} else {
				if (!empty($right_answers)) {
					$cnt .= $this->v_utils->v_input_wrapper('Richtige Antworten', $right_answers, false, ['collapse' => true]);
				}
				if (!empty($wrong_answers)) {
					$cnt .= $this->v_utils->v_input_wrapper('Falsche Antworten', $wrong_answers, false, ['collapse' => true]);
				}
				if (!empty($neutral_answers)) {
					$cnt .= $this->v_utils->v_input_wrapper('Neutrale Antworten', $neutral_answers, false, ['collapse' => true]);
				}
			}

			$cnt .= '<div id="qcomment-' . (int)$r['id'] . '">' . $this->v_utils->v_input_wrapper('Kommentar zu dieser Frage schreiben', '<textarea style="height:50px;" id="comment-' . $r['id'] . '" name="desc" class="input textarea value"></textarea><br /><a class="button" href="#" onclick="ajreq(\'addcomment\',{app:\'quiz\',comment:$(\'#comment-' . (int)$r['id'] . '\').val(),id:' . (int)$r['id'] . '});return false;">Absenden</a>', false, ['collapse' => true]) . '</div>';

			/*
			 * If the question was a joke question lets display it to the user!
			 */
			if ($was_a_joke) {
				$ftext = 'war nur eine Scherzfrage und wird natürlich nicht bewertet <i class="far fa-smile"></i>';
			}

			/*
			 * If the question is k.o. question and the user has error display a message to the user
			 */
			if ($was_a_ko_question && $r['userfp'] > 0) {
				$ftext = 'Diese Frage war leider besonders wichtig und Du hast sie nicht korrekt beantwortet';
				$cnt = $this->v_utils->v_info('Fragen wie diese sind besonders hoch gewichtet und führen leider zum Nichtbbestehen, wenn Du sie falsch beantwortest.');
			}

			$out .= '
					<div class="quizsession">' .
				$this->v_utils->v_field($cnt, 'Frage ' . $i . ' ' . $ftext, ['class' => 'ui-padding']) . '
					</div>';
		}

		return $out;
	}

	public function changeMail()
	{
		return $this->v_utils->v_form_text('newmail');
	}

	public function changemail3($email)
	{
		return
			$this->v_utils->v_info('E-Mail-Adresse wirklich zu <strong>' . $email . '</strong> ändern?') .
			$this->v_utils->v_form_passwd('passcheck');
	}

	public function settingsCalendar($token)
	{
		$url = BASE_URL . '/api.php?f=cal&fs=' . $this->session->id() . '&key=' . $token . '&opts=s';

		return $this->v_utils->v_field('
<p>Du kannst Deinen Abholkalender auch mit einem Kalenderprogramm Deiner Wahl ansehen. Abonniere Dir dazu folgenden Kalender!</p>
<p>Hinweis: Halte den Link unbedingt geheim! Er enthält einen Schlüssel, um ohne Passwort auf Deinen Account zuzugreifen.</p>
<p>Hinweis: Dein Kalenderprogramm muss den Kalender regelmäßig neu synchronisieren. Nur dann tauchen neue Abholtermine auf!</p>

				<table style="border-spacing: 10px;border-collapse: separate;">
				<tr>
					<td style="width:75px;">ICS/ICAL/WebCal:</td>
					<td><strong><a href="' . $url . '">' . $url . '</strong></td>
				</tr>
				</table>

				', 'Dein Abholkalender', ['class' => 'ui-padding']);
	}

	public function delete_account(int $fsId)
	{
		$content =
			'<button type="button" id="delete-account" class="button danger" onclick="confirmDeleteAccount(' . $fsId . ')">' . $this->translationHelper->s('delete_now') . '</button>'
		. $this->v_utils->v_info('Du bist dabei Deinen Account zu löschen. Bist Du Dir ganz sicher?', $this->translationHelper->s('reference'));

		return $this->v_utils->v_field($content, $this->translationHelper->s('delete_account'), ['class' => 'ui-padding']);
	}

	public function foodsaver_form()
	{
		global $g_data;

		$bezirkchoose = '';
		$position = '';
		$communications = $this->v_utils->v_form_text('homepage');

		if ($this->session->may('orga')) {
			$bezirk = ['id' => 0, 'name' => false];
			if ($b = $this->regionGateway->getRegion($this->session->getCurrentRegionId())) {
				$bezirk['id'] = $b['id'];
				$bezirk['name'] = $b['name'];
			}

			$bezirkchoose = $this->v_utils->v_bezirkChooser('bezirk_id', $bezirk);
			$position = $this->v_utils->v_form_text('position');
		}

		$g_data['ort'] = $g_data['stadt'];

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			$latLonOptions[$i] = $g_data[$i];
		}
		$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];

		return $this->v_utils->v_quickform($this->translationHelper->s('settings'), [
			$bezirkchoose,
			$this->latLonPicker('LatLng', $latLonOptions),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('handy'),
			$this->v_utils->v_form_date('geb_datum', ['required' => true, 'yearRangeFrom' => date('Y') - 120, 'yearRangeTo' => date('Y') - 8]),
			$communications,
			$position,
			$this->v_utils->v_form_textarea('about_me_intern', ['desc' => $this->translationHelper->s('profile_description_text_display_info')]),
			$this->v_utils->v_form_textarea('about_me_public', ['desc' => $this->translationHelper->s('profile_description_text_info')]),
		], ['submit' => $this->translationHelper->s('save')]);
	}

	public function quizFailed($failed)
	{
		$out = $this->v_utils->v_field($failed['body'], $failed['title'], ['class' => 'ui-padding']);

		return $out;
	}

	public function pause($days_to_wait, $desc)
	{
		$out = $this->v_utils->v_input_wrapper('Du hast das Quiz 3x nicht bestanden', 'In ' . $days_to_wait . ' Tagen kannst Du es noch einmal probieren');

		if ($desc) {
			$out .= $this->v_utils->v_input_wrapper($desc['title'], $desc['body']);
		}

		$out = $this->v_utils->v_field($out, 'Lernpause', ['class' => 'ui-padding']);

		return $out;
	}

	public function quizContinue($quiz, $desc)
	{
		$out = '';
		if ($desc) {
			$out .= $this->v_utils->v_input_wrapper($desc['title'], $desc['body']);
		}

		$out .= $this->v_utils->v_input_wrapper('Du hast Das Quiz noch nicht beendet', 'Aber kein Problem. Deine Sitzung wurde gespeichert. Du kannst jederzeit die Beantwortung fortführen.');

		$out .= $quiz['desc'];

		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz jetzt weiter beantworten!</a></p>';

		$out = $this->v_utils->v_field($out, $quiz['name'] . '-Quiz fortführen', ['class' => 'ui-padding']);

		return $out;
	}

	public function quizRetry($quiz, $desc, $failed_count, $max_failed_count)
	{
		$out = $this->v_utils->v_input_wrapper(($failed_count + 1) . '. Versuch', '<p>Du hast das Quiz bereits ' . $failed_count . 'x nicht geschafft, hast aber noch ' . ($max_failed_count - $failed_count) . ' Versuche</p><p>Viel Glück!</p>');

		if ($desc) {
			$out .= $this->v_utils->v_input_wrapper($desc['title'], $desc['body']);
		}

		$out .= $quiz['desc'];

		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz mit Zeitlimit und 10 Fragen starten</a></p>';

		if ($quiz['id'] == 1) {
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',easymode:1,qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz ohne Zeitlimit und 20 Fragen starten</a></p>';
		}

		$out = $this->v_utils->v_field($out, $quiz['name'] . ' - Jetzt gilt es noch das Quiz zu bestehen!', ['class' => 'ui-padding']);

		return $out;
	}

	public function confirmBot($cnt)
	{
		$out = $this->v_utils->v_field($cnt['body'], $cnt['title'], ['class' => 'ui-padding']);

		return $out;
	}

	public function confirmBip($cnt, $rv)
	{
		$out = '
			<form action="/?page=settings&amp;sub=upgrade/up_bip" enctype="multipart/form-data" class="validate" id="confirmfs-form" method="post">
				<input type="hidden" value="confirmfs" name="form_submit">';

		if ($cnt) {
			$out .= $this->v_utils->v_field($cnt['body'], $cnt['title'], ['class' => 'ui-padding']);
		}
		if ($rv) {
			$rv['body'] .= '
			<label><input id="rv-accept" class="input" type="checkbox" name="accepted" value="1">&nbsp;' . $this->translationHelper->s('rv_accept') . '</label>
			<div class="input-wrapper">
				<p><input type="submit" value="Bestätigen" class="button"></p>
			</div>';

			$out .= $this->v_utils->v_field($rv['body'], $rv['title'], ['class' => 'ui-padding']);
		}

		$out .= '
			</form>';

		return $out;
	}

	public function confirmFs($cnt, $rv)
	{
		$out = '
			<form action="/?page=settings&amp;sub=upgrade/up_fs" enctype="multipart/form-data" class="validate" id="confirmfs-form" method="post">
				<input type="hidden" value="confirmfs" name="form_submit">';

		if ($cnt) {
			$out .= $this->v_utils->v_field($cnt['body'], $cnt['title'], ['class' => 'ui-padding']);
		}
		if ($rv) {
			$rv['body'] .= '
			<label><input id="rv-accept" class="input" type="checkbox" name="accepted" value="1">&nbsp;' . $this->translationHelper->s('rv_accept') . '</label>
			<div class="input-wrapper">
				<p><input type="submit" value="Bestätigen" class="button"></p>
			</div>';

			$out .= $this->v_utils->v_field($rv['body'], $rv['title'], ['class' => 'ui-padding']);
		}

		$out .= '
			</form>';

		return $out;
	}

	public function quizIndex($quiz, $desc)
	{
		$out = '';
		if ($desc) {
			$out .= $this->v_utils->v_input_wrapper($desc['title'], $desc['body']);
		}

		$out .= nl2br($quiz['desc']);

		if ($quiz['id'] == 1) {
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz mit Zeitlimit und 10 Fragen starten</a></p>';
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',easymode:1,qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz ohne Zeitlimit und 20 Fragen starten</a></p>';
		} else {
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz jetzt starten</a></p>';
		}

		$out = $this->v_utils->v_field($out, $quiz['name'] . ' - Jetzt gilt es noch das Quiz zu bestehen!', ['class' => 'ui-padding']);

		return $out;
	}

	public function picture_box($photo): string
	{
		$p_cnt = $this->v_utils->v_info($this->translationHelper->s('photo_should_be_usable'));

		if (!file_exists('images/thumb_crop_' . $photo)) {
			$p_cnt .= $this->v_utils->v_photo_edit('img/portrait.png');
		} else {
			$p_cnt .= $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo);
		}

		return $this->v_utils->v_field($p_cnt, 'Dein Foto');
	}
}
