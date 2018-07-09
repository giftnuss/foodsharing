<?php

namespace Foodsharing\Services;

use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\EmailTemplateAdmin\EmailTemplateGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Reaction\ReactionGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;

class ForumService
{
	private $forumGateway;
	private $reactionGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $bellGateway;
	private $emailTemplateGateway;
	/* @var Model */
	private $model;
	private $func;
	private $session;
	private $outputSanitizerService;

	public function __construct(
		BellGateway $bellGateway,
		EmailTemplateGateway $emailTemplateGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		Func $func,
		Session $session,
		Model $model,
		RegionGateway $regionGateway,
		ReactionGateway $reactionGateway,
		OutputSanitizerService $outputSanitizerService
	) {
		$this->bellGateway = $bellGateway;
		$this->emailTemplateGateway = $emailTemplateGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumGateway = $forumGateway;
		$this->func = $func;
		$this->session = $session;
		$this->model = $model;
		$this->regionGateway = $regionGateway;
		$this->reactionGateway = $reactionGateway;
		$this->outputSanitizerService = $outputSanitizerService;
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
			'fa fa-comments',
			['href' => $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId)],
			['user' => $this->session->user('name'), 'forum' => $regionName],
			'forum-post-' . $postId
		);
	}

	public function addPostToThread($fsId, $threadId, $body)
	{
		$rawBody = $body;
		/* TODO: Implement proper sanitation that happens on output, not input */
		$body = nl2br(strip_tags($body));
		$body = $this->func->autolink($body);
		$pid = $this->forumGateway->addPost($fsId, $threadId, $body);
		$this->notifyFollowersNewPost($threadId, $rawBody, $fsId, $pid);
		$this->notifyParticipantsViaBell($threadId, $fsId, $pid);

		return $pid;
	}

	public function createThread($fsId, $title, $body, $region, $ambassadorForum, $moderated)
	{
		$body = nl2br(strip_tags($body));
		$body = $this->func->autolink($body);
		/* TODO: Implement proper sanitation that happens on output, not input */
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
			$this->func->tplMail(
				$tpl,
				$recipient['email'],
				array_merge($data,
					[
						'anrede' => $this->func->genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'name' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($recipient['name'])
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
				'theme' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($info['title']),
				'post' => $this->outputSanitizerService->sanitizeForHtml($rawPostBody),
				'poster' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($poster)
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
				'thread' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($theme['name']),
				'poster' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($poster),
				'bezirk' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($region['name']),
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
			'bezirk' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($region['name']),
			'poster' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($poster),
			'thread' => $this->outputSanitizerService->sanitizeForHtmlNoMarkup($theme['name']),
			'link' => BASE_URL . $this->url($region['id'], $ambassadorForum, $threadId),
			'post' => $this->outputSanitizerService->sanitizeForHtml($body),
			];
		$this->notificationMail($foodsaver, $ambassadorForum ? 13 : 12, $data);
	}

	private function getReactionTarget($threadId, $postId = null)
	{
		$target = 'forum-' . $threadId . '-';
		if ($postId) {
			$target .= $postId;
		}

		return $target;
	}

	public function addReaction($fsId, $threadId, $postId, $key)
	{
		if (!$fsId || !$threadId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->reactionGateway->addReaction($this->getReactionTarget($threadId, $postId), $fsId, $key);
	}

	public function removeReaction($fsId, $threadId, $postId, $key)
	{
		if (!$fsId || !$threadId || !$postId || !$key) {
			throw new \InvalidArgumentException();
		}
		$this->reactionGateway->removeReaction($this->getReactionTarget($threadId, $postId), $fsId, $key);
	}

	public function getReactionsForThread($threadId)
	{
		$target = $this->getReactionTarget($threadId);
		$res = $this->reactionGateway->getReactions($target, true);
		$reactions = [];
		foreach ($res as $r) {
			$id = explode($target, $r['target']);
			if (count($id) !== 2) {
				continue;
			} else {
				$postId = $id[1];
			}
			if (!isset($reactions[$postId])) {
				$reactions[$postId] = [];
			}
			if (!isset($reactions[$postId][$r['key']])) {
				$reactions[$postId][$r['key']] = [];
			}
			$reactions[$postId][$r['key']][] = [
				'id' => $r['foodsaver_id'],
				'name' => $r['foodsaver_name']
			];
		}

		return $reactions;
	}
}
