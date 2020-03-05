<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for food share points.
 */
final class FoodSharePointRestController extends AbstractFOSRestController
{
	private $foodSharePointGateway;
	private $session;

	private const NOT_LOGGED_IN = 'not logged in';
	private const MAX_FSP_DISTANCE = 50;

	public function __construct(FoodSharePointGateway $foodSharePointGateway, Session $session)
	{
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->session = $session;
	}

	/**
	 * Returns a list of food share points close to a given location. If the location is not valid the user's
	 * home location is used. The distance is measured in kilometers.
	 *
	 * Returns 200 and a list of food share points, 400 if the distance is out of range, or 401 if not logged in.
	 *
	 * @Rest\Get("foodSharePoints/nearby")
	 * @Rest\QueryParam(name="lat", nullable=true)
	 * @Rest\QueryParam(name="lon", nullable=true)
	 * @Rest\QueryParam(name="distance", nullable=false, requirements="\d+")
	 */
	public function listNearbyFoodSharePointsAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$location = $this->fetchLocationOrUserHome($paramFetcher);
		$distance = $paramFetcher->get('distance');
		if ($distance < 1 || $distance > self::MAX_FSP_DISTANCE) {
			throw new HttpException(400, 'distance must be positive and <= ' . self::MAX_FSP_DISTANCE);
		}

		$fsps = $this->foodSharePointGateway->listNearbyFoodSharePoints($location, $distance);
		$fsps = array_map(function ($fsp) {
			return $this->normalizeFoodSharePoint($fsp);
		}, $fsps);

		return $this->handleView($this->view($fsps, 200));
	}

	/**
	 * DEPRECATED: Wrapper for listNearbyFoodSharePointsAction. Provides endpoint on old url.
	 *
	 * @Rest\Get("fairSharePoints/nearby")
	 * @Rest\QueryParam(name="lat", nullable=true)
	 * @Rest\QueryParam(name="lon", nullable=true)
	 * @Rest\QueryParam(name="distance", nullable=false, requirements="\d+")
	 *
	 * @deprecated Old naming scheme, remove this when all clients are updated
	 */
	public function oldListNearbyFoodSharePointsAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		return $this->listNearbyFoodSharePointsAction($paramFetcher);
	}

	/**
	 * Returns details of the food share point with the given ID. Returns 200 and the
	 * food share point, 500 if the food share point does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("foodSharePoints/{foodSharePointId}", requirements={"foodSharePointId" = "\d+"})
	 */
	public function getFoodSharePointAction(int $foodSharePointId): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$foodSharePoint = $this->foodSharePointGateway->getFoodSharePoint($foodSharePointId);
		if (!$foodSharePoint || $foodSharePoint['status'] !== 1) {
			throw new HttpException(404, 'Food share point does not exist or was deleted.');
		}

		$foodSharePoint = $this->normalizeFoodSharePoint($foodSharePoint);

		return $this->handleView($this->view($foodSharePoint, 200));
	}

	/**
	 * DEPRECATED: Wrapper for getFoodSharePointAction. Provides endpoint on old url.
	 *
	 * @Rest\Get("fairSharePoints/{foodSharePointId}", requirements={"foodSharePointId" = "\d+"})
	 *
	 * @deprecated Old naming scheme, remove this when all clients are updated
	 */
	public function oldGetFoodSharePointAction(int $foodSharePointId): \Symfony\Component\HttpFoundation\Response
	{
		return $this->getFoodSharePointAction($foodSharePointId);
	}

	private function fetchLocationOrUserHome($paramFetcher): array
	{
		$lat = $paramFetcher->get('lat');
		$lon = $paramFetcher->get('lon');
		if (!$this->isValidNumber($lat, -90.0, 90.0) || !$this->isValidNumber($lon, -180.0, 180.0)) {
			// find user's location
			$loc = $this->session->getLocation();
			$lat = $loc['lat'];
			$lon = $loc['lon'];
			if ($lat === 0 && $lon === 0) {
				throw new HttpException(400, 'The user profile has no address.');
			}
		}

		return ['lat' => $lat, 'lon' => $lon];
	}

	/**
	 * Checks if the number is a valid value in the given range.
	 */
	private function isValidNumber($value, $lowerBound, $upperBound): bool
	{
		return !is_null($value) && !is_nan($value)
			&& ($lowerBound <= $value) && ($upperBound >= $value);
	}

	/**
	 * Normalizes the details of a food share point for the Rest response.
	 *
	 * @param array $fspData the food share point data
	 */
	private function normalizeFoodSharePoint(array $data): array
	{
		// set main properties
		$fsp = [
			'id' => (int)$data['id'],
			'regionId' => (int)$data['bezirk_id'],
			'name' => $data['name'],
			'description' => $data['desc'],
			'address' => $data['anschrift'],
			'city' => $data['ort'],
			'postcode' => $data['plz'],
			'lat' => (float)$data['lat'],
			'lon' => (float)$data['lon'],
			'createdAt' => RestNormalization::normalizeDate($data['time_ts']),
			'picture' => $data['picture']
		];

		if ($fsp['picture'] == '' || !$fsp['picture']) {
			$fsp['picture'] = null;
		}

		return $fsp;
	}
}
