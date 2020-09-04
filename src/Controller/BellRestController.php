<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
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
