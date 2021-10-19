<?php

namespace Foodsharing\Modules\Core;

use Carbon\Carbon;
use Envms\FluentPDO\Query;
use PDO;

class Database
{
	private PDO $pdo;
	private $fluent;
	private $influxMetrics;

	public function __construct(PDO $pdo, InfluxMetrics $influxMetrics)
	{
		$this->pdo = $pdo;
		$this->fluent = new Query($pdo);
		$this->influxMetrics = $influxMetrics;
	}

	/**
	 * @return Query FluentPDO Querybuilder
	 */
	public function fluent()
	{
		return $this->fluent;
	}

	// === high-level methods that build SQL internally ===

	/**
	 * Returns the row identified by $id.
	 *
	 * {@internal Assumption: the id field is actually called 'id'}
	 *
	 * @param string $table table name
	 * @param array|string $column_names either one column name, "*" for all columns
	 *                                   or an array of multiple names
	 * @param int $id record ID
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function fetchById(string $table, $column_names, $id)
	{
		return $this->fetchByCriteria($table, $column_names, ['id' => $id]);
	}

	/**
	 * Returns the columns specified in $column_names for the first rows matching $criteria.
	 *
	 * @param string $table table name
	 * @param array|string $column_names either one column name, "*" for all columns
	 *                                   or an array of multiple names
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @return array an array containing the first row
	 *
	 * @throws \Exception
	 */
	public function fetchByCriteria(string $table, $column_names, array $criteria = []): array
	{
		return $this->fetch(...$this->generateSelectStatement($table, $column_names, $criteria));
	}

	/**
	 * Returns the columns specified in $column_names for all rows matching $criteria.
	 *
	 * @param string $table table name
	 * @param array|string $column_names either one column name, "*" for all columns
	 *                                     or an array of multiple names
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @return array rows with the specified columns
	 *
	 * @throws \Exception
	 */
	public function fetchAllByCriteria(string $table, $column_names, array $criteria = []): array
	{
		return $this->fetchAll(...$this->generateSelectStatement($table, $column_names, $criteria));
	}

	/**
	 * Returns the named column for rows matching $criteria.
	 *
	 * @param string $table table name
	 * @param string $column column name the value is contained in
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @return array all values of the named column
	 *
	 * @throws \Exception
	 */
	public function fetchAllValuesByCriteria(string $table, string $column, array $criteria = []): array
	{
		return $this->fetchAllValues(...$this->generateSelectStatement($table, [$column], $criteria));
	}

	/**
	 * Returns a named column of the row identified by $id.
	 *
	 * {@internal Assumption: the id field is actually called 'id'.}
	 *
	 * @param string $table table name
	 * @param string $column_name column name the value is contained in
	 * @param int $id record ID
	 *
	 * @return mixed the value for the specified column
	 *
	 * @throws \Exception if there is no row identified by $id
	 */
	public function fetchValueById(string $table, $column_name, $id)
	{
		return $this->fetchValueByCriteria($table, $column_name, ['id' => $id]);
	}

	/**
	 * Returns the value of a named column in the first row of the result.
	 * Provide table name, desired column and criteria.
	 *
	 * @param string $table the table's name
	 * @param string $column the name of the column to be returned
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @return mixed the first row's value for the specified column
	 *
	 * @throws \Exception if there were no results
	 */
	public function fetchValueByCriteria(string $table, string $column, array $criteria = [])
	{
		return $this->fetchValue(...$this->generateSelectStatement($table, [$column], $criteria));
	}

	/**
	 * Checks if any rows exist in $table that match $criteria.
	 *
	 * @param string $table the table's name
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @return bool whether any rows exist
	 *
	 * @throws \Exception
	 */
	public function exists(string $table, array $criteria): bool
	{
		return $this->count($table, $criteria) > 0;
	}

	/**
	 * Checks if any rows exist in $table that match $criteria and throws an Exception if it does not.
	 *
	 * @param string $table the table's name
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @throws \Exception if no rows matching $criteria exist
	 */
	public function requireExists(string $table, array $criteria)
	{
		if (!$this->exists($table, $criteria)) {
			throw new \Exception('No matching records found for criteria ' . json_encode($criteria) . ' in table ' . $table);
		}
	}

	/**
	 * Count the rows in $table that match $criteria.
	 *
	 * @param string $table the table's name
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()}
	 *
	 * @return int count of the rows matching $criteria
	 *
	 * @throws \Exception
	 */
	public function count(string $table, array $criteria): int
	{
		$where = $this->generateWhereClause($criteria);

		$query = 'SELECT COUNT(*) FROM ' . $this->getQuotedName($table) . ' ' . $where;

		return $this->fetchValue($query, array_values($criteria));
	}

	/**
	 * Inserts or updates one row in a table.
	 *
	 * @param string $table the table's name
	 * @param array $data names of the columns and the row's entries as key-value pairs
	 * @param array $options unused. Setting 'ignore' will raise an exception ({@see Database::insertMultiple()})
	 *
	 * @return int the number of inserted or updated rows
	 *
	 * @throws \Exception
	 */
	public function insertOrUpdate(string $table, array $data, array $options = []): int
	{
		return $this->insert($table, $data, array_merge($options, ['update' => true]));
	}

	/**
	 * Inserts or updates multiple rows in a table.
	 *
	 * @param string $table the table's name
	 * @param array $data names of the columns and the row's entries as key-value pairs
	 * @param array $options unused. Setting 'ignore' will raise an exception ({@see Database::insertMultiple()})
	 *
	 * @return int the number of inserted or updated rows
	 *
	 * @throws \Exception
	 */
	public function insertOrUpdateMultiple(string $table, array $data, array $options = []): int
	{
		return $this->insertMultiple($table, $data, array_merge($options, ['update' => true]));
	}

	/**
	 * @param string $table the table's name
	 * @param array $data names of the columns and the row's entries as key-value pairs
	 * @param array $options unused. Setting 'update' will raise an exception ({@see Database::insertMultiple()})
	 *
	 * @return int the number of inserted or updated rows
	 *
	 * @throws \Exception
	 */
	public function insertIgnore(string $table, array $data, array $options = []): int
	{
		return $this->insert($table, $data, array_merge($options, ['ignore' => true]));
	}

	/**
	 * Inserts one row into a table.
	 *
	 * @param string $table the table's name
	 * @param array $data names of the columns and the row's entries as key-value pairs
	 * @param array $options {@see Database::insertMultiple()}
	 *
	 * @return int the primary key of the inserted row
	 *
	 * @throws \Exception
	 */
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

	/**
	 * Inserts multiple rows into a table.
	 *
	 * @param string $table the table's name
	 * @param array $data 2-dim array with names of the columns and the row's entries as key-value pairs for each row
	 * @param array $options Key 'update': set to true to add a ON DUPLICATE KEY UPDATE clause to the end of the INSERT statement
	 *                       Key 'ignore': set to true to add IGNORE to the INSERT statement
	 *                       'update' and 'ignore' are exclusive of each other
	 *
	 * @return int the number of inserted or updated rows
	 *
	 * @throws \Exception
	 */
	public function insertMultiple(string $table, array $data, array $options = []): int
	{
		if (empty($data)) {
			return 0;
		}

		$options = array_merge([
			'update' => false,
			'ignore' => false,
		], $options);

		if ($options['ignore'] && $options['update']) {
			throw new \Exception('Can not handle ignore and update at the same time, choose one');
		}

		// find all keys in data
		$keys = [];
		foreach ($data as $row) {
			$keys = array_merge($keys, $row);
		}
		$keys = array_keys($keys);
		$columns = array_map(
			[$this, 'getQuotedName'],
			$keys
		);

		// fill unset keys with null values
		$nullArray = array_fill_keys($keys, null);
		$fullData = [];
		foreach ($data as $row) {
			$fullData[] = array_merge($nullArray, $row);
		}

		$updateStatement = '';
		if ($options['update']) {
			$updateValues = array_map(function ($name) {
				return sprintf('%s = VALUES (%s)', $name, $name);
			}, $columns);
			$updateValues = implode(', ', $updateValues);
			$updateStatement = sprintf('ON DUPLICATE KEY UPDATE %s', $updateValues);
		}

		// create placeholders per data set
		$rowsPlaceholders = array_map(function ($row) {
			return '(' . $this->generatePlaceholders(count($row)) . ')';
		}, $fullData);

		$query = sprintf(
			'INSERT %s INTO %s (%s) VALUES %s %s',
			$options['ignore'] ? 'IGNORE' : '',
			$this->getQuotedName($table),
			implode(', ', $columns),
			implode(', ', $rowsPlaceholders),
			$updateStatement
		);

		// flatten values array
		$flattened = $this->flattenArray($fullData, false);
		$statement = $this->preparedQuery($query, array_values($flattened));

		return (int)$statement->rowCount();
	}

	/**
	 * Update rows selected from $table according to $criteria with $data.
	 *
	 * @param string $table table to update
	 * @param array $data map of column => value
	 * @param array $criteria optional criteria to limit which rows are updated
	 *
	 * @return int number of rows updated
	 *
	 * @throws \Exception
	 */
	public function update(string $table, array $data, array $criteria = []): int
	{
		if (empty($data)) {
			throw new \InvalidArgumentException("Query update can't be prepared without data.");
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

	/**
	 * Deletes all rows from table for a given criteria.
	 *
	 * @param string $table table descriptor
	 * @param array $criteria criteria for the WHERE clause
	 * @param int $limit limits the number of rows to delete, if greater than 0
	 *
	 * @return int number of deleted rows
	 *
	 * @throws \Exception if no criteria are supplied, which would lead to all rows being deleted!
	 */
	public function delete(string $table, array $criteria, int $limit = 0): int
	{
		if (empty($criteria)) {
			// It is VERY VERY unlikely that we want to delete ALL ROWS from a table.
			throw new \Exception('Tried to delete all rows from a table! If this was intentional, write a raw query using Database::execute');
		}
		$where = $this->generateWhereClause($criteria);
		/** @noinspection SqlWithoutWhere can not happen because empty criteria will raise an Exception */
		$query = 'DELETE FROM ' . $this->getQuotedName($table) . ' ' . $where;
		if ($limit > 0) {
			$query .= ' LIMIT ' . $limit;
		}

		return $this->preparedQuery($query, array_values($criteria))->rowCount();
	}

	// === methods that accept SQL statements ===

	/**
	 * Returns the first row.
	 *
	 * @param string $query SQL SELECT statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 *
	 * @return array values of the first row
	 *
	 * @throws \Exception if the query is malformed
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
	 * @param string $query SQL SELECT statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 *
	 * @return array rows returned by the statement
	 *
	 * @throws \Exception if the query is malformed
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
	 * Returns the first column for all rows returned by $query.
	 *
	 * @param string $query SQL SELECT statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 *
	 * @return array all values of the first column that were returned
	 *
	 * @throws \Exception if the query is malformed
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
	 * @param string $query SQL SELECT statement to prepare and execute
	 * @param array $params parameters for prepared statement
	 *
	 * @return mixed the value of the first column of the first row
	 *
	 * @throws \Exception if the query is malformed, or if there were no results
	 */
	public function fetchValue(string $query, array $params = [])
	{
		$out = $this->preparedQuery($query, $params)->fetchAll();
		if ($out === false || count($out) === 0) {
			throw new \Exception('Expected one or more results, but none was returned.');
		}

		return array_values($out[0])[0];
	}

	/**
	 * Executes a raw query.
	 * Make sure to use placeholders when inserting values dynamically!
	 *
	 * @param string $query query to be executed
	 * @param array $params parameter values: either an array,
	 *                        or a map of placeholder names to their values.
	 *                        Example: [':placeholder' => 10] or [10, "abcdef"] without placeholder names
	 *
	 * @return \PDOStatement the result of the query execution
	 *
	 * @throws \Exception if the query is malformed
	 *
	 * @see Database::update()
	 * @see Database::delete()
	 * @deprecated Use more specific methods if possible
	 */
	public function execute(string $query, array $params = []): \PDOStatement
	{
		return $this->preparedQuery($query, $params);
	}

	// === helper methods ===

	/**
	 * Generates comma separated question marks for use with SQLs IN() operator.
	 *
	 * @param int $length number of question marks to be generated
	 *
	 * @return string string of comma separated question marks
	 */
	public function generatePlaceholders($length): string
	{
		return implode(', ', array_fill(0, $length, '?'));
	}

	public function quote($string): string
	{
		return $this->pdo->quote($string);
	}

	/**
	 * Use this where you would normally use NOW() in an SQL query.
	 *
	 * @return string the current time
	 */
	public function now(): string
	{
		return date('Y-m-d H:i:s');
	}

	/**
	 * Use this where you would normally use CURDATE() in an SQL query.
	 *
	 * @return string the current day
	 */
	public function curdate(): string
	{
		return date('Y-m-d');
	}

	/**
	 * @param Carbon|\DateTime $date
	 */
	public function date($date, $includeTime = true): string
	{
		return (clone $date)
			->setTimezone(new \DateTimeZone('Europe/Berlin'))
			->format($includeTime ? 'Y-m-d H:i:s' : 'Y-m-d')
		;
	}

	public function parseDate(string $date): Carbon
	{
		return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'Europe/Berlin');
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
	 * Flattens an array. If the keys are not kept this turns ['a', ['b', 'c'], 'd'] into
	 * ['a', 'b', 'c', 'd']. When keeping keys, it is possible that keys are overridden, i.e. this
	 * turns ['a' => 1, ['a' => 2, 'b' => 3], 'c' => 4] into ['a' => 2, 'b' => 3, 'c' => 4].
	 *
	 * @param array $array some array
	 * @param bool $keepKeys whether the new array should use the previous keys
	 *
	 * @return array flattened array
	 */
	private function flattenArray(array $array, bool $keepKeys = true): array
	{
		$out = [];

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$out = array_merge($out, $this->flattenArray($value, $keepKeys));
			} else {
				if ($keepKeys) {
					$out = array_merge($out, [$key => $value]);
				} else {
					$out[] = $value;
				}
			}
		}

		return $out;
	}

	/**
	 * Prepares and executes a query.
	 *
	 * @param string $query the query to prepare and execute
	 * @param array $params parameter values: either an array,
	 *                        or a map of placeholder names to their values.
	 *                        Example: [':placeholder' => 10] or [10, "abcdef"] without placeholder names
	 *
	 * @return \PDOStatement the result of the query execution
	 *
	 * @throws \Exception if the PDO does not accept the $query
	 */
	private function preparedQuery(string $query, array $params): \PDOStatement
	{
		$timing_start = hrtime(true);
		try {
			// Depending on the PDO's error handling, when the query can't be prepared,
			// this will either throw a PDOException, or return false.
			// to cover both cases, either throw an exception ourselves,
			// or catch the PDOException and attach it to a new exception.
			$statement = $this->pdo->prepare($query);
		} catch (\PDOException $exception) {
			throw new \Exception("Query '$query' can't be prepared.", $exception->getCode());
		}
		if (empty($statement)) {
			// PDO did not throw an exception, but returned false.
			// For consistency, we throw one ourselves.
			$errorInfo = $this->pdo->errorInfo();
			throw new \Exception("Query '$query' can't be prepared. Error info: " . implode(', ', $errorInfo));
		}

		/**
		 * for IN-style ({@see Database::generateWhereClause()}) criteria,
		 * their values are an array, which have to be flattened for positional parameter insertion.
		 */
		$params = $this->flattenArray($params);

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

		$timing_stop = hrtime(true);
		$this->influxMetrics->addDbQuery(intdiv($timing_stop - $timing_start, 1000 * 1000));

		return $statement;
	}

	/**
	 * Generates a SELECT statement for a table, a set of columns and criteria.
	 *
	 * @param string $table A full table name
	 * @param array|string $column_names either one column name, "*" for all columns or an array of multiple names
	 * @param array $criteria optional criteria ({@see Database::generateWhereClause()})
	 *
	 * @return array an array containing the generated query, and an array of parameters
	 *
	 * @throws \Exception if $column_names is neither an array nor a string
	 */
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

	/**
	 * Quotes an identifier using SQL syntax.
	 * For convenience, dots are excluded from quoting to allow for specifying the table a field is in.
	 *
	 * Example: "fs_bezirk.name" turns into "\`fs_bezirk\`.\`name\`".
	 *
	 * @param string $name An unquoted SQL identifier
	 *
	 * @return string A quoted SQL identifier
	 */
	private function getQuotedName(string $name): string
	{
		return '`' . str_replace('.', '`.`', $name) . '`';
	}

	/**
	 * Generates a WHERE clause (for use in a prepared statement) from an array of criteria,
	 * which will be joined with AND operators.
	 * Instead of directly inserting the values, positional parameter placeholders are generated.
	 *
	 * The most basic format for criteria is $field => $value.
	 * Example: ['name' => 'Berlin'] evaluates to "WHERE `name` = ?".
	 *
	 * There are some special variants:
	 * - If a value is an empty array, the corresponding expression will simple become 'false'.
	 * - If a value is an array containing values, an 'IN (...)' expression
	 *   is generated with an appropriate amount of placeholders.
	 * - A value of PHP's 'null' will be translated into 'IS NULL'.
	 * - If the field name ends with an SQL operator specified in getSupportedOperators(),
	 *   that operator will be used instead of '='.
	 *   Example: ['banana_count >=' => 10]
	 *
	 * @param array $criteria A map of column names to values, see full PHPDoc for details
	 *
	 * @return string WHERE clause with positional parameter placeholders
	 */
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
				$params[] = 'false '; // an empty array means that the WHERE clause will be false
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
