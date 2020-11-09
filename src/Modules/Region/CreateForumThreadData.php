<?php

namespace Foodsharing\Modules\Region;

use Symfony\Component\Validator\Constraints as Assert;

class CreateForumThreadData
{
	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public string $title;

	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public string $body;

	/**
	 * @Assert\Type("bool")
	 */
	public bool $sendMail;

	public static function create(): self
	{
		return new self();
	}
}
