<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\StoreModel;

class FoodsaverTransactions
{
	private FoodsaverGateway $foodsaverGateway;
	private QuizSessionGateway $quizSessionGateway;
	private BasketGateway $basketGateway;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		QuizSessionGateway $quizSessionGateway,
		BasketGateway $basketGateway
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->quizSessionGateway = $quizSessionGateway;
		$this->basketGateway = $basketGateway;
	}

	public function downgradeAndBlockForQuizPermanently(int $fsId, StoreModel $storeModel): int
	{
		$this->quizSessionGateway->blockUserForQuiz($fsId, Role::FOODSAVER);

		return $this->foodsaverGateway->downgradePermanently($fsId, $storeModel);
	}

	public function deleteFoodsaver(int $foodsaverId): void
	{
		// set all active baskets of the user to deleted
		$this->basketGateway->removeActiveUserBaskets($foodsaverId);

		// delete the user
		$this->foodsaverGateway->deleteFoodsaver($foodsaverId);
	}
}
