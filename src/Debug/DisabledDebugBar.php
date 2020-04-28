<?php

namespace Foodsharing\Debug;

class DisabledDebugBar implements DebugBar
{
	public function isEnabled()
	{
		return false;
	}

	public function addMessage($message)
	{
	}

	public function addQuery($sql, $duration, $success, $error_code = null, $error_message = null)
	{
	}

	public function renderHead()
	{
	}

	public function renderContent()
	{
	}
}
