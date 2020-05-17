<?php

namespace Foodsharing\Modules\Bell\DTO;

/**
 * Class that represents the data of a bell notification, in a format in which it can be sent to foodsavers or be
 * retrieved from the database. This is not an entity class, it does not provide any bell related domain logic nor
 * does it contain any access logic. You can see it more like a Data Transfer Object (DTO) used to pass a bell's data
 * between parts of the application in a unified format.
 */
class Bell
{
	/**
	 * @var string
	 *
	 * This title will be shown as a heading whenever the bell will be displayed. You should use translation keys here,
	 * which will automatically be translated whenever the bell is displayed. For the translation key placeholders, see
	 * the $vars attribute.
	 *
	 * The database and all arrays fetched directly from the database refer to this as 'name'.
	 */
	public $title;

	/**
	 * @var string
	 *
	 * The bell's content. This is the message of the bell. You should use translation keys here, which will
	 * automatically be translated whenever the bell is displayed. For translation key placeholders, see the $vars
	 * attribute.
	 */
	public $body;

	/**
	 * @var string
	 *
	 * This string will be used to display an icon next to the bell in the frontend. Supported are relative urls to
	 * images, as well as CSS classes (such as fontawesome classes).
	 */
	public $icon;

	/**
	 * @var array [string attributeName => string attributeValue]
	 *
	 * Associative array representing the attributes put on the <a> HTML tag surrounding the bell when displayed. Most
	 * commonly used to specify the href attribute (like ['href' => 'url/to/open/on/click.html']).
	 *
	 * The database and all arrays fetched directly from the database refer to this as 'attr'.
	 */
	public $link_attributes;

	/**
	 * @var array<string,string>
	 *
	 * Associative array that maps translation key placeholders to their values. Placeholders will be applied to any
	 * translation key supporting field of the bell.
	 */
	public $vars;

	/**
	 * @var string
	 *
	 * Semantic identifier that identifies a bell inside its domain. Usually consists of a type and, separated
	 * with a dash, an id related to the main entity represented by this bell. For example, 'store-new-42' represents a
	 * bell of the type "store-new", which is being used to notify foodsavers about a new store, and refers to the
	 * store with the id 42.
	 *
	 * This identifier can be used to find bells of certain domains in the database and to match them to their domain
	 * entity.
	 */
	public $identifier;

	/**
	 * @var bool
	 *
	 * Determines if the receiving foodsaver will be able to close the bell. If the value is false, the bell cannot be
	 * removed until some action happens that removes it.
	 */
	public $closeable;

	/**
	 * @var \DateTime
	 *
	 * Some bells contain information that expires after a certain amount of time. Setting an expiration date allows you
	 * to fetch expired bells and remove or update them. If you want to do this on a regular basis, you can subscribe
	 * the Gateway that controls your bells to the BellUpdateTrigger service. @see BellUpdateTrigger documentation
	 * for how to set this up.
	 */
	public $expiration;

	/**
	 * @var \DateTime
	 *
	 * A timestamp for when the bell got created
	 */
	public $time;

	public static function create(
		string $title,
		string $body,
		string $icon,
		array $link_attributes,
		array $vars,
		string $identifier = '',
		int $closeable = 1,
		\DateTime $expiration = null,
		\DateTime $time = null
	): Bell {
		$bell = new Bell();
		$bell->title = $title;
		$bell->body = $body;
		$bell->icon = $icon;
		$bell->link_attributes = $link_attributes;
		$bell->vars = $vars;
		$bell->identifier = $identifier;
		$bell->closeable = $closeable;
		$bell->expiration = $expiration;
		$bell->time = $time;

		return $bell;
	}
}
