<?php

namespace Foodsharing\Utility;

class FlashMessageHelper
{
	public function info(string $msg, string $title = ''): void
	{
		$this->saveMessageInSession('info', $msg, $title);
	}

	public function success(string $msg, string $title = ''): void
	{
		$this->saveMessageInSession('success', $msg, $title);
	}

	public function error(string $msg, string $title = ''): void
	{
		$this->saveMessageInSession('error', $msg, $title);
	}

	private function saveMessageInSession(string $type, string $msg, string $title): void
	{
		$title = $title ? '<strong>' . $title . '</strong> ' : '';
		$_SESSION['msg'][$type][] = $title . $msg;
	}
}
