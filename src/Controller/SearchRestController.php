<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
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
	public function listUserResultsAction(ParamFetcher $paramFetcher, Session $session, FoodsaverGateway $foodsaverGateway, RegionGateway $regionGateway): Response
	{
		if (!$session->id()) {
			throw new HttpException(403);
		}

		$q = $paramFetcher->get('q');

		if (preg_match('/^[0-9]+$/', $q) && $foodsaverGateway->foodsaverExists((int)$q)) {
			$user = $foodsaverGateway->getFoodsaverName((int)$q);
			$results = [['id' => (int)$q, 'value' => $user . ' (' . (int)$q . ')']];
		} else {
			if (in_array(RegionIDs::EUROPE_WELCOME_TEAM, $this->session->listRegionIDs(), true) ||
				$this->session->may('orga')) {
				$regions = null;
			} else {
				$regions = array_column(array_filter(
					$regionGateway->listForFoodsaver($session->id()),
					function ($v) {
						return in_array($v['type'], [Type::WORKING_GROUP, Type::CITY, Type::REGION, TYPE::BIG_CITY, TYPE::DISTRICT, Type::PART_OF_TOWN]);
					}
				), 'id');
				$ambassador = $regionGateway->getFsAmbassadorIds($session->id());
				foreach ($ambassador as $region) {
					/* TODO: Refactor listIdsForDescendantsAndSelf to work with multiple regions. I did not do this now as it might impose too big of a risk for the release.
					2020-05-15 NerdyProjects I will care within a few weeks!
					Anyway, the performance of this should be orders of magnitude higher than the previous implementation.
					 */
					$regions = array_merge(
						$regions,
						$regionGateway->listIdsForDescendantsAndSelf($region)
					);
				}
				array_unique($regions);
			}

			$results = $this->searchGateway->searchUserInGroups(
				$q,
				$regions
			);
			$results = array_map(function ($v) { return ['id' => $v['id'], 'value' => $v['name'] . ' ' . $v['nachname'] . ' (' . $v['id'] . ')']; }, $results);
		}

		return $this->handleView($this->view($results, 200));
	}
}
