<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
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
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="403", description="Insufficient permissions to list bells.")
	 * @SWG\Tag(name="bells")
	 *
	 * @Rest\Get("bells")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="How many bells to return.")
	 * @Rest\QueryParam(name="offset", requirements="\d+", default="0", description="Offset for returned bells.")
	 */
	public function listBells(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$limit = $paramFetcher->get('limit');
		$offset = $paramFetcher->get('offset');
		$bells = $this->bellGateway->listBells($this->session->id(), $limit, $offset);

		return $this->handleView($this->view($bells, 200));
	}

	/**
	 * Marks one or more bells as read.
	 *
	 * @SWG\Parameter(name="bellId", in="path", type="integer", description="which bell to mark as read")
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="400", description="If the list of IDs is empty.")
	 * @SWG\Response(response="403", description="Insufficient permissions to change the bells.")
	 * @SWG\Response(response="404", description="At least one of the bells does not exist.")
	 * @SWG\Tag(name="bells")
	 *
	 * @Rest\Patch("bells")
	 * @Rest\RequestParam(name="ids")
	 */
	public function markBellsAsReadAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$bellIds = $paramFetcher->get('ids');
		if (!is_array($bellIds) || empty($bellIds)) {
			throw new HttpException(400);
		}

		$changed = $this->bellGateway->setBellsAsSeen($bellIds, $this->session->id());

		return $this->handleView($this->view([
			'ids' => $bellIds
		], $changed === sizeof($bellIds) ? 200 : 404));
	}

	/**
	 * Deletes a bell.
	 *
	 * @SWG\Parameter(name="bellId", in="path", type="integer", description="which bell to delete")
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="403", description="Insufficient permissions to delete the bell.")
	 * @SWG\Response(response="404", description="The user does not have a bell with that ID.")
	 * @SWG\Tag(name="bells")
	 *
	 * @Rest\Delete("bells/{bellId}", requirements={"bellId" = "\d+"})
	 */
	public function deleteBellAction(int $bellId): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$deleted = $this->bellGateway->delBellForFoodsaver($bellId, $this->session->id());

		return $this->handleView($this->view([], $deleted ? 200 : 404));
	}
}
