<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BellRestController extends AbstractFOSRestController
{
	private BellGateway $bellGateway;
	private Session $session;

	public function __construct(
		BellGateway $bellGateway,
		Session $session
	) {
		$this->bellGateway = $bellGateway;
		$this->session = $session;
	}

	/**
	 * Returns all bells for the current user.
	 *
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="403", description="Insufficient permissions to list bells.")
	 * @OA\Tag(name="bells")
	 *
	 * @Rest\Get("bells")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="How many bells to return.")
	 * @Rest\QueryParam(name="offset", requirements="\d+", default="0", description="Offset for returned bells.")
	 */
	public function listBells(ParamFetcher $paramFetcher): Response
	{
		$id = $this->session->id();
		if (!$id) {
			throw new HttpException(403);
		}

		$limit = $paramFetcher->get('limit');
		$offset = $paramFetcher->get('offset');
		$bells = $this->bellGateway->listBells($id, $limit, $offset);

		return $this->handleView($this->view($bells, 200));
	}

	/**
	 * Marks one or more bells as read.
	 *
	 * @OA\Parameter(name="bellId", in="path", @OA\Schema(type="integer"), description="which bell to mark as read")
	 * @OA\Response(response="200", description="At least one of the bells was successfully marked.")
	 * @OA\Response(response="400", description="If the list of IDs is empty or none of the bells could be marked.")
	 * @OA\Response(response="403", description="Insufficient permissions to change the bells.")
	 * @OA\Tag(name="bells")
	 *
	 * @Rest\Patch("bells")
	 * @Rest\RequestParam(name="ids")
	 */
	public function markBellsAsReadAction(ParamFetcher $paramFetcher): Response
	{
		$id = $this->session->id();
		if (!$id) {
			throw new HttpException(403);
		}

		$bellIds = $paramFetcher->get('ids');
		if (!is_array($bellIds) || empty($bellIds)) {
			throw new HttpException(400);
		}

		$changed = $this->bellGateway->setBellsAsSeen($bellIds, $id);

		if ($changed === 0) {
			return $this->handleView($this->view([], 400));
		} else {
			return $this->handleView($this->view([
				'marked' => $changed
			], 200));
		}
	}

	/**
	 * Deletes a bell.
	 *
	 * @OA\Parameter(name="bellId", in="path", @OA\Schema(type="integer"), description="which bell to delete")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="403", description="Insufficient permissions to delete the bell.")
	 * @OA\Response(response="404", description="The user does not have a bell with that ID.")
	 * @OA\Tag(name="bells")
	 *
	 * @Rest\Delete("bells/{bellId}", requirements={"bellId" = "\d+"})
	 */
	public function deleteBellAction(int $bellId): Response
	{
		$id = $this->session->id();
		if (!$id) {
			throw new HttpException(403);
		}

		$deleted = $this->bellGateway->delBellForFoodsaver($bellId, $id);

		return $this->handleView($this->view([], $deleted ? 200 : 404));
	}
}
