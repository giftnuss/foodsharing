<?php

namespace Foodsharing\Services;

use Flourish\fImage;
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
	 * Copies the file from 'tmp/' to 'images/basket/' and creates
	 * rescaled versions of it. Returns the base name for the created
	 * files or null if the original file does not exist.
	 */
	public function createResizedPictures($filename): ?string
	{
		if (empty($filename) || !file_exists('tmp/' . $filename)) {
			return null;
		}
		$name = preg_replace('/[^a-z0-9\.]/', '', $filename);

		try {
			copy('tmp/' . $filename, 'images/basket/' . $name);
			$img = new fImage('images/basket/' . $name);
			$img->resize(800, 800);
			$img->saveChanges();

			copy('images/basket/' . $name, 'images/basket/medium-' . $name);
			$img = new fImage('images/basket/medium-' . $name);
			$img->resize(450, 450);
			$img->saveChanges();

			copy('images/basket/medium-' . $name, 'images/basket/thumb-' . $name);
			$img = new fImage('images/basket/thumb-' . $name);
			$img->cropToRatio(1, 1);
			$img->resize(200, 200);
			$img->saveChanges();

			copy('images/basket/thumb-' . $name, 'images/basket/75x75-' . $name);
			$img = new fImage('images/basket/75x75-' . $name);
			$img->cropToRatio(1, 1);
			$img->resize(75, 75);
			$img->saveChanges();

			copy('images/basket/75x75-' . $name, 'images/basket/50x50-' . $name);
			$img = new fImage('images/basket/50x50-' . $name);
			$img->cropToRatio(1, 1);
			$img->resize(50, 50);
			$img->saveChanges();

			return $name;
		}
		catch (Exception $e) {
			// in case of an error remove all created files
			removeResizedPicture($name);
			return null;
		}
	}

	/**
	 * Removes all rescaled versions of the picture with the given name.
	 */
	public function removeResizedPictures($name): void
	{
		$prefix = ['', 'medium-', 'thumb-', '75x75-', '50x50-'];
		foreach ($prefix as $p) {
			if (file_exists('images/basket/' . $p . $name)) {
				unlink('images/basket/' . $p . $name);
			}
		}
	}
}
