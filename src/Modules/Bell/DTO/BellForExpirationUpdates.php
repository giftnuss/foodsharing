<?php

namespace Foodsharing\Modules\Bell\DTO;

class BellForExpirationUpdates
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 *
	 * @see Bell::$identifier
	 */
	public $identifier;

	/**
	 * @param array $databaseRows - 2D-array with bell data, expects indexes []['vars'] and []['attr'] to contain serialized data
	 *
	 * @return Bell[] - BellData objects with with unserialized $ball->vars and $bell->attr
	 */
	public static function createArrayFromDatatabaseRows(array $databaseRows): array
	{
		$output = [];
		foreach ($databaseRows as $row) {
			$bellDTO = new BellForExpirationUpdates();

			$bellDTO->id = $row['id'];
			$bellDTO->identifier = $row['identifier'];

			$output[] = $bellDTO;
		}

		return $output;
	}
}
