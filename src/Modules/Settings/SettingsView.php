<?php

namespace Foodsharing\Modules\Settings;

use DateTime;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;
use Foodsharing\Modules\Core\DBConstants\FoodSharePoint\FollowerType;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\DBConstants\Quiz\AnswerRating;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsView extends View
{
	private RegionGateway $regionGateway;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		RegionGateway $regionGateway,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		TranslatorInterface $translator
	) {
		$this->regionGateway = $regionGateway;

		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);
	}

	public function sleepMode($sleep)
	{
		$this->dataHelper->setEditData($sleep);

		if ($sleep['sleep_status'] != SleepStatus::TEMP) {
			$this->pageHelper->addJs('$("#sleeprange-wrapper").hide();');
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
				$('#sleeprange_from').val('$from');
				$('#sleeprange_to').val('$to');
			");
		}

		$this->pageHelper->addJs('
			$("#sleep_status").on("change", function () {
				var $this = $(this);
				if ($this.val() == 1) {
					$("#sleeprange-wrapper").show();
				} else {
					$("#sleeprange-wrapper").hide();
				}

				if ($this.val() > 0) {
					$("#sleep_msg-wrapper").show();
				} else {
					$("#sleep_msg-wrapper").hide();
				}
			});
			$("#sleep_msg").css("height", "80px");

			$("#schlafmtzenfunktion-form").on("submit", function (ev) {
				ev.preventDefault();
				if ($("#sleep_status").val() == 1) {
					if ($("#sleeprange_from").val() == "" || $("#sleeprange_to").val() == "") {
						pulseError("' . $this->translator->trans('settings.sleep.missing-date') . '");
						return;
					}
				}
				ajax.req("settings", "sleepmode", {
					method: "post",
					data: {
						status: $("#sleep_status").val(),
						from: $("#sleeprange_from").val(),
						until: $("#sleeprange_to").val(),
						msg: $("#sleep_msg").val()
					},
					success: function () {
						pulseSuccess("' . $this->translator->trans('settings.sleep.saved') . '");
					}
				});
			});
			$("#formwrapper").show();
		');

		$out = $this->v_utils->v_quickform($this->translator->trans('settings.sleep.header'), [
			$this->v_utils->v_info($this->translator->trans('settings.sleep.info')),
			$this->v_utils->v_form_select('sleep_status', [
				'values' => [
					['id' => SleepStatus::NONE, 'name' => $this->translator->trans('settings.sleep.none')],
					['id' => SleepStatus::TEMP, 'name' => $this->translator->trans('settings.sleep.temp')],
					['id' => SleepStatus::FULL, 'name' => $this->translator->trans('settings.sleep.full')]
				]
			]),
			$this->v_utils->v_form_daterange('sleeprange', $this->translator->trans('settings.sleep.range')),
			$this->v_utils->v_form_textarea('sleep_msg', [
				'maxlength' => 150
			]),
			$this->v_utils->v_info($this->translator->trans('settings.sleep.show'))
		], ['submit' => $this->translator->trans('button.save')]);

		return '<div id="formwrapper" style="display: none;">' . $out . '</div>';
	}

	public function settingsInfo($foodSharePoints, $threads)
	{
		global $g_data;
		$out = '';

		if ($foodSharePoints) {
			foreach ($foodSharePoints as $fsp) {
				$this->pageHelper->addJs('
					$("input[disabled=\'disabled\']").parent().on("click", function () {
						pulseInfo("' . $this->translator->trans('fsp.info.manager') . '");
					});
				');

				$g_data['fairteiler_' . $fsp['id']] = $fsp['infotype'];
				$out .= $this->v_utils->v_form_radio('fairteiler_' . $fsp['id'], [
					'label' => $this->translator->trans('fsp.info.from', ['{name}' => $fsp['name']]),
					'desc' => $this->translator->trans('fsp.info.descSettings', ['{name}' => $fsp['name']]),
					'values' => [
						['id' => InfoType::BELL, 'name' => $this->translator->trans('fsp.info.bell')],
						['id' => InfoType::EMAIL, 'name' => $this->translator->trans('fsp.info.mail')],
						['id' => InfoType::NONE, 'name' => $this->translator->trans('fsp.info.none')],
					],
					'disabled' => $fsp['type'] == FollowerType::FOOD_SHARE_POINT_MANAGER
				]);
			}
		}

		if ($threads) {
			foreach ($threads as $thread) {
				$g_data['thread_' . $thread['id']] = $thread['infotype'];
				$out .= $this->v_utils->v_form_radio('thread_' . $thread['id'], [
					'label' => $this->translator->trans('settings.follow.thread', ['{thread}' => $thread['name']]),
					'desc' => $thread['name'],
					'values' => [
						['id' => InfoType::EMAIL, 'name' => $this->translator->trans('settings.follow.mail')],
						['id' => InfoType::NONE, 'name' => $this->translator->trans('settings.follow.none')]
					]
				]);
			}
		}

		return $this->v_utils->v_field($this->v_utils->v_form('settingsinfo', [
			$this->v_utils->v_input_wrapper(
				$this->translator->trans('settings.push.title'),
					'<div id="push-notification-label"><!-- Content to be set via JavaScript --></div>
					<a href="#" class="button" id="push-notification-button"><!-- Content to be set via JavaScript --></a>'
			),
			$this->v_utils->v_form_radio('newsletter', [
				'desc' => $this->translator->trans('settings.newsletter'),
				'values' => [
					['id' => 0, 'name' => $this->translator->trans('no')],
					['id' => 1, 'name' => $this->translator->trans('yes')]
				]
			]),
			$this->v_utils->v_form_radio('infomail_message', [
				'desc' => $this->translator->trans('settings.chatmail'),
				'values' => [
					['id' => 0, 'name' => $this->translator->trans('no')],
					['id' => 1, 'name' => $this->translator->trans('yes')]
				]
			]),
			$out
		], ['submit' => $this->translator->trans('button.save')]), $this->translator->trans('settings.notifications'), ['class' => 'ui-padding']);
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
					$btn = '<a href="/?page=settings&sub=up_fs" class="button">Jetzt die Foodsaver-Anmeldung abschließen!</a>';
					break;

				case 2:
					$btn = '<a href="/?page=settings&sub=up_bip" class="button">Jetzt die Betriebsverantwortlichenanmeldung abschließen!</a>';
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
		return $this->vueComponent('calendar', 'Calendar', [
			'url' => WEBCAL_URL . '/api.php?f=cal&fs=' . $this->session->id() . '&key=' . $token
		]);
	}

	public function delete_account(int $fsId)
	{
		$content = '<button type="button" id="delete-account" class="btn btn-sm btn-danger"'
			. ' onclick="confirmDeleteUser(' . $fsId . ',\''
			. $this->translator->trans('foodsaver.your_account') . '\')">'
			. $this->translator->trans('foodsaver.delete_account_now')
			. '</button>'
			. $this->v_utils->v_info(
				$this->translator->trans('foodsaver.delete_own_account'),
				$this->translator->trans('notice')
			);

		return $this->v_utils->v_field($content, $this->translator->trans('foodsaver.delete_account'), ['class' => 'ui-padding bootstrap']);
	}

	public function foodsaver_form()
	{
		global $g_data;

		$regionPicker = '';
		$position = '';
		$communications = $this->v_utils->v_form_text('homepage');

		if ($this->session->may('orga')) {
			$bezirk = ['id' => 0, 'name' => false];
			if ($b = $this->regionGateway->getRegion($this->session->getCurrentRegionId())) {
				$bezirk['id'] = $b['id'];
				$bezirk['name'] = $b['name'];
			}

			$regionPicker = $this->v_utils->v_regionPicker($bezirk, $this->translator->trans('terminology.homeRegion'));
			$position = $this->v_utils->v_form_text('position');
		}

		$g_data['ort'] = $g_data['stadt'];

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			$latLonOptions[$i] = $g_data[$i];
		}
		$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];

		return $this->v_utils->v_quickform($this->translator->trans('settings.header'), [
			$regionPicker,
			$this->latLonPicker('LatLng', $latLonOptions, '_profile'),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('handy'),
			$this->v_utils->v_form_date('geb_datum', ['required' => true, 'yearRangeFrom' => (int)date('Y') - 120, 'yearRangeTo' => (int)date('Y') - 8]),
			$communications,
			$position,
			$this->v_utils->v_form_textarea('about_me_intern', [
				'desc' => $this->translator->trans('foodsaver.about_me_intern'),
			]),
			$this->v_utils->v_form_textarea('about_me_public', [
				'desc' => $this->translator->trans('foodsaver.about_me_public'),
			]),
		], ['submit' => $this->translator->trans('button.save')]);
	}

	public function quizFailed($failed)
	{
		$out = $this->v_utils->v_field($failed['body'], $failed['title'], ['class' => 'ui-padding']);

		return $out;
	}

	public function pause($days_to_wait)
	{
		$out = $this->v_utils->v_input_wrapper('Du hast das Quiz 3x nicht bestanden', 'In ' . $days_to_wait . ' Tagen kannst Du es noch einmal probieren');

		$out = $this->v_utils->v_field($out, 'Lernpause', ['class' => 'ui-padding']);

		return $out;
	}

	public function quizContinue($quiz)
	{
		$out = '';

		$out .= $this->v_utils->v_input_wrapper('Du hast Das Quiz noch nicht beendet', 'Aber kein Problem. Deine Sitzung wurde gespeichert. Du kannst jederzeit die Beantwortung fortführen.');

		$out .= $quiz['desc'];

		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz jetzt weiter beantworten!</a></p>';

		$out = $this->v_utils->v_field($out, $quiz['name'] . '-Quiz fortführen', ['class' => 'ui-padding']);

		return $out;
	}

	public function quizRetry($quiz, $failed_count, $max_failed_count)
	{
		$out = $this->v_utils->v_input_wrapper(($failed_count + 1) . '. Versuch', '<p>Du hast das Quiz bereits ' . $failed_count . 'x nicht geschafft, hast aber noch ' . ($max_failed_count - $failed_count) . ' Versuche</p><p>Viel Glück!</p>');

		$out .= $quiz['desc'];

		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz mit Zeitlimit und 10 Fragen starten</a></p>';

		if ($quiz['id'] == 1) {
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',easymode:1,qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">Quiz ohne Zeitlimit und 20 Fragen starten</a></p>';
		}

		$out = $this->v_utils->v_field($out, $quiz['name'] . ' - Jetzt gilt es noch das Quiz zu bestehen!', ['class' => 'ui-padding']);

		return $out;
	}

	public function confirmBip($cnt, $rv)
	{
		$out = '
			<form action="/?page=settings&amp;sub=up_bip" enctype="multipart/form-data" class="validate" id="confirmfs-form" method="post">
				<input type="hidden" value="confirmfs" name="form_submit">';

		if ($cnt) {
			$out .= $this->v_utils->v_field($cnt['body'], $cnt['title'], ['class' => 'ui-padding']);
		}
		if ($rv) {
			$rv['body'] .= '
			<label><input id="rv-accept" class="input" type="checkbox" name="accepted" value="1">&nbsp;' . $this->translator->trans('foodsaver.upgrade.rv') . '</label>
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
			<form action="/?page=settings&amp;sub=up_fs" enctype="multipart/form-data" class="validate" id="confirmfs-form" method="post">
				<input type="hidden" value="confirmfs" name="form_submit">';

		if ($cnt) {
			$out .= $this->v_utils->v_field($cnt['body'], $cnt['title'], ['class' => 'ui-padding']);
		}
		if ($rv) {
			$rv['body'] .= '
			<label><input id="rv-accept" class="input" type="checkbox" name="accepted" value="1">&nbsp;' . $this->translator->trans('foodsaver.upgrade.rv') . '</label>
			<div class="input-wrapper">
				<p><input type="submit" value="Bestätigen" class="button"></p>
			</div>';

			$out .= $this->v_utils->v_field($rv['body'], $rv['title'], ['class' => 'ui-padding']);
		}

		$out .= '
			</form>';

		return $out;
	}

	public function quizIndex($quiz)
	{
		$out = '';

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
		$p_cnt = $this->v_utils->v_info($this->translator->trans('settings.photo.info', [
			'{link_photo}' => 'https://wiki.foodsharing.de/Leitfaden_f%C3%BCr_ein_repr%C3%A4sentatives_Foto',
			'{link_id}' => 'https://wiki.foodsharing.de/Ausweis',
			'{link_fs}' => 'https://wiki.foodsharing.de/Foodsaver',
		]));

		if (!file_exists('images/thumb_crop_' . $photo)) {
			$p_cnt .= $this->v_utils->v_photo_edit('img/portrait.png');
		} else {
			$p_cnt .= $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo);
		}

		return $this->v_utils->v_field($p_cnt, $this->translator->trans('settings.photo.title'));
	}
}
