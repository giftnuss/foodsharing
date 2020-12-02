<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Blog\BlogGateway;
use Foodsharing\Permissions\BlogPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BlogpostController extends AbstractFOSRestController
{
	private BlogGateway $blogGateway;
	private BlogPermissions $blogPermissions;
	private Session $session;

	public function __construct(
		BlogGateway $blogGateway,
		BlogPermissions $blogPermissions,
		Session $session
	) {
		$this->blogGateway = $blogGateway;
		$this->blogPermissions = $blogPermissions;
		$this->session = $session;
	}

	/**
	 * Removes one blogpost from the database.
	 *
	 * @OA\Parameter(name="blogId", in="path", @OA\Schema(type="integer"), description="which post to delete")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Response(response="403", description="Insufficient permissions to remove this blogpost.")
	 * @OA\Response(response="404", description="Blogpost not found.")
	 * @OA\Tag(name="blog")
	 *
	 * @Rest\Delete("blog/{blogId}", requirements={"blogId" = "\d+"})
	 */
	public function removeBlogpostAction(int $blogId): Response
	{
		$sessionId = $this->session->id();
		if (!$sessionId) {
			throw new UnauthorizedHttpException('Not logged in.');
		}

		$author = $this->blogGateway->getAuthorOfPost($blogId);
		if ($author === false) {
			throw new NotFoundHttpException('Blogpost not found.');
		}
		if (!$this->blogPermissions->mayDelete($blogId)) {
			throw new AccessDeniedHttpException();
		}

		$this->blogGateway->del_blog_entry($blogId);

		return $this->handleView($this->view([], 200));
	}
}
