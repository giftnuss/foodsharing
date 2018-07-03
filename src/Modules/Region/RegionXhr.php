<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class RegionXhr extends Control
{
	private $responses;
	private $forumGateway;
	private $regionHelper;
	private $twig;

	public function __construct(Model $model, ForumGateway $forumGateway, RegionHelper $regionHelper, \Twig\Environment $twig)
	{
		$this->model = $model;
		$this->forumGateway = $forumGateway;
		$this->regionHelper = $regionHelper;
		$this->twig = $twig;
		$this->responses = new XhrResponses();

		parent::__construct();
	}

	private function hasThemeAccess($BotThemestatus)
	{
		return ($BotThemestatus['bot_theme'] == 0 && $this->func->mayBezirk($BotThemestatus['bezirk_id']))
			|| ($BotThemestatus['bot_theme'] == 1 && $this->func->isBotFor($BotThemestatus['bezirk_id']))
			|| $this->func->isOrgaTeam();
	}

	public function followTheme()
	{
		$bot_theme = $this->forumGateway->getBotThreadStatus($_GET['tid']);
		if (!S::may() || !$this->hasThemeAccess($bot_theme)) {
			return $this->responses->fail_permissions();
		}

		$this->forumGateway->followThread(S::id(), $_GET['tid']);

		return $this->responses->success();
	}

	public function unfollowTheme()
	{
		$bot_theme = $this->forumGateway->getBotThreadStatus($_GET['tid']);
		if (!S::may() || !$this->hasThemeAccess($bot_theme)) {
			return $this->responses->fail_permissions();
		}

		$this->forumGateway->unfollowThread(S::id(), $_GET['tid']);

		return $this->responses->success();
	}

	public function stickTheme()
	{
		$bot_theme = $this->forumGateway->getBotThreadStatus($_GET['tid']);
		if (!S::may() || !$this->hasThemeAccess($bot_theme)) {
			return $this->responses->fail_permissions();
		}

		$this->forumGateway->stickThread($_GET['tid']);

		return $this->responses->success();
	}

	public function unstickTheme()
	{
		$bot_theme = $this->forumGateway->getBotThreadStatus($_GET['tid']);
		if (!S::may() || !$this->hasThemeAccess($bot_theme)) {
			return $this->responses->fail_permissions();
		}

		$this->forumGateway->unstickThread($_GET['tid']);

		return $this->responses->success();
	}

	public function morethemes()
	{
		$regionId = (int)$_GET['bid'];
		$ambassadorForum = ($_GET['bot'] == 1);
		if (isset($_GET['page']) && $this->func->mayBezirk($regionId)) {
			if ($ambassadorForum && !$this->func->isBotFor($regionId)) {
				return $this->responses->fail_permissions();
			}

			$viewdata['region']['id'] = $regionId;
			$viewdata['threads'] = $this->regionHelper->transformThreadViewData($this->forumGateway->listThreads($regionId, $ambassadorForum, (int)$_GET['page'], (int)$_GET['last']), $regionId, $ambassadorForum);

			return array(
				'status' => 1,
				'data' => array(
					'html' => $this->twig->render('pages/Region/forum/threadEntries.twig', $viewdata)
				)
			);
		}
	}

	public function quickreply()
	{
		if (isset($_GET['bid']) && isset($_GET['tid']) && isset($_GET['pid']) && S::may() && isset($_POST['msg']) && $_POST['msg'] != '') {
			$sub = 'forum';
			if ($_GET['sub'] != 'forum') {
				$sub = 'botforum';
			}

			$body = strip_tags($_POST['msg']);
			$body = nl2br($body);
			$body = $this->func->autolink($body);

			if ($bezirk = $this->model->getValues(array('id', 'name'), 'bezirk', $_GET['bid'])) {
				if ($post_id = $this->forumGateway->addPost(S::id(), $_GET['tid'], $body, $_GET['pid'], $bezirk)) {
					if ($follower = $this->forumGateway->getThreadFollower(S::id(), $_GET['tid'])) {
						$theme = $this->model->getVal('name', 'theme', $_GET['tid']);

						foreach ($follower as $f) {
							$this->func->tplMail(19, $f['email'], array(
								'anrede' => $this->func->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
								'name' => $f['name'],
								'link' => BASE_URL . '/?page=bezirk&bid=' . $bezirk['id'] . '&sub=' . $sub . '&tid=' . (int)$_GET['tid'] . '&pid=' . $post_id . '#post' . $post_id,
								'theme' => $theme,
								'post' => $body,
								'poster' => S::user('name')
							));
						}
					}

					echo json_encode(array(
						'status' => 1,
						'message' => 'Prima! Deine Antwort wurde gespeichert.'
					));
					exit();
				}
			}

			/*
			 * end add post
			 */
		}

		echo json_encode(array(
			'status' => 0,
			'message' => $this->func->s('post_could_not_saved')
		));
		exit();
	}

	public function signout()
	{
		$data = $_GET;
		if ($this->func->mayBezirk($data['bid'])) {
			$this->model->del('DELETE FROM `fs_foodsaver_has_bezirk` WHERE `bezirk_id` = ' . (int)$data['bid'] . ' AND `foodsaver_id` = ' . (int)$this->func->fsId() . ' ');
			$this->model->del('DELETE FROM `fs_botschafter` WHERE `bezirk_id` = ' . (int)$data['bid'] . ' AND `foodsaver_id` = ' . (int)$this->func->fsId() . ' ');

			return array('status' => 1);
		}

		return array('status' => 0);
	}
}
