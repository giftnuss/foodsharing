<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\FlashMessageHelper;
use Foodsharing\Utility\Sanitizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForumTransactions
{
	private BellGateway $bellGateway;
	private FoodsaverGateway $foodsaverGateway;
	private ForumGateway $forumGateway;
	private ForumFollowerGateway $forumFollowerGateway;
	private Session $session;
	private RegionGateway $regionGateway;
	private Sanitizer $sanitizerService;
	private EmailHelper $emailHelper;
	private FlashMessageHelper $flashMessageHelper;
	private TranslatorInterface $translator;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		ForumFollowerGateway $forumFollowerGateway,
		Session $session,
		RegionGateway $regionGateway,
		Sanitizer $sanitizerService,
		EmailHelper $emailHelper,
		FlashMessageHelper $flashMessageHelper,
		TranslatorInterface $translator
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->flashMessageHelper = $flashMessageHelper;
		$this->translator = $translator;
	}

	public function url($regionId, $ambassadorForum, $threadId = null, $postId = null): string
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
				$this->flashMessageHelper->info($this->translator->trans('forum.thread.no_mail'));
			}
		}

		return $threadId;
	}

	public function activateThread(int $threadId): void
	{
		$this->forumGateway->activateThread($threadId);
	}

	public function notificationMail($recipients, $tpl, $data): void
	{
		foreach ($recipients as $recipient) {
			$this->emailHelper->tplMail(
				$tpl,
				$recipient['email'],
				array_merge($data, [
					'anrede' => $this->translator->trans('salutation.' . $recipient['geschlecht']),
					'name' => $recipient['name'],
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

	private function notifyAdminsModeratedThread($region, $threadId, $rawPostBody): void
	{
		$thread = $this->forumGateway->getThread($threadId);
		$posterName = $this->foodsaverGateway->getFoodsaverName($thread['creator_id']);
		$moderationGroup = $this->regionGateway->getRegionModerationGroupId($region['id']);
		if (empty($moderationGroup)) {
			$moderators = $this->foodsaverGateway->getAdminsOrAmbassadors($region['id']);
		} else {
			$moderators = $this->foodsaverGateway->getAdminsOrAmbassadors($moderationGroup);
		}
		if ($moderators) {
			$data = [
				'link' => BASE_URL . $this->url($region['id'], false, $threadId),
				'thread' => $thread['title'],
				'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
				'poster' => $posterName,
				'bezirk' => $region['name'],
			];

			$this->notificationMail($moderators, 'forum/activation', $data);
		}
	}

	private function notifyMembersOfForumAboutNewThreadViaMail(array $regionData, int $threadId, bool $isAmbassadorForum): void
	{
		$regionType = $this->regionGateway->getType($regionData['id']);
		if (!$isAmbassadorForum && in_array($regionType, [Type::COUNTRY, Type::FEDERAL_STATE])) {
			$this->flashMessageHelper->info($this->translator->trans('forum.thread.too_big_to_mail'));

			return;
		} else {
			$this->flashMessageHelper->info($this->translator->trans('forum.thread.with_mail'));
		}

		$thread = $this->forumGateway->getThread($threadId);
		$body = $this->forumGateway->getPost($thread['last_post_id'])['body'];

		$posterName = $this->foodsaverGateway->getFoodsaverName($thread['creator_id']);

		if ($isAmbassadorForum) {
			$recipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionData['id']);
		} else {
			$recipients = $this->foodsaverGateway->listActiveWithFullNameByRegion($regionData['id']);
		}

		$data = [
			'bezirk' => $regionData['name'],
			'poster' => $posterName,
			'thread' => $thread['title'],
			'link' => BASE_URL . $this->url($regionData['id'], $isAmbassadorForum, $threadId),
			'post' => $this->sanitizerService->markdownToHtml($body),
			];
		$this->notificationMail($recipients,
			$isAmbassadorForum ? 'forum/new_region_ambassador_message' : 'forum/new_message', $data);
	}

	public function addReaction($fsId, $postId, $key): void
	{
		if (!$fsId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->forumGateway->addReaction($postId, $fsId, $key);
	}

	public function removeReaction($fsId, $postId, $key): void
	{
		if (!$fsId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->forumGateway->removeReaction($postId, $fsId, $key);
	}
}
