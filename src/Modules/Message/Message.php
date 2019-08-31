<?php

namespace Foodsharing\Modules\Message;

class Message
{
	/**
	 * @var string
	 */
	public $body;

	/**
	 * @var \DateTime
	 */
	public $sentAt;

	/**
	 * @var int
	 */
	public $authorId;

	public function __construct(string $body, int $authorId, \DateTime $sentAt)
	{
		$this->authorId = $authorId;
		$this->sentAt = $sentAt;
		$this->body = $body;
	}
}
