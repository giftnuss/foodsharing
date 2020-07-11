<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Search\SearchGateway;
use Foodsharing\Modules\Search\SearchHelper;
use Foodsharing\Modules\Search\SearchIndexGenerator;
use Foodsharing\Permissions\ForumPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SearchRestController extends AbstractFOSRestController
{
	private Session $session;
	private SearchGateway $searchGateway;
	private SearchIndexGenerator $searchIndexGenerator;
	private SearchHelper $searchHelper;
	private ForumPermissions $forumPermissions;

	public function __construct(
		Session $session,
		SearchGateway $searchGateway,
		SearchIndexGenerator $searchIndexGenerator,
		SearchHelper $searchHelper,
		ForumPermissions $forumPermissions
	) {
		$this->session = $session;
		$this->searchGateway = $searchGateway;
		$this->searchIndexGenerator = $searchIndexGenerator;
		$this->searchHelper = $searchHelper;
		$this->forumPermissions = $forumPermissions;
	}

	/**
	 * @Rest\Get("search/legacyindex")
	 */
	public function getSearchLegacyIndexAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(403);
		}
		$data = $this->searchIndexGenerator->generateIndex($this->session->id());

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
				false,
				$regions
			);
			$results = array_map(function ($v) { return ['id' => $v->id, 'value' => $v->name . ' (' . $v->id . ')']; }, $results);
		}

		return $this->handleView($this->view($results, 200));
	}

	/**
	 * General search endpoint that returns foodsavers, stores, and regions.
	 *
	 * @Rest\Get("search/all")
	 * @Rest\QueryParam(name="q", description="Search query.")
	 */
	public function searchAction(ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			throw new HttpException(403);
		}

		$q = $paramFetcher->get('q');
		if (empty($q)) {
			throw new HttpException(400);
		}

		$results = $this->searchHelper->search($q);

		return $this->handleView($this->view($results, 200));
	}

	/**
	 * Searches in the titles of forum threads in a specific group.
	 *
	 * @SWG\Parameter(name="groupId", in="path", type="integer", description="which forum to return threads for (region or group)")
	 * @SWG\Parameter(name="q", in="query", type="string", description="search query")
	 * @SWG\Parameter(name="ambassadorForum", in="query", type="boolean", description="whether to search in the ambassador forum")
	 * @SWG\Response(response="200", description="Success",
	 *     @SWG\Schema(type="object", @SWG\Property(property="data", type="array",
	 *         @SWG\Items(type="object",
	 *             @SWG\Property(property="id", type="integer", description="thread id"),
	 *             @SWG\Property(property="name", type="string", description="thread title")
	 *         )
	 *     ))
	 * )
	 * @SWG\Response(response="400", description="Empty search query.")
	 * @SWG\Response(response="403", description="Insufficient permissions to search in that forum.")
	 * @SWG\Tag(name="search")
	 *
	 * @Rest\Get("search/forum/{groupId}", requirements={"groupId" = "\d+"})
	 * @Rest\QueryParam(name="q", description="Search query.", nullable=false)
	 * @Rest\QueryParam(name="ambassadorForum", description="Whether to search in the ambassador forum.", nullable=false)
	 */
	public function searchForumTitleAction(int $groupId, ParamFetcher $paramFetcher)
	{
		$ambassadorForum = (bool)$paramFetcher->get('ambassadorForum');
		if (!$this->session->may() || !$this->forumPermissions->mayAccessForum($groupId, $ambassadorForum ? 1 : 0)) {
			throw new HttpException(403);
		}

		$q = $paramFetcher->get('q');
		if (empty($q)) {
			throw new HttpException(400);
		}

		$results = $this->searchGateway->searchForumTitle($q, $groupId, $ambassadorForum);

		return $this->handleView($this->view($results, 200));
	}
}
