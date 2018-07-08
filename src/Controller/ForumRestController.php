<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Services\ForumService;
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

	public function __construct(Session $session, ForumGateway $forumGateway, ForumPermissions $forumPermissions, ForumService $forumService)
	{
		$this->session = $session;
		$this->forumGateway = $forumGateway;
		$this->forumPermissions = $forumPermissions;
		$this->forumService = $forumService;
	}

	private function normalizeThread($thread)
	{
		$res = [
			'id' => $thread['id'],
			'name' => $thread['name'],
			'createdAt' => $thread['time'],
			'sticky' => $thread['sticky'],
			'active' => $thread['active'] ?? 1,
			'lastPost' => [
				'id' => $thread['last_post_id'],
			],
			'creator' => [
				'id' => $thread['creator_id'],
			]
		];
		if (isset($thread['post_time'])) {
			$res['lastPost']['createdAt'] = $thread['post_time'];
			$res['lastPost']['body'] = $thread['post_body'];
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
			'body' => $post['body'],
			'createdAt' => $post['time'],
			'author' => [
				'id' => $post['author_id'],
				'name' => $post['author_name'],
				'avatar' => '/images/130_q_' . $post['author_photo'],
				'sleep_status' => $post['author_sleep_status']
			],
			'reactions' => $reactions[$post['id']] ?? []
		];
	}

	/**
	 * @param $forumId integer which forum to return threads for (maps to regions/groups)
	 * @param $subForumId integer each region/group as another namespace to separate different forums with the same base id (region/group id, here: forumId).
	 * So with any forumId, there is (currently) 2, possibly infinite, actual forums (list of threads)
	 * @Rest\Get("forum/{forumId}/{subForumId}", requirements={"forumId" = "\d+", "subForumId" = "\d"})
	 */
	public function listThreadsAction(int $forumId, int $subForumId)
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $subForumId)) {
			throw new HttpException(403);
		}

		$threads = $this->forumGateway->listThreads($forumId, $subForumId, 0, 0, 1000);
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
		$thread['posts'] = array_map(function ($post) use ($reactions) { return $this->normalizePost($post, $reactions); }, $posts);

		$view = $this->view([
			'data' => $thread
		], 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Patch("forum/thread/{threadId}", requirements={"threadId" = "\d+"})
	 * @Rest\RequestParam(name="sticky", nullable=true, default=null)
	 * @Rest\RequestParam(name="active", nullable=true, default=null)
	 */
	public function patchThreadAction($threadId, ParamFetcher $paramFetcher)
	{
		if (!$this->forumPermissions->mayAdministrateThread($threadId)) {
			throw new HttpException(403);
		}
		$sticky = $paramFetcher->get('sticky');
		$active = $paramFetcher->get('active');
		if (!is_null($sticky)) {
			if ($sticky === true) {
				$this->forumGateway->stickThread($threadId);
			} else {
				$this->forumGateway->unstickThread($threadId);
			}
		}
		if ($active === true) {
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

		return $this->handleView($this->view());
	}

	/**
	 * @Rest\Delete("forum/thread/{threadId}/follow", requirements={"threadId" = "\d+"})
	 */
	public function unfollowThreadAction($threadId)
	{
		$this->forumGateway->unfollowThread($this->session->id(), $threadId);

		return $this->handleView($this->view());
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

		return $this->handleView($this->view());
	}

	/**
	 * @Rest\Delete("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function deleteReactionAction($postId, $emoji)
	{
		$threadId = $this->forumGateway->getThreadForPost($postId);
		$this->forumService->removeReaction($this->session->id(), $threadId, $postId, $emoji);

		return $this->handleView($this->view());
	}
}
