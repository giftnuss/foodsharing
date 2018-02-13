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

	public function fetchAllValues($query, $params = [])
	{
		return $this->preparedQuery($query, $params)->fetchAll(\PDO::FETCH_COLUMN, 0);
	}


	public function fetchValue($query, $params = [])
	{
		return $this->preparedQuery($query, $params)->fetchColumn(0);
	}

	public function execute($query, $params = [])
	{
		return $this->preparedQuery($query, $params)->rowCount();
	}

	public function insert($table, array $data)
	{
		$columns = array_map(
			[$this, 'getQuotedName'],
			array_keys($data)
		);

		$query = sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$this->getQuotedName($table),
			implode(', ', $columns),
			implode(', ', array_fill(0, count($data), '?'))
		);

		$this->execute($query, array_values($data));

		$lastInsertId = (int)$this->pdo->lastInsertId();
		return $lastInsertId;
	}

	public function update($table, array $data, array $criteria = [])
	{
		if (empty($data)) {
			throw new \InvalidArgumentException(
				"Query update can't be prepared without data."
			);
		}

		$set = [];
		foreach ($data as $column => $value) {
			$set[] = $this->getQuotedName($column) . " = ?";
		}

		$where = $this->generateWhereClause($criteria);

		$query = sprintf('UPDATE %s SET %s %s', $this->getQuotedName($table), implode(', ', $set), $where);

		$params = array_merge(array_values($data), array_values($criteria));
		return $this->execute($query, $params);
	}

	public function delete($table, array $criteria)
	{
		$where = $this->generateWhereClause($criteria);

		$query = 'DELETE FROM ' . $this->getQuotedName($table) . ' ' . $where;
		return $this->execute($query, array_values($criteria));
	}

	// === private functions ===

	private function preparedQuery($query, $params)
	{
		$statement = $this->pdo->prepare($query);
		if (!$statement) {
			throw new \Exception("Query '$query' can't be prepared.");
		}

		$i = 0;
		foreach ($params as $value) {
			$i++;
			if (is_bool($value)) {
				$type = \PDO::PARAM_BOOL;
			} elseif (is_int($value)) {
				$type = \PDO::PARAM_INT;
			} else {
				$type = \PDO::PARAM_STR;
			}
			$statement->bindValue($i, $value, $type);
		}

		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$statement->execute();

		return $statement;
	}

	private function getQuotedName($name)
	{
		return '`' . str_replace('.', '`.`', $name) . '`';
	}

	private function generateWhereClause(array &$criteria)
	{
		if (empty($criteria)) {
			return '';
		}

		$operands = $this->getSupportedOperators();

		$params = [];
		foreach ($criteria as $k => $v) {
			if ($v === null) {
				$params[] = $this->getQuotedName($k) . " IS NULL ";
				unset($criteria[$k]);
				continue;
			}

			$hasOperand = false; // search for equals - no additional operand given

			foreach ($operands as $operand) {
				if (!stripos($k, " $operand") > 0) {
					continue;
				}

				$hasOperand = true;
				$k = str_ireplace(" $operand", '', $k);
				$operand = strtoupper($operand);
				$params[] = $this->getQuotedName($k) . " $operand ? ";
				break;
			}

			if (!$hasOperand) {
				$params[] = $this->getQuotedName($k) . " = ? ";
			}
		}

		return 'WHERE ' . implode('AND ', $params);
	}

	private function getSupportedOperators()
	{
		return [
			'like',
			'!=',
			'<=',
			'>=',
			'<',
			'>',
		];
	}
}
