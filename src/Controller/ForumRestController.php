<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Services\ForumService;
use Foodsharing\Services\OutputSanitizerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumRestController extends FOSRestController
{
	private $session;
	private $forumGateway;
	private $forumPermissions;
	private $forumService;
	private $outputSanitizerService;

	public function __construct(Session $session, ForumGateway $forumGateway, ForumPermissions $forumPermissions, ForumService $forumService, OutputSanitizerService $outputSanitizerService)
	{
		$this->session = $session;
		$this->forumGateway = $forumGateway;
		$this->forumPermissions = $forumPermissions;
		$this->forumService = $forumService;
		$this->outputSanitizerService = $outputSanitizerService;
	}

	private function normalizeThread($thread)
	{
		$res = [
			'id' => $thread['id'],
			'regionId' => $thread['regionId'],
			'regionSubId' => $thread['regionSubId'],
			'title' => $thread['title'],
			'createdAt' => $thread['time'],
			'isSticky' => (bool)$thread['sticky'],
			'isActive' => (bool)$thread['active'] ?? true,
			'lastPost' => [
				'id' => $thread['last_post_id'],
			],
			'creator' => [
				'id' => $thread['creator_id'],
			]
		];
		if (isset($thread['post_time'])) {
			$res['lastPost']['createdAt'] = $thread['post_time'];
			$res['lastPost']['body'] = $this->outputSanitizerService->sanitizeForHtml($thread['post_body']);
			$res['lastPost']['author'] = [
				'id' => $thread['foodsaver_id'],
				'name' => $thread['foodsaver_name'],
				'avatar' => '/images/130_q_' . $thread['foodsaver_photo'],
				'sleepStatus' => $thread['sleep_status']
			];
		}
		if (isset($thread['creator_name'])) {
			$res['creator'] = [
				'id' => $thread['creator_id'],
				'name' => $thread['creator_name'],
				'avatar' => '/images/130_q_' . $thread['creator_photo'],
				'sleepStatus' => $thread['creator_sleep_status']
			];
		}

		return $res;
	}

	private function normalizePost($post, $reactions)
	{
		return [
			'id' => $post['id'],
			'body' => $this->outputSanitizerService->sanitizeForHtml($post['body']),
			'createdAt' => $post['time'],
			'author' => [
				'id' => $post['author_id'],
				'name' => $post['author_name'],
				'avatar' => '/images/130_q_' . $post['author_photo'],
				'sleepStatus' => $post['author_sleep_status']
			],
			'reactions' => $reactions[$post['id']] ?? new \ArrayObject(),
			'mayDelete' => $this->forumPermissions->mayDeletePost($post)
		];
	}

	/**
	 * @param $forumId integer which forum to return threads for (maps to regions/groups)
	 * @param $forumSubId integer each region/group as another namespace to separate different forums with the same base id (region/group id, here: forumId).
	 * So with any forumId, there is (currently) 2, possibly infinite, actual forums (list of threads)
	 * @Rest\Get("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 */
	public function listThreadsAction(int $forumId, int $forumSubId)
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $forumSubId)) {
			throw new HttpException(403);
		}

		$threads = $this->forumGateway->listThreads($forumId, $forumSubId, 0, 0, 1000);
		$threads = array_map(function ($thread) { return $this->normalizeThread($thread); }, $threads);

		$view = $this->view([
			'data' => $threads
		], 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Get("forum/thread/{threadId}", requirements={"threadId" = "\d+"})
	 */
	public function getThreadAction($threadId)
	{
		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		$thread = $this->forumGateway->getThread($threadId);
		$posts = $this->forumGateway->listPosts($threadId);
		$reactions = $this->forumService->getReactionsForThread($threadId);

		$thread = $this->normalizeThread($thread);
		$thread['isFollowing'] = $this->forumGateway->isFollowing($this->session->id(), $threadId);
		$thread['mayModerate'] = $this->forumPermissions->mayModerate($threadId);
		$thread['posts'] = array_map(function ($post) use ($reactions) { return $this->normalizePost($post, $reactions); }, $posts);
		
		$view = $this->view([
			'data' => $thread
		], 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Post("forum/thread/{threadId}/posts", requirements={"threadId" = "\d+"})
	 * @Rest\RequestParam(name="body")
	 */
	public function createPostAction($threadId, ParamFetcher $paramFetcher)
	{
		if (!$this->forumPermissions->mayPostToThread($threadId)) {
			throw new HttpException(403);
		}

		$body = $paramFetcher->get('body');
		$this->forumService->addPostToThread($this->session->id(), $threadId, $body);

		return $this->handleView($this->view());
	}

	/**
	 * @Rest\Post("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 * @Rest\RequestParam(name="title")
	 * @Rest\RequestParam(name="body")
	 */
	public function createThreadAction($forumId, $forumSubId, ParamFetcher $paramFetcher)
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $forumSubId)) {
			throw new HttpException(403);
		}

		$body = $paramFetcher->get('body');
		$title = $paramFetcher->get('title');

		$threadId = $this->forumService->createThread($this->session->id(), $title, $body, $forumId, $forumSubId);

		return $this->getThreadAction($threadId);
	}

	/**
	 * @Rest\Patch("forum/thread/{threadId}", requirements={"threadId" = "\d+"})
	 * @Rest\RequestParam(name="isSticky", nullable=true, default=null)
	 * @Rest\RequestParam(name="isActive", nullable=true, default=null)
	 */
	public function patchThreadAction($threadId, ParamFetcher $paramFetcher)
	{
		if (!$this->forumPermissions->mayModerate($threadId)) {
			throw new HttpException(403);
		}
		$isSticky = $paramFetcher->get('isSticky');
		$isActive = $paramFetcher->get('isActive');
		if (!is_null($isSticky)) {
			if ($isSticky === true) {
				$this->forumGateway->stickThread($threadId);
			} else {
				$this->forumGateway->unstickThread($threadId);
			}
		}
		if ($isActive === true) {
			$this->forumService->activateThread($threadId);
		}

		return $this->getThreadAction($threadId);
	}

	/**
	 * @Rest\Post("forum/thread/{threadId}/follow", requirements={"threadId" = "\d+"})
	 */
	public function followThreadAction($threadId)
	{
		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		$this->forumGateway->followThread($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Delete("forum/thread/{threadId}/follow", requirements={"threadId" = "\d+"})
	 */
	public function unfollowThreadAction($threadId)
	{
		$this->forumGateway->unfollowThread($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Delete("forum/post/{postId}", requirements={"postId" = "\d+"})
	 */
	public function deletePostAction($postId)
	{
		$post = $this->forumGateway->getPost($postId);
		if (!$this->forumPermissions->mayDeletePost($post)) {
			return new HttpException(403);
		}
		$this->forumGateway->deletePost($postId);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Post("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function addReactionAction($postId, $emoji)
	{
		$threadId = $this->forumGateway->getThreadForPost($postId);
		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			return new HttpException(403);
		}
		$this->forumService->addReaction($this->session->id(), $threadId, $postId, $emoji);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Delete("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function deleteReactionAction($postId, $emoji)
	{
		$threadId = $this->forumGateway->getThreadForPost($postId);
		$this->forumService->removeReaction($this->session->id(), $threadId, $postId, $emoji);

		return $this->handleView($this->view([]));
	}
}
