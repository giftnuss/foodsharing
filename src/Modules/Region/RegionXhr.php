<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;

class RegionXhr extends Control
{
	private $responses;
	private $forumGateway;
	private $regionHelper;
	private $twig;

	public function __construct(Db $model, ForumGateway $forumGateway, RegionHelper $regionHelper, \Twig\Environment $twig)
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
			|| $this->session->isOrgaTeam();
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
		if (isset($_GET['bid']) && isset($_GET['tid']) && isset($_GET['pid']) && $this->session->may() && isset($_POST['msg']) && $_POST['msg'] != '') {
			$sub = 'forum';
			if ($_GET['sub'] != 'forum') {
				$sub = 'botforum';
			}

			$body = strip_tags($_POST['msg']);
			$body = nl2br($body);
			$body = $this->func->autolink($body);

			if ($bezirk = $this->model->getValues(array('id', 'name'), 'bezirk', $_GET['bid'])) {
				if ($post_id = $this->forumGateway->addPost($this->session->id(), $_GET['tid'], $body)) {
					if ($follower = $this->forumGateway->getThreadFollower($this->session->id(), $_GET['tid'])) {
						$theme = $this->model->getVal('name', 'theme', $_GET['tid']);

						foreach ($follower as $f) {
							$this->func->tplMail(19, $f['email'], array(
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
