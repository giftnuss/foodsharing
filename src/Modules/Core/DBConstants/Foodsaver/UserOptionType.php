<?php

namespace Foodsharing\Modules\Core\DBConstants\Foodsaver;

use Foodsharing\Modules\Settings\SettingsGateway;

/**
 * Types of user-specific settings. Corresponds to column 'option_type' in 'fs_foodsaver_has_options'.
 * See {@see SettingsGateway::getUserOption()} and {@see SettingsGateway::setUserOption()}.
 */
class UserOptionType
{
	public const LOCALE = 1;
}
