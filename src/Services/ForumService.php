<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;

class ForumService
{
	private $forumGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $bellGateway;
	/* @var Db */
	private $model;
	private $func;
	private $session;
	private $sanitizerService;
	private $emailHelper;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		Func $func,
		Session $session,
		Db $model,
		RegionGateway $regionGateway,
		SanitizerService $sanitizerService,
		EmailHelper $emailHelper
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->func = $func;
		$this->session = $session;
		$this->model = $model;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
	}

	public function url($regionId, $ambassadorForum, $threadId = null, $postId = null)
	{
		$url = '/?page=bezirk&bid=' . $regionId . '&sub=' . ($ambassadorForum ? 'botforum' : 'forum');
		if ($threadId) {
			$url .= '&tid=' . $threadId;
		}
		if ($postId) {
			$url .= '&pid=' . $postId . '#post' . $postId;
		}

		return $url;
	}

	public function notifyParticipantsViaBell($threadId, $authorId, $postId)
	{
		$posts = $this->forumGateway->listPosts($threadId);
		$info = $this->forumGateway->getThreadInfo($threadId);
		$regionName = $this->regionGateway->getBezirkName($info['region_id']);

		$getFsId = function ($post) {
			return $post['author_id'];
		};
		$removeAuthorFsId = function ($id) use ($authorId) {
			return $id != $authorId;
		};
		$fsIds = array_map($getFsId, $posts);
		$fsIds = array_filter($fsIds, $removeAuthorFsId);
		$fsIds = array_unique($fsIds);

		$this->bellGateway->addBell(
			$fsIds,
			'forum_answer_title',
			'forum_answer',
			'fas fa-comments',
			['href' => $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId)],
			['user' => $this->session->user('name'), 'forum' => $regionName],
			'forum-post-' . $postId
		);
	}

	public function addPostToThread($fsId, $threadId, $body)
	{
		$rawBody = $body;
		$pid = $this->forumGateway->addPost($fsId, $threadId, $body);
		$this->notifyFollowersNewPost($threadId, $rawBody, $fsId, $pid);
		$this->notifyParticipantsViaBell($threadId, $fsId, $pid);

		return $pid;
	}

	public function createThread($fsId, $title, $body, $region, $ambassadorForum, $moderated)
	{
		$threadId = $this->forumGateway->addThread($fsId, $region['id'], $title, $body, $ambassadorForum, !$moderated);
		if ($moderated) {
			$this->notifyAdminsModeratedThread($region, $threadId);
		} else {
			$this->notifyUsersNewThread($region, $threadId, $ambassadorForum);
		}

		return $threadId;
	}

	public function activateThread($threadId, $region = null, $ambassadorForum = false)
	{
		$this->forumGateway->activateThread($threadId);
		/* TODO: this needs proper first activation handling */
		if ($region) {
			$this->notifyUsersNewThread($region, $threadId, $ambassadorForum);
		}
	}

	public function notificationMail($recipients, $tpl, $data)
	{
		foreach ($recipients as $recipient) {
			$this->emailHelper->tplMail(
				$tpl,
				$recipient['email'],
				array_merge($data,
					[
						'anrede' => $this->func->genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'name' => $this->sanitizerService->plainToHtml($recipient['name'])
					])
			);
		}
	}

	public function notifyFollowersNewPost($threadId, $rawPostBody, $postFrom, $postId)
	{
		if ($follower = $this->forumGateway->getThreadFollower($this->session->id(), $threadId)) {
			$info = $this->forumGateway->getThreadInfo($threadId);
			$poster = $this->model->getVal('name', 'foodsaver', $this->session->id());
			$data = [
				'link' => BASE_URL . $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId),
				'theme' => $this->sanitizerService->plainToHtml($info['title']),
				'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
				'poster' => $this->sanitizerService->plainToHtml($poster)
			];
			$this->notificationMail($follower, 19, $data);
		}
	}

	private function notifyAdminsModeratedThread($region, $threadId)
	{
		$theme = $this->model->getValues(array('foodsaver_id', 'name'), 'theme', $threadId);
		$poster = $this->model->getVal('name', 'foodsaver', $theme['foodsaver_id']);

		if ($foodsaver = $this->foodsaverGateway->getBotschafter($region['id'])) {
			$data = [
				'link' => BASE_URL . $this->url($region['id'], false, $threadId),
				'thread' => $this->sanitizerService->plainToHtml($theme['name']),
				'poster' => $this->sanitizerService->plainToHtml($poster),
				'bezirk' => $this->sanitizerService->plainToHtml($region['name']),
			];

			$this->notificationMail($foodsaver, 20, $data);
		}
	}

	private function notifyUsersNewThread($region, $threadId, $ambassadorForum)
	{
		$theme = $this->model->getValues(array('foodsaver_id', 'name', 'last_post_id'), 'theme', $threadId);
		$body = $this->model->getVal('body', 'theme_post', $theme['last_post_id']);

		$poster = $this->model->getVal('name', 'foodsaver', $theme['foodsaver_id']);

		if ($ambassadorForum) {
			$foodsaver = $this->foodsaverGateway->getBotschafter($region['id']);
		} else {
			$foodsaver = $this->foodsaverGateway->listActiveWithFullNameByRegion($region['id']);
		}

		$data = [
			'bezirk' => $this->sanitizerService->plainToHtml($region['name']),
			'poster' => $this->sanitizerService->plainToHtml($poster),
			'thread' => $this->sanitizerService->plainToHtml($theme['name']),
			'link' => BASE_URL . $this->url($region['id'], $ambassadorForum, $threadId),
			'post' => $this->sanitizerService->markdownToHtml($body),
			];
		$this->notificationMail($foodsaver, $ambassadorForum ? 13 : 12, $data);
	}

	public function addReaction($fsId, $postId, $key)
	{
		if (!$fsId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->forumGateway->addReaction($postId, $fsId, $key);
	}

	public function removeReaction($fsId, $postId, $key)
	{
		if (!$fsId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->forumGateway->removeReaction($postId, $fsId, $key);
	}
}
