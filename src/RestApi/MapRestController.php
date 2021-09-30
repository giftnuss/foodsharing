<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;
use Foodsharing\Modules\Map\MapGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MapRestController extends AbstractFOSRestController
{
	private MapGateway $mapGateway;
	private Session $session;

	public function __construct(
		MapGateway $mapGateway,
		Session $session
	) {
		$this->mapGateway = $mapGateway;
		$this->session = $session;
	}

	/**
	 * Returns the coordinates of all baskets.
	 *
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Tag(name="map")
	 *
	 * @Rest\Get("map/markers")
	 * @Rest\QueryParam(name="types")
	 * @Rest\QueryParam(name="status")
	 */
	public function getMapMarkersAction(ParamFetcher $paramFetcher): Response
	{
		$types = (array)$paramFetcher->get('types');
		$markers = [];
		if (in_array('baskets', $types)) {
			$markers['baskets'] = $this->mapGateway->getBasketMarkers();
		}
		if (in_array('fairteiler', $types)) {
			$markers['fairteiler'] = $this->mapGateway->getFoodSharePointMarkers();
		}
		if (in_array('communities', $types)) {
			$markers['communities'] = $this->mapGateway->getCommunityMarkers();
		}
		if (in_array('betriebe', $types)) {
			if (!$this->session->id()) {
				throw new UnauthorizedHttpException('Not logged in.');
			}

			$excludedStoreTypes = [];
			$teamStatus = [];
			$status = $paramFetcher->get('status');
			if (is_array($status) && !empty($status)) {
				foreach ($status as $s) {
					switch ($s) {
						case 'needhelpinstant':
							$teamStatus[] = TeamStatus::OPEN_SEARCHING;
							break;
						case 'needhelp':
							$teamStatus[] = TeamStatus::OPEN;
							break;
						case 'nkoorp':
							$excludedStoreTypes = array_merge($excludedStoreTypes, [
								CooperationStatus::COOPERATION_STARTING, CooperationStatus::COOPERATION_ESTABLISHED
							]);
							break;
					}
				}
			}

			$markers['betriebe'] = $this->mapGateway->getStoreMarkers($excludedStoreTypes, $teamStatus);
		}

		return $this->handleView($this->view($markers, 200));
	}
}
