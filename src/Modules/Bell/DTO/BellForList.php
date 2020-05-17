<?php

namespace Foodsharing\Modules\Bell\DTO;

/**
 * A Data Transfer Object to contain all data of a bell to be displayed in the bell list in the frontend.
 */
class BellForList
{
	public $id;

	/**
	 * @var string
	 *
	 * @see Bell::$body
	 *
	 * The body is called "key" in the frontend
	 */
	public $key;

	/**
	 * @var string
	 *
	 * @see Bell::$name
	 *
	 * The name is called "title" in the frontend
	 */
	public $title;

	/**
	 * @var string
	 *
	 * The destination of the bell when clicked on. Will be put in the href attribute of the a tag surrounding the
	 * notification.
	 */
	public $href;

	/**
	 * @var array<string,string>
	 *
	 * @see Bell::$vars
	 *
	 * The translation key variables ("vars") will be transferred as "payload" to the frontend.
	 */
	public $payload;

	/**
	 * @var string
	 *
	 * @see Bell::$icon
	 *
	 * A CSS class of the bell's icon. Must be one or multiple CSS classes.
	 */
	public $icon;

	/**
	 * @var string
	 *
	 * @see Bell::$icon
	 *
	 * A relative URL to an image to be used as an icon.
	 *
	 * Only one of $image and $icon are supported. Whether the $icon ot the $image property is used when converting from
	 * a database array or a BellData object will be determined by whether the BellData::$icon attribute starts with '/'.
	 */
	public $image;

	/**
	 * @var string
	 *
	 * @see Bell::$time
	 *
	 * The time of the bell – usually the creation time, but some bells use different times for this attribute.
	 * The time is formatted as a string of the date and the time, separated by a 'T'. To format a date accordingly
	 * using PHP's DateTime functionality, use the following format string: 'Y-m-d\TH:i:s'
	 */
	public $createdAt;

	/**
	 * @var bool
	 *
	 * @see Bell::$closeable
	 */
	public $isCloseable;

	/**
	 * @var bool
	 *
	 * Whether the foodsharer, for whom the bell is displayed, has already clicked on it. The database refers to this
	 * as 'seen'.
	 */
	public $isRead;
}
