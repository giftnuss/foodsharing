<?php

namespace Foodsharing\Modules\Core;

use PDO;

class Database
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function fetch($query, $params = [])
	{
		return $this->preparedQuery($query, $params)->fetch();
	}

	public function fetchAll($query, $params = [])
	{
		return $this->preparedQuery($query, $params)->fetchAll();
	}

	public function fetchValue($query, $params = [])
	{
		return $this->preparedQuery($query, $params)->fetchColumn(0);
	}

	private function preparedQuery($query, $params)
	{
		$statement = $this->pdo->prepare($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$statement->execute($params);

		return $statement;
	}
}
