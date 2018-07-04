<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session\S;

class SearchHelper
{
	private $searchGateway;

	public function __construct(SearchGateway $searchGateway)
	{
		$this->searchGateway = $searchGateway;
	}

	public function search($q)
	{
		$isAdmin = S::isBotschafter() || S::isOrgaTeam();

		return $this->searchGateway->search(
			$q,
			S::may('orga'),
			$isAdmin ? false : S::getCurrentBezirkId()
		);
	}
}
