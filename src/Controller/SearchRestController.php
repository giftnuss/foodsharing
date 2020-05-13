<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Search\SearchGateway;
use Foodsharing\Services\SearchService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SearchRestController extends AbstractFOSRestController
{
	private $session;
	private $searchGateway;
	private $searchService;

	public function __construct(Session $session, SearchGateway $searchGateway, SearchService $searchService)
	{
		$this->session = $session;
		$this->searchGateway = $searchGateway;
		$this->searchService = $searchService;
	}

	/**
	 * @Rest\Get("search/legacyindex")
	 */
	public function getSearchLegacyIndexAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(403);
		}
		$data = $this->searchService->generateIndex($this->session->id());

		$view = $this->view($data, 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Get("search/user")
	 * @Rest\QueryParam(name="q", description="Search query.")
	 */
	public function listUserResultsAction(ParamFetcher $paramFetcher, FoodsaverGateway $foodsaverGateway): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$q = $paramFetcher->get('q');

		$canSearchAllFoodsaver = in_array(RegionIDs::EUROPE_WELCOME_TEAM, $this->session->listRegionIDs(), true) ||
			$this->session->may('orga');

		$results = $this->searchGateway->searchUserInGroups(
			$q,
			$this->session->listRegionIDs(),
			$canSearchAllFoodsaver
		);

		if (preg_match('/^[0-9]+$/', $q) && $foodsaverGateway->foodsaverExists((int)$q)) {
			$user = $foodsaverGateway->getFoodsaverName((int)$q);
			$results[] = ['id' => (int)$q, 'value' => $user . ' (' . (int)$q . ')'];
		}

		return $this->handleView($this->view($results, 200));
	}
}
