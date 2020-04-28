<?php

namespace Foodsharing\Modules\Region;

use Symfony\Component\Validator\Constraints as Assert;

class CreateForumThreadData
{
	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public $title;

	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public $body;

	/**
	 * @Assert\Type("bool")
	 */
	public $sendMail;

	public static function create()
	{
		return new self();
	}
}
