<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Services\ForumService;
use Foodsharing\Services\SanitizerService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumRestController extends AbstractFOSRestController
{
	private $session;
	private $regionGateway;
	private $forumGateway;
	private $forumFollowerGateway;
	private $forumPermissions;
	private $forumService;
	private $sanitizerService;

	public function __construct(
		Session $session,
		RegionGateway $regionGateway,
		ForumGateway $forumGateway,
		ForumFollowerGateway $forumFollowerGateway,
		ForumPermissions $forumPermissions,
		ForumService $forumService,
		SanitizerService $sanitizerService
	) {
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->forumPermissions = $forumPermissions;
		$this->forumService = $forumService;
		$this->sanitizerService = $sanitizerService;
	}

	private function normalizeThread($thread): array
	{
		$normalizedThread = [
			'id' => $thread['id'],
			'regionId' => $thread['regionId'],
			'regionSubId' => $thread['regionSubId'],
			'title' => $thread['title'],
			'createdAt' => str_replace(' ', 'T', $thread['time']),
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
			$normalizedThread['lastPost']['createdAt'] = str_replace(' ', 'T', $thread['post_time']);
			$normalizedThread['lastPost']['body'] = $this->sanitizerService->markdownToHtml($thread['post_body']);
			$normalizedThread['lastPost']['author'] = RestNormalization::normalizeFoodsaver($thread, 'foodsaver_');
		}
		if (isset($thread['creator_name'])) {
			$normalizedThread['creator'] = RestNormalization::normalizeFoodsaver($thread, 'creator_');
		}

		return $normalizedThread;
	}

	private function normalizePost($post): array
	{
		return [
			'id' => $post['id'],
			'body' => $this->sanitizerService->markdownToHtml($post['body']),
			'createdAt' => str_replace(' ', 'T', $post['time']),
			'author' => RestNormalization::normalizeFoodsaver($post, 'author_'),
			'reactions' => $post['reactions'] ?: new \ArrayObject(),
			'mayDelete' => $this->forumPermissions->mayDeletePost($post)
		];
	}

	/**
	 * @param int $forumId which forum to return threads for (maps to regions/groups)
	 * @param int $forumSubId each region/group as another namespace to separate different forums with the same base id (region/group id, here: forumId).
	 * So with any forumId, there is (currently) 2, possibly infinite, actual forums (list of threads)
	 * @Rest\Get("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 */
	public function listThreadsAction(int $forumId, int $forumSubId): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $forumSubId)) {
			throw new HttpException(403);
		}

		$threads = $this->getNormalizedThreads($forumId, $forumSubId);

		$view = $this->view([
			'data' => $threads
		], 200);

		return $this->handleView($view);
	}

	private function getNormalizedThreads(int $forumId, int $forumSubId)
	{
		$threads = $this->forumGateway->listThreads($forumId, $forumSubId, 0, 0, 1000);
		$threads = array_map(function ($thread) {
			return $this->normalizeThread($thread);
		}, $threads);

		return $threads;
	}

	/**
	 * @Rest\Get("forum/thread/{threadId}", requirements={"threadId" = "\d+"})
	 */
	public function getThreadAction($threadId)
	{
		$thread = $this->forumGateway->getThread($threadId);

		if (!$thread) {
			throw new HttpException(404);
		}

		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		$thread = $this->normalizeThread($thread);
		$posts = $this->forumGateway->listPosts($threadId);

		$thread['isFollowing'] = $this->forumFollowerGateway->isFollowing($this->session->id(), $threadId);
		$thread['mayModerate'] = $this->forumPermissions->mayModerate($threadId);
		$thread['posts'] = array_map(function ($post) {
			return $this->normalizePost($post);
		}, $posts);

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
		$regionDetails = $this->regionGateway->getRegionDetails($forumId);
		$postActiveWithoutModeration = ($this->session->user('verified') && !$regionDetails['moderated']) || $this->session->isAmbassadorForRegion([$forumId]);

		$threadId = $this->forumService->createThread($this->session->id(), $title, $body, $regionDetails, $forumSubId, $postActiveWithoutModeration, true);

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
		if (!is_null($isSticky)) {
			if ($isSticky === true) {
				$this->forumGateway->stickThread($threadId);
			} else {
				$this->forumGateway->unstickThread($threadId);
			}
		}
		$isActive = $paramFetcher->get('isActive');
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

		$this->forumFollowerGateway->followThread($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Delete("forum/thread/{threadId}/follow", requirements={"threadId" = "\d+"})
	 */
	public function unfollowThreadAction($threadId)
	{
		$this->forumFollowerGateway->unfollowThread($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Delete("forum/post/{postId}", requirements={"postId" = "\d+"})
	 */
	public function deletePostAction($postId)
	{
		$post = $this->forumGateway->getPost($postId);
		if (!$post) {
			throw new HttpException(404);
		}
		if (!$this->forumPermissions->mayDeletePost($post)) {
			throw new HttpException(403);
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
			throw new HttpException(403);
		}

		$this->forumService->addReaction($this->session->id(), $postId, $emoji);

		return $this->handleView($this->view([]));
	}

	/**
	 * @Rest\Delete("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function deleteReactionAction($postId, $emoji)
	{
		$this->forumService->removeReaction($this->session->id(), $postId, $emoji);

		return $this->handleView($this->view([]));
	}
}
