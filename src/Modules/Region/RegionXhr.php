<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\ForumPermissions;

final class RegionXhr extends Control
{
	private $responses;
	private RegionGateway $regionGateway;
	private FoodsaverGateway $foodsaverGateway;
	private ForumGateway $forumGateway;
	private ForumFollowerGateway $forumFollowerGateway;
	private ForumPermissions $forumPermissions;
	private \Twig\Environment $twig;

	public function __construct(
		RegionGateway $regionGateway,
		ForumGateway $forumGateway,
		ForumPermissions $forumPermissions,
		\Twig\Environment $twig,
		FoodsaverGateway $foodsaverGateway,
		ForumFollowerGateway $forumFollowerGateway
	) {
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->forumPermissions = $forumPermissions;
		$this->twig = $twig;
		$this->responses = new XhrResponses();

		parent::__construct();
	}

	public function quickreply()
	{
		$data = json_decode(file_get_contents('php://input'), true);

		if (isset($_GET['bid'], $_GET['tid'], $_GET['pid'], $data['msg']) && $this->session->may(
			) && $data['msg'] != '') {
			$sub = 'forum';
			if ($_GET['sub'] != 'forum') {
				$sub = 'botforum';
			}

			$body = $data['msg'];

			if ($this->forumPermissions->mayPostToThread($_GET['tid'])
				&& $bezirk = $this->regionGateway->getRegion($_GET['bid'])
			) {
				if ($post_id = $this->forumGateway->addPost($this->session->id(), $_GET['tid'], $body)) {
					if ($follower = $this->forumFollowerGateway->getThreadEmailFollower($this->session->id(), $_GET['tid'])) {
						$theme = $this->forumGateway->getThreadInfo($_GET['tid']);

						foreach ($follower as $f) {
							$this->emailHelper->tplMail('forum/answer', $f['email'], [
								'anrede' => $this->translator->trans('salutation.' . $f['geschlecht']),
								'name' => $f['name'],
								'link' => BASE_URL . '/?page=bezirk&bid=' . $bezirk['id'] . '&sub=' . $sub . '&tid=' . (int)$_GET['tid'] . '&pid=' . $post_id . '#post' . $post_id,
								'thread' => $theme['title'],
								'bezirk' => $bezirk['name'],
								'post' => $body,
								'poster' => $this->session->user('name')
							]);
						}
					}

					echo json_encode([
						'status' => 1,
						'message' => $this->translator->trans('forum.quickreply.success'),
					]);
					exit();
				}
			}
		}

		echo json_encode([
			'status' => 0,
			'message' => $this->translator->trans('forum.quickreply.error'),
		]);
		exit();
	}
}
