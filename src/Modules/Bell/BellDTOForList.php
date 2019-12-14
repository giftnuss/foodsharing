<?php

namespace Foodsharing\Modules\Bell;

/**
 * A Data Transfer Object to contain all data of a bell to be displayed in the bell list in the frontend.
 */
class BellDTOForList
{
	public $id;

	/**
	 * @var string
	 *
	 * @see BellData::$body
	 *
	 * The body is called "key" in the frontend
	 */
	public $key;

	/**
	 * @var string
	 *
	 * The destination of the bell when clicked on. Will be put in the href attribute of the a tag sourrounding the
	 * notification.
	 */
	public $href;

	/**
	 * @var array<string,string>
	 *
	 * @see BellData::$vars
	 *
	 * The translation key variables ("vars") will be transferred as "payload" to the frontend.
	 */
	public $payload;

	/**
	 * @var string
	 *
	 * @see BellData::$icon
	 *
	 * A CSS class of the bell's icon. Must be one or multiple CSS classes.
	 */
	public $icon;

	/**
	 * @var string
	 *
	 * @see BellData::$icon
	 *
	 * A realtive URL to an image to be used as an icon.
	 *
	 * Only one of $image and $icon are supported. Whether the $icon ot the $image property is used when converting from
	 * a database array or a BellData object will be determined by whether the BellData::$icon attribute starts with '/'.
	 */
	public $image;

	/**
	 * @var string
	 *
	 * @see BellData::$time
	 *
	 * The time of the bell – usually the creation time, but some bells use different times for this attribute.
	 * The time is formatted as a string of the date and the time, separated by a T. To format a date accordingly
	 * using PHP's DateTime functionality, use the following format string: 'Y-m-d\TH:i:s'
	 */
	public $createdAt;

	/**
	 * @var bool
	 *
	 * @see BellData::$closeable
	 */
	public $isCloseable;

	/**
	 * @var bool
	 *
	 * Whether the foodsharer, for whom the bell is displayed, has already clicked on it. The database refers to this
	 * as 'seen'.
	 */
	public $isRead;

	/**
	 * @param array $databaseRows - 2D-array with bell data, expects indexes []['vars'] and []['attr'] to contain serialized data
	 *
	 * @return BellData[] - BellData objects with with unserialized $ball->vars and $bell->attr
	 */
	public static function createArrayFromDatatabaseRows(array $databaseRows): array
	{
		$output = [];
		foreach ($databaseRows as $row) {
			$bellDTO = new BellDTOForList();

			// This onclick-to-href conversion is probably not needed anymore
			if (isset($row['attr']['onclick'])) {
				preg_match('/profile\((.*?)\)/', $row['attr']['onclick'], $matches);
				if ($matches) {
					$row['attr']['href'] = '/profile/' . $matches[1];
				}
			}

			$bellDTO->id = $row['id'];
			$bellDTO->key = $row['body'];
			$bellDTO->payload = unserialize($row['vars'], ['allowed_classes' => false]);
			$bellDTO->href = unserialize($row['attr'], ['allowed_classes' => false])['href'];
			$bellDTO->icon = $row['icon'][0] != '/' ? $row['icon'] : null;
			$bellDTO->image = $row['icon'][0] == '/' ? $row['icon'] : null;
			$bellDTO->createdAt = (new \DateTime($row['time']))->format('Y-m-d\TH:i:s');
			$bellDTO->isRead = $row['seen'];
			$bellDTO->isCloseable = $row['closeable'];

			$output[] = $bellDTO;
		}

		return $output;
	}
}
