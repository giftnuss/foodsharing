<?php

namespace Foodsharing\Modules\Region;

use Flourish\fFile;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\FairTeiler\FairTeilerGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class RegionControl extends Control
{
	private $bezirk_id;
	private $bezirk;
	private $bot_theme;
	private $mode;
	private $gateway;
	private $eventGateway;
	private $foodsaverGateway;
	private $forumGateway;
	private $fairteilerGateway;

	public function __construct(Model $model, RegionView $view, RegionGateway $gateway, EventGateway $eventGateway, FoodsaverGateway $foodsaverGateway, ForumGateway $forumGateway, FairTeilerGateway $fairteilerGateway)
	{
		$this->mode = 'normal';
		$this->model = $model;
		$this->view = $view;
		$this->gateway = $gateway;
		$this->eventGateway = $eventGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->fairteilerGateway = $fairteilerGateway;
		$this->view->setMode($this->mode);
		parent::__construct();

		if (!S::may()) {
			$this->func->goLogin();
		}

		$this->bezirk_id = false;
		if (($this->bezirk_id = $this->func->getGetId('bid')) === false) {
			$this->bezirk_id = $this->func->getBezirkId();
		}

		if (!$this->func->mayBezirk($this->bezirk_id)) {
			$this->func->go('/?page=dashboard');
		}

		$this->bezirk = false;
		if ($bezirk = $this->gateway->getRegionDetails($this->bezirk_id)) {
			$big = array(8 => 1, 5 => 1, 6 => 1);
			if (isset($big[$bezirk['type']])) {
				$this->mode = 'big';
			} elseif ($bezirk['type'] == 7) {
				$this->mode = 'orgateam';
			}
			$this->view->setMode($this->mode);
			$this->bezirk = $bezirk;
		}

		$this->view->setBezirk($this->bezirk);

		$this->bot_theme = 0;
		if ($this->getSub() == 'botforum') {
			$this->bot_theme = 1;
		}

		if ($this->bot_theme == 1) {
			if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
			} else {
				$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=forum');
			}
		}
	}

	public function index()
	{
		if ($this->bezirk !== false && $this->func->mayBezirk($this->bezirk_id)) {
			if ($this->mode == 'orgateam') {
				return $this->orgateam();
			} else {
				return $this->normal();
			}
		}
	}

	public function normal()
	{
		if (!isset($_GET['sub'])) {
			$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=forum');
		}

		$this->func->addTitle($this->bezirk['name']);

		$bezirk = $this->bezirk;
		if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
			$this->bezirkRequests();
		}
		$this->func->addBread($bezirk['name'], '/?page=bezirk&bid=' . (int)$this->bezirk_id);
		$this->func->addContent($this->view->top(), CNT_TOP);

		$menu = array();

		$menu[] = array('name' => 'Forum', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=forum');
		$menu[] = array('name' => 'Termine', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=events');

		if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
			$menu[] = array('name' => 'BotschafterInnenforum', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=botforum');
		}

		$menu[] = array('name' => 'Fair-Teiler', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=fairteiler');

		$menu[] = array('name' => 'Arbeitsgruppen', 'href' => '/?page=groups&p=' . (int)$this->bezirk_id);

		$this->func->addContent($this->view->menu($menu, array('active' => $this->getSub())), CNT_LEFT);

		$this->func->addContent(
			$this->v_utils->v_field($this->view->fsAvatarList($bezirk['botschafter'], array(
				'scroller' => false
			)), 'BotschafterInnen für ' . $bezirk['name']),
			CNT_LEFT
		);

		$this->func->addContent(
			$this->v_utils->v_field($this->view->fsAvatarList($bezirk['foodsaver']), count($bezirk['foodsaver']) . ' aktive Foodsaver in ' . $bezirk['name']),
			CNT_LEFT
		);

		if ($this->bezirk['sleeper']) {
			$this->func->addContent(
				$this->v_utils->v_field($this->view->fsAvatarList($bezirk['sleeper']), count($bezirk['sleeper']) . ' Schlafmützen in ' . $bezirk['name']),
				CNT_LEFT
			);
		}

		$this->func->addContent(
			$this->view->signout($this->bezirk),
			CNT_LEFT
		);
	}

	public function orgateam()
	{
		if (!isset($_GET['sub'])) {
			$this->func->go('/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=wall');
		}

		$bezirk = $this->bezirk;
		$menu = array();

		$this->func->addTitle($this->bezirk['name']);

		if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
			if ($requests = $this->gateway->listRequests($this->bezirk_id)) {
				$menu[] = array('name' => 'Bewerbungen <strong>(' . count($requests) . ')</strong>', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=applications');
			}
		}
		$this->func->addBread($bezirk['name'], '/?page=bezirk&bid=' . (int)$this->bezirk_id);
		$this->func->addContent($this->view->topOrga(), CNT_TOP);

		$menu[] = array('name' => 'Pinnwand', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=wall');
		$menu[] = array('name' => 'Forum', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=forum');
		$menu[] = array('name' => 'Termine', 'href' => '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=events');

		if (S::may('orga') || $this->func->isBotFor($this->bezirk_id)) {
			$menu[] = array('name' => 'Gruppe verwalten', 'href' => '/?page=groups&sub=edit&id=' . (int)$this->bezirk_id);
		}

		$this->func->addContent($this->view->menu($menu, array('active' => $this->getSub())), CNT_LEFT);

		$this->func->addContent(
			$this->v_utils->v_field($this->view->fsAvatarList($bezirk['foodsaver'], array('shuffle' => false)), count($bezirk['foodsaver']) . ' aktive Mitglieder'),
			CNT_LEFT
		);

		if ($this->bezirk['sleeper']) {
			$this->func->addContent(
				$this->v_utils->v_field($this->view->fsAvatarList($bezirk['sleeper']), count($bezirk['sleeper']) . ' Schlafmützen in ' . $bezirk['name']),
				CNT_LEFT
			);
		}

		$this->func->addContent(
			$this->view->signout($this->bezirk),
			CNT_LEFT
		);
	}

	public function wall()
	{
		$this->func->addContent($this->v_utils->v_field($this->wallposts('bezirk', $this->bezirk_id), 'Pinnwand'));
	}

	public function fairteiler()
	{
		$this->func->addBread($this->func->s('fairteiler'), '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=fairteiler');
		$this->func->addContent($this->view->ftOptions($this->bezirk_id), CNT_RIGHT);
		$this->func->addTitle($this->func->s('fairteiler'));
		$bezirk_ids = $this->gateway->listIdsForDescendantsAndSelf($this->bezirk_id);
		if ($fairteiler = $this->fairteilerGateway->listFairteiler($bezirk_ids)) {
			$this->func->addContent($this->view->listFairteiler($fairteiler));
		} else {
			$this->func->addContent($this->v_utils->v_info($this->func->s('no_fairteiler_available')));
		}
	}

	private function bezirkRequests()
	{
		if ($requests = $this->gateway->listRequests($this->bezirk_id)) {
			$out = '<table class="pintable">';
			$odd = 'odd';
			$this->func->addJs('$("table.pintable tr td ul li").tooltip();');

			$this->func->addJsFunc('
				function acceptRequest(fsid,bid){
					showLoader();
					$.ajax({
						dataType:"json",
						data: "fsid="+fsid+"&bid="+bid,
						url:"xhr.php?f=acceptBezirkRequest",
						success : function(data){
							if(data.status == 1)
							{
								reload();
								//$("tr.request-"+fsid).fadeOut();
							}
						},
						complete:function(){hideLoader();}
					});
				}
				function denyRequest(fsid,bid){
					showLoader();
					$.ajax({
						dataType:"json",
						data: "fsid="+fsid+"&bid="+bid,
						url:"xhr.php?f=denyBezirkRequest",
						success : function(data){
							if(data.status == 1)
							{
								reload();
							}
						},
						complete:function(){hideLoader();}
					});
				}');

			foreach ($requests as $r) {
				if ($odd == 'even') {
					$odd = 'odd';
				} else {
					$odd = 'even';
				}
				$out .= '
		<tr class="' . $odd . ' request-' . $r['id'] . '">
			<td class="img" width="35px"><a href="#" onclick="profile(' . (int)$r['id'] . ');return false;"><img src="' . $this->func->img($r['photo']) . '" /></a></td>
			<td style="padding-top:17px;"><span class="msg"><a href="#" onclick="profile(' . (int)$r['id'] . ');return false;">' . $r['name'] . '</a></span></td>
			<td style="width:66px;padding-top:17px;"><span class="msg"><ul class="toolbar"><li class="ui-state-default ui-corner-left" title="Ablehnen" onclick="denyRequest(' . (int)$r['id'] . ',' . (int)$this->bezirk_id . ');"><span class="ui-icon ui-icon-closethick"></span></li><li class="ui-state-default ui-corner-right" title="Akzeptieren" onclick="acceptRequest(' . (int)$r['id'] . ',' . (int)$this->bezirk_id . ');"><span class="ui-icon ui-icon-heart"></span></li></ul></span></td>
		</tr>';
			}

			$out .= '</table>';

			$this->func->hiddenDialog('requests', array($out));
			$this->func->addJs('$("#dialog_requests").dialog("option","title","Anfragen für ' . $this->bezirk['name'] . '");');
			$this->func->addJs('$("#dialog_requests").dialog("option","buttons",{});');
			$this->func->addJs('$("#dialog_requests").dialog("open");');
		}
	}

	public function forum()
	{
		return $this->_forum(false);
	}

	public function botforum()
	{
		return $this->_forum(true);
	}

	private function _forum($botForum)
	{
		$botForum = $botForum && ($this->bot_theme == 1);
		$this->func->addBread($this->func->s('forum'), '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=' . ($botForum ? 'botforum' : 'forum'));

		$this->func->addTitle($this->func->s($botForum ? 'bot_forum' : 'forum'));

		if (isset($_POST['submitted'])) {
			$body = strip_tags($_POST['body']);
			$body = nl2br($body);
			$body = $this->func->autolink($body);

			if ($post_id = $this->forumGateway->addPost(S::id(), $_POST['thread'], $body, $_POST['post'], $this->bezirk)) {
				// Dunno why this is only done for non-bot-posts
				if (!$botForum) {
					if ($_POST['follow'] == 1) {
						$this->forumGateway->followThread(S::id(), $_POST['thread']);
					} elseif ($_POST['follow'] == 0) {
						$this->forumGateway->unfollowThread(S::id(), $_POST['thread']);
					}

					if ($follower = $this->forumGateway->getThreadFollower(S::id(), $_POST['thread'])) {
						$theme = $this->model->getVal('name', 'theme', $_POST['thread']);
						$poster = $this->model->getVal('name', 'foodsaver', $this->func->fsId());
						foreach ($follower as $f) {
							$this->func->tplMail(19, $f['email'], array(
								'anrede' => $this->func->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
								'name' => $f['name'],
								'link' => BASE_URL . '/?page=bezirk&bid=' . $this->bezirk_id . '&sub=' . $this->getSub() . '&tid=' . (int)$_POST['thread'] . '&pid=' . $post_id . '#post' . $post_id,
								'theme' => $theme,
								'post' => $body,
								'poster' => $poster
							));
						}
					}
				}

				$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=' . $this->getSub() . '&tid=' . (int)$_POST['thread'] . '&pid=' . $post_id . '#post' . $post_id);
			} else {
				$this->func->error($this->func->s('post_could_not_saved'));
				$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=' . $this->getSub() . '&tid=' . (int)$_POST['thread'] . '&pid=' . (int)$_POST['post'] . '#post' . (int)$_POST['post']);
			}
		}

		if (isset($_GET['tid'])) {
			return $this->forum_thread($_GET['tid']);
		}

		$this->func->addContent($this->view->forum_top());

		if ($themes = $this->forumGateway->listThreads($this->bezirk_id, $botForum ? 1 : 0)) {
			$this->func->addContent(
				$this->view->forum_index($themes, false, $botForum ? 'botforum' : 'forum')
			);
		} else {
			$this->func->addContent(
				$this->view->forum_empty()
			);
		}

		$this->func->addContent($this->view->forum_bottom($botForum ? 1 : 0));
	}

	private function forum_thread($thread_id)
	{
		if ($thread = $this->forumGateway->getThread($this->bezirk_id, $thread_id, $this->bot_theme)) {
			$this->func->addBread($thread['name']);
			if ($thread['active'] == 0 && ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam())) {
				if (isset($_GET['activate'])) {
					$this->forumGateway->activateThread($thread_id);
					$this->themeInfoMail($thread_id);
					$this->func->info('Thema wurde aktiviert!');
					$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=forum&tid=' . (int)$thread_id);
				} elseif (isset($_GET['delete'])) {
					$this->func->info('Thema wurde gelöscht!');
					$this->forumGateway->deleteThread($thread_id);
					$this->func->go('/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=forum');
				}
				$this->func->addContent($this->view->activateTheme($thread), CNT_TOP);
			}

			if ($thread['active'] == 1 || S::may('orga') || $this->func->isBotFor($this->bezirk_id)) {
				$posts = $this->forumGateway->listPosts($thread_id);
				$followCounter = $this->forumGateway->getFollowCounter(S::id(), $thread_id);
				$bezirkType = $this->gateway->getType($this->bezirk_id);
				$stickStatus = $this->forumGateway->getStickStatus($thread_id);
				$this->func->addContent($this->view->thread($thread, $posts, $followCounter, $bezirkType, $stickStatus));
			} else {
				$this->func->go('/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=forum');
			}
		} else {
			$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=' . $this->getSub());
		}
	}

	public function ntheme()
	{
		$this->func->addBread($this->func->s('forum'), '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=' . $this->getSub());
		$this->func->addBread($this->func->s('new_theme'));

		if ($this->handleNtheme()) {
			$this->func->go('/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=' . $this->getSub());
		}

		$this->func->addContent($this->view->newThemeForm());
	}

	private function themeInfoBotschafter($theme_id)
	{
		$theme = $this->model->getValues(array('foodsaver_id', 'name'), 'theme', $theme_id);
		$poster = $this->model->getVal('name', 'foodsaver', $theme['foodsaver_id']);

		if ($foodsaver = $this->model->getBotschafter($this->bezirk_id)) {
			foreach ($foodsaver as $i => $fs) {
				$foodsaver[$i]['var'] = array(
					'name' => $fs['vorname'],
					'anrede' => $this->func->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
					'bezirk' => $this->bezirk['name'],
					'poster' => $poster,
					'thread' => $theme['name'],
					'link' => BASE_URL . '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=forum&tid=' . (int)$theme_id
				);
			}

			$this->tplMailList(20, $foodsaver, array(
				'email' => EMAIL_PUBLIC,
				'email_name' => EMAIL_PUBLIC_NAME
			));
		}
	}

	private function themeInfoMail($theme_id)
	{
		$theme = $this->model->getValues(array('foodsaver_id', 'name', 'last_post_id'), 'theme', $theme_id);
		$body = $this->model->getVal('body', 'theme_post', $theme['last_post_id']);

		$poster = $this->model->getVal('name', 'foodsaver', $theme['foodsaver_id']);
		$link = BASE_URL . '/?page=bezirk&bid=' . $this->bezirk_id . '&sub=' . $this->getSub() . '&tid=' . $theme_id;

		if ($this->bot_theme == 1) {
			$foodsaver = $this->model->getBotschafter($this->bezirk_id);
		} elseif ($this->mode == 'orgateam') {
			$foodsaver = $this->foodsaverGateway->listActiveWithFullNameByRegion($this->bezirk_id);
		} else {
			$foodsaver = $this->model->getFoodsaver($this->bezirk_id);
		}

		if ($foodsaver) {
			$tmp = array();
			foreach ($foodsaver as $fs) {
				$tmp[$fs['email']] = $fs;
			}

			$foodsaver = array();
			$i = 0;
			foreach ($tmp as $fs) {
				$foodsaver[$i] = $fs;
				$foodsaver[$i]['var'] = array(
					'name' => $fs['vorname'],
					'anrede' => $this->func->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
					'bezirk' => $this->bezirk['name'],
					'poster' => $poster,
					'thread' => $theme['name'],
					'link' => $link,
					'post' => $body
				);
				++$i;
			}

			if ($this->bot_theme == 1) {
				$this->tplMailList(13, $foodsaver, array(
					'email' => 'noreply@' . DEFAULT_EMAIL_HOST,
					'email_name' => EMAIL_PUBLIC_NAME
				));
			} else {
				$this->tplMailList(12, $foodsaver, array(
					'email' => 'noreply@' . DEFAULT_EMAIL_HOST,
					'email_name' => EMAIL_PUBLIC_NAME
				));
			}
		}
	}

	private function handleNtheme()
	{
		if (isset($_POST['form_submit'])) {
			$active = 1;
			if (
				!$this->func->isVerified()
				||
				(
					$this->mode == 'big'
					&&
					!($this->func->isBotFor($this->bezirk_id) || $this->getSub() == 'botforum')
				)
				||
				$this->bezirk['moderated']
			) {
				$this->func->info('Das Thema wurde gespeichert und wird veröffentlicht sobald ein Botschafter aus ' . $this->bezirk['name'] . ' es bestätigt.');
				$active = 0;
			}

			$body = strip_tags($_POST['body']);
			$body = nl2br($body);
			$body = $this->func->autolink($body);

			if ($theme_id = $this->forumGateway->addThread($this->func->fsId(), $this->bezirk_id, $_POST['title'], $body, $this->bot_theme, $active)) {
				if ($active) {
					$this->themeInfoMail($theme_id);
				} else {
					$this->themeInfoBotschafter($theme_id);
				}

				return true;
			}
		}

		return false;
	}

	public function events()
	{
		$this->func->addBread('Termine', '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=events');

		$this->func->addTitle($this->func->s('dates'));

		if ($events = $this->eventGateway->listForRegion((int)$this->bezirk_id)) {
			$this->func->addContent($this->view->listEvents($events));
		} else {
			$this->func->addContent($this->v_utils->v_info($this->func->s('no_events_posted')));
		}

		$this->func->addContent($this->view->addEvent(), CNT_RIGHT);
	}

	public function applications()
	{
		if (!($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam())) {
			return;
		}
		if ($requests = $this->gateway->listRequests($this->bezirk_id)) {
			$this->func->addContent($this->view->applications($requests));
		}
	}

	public function show()
	{
		if ($event = $this->eventGateway->getEvent($_GET['id'])) {
			$this->func->addBread('Termine', '/?page=bezirk&bid=' . (int)$this->bezirk_id . '&sub=events');
			$this->func->addBread($event['name']);
			$this->func->addContent($this->view->eventTop($event), CNT_TOP);
			$this->func->addContent($this->view->event($event));
			if ($event['location'] != false) {
				$this->func->addContent($this->view->location($event['location']), CNT_RIGHT);
			}

			$this->func->addContent($this->v_utils->v_field($this->wallposts('event', $event['id']), 'Pinnwand'));
		} else {
			$this->func->go('/?page=bezirk&bid=' . $this->bezirk_id . '&sub=events');
		}
	}

	private function tplMailList($tpl_id, $to, $from = false, $attach = false)
	{
		if (!is_array($from) && $this->func->validEmail($from)) {
			$from = array(
				'email' => $from,
				'email_name' => $from
			);
		} elseif ($from === false) {
			$from = array(
				'email' => DEFAULT_EMAIL,
				'email_name' => DEFAULT_EMAIL_NAME
			);
		}

		$tpl_message = $this->model->getOne_message_tpl($tpl_id);

		foreach ($to as $t) {
			if (!$this->func->validEmail($t['email'])) {
				continue;
			}

			$mail = new AsyncMail();
			$mail->setFrom($from['email'], $from['email_name']);

			$search = array();
			$replace = array();
			foreach ($t['var'] as $key => $v) {
				$search[] = '{' . strtoupper($key) . '}';
				$replace[] = $v;
			}

			$message = str_replace($search, $replace, $tpl_message['body']);
			$subject = str_replace($search, $replace, $tpl_message['subject']);

			$mail->addRecipient($t['email']);

			if (!$subject) {
				$subject = 'Foodsharing-Mail';
			}
			$mail->setSubject($subject);
			//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body

			if (!isset($t['token'])) {
				$t['token'] = false;
			}

			$mail->setHTMLBody($this->func->emailBodyTpl($message, $t['email'], $t['token']));

			// playintext body
			$message = str_replace('<br />', "\r\n", $message);
			$message = strip_tags($message);
			$mail->setBody($message);

			/*
			 *  todo: implement logic that we dont have to send one attachment multiple time to the slave db ...
			*/

			if ($attach !== false) {
				foreach ($attach as $a) {
					$mail->addAttachment(new fFile($a['path']), $a['name']);
				}
			}
			$mail->send();
		}
	}
}
