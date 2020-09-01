<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Lib\Session;

class BasketTransactions
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
	 * @param int $weight weight in kg
	 * @param int $lifetime lifetime in days
	 */
	public function addBasket(string $description, string $photo, ?array $contactTypes, ?string $tel, ?string $handy,
			?int $weight, ?float $lat, ?float $lon, ?int $lifetime): array
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
			return [];
		}

		return $this->basketGateway->getBasket($basketId);
	}
}
