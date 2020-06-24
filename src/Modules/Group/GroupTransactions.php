<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TranslationHelper;

/**
 * Groups are the super category in which Regions and WorkGroups belong. GroupTransactions contains the common logic
 * of Regions and WorkGroups.
 */
final class GroupTransactions
{
	private $bellGateway;
	private $foodSharePointGateway;
	private $sanitizerService;
	private $emailHelper;
	private $translationHelper;
	private $regionGateway;
	private $foodsaverGateway;
	private $session;

	public function __construct(
		BellGateway $bellGateway,
		FoodSharePointGateway $foodSharePoint,
		Sanitizer $sanitizerService,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper,
		RegionGateway $regionGateway,
		FoodsaverGateway $foodsaverGateway,
		Session $session
	) {
		$this->bellGateway = $bellGateway;
		$this->foodSharePointGateway = $foodSharePoint;
		$this->sanitizerService = $sanitizerService;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->session = $session;
	}
}
