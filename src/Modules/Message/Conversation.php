<?php

namespace Foodsharing\Modules\Message;

class Conversation
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var bool
	 */
	public $hasUnreadMessages;

	/**
	 * @var array
	 */
	public $members;

	/**
	 * @var ?Message
	 */
	public $lastMessage;

	/**
	 * @var ?array
	 */
	public $messages;
}
