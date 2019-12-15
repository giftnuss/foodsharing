<?php

namespace Foodsharing\Services;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\StoreModel;

class FoodsaverService
{
	private $foodsaverGateway;
	private $quizSessionGateway;

	public function __construct(
		FoodsaverGateway $buddyGateway,
		QuizSessionGateway $workGroupGateway
	) {
		$this->buddyGateway = $buddyGateway;
		$this->workGroupGateway = $workGroupGateway;
	}

	public function downgradePermanently(int $fsId, StoreModel $storeModel): int
	{
		$this->quizSessionGateway->blockUserForQuiz($fsId, Role::FOODSAVER);

		return $this->foodsaverGateway->downgradePermanently($fsId, $storeModel);
	}
}
