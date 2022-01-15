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
			$infotext = $this->v_utils->v_success($this->translator->trans('quiz.congrats_details', [
				'{points}' => $session['fp'],
				'{max_points}' => $session['maxfp']
			]));
		} else {
			$infotext = $this->v_utils->v_error($this->translator->trans('quiz.not_passed_details', [
				'{points}' => $session['fp'],
				'{max_points}' => $session['maxfp']
			]));
		}
		$this->pageHelper->addContent('<div class="quizsession">' . $this->topbar($session['name'] . $this->translator->trans('quiz.type'), '', '<img src="/img/quiz.png" />') . '</div>');
		$out = '';

		$out .= $infotext;

		if ($session['fp'] <= $session['maxfp']) {
			$btn = '';
			switch ($session['quiz_id']) {
				case 1:
					$btn = '<a href="/?page=settings&sub=up_fs" class="button">' . $this->translator->trans('quiz.finishnow.fs') . '</a>';
					break;

				case 2:
					$btn = '<a href="/?page=settings&sub=up_bip" class="button">' . $this->translator->trans('quiz.finishnow.bv') . '</a>';
					break;

				default:
					break;
			}
			$out .= $this->v_utils->v_field('<p>' . $this->translator->trans('quiz.finished') . '</p><p>' . $this->translator->trans('quiz.resultsbelow') . '</p><p style="padding:15px;text-align:center;">' . $btn . '</p>', $this->translator->trans('quiz.heavysigh'), ['class' => 'ui-padding']);
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

			$ftext = $this->translator->trans('quiz.donecorrectly');
			++$i;
			$cnt = '<div class="question">' . $r['text'] . '</div>';

			$cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.wikilink'), '<a target="_blank" href="' . $r['wikilink'] . '">' . $r['wikilink'] . '</a>');

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
						$atext = ' ' . $this->translator->trans('quiz.choice.correcta');
						$sort_right = 'right';
					} else {
						$atext = ' ' . $this->translator->trans('quiz.choice.correctb');
						$sort_right = 'right';
					}
				} elseif ($a['right'] == AnswerRating::NEUTRAL) {
					$atext = ' ' . $this->translator->trans('quiz.choice.neut');
					$right = 'neutral';
					$sort_right = 'neutral';
				} else {
					if ($a['right']) {
						$atext = ' ' . $this->translator->trans('quiz.choice.wronga');
						$sort_right = 'false';
					} else {
						$atext = ' ' . $this->translator->trans('quiz.choice.wrongb');
						$sort_right = 'false';
					}
				}

				if ($sort_right == 'right') {
					$right_answers .= '
					<div class="answer q-' . $right . '">
						' . $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.answer') . ' ' . $ai . $atext, $a['text']) . '
						' . $this->v_utils->v_input_wrapper($this->translator->trans('explanation'), $a['explanation']) . '

					</div>';
				} elseif ($sort_right == 'neutral') {
					$neutral_answers .= '
					<div class="answer q-' . $right . '">
						' . $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.answer') . ' ' . $ai . $atext, $a['text']) . '
						' . $this->v_utils->v_input_wrapper($this->translator->trans('explanation'), $a['explanation']) . '

					</div>';
				} elseif ($sort_right == 'false') {
					$wrong_answers .= '
					<div class="answer q-' . $right . '">
						' . $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.answer') . ' ' . $ai . $atext, $a['text']) . '
						' . $this->v_utils->v_input_wrapper($this->translator->trans('explanation'), $a['explanation']) . '

					</div>';
				}
			}

			$no_wrong_right_sort = false;

			if ($r['userfp'] > 0) {
				$cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.fpsum'), $r['userfp']);
				if ($r['percent'] == 100) {
					$ftext = ' ' . $this->translator->trans('quiz.choice.sadlywrong');
					if (!$r['noco'] && $noclicked) {
						$no_wrong_right_sort = true;
						$ftext = ' ' . $this->translator->trans('quiz.choice.alsowrong');
					}
				} else {
					$ftext = ' ' . $this->translator->trans('quiz.choice.percenta') . ' ' . (100 - $r['percent']) . ' ' . $this->translator->trans('quiz.choice.percentb');
				}
			}

			if ($no_wrong_right_sort) {
				$cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.responses'), $wrong_answers . $right_answers, false, ['collapse' => true]);
			} else {
				if (!empty($right_answers)) {
					$cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.cresponses'), $right_answers, false, ['collapse' => true]);
				}
				if (!empty($wrong_answers)) {
					$cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.wresponses'), $wrong_answers, false, ['collapse' => true]);
				}
				if (!empty($neutral_answers)) {
					$cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.nresponses'), $neutral_answers, false, ['collapse' => true]);
				}
			}

			$cnt .= '<div id="qcomment-' . (int)$r['id'] . '">' . $this->v_utils->v_input_wrapper($this->translator->trans('quiz.choice.writecomment'), '<textarea style="height:50px;" id="comment-' . $r['id'] . '" name="desc" class="input textarea value"></textarea><br /><a class="button" href="#" onclick="ajreq(\'addcomment\',{app:\'quiz\',comment:$(\'#comment-' . (int)$r['id'] . '\').val(),id:' . (int)$r['id'] . '});return false;">' . $this->translator->trans('quiz.choice.send') . '</a>', false, ['collapse' => true]) . '</div>';

			/*
			 * If the question was a joke question lets display it to the user!
			 */
			if ($was_a_joke) {
				$ftext = $this->translator->trans('quiz.choice.joke') . ' <i class="far fa-smile"></i>';
			}

			/*
			 * If the question is k.o. question and the user has error display a message to the user
			 */
			if ($was_a_ko_question && $r['userfp'] > 0) {
				$ftext = $this->translator->trans('quiz.choice.koquestion');
				$cnt = $this->v_utils->v_info($this->translator->trans('quiz.choice.whatisko'));
			}

			$out .= '
					<div class="quizsession">' .
				$this->v_utils->v_field($cnt, $this->translator->trans('quiz.question') . ' ' . $i . ' ' . $ftext, ['class' => 'ui-padding']) . '
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
			$this->v_utils->v_info($this->translator->trans('settings.changemail.question') . ' <strong>' . $email . '</strong> ?') .
			$this->v_utils->v_form_passwd('passcheck');
	}

	public function settingsCalendar()
	{
		return $this->vueComponent('calendar', 'Calendar', [
			'baseUrlWebcal' => WEBCAL_URL . '/api/calendar/',
			'baseUrlHttp' => BASE_URL . '/api/calendar/'
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
				$this->translator->trans('foodsaver.delete_own_account')
				. $this->translator->trans('notice') . '<br/>'
				. $this->translator->trans('legal.if_delete.legal_1') . '<br/>'
				. $this->translator->trans('legal.if_delete.legal_2') . '<br/><br/>'
				. $this->translator->trans('legal.if_delete.this_gets_deleted_main')
				. $this->translator->trans('legal.if_delete.this_gets_deleted_stores')
				. $this->translator->trans('legal.if_delete.this_gets_deleted_quiz')
				. $this->translator->trans('legal.if_delete.this_gets_deleted_verify')
				. $this->translator->trans('legal.if_delete.this_gets_deleted_friendlist')
				. $this->translator->trans('legal.if_delete.this_gets_deleted_trustbananas') . '<br/><br/>'
				. $this->translator->trans('legal.if_delete.this_doesnt_get_deleted') . '<br/>'
				. $this->translator->trans('legal.if_delete.this_doesnt_get_deleted_name')
				. $this->translator->trans('legal.if_delete.this_doesnt_get_deleted_address')
				. $this->translator->trans('legal.if_delete.this_doesnt_get_deleted_history')
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
			$this->v_utils->v_form_text('telefon', ['placeholder' => $this->translator->trans('register.landline_example')]),
			$this->v_utils->v_form_text('handy', ['placeholder' => $this->translator->trans('register.phone_example')]),
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
		$out = $this->v_utils->v_input_wrapper($this->translator->trans('quiz.threestrikes'), $this->translator->trans('quiz.waitxdays') . $days_to_wait . $this->translator->trans('quiz.days') . '.');

		$out = $this->v_utils->v_field($out, $this->translator->trans('quiz.learnbreak'), ['class' => 'ui-padding']);

		return $out;
	}

	public function quizContinue($quiz)
	{
		$out = '';

		$out .= $this->v_utils->v_input_wrapper($this->translator->trans('quiz.notfinishedyet'), $this->translator->trans('quiz.safeandsound'));

		$out .= $quiz['desc'];

		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">' . $this->translator->trans('quiz.continuenow') . '</a></p>';

		$out = $this->v_utils->v_field($out, $quiz['name'] . $this->translator->trans('quiz.continuetype'), ['class' => 'ui-padding']);

		return $out;
	}

	public function quizRetry($quiz, $failed_count, $max_failed_count)
	{
		$out = $this->v_utils->v_input_wrapper($this->translator->trans('quiz.trynumber') . ' ' . ($failed_count + 1), '<p>' . $failed_count . $this->translator->trans('quiz.failedbeforebut') . ' ' . ($max_failed_count - $failed_count) . '</p><p>' . $this->translator->trans('quiz.failedbeforebut') . '</p>');

		$out .= $quiz['desc'];

		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">' . $this->translator->trans('quiz.timedstart') . '</a></p>';

		if ($quiz['id'] == 1) {
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',easymode:1,qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">' . $this->translator->trans('quiz.regstart') . '</a></p>';
		}

		$out = $this->v_utils->v_field($out, $quiz['name'] . $this->translator->trans('quiz.quizleft'), ['class' => 'ui-padding']);

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
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">' . $this->translator->trans('quiz.timedstart') . '</a></p>';
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',easymode:1,qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">' . $this->translator->trans('quiz.regstart') . '</a></p>';
		} else {
			$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:' . (int)$quiz['id'] . '});" href="#" class="button button-big">' . $this->translator->trans('quiz.nownownow') . '</a></p>';
		}

		$out = $this->v_utils->v_field($out, $quiz['name'] . $this->translator->trans('quiz.quizleft'), ['class' => 'ui-padding']);

		return $out;
	}

	public function picture_box($photo): string
	{
		$p_cnt = $this->v_utils->v_info($this->translator->trans('settings.photo.info', [
			'{link_photo}' => 'https://wiki.foodsharing.de/Foto_-_Leitfaden_f%C3%BCr_ein_repr%C3%A4sentatives_Foto',
			'{link_id}' => 'https://wiki.foodsharing.de/Ausweis',
			'{link_fs}' => 'https://wiki.foodsharing.de/Foodsaver',
		]));

		// find previous picture
		$initialValue = 'img/portrait.png';
		if (!empty($photo)) {
			if (strpos($photo, '/api/uploads/') === 0) {
				// path for pictures uploaded with the new API
				$initialValue = $photo . '?w=200&h=257';
			} elseif (file_exists('images/thumb_crop_' . $photo)) {
				// backward compatible path for old pictures
				$initialValue = 'images/thumb_crop_' . $photo;
			}
		}

		// create picture upload component
		$p_cnt .= $this->vueComponent('image-upload', 'profile-picture', [
			'initialValue' => $initialValue,
			'imgHeight' => 400,
			'imgWidth' => 400,
		]);

		return $this->v_utils->v_field($p_cnt, $this->translator->trans('settings.photo.title'));
	}
}
