<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Services\ForumService;
use Foodsharing\Services\SanitizerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumRestController extends AbstractFOSRestController
{
	private $session;
	private $forumGateway;
	private $forumPermissions;
	private $forumService;
	private $sanitizerService;

	public function __construct(
		Session $session,
		ForumGateway $forumGateway,
		ForumPermissions $forumPermissions,
		ForumService $forumService,
		SanitizerService $sanitizerService
	) {
		$this->session = $session;
		$this->forumGateway = $forumGateway;
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
	 * @param $forumId integer which forum to return threads for (maps to regions/groups)
	 * @param $forumSubId integer each region/group as another namespace to separate different forums with the same base id (region/group id, here: forumId).
	 * So with any forumId, there is (currently) 2, possibly infinite, actual forums (list of threads)
	 * @Rest\Get("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listThreadsAction(int $forumId, int $forumSubId): \Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotAllowedToAccessForum($forumId, $forumSubId);

		$threads = $this->getNormalizedThreads($forumId, $forumSubId);

		$view = $this->view([
			'data' => $threads
		], 200);

		return $this->handleView($view);
	}

	private function throwExceptionIfNotAllowedToAccessForum(int $forumId, int $forumSubId)
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $forumSubId)) {
			throw new HttpException(403);
		}
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
		$thread = $this->getValidatedThreadOrThrowException($threadId);

		$thread = $this->normalizeThread($thread);
		$thread = $this->addAdditionalInformationToThread($thread, $threadId);

		$view = $this->view([
			'data' => $thread
		], 200);

		return $this->handleView($view);
	}

	private function getValidatedThreadOrThrowException($threadId)
	{
		$thread = $this->forumGateway->getThread($threadId);

		if (!$thread) {
			throw new HttpException(404);
		}

		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		return $thread;
	}

	private function addAdditionalInformationToThread($thread, $threadId)
	{
		$posts = $this->forumGateway->listPosts($threadId);

		$thread['isFollowing'] = $this->forumGateway->isFollowing($this->session->id(), $threadId);
		$thread['mayModerate'] = $this->forumPermissions->mayModerate($threadId);
		$thread['posts'] = array_map(function ($post) {
			return $this->normalizePost($post);
		}, $posts);

		return $thread;
	}

	/**
	 * @Rest\Post("forum/thread/{threadId}/posts", requirements={"threadId" = "\d+"})
	 * @Rest\RequestParam(name="body")
	 */
	public function createPostAction($threadId, ParamFetcher $paramFetcher)
	{
		$this->throwExceptionIfNotAllowedToPostToThread($threadId);

		$body = $paramFetcher->get('body');
		$this->forumService->addPostToThread($this->session->id(), $threadId, $body);

		return $this->handleView($this->view());
	}

	private function throwExceptionIfNotAllowedToPostToThread($threadId)
	{
		if (!$this->forumPermissions->mayPostToThread($threadId)) {
			throw new HttpException(403);
		}
	}

	/**
	 * @Rest\Post("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 * @Rest\RequestParam(name="title")
	 * @Rest\RequestParam(name="body")
	 */
	public function createThreadAction($forumId, $forumSubId, ParamFetcher $paramFetcher)
	{
		$this->throwExceptionIfNotAllowedToAccessForum($forumId, $forumSubId);

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
		$this->throwExceptionIfNotAllowedToModerateThread($threadId);

		$isSticky = $paramFetcher->get('isSticky');
		if (!is_null($isSticky)) {
			$this->stickOrUnstickThread($threadId, $isSticky);
		}
		$isActive = $paramFetcher->get('isActive');
		if ($isActive === true) {
			$this->forumService->activateThread($threadId);
		}

		return $this->getThreadAction($threadId);
	}

	private function throwExceptionIfNotAllowedToModerateThread($threadId)
	{
		if (!$this->forumPermissions->mayModerate($threadId)) {
			throw new HttpException(403);
		}
	}

	private function stickOrUnstickThread($threadId, $isSticky)
	{
		if ($isSticky === true) {
			$this->forumGateway->stickThread($threadId);
		} else {
			$this->forumGateway->unstickThread($threadId);
		}
	}

	/**
	 * @Rest\Post("forum/thread/{threadId}/follow", requirements={"threadId" = "\d+"})
	 */
	public function followThreadAction($threadId)
	{
		$this->throwExceptionIfNotAllowedToAccessThread($threadId);

		$this->forumGateway->followThread($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	private function throwExceptionIfNotAllowedToAccessThread($threadId)
	{
		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}
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
		$this->validatePostOrThrowException($postId);

		$this->forumGateway->deletePost($postId);

		return $this->handleView($this->view([]));
	}

	private function validatePostOrThrowException($postId)
	{
		$post = $this->forumGateway->getPost($postId);
		if (!$post) {
			throw new HttpException(404);
		}
		if (!$this->forumPermissions->mayDeletePost($post)) {
			throw new HttpException(403);
		}
	}

	/**
	 * @Rest\Post("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function addReactionAction($postId, $emoji)
	{
		$threadId = $this->forumGateway->getThreadForPost($postId);

		$this->throwExceptionIfNotAllowedToAccessThread($threadId);

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
