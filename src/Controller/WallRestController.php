<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\WallPost\WallPostGateway;
use Foodsharing\Permissions\WallPostPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WallRestController extends AbstractFOSRestController
{
	private WallPostGateway $wallPostGateway;
	private WallPostPermissions $wallPostPermissions;
	private Session $session;

	public function __construct(
		WallPostGateway $wallPostGateway,
		WallPostPermissions $wallPostPermissions,
		Session $session
	) {
		$this->wallPostGateway = $wallPostGateway;
		$this->wallPostPermissions = $wallPostPermissions;
		$this->session = $session;
	}

	private function normalizePost(array $post): array
	{
		return [
			'id' => $post['id'],
			'body' => $post['body'],
			'createdAt' => str_replace(' ', 'T', $post['time']),
			'pictures' => $post['gallery'] ?? null,
			'author' => [
				'id' => $post['foodsaver_id'],
				'name' => $post['name'],
				'avatar' => $post['photo'] ?? null
			]
		];
	}

	/**
	 * @Rest\Get("wall/{target}/{targetId}", requirements={"targetId" = "\d+"})
	 */
	public function getPostsAction(string $target, int $targetId): \Symfony\Component\HttpFoundation\Response
	{
		if ($this->session->id() === null || !$this->wallPostPermissions->mayReadWall($this->session->id(), $target, $targetId)) {
			throw new HttpException(403);
		}

		$posts = $this->getNormalizedPosts($target, $targetId);

		$sessionId = $this->session->id();

		$view = $this->view([
			'results' => $posts,
			'mayPost' => $this->wallPostPermissions->mayWriteWall($sessionId, $target, $targetId),
			'mayDelete' => $this->wallPostPermissions->mayDeleteFromWall($sessionId, $target, $targetId)
		], 200);

		return $this->handleView($view);
	}

	private function getNormalizedPosts(string $target, int $targetId): array
	{
		$posts = $this->wallPostGateway->getPosts($target, $targetId);

		return array_map(function ($value) {
			return $this->normalizePost($value);
		}, $posts);
	}

	/**
	 * @Rest\Post("wall/{target}/{targetId}", requirements={"targetId" = "\d+"})
	 * @Rest\RequestParam(name="body", nullable=false)
	 *
	 * @throws \Exception
	 */
	public function addPostAction(string $target, int $targetId, ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		if ($this->session->id() === null || !$this->wallPostPermissions->mayWriteWall($this->session->id(), $target, $targetId)) {
			throw new HttpException(403);
		}

		$body = $paramFetcher->get('body');
		$postId = $this->wallPostGateway->addPost($body, $this->session->id(), $target, $targetId);

		$view = $this->view(['post' => $this->normalizePost($this->wallPostGateway->getPost($postId))], 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Delete("wall/{target}/{targetId}/{id}", requirements={"targetId" = "\d+", "id" = "\d+"})
	 */
	public function delPostAction(string $target, int $targetId, int $id): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->wallPostGateway->isLinkedToTarget($id, $target, $targetId)) {
			throw new HttpException(403);
		}
		$sessionId = $this->session->id();
		if ($this->wallPostGateway->getFsByPost($id) != $sessionId
			&& !$this->wallPostPermissions->mayDeleteFromWall($sessionId, $target, $targetId)
		) {
			throw new HttpException(403);
		}

		$this->wallPostGateway->unlinkPost($id, $target);
		$this->wallPostGateway->deletePost($id);

		$view = $this->view([], 200);

		return $this->handleView($view);
	}
}
