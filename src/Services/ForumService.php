<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\FlashMessageHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;

class ForumService
{
	private $forumGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $bellGateway;
	private $forumFollowerGateway;
	private $session;
	private $sanitizerService;
	private $emailHelper;
	private $translationHelper;
	private $flashMessageHelper;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		ForumFollowerGateway $forumFollowerGateway,
		Session $session,
		RegionGateway $regionGateway,
		SanitizerService $sanitizerService,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper,
		FlashMessageHelper $flashMessageHelper
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
		$this->flashMessageHelper = $flashMessageHelper;
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

	public function notifyFollowersViaBell($threadId, $authorId, $postId): void
	{
		$subscribedFs = $this->forumFollowerGateway->getThreadBellFollower($threadId, $authorId);

		if (empty($subscribedFs)) {
			return;
		}

		$info = $this->forumGateway->getThreadInfo($threadId);
		$regionName = $this->regionGateway->getRegionName($info['region_id']);

		$bellData = Bell::create(
			'forum_reply_title',
			'forum_answer',
			'fas fa-comments',
			['href' => $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId)],
			[
				'user' => $this->session->user('name'),
				'forum' => $regionName,
				'title' => $info['title'],
			],
			'forum-post-' . $postId
		);
		$this->bellGateway->addBell(array_column($subscribedFs, 'id'), $bellData);
	}

	public function addPostToThread($fsId, $threadId, $body)
	{
		$rawBody = $body;
		$pid = $this->forumGateway->addPost($fsId, $threadId, $body);
		$this->notifyFollowersViaMail($threadId, $rawBody, $fsId, $pid);
		$this->notifyFollowersViaBell($threadId, $fsId, $pid);

		return $pid;
	}

	public function createThread($fsId, $title, $body, $region, $ambassadorForum, $isActive, $sendMail)
	{
		$threadId = $this->forumGateway->addThread($fsId, $region['id'], $title, $body, $isActive, $ambassadorForum);
		if (!$isActive) {
			$this->notifyAdminsModeratedThread($region, $threadId, $body);
		} else {
			if ($sendMail) {
				$this->notifyMembersOfForumAboutNewThreadViaMail($region, $threadId, $ambassadorForum);
			} else {
				$this->flashMessageHelper->info($this->translationHelper->s('new_thread_without_email'));
			}
		}

		return $threadId;
	}

	public function activateThread($threadId, $region = null, $ambassadorForum = false)
	{
		$this->forumGateway->activateThread($threadId);
		/* TODO: this needs proper first activation handling */
		if ($region) {
			$this->notifyMembersOfForumAboutNewThreadViaMail($region, $threadId, $ambassadorForum);
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
						'anrede' => $this->translationHelper->genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'name' => $recipient['name']
					])
			);
		}
	}

	public function notifyFollowersViaMail($threadId, $rawPostBody, $postFrom, $postId): void
	{
		if ($follower = $this->forumFollowerGateway->getThreadEmailFollower($postFrom, $threadId)) {
			$info = $this->forumGateway->getThreadInfo($threadId);
			$posterName = $this->foodsaverGateway->getFoodsaverName($this->session->id());
			$data = [
				'link' => BASE_URL . $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId),
				'thread' => $info['title'],
				'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
				'poster' => $posterName
			];
			$this->notificationMail($follower, 'forum/answer', $data);
		}
	}

	private function notifyAdminsModeratedThread($region, $threadId, $rawPostBody)
	{
		$theme = $this->forumGateway->getThread($threadId);
		$posterName = $this->foodsaverGateway->getFoodsaverName($theme['creator_id']);

		if ($foodsaver = $this->foodsaverGateway->getAdminsOrAmbassadors($region['id'])) {
			$data = [
				'link' => BASE_URL . $this->url($region['id'], false, $threadId),
				'thread' => $theme['title'],
				'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
				'poster' => $posterName,
				'bezirk' => $region['name'],
			];

			$this->notificationMail($foodsaver, 'forum/activation', $data);
		}
	}

	private function notifyMembersOfForumAboutNewThreadViaMail(array $regionData, int $threadId, bool $isAmbassadorForum)
	{
		$regionType = $this->regionGateway->getType($regionData['id']);
		if (!$isAmbassadorForum && in_array($regionType, [Type::COUNTRY, Type::FEDERAL_STATE])) {
			$this->flashMessageHelper->info($this->translationHelper->s('no_email_to_states'));

			return;
		} else {
			$this->flashMessageHelper->info($this->translationHelper->s('new_thread_with_email'));
		}

		$theme = $this->forumGateway->getThread($threadId);
		$body = $this->forumGateway->getPost($theme['last_post_id'])['body'];

		$posterName = $this->foodsaverGateway->getFoodsaverName($theme['creator_id']);

		if ($isAmbassadorForum) {
			$recipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionData['id']);
		} else {
			$recipients = $this->foodsaverGateway->listActiveWithFullNameByRegion($regionData['id']);
		}

		$data = [
			'bezirk' => $regionData['name'],
			'poster' => $posterName,
			'thread' => $theme['title'],
			'link' => BASE_URL . $this->url($regionData['id'], $isAmbassadorForum, $threadId),
			'post' => $this->sanitizerService->markdownToHtml($body),
			];
		$this->notificationMail($recipients,
			$isAmbassadorForum ? 'forum/new_region_ambassador_message' : 'forum/new_message', $data);
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
