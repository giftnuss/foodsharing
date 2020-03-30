<?php

namespace Foodsharing\Debug;

interface DebugBar
{
	public function isEnabled();

	public function addMessage($message);

	public function addQuery($sql, $duration, $success, $error_code = null, $error_message = null);

	public function renderHead();

	public function renderContent();
}
