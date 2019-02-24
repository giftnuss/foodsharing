<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\ForumPermissions;

final class RegionXhr extends Control
{
	private $responses;
	private $foodsaverGateway;
	private $forumGateway;
	private $forumPermissions;
	private $regionHelper;
	private $twig;

	public function __construct(
		Db $model,
		ForumGateway $forumGateway,
		ForumPermissions $forumPermissions,
		RegionHelper $regionHelper,
		\Twig\Environment $twig,
		FoodsaverGateway $foodsaverGateway
	) {
		$this->model = $model;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->forumPermissions = $forumPermissions;
		$this->regionHelper = $regionHelper;
		$this->twig = $twig;
		$this->responses = new XhrResponses();

		parent::__construct();
	}

	private function hasThemeAccess($BotThemestatus)
	{
		return ($BotThemestatus['bot_theme'] == 0 && $this->session->mayBezirk($BotThemestatus['bezirk_id']))
			|| ($BotThemestatus['bot_theme'] == 1 && $this->session->isAdminFor($BotThemestatus['bezirk_id']))
			|| $this->session->isOrgaTeam();
	}

	public function morethemes()
	{
		$regionId = (int)$_GET['bid'];
		$ambassadorForum = ($_GET['bot'] == 1);
		if (isset($_GET['page']) && $this->session->mayBezirk($regionId)) {
			if ($ambassadorForum && !$this->session->isAdminFor($regionId)) {
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
		if (isset($_GET['bid'], $_GET['tid'], $_GET['pid'], $_POST['msg']) && $this->session->may(
			) && $_POST['msg'] != '') {
			$sub = 'forum';
			if ($_GET['sub'] != 'forum') {
				$sub = 'botforum';
			}

			$body = $_POST['msg'];

			if ($this->forumPermissions->mayPostToThread($_GET['tid'])
				&& $bezirk = $this->model->getValues(array('id', 'name'), 'bezirk', $_GET['bid'])
			) {
				if ($post_id = $this->forumGateway->addPost($this->session->id(), $_GET['tid'], $body)) {
					if ($follower = $this->forumGateway->getThreadFollower($this->session->id(), $_GET['tid'])) {
						$theme = $this->model->getVal('name', 'theme', $_GET['tid']);

						foreach ($follower as $f) {
							$this->emailHelper->tplMail(19, $f['email'], array(
								'anrede' => $this->func->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
								'name' => $f['name'],
								'link' => BASE_URL . '/?page=bezirk&bid=' . $bezirk['id'] . '&sub=' . $sub . '&tid=' . (int)$_GET['tid'] . '&pid=' . $post_id . '#post' . $post_id,
								'theme' => $theme,
								'post' => $body,
								'poster' => $this->session->user('name')
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

	public function signout(): array
	{
		$data = $_GET;
		if ($this->session->mayBezirk($data['bid'])) {
			$this->foodsaverGateway->deleteFromRegion($data['bid'], $this->session->id());

			return array('status' => 1);
		}

		return array('status' => 0);
	}
}
