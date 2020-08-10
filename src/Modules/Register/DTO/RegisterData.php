<?php

namespace Foodsharing\Modules\Register\DTO;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Gender;

/**
 * Class that represents the registration data for a new user from the registration form.
 */
class RegisterData
{
	/**
	 * First name of the user. The database and all arrays fetched directly from the database refer to this
	 * as 'name'.
	 */
	public string $firstName;

	/**
	 * Last name of the user. The database and all arrays fetched directly from the database refer to this
	 * as 'surname' or 'nachname'.
	 */
	public string $lastName;

	/**
	 * Email address of the user.
	 */
	public string $email;

	/**
	 * Password of the user.
	 */
	public string $password;

	/**
	 * Gender of the user. Should be one of the constants in {@see Gender}.
	 */
	public int $gender;

	/**
	 * Birthday of the user.
	 */
	public DateTime $birthday;

	/**
	 * Mobile phone number of the user.
	 */
	public string $mobilePhone;

	/**
	 * Whether the user is subscribing to the newsletter.
	 */
	public bool $subscribeNewsletter;
}
