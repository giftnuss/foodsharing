<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Foodsharing\Permissions\StorePermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreRestController extends AbstractFOSRestController
{
	private Session $session;
	private StoreGateway $storeGateway;
	private StoreTransactions $storeTransactions;
	private StorePermissions $storePermissions;
	private BellGateway $bellGateway;

	// literal constants
	private const NOT_LOGGED_IN = 'not logged in';
	private const ID = 'id';

	public function __construct(
		Session $session,
		StoreGateway $storeGateway,
		StoreTransactions $storeTransactions,
		StorePermissions $storePermissions,
		BellGateway $bellGateway
	) {
		$this->session = $session;
		$this->storeGateway = $storeGateway;
		$this->storeTransactions = $storeTransactions;
		$this->storePermissions = $storePermissions;
		$this->bellGateway = $bellGateway;
	}

	/**
	 * Returns details of the store with the given ID. Returns 200 and the
	 * store, 404 if the store does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("stores/{storeId}", requirements={"basketId" = "\d+"})
	 */
	public function getStoreAction(int $storeId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$store = $this->storeGateway->getBetrieb($storeId);

		if (!$store || !isset($store[self::ID])) {
			throw new HttpException(404, 'Store does not exist.');
		}

		$store = RestNormalization::normalizeStore($store);

		return $this->handleView($this->view(['store' => $store], 200));
	}

	/**
	 * @Rest\Get("user/current/stores")
	 */
	public function getFilteredStoresForUserAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(403, self::NOT_LOGGED_IN);
		}

		$filteredStoresForUser = $this->storeTransactions->getFilteredStoresForUser($this->session->id());

		if ($filteredStoresForUser === []) {
			return $this->handleView($this->view([], 204));
		}

		return $this->handleView($this->view($filteredStoresForUser, 200));
	}

	/**
	 * @Rest\Post("stores/{storeId}/posts")
	 * @Rest\RequestParam(name="text")
	 */
	public function addStorePostAction(int $storeId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->storePermissions->mayWriteStoreWall($storeId)) {
			throw new AccessDeniedHttpException();
		}

		$text = $paramFetcher->get('text');
		$this->storeGateway->add_betrieb_notiz([
			'foodsaver_id' => $this->session->id(),
			'betrieb_id' => $storeId,
			'text' => $text,
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => Milestone::NONE,
			'last' => 1
		]);

		$storeName = $this->storeGateway->getStoreName($storeId);
		$team = $this->storeGateway->getStoreTeam($storeId);

		$bellData = Bell::create(
			'store_wallpost_title',
			'store_wallpost',
			'fas fa-thumbtack',
			['href' => '/?page=fsbetrieb&id=' . $storeId],
			[
				'user' => $this->session->user('name'),
				'name' => $storeName
			],
			'store-wallpost-' . $storeId
		);

		$this->bellGateway->addBell($team, $bellData);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Deletes a post from the wall of a store.
	 *
	 * @Rest\Delete("stores/{storeId}/posts/{postId}")
	 */
	public function deleteStorePostAction(int $storeId, int $postId): Response
	{
		if (!$this->storePermissions->mayDeleteStoreWallPost($storeId, $postId)) {
			throw new AccessDeniedHttpException();
		}
		$result = $this->storeGateway->getStoreWallpost($storeId, $postId);

		$this->storeGateway->addStoreLog($result['betrieb_id'], $this->session->id(), $result['foodsaver_id'], new \DateTime($result['zeit']), StoreLogAction::DELETED_FROM_WALL, $result['text']);

		$this->storeGateway->deleteStoreWallpost($storeId, $postId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Removes the user's own request or denies another user's request for a store.
	 *
	 * @Rest\Delete("stores/{storeId}/requests/{userId}")
	 */
	public function removeStoreRequestAction(int $storeId, int $userId): Response
	{
		if ($this->session->id() !== $userId && !$this->storePermissions->mayEditStoreTeam($storeId)) {
			throw new HttpException(403);
		}

		$this->storeTransactions->removeStoreRequest($storeId, $userId);

		if ($this->session->id() == $userId) {
			$LogAction = StoreLogAction::REQUEST_CANCELLED;
		} else {
			$LogAction = StoreLogAction::REQUEST_DECLINED;
		}

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, $LogAction);

		return $this->handleView($this->view([], 200));
	}
}
