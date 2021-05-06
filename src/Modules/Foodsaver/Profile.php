<?php

namespace Foodsharing\Modules\Foodsaver;

class Profile
{
	public int $id;

	public ?string $name;

	public ?string $avatar;

	public int $sleepStatus;

	public function __construct(int $id, ?string $name, ?string $avatar, int $sleepStatus)
	{
		$this->id = $id;
		$this->name = $name;
		$this->avatar = $avatar;
		$this->sleepStatus = $sleepStatus;
	}
}
