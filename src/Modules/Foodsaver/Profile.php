<?php

namespace Foodsharing\Modules\Foodsaver;

class Profile
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $avatar;

	/**
	 * @var int
	 */
	public $sleepStatus;

	public function __construct(int $id, string $name, ?string $avatar, int $sleepStatus)
	{
		$this->id = $id;
		$this->name = $name;
		$this->avatar = $avatar;
		$this->sleepStatus = $sleepStatus;
	}
}
