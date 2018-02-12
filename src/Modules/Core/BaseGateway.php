<?php

namespace Foodsharing\Modules\Core;

abstract class BaseGateway
{
	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}
}
