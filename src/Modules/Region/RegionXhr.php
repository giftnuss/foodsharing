<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\ForumPermissions;

final class RegionXhr extends Control
{
	private $responses;
	private $regionGateway;
	private $foodsaverGateway;
	private $forumGateway;
	private $forumFollowerGateway;
	private $forumPermissions;
	private $regionHelper;
	private $twig;
	private $regionGateway;

	public function __construct(
		RegionGateway $regionGateway,
		ForumGateway $forumGateway,
		ForumPermissions $forumPermissions,
		RegionHelper $regionHelper,
		\Twig\Environment $twig,
		FoodsaverGateway $foodsaverGateway,
		ForumFollowerGateway $forumFollowerGateway,
		RegionGateway $regionGateway
	) {
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->forumPermissions = $forumPermissions;
		$this->regionHelper = $regionHelper;
		$this->twig = $twig;
		$this->regionGateway = $regionGateway;
		$this->responses = new XhrResponses();

		parent::__construct();
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
				&& $bezirk = $this->regionGateway->getRegion($_GET['bid'])
			) {
				if ($post_id = $this->forumGateway->addPost($this->session->id(), $_GET['tid'], $body)) {
					if ($follower = $this->forumFollowerGateway->getThreadFollower($this->session->id(), $_GET['tid'])) {
						$theme = $this->forumGateway->getThreadInfo($_GET['tid']);

						foreach ($follower as $f) {
							$this->emailHelper->tplMail('forum/answer', $f['email'], array(
								'anrede' => $this->translationHelper->genderWord($f['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
								'name' => $f['name'],
								'link' => BASE_URL . '/?page=bezirk&bid=' . $bezirk['id'] . '&sub=' . $sub . '&tid=' . (int)$_GET['tid'] . '&pid=' . $post_id . '#post' . $post_id,
								'thread' => $theme,
								'bezirk' => $bezirk['name'],
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
			'message' => $this->translationHelper->s('post_could_not_saved')
		));
		exit();
	}

	public function signout(): array
	{
		$data = $_GET;
		$groupId = (int)$data['bid'];
		unset($data);

		if ($this->session->mayBezirk($groupId)) {
			$wasAdminForThisGroup = $this->session->isAdminFor($groupId);
			$this->foodsaverGateway->deleteFromRegion($groupId, $this->session->id());

			if ($wasAdminForThisGroup && count($this->foodsaverGateway->getBotschafter($groupId)) < 1) {
				$recipient = ['welcome@foodsharing.network', 'ags.bezirke@foodsharing.network'];
				$groupName = $this->regionGateway->getRegionName($groupId);
				$idStructure = $this->regionGateway->listRegionsIncludingParents([$groupId]);

				$idStructureList = [];
				foreach ($idStructure as $id) {
					$idStructureList[] = '' . $id . '  -  ' . $this->regionGateway->getRegionName($id) . '';
				}
				$idStructureList = implode('<br>', $idStructureList);

				$messageText = $this->translationHelper->sv('message_text_to_group_admin_workgroup', ['groupId' => $groupId, '$idStructureList' => $idStructureList, 'groupName' => $groupName]);

				$this->emailHelper->tplMail('general/workgroup_contact', $recipient, [
					'gruppenname' => $groupName,
					'message' => $messageText,
					'username' => $this->session->user('name'),
					'userprofile' => BASE_URL . '/profile/' . $this->session->id()
				], $this->session->user('email'));
			}

			return ['status' => 1];
		}

		return ['status' => 0];
	}
}
