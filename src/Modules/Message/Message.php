<?php

namespace Foodsharing\Modules\Message;

use Carbon\Carbon;

class Message
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $body;

	/**
	 * @var Carbon
	 */
	public $sentAt;

	/**
	 * @var int
	 */
	public $authorId;

	public function __construct(string $body, int $authorId, Carbon $sentAt, int $messageId)
	{
		$this->authorId = $authorId;
		$this->sentAt = $sentAt;
		$this->body = $body;
		$this->id = $messageId;
	}
}
