<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;

class SearchHelper
{
	private $searchGateway;
	private $session;

	public function __construct(SearchGateway $searchGateway, Session $session)
	{
		$this->searchGateway = $searchGateway;
		$this->session = $session;
	}

	public function search($q)
	{
		$isAdmin = $this->session->isBotschafter() || $this->session->isOrgaTeam();

		return $this->searchGateway->search(
			$q,
			$this->session->may('orga'),
			$isAdmin ? false : $this->session->getCurrentBezirkId()
		);
	}
}
