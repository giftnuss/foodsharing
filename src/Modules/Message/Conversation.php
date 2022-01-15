<?php

namespace Foodsharing\Modules\Message;

class Conversation
{
	public int $id;
	public ?string $title;
	public ?int $storeId;
	public bool $hasUnreadMessages;
	public array $members;
	public ?Message $lastMessage;
	public ?array $messages = [];

	public function __construct()
	{
		$this->id = 0;
		$this->title = null;
		$this->storeId = null;
		$this->hasUnreadMessages = false;
		$this->members = [];
		$this->lastMessage = null;
		$this->messages = null;
	}
}
