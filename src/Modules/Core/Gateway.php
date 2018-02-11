<?php

namespace Foodsharing\Modules\Core;

use PDO;

class Gateway
{

	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function fetch($query, $params = [])
	{
		$statement = $this->pdo->prepare($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$statement->execute($params);
		return $statement->fetch();
	}

	public function fetchFirstColumn($query, $params = [])
	{
		$statement = $this->pdo->prepare($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$statement->execute($params);
		return $statement->fetchColumn(0);
	}

}
