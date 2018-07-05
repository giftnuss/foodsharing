<?php

namespace Foodsharing\Modules\Region;

use Symfony\Component\Validator\Constraints as Assert;

class CreateForumPostData
{
	/**
	 * @Assert\Type("string")
	 * @Assert\NotBlank()
	 */
	public $body;

	/**
	 * @Assert\Type("integer")
	 * @Assert\Range(
	 *     min = 0,
	 *     max = 1
	 * )
	 */
	public $subscribe;

	public $thread;

	public static function create(bool $isFollowing, int $threadId)
	{
		$data = new self();
		$data->subscribe = $isFollowing ? 1 : 0;
		$data->thread = $threadId;

		return $data;
	}

	public function toArray(): array
	{
		$res = [
			'body' => $this->body,
			'subscribe' => $this->subscribe,
			'thread' => $this->thread
		];

		return $res;
	}
}
