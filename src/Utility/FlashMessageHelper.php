<?php

namespace Foodsharing\Utility;

class FlashMessageHelper
{
	public function info(string $msg, string $title = ''): void
	{
		$this->storeMessage('info', $msg, $title);
	}

	public function success(string $msg, string $title = ''): void
	{
		$this->storeMessage('success', $msg, $title);
	}

	public function error(string $msg, string $title = ''): void
	{
		$this->storeMessage('error', $msg, $title);
	}

	private function storeMessage(string $type, string $msg, string $title): void
	{
		$title = $title ? '<strong>' . $title . '</strong> ' : '';
		$_SESSION['msg'][$type][] = $title . $msg;
	}
}
