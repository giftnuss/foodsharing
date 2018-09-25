<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for food baskets.
 */
class BasketRestController extends FOSRestController
{
	private $gateway;
	private $session;

	public function __construct(BasketGateway $gateway, Session $session)
	{
		$this->gateway = $gateway;
		$this->session = $session;
	}

	/**
	 * Normalizes the details of a basket of the current user for the Rest 
	 * response, including requests.
	 */
	private function normalizeMyBasket($b, $updates = []) {
		$basket = [
			'id' => (int)$b['id'],
			'description' => html_entity_decode($b['description']),
			'picture' => $b['picture'],
			'createdAt' => date('Y-m-d\TH:i:s', $b['time_ts']),
			'updatedAt' => date('Y-m-d\TH:i:s', $b['time_ts']),
			'requests' => []
		];
		
		//add requests, if there are any in the updates
		foreach ($updates as $update) {
			if ((int)$update['id'] == $basket['id']) {
				$time = date('Y-m-d\TH:i:s', $update['time_ts']);
				$basket['requests'][] = [
					'user' => [
						'id' => (int)$update['fs_id'],
						'name' => $update['fs_name'],
						'avatar' => $update['fs_photo'],
						'sleepStatus' => $update['sleep_status'],
					],
					'description' => $update['description'],
					'time' => $time,
				];
				if (strcmp($time, $basket['updatedAt']) > 0) {
					$basket['updatedAt'] = $time;
				}
			}
		}
		
		return $basket;
	}

	/**
	 * Normalizes the details of a basket for the Rest response.
	 */
	private function normalizeBasket($b)
	{
		//set main properties
		$basket = [
			'id' => (int)$b['id'],
			'status' => (int)$b['status'],
			'description' => html_entity_decode($b['description']),
			'picture' => $b['picture'],
			'contactTypes' => $b['contact_type'],
			'createdAt' => date('Y-m-d\TH:i:s', $b['time_ts']),
			'updatedAt' => date('Y-m-d\TH:i:s', $b['time_ts']),
			'until' => date('Y-m-d\TH:i:s', $b['until_ts']),
			'lat' => (float)$b['lat'],
			'lon' => (float)$b['lon'],
			'creator' => [
				'id' => $b['fs_id'],
				'name' => $b['fs_name'],
				'avatar' => $b['fs_photo'] ? ('/images/130_q_' . $b['fs_photo']) : null,
				'sleepStatus' => $b['sleep_status']
			]
		];
		
		//add phone numbers if contact_type includes telephone
		$tel = '';
		$handy = '';
		if (isset($b['contact_type'])) {
			$contactTypes = explode(':', $b['contact_type']);
			
			if (in_array(2, $contactTypes)) {
				$tel = $b['tel'];
				$handy = $b['handy'];
			}
		}
		$basket['tel'] = $tel;
		$basket['handy'] = $handy;

		return $basket;
	}

	/**
	 * Returns a list of all basket IDs together with the coordinates.
	 *
	 * @Rest\Get("baskets/coordinates")
	 */
	public function getBasketCoordinatesAction()
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$baskets = $this->gateway->getBasketCoordinates();

		$view = $this->view([
			'baskets' => $baskets
		], 200);

		return $this->handleView($view);
	}

	/**
	 * Returns details of the basket with the given ID.
	 *
	 * @Rest\Get("baskets/basket/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function getBasketAction($basketId)
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		//TODO: this throws (500 "Notice: Undefined index: foodsaver_id") instead of a 400 code
		$basket = $this->gateway->getBasket($basketId);
		$updates = $this->gateway->listUpdates($this->session->id());
		$data = $this->normalizeBasket($basket, $updates);
		
		$view = $this->view([
			'basket' => $data
		], 200);

		return $this->handleView($view);
	}
	
	/**
	 * Lists all baskets of the current user.
	 * 
	 * @Rest\Get("baskets/mybaskets")
	 */
	public function listMyBasketsAction() {
		if (!$this->session->may()) {
			throw new HttpException(401);
		}
        
		$updates = $this->gateway->listUpdates($this->session->id());
		$baskets = $this->gateway->listMyBaskets($this->session->id());
		$data = array_map(function ($b) use ($updates) { return $this->normalizeMyBasket($b, $updates); }, $baskets);

		$view = $this->view([
			'baskets' => $data
		], 200);

		return $this->handleView($view);
    }
	
	/**
	 * Adds a new basket. The description must not be empty, all other 
	 * parameters are optional.
	 * 
	 * @Rest\Post("baskets/add")
	 * @Rest\RequestParam(name="description", nullable=false)
	 * @Rest\RequestParam(name="contactTypes", nullable=true)
	 * @Rest\RequestParam(name="tel", nullable=true)
	 * @Rest\RequestParam(name="handy", nullable=true)
	 * @Rest\RequestParam(name="weight", nullable=true)
	 * @Rest\RequestParam(name="foodTypes", nullable=true)
	 * @Rest\RequestParam(name="foodKinds", nullable=true)
	 */
	public function addBasketAction(ParamFetcher $paramFetcher) {
		if (!$this->session->may()) {
			throw new HttpException(401);
		}
		
		//prepare and check description
		$description = trim(strip_tags($paramFetcher->get('description')));
		if (empty($description)) {
			throw new HttpException(400, 'the description must not be empty: '.
					$paramFetcher->get('description')
					.' '.strip_tags($paramFetcher->get('description'))
					.' '.trim(strip_tags($paramFetcher->get('description'))));
		}
		
		//find user's location
		$location_type = 0;
		$loc = $this->session->getLocation();
		$lat = $loc['lat'];
		$lon = $loc['lon'];
		if ($lat == 0 && $lon == 0) {
			throw new HttpException(400, 'the user profile has no address');
		}
		
		//parse contact type and phone numbers
		$contactString = '1';
		$phone = [
			'tel' => '',
			'handy' => ''
		];
		$contactTypes = $paramFetcher->get('body');
		if (!is_null($contactTypes) && is_array($contactTypes)) {
			$contactString = implode(':', $contactTypes);
			if (in_array(2, $contactTypes)) {
				$phone = [
					'tel' => preg_replace('/[^0-9\-\/]/', '', $paramFetcher->get('tel')),
					'handy' => preg_replace('/[^0-9\-\/]/', '', $paramFetcher->get('handy')),
				];
			}
		}
		
		//add basket
		$pic = ''; //TODO
		$basketId = $this->gateway->addBasket($description, $pic, $phone, $contactString, 
				$paramFetcher->get('weight'), $location_type, $lat, $lon, 
				$this->session->user('bezirk_id'), $this->session->id()
		);
		if ($basketId == 0) {
			throw new HttpException(400, 'unable to create the basket');
		} 

		//add food types
		$foodTypes = $paramFetcher->get('foodTypes');
		if (!is_null($foodTypes) && is_array($foodTypes)) {
			$types = array();
			foreach ($foodTypes as $ft) {
				if ((int) $ft > 0) {
					$types[] = (int) $ft;
				}
			}

			$this->basketGateway->addTypes($basketId, $types);
		}

		//add kinds of food
		$foodKinds = $paramFetcher->get('foodKinds');
		if (!is_null($foodKinds) && is_array($foodKinds)) {
			$kinds = array();
			foreach ($foodKinds as $fk) {
				if ((int) $fk > 0) {
					$kinds[] = (int) $fk;
				}
			}

			$this->basketGateway->addKind($basketId, $kinds);
		}

		return $this->handleView($this->view([]), 200);
	}

	/**
	 * Removes a basket of this user with the given ID.
	 * 
	 * @Rest\Get("baskets/remove/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function removeBasketAction($basketId) {
		if (!$this->session->may()) {
			throw new HttpException(401);
		}
		
		$status = $this->gateway->removeBasket($basketId, $this->session->id());

		if ($status == 0) {
			throw new HttpException(404);
		} else {
			return $this->handleView($this->view([]), 200);
		}
	}
}
