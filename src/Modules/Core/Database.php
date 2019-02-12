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

	// === high-level methods that build SQL internally ===

	/**
	 * Returns the first row.
	 * Provide table name, column names and criteria.
	 *
	 * @param string $table
	 * @param mixed  $column_names
	 * @param array  $criteria
	 *
	 * @return array
	 */
	public function fetchByCriteria(string $table, $column_names, array $criteria = []): array
	{
		return $this->fetch(...$this->generateSelectStatement($table, $column_names, $criteria));
	}

	/**
	 * Returns all rows.
	 * Provide table name, column names and criteria.
	 *
	 * @param string $table
	 * @param mixed $column_names
	 * @param array  $criteria
	 *
	 * @return array
	 */
	public function fetchAllByCriteria(string $table, $column_names, array $criteria = []): array
	{
		return $this->fetchAll(...$this->generateSelectStatement($table, $column_names, $criteria));
	}

	/**
	 * Returns the named column.
	 * Provide table name, desired column and criteria.
	 *
	 * @param string $table
	 * @param string $column
	 * @param array  $criteria
	 *
	 * @return array
	 */
	public function fetchAllValuesByCriteria(string $table, string $column, array $criteria = []): array
	{
		return $this->fetchAllValues(...$this->generateSelectStatement($table, [$column], $criteria));
	}

	/**
	 * Returns the value of a named column in the first row of the result.
	 * Provide table name, desired column and criteria.
	 *
	 * @param string $table
	 * @param string $column
	 * @param array  $criteria
	 *
	 * @return mixed
	 */
	public function fetchValueByCriteria(string $table, string $column, array $criteria = [])
	{
		return $this->fetchValue(...$this->generateSelectStatement($table, [$column], $criteria));
	}

	public function exists($table, array $criteria): bool
	{
		return $this->count($table, $criteria) > 0;
	}

	public function requireExists($table, array $criteria)
	{
		if (!$this->exists($table, $criteria)) {
			throw new \Exception('No matching records found for criteria ' . json_encode($criteria) . ' in table ' . $table);
		}
	}

	public function count($table, array $criteria): bool
	{
		$where = $this->generateWhereClause($criteria);

		$query = 'SELECT COUNT(*) FROM ' . $this->getQuotedName($table) . ' ' . $where;

		return $this->fetchValue($query, array_values($criteria));
	}

	public function insertOrUpdate(string $table, array $data, array $options = []): int
	{
		return $this->insert($table, $data, array_merge($options, ['update' => true]));
	}

	public function insertIgnore(string $table, array $data, array $options = []): int
	{
		return $this->insert($table, $data, array_merge($options, ['ignore' => true]));
	}

	public function insert(string $table, array $data, array $options = []): int
	{
		$options = array_merge([
			'update' => false,
			'ignore' => false,
		], $options);

		if ($options['ignore'] && $options['update']) {
			throw new \Exception('Can not handle ignore and update at the same time, choose one');
		}

		$columns = array_map(
			[$this, 'getQuotedName'],
			array_keys($data)
		);

		$updateStatement = '';
		if ($options['update']) {
			$updateValues = array_map(function ($name) {
				return sprintf('%s = VALUES (%s)', $name, $name);
			}, $columns);
			$updateValues = implode(', ', $updateValues);
			$updateStatement = sprintf('ON DUPLICATE KEY UPDATE %s', $updateValues);
		}

		$query = sprintf(
			'INSERT %s INTO %s (%s) VALUES (%s) %s',
			 $options['ignore'] ? 'IGNORE' : '',
			$this->getQuotedName($table),
			implode(', ', $columns),
			implode(', ', array_fill(0, count($data), '?')),
			$updateStatement
		);

		$this->preparedQuery($query, array_values($data));

		return (int)$this->pdo->lastInsertId();
	}

	public function update(string $table, array $data, array $criteria = []): int
	{
		if (empty($data)) {
			throw new \InvalidArgumentException(
				"Query update can't be prepared without data."
			);
		}

		$set = [];
		foreach ($data as $column => $value) {
			$set[] = $this->getQuotedName($column) . ' = ?';
		}

		$where = $this->generateWhereClause($criteria);

		$query = sprintf('UPDATE %s SET %s %s', $this->getQuotedName($table), implode(', ', $set), $where);

		$params = array_merge(array_values($data), array_values($criteria));

		return $this->preparedQuery($query, $params)->rowCount();
	}

	public function delete($table, array $criteria): int
	{
		$where = $this->generateWhereClause($criteria);

		$query = 'DELETE FROM ' . $this->getQuotedName($table) . ' ' . $where;

		return $this->preparedQuery($query, array_values($criteria))->rowCount();
	}

	// === methods that accept SQL statements ===

	/**
	 * Returns the first row.
	 *
	 * @param string $query SQL select statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 */
	public function fetch(string $query, array $params = []): array
	{
		$out = $this->preparedQuery($query, $params)->fetch();
		// TODO throw Exception if no result was found
		if (!$out) {
			return [];
		}

		return $out;
	}

	/**
	 * Returns all rows.
	 *
	 * @param string $query SQL select statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 */
	public function fetchAll(string $query, array $params = []): array
	{
		$out = $this->preparedQuery($query, $params)->fetchAll();
		if (!$out) {
			return [];
		}

		return $out;
	}

	/**
	 * Returns the first column.
	 *
	 * @param string $query SQL select statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 */
	public function fetchAllValues(string $query, array $params = []): array
	{
		$out = $this->preparedQuery($query, $params)->fetchAll(\PDO::FETCH_COLUMN, 0);
		if (!$out) {
			return [];
		}

		return $out;
	}

	/**
	 * Returns the value of the first column of the first row.
	 *
	 * @param string $query SQL select statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 */
	public function fetchValue(string $query, array $params = [])
	{
		// throw new \Exception('query ' . $query . ', params ' . json_encode($params));
		$out = $this->preparedQuery($query, $params)->fetchAll();
		if ($out === false || count($out) === 0) {
			throw new \Exception('Expected one or more results, but none was returned.');
		}

		return array_values($out[0])[0];
	}

	/**
	 * @deprecated Use more specific methods if possible
	 * @see Database::update()
	 * @see Database::delete()
	 */
	public function execute($query, $params = [])
	{
		return $this->preparedQuery($query, $params);
	}

	// === helper methods ===

	/**
	 * Generates comma separated question marks for use with SQLs IN() operator.
	 *
	 * @param int $length - number of question marks to be generated
	 */
	public function generatePlaceholders($length): string
	{
		return implode(', ', array_fill(0, $length, '?'));
	}

	public function quote($string): string
	{
		return $this->pdo->quote($string);
	}

	public function now(): string
	{
		return date('Y-m-d H:i:s');
	}

	public function beginTransaction(): bool
	{
		return $this->pdo->beginTransaction();
	}

	public function commit(): bool
	{
		return $this->pdo->commit();
	}

	// === private methods ===

	/**
	 * dehierarchize array – eg. turn ['a', ['b', 'c'], 'd'] into ['a', 'b', 'c', 'd'].
	 */
	private function dehierarchizeArray(array $array): array
	{
		foreach ($array as $index => $value) {
			if (is_array($value)) {
				array_splice($array, $index, 1, $value);
			}
		}

		return $array;
	}

	/**
	 * @throws \Exception
	 */
	private function preparedQuery($query, $params)
	{
		$statement = $this->pdo->prepare($query);
		if (!$statement) {
			throw new \Exception("Query '$query' can't be prepared.");
		}

		$params = $this->dehierarchizeArray($params);

		foreach ($params as $param => $value) {
			if (is_bool($value)) {
				$type = \PDO::PARAM_INT;
			} elseif (is_int($value)) {
				$type = \PDO::PARAM_INT;
			} else {
				$type = \PDO::PARAM_STR;
			}

			// Positional arguments start with 1, not 0
			if (is_int($param)) {
				++$param;
			}
			$statement->bindValue($param, $value, $type);
		}

		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$statement->execute();

		return $statement;
	}

	private function generateSelectStatement(string $table, $column_names, array $criteria)
	{
		$where = $this->generateWhereClause($criteria);

		if (is_string($column_names) && $column_names === '*') {
			$column_query = '*';
		} elseif (is_array($column_names)) {
			$column_query = implode(', ', array_map(
				[$this, 'getQuotedName'],
				$column_names
			));
		} else {
			throw new \Exception('$column_names has unknown format: ' . json_encode($column_names));
		}

		$query = sprintf('SELECT %s FROM %s %s', $column_query, $this->getQuotedName($table), $where);
		$params = array_values($criteria);

		return [$query, $params];
	}

	private function getQuotedName(string $name): string
	{
		return '`' . str_replace('.', '`.`', $name) . '`';
	}

	private function generateWhereClause(array &$criteria): string
	{
		if (empty($criteria)) {
			return '';
		}

		$operands = $this->getSupportedOperators();

		$params = [];
		foreach ($criteria as $k => $v) {
			if ($v === null) {
				$params[] = $this->getQuotedName($k) . ' IS NULL ';
				unset($criteria[$k]);
				continue;
			}

			if (is_array($v) && empty($v)) {
				$params[] = 'false'; // an empty array means that the where clause will be false
				continue;
			}

			if (is_array($v)) {
				$params[] = $this->getQuotedName($k) . ' IN (' . $this->generatePlaceholders(count($v)) . ') ';
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
				$params[] = $this->getQuotedName($k) . ' = ? ';
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
