<?php

namespace Foodsharing\Services;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;

class BasketService
{
	private const TEL = 'tel';
	private const MOBILE_NUMBER = 'handy';

	private $basketGateway;
	private $session;

	public function __construct(
		BasketGateway $basketGateway,
		Session $session
	) {
		$this->basketGateway = $basketGateway;
		$this->session = $session;
	}

	/**
	 * Creates a new food basket and returns the created basket.
	 *
	 * @param string $description basket's description
	 * @param string $phone array with 'tel' and 'handy' entries
	 * @param string $contactString
	 * @param int $weight weight in kg
	 * @param float $lat latitude
	 * @param float $lon longitude
	 * @param int $lifetime lifetime in days
	 *
	 * @return array|bool the basket's details in an array or false
	 */
	public function addBasket($description, $photo, $contactTypes, $tel, $handy,
			$weight, $lat, $lon, $lifetime): array
	{
		// parse contact types and phone numbers
		$contactString = '1';
		$phone = [
			self::TEL => '',
			self::MOBILE_NUMBER => ''
		];
		if ($contactTypes !== null && \is_array($contactTypes)) {
			$contactString = implode(':', $contactTypes);
			if (\in_array(2, $contactTypes, true)) {
				$phone = [
					self::TEL => preg_replace('/[^0-9\-\/]/', '', $tel),
					self::MOBILE_NUMBER => preg_replace('/[^0-9\-\/]/', '', $handy),
				];
			}
		}

		// fix lifetime between 1 and 21 days and convert from days to seconds
		if ($lifetime < 1 || $lifetime > 21) {
			$lifetime = 7;
		}
		if ($weight <= 0) {
			$weight = 3;
		}

		// create basket
		$basketId = $this->basketGateway->addBasket($description, '', $phone, $contactString,
				$weight, 0, $lat, $lon, $lifetime * 60 * 60 * 24,
				$this->session->user('bezirk_id'), $this->session->id()
		);
		if ($basketId === 0) {
			return false;
		}

		return $this->basketGateway->getBasket($basketId);
	}

	/**
	 * Adds food types to a basket. All valid indices of food types from the
	 * array will be added to the basket with the given id if it exists.
	 *
	 * @param int $basketId id of a food basket
	 * @param array $foodTypes array of food type indices
	 *
	 * @return bool whether any valid types were added
	 */
	public function addFoodTypes($basketId, $foodTypes): bool
	{
		if ($foodTypes !== null && \is_array($foodTypes) && $this->basketGateway->getBasket($basketId)) {
			$types = array();
			foreach ($foodTypes as $ft) {
				if ((int)$ft > 0) {
					$types[] = (int)$ft;
				}
			}

			if (!empty(types)) {
				$this->basketGateway->addTypes($basketId, $types);

				return true;
			}
		}

		return false;
	}

	/**
	 * Adds food kinds to a basket. All valid indices of food kinds from the
	 * array will be added to the basket with the given id if it exists.
	 *
	 * @param int $basketId id of a food basket
	 * @param array $foodKinds array of food type indices
	 *
	 * @return bool whether any valid kinds were added
	 */
	public function addFoodKinds($basketId, $foodKinds): bool
	{
		if ($foodKinds !== null && \is_array($foodKinds) && $this->basketGateway->getBasket($basketId)) {
			$kinds = array();
			foreach ($foodKinds as $fk) {
				if ((int)$fk > 0) {
					$kinds[] = (int)$fk;
				}
			}

			if (!empty(kinds)) {
				$this->basketGateway->addKind($basketId, $kinds);

				return true;
			}
		}

		return false;
	}
}
