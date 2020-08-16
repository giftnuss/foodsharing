<?php

namespace Foodsharing\Modules\Core;

abstract class BaseGateway
{
	/**
	 * @var Database
	 */
	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}
}
