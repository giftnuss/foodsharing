<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class ProfileTransactions
{
	private ProfileGateway $profileGateway;
	private FoodsaverGateway $foodsaverGateway;
	private BellGateway $bellGateway;

	public function __construct(
		ProfileGateway $profileGateway,
		FoodsaverGateway $foodsaverGateway,
		BellGateway $bellGateway
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->bellGateway = $bellGateway;
	}

	/**
	 * Gives Banana to a user and notifies the receiver with a bell.
	 *
	 * @param int $receiverId the person receiving the trust banana
	 * @param string $message the message the trust banana should contain
	 * @param int $giverId the person giving the trust banana
	 *
	 * @return int the database id of the newly created trust banana
	 */
	public function giveBanana(int $receiverId, string $message, int $giverId): int
	{
		$giver = $this->foodsaverGateway->getFoodsaverBasics($giverId);
		$bell = Bell::create(
			'banana_given_title',
			'banana_given',
			'bell_banana',
			['href' => '/profile/' . $receiverId],
			[
				'name' => $giver['name'],
				'message' => $message,
			],
			'banana-' . $giverId
		);
		$this->bellGateway->addBell($receiverId, $bell);

		return $this->profileGateway->giveBanana($receiverId, $message, $giverId);
	}
}
