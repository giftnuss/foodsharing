<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\FairTeiler\FairTeilerGateway;
use Foodsharing\Services\ImageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for fair share points.
 */
final class FairSharePointRestController extends AbstractFOSRestController
{
	private $gateway;
	private $imageService;
	private $session;

	private const NOT_LOGGED_IN = 'not logged in';
	private const MAX_FSP_DISTANCE = 50;

	public function __construct(FairTeilerGateway $gateway, ImageService $imageService, Session $session)
	{
		$this->gateway = $gateway;
		$this->imageService = $imageService;
		$this->session = $session;
	}

	/**
	 * Returns a list of fair share points close to a given location. If the location is not valid the user's
	 * home location is used. The distance is measured in kilometers.
	 *
	 * Returns 200 and a list of fair share points, 400 if the distance is out of range, or 401 if not logged in.
	 *
	 * @Rest\Get("fairSharePoints/nearby")
	 * @Rest\QueryParam(name="lat", nullable=true)
	 * @Rest\QueryParam(name="lon", nullable=true)
	 * @Rest\QueryParam(name="distance", nullable=false, requirements="\d+")
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listNearbyFairSharePointsAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$location = $this->fetchLocationOrUserHome($paramFetcher);
		$distance = $paramFetcher->get('distance');
		if ($distance < 1 || $distance > self::MAX_FSP_DISTANCE) {
			throw new HttpException(400, 'distance must be positive and <= ' . self::MAX_FSP_DISTANCE);
		}

		$fsps = $this->gateway->listCloseFairteiler($location, $distance);
		$fsps = array_map(function ($fsp) {
			return $this->normalizeFairSharePoint($fsp);
		}, $fsps);

		return $this->handleView($this->view($fsps, 200));
	}

	/**
	 * Returns details of the fair share point with the given ID. Returns 200 and the
	 * fair share point, 500 if the fair share point does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("fairSharePoints/{fairSharePointId}", requirements={"fairSharePointId" = "\d+"})
	 *
	 * @param int $fairSharePointId
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getFairSharePointAction(int $fairSharePointId): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$fairSharePoint = $this->gateway->getFairteiler($fairSharePointId);
		if (!$fairSharePoint || $fairSharePoint['status'] !== 1) {
			throw new HttpException(404, 'FairSharePoint does not exist or was deleted.');
		}

		$fairSharePoint = $this->normalizeFairSharePoint($fairSharePoint);

		return $this->handleView($this->view($fairSharePoint, 200));
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
	 * Normalizes the details of a fair share point for the Rest response.
	 *
	 * @param array $fspData the fair share point data
	 *
	 * @return array
	 */
	private function normalizeFairSharePoint(array $data): array
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
