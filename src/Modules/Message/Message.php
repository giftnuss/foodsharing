<?php

namespace Foodsharing\Modules\Message;

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
	 * @var \DateTime
	 */
	public $sentAt;

	/**
	 * @var int
	 */
	public $authorId;

	public function __construct(string $body, int $authorId, \DateTime $sentAt, int $messageId)
	{
		$this->authorId = $authorId;
		$this->sentAt = $sentAt;
		$this->body = $body;
		$this->id = $messageId;
	}
}
