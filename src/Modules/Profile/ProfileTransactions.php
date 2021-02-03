<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Mails\MailsGateway;

class ProfileTransactions
{
	private ProfileGateway $profileGateway;
	private FoodsaverGateway $foodsaverGateway;
	private BellGateway $bellGateway;
	private MailsGateway $mailsGateway;

	public function __construct(
		ProfileGateway $profileGateway,
		FoodsaverGateway $foodsaverGateway,
		BellGateway $bellGateway,
		MailsGateway $mailsGateway
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->bellGateway = $bellGateway;
		$this->mailsGateway = $mailsGateway;
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
		$bell = Bell::create(
			'banana_given_title',
			'banana_given',
			'fas fa-gifts',
			['href' => '/profile/' . $receiverId],
			['name' => $this->foodsaverGateway->getFoodsaverName($giverId)],
			'banana-' . $receiverId . '-' . $giverId
		);
		$this->bellGateway->addBell($receiverId, $bell);

		return $this->profileGateway->giveBanana($receiverId, $message, $giverId);
	}

	/**
	 * Removes the user's personal (login) email address from the bounce list.
	 */
	public function removeUserFromBounceList(int $userId): void
	{
		$address = $this->foodsaverGateway->getEmailAddress($userId);
		$this->mailsGateway->removeBounceForMail($address);
	}
}
